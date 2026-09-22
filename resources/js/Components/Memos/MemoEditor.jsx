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

export function normalizeMemoContent(value) {
    return String(value ?? '')
        .replace(/\[\[upper\]\]([\s\S]*?)\[\[\/upper\]\]/g, (_, text) => text.toUpperCase())
        .replace(/\[\[lower\]\]([\s\S]*?)\[\[\/lower\]\]/g, (_, text) => text.toLowerCase())
        .replace(/\[\[capitalize\]\]([\s\S]*?)\[\[\/capitalize\]\]/g, (_, text) => text.replace(/(^|[\s-])\p{L}/gu, (match) => match.toUpperCase()));
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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function memoValueToHtml(value) {
    const raw = String(value ?? '');
    if (/<(?:p|div|h[1-6]|strong|b|ul|ol|li|blockquote|pre|code|br|hr)\\b/i.test(raw)) {
        return sanitizeMemoHtml(raw);
    }

    return raw
        .split(/\\n{2,}/)
        .map((block) => {
            const line = block.trim();
            if (!line) return '';
            if (/^###\\s+/.test(line)) return '<h4>' + escapeHtml(line.replace(/^###\\s+/, '')) + '</h4>';
            if (/^##\\s+/.test(line)) return '<h3>' + escapeHtml(line.replace(/^##\\s+/, '')) + '</h3>';
            if (/^#\\s+/.test(line)) return '<h2>' + escapeHtml(line.replace(/^#\\s+/, '')) + '</h2>';
            if (/^>\\s?/.test(line)) return '<blockquote>' + escapeHtml(line.replace(/^>\\s?/, '')) + '</blockquote>';
            if (/^[-*]\\s+/.test(line)) return '<ul>' + line.split(/\\n/).map((item) => '<li>' + escapeHtml(item.replace(/^[-*]\\s+/, '')) + '</li>').join('') + '</ul>';
            if (/^\\d+\\.\\s+/.test(line)) return '<ol>' + line.split(/\\n/).map((item) => '<li>' + escapeHtml(item.replace(/^\\d+\\.\\s+/, '')) + '</li>').join('') + '</ol>';
            return '<p>' + escapeHtml(line).replace(/\\*\\*([^*]+)\\*\\*/g, '<strong>$1</strong>') + '</p>';
        })
        .join('');
}

export function sanitizeMemoHtml(value) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(String(value ?? ''), 'text/html');
    const allowed = new Set(['P','DIV','BR','H2','H3','H4','STRONG','B','EM','I','U','S','UL','OL','LI','BLOCKQUOTE','PRE','CODE','HR','SPAN','FONT']);
    doc.body.querySelectorAll('*').forEach((node) => {
        if (!allowed.has(node.tagName)) {
            node.replaceWith(...Array.from(node.childNodes));
            return;
        }
        Array.from(node.attributes).forEach((attribute) => {
            if (!['style','face','size'].includes(attribute.name.toLowerCase())) node.removeAttribute(attribute.name);
        });
        if (node.tagName === 'FONT' && node.getAttribute('face')) {
            node.style.fontFamily = node.getAttribute('face');
            node.removeAttribute('face');
        }
    });
    return doc.body.innerHTML;
}

export default function MemoEditor({ content, onContentChange, formatting, onFormattingChange, onAutoTitle }) {
    const style = useMemo(() => defaultMemoFormatting(formatting), [formatting]);
    const editorRef = useRef(null);
    const [slashOpen, setSlashOpen] = useState(false);
    const initializedRef = useRef(false);

    function update(key, value) {
        onFormattingChange({ ...style, [key]: value });
    }

    function focusEditor() {
        editorRef.current?.focus();
    }

    function emitContent() {
        const html = sanitizeMemoHtml(editorRef.current?.innerHTML ?? '');
        onContentChange(html);
        return html;
    }

    function restoreSelection() {
        focusEditor();
    }

    function runCommand(command, value = null) {
        focusEditor();
        document.execCommand(command, false, value);
        emitContent();
        restoreSelection();
    }

    function applyCase(mode) {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0 || selection.isCollapsed) {
            update('textTransform', mode);
            return;
        }

        const range = selection.getRangeAt(0);
        if (!editorRef.current?.contains(range.commonAncestorContainer)) return;

        const fragment = range.cloneContents();
        const walker = document.createTreeWalker(fragment, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach((node) => {
            if (mode === 'uppercase') node.nodeValue = node.nodeValue.toUpperCase();
            if (mode === 'lowercase') node.nodeValue = node.nodeValue.toLowerCase();
            if (mode === 'capitalize') node.nodeValue = node.nodeValue.replace(/(^|[\\s-])\\p{L}/gu, (match) => match.toUpperCase());
        });

        range.deleteContents();
        range.insertNode(fragment);
        selection.removeAllRanges();
        selection.addRange(range);
        onFormattingChange({ ...style, textTransform: 'none' });
        emitContent();
    }

    function applyBlock(type) {
        focusEditor();
        if (type === 'h1') document.execCommand('formatBlock', false, 'h2');
        if (type === 'h2') document.execCommand('formatBlock', false, 'h3');
        if (type === 'h3') document.execCommand('formatBlock', false, 'h4');
        if (type === 'bullet') document.execCommand('insertUnorderedList');
        if (type === 'number') document.execCommand('insertOrderedList');
        if (type === 'quote') document.execCommand('formatBlock', false, 'blockquote');
        if (type === 'check') document.execCommand('insertUnorderedList');
        emitContent();
    }

    function applyFont(font) {
        focusEditor();
        document.execCommand('fontName', false, font);
        emitContent();
    }

    function adjustSize(delta) {
        const next = Math.min(32, Math.max(12, Number(style.fontSize) + delta));
        update('fontSize', next);
    }

    function insertCode() {
        focusEditor();
        document.execCommand('formatBlock', false, 'pre');
        emitContent();
    }

    function insertSeparator() {
        focusEditor();
        document.execCommand('insertHorizontalRule');
        emitContent();
    }

    function insertText(text) {
        focusEditor();
        document.execCommand('insertText', false, text);
        emitContent();
    }

    function handleInput() {
        const text = editorRef.current?.innerText ?? '';
        setSlashOpen(text.trimStart().startsWith('/') && text.trim().length > 1);
        emitContent();
    }

    function handleKeyDown(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') {
            event.preventDefault();
            runCommand('bold');
            return;
        }
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'i') {
            event.preventDefault();
            runCommand('italic');
        }
    }

    function initializeEditor(element) {
        if (!element || initializedRef.current) return;
        initializedRef.current = true;
        element.innerHTML = memoValueToHtml(content);
    }

    return <div className="overflow-hidden rounded-2xl border border-white/[0.07] bg-[#08111F]">
        <div className="flex flex-wrap items-center gap-2 border-b border-white/[0.06] bg-[#0D1725] p-2">
            <div className="flex items-center gap-1 rounded-lg border border-white/[0.06] bg-[#08111F] px-1.5">
                <Type size={14} className="ml-1 text-slate-600" />
                <select value={style.fontFamily} onChange={(e) => applyFont(e.target.value)} className="h-8 border-0 bg-transparent px-1 text-xs text-slate-300 outline-none" aria-label="Police">
                    {FONTS.map((font) => <option key={font.value} value={font.value}>{font.label}</option>)}
                </select>
            </div>
            <div className="flex items-center rounded-lg border border-white/[0.06] bg-[#08111F]">
                <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => adjustSize(-1)} className="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-white"><Minus size={13} /></button>
                <span className="min-w-8 text-center text-[11px] text-slate-400">{style.fontSize}</span>
                <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => adjustSize(1)} className="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-white"><Plus size={13} /></button>
            </div>
            <div className="flex rounded-lg border border-white/[0.06] bg-[#08111F] p-0.5">
                <FormatButton active={style.textTransform === 'none'} onClick={() => applyCase('none')} label="Normal"><Type size={14} /></FormatButton>
                <FormatButton onClick={() => applyCase('uppercase')} label="Majuscules"><CaseUpper size={14} /></FormatButton>
                <FormatButton onClick={() => applyCase('lowercase')} label="Minuscules"><CaseLower size={14} /></FormatButton>
            </div>
            <div className="flex rounded-lg border border-white/[0.06] bg-[#08111F] p-0.5">
                <FormatButton onClick={() => update('textAlign', 'left')} label="Gauche"><AlignLeft size={14} /></FormatButton>
                <FormatButton onClick={() => update('textAlign', 'center')} label="Centre"><AlignCenter size={14} /></FormatButton>
                <FormatButton onClick={() => update('textAlign', 'right')} label="Droite"><AlignRight size={14} /></FormatButton>
                <FormatButton onClick={() => update('textAlign', 'justify')} label="Justifié"><AlignJustify size={14} /></FormatButton>
            </div>
            <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => runCommand('bold')} className="flex h-8 items-center rounded-lg border border-white/[0.06] bg-[#08111F] px-2 text-xs font-bold text-slate-500 hover:text-white" title="Gras">B</button>
            <div className="h-6 w-px bg-white/[0.06]" />
            <select defaultValue="" onChange={(e) => { if (e.target.value) applyBlock(e.target.value); e.target.value = ''; }} className="h-8 rounded-lg border border-white/[0.06] bg-[#08111F] px-2 text-xs text-slate-400 outline-none" aria-label="Insérer un bloc">
                <option value="">Bloc…</option>
                <option value="h1">Titre 1</option><option value="h2">Titre 2</option><option value="h3">Titre 3</option>
                <option value="bullet">Liste</option><option value="number">Liste numérotée</option><option value="check">Checklist</option><option value="quote">Citation</option>
            </select>
            <FormatButton onClick={insertCode} label="Bloc de code"><Code2 size={14} /></FormatButton>
            <FormatButton onClick={insertSeparator} label="Séparateur"><Minus size={14} /></FormatButton>
            <button type="button" onClick={() => insertText('')} className="hidden" aria-hidden="true" />
            <button type="button" onClick={() => onAutoTitle?.(autoMemoTitle(editorRef.current?.innerText ?? ''))} className="ml-auto inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#FF6A00]/20 bg-[#FF6A00]/10 px-2.5 text-xs font-semibold text-[#FF8A3D]"><Sparkles size={13} />Titre automatique</button>
        </div>
        {slashOpen && <div className="border-b border-white/[0.05] bg-[#0D1725] px-3 py-2 text-[11px] text-slate-500">Écris directement dans l'éditeur puis utilise les boutons pour formater le texte.</div>}
        <div className="flex items-center gap-2 border-b border-white/[0.05] px-3 py-2 text-[10px] text-slate-600"><FileText size={12} /><span>Éditeur riche · les titres, listes et styles sont appliqués directement, sans dièses ni marqueurs.</span></div>
        <div
            ref={(element) => { editorRef.current = element; initializeEditor(element); }}
            contentEditable
            suppressContentEditableWarning
            onInput={handleInput}
            onKeyDown={handleKeyDown}
            role="textbox"
            aria-multiline="true"
            aria-label="Contenu de la fiche"
            spellCheck
            style={{ fontFamily: MEMO_FONT_STACKS[style.fontFamily] ?? MEMO_FONT_STACKS.Inter, fontSize: style.fontSize, fontWeight: style.fontWeight, textTransform: style.textTransform, textAlign: style.textAlign }}
            className="min-h-[400px] w-full resize-y overflow-y-auto bg-[#08111F] p-4 leading-7 text-slate-200 outline-none empty:before:text-slate-600 empty:before:content-[attr(data-placeholder)]"
            data-placeholder="Écris une note puis applique les formats directement, comme dans Notion."
        />
    </div>;
}
function FormatButton({ active, onClick, label, children }) { return <button type="button" onClick={onClick} aria-label={label} title={label} className={'flex h-8 w-8 items-center justify-center rounded-lg border border-white/[0.06] bg-[#08111F] transition ' + (active ? 'text-white' : 'text-slate-500 hover:text-white')}>{children}</button>; }