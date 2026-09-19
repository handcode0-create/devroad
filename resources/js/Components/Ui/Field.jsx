export const inputClass =
    'w-full rounded-xl border border-white/[0.07] bg-[#101A2A] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-[#FF6A00]/40 focus:ring-2 focus:ring-[#FF6A00]/10';

export function Field({ label, htmlFor, error, hint, children }) {
    return (
        <div>
            <label htmlFor={htmlFor} className="mb-1.5 block text-sm font-semibold text-white">
                {label}
            </label>

            {children}

            {hint && !error && <p className="mt-1.5 text-xs text-slate-500">{hint}</p>}

            {error && (
                <p role="alert" className="mt-1.5 text-xs font-medium text-red-400">
                    {error}
                </p>
            )}
        </div>
    );
}
