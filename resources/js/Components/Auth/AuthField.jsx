import { useState } from 'react';
import { AlertCircle, Eye, EyeOff } from 'lucide-react';

// Même famille que inputClass du design système (fond #101A2A, bord blanc léger,
// focus orange), mais à 48 px de haut et 16 px de texte : pas de zoom sur iOS,
// zone tactile confortable.
export const authInputClass =
    'block h-12 w-full rounded-xl border bg-[#101A2A] px-4 text-base text-white outline-none transition placeholder:text-[#7D8AA0] hover:border-[#7B8CA6] focus:ring-2';

// Bord à ≈ 3,4:1 sur le fond de page (WCAG 1.4.11, contour d'un champ de saisie).
const OK = 'border-[#5A6A82] focus:border-[#FF6A00] focus:ring-[#FF6A00]/25';
const KO = 'border-red-300/70 focus:border-red-300 focus:ring-red-300/25';

export default function AuthField({
    id,
    label,
    type = 'text',
    value,
    onChange,
    error,
    hint,
    autoComplete,
    inputMode,
    placeholder,
    autoFocus = false,
    required = true,
    name = id,
}) {
    const [visible, setVisible] = useState(false);
    const isPassword = type === 'password';
    const describedBy = error ? `${id}-error` : hint ? `${id}-hint` : undefined;

    return (
        <div>
            <label htmlFor={id} className="mb-1.5 block text-sm font-semibold text-slate-200">
                {label}
            </label>

            <div className="relative">
                <input
                    id={id}
                    name={name}
                    type={isPassword && visible ? 'text' : type}
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    autoComplete={autoComplete}
                    inputMode={inputMode}
                    placeholder={placeholder}
                    autoFocus={autoFocus}
                    required={required}
                    autoCapitalize="none"
                    spellCheck={false}
                    aria-invalid={error ? 'true' : undefined}
                    aria-describedby={describedBy}
                    className={`${authInputClass} ${error ? KO : OK} ${isPassword ? 'pr-12' : ''}`}
                />

                {isPassword && (
                    <button
                        type="button"
                        onClick={() => setVisible((current) => !current)}
                        aria-label={visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                        aria-pressed={visible}
                        className="absolute right-1 top-1 flex h-10 w-10 items-center justify-center rounded-lg text-slate-400 transition hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6A00]/60"
                    >
                        {visible ? <EyeOff size={18} aria-hidden="true" /> : <Eye size={18} aria-hidden="true" />}
                    </button>
                )}
            </div>

            {error ? (
                <p
                    id={`${id}-error`}
                    role="alert"
                    className="mt-1.5 flex items-start gap-1.5 text-sm font-medium text-red-300"
                >
                    <AlertCircle size={16} className="mt-0.5 shrink-0" aria-hidden="true" />
                    {error}
                </p>
            ) : (
                hint && (
                    <p id={`${id}-hint`} className="mt-1.5 text-sm text-slate-400">
                        {hint}
                    </p>
                )
            )}
        </div>
    );
}
