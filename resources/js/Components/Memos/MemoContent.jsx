import { useState } from 'react';
import { Check, Copy } from 'lucide-react';
import { sanitizeMemoHtml } from '@/Components/Memos/MemoEditor';

const DEFAULT_FORMATTING = { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 };
const ALLOWED_FONTS = new Set(['Inter', 'Poppins', 'Space Grotesk', 'JetBrains Mono', 'Manrope', 'Lora']);

const FONT_STACKS = {
    Inter: "'Inter', system-ui, sans-serif",
    Poppins: "'Poppins', system-ui, sans-serif",
    'Space Grotesk': "'Space Grotesk', system-ui, sans-serif",
    'JetBrains Mono': "'JetBrains Mono', ui-monospace, SFMono-Regular, monospace",
    Manrope: "'Manrope', system-ui, sans-serif",
    Lora: "'Lora', Georgia, serif",
};
const ALLOWED_TRANSFORMS = new Set(['none', 'uppercase', 'lowercase', 'capitalize']);
const ALLOWED_ALIGNS = new Set(['left', 'center', 'right', 'justify']);
const ALLOWED_WEIGHTS = new Set([400, 500, 600, 700]);

function safeFormatting(value) {
    const input = { ...DEFAULT_FORMATTING, ...(value ?? {}) };
    return { fontFamily: ALLOWED_FONTS.has(input.fontFamily) ? input.fontFamily : 'Inter', fontSize: Math.min(32, Math.max(12, Number(input.fontSize) || 16)), textTransform: ALLOWED_TRANSFORMS.has(input.textTransform) ? input.textTransform : 'none', textAlign: ALLOWED_ALIGNS.has(input.textAlign) ? input.textAlign : 'left', fontWeight: ALLOWED_WEIGHTS.has(Number(input.fontWeight)) ? Number(input.fontWeight) : 400 };
}

function inline(text) {
    // Backward compatibility for memos created by the previous marker-based
    // implementation. New edits no longer write these markers.
    const pattern = /(\[\[upper\]\][\s\S]*?\[\[\/upper\]\]|\[\[lower\]\][\s\S]*?\[\[\/lower\]\]|\[\[capitalize\]\][\s\S]*?\[\[\/capitalize\]\]|\*\*[^*]+\*\*|`[^`]+`)/g;
    return text.split(pattern).map((part, index) => {
        if (part.startsWith('[[upper]]')) return <span key={index} style={{ textTransform: 'none' }}>{part.slice(9, -10).toUpperCase()}</span>;
        if (part.startsWith('[[lower]]')) return <span key={index} style={{ textTransform: 'none' }}>{part.slice(9, -10).toLowerCase()}</span>;
        if (part.startsWith('[[capitalize]]')) return <span key={index} style={{ textTransform: 'none' }}>{part.slice(14, -15).replace(/(^|\s)\S/g, (char) => char.toUpperCase())}</span>;
        if (part.startsWith('**')) return <strong key={index}>{part.slice(2, -2)}</strong>;
        if (part.startsWith('`')) return <code key={index} className="rounded bg-white/[0.06] px-1.5 py-0.5 font-mono text-[0.9em] text-[#FF8A3D]">{part.slice(1, -1)}</code>;
        return part;
    });
}

function CodeBlock({ lang, value }) {
    const [copied, setCopied] = useState(false);
    async function copy() { try { await navigator.clipboard.writeText(value); setCopied(true); setTimeout(() => setCopied(false), 1500); } catch {} }
    return <div className="overflow-hidden rounded-xl border border-white/[0.06] bg-[#060D18]">
        <div className="flex items-center justify-between border-b border-white/[0.06] px-4 py-2"><span className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{lang || 'code'}</span><button type="button" onClick={copy} className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 transition hover:text-white">{copied ? <Check size={13} /> : <Copy size={13} />}{copied ? 'Copié' : 'Copier'}</button></div>
        <pre className="overflow-x-auto p-4 text-[13px] leading-6 text-slate-200"><code>{value}</code></pre>
    </div>;
}

function renderTextBlock(text, style, key) {
    const lines = text.split('\n');
    const output = [];
    let list = null;
    const flushList = () => { if (!list) return; output.push(<ul key={list.key} className="my-2 space-y-1.5">{list.items.map((item, i) => <li key={i} className="flex gap-2 leading-7 text-slate-300"><span className="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[#FF6A00]" /> <span>{inline(item)}</span></li>)}</ul>); list = null; };
    lines.forEach((raw, index) => {
        const line = raw.trimEnd();
        if (/^#{1,3}\s+/.test(line)) { flushList(); const level = (line.match(/^#+/) || [''])[0].length; const value = line.replace(/^#{1,3}\s+/, ''); const Tag = level === 1 ? 'h2' : level === 2 ? 'h3' : 'h4'; output.push(<Tag key={key + '-h-' + index} className={(level === 1 ? 'text-2xl' : level === 2 ? 'text-xl' : 'text-lg') + ' mb-2 mt-4 font-semibold text-white'}>{inline(value)}</Tag>); return; }
        if (/^---+$/.test(line)) { flushList(); output.push(<hr key={key + '-hr-' + index} className="my-5 border-white/[0.08]" />); return; }
        if (/^>\s?/.test(line)) { flushList(); output.push(<blockquote key={key + '-q-' + index} className="my-3 border-l-2 border-[#FF6A00]/50 pl-4 italic text-slate-400">{inline(line.replace(/^>\s?/, ''))}</blockquote>); return; }
        if (/^- \[[ xX]\]\s*/.test(line)) { flushList(); const checked = /^- \[[xX]\]/.test(line); output.push(<label key={key + '-c-' + index} className="flex gap-2 py-1 leading-7 text-slate-300"><input type="checkbox" checked={checked} readOnly className="mt-2 h-4 w-4 rounded border-white/20 bg-[#101A2A] text-[#FF6A00]" /><span className={checked ? 'line-through opacity-50' : ''}>{inline(line.replace(/^- \[[ xX]\]\s*/, ''))}</span></label>); return; }
        if (/^[-*]\s+/.test(line)) { if (!list || list.type !== 'ul') { flushList(); list = { key: key + '-ul-' + index, type: 'ul', items: [] }; } list.items.push(line.replace(/^[-*]\s+/, '')); return; }
        if (/^\d+\.\s+/.test(line)) { flushList(); output.push(<p key={key + '-ol-' + index} className="leading-7 text-slate-300"><span className="mr-2 text-[#FF8A3D]">{line.match(/^\d+/)[0]}.</span>{inline(line.replace(/^\d+\.\s+/, ''))}</p>); return; }
        if (line.trim() === '') { flushList(); return; }
        flushList(); output.push(<p key={key + '-p-' + index} className="whitespace-pre-wrap leading-7 text-slate-300">{inline(line)}</p>);
    });
    flushList();
    return <div key={key}>{output}</div>;
}

export default function MemoContent({ content, formatting }) {
    const rawContent = String(content ?? '');
    const isRichHtml = /<(?:p|div|h[1-6]|strong|b|em|i|u|s|ul|ol|li|blockquote|pre|code|br|hr|figure|img|a)\\b/i.test(rawContent);
    const richHtml = isRichHtml ? sanitizeMemoHtml(rawContent) : null;
    const parts = []; const fence = new RegExp('```([\\w+-]*)\\n([\\s\\S]*?)```', 'g'); let last = 0; let match;
    while ((match = fence.exec(content ?? '')) !== null) { if (match.index > last) parts.push({ type: 'text', value: content.slice(last, match.index) }); parts.push({ type: 'code', lang: match[1], value: match[2].replace(/\n$/, '') }); last = match.index + match[0].length; }
    if (last < (content ?? '').length) parts.push({ type: 'text', value: content.slice(last) });
    const style = safeFormatting(formatting);
    return <div
        className="space-y-4 prose prose-invert max-w-none [&_h2]:mb-2 [&_h2]:mt-4 [&_h2]:text-2xl [&_h2]:font-semibold [&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:text-xl [&_h3]:font-semibold [&_h4]:mb-2 [&_h4]:mt-4 [&_h4]:text-lg [&_h4]:font-semibold [&_p]:leading-7 [&_li]:leading-7 [&_blockquote]:border-l-2 [&_blockquote]:border-[#FF6A00]/50 [&_blockquote]:pl-4 [&_blockquote]:italic"
        style={{ fontFamily: FONT_STACKS[style.fontFamily] ?? FONT_STACKS.Inter, fontSize: style.fontSize, fontWeight: style.fontWeight, textTransform: style.textTransform, textAlign: style.textAlign }}
    >
        {richHtml
            ? <div dangerouslySetInnerHTML={{ __html: richHtml }} />
            : parts.map((part, index) => part.type === 'code' ? <CodeBlock key={index} lang={part.lang} value={part.value} /> : renderTextBlock(part.value, style, index))}
    </div>;
}