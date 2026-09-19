import { useState } from 'react';
import { Check, Copy } from 'lucide-react';

// Affiche le contenu d'une fiche : texte + blocs de code entre ```.
// Tout passe par React (échappement automatique) : aucun HTML brut n'est injecté.
function parse(content) {
    const fence = /```([\w+-]*)\n([\s\S]*?)```/g;
    const parts = [];
    let last = 0;
    let match;

    while ((match = fence.exec(content)) !== null) {
        if (match.index > last) {
            parts.push({ type: 'text', value: content.slice(last, match.index) });
        }

        parts.push({ type: 'code', lang: match[1], value: match[2].replace(/\n$/, '') });
        last = match.index + match[0].length;
    }

    if (last < content.length) {
        parts.push({ type: 'text', value: content.slice(last) });
    }

    return parts;
}

function CodeBlock({ lang, value }) {
    const [copied, setCopied] = useState(false);

    async function copy() {
        try {
            await navigator.clipboard.writeText(value);
            setCopied(true);
            setTimeout(() => setCopied(false), 1500);
        } catch {
            // Presse-papiers indisponible (contexte non sécurisé) : on ignore.
        }
    }

    return (
        <div className="overflow-hidden rounded-xl border border-white/[0.06] bg-[#060D18]">
            <div className="flex items-center justify-between border-b border-white/[0.06] px-4 py-2">
                <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {lang || 'code'}
                </span>

                <button
                    type="button"
                    onClick={copy}
                    className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 transition hover:text-white"
                >
                    {copied ? <Check size={13} aria-hidden="true" /> : <Copy size={13} aria-hidden="true" />}
                    {copied ? 'Copié' : 'Copier'}
                </button>
            </div>

            <pre className="overflow-x-auto p-4 text-[13px] leading-6 text-slate-200">
                <code>{value}</code>
            </pre>
        </div>
    );
}

export default function MemoContent({ content }) {
    const parts = parse(content ?? '');

    return (
        <div className="space-y-4">
            {parts.map((part, index) =>
                part.type === 'code' ? (
                    <CodeBlock key={index} lang={part.lang} value={part.value} />
                ) : part.value.trim() === '' ? null : (
                    <p key={index} className="whitespace-pre-wrap text-sm leading-7 text-slate-300">
                        {part.value.trim()}
                    </p>
                ),
            )}
        </div>
    );
}
