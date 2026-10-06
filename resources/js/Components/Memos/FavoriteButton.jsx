import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Bookmark } from 'lucide-react';
import { popFavorite } from '@/Components/Memos/memoMotion';

export default function FavoriteButton({ memo, className = '' }) {
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
            className={'relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors hover:bg-white/[0.06] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#FF6A00]/60 ' + (favorite ? 'text-[#FF6A00]' : 'text-slate-500 hover:text-white') + ' ' + className}
        >
            <span ref={iconRef} className="flex">
                <Bookmark size={18} aria-hidden="true" className={favorite ? 'fill-[#FF6A00]' : ''} />
            </span>
        </button>
    );
}
