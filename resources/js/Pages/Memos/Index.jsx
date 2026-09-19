import { Head, Link } from '@inertiajs/react';
import { FileText, Plus } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import Pagination from '@/Components/Ui/Pagination';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import TagBadge from '@/Components/Memos/TagBadge';
import { buttonClass } from '@/Components/Ui/buttons';

// Construit l'URL de la liste avec les filtres voulus.
function listUrl({ tag, favorites }) {
    const params = new URLSearchParams();

    if (tag) params.set('tag', tag);
    if (favorites) params.set('favorites', '1');

    const query = params.toString();

    return query ? `/memos?${query}` : '/memos';
}

export default function Index({ memos, tags = [], filters = {} }) {
    const items = memos?.data ?? [];
    const hasFilter = Boolean(filters.tag || filters.favorites);

    return (
        <AppLayout>
            <Head title="Fiches mémo" />

            <PageHeader
                title="Fiches mémo"
                subtitle="Des résumés clairs pour ne plus oublier l'essentiel."
                actions={
                    <Link href="/memos/create" className={buttonClass('primary')}>
                        <Plus size={16} aria-hidden="true" />
                        <span className="hidden sm:inline">Nouvelle fiche</span>
                        <span className="sm:hidden">Nouvelle</span>
                    </Link>
                }
            />

            {/* Filtres */}
            <nav aria-label="Filtres" className="-mx-4 mb-5 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
                <Chip href="/memos" active={!hasFilter}>
                    Toutes
                </Chip>

                <Chip
                    href={listUrl({ tag: filters.tag, favorites: !filters.favorites })}
                    active={Boolean(filters.favorites)}
                >
                    Favoris
                </Chip>

                {tags.map((tag) => (
                    <Chip
                        key={tag.id}
                        href={listUrl({
                            tag: filters.tag === tag.slug ? null : tag.slug,
                            favorites: filters.favorites,
                        })}
                        active={filters.tag === tag.slug}
                    >
                        {tag.name}
                    </Chip>
                ))}
            </nav>

            {items.length > 0 ? (
                <>
                    <ul className="space-y-3">
                        {items.map((memo) => (
                            <li key={memo.id}>
                                <MemoCard memo={memo} />
                            </li>
                        ))}
                    </ul>

                    <Pagination links={memos.links} />
                </>
            ) : (
                <EmptyState filtered={hasFilter} />
            )}
        </AppLayout>
    );
}

function Chip({ href, active, children }) {
    return (
        <Link
            href={href}
            preserveScroll
            aria-current={active ? 'true' : undefined}
            className={`shrink-0 rounded-full px-4 py-2 text-xs font-semibold transition ${
                active
                    ? 'bg-[#FF6A00] text-[#08111F]'
                    : 'border border-white/[0.07] bg-[#0D1725] text-slate-400 hover:text-white'
            }`}
        >
            {children}
        </Link>
    );
}

function MemoCard({ memo }) {
    return (
        <article className="flex items-start gap-2 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4 transition hover:border-white/[0.11] hover:bg-[#101B2C]">
            <Link href={`/memos/${memo.id}`} className="min-w-0 flex-1">
                <h2 className="truncate text-sm font-semibold text-white">{memo.title}</h2>

                <p className="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{memo.excerpt}</p>

                {memo.tags?.length > 0 && (
                    <div className="mt-3 flex flex-wrap gap-1.5">
                        {memo.tags.map((tag) => (
                            <TagBadge key={tag.id} name={tag.name} />
                        ))}
                    </div>
                )}
            </Link>

            <FavoriteButton memo={memo} />
        </article>
    );
}

function EmptyState({ filtered }) {
    return (
        <div className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-8 text-center">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                <FileText size={25} aria-hidden="true" />
            </div>

            <h2 className="mt-4 text-base font-semibold text-white">
                {filtered ? 'Aucune fiche ne correspond' : 'Aucune fiche mémo'}
            </h2>

            <p className="mx-auto mt-1 max-w-sm text-sm leading-6 text-slate-500">
                {filtered
                    ? 'Essaie un autre filtre, ou affiche toutes tes fiches.'
                    : 'Crée ta première fiche pour garder une commande, une notion ou une astuce sous la main.'}
            </p>

            <Link
                href={filtered ? '/memos' : '/memos/create'}
                className={`${buttonClass('primary')} mt-5`}
            >
                {filtered ? 'Voir toutes les fiches' : 'Créer une fiche'}
            </Link>
        </div>
    );
}
