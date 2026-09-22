import { useState } from 'react';
import { Check, Copy } from 'lucide-react';

const DEFAULT_FORMATTING = { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 };
const ALLOWED_FONTS = new Set(['Inter', 'Poppins', 'Space Grotesk', 'JetBrains Mono', 'Georgia', 'Arial']);
const ALLOWED_TRANSFORMS = new Set(['none', 'uppercase', 'lowercase', 'capitalize']);
const ALLOWED_ALIGNS = new Set(['left', 'center', 'right', 'justify']);
const ALLOWED_WEIGHTS = new Set([400, 500, 600, 700]);

function parse(content) {
    const fence = new RegExp('```([\\w+-]*)\\n([\\s\\S]*?)```', 'g');
    const parts = []; let last = 0; let match;
    while ((match = fence.exec(content)) !== null) {
        if (match.index > last) parts.push({ type: 'text', value: content.slice(last, match.index) });
        parts.push({ type: 'code', lang: match[1], value: match[2].replace(/\n$/, '') });
        last = match.index + match[0].length;
    }
    if (last < content.length) parts.push({ type: 'text', value: content.slice(last) });
    return parts;
}

function safeFormatting(value) {
    const input = { ...DEFAULT_FORMATTING, ...(value ?? {}) };
    return {
        fontFamily: ALLOWED_FONTS.has(input.fontFamily) ? input.fontFamily : 'Inter',
        fontSize: Math.min(32, Math.max(12, Number(input.fontSize) || 16)),
        textTransform: ALLOWED_TRANSFORMS.has(input.textTransform) ? input.textTransform : 'none',
        textAlign: ALLOWED_ALIGNS.has(input.textAlign) ? input.textAlign : 'left',
        fontWeight: ALLOWED_WEIGHTS.has(Number(input.fontWeight)) ? Number(input.fontWeight) : 400,
    };
}

function CodeBlock({ lang, value }) {
    const [copied, setCopied] = useState(false);
    async function copy() { try { await navigator.clipboard.writeText(value); setCopied(true); setTimeout(() => setCopied(false), 1500); } catch {} }
    return <div className="overflow-hidden rounded-xl border border-white/[0.06] bg-[#060D18]">
        <div className="flex items-center justify-between border-b border-white/[0.06] px-4 py-2"><span className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{lang || 'code'}</span><button type="button" onClick={copy} className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 transition hover:text-white">{copied ? <Check size={13} /> : <Copy size={13} />}{copied ? 'Copié' : 'Copier'}</button></div>
        <pre className="overflow-x-auto p-4 text-[13px] leading-6 text-slate-200"><code>{value}</code></pre>
    </div>;
}

export default function MemoContent({ content, formatting }) {
    const parts = parse(content ?? '');
    const style = safeFormatting(formatting);
    return <div className="space-y-4">{parts.map((part, index) => part.type === 'code' ? <CodeBlock key={index} lang={part.lang} value={part.value} /> : part.value.trim() === '' ? null : <p key={index} className="whitespace-pre-wrap leading-7 text-slate-300" style={{ fontFamily: style.fontFamily, fontSize: style.fontSize, fontWeight: style.fontWeight, textTransform: style.textTransform, textAlign: style.textAlign }}>{part.value.trim()}</p>)}</div>;
}