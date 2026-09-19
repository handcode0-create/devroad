import { Link } from '@inertiajs/react';

// Les libellés Laravel contiennent des entités HTML (« &laquo; Previous ») :
// on les remplace par de simples flèches, sans dangerouslySetInnerHTML.
function labelOf(label) {
    if (/previous/i.test(label)) return '‹';
    if (/next/i.test(label)) return '›';

    return label;
}

export default function Pagination({ links }) {
    if (!links || links.length <= 3) {
        return null;
    }

    return (
        <nav aria-label="Pagination" className="mt-6 flex flex-wrap items-center justify-center gap-1.5">
            {links.map((link, index) => {
                const label = labelOf(link.label);
                const base = 'flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-sm font-semibold transition';

                if (!link.url) {
                    return (
                        <span key={index} className={`${base} text-slate-600`}>
                            {label}
                        </span>
                    );
                }

                return (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        aria-current={link.active ? 'page' : undefined}
                        className={`${base} ${
                            link.active
                                ? 'bg-[#FF6A00] text-[#08111F]'
                                : 'border border-white/[0.07] bg-[#0D1725] text-slate-400 hover:text-white'
                        }`}
                    >
                        {label}
                    </Link>
                );
            })}
        </nav>
    );
}
