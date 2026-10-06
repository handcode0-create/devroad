import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { ArrowDown, ArrowUp, Bookmark, Check, ChevronDown, FileText, Folder, FolderPlus, LayoutGrid, List, MoreHorizontal, Pencil, Plus, Search, Sparkles, Trash2, X } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import Pagination from '@/Components/Ui/Pagination';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import TagBadge from '@/Components/Memos/TagBadge';
import { buttonClass } from '@/Components/Ui/buttons';
import Modal from '@/Components/Ui/Modal';
import ConfirmModal from '@/Components/Ui/ConfirmModal';
import useGsapScrollReveal from '@/hooks/useGsapScrollReveal';
import { useMemoListMotion, useMenuPop } from '@/Components/Memos/memoMotion';

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
    const isDesktop = useIsDesktop();
    const authUser = usePage().props.auth?.user;
    const [actionsMemo, setActionsMemo] = useState(null);
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
    useGsapScrollReveal(animationRef, [items.length, filters.folder, filters.q, filters.trash]);
    const listRef = useRef(null);
    const contentKey = items.map((memo) => memo.id).join(',') + '|' + (filters.folder ?? '') + '|' + (filters.q ?? '') + '|' + (filters.trash ? 't' : '');
    const { captureLayout } = useMemoListMotion(listRef, view, contentKey);

    function changeView(next) {
        if (next === view) return;
        captureLayout();
        setView(next);
    }

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

    // Repli clavier/tactile du glisser-déposer ci-dessus : monte ou descend
    // un dossier d'un rang parmi ses frères (même parent).
    function moveFolderByOffset(folder, offset) {
        const parentId = folder.parent_id ?? null;
        const siblings = folders.filter((f) => (f.parent_id ?? null) === parentId).sort((a, b) => a.position - b.position);
        const ids = siblings.map((f) => f.id);
        const currentIndex = ids.indexOf(folder.id);
        const targetIndex = currentIndex + offset;
        if (targetIndex < 0 || targetIndex >= ids.length) return;

        ids.splice(currentIndex, 1);
        ids.splice(targetIndex, 0, folder.id);

        router.patch('/memo-folders/reorder', { parent_id: parentId, ordered_ids: ids }, { preserveScroll: true, preserveState: true });
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

    const libraryPanel = (
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
                                    {buildFolderTree(folders).map((folder) => <FolderNavItem key={folder.id} folder={folder} active={Number(filters.folder) === folder.id} onCreateChild={createFolder} onRename={renameFolder} onMove={openMoveFolder} onDelete={deleteFolder} onMoveUp={() => moveFolderByOffset(folder, -1)} onMoveDown={() => moveFolderByOffset(folder, 1)} activeMenu={activeFolderMenu} onMenu={(id) => setActiveFolderMenu((current) => current === id ? null : id)} isDragging={dragged?.type === 'folder' && dragged.id === folder.id} isDropTarget={dropTarget === folder.id} onDragStartFolder={startDragFolder} onDragEnd={endDrag} onDragOverTarget={() => setDropTarget(folder.id)} onDropReorder={reorderFolder} onDropNest={dropOnFolder} canDrop={Boolean(dragged)} />)}
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
    );

    return (
        <AppLayout mobileHeader={isDesktop}>
            <Head title="Fiches mémo" />
            {!isDesktop && <MobileMemos
                user={authUser}
                items={items}
                memos={memos}
                counts={counts}
                folders={folders}
                tags={tags}
                filters={filters}
                hasFilter={hasFilter}
                query={query}
                setQuery={setQuery}
                onSearch={submitSearch}
                onClearSearch={clearSearch}
                getFolderPath={getFolderPath}
                loading={loading}
                onOpenLibrary={() => setMobileLibraryOpen(true)}
                onActions={setActionsMemo}
            />}
            {!isDesktop && mobileLibraryOpen && <BottomSheet title="Dossiers" onClose={() => setMobileLibraryOpen(false)}>
                <div onClick={(event) => { if (event.target.closest('a')) setMobileLibraryOpen(false); }}>{libraryPanel}</div>
            </BottomSheet>}
            {!isDesktop && actionsMemo && <BottomSheet title={actionsMemo.title} onClose={() => setActionsMemo(null)}>
                <div className="flex flex-col gap-2">
                    {filters.trash ? <>
                        <SheetAction onClick={() => { setActionsMemo(null); router.post('/memos/' + actionsMemo.id + '/restore'); }}>Restaurer</SheetAction>
                        <SheetAction danger onClick={() => { const memo = actionsMemo; setActionsMemo(null); forceDeleteMemo(memo); }}>Supprimer définitivement</SheetAction>
                    </> : <>
                        <SheetAction href={'/memos/' + actionsMemo.id}>Ouvrir</SheetAction>
                        <SheetAction href={'/memos/' + actionsMemo.id + '/edit'}>Modifier</SheetAction>
                        <SheetAction onClick={() => { const memo = actionsMemo; setActionsMemo(null); openMoveMemo(memo); }}>Déplacer</SheetAction>
                        <SheetAction onClick={() => { const memo = actionsMemo; setActionsMemo(null); duplicateMemo(memo); }}>Dupliquer</SheetAction>
                        <SheetAction danger onClick={() => { const memo = actionsMemo; setActionsMemo(null); deleteMemo(memo); }}>Mettre à la corbeille</SheetAction>
                    </>}
                </div>
            </BottomSheet>}
            {isDesktop && <div ref={animationRef} className="space-y-5">
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
                        {libraryPanel}
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
                                    <select value={filters.sort ?? 'updated_desc'} onChange={(event) => changeSort(event.target.value)} className="h-9 rounded-lg border border-white/[0.06] bg-[#08111F] py-0 pl-2.5 pr-8 text-xs font-semibold leading-none text-slate-300 outline-none focus:border-[#FF6A00]/30" aria-label="Trier les fiches">
                                        <option value="updated_desc">Plus récentes</option><option value="updated_asc">Plus anciennes</option><option value="title_asc">A → Z</option><option value="title_desc">Z → A</option><option value="favorite">Favoris d'abord</option>
                                    </select>
                                    <div className="flex rounded-xl border border-white/[0.06] bg-[#08111F] p-1">
                                        <ViewButton active={view === 'list'} onClick={() => changeView('list')} icon={List} label="Liste" />
                                        <ViewButton active={view === 'grid'} onClick={() => changeView('grid')} icon={LayoutGrid} label="Grille" />
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
                            <ul ref={listRef} className={view === 'grid' ? 'grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3' : 'flex flex-col gap-2.5'}>{items.map((memo) => <li key={memo.id} className="min-w-0"><MemoCard memo={memo} grid={view === 'grid'} trash={Boolean(filters.trash)} selected={selectedIds.includes(memo.id)} menuOpen={activeMenu === memo.id} folderPath={getFolderPath(memo.folder_id)} onSelect={() => toggleSelected(memo.id)} onMenu={() => setActiveMenu((current) => current === memo.id ? null : memo.id)} onDragStart={(event) => startDragMemo(memo, event)} onDragEnd={endDrag} onMove={openMoveMemo} onDuplicate={duplicateMemo} onDelete={deleteMemo} onForceDelete={forceDeleteMemo} /></li>)}</ul><Pagination links={memos.links} />
                        </> : <EmptyState filtered={hasFilter} trash={Boolean(filters.trash)} folder={Boolean(filters.folder)} />}
                    </section>
                </div>
            </div>}
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
            <ConfirmModal show={Boolean(deleteFolderTarget)} title={'Supprimer « ' + (deleteFolderTarget?.name ?? '') + ' » ?'} description="Les fiches seront conservées mais retirées de ce dossier. Les sous-dossiers remonteront d'un niveau." confirmLabel="Supprimer le dossier" onClose={() => setDeleteFolderTarget(null)} onConfirm={confirmDeleteFolder} />
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

function FolderNavItem({ folder, active, onCreateChild, onRename, onMove, onDelete, onMoveUp, onMoveDown, activeMenu, onMenu, isDragging, isDropTarget, onDragStartFolder, onDragEnd, onDragOverTarget, onDropReorder, onDropNest, canDrop }) {
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
                <div className="flex gap-1 px-1 py-1">
                    <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onMoveUp(); }} className="flex flex-1 items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-[11px] text-slate-300 hover:bg-white/[0.05]" title="Monter d'un rang"><ArrowUp size={12} /> Monter</button>
                    <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onMoveDown(); }} className="flex flex-1 items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-[11px] text-slate-300 hover:bg-white/[0.05]" title="Descendre d'un rang"><ArrowDown size={12} /> Descendre</button>
                </div>
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onDelete(folder); }} className="block w-full rounded-lg px-3 py-2 text-left text-xs text-red-300 hover:bg-red-400/[0.08]">Supprimer</button>
            </div>}
        </div>
    </div>;
}

function ViewButton({ active, onClick, icon: Icon, label }) {
    return <button type="button" onClick={onClick} aria-label={label} aria-pressed={active} className={'flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-200 ' + (active ? 'bg-white/[0.07] text-white' : 'text-slate-600 hover:text-slate-300')}><Icon size={15} /></button>;
}

function formatMemoDate(value) {
    if (!value) return '';
    const date = new Date(value);
    const today = new Date();
    const startOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    const days = Math.round((startOfDay(today) - startOfDay(date)) / 86400000);
    if (days === 0) return 'Aujourd’hui';
    if (days === 1) return 'Hier';
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', ...(date.getFullYear() !== today.getFullYear() ? { year: 'numeric' } : {}) });
}

// Carte d'une fiche. Anatomie identique en liste et en grille sur mobile
// (en-tête d'actions, titre pleine largeur, extrait, pied) ; en liste à partir
// de « sm », l'en-tête passe à gauche et les actions à droite.
function MemoCard({ memo, grid, trash = false, selected = false, menuOpen = false, folderPath = [], onSelect, onMenu, onDragStart, onDragEnd, onMove, onDuplicate, onDelete, onForceDelete }) {
    const tags = memo.tags ?? [];
    const visibleTags = tags.slice(0, grid ? 3 : 4);
    const hiddenTags = tags.length - visibleTags.length;
    const date = formatMemoDate(memo.updated_at);

    const layout = grid
        ? 'h-full grid-cols-[minmax(0,1fr)_auto] grid-rows-[auto_1fr_auto] [grid-template-areas:"lead_actions"_"body_body"_"foot_foot"]'
        : 'grid-cols-[minmax(0,1fr)_auto] [grid-template-areas:"lead_actions"_"body_body"_"foot_foot"] sm:grid-cols-[auto_minmax(0,1fr)_auto] sm:[grid-template-areas:"lead_body_actions"_"lead_foot_actions"] sm:gap-x-4';

    return <article
        data-memo-card
        draggable
        onDragStart={(event) => onDragStart(event)}
        onDragEnd={onDragEnd}
        onContextMenu={(event) => { event.preventDefault(); onMenu(); }}
        className={
            'group relative grid gap-x-3 rounded-2xl border bg-[#0D1725] p-4 transition-[border-color,background-color,box-shadow,transform] duration-200 ease-out motion-reduce:transition-none '
            + 'hover:-translate-y-0.5 hover:bg-[#0F1B2B] hover:shadow-[0_18px_40px_-24px_rgba(0,0,0,.9)] active:translate-y-0 active:scale-[.995] motion-reduce:hover:translate-y-0 '
            + 'has-[a:focus-visible]:ring-2 has-[a:focus-visible]:ring-[#FF6A00]/50 '
            + (menuOpen ? 'z-50 ' : 'z-0 ')
            + (selected ? 'border-[#FF6A00]/50 bg-[#FF6A00]/[0.04] ring-1 ring-[#FF6A00]/25 ' : 'border-white/[0.07] hover:border-[#FF6A00]/25 ')
            + layout
        }
    >
        {/* En-tête : sélection + icône de la fiche */}
        <div className={'flex items-center gap-2.5 [grid-area:lead] ' + (grid ? '' : 'sm:self-start')}>
            <button
                type="button"
                onClick={(event) => { event.stopPropagation(); onSelect(); }}
                aria-pressed={selected}
                aria-label={(selected ? 'Désélectionner « ' : 'Sélectionner « ') + memo.title + ' »'}
                className={'relative z-10 -ml-1.5 flex h-9 w-8 items-center justify-center rounded-lg transition hover:bg-white/[0.05] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#FF6A00]/60 ' + (selected ? 'opacity-100' : 'opacity-100 sm:opacity-40 sm:group-hover:opacity-100 sm:focus-visible:opacity-100')}
            >
                {selected
                    ? <span className="flex h-[18px] w-[18px] items-center justify-center rounded-[5px] bg-[#FF6A00] text-[#08111F]"><Check size={12} strokeWidth={3} /></span>
                    : <span className="h-[18px] w-[18px] rounded-[5px] border border-white/25" />}
            </button>
            <span aria-hidden="true" className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/[0.06] bg-[#08111F] text-[19px] leading-none shadow-[inset_0_1px_0_rgba(255,255,255,.04)] transition-transform duration-200 group-hover:scale-105 motion-reduce:group-hover:scale-100">
                {memo.icon || '📝'}
            </span>
        </div>

        {/* Actions : favori / restauration + menu */}
        <div className={'flex items-center gap-0.5 [grid-area:actions] ' + (grid ? '' : 'sm:self-start')}>
            {trash
                ? <button type="button" onClick={() => router.post('/memos/' + memo.id + '/restore')} className="relative z-10 rounded-lg border border-white/[0.06] px-2.5 py-1.5 text-[11px] font-semibold text-[#FF8A3D] hover:bg-[#FF6A00]/10">Restaurer</button>
                : <FavoriteButton memo={memo} />}
            <div className="relative">
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(); }} aria-haspopup="menu" aria-expanded={menuOpen} className={'relative z-10 flex h-9 w-9 items-center justify-center rounded-xl transition hover:bg-white/[0.06] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#FF6A00]/60 ' + (menuOpen ? 'bg-white/[0.06] text-white' : 'text-slate-500')} aria-label={'Actions de « ' + memo.title + ' »'}><MoreHorizontal size={18} /></button>
                {menuOpen && <MemoMenu memo={memo} trash={trash} onMove={onMove} onDuplicate={onDuplicate} onDelete={onDelete} onForceDelete={onForceDelete} />}
            </div>
        </div>

        {/* Corps : le titre est l'information principale */}
        <div className={'min-w-0 [grid-area:body] ' + (grid ? 'mt-3' : 'mt-3 sm:mt-0')}>
            <h3 className={'font-semibold tracking-[-0.01em] text-white ' + (grid ? 'line-clamp-3 text-base leading-[1.4]' : 'line-clamp-3 text-[15px] leading-[1.4] sm:line-clamp-2')}>
                <Link
                    href={'/memos/' + memo.id}
                    draggable={false}
                    title={memo.title}
                    className="outline-none transition-colors duration-200 after:absolute after:inset-0 after:rounded-2xl after:content-[''] group-hover:text-[#FFE2CC]"
                >
                    {memo.title}
                </Link>
            </h3>
            {folderPath.length > 0 && <p className="mt-1.5 flex min-w-0 items-center gap-1 text-[11px] font-medium text-[#FF8A3D]/85">
                <Folder size={11} aria-hidden="true" className="shrink-0" />
                <span className="truncate">{folderPath.map((folder) => folder.name).join(' / ')}</span>
            </p>}
            {memo.excerpt && <p className={'mt-1.5 text-[13px] leading-5 text-slate-400 ' + (grid ? 'line-clamp-3' : 'line-clamp-2 sm:line-clamp-1')}>{memo.excerpt}</p>}
        </div>

        {/* Pied : tags + date */}
        <div className={'flex min-w-0 items-end gap-2 [grid-area:foot] ' + (grid ? 'mt-4 border-t border-white/[0.06] pt-3' : 'mt-3 sm:mt-2')}>
            <div className="flex min-w-0 flex-1 flex-wrap gap-1.5">
                {visibleTags.map((tag) => <TagBadge key={tag.id} name={tag.name} />)}
                {hiddenTags > 0 && <span className="rounded-full px-1.5 py-1 text-[11px] font-medium text-slate-500" title={tags.slice(visibleTags.length).map((tag) => tag.name).join(', ')}>+{hiddenTags}</span>}
            </div>
            {date && <time dateTime={memo.updated_at} className="shrink-0 pb-1 text-[11px] font-medium tabular-nums text-slate-500">{date}</time>}
        </div>
    </article>;
}

function MemoMenu({ memo, trash, onMove, onDuplicate, onDelete, onForceDelete }) {
    const ref = useRef(null);
    useMenuPop(ref);
    const item = 'block w-full rounded-lg px-3 py-2.5 text-left text-xs transition-colors';

    return <div ref={ref} role="menu" className="absolute right-0 top-10 z-30 w-52 overflow-hidden rounded-xl border border-white/[0.08] bg-[#0D1725] p-1 shadow-[0_20px_50px_rgba(0,0,0,.5)]" onClick={(event) => event.stopPropagation()}>
        <Link role="menuitem" href={'/memos/' + memo.id} className={item + ' text-slate-300 hover:bg-white/[0.05]'}>Ouvrir</Link>
        {!trash && <>
            <Link role="menuitem" href={'/memos/' + memo.id + '/edit'} className={item + ' text-slate-300 hover:bg-white/[0.05]'}>Modifier</Link>
            <button role="menuitem" type="button" onClick={() => onMove(memo)} className={item + ' text-slate-300 hover:bg-white/[0.05]'}>Déplacer</button>
            <button role="menuitem" type="button" onClick={() => onDuplicate(memo)} className={item + ' text-slate-300 hover:bg-white/[0.05]'}>Dupliquer</button>
            <div className="my-1 h-px bg-white/[0.06]" />
            <button role="menuitem" type="button" onClick={() => onDelete(memo)} className={item + ' text-red-300 hover:bg-red-400/[0.08]'}>Mettre à la corbeille</button>
        </>}
        {trash && <>
            <button role="menuitem" type="button" onClick={() => router.post('/memos/' + memo.id + '/restore')} className={item + ' text-[#FF8A3D] hover:bg-[#FF6A00]/10'}>Restaurer</button>
            <button role="menuitem" type="button" onClick={() => onForceDelete(memo)} className={item + ' text-red-300 hover:bg-red-400/[0.08]'}>Supprimer définitivement</button>
        </>}
    </div>;
}

// ─── Vue mobile : artboard « Mobile — Fiches » (Claude Design), reproduit à l'identique ───
function useIsDesktop() {
    const query = '(min-width: 1024px)';
    const [desktop, setDesktop] = useState(() => typeof window !== 'undefined' && window.matchMedia(query).matches);
    useEffect(() => {
        const media = window.matchMedia(query);
        const onChange = () => setDesktop(media.matches);
        media.addEventListener?.('change', onChange);
        return () => media.removeEventListener?.('change', onChange);
    }, []);
    return desktop;
}

function plural(count, one, many) {
    return count + ' ' + (count > 1 ? many : one);
}

function monogram(title) {
    const match = String(title ?? '').match(/[\p{L}\p{N}]/u);
    return match ? match[0].toUpperCase() : '#';
}

function initials(name) {
    return String(name ?? '').trim().split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]?.toUpperCase()).join('') || 'DR';
}

function MobileMemos({ user, items, memos, counts, folders, tags, filters, hasFilter, query, setQuery, onSearch, onClearSearch, getFolderPath, loading, onOpenLibrary, onActions }) {
    const listRef = useRef(null);
    const contentKey = items.map((memo) => memo.id).join(',') + '|' + (filters.folder ?? '') + '|' + (filters.q ?? '') + '|' + (filters.tag ?? '') + '|' + (filters.favorites ? 'f' : '') + (filters.trash ? 't' : '');
    useMemoListMotion(listRef, 'mobile', contentKey);

    const chips = [
        ...(filters.trash ? [{ key: 'trash', label: 'Corbeille', href: listUrl({ trash: true }), active: true }] : []),
        ...(filters.recent ? [{ key: 'recent', label: 'Récents', href: listUrl({ recent: true }), active: true }] : []),
        { key: 'all', label: 'Toutes', href: '/memos', active: !hasFilter || (Boolean(filters.q) && !filters.tag && !filters.folder && !filters.favorites && !filters.trash && !filters.recent) },
        { key: 'fav', label: 'Favoris', href: listUrl({ favorites: true }), active: Boolean(filters.favorites) },
        ...folders.map((folder) => ({ key: 'folder-' + folder.id, label: folder.name, href: listUrl({ folder: folder.id }), active: Number(filters.folder) === folder.id })),
        ...tags.map((tag) => ({ key: 'tag-' + tag.id, label: tag.name, href: listUrl({ tag: tag.slug }), active: filters.tag === tag.slug })),
    ];

    return <div className="-mx-4 -mt-5 flex flex-col font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] sm:-mx-6 sm:-mt-6">
        <header className="flex flex-col gap-3.5 px-5 pt-[18px]">
            <div className="flex items-center gap-2.5">
                <div className="flex flex-1 flex-col gap-0.5">
                    <h1 className="m-0 font-['Manrope',sans-serif] text-[30px] font-extrabold leading-[1.1] tracking-[-0.03em]">Fiches mémo</h1>
                    <span className="text-sm text-[var(--dr-text-2)]">{plural(counts.total ?? 0, 'fiche', 'fiches')} · {plural(counts.favorites ?? 0, 'favori', 'favoris')}</span>
                </div>
                <button type="button" onClick={onOpenLibrary} aria-label="Dossiers" aria-haspopup="dialog" className="flex h-11 w-11 items-center justify-center rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] text-[var(--dr-text-2)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinejoin="round" aria-hidden="true"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                </button>
                <button type="button" onClick={() => window.dispatchEvent(new Event('devroad:appearance'))} aria-label="Profil et apparence" aria-haspopup="dialog" className="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--dr-accent)] text-sm font-bold text-[var(--dr-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]">
                    {initials(user?.name)}
                </button>
            </div>

            <form onSubmit={onSearch} role="search" className="m-0">
                <label className="flex h-[46px] items-center gap-2.5 rounded-[14px] border border-[var(--dr-border)] bg-[var(--dr-field)] px-3.5 text-[var(--dr-text-3)]">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="M20 20l-3.5-3.5" /></svg>
                    <input data-memo-search data-dr-native type="search" value={query} onChange={(event) => setQuery(event.target.value)} aria-label="Rechercher dans les fiches" placeholder="Rechercher une commande, une notion…" className="min-w-0 flex-1 appearance-none border-0 bg-transparent p-0 text-base text-[var(--dr-text)] outline-none placeholder:text-[var(--dr-text-3)] focus:ring-0 [&::-webkit-search-cancel-button]:hidden [&::-webkit-search-decoration]:hidden" />
                    {query && <button type="button" onClick={onClearSearch} aria-label="Effacer la recherche" className="-mr-1.5 flex h-8 w-8 items-center justify-center rounded-lg text-[var(--dr-text-3)]"><X size={15} /></button>}
                </label>
            </form>

            <nav aria-label="Filtrer" className="dr-scrollbar-none -mx-5 flex gap-2 overflow-x-auto px-5">
                {chips.map((chip) => <Link
                    key={chip.key}
                    href={chip.href}
                    preserveScroll
                    aria-current={chip.active ? 'page' : undefined}
                    className={'flex h-9 shrink-0 items-center whitespace-nowrap rounded-full px-3.5 text-sm ' + (chip.active
                        ? 'bg-[var(--dr-text)] font-semibold text-[var(--dr-bg)]'
                        : 'border border-[var(--dr-border)] bg-[var(--dr-surface)] text-[var(--dr-text-2)]')}
                >{chip.label}</Link>)}
            </nav>
        </header>

        <section aria-label="Fiches" className="flex flex-col gap-3 px-5 pt-4">
            {loading ? <MemoSkeletons grid={false} /> : items.length === 0
                ? <EmptyState filtered={hasFilter} trash={Boolean(filters.trash)} folder={Boolean(filters.folder)} />
                : <ul ref={listRef} className="m-0 flex list-none flex-col gap-3 p-0">
                    {items.map((memo) => <li key={memo.id}><MobileMemoCard memo={memo} trash={Boolean(filters.trash)} folderPath={getFolderPath(memo.folder_id)} onActions={onActions} /></li>)}
                </ul>}
            {!loading && items.length > 0 && <Pagination links={memos.links} />}
        </section>
    </div>;
}

function MobileMemoCard({ memo, trash, folderPath, onActions }) {
    const pressTimer = useRef(null);
    const longPressed = useRef(false);
    const tags = memo.tags ?? [];
    const visibleTags = tags.slice(0, 2);
    const date = formatMemoDate(memo.updated_at);

    // Appui long (ou clic droit) : actions de la fiche, sans rien ajouter de visible à la carte.
    function startPress() {
        longPressed.current = false;
        clearTimeout(pressTimer.current);
        pressTimer.current = setTimeout(() => {
            longPressed.current = true;
            navigator.vibrate?.(12);
            onActions(memo);
        }, 480);
    }
    function cancelPress() { clearTimeout(pressTimer.current); }

    return <article
        data-memo-card
        onPointerDown={startPress}
        onPointerUp={cancelPress}
        onPointerLeave={cancelPress}
        onPointerCancel={cancelPress}
        onContextMenu={(event) => { event.preventDefault(); cancelPress(); onActions(memo); }}
        onClickCapture={(event) => { if (longPressed.current) { event.preventDefault(); event.stopPropagation(); longPressed.current = false; } }}
        className="relative flex select-none flex-col gap-2.5 rounded-[18px] border border-[var(--dr-border)] bg-[var(--dr-surface)] p-4 shadow-[var(--dr-shadow)] [-webkit-touch-callout:none] has-[a:focus-visible]:ring-2 has-[a:focus-visible]:ring-[var(--dr-accent)]"
    >
        <div className="flex items-start gap-3">
            <span aria-hidden="true" className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--dr-field)] font-['JetBrains_Mono',ui-monospace,monospace] text-[15px] text-[var(--dr-accent-text)]">{monogram(memo.title)}</span>
            <div className="flex min-w-0 flex-1 flex-col gap-1">
                <Link href={'/memos/' + memo.id} draggable={false} className="text-base font-semibold leading-[1.35] text-[var(--dr-text)] outline-none after:absolute after:inset-0 after:rounded-[18px] after:content-['']">{memo.title}</Link>
                {folderPath.length > 0 && <span className="text-xs font-semibold text-[var(--dr-accent-text)]">{folderPath.map((folder) => folder.name).join(' / ')}</span>}
            </div>
            {trash
                ? <button type="button" onClick={() => router.post('/memos/' + memo.id + '/restore')} className="relative z-10 -mr-1 rounded-lg px-2 py-1.5 text-xs font-semibold text-[var(--dr-accent-text)]">Restaurer</button>
                : <FavoriteButton memo={memo} variant="design" />}
        </div>
        {memo.excerpt && <p className="m-0 line-clamp-3 text-sm leading-[1.5] text-[var(--dr-text-2)]">{memo.excerpt}</p>}
        <div className="flex items-center gap-1.5">
            {visibleTags.map((tag) => <span key={tag.id} className="rounded-full bg-[var(--dr-field)] px-2.5 py-1 text-xs text-[var(--dr-text-2)]">{tag.name}</span>)}
            {tags.length > visibleTags.length && <span className="px-1 text-xs text-[var(--dr-text-3)]">+{tags.length - visibleTags.length}</span>}
            {date && <time dateTime={memo.updated_at} className="ml-auto text-xs text-[var(--dr-text-3)]">{date}</time>}
        </div>
    </article>;
}

function BottomSheet({ title, onClose, children }) {
    const panelRef = useRef(null);
    useEffect(() => {
        panelRef.current?.querySelector('button, a')?.focus();
        const onKey = (event) => { if (event.key === 'Escape') onClose(); };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);
    return <div className="fixed inset-0 z-[80] flex items-end justify-center" role="presentation">
        <div aria-hidden="true" onClick={onClose} className="absolute inset-0 bg-[var(--dr-scrim)]" />
        <section ref={panelRef} role="dialog" aria-modal="true" aria-label={title} className="relative flex max-h-[85dvh] w-full max-w-[430px] flex-col gap-3 overflow-y-auto rounded-t-[28px] border-t border-[var(--dr-border-2)] bg-[var(--dr-surface)] px-5 pb-[calc(24px+env(safe-area-inset-bottom))] pt-[10px] font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)]">
            <span aria-hidden="true" className="h-[5px] w-10 shrink-0 self-center rounded-full bg-[var(--dr-border-2)]" />
            <h2 className="m-0 font-['Manrope',sans-serif] text-2xl font-extrabold tracking-[-0.02em]">{title}</h2>
            {children}
        </section>
    </div>;
}

function SheetAction({ onClick, href, danger = false, children }) {
    const className = 'flex min-h-[48px] w-full items-center rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-field)] px-4 text-left text-[15px] font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] ' + (danger ? 'text-[var(--dr-danger)]' : 'text-[var(--dr-text)]');
    return href
        ? <Link href={href} className={className}>{children}</Link>
        : <button type="button" onClick={onClick} className={className}>{children}</button>;
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