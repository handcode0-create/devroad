import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Bookmark } from 'lucide-react';
import { popFavorite } from '@/Components/Memos/memoMotion';

export default function FavoriteButton({ memo, className = '', variant = 'default' }) {
    // Mise à jour optimiste : le marque-page réagit tout de suite, puis
    // revient à l'état du serveur si la requête échoue.
    const [favorite, setFavorite] = useState(Boolean(memo.is_favorite));
    const iconRef = useRef(null);

    useEffect(() => setFavorite(Boolean(memo.is_favorite)), [memo.is_favorite]);

    function toggle(event) {
        event.preventDefault();
        event.stopPropagation();

        const next = !favorite;
        setFavorite(next);
        popFavorite(iconRef.current, next);

        router.patch(`/memos/${memo.id}/favorite`, {}, {
            preserveScroll: true,
            onError: () => setFavorite(!next),
        });
    }

    return (
        <button
            type="button"
            onClick={toggle}
            aria-pressed={favorite}
            aria-label={favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'}
            title={favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'}
            className={variant === 'design'
                // Maquette « Mobile — Fiches » : 40×40, rayon 12, accent si favori, sinon texte tertiaire.
                ? 'relative z-10 -mr-2 -mt-1.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-transparent focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] ' + (favorite ? 'text-[var(--dr-accent)]' : 'text-[var(--dr-text-3)]') + ' ' + className
                : 'relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors hover:bg-white/[0.06] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#FF6A00]/60 ' + (favorite ? 'text-[#FF6A00]' : 'text-slate-500 hover:text-white') + ' ' + className}
        >
            <span ref={iconRef} className="flex">
                {variant === 'design'
                    ? <svg width="19" height="19" viewBox="0 0 24 24" fill={favorite ? 'var(--dr-accent)' : 'none'} stroke="currentColor" strokeWidth="2" strokeLinejoin="round" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z" /></svg>
                    : <Bookmark size={18} aria-hidden="true" className={favorite ? 'fill-[#FF6A00]' : ''} />}
            </span>
        </button>
    );
}
