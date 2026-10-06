const base =
    'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]';

// Rôles de thème uniquement : chaque thème (Nuit, Minuit, Ardoise, Clair, Sable) les redéfinit.
const variants = {
    primary: 'bg-[var(--dr-accent)] font-bold text-[var(--dr-ink)] hover:brightness-110',
    secondary: 'border border-[var(--dr-border-2)] bg-[var(--dr-surface)] text-[var(--dr-text)] hover:bg-[var(--dr-field)]',
    danger: 'text-[var(--dr-danger)] hover:bg-[var(--dr-field)]',
};

export function buttonClass(variant = 'primary') {
    return `${base} ${variants[variant] ?? variants.primary}`;
}
