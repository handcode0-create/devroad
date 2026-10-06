export const inputClass =
    'w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-field)] px-4 py-3 text-sm text-[var(--dr-text)] outline-none transition placeholder:text-[var(--dr-text-3)] focus:border-[#FF6A00]/40 focus:ring-2 focus:ring-[#FF6A00]/10';

export function Field({ label, htmlFor, error, hint, children }) {
    return (
        <div>
            <label htmlFor={htmlFor} className="mb-1.5 block text-sm font-semibold text-[var(--dr-text)]">
                {label}
            </label>

            {children}

            {hint && !error && <p className="mt-1.5 text-xs text-[var(--dr-text-3)]">{hint}</p>}

            {error && (
                <p role="alert" className="mt-1.5 text-xs font-medium text-[var(--dr-danger)]">
                    {error}
                </p>
            )}
        </div>
    );
}
