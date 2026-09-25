import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Bookmark, Check, ChevronDown, FileText, Folder, FolderPlus, LayoutGrid, List, MoreHorizontal, Pencil, Plus, Search, Sparkles, Trash2, X } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import Pagination from '@/Components/Ui/Pagination';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import TagBadge from '@/Components/Memos/TagBadge';
import { buttonClass } from '@/Components/Ui/buttons';
import Modal from '@/Components/Ui/Modal';
import ConfirmModal from '@/Components/Ui/ConfirmModal';
import useGsapScrollReveal from '@/hooks/useGsapScrollReveal';

function listUrl({ tag, favorites, recent, q, folder, trash, sort }) {
    const params = new URLSearchParams();
    if (tag) params.set('tag', tag);
    if (favorites) params.set('favorites', '1');
    if (recent) params.set('recent', '1');
    if (q) params.set('q', q);
    if (folder) params.set('folder', folder);
    if (trash) params.set('trash', '1');
    if (sort) params.set('sort', sort);
    const query = params.toString();
    return query ? '/memos?' + query : '/memos';
}

export default function Index({ memos, tags = [], folders = [], filters = {}, counts = {} }) {
    const items = memos?.data ?? [];
    const [query, setQuery] = useState(filters.q ?? '');
    const [mobileLibraryOpen, setMobileLibraryOpen] = useState(false);
    const [view, setView] = useState(() => { try { return localStorage.getItem('devroad:memos:view') || 'list'; } catch { return 'list'; } });
    const [folderModal, setFolderModal] = useState({ open: false, mode: 'create', folder: null, parentId: null });
    const [folderName, setFolderName] = useState('');
    const [folderProcessing, setFolderProcessing] = useState(false);
    const [deleteFolderTarget, setDeleteFolderTarget] = useState(null);
    const [moveFolderTarget, setMoveFolderTarget] = useState(null);
    const [moveFolderParentId, setMoveFolderParentId] = useState('');
    const [moveFolderProcessing, setMoveFolderProcessing] = useState(false);
    const [moveMemoTarget, setMoveMemoTarget] = useState(null);
    const [moveFolderId, setMoveFolderId] = useState('');
    const [moveProcessing, setMoveProcessing] = useState(false);
    const [selectedIds, setSelectedIds] = useState([]);
    const [activeMenu, setActiveMenu] = useState(null);
    const [activeFolderMenu, setActiveFolderMenu] = useState(null);
    const [bulkMoveOpen, setBulkMoveOpen] = useState(false);
    const [bulkMoveFolderId, setBulkMoveFolderId] = useState('');
    const [bulkProcessing, setBulkProcessing] = useState(false);
    const [bulkDeleteOpen, setBulkDeleteOpen] = useState(false);
    const [emptyTrashOpen, setEmptyTrashOpen] = useState(false);
    const [emptyTrashProcessing, setEmptyTrashProcessing] = useState(false);
    const [deleteMemoTarget, setDeleteMemoTarget] = useState(null);
    const [forceDeleteMemoTarget, setForceDeleteMemoTarget] = useState(null);
    const [searchInput, setSearchInput] = useState(null);
    const [loading, setLoading] = useState(false);
    const animationRef = useRef(null);
    useGsapScrollReveal(animationRef, [items.length, view, filters.folder, filters.q, filters.trash]);

    useEffect(() => { setQuery(filters.q ?? ''); }, [filters.q]);
    useEffect(() => { try { localStorage.setItem('devroad:memos:view', view); } catch {} }, [view]);

    useEffect(() => {
        setSelectedIds([]);
        setActiveMenu(null);
    }, [filters.folder, filters.q, filters.tag, filters.favorites, filters.recent, filters.trash, filters.sort]);

    useEffect(() => {
        const removeStart = router.on('start', () => setLoading(true));
        const removeFinish = router.on('finish', () => setLoading(false));
        return () => { removeStart(); removeFinish(); };
    }, []);

    useEffect(() => {
        const handler = (event) => {
            const tag = event.target?.tagName?.toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select' || event.target?.isContentEditable) {
                if (event.key === 'Escape') setActiveMenu(null);
                return;
            }
            if (event.key === '/') {
                event.preventDefault();
                document.querySelector('[data-memo-search]')?.focus();
            } else if (event.key === 'n') {
                event.preventDefault();
                window.location.href = filters.folder ? '/memos/create?folder=' + filters.folder : '/memos/create';
            } else if (event.key === 'Escape') {
                setActiveMenu(null);
                setActiveFolderMenu(null);
                setSelectedIds([]);
            }
        };
        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, [filters.folder]);

    useEffect(() => {
        setSelectedIds([]);
    }, [memos?.current_page]);

    useEffect(() => {
        if (!activeMenu && !activeFolderMenu) return undefined;
        const close = () => { setActiveMenu(null); setActiveFolderMenu(null); };
        document.addEventListener('mousedown', close);
        return () => document.removeEventListener('mousedown', close);
    }, [activeMenu, activeFolderMenu]);

    function submitSearch(event) {
        event.preventDefault();
        router.get('/memos', {
            q: query.trim() || undefined,
            tag: filters.tag || undefined,
            favorites: filters.favorites ? 1 : undefined,
            recent: filters.recent ? 1 : undefined,
            trash: filters.trash ? 1 : undefined,
            folder: filters.folder || undefined,
            sort: filters.sort || undefined,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }

    function createFolder(parentId = null) {
        setFolderModal({ open: true, mode: 'create', folder: null, parentId });
        setFolderName('');
    }

    function renameFolder(folder) {
        setFolderModal({ open: true, mode: 'rename', folder, parentId: folder.parent_id ?? null });
        setFolderName(folder.name);
    }

    function openMoveFolder(folder) {
        setActiveFolderMenu(null);
        setMoveFolderTarget(folder);
        setMoveFolderParentId(folder.parent_id ? String(folder.parent_id) : '');
    }

    function submitMoveFolder() {
        if (!moveFolderTarget || moveFolderProcessing) return;
        const parentId = moveFolderParentId ? Number(moveFolderParentId) : null;
        if (parentId === moveFolderTarget.id) return;
        setMoveFolderProcessing(true);
        router.patch('/memo-folders/' + moveFolderTarget.id, { parent_id: parentId }, {
            preserveScroll: true,
            onSuccess: () => setMoveFolderTarget(null),
            onFinish: () => setMoveFolderProcessing(false),
        });
    }

    function submitFolder() {
        const name = folderName.trim();
        if (!name || folderProcessing) return;
        setFolderProcessing(true);
        const options = { preserveScroll: true, onSuccess: () => setFolderModal((current) => ({ ...current, open: false })), onFinish: () => setFolderProcessing(false) };
        if (folderModal.mode === 'rename') router.patch('/memo-folders/' + folderModal.folder.id, { name }, options);
        else router.post('/memo-folders', { name, parent_id: folderModal.parentId }, options);
    }

    function deleteFolder(folder) {
        setDeleteFolderTarget(folder);
    }

    function confirmDeleteFolder() {
        if (!deleteFolderTarget) return;
        router.delete('/memo-folders/' + deleteFolderTarget.id, { preserveScroll: true, onFinish: () => setDeleteFolderTarget(null) });
    }

    function duplicateMemo(memo) {
        setActiveMenu(null);
        router.post('/memos/' + memo.id + '/duplicate', {}, { preserveScroll: true });
    }

    function deleteMemo(memo) {
        setActiveMenu(null);
        setDeleteMemoTarget(memo);
    }

    function forceDeleteMemo(memo) { setActiveMenu(null); setForceDeleteMemoTarget(memo); }

    function confirmForceDeleteMemo() {
        if (!forceDeleteMemoTarget) return;
        router.delete('/memos/' + forceDeleteMemoTarget.id + '/force-delete', { preserveScroll: true, onFinish: () => setForceDeleteMemoTarget(null) });
    }

    function confirmDeleteMemo() {
        if (!deleteMemoTarget) return;
        router.delete('/memos/' + deleteMemoTarget.id, { preserveScroll: true, onFinish: () => setDeleteMemoTarget(null) });
    }

    function openMoveMemo(memo) {
        setActiveMenu(null);
        setMoveMemoTarget(memo);
        setMoveFolderId(memo.folder_id ? String(memo.folder_id) : '');
    }

    function toggleSelected(id) {
        setSelectedIds((current) => current.includes(id) ? current.filter((item) => item !== id) : [...current, id]);
    }

    function toggleSelectAll() {
        const ids = items.map((memo) => memo.id);
        setSelectedIds((current) => current.length === ids.length ? [] : ids);
    }

    function bulkAction(action, extra = {}) {
        if (!selectedIds.length || bulkProcessing) return;
        setBulkProcessing(true);
        router.post('/memos/bulk', { ids: selectedIds, action, ...extra }, {
            preserveScroll: true,
            onSuccess: () => setSelectedIds([]),
            onFinish: () => setBulkProcessing(false),
        });
    }

    function openBulkMove() {
        setBulkMoveFolderId('');
        setBulkMoveOpen(true);
    }

    function submitBulkMove() {
        bulkAction('move', { folder_id: bulkMoveFolderId || null });
        setBulkMoveOpen(false);
    }

    function changeSort(sort) {
        router.get('/memos', {
            q: filters.q || undefined,
            tag: filters.tag || undefined,
            favorites: filters.favorites ? 1 : undefined,
            recent: filters.recent ? 1 : undefined,
            folder: filters.folder || undefined,
            trash: filters.trash ? 1 : undefined,
            sort,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }

    function moveMemo() {
        if (!moveMemoTarget || moveProcessing) return;
        setMoveProcessing(true);
        router.patch('/memos/' + moveMemoTarget.id + '/move', { folder_id: moveFolderId || null }, {
            preserveScroll: true, preserveState: true,
            onSuccess: () => setMoveMemoTarget(null),
            onFinish: () => setMoveProcessing(false),
        });
    }

    // --- Glisser-déposer -------------------------------------------------
    // dragged : { type: 'folder' | 'memo', id, parentId } — parentId sert à
    // ne réordonner qu'entre dossiers d'un même niveau (mêmes « frères »).
    const [dragged, setDragged] = useState(null);
    const draggedRef = useRef(null);
    const [dropTarget, setDropTarget] = useState(null); // id de dossier survolé (ou 'root')

    function setDraggedItem(item, event = null) {
        draggedRef.current = item;
        setDragged(item);
        if (event?.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('application/x-devroad-memo-dnd', JSON.stringify(item));
            event.dataTransfer.setData('text/plain', item.type + ':' + item.id);
        }
    }

    function readDragged(event = null) {
        if (draggedRef.current) return draggedRef.current;
        try {
            const raw = event?.dataTransfer?.getData('application/x-devroad-memo-dnd');
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    }

    function startDragFolder(folder, event) {
        setDraggedItem({ type: 'folder', id: folder.id, parentId: folder.parent_id ?? null }, event);
    }

    function startDragMemo(memo, event) {
        setDraggedItem({ type: 'memo', id: memo.id }, event);
    }

    function endDrag() {
        draggedRef.current = null;
        setDragged(null);
        setDropTarget(null);
    }

    // Dépose SUR un dossier : une fiche s'y range, un dossier s'y imbrique.
    function dropOnFolder(folder, event = null) {
        const item = readDragged(event);
        if (!item) return;

        if (item.type === 'memo') {
            router.patch('/memos/' + item.id + '/move', { folder_id: folder.id }, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: endDrag,
                onError: endDrag,
            });
            return;
        }

        if (item.type === 'folder' && item.id !== folder.id) {
            router.patch('/memo-folders/' + item.id, { parent_id: folder.id }, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: endDrag,
                onError: endDrag,
            });
            return;
        }

        endDrag();
    }

    // Dépose sur l'en-tête « Dossiers » : remonte un dossier à la racine,
    // ou retire une fiche de son dossier.
    function dropOnRoot(event = null) {
        const item = readDragged(event);
        if (!item) return;

        if (item.type === 'memo') {
            router.patch('/memos/' + item.id + '/move', { folder_id: null }, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: endDrag,
                onError: endDrag,
            });
        } else if (item.type === 'folder' && item.parentId !== null) {
            router.patch('/memo-folders/' + item.id, { parent_id: null }, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: endDrag,
                onError: endDrag,
            });
        } else {
            endDrag();
        }
    }

    // Réordonne un dossier parmi ses frères (même parent), à la position de « target ».
    function reorderFolder(target, event = null) {
        const item = readDragged(event);
        if (!item || item.type !== 'folder') return;
        if (item.id === target.id) return;
        if ((item.parentId ?? null) !== (target.parent_id ?? null)) return; // niveaux différents : dropOnFolder gère l'imbrication

        const parentId = target.parent_id ?? null;
        const siblings = folders.filter((folder) => (folder.parent_id ?? null) === parentId).sort((a, b) => a.position - b.position);
        const ids = siblings.map((folder) => folder.id).filter((id) => id !== item.id);
        const targetIndex = ids.indexOf(target.id);
        ids.splice(targetIndex, 0, item.id);

        router.patch('/memo-folders/reorder', { parent_id: parentId, ordered_ids: ids }, { preserveScroll: true, preserveState: true, onSuccess: endDrag, onError: endDrag });
    }

    function clearSearch() {
        setQuery('');
        router.get('/memos', {
            tag: filters.tag || undefined,
            favorites: filters.favorites ? 1 : undefined,
            recent: filters.recent ? 1 : undefined,
            trash: filters.trash ? 1 : undefined,
            folder: filters.folder || undefined,
            sort: filters.sort || undefined,
        }, { preserveState: true, replace: true });
    }

    const folderMap = useMemo(() => new Map(folders.map((folder) => [folder.id, folder])), [folders]);
    const folderTree = useMemo(() => buildFolderTree(folders), [folders]);

    function getFolderPath(folderId) {
        const path = [];
        let current = folderId ? folderMap.get(Number(folderId)) : null;
        let guard = 0;
        while (current && guard++ < 30) {
            path.unshift(current);
            current = current.parent_id ? folderMap.get(Number(current.parent_id)) : null;
        }
        return path;
    }

    const activeFolderPath = getFolderPath(filters.folder);

    const activeFolder = useMemo(() => {
        if (filters.trash) return 'Corbeille';
        if (filters.favorites) return 'Favoris';
        if (filters.recent) return 'Récents';
        if (filters.tag) return tags.find((tag) => tag.slug === filters.tag)?.name ?? 'Collection';
        if (filters.folder) return activeFolderPath.at(-1)?.name ?? 'Dossier';
        return 'Toutes les fiches';
    }, [filters, tags, activeFolderPath]);

    const hasFilter = Boolean(filters.tag || filters.favorites || filters.recent || filters.q || filters.folder || filters.trash);

    return (
        <AppLayout>
            <Head title="Fiches mémo" />
            <div ref={animationRef} className="space-y-5">
                <PageHeader title="Fiches mémo" subtitle="Un espace de rangement façon Notion pour organiser tes connaissances, commandes et astuces." actions={
                    <Link href={filters.folder ? '/memos/create?folder=' + filters.folder : '/memos/create'} className={buttonClass('primary')}>
                        <Plus size={16} aria-hidden="true" />
                        <span className="hidden sm:inline">Nouvelle fiche</span>
                        <span className="sm:hidden">Nouvelle</span>
                    </Link>
                } />

                <div data-gsap-reveal className="flex items-center gap-2 lg:hidden">
                    <button type="button" onClick={() => setMobileLibraryOpen((open) => !open)} className="inline-flex items-center gap-2 rounded-xl border border-white/[0.07] bg-[#0D1725] px-3 py-2 text-xs font-semibold text-slate-300">
                        <Folder size={15} className="text-[#FF8A3D]" /> Rangement
                        <ChevronDown size={14} className={mobileLibraryOpen ? 'rotate-180 transition' : 'transition'} />
                    </button>
                    <span className="truncate text-xs text-slate-500">{activeFolder}</span>
                </div>

                <div data-gsap-reveal className="grid gap-5 lg:grid-cols-[230px_minmax(0,1fr)]">
                    <aside data-gsap-reveal className={(mobileLibraryOpen ? 'block' : 'hidden') + ' lg:block'}>
                        <div className="sticky top-5 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-3">
                            <div className="mb-3 flex items-center gap-2 px-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FF6A00]/10 text-[#FF8A3D]"><Folder size={16} /></div>
                                <div className="min-w-0"><p className="text-xs font-semibold text-white">Mon espace</p><p className="text-[10px] text-slate-500">{counts.total ?? 0} fiches</p></div>
                            </div>
                            <div className="space-y-1">
                                <NavItem href="/memos" active={!hasFilter} icon={FileText} label="Toutes les fiches" count={counts.total} />
                                <NavItem href={listUrl({ favorites: true })} active={Boolean(filters.favorites)} icon={Bookmark} label="Favoris" count={counts.favorites} />
                                <NavItem href={listUrl({ trash: true })} active={Boolean(filters.trash)} icon={Trash2} label="Corbeille" count={counts.trash} />
                                <NavItem href={listUrl({ recent: true })} active={Boolean(filters.recent)} icon={Sparkles} label="Récents" count={counts.recent} />
                            </div>
                            <div className="mt-5 border-t border-white/[0.06] pt-4">
                                <div
                                    onDragOver={(event) => { if (dragged) { event.preventDefault(); setDropTarget('root'); } }}
                                    onDragLeave={() => setDropTarget((current) => (current === 'root' ? null : current))}
                                    onDrop={(event) => { event.preventDefault(); dropOnRoot(event); }}
                                    className={'mb-2 flex items-center justify-between rounded-lg px-2 py-1 transition ' + (dropTarget === 'root' ? 'bg-[#FF6A00]/10 ring-1 ring-[#FF6A00]/30' : '')}
                                >
                                    <span className="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-600">Dossiers</span>
                                    <button type="button" onClick={() => createFolder()} className="text-slate-600 transition hover:text-[#FF8A3D]" title="Nouveau dossier"><FolderPlus size={14} /></button>
                                </div>
                                <div className="space-y-1">
                                    {buildFolderTree(folders).map((folder) => <FolderNavItem key={folder.id} folder={folder} active={Number(filters.folder) === folder.id} onCreateChild={createFolder} onRename={renameFolder} onMove={openMoveFolder} onDelete={deleteFolder} activeMenu={activeFolderMenu} onMenu={(id) => setActiveFolderMenu((current) => current === id ? null : id)} isDragging={dragged?.type === 'folder' && dragged.id === folder.id} isDropTarget={dropTarget === folder.id} onDragStartFolder={startDragFolder} onDragEnd={endDrag} onDragOverTarget={() => setDropTarget(folder.id)} onDropReorder={reorderFolder} onDropNest={dropOnFolder} canDrop={Boolean(dragged)} />)}
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

                    <section data-gsap-reveal className="min-w-0">
                        <div className="mb-4 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-3">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <form onSubmit={submitSearch} className="relative min-w-0 flex-1">
                                    <Search size={16} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-600" />
                                    <input data-memo-search type="search" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Rechercher dans tes fiches..." className="h-10 w-full rounded-xl border border-white/[0.06] bg-[#08111F] pl-9 pr-10 text-sm text-white outline-none transition-[border-color,box-shadow] duration-200 placeholder:text-slate-600 focus:border-[#FF6A00]/40 focus:ring-2 focus:ring-[#FF6A00]/5" />
                                    {query && <button type="button" onClick={clearSearch} aria-label="Effacer la recherche" className="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 hover:bg-white/[0.05] hover:text-white"><X size={14} /></button>}
                                </form>
                                <div className="flex items-center gap-2">
                                    <select value={filters.sort ?? 'updated_desc'} onChange={(event) => changeSort(event.target.value)} className="h-8 rounded-lg border border-white/[0.06] bg-[#08111F] px-2 text-[11px] font-semibold text-slate-400 outline-none focus:border-[#FF6A00]/30" aria-label="Trier les fiches">
                                        <option value="updated_desc">Plus récentes</option><option value="updated_asc">Plus anciennes</option><option value="title_asc">A → Z</option><option value="title_desc">Z → A</option><option value="favorite">Favoris d'abord</option>
                                    </select>
                                    <div className="flex rounded-xl border border-white/[0.06] bg-[#08111F] p-1">
                                        <ViewButton active={view === 'list'} onClick={() => setView('list')} icon={List} label="Liste" />
                                        <ViewButton active={view === 'grid'} onClick={() => setView('grid')} icon={LayoutGrid} label="Grille" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                            <div className="min-w-0">
                                <div className="flex flex-wrap items-center gap-1 text-xs text-slate-500">
                                    <Link href="/memos" className="hover:text-[#FF8A3D]">Mémos</Link>
                                    {activeFolderPath.map((folder) => <span key={folder.id} className="flex items-center gap-1"><span className="text-slate-700">/</span><Link href={listUrl({ folder: folder.id, sort: filters.sort })} className="hover:text-[#FF8A3D]">{folder.name}</Link></span>)}
                                </div>
                                <h2 className="mt-1 truncate text-sm font-semibold text-white">{activeFolder}</h2>
                                <p className="text-xs text-slate-600">{memos?.total ?? items.length} {memos?.total === 1 ? 'fiche' : 'fiches'}{filters.q ? ' pour « ' + filters.q + ' »' : ''}</p>
                            </div>
                            {filters.trash && counts.trash > 0 && <button type="button" onClick={() => setEmptyTrashOpen(true)} className="rounded-xl border border-red-400/15 bg-red-400/[0.05] px-3 py-2 text-xs font-semibold text-red-300 hover:bg-red-400/10">Vider la corbeille</button>}
                            {hasFilter && <Link href="/memos" className="shrink-0 text-xs font-semibold text-[#FF8A3D] hover:text-[#FFB078]">Réinitialiser</Link>}
                        </div>
                        {loading ? <MemoSkeletons grid={view === 'grid'} /> : items.length > 0 ? <>
                            <div className="mb-3 flex items-center justify-between gap-3 rounded-xl border border-white/[0.05] bg-[#0D1725]/70 px-3 py-2">
                                <label className="flex items-center gap-2 text-xs font-semibold text-slate-400"><input type="checkbox" checked={items.length > 0 && selectedIds.length === items.length} onChange={toggleSelectAll} className="h-4 w-4 rounded border-white/20 bg-[#101A2A] text-[#FF6A00] focus:ring-[#FF6A00]/30" /><span>{selectedIds.length ? selectedIds.length + ' sélectionnée(s)' : 'Sélectionner'}</span></label>
                                {selectedIds.length > 0 && <div className="flex flex-wrap items-center gap-1.5">
                                    {!filters.trash && <><button type="button" onClick={openBulkMove} className="rounded-lg bg-white/[0.04] px-2.5 py-1.5 text-[11px] font-semibold text-slate-300 hover:bg-white/[0.08]">Déplacer</button><button type="button" onClick={() => bulkAction('favorite')} className="rounded-lg bg-white/[0.04] px-2.5 py-1.5 text-[11px] font-semibold text-slate-300 hover:bg-white/[0.08]">Favori</button><button type="button" onClick={() => setBulkDeleteOpen(true)} className="rounded-lg bg-red-400/[0.06] px-2.5 py-1.5 text-[11px] font-semibold text-red-300 hover:bg-red-400/10">Supprimer</button></>}
                                    {filters.trash && <><button type="button" onClick={() => bulkAction('restore')} className="rounded-lg bg-[#FF6A00]/10 px-2.5 py-1.5 text-[11px] font-semibold text-[#FF8A3D]">Restaurer</button><button type="button" onClick={() => setBulkDeleteOpen(true)} className="rounded-lg bg-red-400/[0.06] px-2.5 py-1.5 text-[11px] font-semibold text-red-300">Supprimer définitivement</button></>}
                                    <button type="button" onClick={() => setSelectedIds([])} className="rounded-lg p-1.5 text-slate-500 hover:text-white" aria-label="Annuler la sélection"><X size={14} /></button>
                                </div>}
                            </div>
                            <ul className={view === 'grid' ? 'grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3' : 'space-y-2.5'}>{items.map((memo) => <li key={memo.id}><MemoCard memo={memo} grid={view === 'grid'} trash={Boolean(filters.trash)} selected={selectedIds.includes(memo.id)} menuOpen={activeMenu === memo.id} folderPath={getFolderPath(memo.folder_id)} onSelect={() => toggleSelected(memo.id)} onMenu={() => setActiveMenu((current) => current === memo.id ? null : memo.id)} onDragStart={(event) => startDragMemo(memo, event)} onDragEnd={endDrag} onMove={openMoveMemo} onDuplicate={duplicateMemo} onDelete={deleteMemo} onForceDelete={forceDeleteMemo} /></li>)}</ul><Pagination links={memos.links} />
                        </> : <EmptyState filtered={hasFilter} trash={Boolean(filters.trash)} folder={Boolean(filters.folder)} />}
                    </section>
                </div>
            </div>
            <Modal show={folderModal.open} onClose={() => !folderProcessing && setFolderModal((current) => ({ ...current, open: false }))} title={folderModal.mode === 'rename' ? 'Renommer le dossier' : (folderModal.parentId ? 'Créer un sous-dossier' : 'Créer un dossier')} description={folderModal.mode === 'rename' ? 'Modifie le nom sans toucher aux fiches.' : 'Organise tes fiches dans une arborescence claire.'} footer={<div className="flex justify-end gap-2"><button type="button" onClick={() => setFolderModal((current) => ({ ...current, open: false }))} className="rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm font-semibold text-slate-300">Annuler</button><button type="button" onClick={submitFolder} disabled={!folderName.trim() || folderProcessing} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[#08111F] disabled:opacity-50">{folderProcessing ? 'Enregistrement...' : (folderModal.mode === 'rename' ? 'Renommer' : 'Créer le dossier')}</button></div>}>
                <label className="block"><span className="mb-2 block text-xs font-semibold text-slate-400">Nom du dossier</span><input autoFocus value={folderName} onChange={(event) => setFolderName(event.target.value)} onKeyDown={(event) => event.key === 'Enter' && submitFolder()} maxLength={120} placeholder="Ex. Laravel, React, DevOps..." className="h-11 w-full rounded-xl border border-white/[0.08] bg-[#08111F] px-3 text-sm text-white outline-none focus:border-[#FF6A00]/40" /></label>
            </Modal>
            <Modal show={bulkMoveOpen} onClose={() => !bulkProcessing && setBulkMoveOpen(false)} title="Déplacer les fiches sélectionnées" description={selectedIds.length + ' fiche(s) seront déplacée(s).'} footer={<div className="flex justify-end gap-2"><button type="button" onClick={() => setBulkMoveOpen(false)} className="rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm font-semibold text-slate-300">Annuler</button><button type="button" onClick={submitBulkMove} disabled={bulkProcessing} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[#08111F]">Déplacer</button></div>}><select value={bulkMoveFolderId} onChange={(event) => setBulkMoveFolderId(event.target.value)} className="h-11 w-full rounded-xl border border-white/[0.08] bg-[#08111F] px-3 text-sm text-white"><option value="">Sans dossier — racine</option>{folderTree.map((folder) => <option key={folder.id} value={folder.id}>{'— '.repeat(folder.depth ?? 0)}{folder.name}</option>)}</select></Modal>

            <ConfirmModal show={bulkDeleteOpen} title={filters.trash ? 'Supprimer définitivement les fiches ?' : 'Mettre les fiches à la corbeille ?'} description={filters.trash ? 'Cette action ne peut pas être annulée.' : 'Les fiches pourront être restaurées depuis la corbeille.'} confirmLabel={filters.trash ? 'Supprimer définitivement' : 'Mettre à la corbeille'} onClose={() => setBulkDeleteOpen(false)} onConfirm={() => { if (filters.trash) { bulkAction('force_delete'); } else { bulkAction('delete'); } setBulkDeleteOpen(false); }} />

            <ConfirmModal show={Boolean(deleteMemoTarget)} title={'Mettre « ' + (deleteMemoTarget?.title ?? '') + ' » à la corbeille ?'} description="La fiche pourra être restaurée depuis la corbeille." confirmLabel="Mettre à la corbeille" onClose={() => setDeleteMemoTarget(null)} onConfirm={confirmDeleteMemo} />
            <ConfirmModal show={Boolean(forceDeleteMemoTarget)} title={'Supprimer définitivement « ' + (forceDeleteMemoTarget?.title ?? '') + ' » ?'} description="Cette action est irréversible. Le contenu et les pièces jointes seront définitivement supprimés." confirmLabel="Supprimer définitivement" onClose={() => setForceDeleteMemoTarget(null)} onConfirm={confirmForceDeleteMemo} />
            <ConfirmModal
                show={emptyTrashOpen}
                title="Vider la corbeille ?"
                description="Toutes les fiches supprimées seront définitivement effacées."
                confirmLabel={emptyTrashProcessing ? 'Suppression...' : 'Vider la corbeille'}
                onClose={() => !emptyTrashProcessing && setEmptyTrashOpen(false)}
                onConfirm={() => {
                    if (emptyTrashProcessing) return;
                    setEmptyTrashProcessing(true);
                    router.post('/memos/empty-trash', {}, {
                        preserveScroll: true,
                        onFinish: () => {
                            setEmptyTrashProcessing(false);
                            setEmptyTrashOpen(false);
                        },
                    });
                }}
            />

            <Modal show={Boolean(moveFolderTarget)} onClose={() => !moveFolderProcessing && setMoveFolderTarget(null)} title="Déplacer le dossier" description="Choisis son dossier parent, ou remets-le à la racine." footer={<div className="flex justify-end gap-2"><button type="button" onClick={() => setMoveFolderTarget(null)} className="rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm font-semibold text-slate-300">Annuler</button><button type="button" onClick={submitMoveFolder} disabled={moveFolderProcessing} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[#08111F] disabled:opacity-50">{moveFolderProcessing ? 'Déplacement...' : 'Déplacer'}</button></div>}>
                <select value={moveFolderParentId} onChange={(event) => setMoveFolderParentId(event.target.value)} className="h-11 w-full rounded-xl border border-white/[0.08] bg-[#08111F] px-3 text-sm text-white outline-none focus:border-[#FF6A00]/40">
                    <option value="">Sans dossier — racine</option>
                    {folderTree.filter((candidate) => candidate.id !== moveFolderTarget?.id && !getFolderPath(candidate.id).some((ancestor) => ancestor.id === moveFolderTarget?.id)).map((folder) => <option key={folder.id} value={folder.id}>{'— '.repeat(folder.depth ?? 0)}{folder.name}</option>)}
                </select>
            </Modal>
            <ConfirmModal show={Boolean(deleteFolderTarget) title={'Supprimer « ' + (deleteFolderTarget?.name ?? '') + ' » ?'} description="Les fiches seront conservées mais retirées de ce dossier. Les sous-dossiers remonteront d'un niveau." confirmLabel="Supprimer le dossier" onClose={() => setDeleteFolderTarget(null)} onConfirm={confirmDeleteFolder} />
            <Modal show={Boolean(moveMemoTarget)} onClose={() => !moveProcessing && setMoveMemoTarget(null)} title="Déplacer la fiche" description="Choisis le dossier de destination, ou remets-la à la racine." footer={<div className="flex justify-end gap-2"><button type="button" onClick={() => setMoveMemoTarget(null)} className="rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm font-semibold text-slate-300">Annuler</button><button type="button" onClick={moveMemo} disabled={moveProcessing} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[#08111F] disabled:opacity-50">{moveProcessing ? 'Déplacement...' : 'Déplacer'}</button></div>}>
                <div className="rounded-xl border border-white/[0.06] bg-[#08111F] p-3"><p className="mb-2 truncate text-xs font-semibold text-white">{moveMemoTarget?.icon ?? '📝'} {moveMemoTarget?.title}</p><select value={moveFolderId} onChange={(event) => setMoveFolderId(event.target.value)} className="h-11 w-full rounded-xl border border-white/[0.08] bg-[#0D1725] px-3 text-sm text-white outline-none focus:border-[#FF6A00]/40"><option value="">Sans dossier — racine</option>{folders.map((folder) => <option key={folder.id} value={folder.id}>{'— '.repeat(folder.depth ?? 0)}{folder.name}</option>)}</select></div>
            </Modal>

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

function FolderNavItem({ folder, active, onCreateChild, onRename, onMove, onDelete, activeMenu, onMenu, isDragging, isDropTarget, onDragStartFolder, onDragEnd, onDragOverTarget, onDropReorder, onDropNest, canDrop }) {
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
        event.stopPropagation();
        const raw = event.dataTransfer?.getData('application/x-devroad-memo-dnd');
        let item = null;
        try { item = raw ? JSON.parse(raw) : null; } catch {}
        if (item?.type === 'memo') {
            onDropNest(folder, event);
        } else if (nestHover) {
            onDropNest(folder, event);
        } else {
            onDropReorder(folder, event);
        }
        setNestHover(false);
    }

    return <div className="relative">
        <div
            draggable
            onDragStart={(event) => onDragStartFolder(folder, event)}
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
            <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); }} className="flex h-7 w-7 items-center justify-center rounded-lg text-slate-700 hover:bg-white/[0.05] hover:text-white sm:hidden sm:group-hover:flex" title="Actions du dossier"><MoreHorizontal size={14} /></button>
            {activeMenu === folder.id && <div
                className="absolute right-1 top-9 z-[70] w-44 overflow-hidden rounded-xl border border-white/[0.08] bg-[#0D1725] p-1 shadow-[0_20px_50px_rgba(0,0,0,.45)]"
                onMouseDown={(event) => event.stopPropagation()}
                onClick={(event) => event.stopPropagation()}
            >
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onCreateChild(folder.id); }} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-slate-300 hover:bg-white/[0.05]">Nouveau sous-dossier</button>
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onRename(folder); }} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-slate-300 hover:bg-white/[0.05]">Renommer</button>
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onMove(folder); }} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-slate-300 hover:bg-white/[0.05]">Déplacer</button>
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onDelete(folder); }} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-red-300 hover:bg-red-400/[0.08]">Supprimer</button>
            </div>}
        </div>
    </div>;
}

function ViewButton({ active, onClick, icon: Icon, label }) {
    return <button type="button" onClick={onClick} aria-label={label} aria-pressed={active} className={'flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-200 ' + (active ? 'bg-white/[0.07] text-white' : 'text-slate-600 hover:text-slate-300')}><Icon size={15} /></button>;
}

function MemoCard({ memo, grid, trash = false, selected = false, menuOpen = false, folderPath = [], onSelect, onMenu, onDragStart, onDragEnd, onMove, onDuplicate, onDelete, onForceDelete }) {
    return <article
        draggable
        onDragStart={(event) => onDragStart(event)}
        onDragEnd={onDragEnd}
        onContextMenu={(event) => { event.preventDefault(); onMenu(); }}
        className={'group relative overflow-visible rounded-2xl border bg-[#0D1725] transition-all duration-200 ease-out hover:-translate-y-px hover:bg-[#101B2C] cursor-grab active:cursor-grabbing ' + (menuOpen ? 'z-50 ' : 'z-0 ') + (selected ? 'border-[#FF6A00]/50 ring-1 ring-[#FF6A00]/20' : 'border-white/[0.06] hover:border-[#FF6A00]/20 ') + (grid ? 'flex min-h-[230px] flex-col p-4' : 'flex items-center gap-4 px-4 py-3')}
    >
        <button type="button" onClick={(event) => { event.stopPropagation(); onSelect(); }} className="absolute left-3 top-3 z-10 flex h-7 w-7 items-center justify-center rounded-lg bg-[#08111F]/80 text-slate-600 opacity-100 backdrop-blur transition sm:opacity-0 sm:group-hover:opacity-100 hover:text-white" aria-label={selected ? 'Désélectionner' : 'Sélectionner'}>
            {selected ? <span className="flex h-4 w-4 items-center justify-center rounded bg-[#FF6A00] text-[#08111F]"><Check size={11} strokeWidth={3} /></span> : <span className="h-4 w-4 rounded border border-white/20" />}
        </button>
        <Link href={'/memos/' + memo.id} className={'min-w-0 flex-1 ' + (grid ? 'flex flex-col' : 'flex items-center gap-4')} draggable={false}>
            <div className={grid ? 'mb-3 flex items-center justify-between gap-2' : 'flex w-[150px] shrink-0 items-center gap-2'}>
                <span className="flex items-center gap-2 text-[10px] font-medium uppercase tracking-[0.12em] text-slate-600"><span className="h-1.5 w-1.5 rounded-full bg-[#FF6A00]" />{memo.is_favorite ? 'Favori' : 'Fiche'}</span>
                {grid && <span className="text-[10px] text-slate-600">{memo.updated_at ? new Date(memo.updated_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : ''}</span>}
            </div>
            <div className={grid ? 'min-w-0' : 'min-w-0 flex-1'}>
                <h3 className={'font-semibold text-white transition-colors duration-200 group-hover:text-[#FFB078] ' + (grid ? 'line-clamp-2 text-base leading-6' : 'truncate text-sm')}>{memo.icon ?? '📝'} {memo.title}</h3>
                {folderPath.length > 0 && <p className="mt-1 truncate text-[10px] font-medium text-[#FF8A3D]/70">{folderPath.map((folder) => folder.name).join(' / ')}</p>}
                <p className="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{memo.excerpt}</p>
                {memo.tags?.length > 0 && <div className={'flex flex-wrap gap-1.5 ' + (grid ? 'mt-auto pt-4' : 'mt-2')}>{memo.tags.map((tag) => <TagBadge key={tag.id} name={tag.name} />)}</div>}
            </div>
        </Link>
        <div className={grid ? 'mt-3 flex items-center justify-end gap-2 border-t border-white/[0.06] pt-3' : 'shrink-0'}>
            {trash ? <button type="button" onClick={() => router.post('/memos/' + memo.id + '/restore')} className="rounded-lg border border-white/[0.06] px-2.5 py-1.5 text-[11px] font-semibold text-[#FF8A3D] hover:bg-[#FF6A00]/10">Restaurer</button> : <FavoriteButton memo={memo} />}
        </div>
        <div className="absolute right-2 top-2 z-20">
            <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(); }} className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#08111F]/75 text-slate-500 backdrop-blur transition hover:bg-[#101B2C] hover:text-white" aria-label="Actions de la fiche"><MoreHorizontal size={17} /></button>
            {menuOpen && <div className="absolute right-0 top-9 w-48 overflow-hidden rounded-xl border border-white/[0.08] bg-[#0D1725] p-1 shadow-[0_20px_50px_rgba(0,0,0,.45)]">
                <Link href={'/memos/' + memo.id} className="block rounded-lg px-3 py-2 text-xs text-slate-300 hover:bg-white/[0.05]">Ouvrir</Link>
                {!trash && <><Link href={'/memos/' + memo.id + '/edit'} className="block rounded-lg px-3 py-2 text-xs text-slate-300 hover:bg-white/[0.05]">Modifier</Link><button type="button" onClick={() => onMove(memo)} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-slate-300 hover:bg-white/[0.05]">Déplacer</button><button type="button" onClick={() => onDuplicate(memo)} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-slate-300 hover:bg-white/[0.05]">Dupliquer</button><button type="button" onClick={() => onDelete(memo)} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-red-300 hover:bg-red-400/[0.08]">Mettre à la corbeille</button></>}
                {trash && <><button type="button" onClick={() => router.post('/memos/' + memo.id + '/restore')} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-[#FF8A3D] hover:bg-[#FF6A00]/10">Restaurer</button><button type="button" onClick={() => onForceDelete(memo)} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-red-300 hover:bg-red-400/[0.08]">Supprimer définitivement</button></>}
            </div>}
        </div>
    </article>;
}

function MemoSkeletons({ grid }) {
    return <div className={grid ? 'grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3' : 'space-y-2.5'} aria-label="Chargement">
        {Array.from({ length: grid ? 6 : 5 }).map((_, index) => <div key={index} className={'animate-pulse rounded-2xl border border-white/[0.05] bg-[#0D1725] ' + (grid ? 'h-52 p-4' : 'h-20 p-4')}>
            <div className="h-3 w-24 rounded bg-white/[0.06]" />
            <div className="mt-4 h-4 w-2/3 rounded bg-white/[0.06]" />
            <div className="mt-3 h-3 w-full rounded bg-white/[0.04]" />
        </div>)}
    </div>;
}

function EmptyState({ filtered, trash = false, folder = false }) {
    return <div className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-8 text-center">
        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FF6A00]/10 text-[#FF8A3D]"><FileText size={25} aria-hidden="true" /></div>
        <h2 className="mt-4 text-base font-semibold text-white">{trash ? 'La corbeille est vide' : filtered ? 'Aucune fiche ne correspond' : folder ? 'Ce dossier est vide' : 'Aucune fiche mémo'}</h2>
        <p className="mx-auto mt-1 max-w-sm text-sm leading-6 text-slate-500">{trash ? 'Les fiches supprimées apparaîtront ici.' : filtered ? 'Essaie une autre recherche, collection ou un autre dossier.' : folder ? 'Crée une fiche ici ou déplace-en une depuis un autre dossier.' : 'Crée ta première fiche pour construire ton espace de connaissances.'}</p>
        {!trash && <Link href={filtered ? '/memos' : '/memos/create'} className={buttonClass('primary') + ' mt-5'}>{filtered ? 'Voir toutes les fiches' : 'Créer une fiche'}</Link>}
    </div>;
}