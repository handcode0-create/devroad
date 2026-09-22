import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { Bookmark, ChevronDown, FileText, Folder, FolderPlus, LayoutGrid, List, Plus, Search, Sparkles, Trash2, X } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import Pagination from '@/Components/Ui/Pagination';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import TagBadge from '@/Components/Memos/TagBadge';
import { buttonClass } from '@/Components/Ui/buttons';

function listUrl({ tag, favorites, recent, q, folder }) {
    const params = new URLSearchParams();
    if (tag) params.set('tag', tag);
    if (favorites) params.set('favorites', '1');
    if (recent) params.set('recent', '1');
    if (q) params.set('q', q);
    if (folder) params.set('folder', folder);
    const query = params.toString();
    return query ? '/memos?' + query : '/memos';
}

export default function Index({ memos, tags = [], folders = [], filters = {}, counts = {} }) {
    const items = memos?.data ?? [];
    const [query, setQuery] = useState(filters.q ?? '');
    const [mobileLibraryOpen, setMobileLibraryOpen] = useState(false);
    const [view, setView] = useState(() => { try { return localStorage.getItem('devroad:memos:view') || 'list'; } catch { return 'list'; } });

    useEffect(() => { setQuery(filters.q ?? ''); }, [filters.q]);
    useEffect(() => { try { localStorage.setItem('devroad:memos:view', view); } catch {} }, [view]);

    function submitSearch(event) {
        event.preventDefault();
        router.get('/memos', {
            q: query.trim() || undefined,
            tag: filters.tag || undefined,
            favorites: filters.favorites ? 1 : undefined,
            recent: filters.recent ? 1 : undefined,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }

    function createFolder(parentId = null) {
        const name = window.prompt(parentId ? 'Nom du sous-dossier' : 'Nom du dossier');
        if (!name?.trim()) return;
        router.post('/memo-folders', { name: name.trim(), parent_id: parentId }, { preserveScroll: true });
    }

    function deleteFolder(folder) {
        if (!window.confirm('Supprimer le dossier « ' + folder.name + ' » ? Les fiches resteront conservées.')) return;
        router.delete('/memo-folders/' + folder.id, { preserveScroll: true });
    }

    // --- Glisser-déposer -------------------------------------------------
    // dragged : { type: 'folder' | 'memo', id, parentId } — parentId sert à
    // ne réordonner qu'entre dossiers d'un même niveau (mêmes « frères »).
    const [dragged, setDragged] = useState(null);
    const [dropTarget, setDropTarget] = useState(null); // id de dossier survolé (ou 'root')

    function startDragFolder(folder) {
        setDragged({ type: 'folder', id: folder.id, parentId: folder.parent_id ?? null });
    }

    function startDragMemo(memo) {
        setDragged({ type: 'memo', id: memo.id });
    }

    function endDrag() {
        setDragged(null);
        setDropTarget(null);
    }

    // Dépose SUR un dossier : une fiche s'y range, un dossier s'y imbrique.
    function dropOnFolder(folder) {
        if (!dragged) return;

        if (dragged.type === 'memo') {
            router.patch('/memos/' + dragged.id + '/move', { folder_id: folder.id }, { preserveScroll: true, preserveState: true });
        } else if (dragged.type === 'folder' && dragged.id !== folder.id) {
            router.patch('/memo-folders/' + dragged.id, { parent_id: folder.id }, { preserveScroll: true, preserveState: true });
        }

        endDrag();
    }

    // Dépose sur l'en-tête « Dossiers » : remonte un dossier à la racine,
    // ou retire une fiche de son dossier.
    function dropOnRoot() {
        if (!dragged) return;

        if (dragged.type === 'memo') {
            router.patch('/memos/' + dragged.id + '/move', { folder_id: null }, { preserveScroll: true, preserveState: true });
        } else if (dragged.type === 'folder' && dragged.parentId !== null) {
            router.patch('/memo-folders/' + dragged.id, { parent_id: null }, { preserveScroll: true, preserveState: true });
        }

        endDrag();
    }

    // Réordonne un dossier parmi ses frères (même parent), à la position de « target ».
    function reorderFolder(target) {
        if (!dragged || dragged.type !== 'folder') return;
        if (dragged.id === target.id) return;
        if ((dragged.parentId ?? null) !== (target.parent_id ?? null)) return; // niveaux différents : dropOnFolder gère l'imbrication

        const parentId = target.parent_id ?? null;
        const siblings = folders.filter((folder) => (folder.parent_id ?? null) === parentId).sort((a, b) => a.position - b.position);
        const ids = siblings.map((folder) => folder.id).filter((id) => id !== dragged.id);
        const targetIndex = ids.indexOf(target.id);
        ids.splice(targetIndex, 0, dragged.id);

        router.patch('/memo-folders/reorder', { parent_id: parentId, ordered_ids: ids }, { preserveScroll: true, preserveState: true });
        endDrag();
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

    const hasFilter = Boolean(filters.tag || filters.favorites || filters.recent || filters.q || filters.folder);

    return (
        <AppLayout>
            <Head title="Fiches mémo" />
            <div className="space-y-5">
                <PageHeader title="Fiches mémo" subtitle="Un espace de rangement façon Notion pour organiser tes connaissances, commandes et astuces." actions={
                    <Link href={filters.folder ? '/memos/create?folder=' + filters.folder : '/memos/create'} className={buttonClass('primary')}>
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
                            <div className="mt-5 border-t border-white/[0.06] pt-4">
                                <div
                                    onDragOver={(event) => { if (dragged) { event.preventDefault(); setDropTarget('root'); } }}
                                    onDragLeave={() => setDropTarget((current) => (current === 'root' ? null : current))}
                                    onDrop={(event) => { event.preventDefault(); dropOnRoot(); }}
                                    className={'mb-2 flex items-center justify-between rounded-lg px-2 py-1 transition ' + (dropTarget === 'root' ? 'bg-[#FF6A00]/10 ring-1 ring-[#FF6A00]/30' : '')}
                                >
                                    <span className="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-600">Dossiers</span>
                                    <button type="button" onClick={() => createFolder()} className="text-slate-600 transition hover:text-[#FF8A3D]" title="Nouveau dossier"><FolderPlus size={14} /></button>
                                </div>
                                <div className="space-y-1">
                                    {buildFolderTree(folders).map((folder) => <FolderNavItem key={folder.id} folder={folder} active={Number(filters.folder) === folder.id} onCreateChild={createFolder} onDelete={deleteFolder} isDragging={dragged?.type === 'folder' && dragged.id === folder.id} isDropTarget={dropTarget === folder.id} onDragStartFolder={startDragFolder} onDragEnd={endDrag} onDragOverTarget={() => setDropTarget(folder.id)} onDropReorder={reorderFolder} onDropNest={dropOnFolder} canDrop={Boolean(dragged)} />)}
                                    {folders.length === 0 && <button type="button" onClick={() => createFolder()} className="flex w-full items-center gap-2 rounded-xl px-2.5 py-2 text-left text-xs text-slate-600 hover:bg-white/[0.035] hover:text-slate-300"><FolderPlus size={14} />Créer ton premier dossier</button>}
                                </div>
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
                                    <input type="search" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Rechercher dans tes fiches..." className="h-10 w-full rounded-xl border border-white/[0.06] bg-[#08111F] pl-9 pr-10 text-sm text-white outline-none transition-[border-color,box-shadow] duration-200 placeholder:text-slate-600 focus:border-[#FF6A00]/40 focus:ring-2 focus:ring-[#FF6A00]/5" />
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
                        {items.length > 0 ? <><ul className={view === 'grid' ? 'grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3' : 'space-y-2.5'}>{items.map((memo) => <li key={memo.id}><MemoCard memo={memo} grid={view === 'grid'} onDragStart={() => startDragMemo(memo)} onDragEnd={endDrag} /></li>)}</ul><Pagination links={memos.links} /></> : <EmptyState filtered={hasFilter} />}
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

function buildFolderTree(folders) {
    const byParent = new Map();
    folders.forEach((folder) => {
        const key = folder.parent_id ?? 0;
        if (!byParent.has(key)) byParent.set(key, []);
        byParent.get(key).push(folder);
    });
    const walk = (parentId, depth = 0) => (byParent.get(parentId) ?? []).flatMap((folder) => [{ ...folder, depth }, ...walk(folder.id, depth + 1)]);
    return walk(0);
}

function FolderNavItem({ folder, active, onCreateChild, onDelete, isDragging, isDropTarget, onDragStartFolder, onDragEnd, onDragOverTarget, onDropReorder, onDropNest, canDrop }) {
    // Survol dans la moitié basse de la ligne = « insérer après » (réordonner) ;
    // reste de la ligne = « déposer dedans » (imbriquer comme sous-dossier).
    const [nestHover, setNestHover] = useState(false);

    function onDragOver(event) {
        if (!canDrop) return;
        event.preventDefault();
        onDragOverTarget();
        const rect = event.currentTarget.getBoundingClientRect();
        setNestHover(event.clientY - rect.top < rect.height * 0.7);
    }

    function onDrop(event) {
        event.preventDefault();
        if (nestHover) onDropNest(folder); else onDropReorder(folder);
        setNestHover(false);
    }

    return <div>
        <div
            draggable
            onDragStart={(event) => { event.dataTransfer.effectAllowed = 'move'; onDragStartFolder(folder); }}
            onDragEnd={onDragEnd}
            onDragOver={onDragOver}
            onDragLeave={() => setNestHover(false)}
            onDrop={onDrop}
            className={
                'group flex items-center gap-1 rounded-xl pr-1 transition ' +
                (active ? 'bg-[#FF6A00]/10' : 'hover:bg-white/[0.035]') +
                (isDragging ? ' opacity-40' : '') +
                (isDropTarget ? (nestHover ? ' bg-[#FF6A00]/10 ring-1 ring-[#FF6A00]/40' : ' border-t-2 border-[#FF6A00]') : '')
            }
        >
            <Link href={listUrl({ folder: active ? null : folder.id })} className={'flex min-w-0 flex-1 items-center gap-2.5 rounded-xl px-2.5 py-2 text-xs font-medium ' + (active ? 'text-[#FF8A3D]' : 'text-slate-500 hover:text-slate-200')} style={{ paddingLeft: 10 + (folder.depth ?? 0) * 14 }}>
                <Folder size={14} className={active ? 'text-[#FF8A3D]' : 'text-slate-600'} />
                <span className="min-w-0 flex-1 truncate">{folder.name}</span>
                {typeof folder.memos_count === 'number' && <span className="text-[10px] text-slate-700">{folder.memos_count}</span>}
            </Link>
            <button type="button" onClick={() => onCreateChild(folder.id)} className="hidden h-7 w-7 items-center justify-center rounded-lg text-slate-700 hover:bg-white/[0.05] hover:text-[#FF8A3D] group-hover:flex" title="Créer un sous-dossier"><Plus size={12} /></button>
            <button type="button" onClick={() => onDelete(folder)} className="hidden h-7 w-7 items-center justify-center rounded-lg text-slate-700 hover:bg-white/[0.05] hover:text-red-300 group-hover:flex" title="Supprimer le dossier"><Trash2 size={12} /></button>
        </div>
    </div>;
}

function ViewButton({ active, onClick, icon: Icon, label }) {
    return <button type="button" onClick={onClick} aria-label={label} aria-pressed={active} className={'flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-200 ' + (active ? 'bg-white/[0.07] text-white' : 'text-slate-600 hover:text-slate-300')}><Icon size={15} /></button>;
}

function MemoCard({ memo, grid, onDragStart, onDragEnd }) {
    return <article
        draggable
        onDragStart={(event) => { event.dataTransfer.effectAllowed = 'move'; onDragStart(); }}
        onDragEnd={onDragEnd}
        className={'group relative overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0D1725] transition-all duration-200 ease-out hover:-translate-y-px hover:border-[#FF6A00]/20 hover:bg-[#101B2C] cursor-grab active:cursor-grabbing ' + (grid ? 'flex min-h-[230px] flex-col p-4' : 'flex items-center gap-4 px-4 py-3')}
    >
        <Link href={'/memos/' + memo.id} className={'min-w-0 flex-1 ' + (grid ? 'flex flex-col' : 'flex items-center gap-4')}>
            <div className={grid ? 'mb-3 flex items-center justify-between gap-2' : 'flex w-[150px] shrink-0 items-center gap-2'}>
                <span className="flex items-center gap-2 text-[10px] font-medium uppercase tracking-[0.12em] text-slate-600"><span className="h-1.5 w-1.5 rounded-full bg-[#FF6A00]" />{memo.is_favorite ? 'Favori' : 'Fiche'}</span>
                {grid && <span className="text-[10px] text-slate-600">{memo.updated_at ? new Date(memo.updated_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : ''}</span>}
            </div>
            <div className={grid ? 'min-w-0' : 'min-w-0 flex-1'}>
                <h3 className={'font-semibold text-white transition-colors duration-200 group-hover:text-[#FFB078] ' + (grid ? 'line-clamp-2 text-base leading-6' : 'truncate text-sm')}>{memo.title}</h3>
                <p className={'mt-1 text-xs leading-5 text-slate-500 ' + (grid ? 'line-clamp-4' : 'line-clamp-1')}>{memo.excerpt}</p>
                {memo.tags?.length > 0 && <div className={'flex flex-wrap gap-1.5 ' + (grid ? 'mt-auto pt-4' : 'mt-2')}>{memo.tags.map((tag) => <TagBadge key={tag.id} name={tag.name} />)}</div>}
            </div>
        </Link>
        <div className={grid ? 'mt-3 flex items-center justify-end border-t border-white/[0.06] pt-3' : 'shrink-0'}><FavoriteButton memo={memo} /></div>
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