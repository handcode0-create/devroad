import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import MemoContent from '@/Components/Memos/MemoContent';
import TagBadge from '@/Components/Memos/TagBadge';
import { buttonClass } from '@/Components/Ui/buttons';

export default function Show({ memo }) {
    function destroy() {
        if (window.confirm('Supprimer cette fiche mémo ? Cette action est définitive.')) {
            router.delete(`/memos/${memo.id}`);
        }
    }

    return (
        <AppLayout>
            <Head title={memo.title} />

            <div className="mx-auto max-w-3xl">
                <PageHeader
                    title={memo.title}
                    backHref="/memos"
                    backLabel="Fiches mémo"
                    actions={<FavoriteButton memo={memo} />}
                />

                {memo.tags?.length > 0 && (
                    <ul className="-mt-2 mb-5 flex flex-wrap gap-2" aria-label="Tags">
                        {memo.tags.map((tag) => (
                            <li key={tag.id}>
                                <Link href={`/memos?tag=${tag.slug}`}>
                                    <TagBadge name={tag.name} />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}

                <article className="rounded-2xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-6">
                    <MemoContent content={memo.content} />
                </article>

                <div className="mt-6 flex flex-wrap items-center gap-3">
                    <Link href={`/memos/${memo.id}/edit`} className={buttonClass('primary')}>
                        <Pencil size={16} aria-hidden="true" />
                        Modifier
                    </Link>

                    <button type="button" onClick={destroy} className={buttonClass('danger')}>
                        <Trash2 size={16} aria-hidden="true" />
                        Supprimer
                    </button>
                </div>
            </div>
        </AppLayout>
    );
}
