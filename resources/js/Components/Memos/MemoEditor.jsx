import { useMemo } from 'react';
import { AlignCenter, AlignJustify, AlignLeft, AlignRight, CaseLower, CaseUpper, FileText, Minus, Plus, Sparkles, Type } from 'lucide-react';

const FONTS = [
    { value: 'Inter', label: 'Inter' },
    { value: 'Poppins', label: 'Poppins' },
    { value: 'Space Grotesk', label: 'Space Grotesk' },
    { value: 'JetBrains Mono', label: 'JetBrains Mono' },
    { value: 'Georgia', label: 'Georgia' },
    { value: 'Arial', label: 'Arial' },
];

const DEFAULT_FORMATTING = { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 };

export function defaultMemoFormatting(value = {}) { return { ...DEFAULT_FORMATTING, ...value }; }

export function autoMemoTitle(content) {
    const firstLine = String(content ?? '').split('\n').map((line) => line.replace(/^\s*#+\s*/, '').trim()).find(Boolean);
    if (!firstLine) return '';
    return firstLine.replace(/[`*_>#]/g, '').trim().slice(0, 255);
}

export default function MemoEditor({ content, onContentChange, formatting, onFormattingChange, onAutoTitle }) {
    const style = useMemo(() => defaultMemoFormatting(formatting), [formatting]);
    function update(key, value) { onFormattingChange({ ...style, [key]: value }); }
    function adjustSize(delta) { update('fontSize', Math.min(32, Math.max(12, Number(style.fontSize) + delta))); }

    return (
        <div className="overflow-hidden rounded-2xl border border-white/[0.07] bg-[#08111F]">
            <div className="flex flex-wrap items-center gap-2 border-b border-white/[0.06] bg-[#0D1725] p-2">
                <div className="flex items-center gap-1 rounded-lg border border-white/[0.06] bg-[#08111F] px-1.5">
                    <Type size={14} className="ml-1 text-slate-600" />
                    <select value={style.fontFamily} onChange={(e) => update('fontFamily', e.target.value)} className="h-8 border-0 bg-transparent px-1 text-xs text-slate-300 outline-none" aria-label="Police">
                        {FONTS.map((font) => <option key={font.value} value={font.value}>{font.label}</option>)}
                    </select>
                </div>
                <div className="flex items-center rounded-lg border border-white/[0.06] bg-[#08111F]">
                    <button type="button" onClick={() => adjustSize(-1)} className="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-white" aria-label="Réduire la taille"><Minus size={13} /></button>
                    <span className="min-w-8 text-center text-[11px] text-slate-400">{style.fontSize}</span>
                    <button type="button" onClick={() => adjustSize(1)} className="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-white" aria-label="Augmenter la taille"><Plus size={13} /></button>
                </div>
                <div className="flex rounded-lg border border-white/[0.06] bg-[#08111F] p-0.5">
                    <FormatButton active={style.textTransform === 'none'} onClick={() => update('textTransform', 'none')} label="Normal"><Type size={14} /></FormatButton>
                    <FormatButton active={style.textTransform === 'uppercase'} onClick={() => update('textTransform', 'uppercase')} label="Majuscules"><CaseUpper size={14} /></FormatButton>
                    <FormatButton active={style.textTransform === 'lowercase'} onClick={() => update('textTransform', 'lowercase')} label="Minuscules"><CaseLower size={14} /></FormatButton>
                </div>
                <div className="flex rounded-lg border border-white/[0.06] bg-[#08111F] p-0.5">
                    <FormatButton active={style.textAlign === 'left'} onClick={() => update('textAlign', 'left')} label="Gauche"><AlignLeft size={14} /></FormatButton>
                    <FormatButton active={style.textAlign === 'center'} onClick={() => update('textAlign', 'center')} label="Centre"><AlignCenter size={14} /></FormatButton>
                    <FormatButton active={style.textAlign === 'right'} onClick={() => update('textAlign', 'right')} label="Droite"><AlignRight size={14} /></FormatButton>
                    <FormatButton active={style.textAlign === 'justify'} onClick={() => update('textAlign', 'justify')} label="Justifié"><AlignJustify size={14} /></FormatButton>
                </div>
                <button type="button" onClick={() => update('fontWeight', style.fontWeight === 700 ? 400 : 700)} className={'flex h-8 items-center rounded-lg border px-2 text-xs font-bold transition ' + (style.fontWeight === 700 ? 'border-[#FF6A00]/30 bg-[#FF6A00]/10 text-[#FF8A3D]' : 'border-white/[0.06] bg-[#08111F] text-slate-500 hover:text-white')} title="Gras">B</button>
                <button type="button" onClick={() => onAutoTitle?.(autoMemoTitle(content))} className="ml-auto inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#FF6A00]/20 bg-[#FF6A00]/10 px-2.5 text-xs font-semibold text-[#FF8A3D] transition hover:bg-[#FF6A00]/15"><Sparkles size={13} />Titre automatique</button>
            </div>
            <div className="flex items-center gap-2 border-b border-white/[0.05] px-3 py-2 text-[10px] text-slate-600"><FileText size={12} /><span>La mise en forme est enregistrée avec la fiche.</span></div>
            <textarea value={content} onChange={(e) => onContentChange(e.target.value)} rows={16} maxLength={50000} aria-label="Contenu de la fiche" style={{ fontFamily: style.fontFamily, fontSize: style.fontSize, fontWeight: style.fontWeight, textTransform: style.textTransform, textAlign: style.textAlign }} className="min-h-[360px] w-full resize-y border-0 bg-[#08111F] p-4 leading-7 text-slate-200 outline-none placeholder:text-slate-600" placeholder="Écris tes notes ici..." />
        </div>
    );
}

function FormatButton({ active, onClick, label, children }) {
    return <button type="button" onClick={onClick} aria-label={label} title={label} className={'flex h-7 w-7 items-center justify-center rounded-md transition ' + (active ? 'bg-white/[0.08] text-white' : 'text-slate-600 hover:text-slate-300')}>{children}</button>;
}