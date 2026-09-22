import { useMemo, useRef, useState } from 'react';
import { AlignCenter, AlignJustify, AlignLeft, AlignRight, ArrowDown, ArrowUp, CaseLower, CaseUpper, CheckSquare, Code2, FileText, Heading1, Heading2, Heading3, List, ListOrdered, Minus, Move, Plus, Quote, Sparkles, Type } from 'lucide-react';

const FONTS = [
    { value: 'Inter', label: 'Inter' },
    { value: 'Poppins', label: 'Poppins' },
    { value: 'Space Grotesk', label: 'Space Grotesk' },
    { value: 'JetBrains Mono', label: 'JetBrains Mono' },
    { value: 'Manrope', label: 'Manrope' },
    { value: 'Lora', label: 'Lora' },
];

export const MEMO_FONT_STACKS = {
    Inter: "'Inter', system-ui, sans-serif",
    Poppins: "'Poppins', system-ui, sans-serif",
    'Space Grotesk': "'Space Grotesk', system-ui, sans-serif",
    'JetBrains Mono': "'JetBrains Mono', ui-monospace, SFMono-Regular, monospace",
    Manrope: "'Manrope', system-ui, sans-serif",
    Lora: "'Lora', Georgia, serif",
};

function stripInlineCaseMarkers(value) {
    return String(value ?? '').replace(/\[\[upper\]\]|\[\[\/upper\]\]|\[\[lower\]\]|\[\[\/lower\]\]|\[\[capitalize\]\]|\[\[\/capitalize\]\]/g, '');
}
const DEFAULT_FORMATTING = { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 };
const BLOCKS = [
    { key: 'h1', label: 'Titre 1', prefix: '# ', icon: Heading1 },
    { key: 'h2', label: 'Titre 2', prefix: '## ', icon: Heading2 },
    { key: 'h3', label: 'Titre 3', prefix: '### ', icon: Heading3 },
    { key: 'bullet', label: 'Liste', prefix: '- ', icon: List },
    { key: 'number', label: 'Liste numérotée', prefix: '1. ', icon: ListOrdered },
    { key: 'check', label: 'Checklist', prefix: '- [ ] ', icon: CheckSquare },
    { key: 'quote', label: 'Citation', prefix: '> ', icon: Quote },
];

export function defaultMemoFormatting(value = {}) { return { ...DEFAULT_FORMATTING, ...value }; }
export function autoMemoTitle(content) { const firstLine = String(content ?? '').split('\n').map((line) => stripInlineCaseMarkers(line).replace(/^\s*#+\s*/, '').trim()).find(Boolean); return firstLine ? firstLine.replace(/[`*_>#]/g, '').trim().slice(0, 255) : ''; }

export default function MemoEditor({ content, onContentChange, formatting, onFormattingChange, onAutoTitle }) {
    const style = useMemo(() => defaultMemoFormatting(formatting), [formatting]);
    const textareaRef = useRef(null);
    const [slashOpen, setSlashOpen] = useState(false);
    function update(key, value) { onFormattingChange({ ...style, [key]: value }); }
    function adjustSize(delta) { update('fontSize', Math.min(32, Math.max(12, Number(style.fontSize) + delta))); }

    function applyTextTransform(mode) {
        const el = textareaRef.current;
        if (!el) return;
        const start = el.selectionStart;
        const end = el.selectionEnd;

        if (start === end) {
            update('textTransform', mode);
            return;
        }

        const selected = stripInlineCaseMarkers(content.slice(start, end));
        let value = selected;

        if (mode === 'uppercase') value = selected.toUpperCase();
        if (mode === 'lowercase') value = selected.toLowerCase();
        if (mode === 'capitalize') value = selected.replace(/(^|[\s-])\p{L}/gu, (match) => match.toUpperCase());
        if (mode === 'none') value = selected;

        const next = content.slice(0, start) + value + content.slice(end);
        onContentChange(next);

        // A selection transform becomes part of the stored text. Disable the
        // global CSS transform so it cannot override the selected characters.
        onFormattingChange({ ...style, textTransform: 'none' });

        requestAnimationFrame(() => {
            el.focus();
            el.setSelectionRange(start, start + value.length);
        });
    }

    function transformSelection(prefix, suffix = '') {
        const el = textareaRef.current; if (!el) return;
        const start = el.selectionStart; const end = el.selectionEnd;
        const selected = content.slice(start, end);
        const value = selected.split('\n').map((line) => prefix + line.replace(/^((#{1,3}|[-*]|\d+\.|> |\- \[[ xX]\] )\s*)/, '') + suffix).join('\n');
        const next = content.slice(0, start) + value + content.slice(end);
        onContentChange(next);
        requestAnimationFrame(() => { el.focus(); el.setSelectionRange(start, start + value.length); });
    }

    function insertBlock(prefix) {
        const el = textareaRef.current; if (!el) return;
        const pos = el.selectionStart;
        const before = content.slice(0, pos); const after = content.slice(pos);
        const needsBreak = before.length > 0 && !before.endsWith('\n') ? '\n\n' : '';
        const insertion = needsBreak + prefix;
        const next = before + insertion + after;
        onContentChange(next);
        requestAnimationFrame(() => { el.focus(); const cursor = pos + insertion.length; el.setSelectionRange(cursor, cursor); });
    }

    function moveBlock(direction) {
        const el = textareaRef.current; if (!el) return;
        const pos = el.selectionStart;
        const blocks = content.split(/\n{2,}/);
        let cursor = 0; let index = 0;
        for (let i = 0; i < blocks.length; i += 1) { const end = cursor + blocks[i].length; if (pos >= cursor && pos <= end) { index = i; break; } cursor = end + 2; }
        const target = index + direction; if (target < 0 || target >= blocks.length) return;
        [blocks[index], blocks[target]] = [blocks[target], blocks[index]];
        const next = blocks.join('\n\n'); onContentChange(next);
        requestAnimationFrame(() => { el.focus(); el.setSelectionRange(0, 0); });
    }

    function handleKeyDown(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') { event.preventDefault(); transformSelection('**', '**'); return; }
        if (event.key === 'Enter') {
            const el = textareaRef.current; const lineStart = content.lastIndexOf('\n', el.selectionStart - 1) + 1; const line = content.slice(lineStart, el.selectionStart);
            if (/^(- \[ \]|- |\* |\d+\.)\s/.test(line) && line.trim().length <= 3) { event.preventDefault(); onContentChange(content.slice(0, lineStart) + content.slice(el.selectionStart)); return; }
        }
    }

    function onChange(event) {
        const value = event.target.value; onContentChange(value);
        const lineStart = value.lastIndexOf('\n', event.target.selectionStart - 1) + 1; const line = value.slice(lineStart, event.target.selectionStart);
        setSlashOpen(line.trimStart().startsWith('/') && line.trim().length > 1);
    }

    return <div className="overflow-hidden rounded-2xl border border-white/[0.07] bg-[#08111F]">
        <div className="flex flex-wrap items-center gap-2 border-b border-white/[0.06] bg-[#0D1725] p-2">
            <div className="flex items-center gap-1 rounded-lg border border-white/[0.06] bg-[#08111F] px-1.5"><Type size={14} className="ml-1 text-slate-600" /><select value={style.fontFamily} onChange={(e) => update('fontFamily', e.target.value)} className="h-8 border-0 bg-transparent px-1 text-xs text-slate-300 outline-none" aria-label="Police">{FONTS.map((font) => <option key={font.value} value={font.value}>{font.label}</option>)}</select></div>
            <div className="flex items-center rounded-lg border border-white/[0.06] bg-[#08111F]"><button type="button" onClick={() => adjustSize(-1)} className="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-white"><Minus size={13} /></button><span className="min-w-8 text-center text-[11px] text-slate-400">{style.fontSize}</span><button type="button" onClick={() => adjustSize(1)} className="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-white"><Plus size={13} /></button></div>
            <div className="flex rounded-lg border border-white/[0.06] bg-[#08111F] p-0.5"><FormatButton active={style.textTransform === 'none'} onClick={() => applyTextTransform('none')} label="Normal"><Type size={14} /></FormatButton><FormatButton active={style.textTransform === 'uppercase'} onClick={() => applyTextTransform('uppercase')} label="Majuscules"><CaseUpper size={14} /></FormatButton><FormatButton active={style.textTransform === 'lowercase'} onClick={() => applyTextTransform('lowercase')} label="Minuscules"><CaseLower size={14} /></FormatButton></div>
            <div className="flex rounded-lg border border-white/[0.06] bg-[#08111F] p-0.5"><FormatButton active={style.textAlign === 'left'} onClick={() => update('textAlign', 'left')} label="Gauche"><AlignLeft size={14} /></FormatButton><FormatButton active={style.textAlign === 'center'} onClick={() => update('textAlign', 'center')} label="Centre"><AlignCenter size={14} /></FormatButton><FormatButton active={style.textAlign === 'right'} onClick={() => update('textAlign', 'right')} label="Droite"><AlignRight size={14} /></FormatButton><FormatButton active={style.textAlign === 'justify'} onClick={() => update('textAlign', 'justify')} label="Justifié"><AlignJustify size={14} /></FormatButton></div>
            <button type="button" onClick={() => transformSelection('**', '**')} className="flex h-8 items-center rounded-lg border border-white/[0.06] bg-[#08111F] px-2 text-xs font-bold text-slate-500 hover:text-white" title="Gras">B</button>
            <div className="h-6 w-px bg-white/[0.06]" />
            <select defaultValue="" onChange={(e) => { const block = BLOCKS.find((item) => item.key === e.target.value); if (block) transformSelection(block.prefix); e.target.value = ''; }} className="h-8 rounded-lg border border-white/[0.06] bg-[#08111F] px-2 text-xs text-slate-400 outline-none" aria-label="Insérer un bloc"><option value="">Bloc…</option>{BLOCKS.map((block) => <option key={block.key} value={block.key}>{block.label}</option>)}</select>
            <FormatButton onClick={() => insertBlock('```\n\n```')} label="Bloc de code"><Code2 size={14} /></FormatButton>
            <FormatButton onClick={() => insertBlock('---')} label="Séparateur"><Minus size={14} /></FormatButton>
            <FormatButton onClick={() => moveBlock(-1)} label="Déplacer le bloc vers le haut"><ArrowUp size={14} /></FormatButton>
            <FormatButton onClick={() => moveBlock(1)} label="Déplacer le bloc vers le bas"><ArrowDown size={14} /></FormatButton>
            <button type="button" onClick={() => onAutoTitle?.(autoMemoTitle(content))} className="ml-auto inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#FF6A00]/20 bg-[#FF6A00]/10 px-2.5 text-xs font-semibold text-[#FF8A3D]"><Sparkles size={13} />Titre automatique</button>
        </div>
        {slashOpen && <div className="border-b border-white/[0.05] bg-[#0D1725] px-3 py-2 text-[11px] text-slate-500">Commande rapide : <span className="text-slate-300">/titre</span>, <span className="text-slate-300">/liste</span>, <span className="text-slate-300">/check</span> ou <span className="text-slate-300">/code</span>.</div>}
        <div className="flex items-center gap-2 border-b border-white/[0.05] px-3 py-2 text-[10px] text-slate-600"><FileText size={12} /><span>Éditeur par blocs · sélectionne un texte puis applique un bloc, ou utilise les commandes rapides.</span></div>
        <textarea ref={textareaRef} value={content} onChange={onChange} onKeyDown={handleKeyDown} rows={18} maxLength={50000} aria-label="Contenu de la fiche" style={{ fontFamily: MEMO_FONT_STACKS[style.fontFamily] ?? MEMO_FONT_STACKS.Inter, fontSize: style.fontSize, fontWeight: style.fontWeight, textTransform: style.textTransform, textAlign: style.textAlign }} className="min-h-[400px] w-full resize-y border-0 bg-[#08111F] p-4 leading-7 text-slate-200 outline-none placeholder:text-slate-600" placeholder="Écris une note, puis utilise Bloc… pour structurer ta fiche comme dans Notion." />
    </div>;
}

function FormatButton({ active, onClick, label, children }) { return <button type="button" onClick={onClick} aria-label={label} title={label} className={'flex h-8 w-8 items-center justify-center rounded-lg border border-white/[0.06] bg-[#08111F] transition ' + (active ? 'text-white' : 'text-slate-500 hover:text-white')}>{children}</button>; }