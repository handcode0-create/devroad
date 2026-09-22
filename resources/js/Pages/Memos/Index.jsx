import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { Bookmark, ChevronDown, FileText, Folder, LayoutGrid, List, Plus, Search, Sparkles, X } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import Pagination from '@/Components/Ui/Pagination';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import TagBadge from '@/Components/Memos/TagBadge';
import { buttonClass } from '@/Components/Ui/buttons';

function listUrl({ tag, favorites, recent, q }) {
    const params = new URLSearchParams();
    if (tag) params.set('tag', tag);
    if (favorites) params.set('favorites', '1');
    if (recent) params.set('recent', '1');
    if (q) params.set('q', q);
    const query = params.toString();
    return query ? '/memos?' + query : '/memos';
}

export default function Index({ memos, tags = [], filters = {}, counts = {} }) {
    const items = memos?.data ?? [];
    const [query, setQuery] = useState(filters.q ?? '');
    const [mobileLibraryOpen, setMobileLibraryOpen] = useState(false);
    const [view, setView] = useState('list');

    useEffect(() => { setQuery(filters.q ?? ''); }, [filters.q]);

    function submitSearch(event) {
        event.preventDefault();
        router.get('/memos', {
            q: query.trim() || undefined,
            tag: filters.tag || undefined,
            favorites: filters.favorites ? 1 : undefined,
            recent: filters.recent ? 1 : undefined,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }

    function clearSearch() {
        setQuery('');
        router.get('/memos', {
            tag: filters.tag || undefined,
            favorites: filters.favorites ? 1 : undefined,
            recent: filters.recent ? 1 : undefined,
        }, { preserveState: true, replace: true });
    }

    const activeFolder = useMemo(() => {
        if (filters.favorites) return 'Favoris';
        if (filters.recent) return 'Récents';
        if (filters.tag) return tags.find((tag) => tag.slug === filters.tag)?.name ?? 'Collection';
        return 'Toutes les fiches';
    }, [filters, tags]);

    const hasFilter = Boolean(filters.tag || filters.favorites || filters.recent || filters.q);

    return (
        <AppLayout>
            <Head title="Fiches mémo" />
            <div className="space-y-5">
                <PageHeader title="Fiches mémo" subtitle="Un espace de rangement façon Notion pour organiser tes connaissances, commandes et astuces." actions={
                    <Link href="/memos/create" className={buttonClass('primary')}>
                        <Plus size={16} aria-hidden="true" />
                        <span className="hidden sm:inline">Nouvelle fiche</span>
                        <span className="sm:hidden">Nouvelle</span>
                    </Link>
                } />

                <div className="flex items-center gap-2 lg:hidden">
                    <button type="button" onClick={() => setMobileLibraryOpen((open) => !open)} className="inline-flex items-center gap-2 rounded-xl border border-white/[0.07] bg-[#0D1725] px-3 py-2 text-xs font-semibold text-slate-300">
                        <Folder size={15} className="text-[#FF8A3D]" /> Rangement
                        <ChevronDown size={14} className={mobileLibraryOpen ? 'rotate-180 transition' : 'transition'} />
                    </button>
                    <span className="truncate text-xs text-slate-500">{activeFolder}</span>
                </div>

                <div className="grid gap-5 lg:grid-cols-[230px_minmax(0,1fr)]">
                    <aside className={(mobileLibraryOpen ? 'block' : 'hidden') + ' lg:block'}>
                        <div className="sticky top-5 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-3">
                            <div className="mb-3 flex items-center gap-2 px-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FF6A00]/10 text-[#FF8A3D]"><Folder size={16} /></div>
                                <div className="min-w-0"><p className="text-xs font-semibold text-white">Mon espace</p><p className="text-[10px] text-slate-500">{counts.total ?? 0} fiches</p></div>
                            </div>
                            <div className="space-y-1">
                                <NavItem href="/memos" active={!hasFilter} icon={FileText} label="Toutes les fiches" count={counts.total} />
                                <NavItem href={listUrl({ favorites: true })} active={Boolean(filters.favorites)} icon={Bookmark} label="Favoris" count={counts.favorites} />
                                <NavItem href={listUrl({ recent: true })} active={Boolean(filters.recent)} icon={Sparkles} label="Récents" count={counts.recent} />
                            </div>
                            {tags.length > 0 && <div className="mt-5 border-t border-white/[0.06] pt-4">
                                <div className="mb-2 flex items-center justify-between px-2"><span className="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-600">Collections</span><span className="text-[10px] text-slate-600">{tags.length}</span></div>
                                <div className="space-y-1">
                                    {tags.map((tag) => <NavItem key={tag.id} href={listUrl({ tag: filters.tag === tag.slug ? null : tag.slug, q: filters.q })} active={filters.tag === tag.slug} icon={Folder} label={tag.name} count={tag.memos_count} />)}
                                </div>
                            </div>}
                        </div>
                    </aside>

                    <section className="min-w-0">
                        <div className="mb-4 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-3">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <form onSubmit={submitSearch} className="relative min-w-0 flex-1">
                                    <Search size={16} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-600" />
                                    <input type="search" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Rechercher dans tes fiches..." className="h-10 w-full rounded-xl border border-white/[0.06] bg-[#08111F] pl-9 pr-10 text-sm text-white outline-none placeholder:text-slate-600 focus:border-[#FF6A00]/40" />
                                    {query && <button type="button" onClick={clearSearch} aria-label="Effacer la recherche" className="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 hover:bg-white/[0.05] hover:text-white"><X size={14} /></button>}
                                </form>
                                <div className="flex items-center justify-between gap-2"><div className="flex rounded-xl border border-white/[0.06] bg-[#08111F] p-1">
                                    <ViewButton active={view === 'list'} onClick={() => setView('list')} icon={List} label="Liste" />
                                    <ViewButton active={view === 'grid'} onClick={() => setView('grid')} icon={LayoutGrid} label="Grille" />
                                </div></div>
                            </div>
                        </div>
                        <div className="mb-3 flex items-center justify-between gap-3">
                            <div className="min-w-0"><h2 className="truncate text-sm font-semibold text-white">{activeFolder}</h2><p className="text-xs text-slate-600">{memos?.total ?? items.length} {memos?.total === 1 ? 'fiche' : 'fiches'}{filters.q ? ' pour « ' + filters.q + ' »' : ''}</p></div>
                            {hasFilter && <Link href="/memos" className="shrink-0 text-xs font-semibold text-[#FF8A3D] hover:text-[#FFB078]">Réinitialiser</Link>}
                        </div>
                        {items.length > 0 ? <><ul className={view === 'grid' ? 'grid gap-3 sm:grid-cols-2' : 'space-y-3'}>{items.map((memo) => <li key={memo.id}><MemoCard memo={memo} grid={view === 'grid'} /></li>)}</ul><Pagination links={memos.links} /></> : <EmptyState filtered={hasFilter} />}
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}

function NavItem({ href, active, icon: Icon, label, count }) {
    return <Link href={href} preserveScroll className={'group flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-xs font-medium transition ' + (active ? 'bg-[#FF6A00]/10 text-[#FF8A3D]' : 'text-slate-500 hover:bg-white/[0.035] hover:text-slate-200')}>
        <Icon size={15} className={active ? 'text-[#FF8A3D]' : 'text-slate-600 group-hover:text-slate-400'} /><span className="min-w-0 flex-1 truncate">{label}</span>{typeof count === 'number' && <span className={active ? 'text-[10px] text-[#FF8A3D]' : 'text-[10px] text-slate-700'}>{count}</span>}
    </Link>;
}

function ViewButton({ active, onClick, icon: Icon, label }) {
    return <button type="button" onClick={onClick} aria-label={label} aria-pressed={active} className={'flex h-8 w-8 items-center justify-center rounded-lg transition ' + (active ? 'bg-white/[0.07] text-white' : 'text-slate-600 hover:text-slate-300')}><Icon size={15} /></button>;
}

function MemoCard({ memo, grid }) {
    return <article className={'group flex items-start gap-2 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4 transition hover:border-[#FF6A00]/20 hover:bg-[#101B2C] ' + (grid ? 'min-h-[175px] flex-col' : '')}>
        <Link href={'/memos/' + memo.id} className="min-w-0 flex-1">
            <div className="mb-2 flex items-center gap-2"><span className="h-1.5 w-1.5 rounded-full bg-[#FF6A00]" /><span className="text-[10px] font-medium uppercase tracking-[0.12em] text-slate-600">{memo.is_favorite ? 'Favori' : 'Fiche'}</span></div>
            <h3 className="truncate text-sm font-semibold text-white group-hover:text-[#FFB078]">{memo.title}</h3>
            <p className="mt-1 line-clamp-3 text-xs leading-5 text-slate-500">{memo.excerpt}</p>
            {memo.tags?.length > 0 && <div className="mt-3 flex flex-wrap gap-1.5">{memo.tags.map((tag) => <TagBadge key={tag.id} name={tag.name} />)}</div>}
        </Link>
        <FavoriteButton memo={memo} />
    </article>;
}

function EmptyState({ filtered }) {
    return <div className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-8 text-center">
        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FF6A00]/10 text-[#FF8A3D]"><FileText size={25} aria-hidden="true" /></div>
        <h2 className="mt-4 text-base font-semibold text-white">{filtered ? 'Aucune fiche ne correspond' : 'Aucune fiche mémo'}</h2>
        <p className="mx-auto mt-1 max-w-sm text-sm leading-6 text-slate-500">{filtered ? 'Essaie une autre collection ou une autre recherche.' : 'Crée ta première fiche pour construire ton espace de connaissances.'}</p>
        <Link href={filtered ? '/memos' : '/memos/create'} className={buttonClass('primary') + ' mt-5'}>{filtered ? 'Voir toutes les fiches' : 'Créer une fiche'}</Link>
    </div>;
}