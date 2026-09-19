const base =
    'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60';

// Texte sombre sur orange : contraste ≈ 6:1 (le blanc n'atteint que ≈ 2,9:1).
const variants = {
    primary: 'bg-[#FF6A00] text-[#08111F] hover:bg-[#ff781a]',
    secondary:
        'border border-white/[0.08] bg-white/[0.03] text-slate-300 hover:bg-white/[0.06] hover:text-white',
    danger: 'border border-red-500/20 bg-red-500/10 text-red-300 hover:bg-red-500/15',
};

export function buttonClass(variant = 'primary') {
    return `${base} ${variants[variant] ?? variants.primary}`;
}
