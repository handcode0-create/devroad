import { router } from '@inertiajs/react';
import { Bookmark } from 'lucide-react';

export default function FavoriteButton({ memo }) {
    function toggle(event) {
        event.preventDefault();
        event.stopPropagation();

        router.patch(`/memos/${memo.id}/favorite`, {}, { preserveScroll: true });
    }

    return (
        <button
            type="button"
            onClick={toggle}
            aria-pressed={memo.is_favorite}
            aria-label={memo.is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'}
            className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-500 transition hover:bg-white/[0.05] hover:text-white"
        >
            <Bookmark
                size={18}
                aria-hidden="true"
                className={memo.is_favorite ? 'fill-[#FF6A00] text-[#FF6A00]' : ''}
            />
        </button>
    );
}
