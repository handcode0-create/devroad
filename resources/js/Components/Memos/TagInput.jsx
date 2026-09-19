import { useState } from 'react';
import { X } from 'lucide-react';

// Saisie de tags : Entrée ou virgule pour valider, Retour arrière pour retirer le dernier.
// On envoie des NOMS au backend, jamais des identifiants.
export default function TagInput({ id, value, onChange, max = 10 }) {
    const [draft, setDraft] = useState('');
    const full = value.length >= max;

    function add(raw) {
        const name = raw.replace(/,/g, '').trim().slice(0, 50);

        if (!name) {
            setDraft('');
            return;
        }

        const exists = value.some((tag) => tag.toLowerCase() === name.toLowerCase());

        if (!exists && !full) {
            onChange([...value, name]);
        }

        setDraft('');
    }

    function onKeyDown(event) {
        if (event.key === 'Enter' || event.key === ',') {
            event.preventDefault();
            add(draft);
        } else if (event.key === 'Backspace' && draft === '' && value.length > 0) {
            onChange(value.slice(0, -1));
        }
    }

    return (
        <div>
            <div className="flex flex-wrap items-center gap-2 rounded-xl border border-white/[0.07] bg-[#101A2A] px-3 py-2.5 transition focus-within:border-[#FF6A00]/40 focus-within:ring-2 focus-within:ring-[#FF6A00]/10">
                {value.map((tag) => (
                    <span
                        key={tag}
                        className="inline-flex items-center gap-1 rounded-full bg-[#FF6A00]/10 py-1 pl-2.5 pr-1.5 text-xs font-medium text-[#FF8A3D]"
                    >
                        {tag}
                        <button
                            type="button"
                            onClick={() => onChange(value.filter((t) => t !== tag))}
                            aria-label={`Retirer le tag ${tag}`}
                            className="rounded-full p-0.5 transition hover:bg-white/10"
                        >
                            <X size={12} aria-hidden="true" />
                        </button>
                    </span>
                ))}

                <input
                    id={id}
                    type="text"
                    value={draft}
                    onChange={(e) => setDraft(e.target.value)}
                    onKeyDown={onKeyDown}
                    onBlur={() => add(draft)}
                    disabled={full}
                    maxLength={50}
                    placeholder={value.length === 0 ? 'Laravel, Git, React...' : ''}
                    className="min-w-[8rem] flex-1 border-0 bg-transparent p-0 text-sm text-white outline-none placeholder:text-slate-600 focus:ring-0"
                />
            </div>

            <p className="mt-1.5 text-xs text-slate-500">
                Entrée ou virgule pour ajouter · {value.length}/{max} tags
            </p>
        </div>
    );
}
