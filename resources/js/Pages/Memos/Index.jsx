import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react';
import { ArrowDown, ArrowUp, Bookmark, Check, ChevronDown, FileText, Folder, FolderPlus, LayoutGrid, List, MoreHorizontal, Pencil, Plus, Search, Sparkles, Trash2, X } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import Pagination from '@/Components/Ui/Pagination';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import Modal from '@/Components/Ui/Modal';
import ConfirmModal from '@/Components/Ui/ConfirmModal';
import useGsapScrollReveal from '@/hooks/useGsapScrollReveal';
import { useMemoListMotion, useMenuPop, prefersReducedMotion } from '@/Components/Memos/memoMotion';
import { AnimatePresence, Sheet, m, softSpring } from '@/Components/Ui/Motion';
import { gsap } from 'gsap';

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
    // Fiche gardée affichée pendant l'animation de fermeture du panneau d'actions.
    const [shownActionsMemo, setShownActionsMemo] = useState(null);
    useEffect(() => { if (actionsMemo) setShownActionsMemo(actionsMemo); }, [actionsMemo]);
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

    // Mise à la corbeille immédiate : la notification propose « Annuler » (plus rapide qu'une confirmation).
    function deleteMemo(memo) {
        setActiveMenu(null);
        router.delete('/memos/' + memo.id, { data: { stay: 1 }, preserveScroll: true });
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

    const tab = filters.trash ? 'trash' : filters.favorites ? 'favorites' : filters.recent ? 'recent' : (!filters.folder && !filters.tag ? 'all' : null);
    const TABS = [
        { key: 'all', label: 'Toutes', href: listUrl({ q: filters.q, sort: filters.sort }) },
        { key: 'favorites', label: 'Favoris', href: listUrl({ favorites: true, q: filters.q, sort: filters.sort }) },
        { key: 'recent', label: 'Récents', href: listUrl({ recent: true, q: filters.q, sort: filters.sort }) },
        { key: 'trash', label: 'Corbeille', href: listUrl({ trash: true, sort: filters.sort }) },
    ];
    const shownTotal = memos?.total ?? items.length;
    const subtitle = filters.q
        ? plural(shownTotal, 'fiche', 'fiches') + ' pour « ' + filters.q + ' »'
        : tab === 'all'
            ? plural(counts.total ?? shownTotal, 'fiche', 'fiches') + ' · ' + (counts.favorites ?? 0) + ' en favoris'
            : plural(shownTotal, 'fiche', 'fiches');

    // Liens de la barre latérale : « Voir les N dossiers » et « Nouveau dossier ».
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const openLibrary = params.has('dossiers');
        const newFolder = params.has('nouveau-dossier');
        if (!openLibrary && !newFolder) return;
        if (openLibrary) setMobileLibraryOpen(true);
        if (newFolder) createFolder();
        params.delete('dossiers');
        params.delete('nouveau-dossier');
        const rest = params.toString();
        // Après l'initialisation de l'historique par Inertia, sinon l'URL est rétablie.
        setTimeout(() => window.history.replaceState(window.history.state, '', window.location.pathname + (rest ? '?' + rest : '')), 0);
    }, []);

    const libraryPanel = (
                        <div className="rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-3 lg:border-0 lg:bg-transparent lg:p-0">
                            <div className="mb-3 flex items-center gap-2 px-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]"><Folder size={16} /></div>
                                <div className="min-w-0"><p className="text-sm font-semibold text-[var(--dr-text)]">Mon espace</p><p className="text-xs text-[var(--dr-text-3)]">{counts.total ?? 0} fiches</p></div>
                            </div>
                            <div className="space-y-1">
                                <NavItem href="/memos" active={!hasFilter} icon={FileText} label="Toutes les fiches" count={counts.total} />
                                <NavItem href={listUrl({ favorites: true })} active={Boolean(filters.favorites)} icon={Bookmark} label="Favoris" count={counts.favorites} />
                                <NavItem href={listUrl({ trash: true })} active={Boolean(filters.trash)} icon={Trash2} label="Corbeille" count={counts.trash} />
                                <NavItem href={listUrl({ recent: true })} active={Boolean(filters.recent)} icon={Sparkles} label="Récents" count={counts.recent} />
                            </div>
                            <div className="mt-5 border-t border-[var(--dr-border)] pt-4">
                                <div
                                    onDragOver={(event) => { if (dragged) { event.preventDefault(); setDropTarget('root'); } }}
                                    onDragLeave={() => setDropTarget((current) => (current === 'root' ? null : current))}
                                    onDrop={(event) => { event.preventDefault(); dropOnRoot(event); }}
                                    className={'mb-2 flex items-center justify-between rounded-lg px-2 py-1 transition ' + (dropTarget === 'root' ? 'bg-[var(--dr-accent-soft)] ring-1 ring-[var(--dr-accent)]' : '')}
                                >
                                    <span className="text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-text-3)]">Dossiers</span>
                                    <button type="button" onClick={() => createFolder()} className="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--dr-text-3)] transition hover:bg-[var(--dr-field)] hover:text-[var(--dr-accent-text)]" title="Nouveau dossier"><FolderPlus size={14} /></button>
                                </div>
                                <div className="space-y-1">
                                    {buildFolderTree(folders).map((folder) => <FolderNavItem key={folder.id} folder={folder} active={Number(filters.folder) === folder.id} onCreateChild={createFolder} onRename={renameFolder} onMove={openMoveFolder} onDelete={deleteFolder} onMoveUp={() => moveFolderByOffset(folder, -1)} onMoveDown={() => moveFolderByOffset(folder, 1)} activeMenu={activeFolderMenu} onMenu={(id) => setActiveFolderMenu((current) => current === id ? null : id)} isDragging={dragged?.type === 'folder' && dragged.id === folder.id} isDropTarget={dropTarget === folder.id} onDragStartFolder={startDragFolder} onDragEnd={endDrag} onDragOverTarget={() => setDropTarget(folder.id)} onDropReorder={reorderFolder} onDropNest={dropOnFolder} canDrop={Boolean(dragged)} />)}
                                    {folders.length === 0 && <button type="button" onClick={() => createFolder()} className="flex w-full items-center gap-2 rounded-xl px-2.5 py-2 text-left text-sm text-[var(--dr-text-3)] hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)]"><FolderPlus size={14} />Créer ton premier dossier</button>}
                                </div>
                            </div>
                            {tags.length > 0 && <div className="mt-5 border-t border-[var(--dr-border)] pt-4">
                                <div className="mb-2 flex items-center justify-between px-2"><span className="text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-text-3)]">Collections</span><span className="text-xs text-[var(--dr-text-3)]">{tags.length}</span></div>
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
            <Sheet open={mobileLibraryOpen} onClose={() => setMobileLibraryOpen(false)} label="Dossiers et collections" desktopCentered className="max-h-[85dvh] gap-3 overflow-y-auto pb-[calc(24px+env(safe-area-inset-bottom))] lg:max-w-[480px] lg:rounded-[24px] lg:border lg:pb-6">
                <div className="flex items-center justify-between gap-3">
                    <h2 className="m-0 font-['Manrope',sans-serif] text-2xl font-extrabold tracking-[-0.02em]">{isDesktop ? 'Dossiers et collections' : 'Dossiers'}</h2>
                    {isDesktop && <button type="button" onClick={() => setMobileLibraryOpen(false)} aria-label="Fermer" className="flex h-9 w-9 items-center justify-center rounded-[10px] text-[var(--dr-text-3)] hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)]"><X size={18} /></button>}
                </div>
                <div onClick={(event) => { if (event.target.closest('a')) setMobileLibraryOpen(false); }}>{libraryPanel}</div>
            </Sheet>
            <Sheet open={!isDesktop && Boolean(actionsMemo)} onClose={() => setActionsMemo(null)} label={shownActionsMemo?.title} className="gap-3 pb-[calc(24px+env(safe-area-inset-bottom))]">
                {shownActionsMemo && <>
                <h2 className="m-0 line-clamp-2 font-['Manrope',sans-serif] text-2xl font-extrabold tracking-[-0.02em]">{shownActionsMemo.title}</h2>
                <div className="flex flex-col gap-2">
                    {filters.trash ? <>
                        <SheetAction onClick={() => { setActionsMemo(null); router.post('/memos/' + shownActionsMemo.id + '/restore'); }}>Restaurer</SheetAction>
                        <SheetAction danger onClick={() => { const memo = shownActionsMemo; setActionsMemo(null); forceDeleteMemo(memo); }}>Supprimer définitivement</SheetAction>
                    </> : <>
                        <SheetAction href={'/memos/' + shownActionsMemo.id}>Ouvrir</SheetAction>
                        <SheetAction href={'/memos/' + shownActionsMemo.id + '/edit'}>Modifier</SheetAction>
                        <SheetAction onClick={() => { const memo = shownActionsMemo; setActionsMemo(null); openMoveMemo(memo); }}>Déplacer</SheetAction>
                        <SheetAction onClick={() => { const memo = shownActionsMemo; setActionsMemo(null); duplicateMemo(memo); }}>Dupliquer</SheetAction>
                        <SheetAction danger onClick={() => { const memo = shownActionsMemo; setActionsMemo(null); deleteMemo(memo); }}>Mettre à la corbeille</SheetAction>
                    </>}
                </div>
                </>}
            </Sheet>
            {isDesktop && <div ref={animationRef} className="flex flex-col gap-7 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)]">
                {/* En-tête — artboard « Desktop — Fiches » */}
                <div data-gsap-reveal className="flex flex-wrap items-end justify-between gap-4">
                    <div className="flex min-w-0 flex-col gap-1.5">
                        {activeFolderPath.length > 0 && <nav aria-label="Fil d'Ariane" className="flex flex-wrap items-center gap-1 text-[13px] text-[var(--dr-text-3)]">
                            <Link href="/memos" className="hover:text-[var(--dr-accent-text)]">Fiches</Link>
                            {activeFolderPath.map((folder) => <span key={folder.id} className="flex items-center gap-1"><span aria-hidden="true">/</span><Link href={listUrl({ folder: folder.id, sort: filters.sort })} className="hover:text-[var(--dr-accent-text)]">{folder.name}</Link></span>)}
                        </nav>}
                        <h1 className="m-0 truncate font-['Manrope',sans-serif] text-[38px] font-extrabold leading-[1.05] tracking-[-0.03em]">{tab === 'all' ? 'Fiches mémo' : activeFolder}</h1>
                        <span className="text-[15px] text-[var(--dr-text-2)]">{subtitle}</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div role="tablist" aria-label="Filtrer" className="flex gap-1 rounded-xl bg-[var(--dr-field)] p-1">
                            {TABS.map((item) => {
                                const active = tab === item.key;
                                return <Link key={item.key} role="tab" aria-selected={active} href={item.href} preserveScroll className={'relative flex h-[34px] items-center rounded-[9px] px-3.5 text-[13px] transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] ' + (active ? 'font-semibold text-[var(--dr-text)]' : 'text-[var(--dr-text-2)] hover:text-[var(--dr-text)]')}>
                                    {active && <m.span layoutId="memos-tab" transition={softSpring} aria-hidden="true" className="absolute inset-0 rounded-[9px] bg-[var(--dr-surface)] shadow-[var(--dr-shadow)]" />}
                                    <span className="relative">{item.label}</span>
                                    {item.key === 'trash' && counts.trash > 0 && <span className="relative ml-1.5 text-[11px] tabular-nums text-[var(--dr-text-3)]">{counts.trash}</span>}
                                </Link>;
                            })}
                        </div>
                        <MotionLink whileTap={{ scale: 0.97 }} href={filters.folder ? '/memos/create?folder=' + filters.folder : '/memos/create'} className="flex h-[42px] items-center gap-2 rounded-xl bg-[var(--dr-accent)] px-4 text-sm font-bold text-[var(--dr-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]">
                            <Plus size={16} strokeWidth={2.4} aria-hidden="true" />Nouvelle fiche
                        </MotionLink>
                    </div>
                </div>

                {/* Barre d'outils : recherche dans les fiches, tri, affichage, dossiers */}
                <div data-gsap-reveal className="-mt-2 flex flex-wrap items-center gap-2">
                    <form onSubmit={submitSearch} role="search" className="m-0 flex min-w-[220px] max-w-[340px] flex-1">
                        <label className="flex h-10 w-full items-center gap-2 rounded-xl border border-[var(--dr-border)] bg-[var(--dr-field)] px-3 text-[var(--dr-text-3)] focus-within:border-[var(--dr-accent)]">
                            <Search size={15} aria-hidden="true" />
                            <input data-memo-search data-dr-native type="search" value={query} onChange={(event) => setQuery(event.target.value)} aria-label="Filtrer les fiches" placeholder="Filtrer ces fiches…  ( / )" className="min-w-0 flex-1 appearance-none border-0 bg-transparent p-0 text-sm text-[var(--dr-text)] outline-none placeholder:text-[var(--dr-text-3)] focus:ring-0 [&::-webkit-search-cancel-button]:hidden" />
                            {query && <button type="button" onClick={clearSearch} aria-label="Effacer la recherche" className="flex h-6 w-6 items-center justify-center rounded-md hover:bg-[var(--dr-surface)] hover:text-[var(--dr-text)]"><X size={14} /></button>}
                        </label>
                    </form>
                    <button type="button" onClick={() => setMobileLibraryOpen(true)} className="flex h-10 items-center gap-2 rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-3.5 text-[13px] font-semibold text-[var(--dr-text)] hover:border-[var(--dr-border-2)]">
                        <Folder size={15} className="text-[var(--dr-accent-text)]" aria-hidden="true" />Dossiers et collections
                    </button>
                    {filters.tag && <Link href={listUrl({ folder: filters.folder, sort: filters.sort })} className="flex h-10 items-center gap-1.5 rounded-xl bg-[var(--dr-accent-soft)] px-3 text-[13px] font-semibold text-[var(--dr-accent-text)]" aria-label="Retirer le filtre de collection">#{tags.find((tag) => tag.slug === filters.tag)?.name ?? filters.tag}<X size={13} /></Link>}
                    <div className="ml-auto flex items-center gap-2">
                        {filters.trash && counts.trash > 0 && <button type="button" onClick={() => setEmptyTrashOpen(true)} className="h-10 rounded-xl px-3 text-[13px] font-semibold text-[var(--dr-danger)] hover:bg-[var(--dr-field)]">Vider la corbeille</button>}
                        <select data-dr-native value={filters.sort ?? 'updated_desc'} onChange={(event) => changeSort(event.target.value)} className="h-10 rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] py-0 pl-3 pr-8 text-[13px] font-semibold text-[var(--dr-text)] outline-none focus:border-[var(--dr-accent)] focus:ring-0" aria-label="Trier les fiches">
                            <option value="updated_desc">Plus récentes</option><option value="updated_asc">Plus anciennes</option><option value="title_asc">A → Z</option><option value="title_desc">Z → A</option><option value="favorite">Favoris d'abord</option>
                        </select>
                        <div className="flex rounded-xl bg-[var(--dr-field)] p-1" role="group" aria-label="Affichage">
                            <ViewButton active={view === 'grid'} onClick={() => changeView('grid')} icon={LayoutGrid} label="Grille" />
                            <ViewButton active={view === 'list'} onClick={() => changeView('list')} icon={List} label="Liste" />
                        </div>
                    </div>
                </div>

                <section data-gsap-reveal aria-label="Fiches" className="-mt-2 min-w-0">
                    <AnimatePresence initial={false}>
                        {selectedIds.length > 0 && <m.div
                            initial={{ opacity: 0, y: -6, height: 0 }}
                            animate={{ opacity: 1, y: 0, height: 'auto' }}
                            exit={{ opacity: 0, y: -6, height: 0 }}
                            transition={softSpring}
                            className="overflow-hidden"
                        >
                            <div className="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-[var(--dr-border-2)] bg-[var(--dr-surface-2)] px-3 py-2 shadow-[var(--dr-shadow)]" role="toolbar" aria-label="Actions sur la sélection">
                                <label className="flex items-center gap-2 pr-2 text-[13px] font-semibold"><input type="checkbox" checked={selectedIds.length === items.length} onChange={toggleSelectAll} className="h-4 w-4 rounded border-[var(--dr-border-2)] bg-[var(--dr-field)] text-[var(--dr-accent)] focus:ring-[var(--dr-accent)]" />{plural(selectedIds.length, 'sélectionnée', 'sélectionnées')}</label>
                                <span className="h-5 w-px bg-[var(--dr-border)]" aria-hidden="true" />
                                {!filters.trash && <>
                                    <BulkButton onClick={openBulkMove}>Déplacer</BulkButton>
                                    <BulkButton onClick={() => bulkAction('favorite')}>Ajouter aux favoris</BulkButton>
                                    <BulkButton danger onClick={() => bulkAction('delete')}>Mettre à la corbeille</BulkButton>
                                </>}
                                {filters.trash && <>
                                    <BulkButton accent onClick={() => bulkAction('restore')}>Restaurer</BulkButton>
                                    <BulkButton danger onClick={() => setBulkDeleteOpen(true)}>Supprimer définitivement</BulkButton>
                                </>}
                                <button type="button" onClick={() => setSelectedIds([])} className="ml-auto flex h-8 w-8 items-center justify-center rounded-lg text-[var(--dr-text-3)] hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)]" aria-label="Annuler la sélection"><X size={15} /></button>
                            </div>
                        </m.div>}
                    </AnimatePresence>
                    {loading ? <MemoSkeletons grid={view === 'grid'} /> : items.length > 0 ? <>
                        <ul ref={listRef} className={view === 'grid' ? 'grid gap-4 [grid-template-columns:repeat(auto-fill,minmax(300px,1fr))]' : 'flex flex-col gap-2.5'}>
                            {items.map((memo) => <li key={memo.id} className="min-w-0"><MemoCard memo={memo} grid={view === 'grid'} trash={Boolean(filters.trash)} selected={selectedIds.includes(memo.id)} selecting={selectedIds.length > 0} menuOpen={activeMenu === memo.id} folderPath={getFolderPath(memo.folder_id)} onSelect={() => toggleSelected(memo.id)} onMenu={() => setActiveMenu((current) => current === memo.id ? null : memo.id)} onDragStart={(event) => startDragMemo(memo, event)} onDragEnd={endDrag} onMove={openMoveMemo} onDuplicate={duplicateMemo} onDelete={deleteMemo} onForceDelete={forceDeleteMemo} /></li>)}
                        </ul>
                        <Pagination links={memos.links} />
                    </> : <EmptyState filtered={hasFilter} trash={Boolean(filters.trash)} folder={Boolean(filters.folder)} />}
                </section>
            </div>}
            <Modal show={folderModal.open} onClose={() => !folderProcessing && setFolderModal((current) => ({ ...current, open: false }))} title={folderModal.mode === 'rename' ? 'Renommer le dossier' : (folderModal.parentId ? 'Créer un sous-dossier' : 'Créer un dossier')} description={folderModal.mode === 'rename' ? 'Modifie le nom sans toucher aux fiches.' : 'Organise tes fiches dans une arborescence claire.'} footer={<div className="flex justify-end gap-2"><button type="button" onClick={() => setFolderModal((current) => ({ ...current, open: false }))} className="rounded-xl border border-[var(--dr-border)] bg-[var(--dr-hover)] px-4 py-2.5 text-sm font-semibold text-[var(--dr-text-2)]">Annuler</button><button type="button" onClick={submitFolder} disabled={!folderName.trim() || folderProcessing} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[var(--dr-ink)] disabled:opacity-50">{folderProcessing ? 'Enregistrement...' : (folderModal.mode === 'rename' ? 'Renommer' : 'Créer le dossier')}</button></div>}>
                <label className="block"><span className="mb-2 block text-xs font-semibold text-[var(--dr-text-2)]">Nom du dossier</span><input autoFocus value={folderName} onChange={(event) => setFolderName(event.target.value)} onKeyDown={(event) => event.key === 'Enter' && submitFolder()} maxLength={120} placeholder="Ex. Laravel, React, DevOps..." className="h-11 w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] px-3 text-sm text-[var(--dr-text)] outline-none focus:border-[#FF6A00]/40" /></label>
            </Modal>
            <Modal show={bulkMoveOpen} onClose={() => !bulkProcessing && setBulkMoveOpen(false)} title="Déplacer les fiches sélectionnées" description={selectedIds.length + ' fiche(s) seront déplacée(s).'} footer={<div className="flex justify-end gap-2"><button type="button" onClick={() => setBulkMoveOpen(false)} className="rounded-xl border border-[var(--dr-border)] bg-[var(--dr-hover)] px-4 py-2.5 text-sm font-semibold text-[var(--dr-text-2)]">Annuler</button><button type="button" onClick={submitBulkMove} disabled={bulkProcessing} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[var(--dr-ink)]">Déplacer</button></div>}><select value={bulkMoveFolderId} onChange={(event) => setBulkMoveFolderId(event.target.value)} className="h-11 w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] px-3 text-sm text-[var(--dr-text)]"><option value="">Sans dossier — racine</option>{folderTree.map((folder) => <option key={folder.id} value={folder.id}>{'— '.repeat(folder.depth ?? 0)}{folder.name}</option>)}</select></Modal>

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

            <Modal show={Boolean(moveFolderTarget)} onClose={() => !moveFolderProcessing && setMoveFolderTarget(null)} title="Déplacer le dossier" description="Choisis son dossier parent, ou remets-le à la racine." footer={<div className="flex justify-end gap-2"><button type="button" onClick={() => setMoveFolderTarget(null)} className="rounded-xl border border-[var(--dr-border)] bg-[var(--dr-hover)] px-4 py-2.5 text-sm font-semibold text-[var(--dr-text-2)]">Annuler</button><button type="button" onClick={submitMoveFolder} disabled={moveFolderProcessing} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[var(--dr-ink)] disabled:opacity-50">{moveFolderProcessing ? 'Déplacement...' : 'Déplacer'}</button></div>}>
                <select value={moveFolderParentId} onChange={(event) => setMoveFolderParentId(event.target.value)} className="h-11 w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] px-3 text-sm text-[var(--dr-text)] outline-none focus:border-[#FF6A00]/40">
                    <option value="">Sans dossier — racine</option>
                    {folderTree.filter((candidate) => candidate.id !== moveFolderTarget?.id && !getFolderPath(candidate.id).some((ancestor) => ancestor.id === moveFolderTarget?.id)).map((folder) => <option key={folder.id} value={folder.id}>{'— '.repeat(folder.depth ?? 0)}{folder.name}</option>)}
                </select>
            </Modal>
            <ConfirmModal show={Boolean(deleteFolderTarget)} title={'Supprimer « ' + (deleteFolderTarget?.name ?? '') + ' » ?'} description="Les fiches seront conservées mais retirées de ce dossier. Les sous-dossiers remonteront d'un niveau." confirmLabel="Supprimer le dossier" onClose={() => setDeleteFolderTarget(null)} onConfirm={confirmDeleteFolder} />
            <Modal show={Boolean(moveMemoTarget)} onClose={() => !moveProcessing && setMoveMemoTarget(null)} title="Déplacer la fiche" description="Choisis le dossier de destination, ou remets-la à la racine." footer={<div className="flex justify-end gap-2"><button type="button" onClick={() => setMoveMemoTarget(null)} className="rounded-xl border border-[var(--dr-border)] bg-[var(--dr-hover)] px-4 py-2.5 text-sm font-semibold text-[var(--dr-text-2)]">Annuler</button><button type="button" onClick={moveMemo} disabled={moveProcessing} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[var(--dr-ink)] disabled:opacity-50">{moveProcessing ? 'Déplacement...' : 'Déplacer'}</button></div>}>
                <div className="rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] p-3"><p className="mb-2 truncate text-xs font-semibold text-[var(--dr-text)]">{moveMemoTarget?.icon ?? '📝'} {moveMemoTarget?.title}</p><select value={moveFolderId} onChange={(event) => setMoveFolderId(event.target.value)} className="h-11 w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-3 text-sm text-[var(--dr-text)] outline-none focus:border-[#FF6A00]/40"><option value="">Sans dossier — racine</option>{folders.map((folder) => <option key={folder.id} value={folder.id}>{'— '.repeat(folder.depth ?? 0)}{folder.name}</option>)}</select></div>
            </Modal>

        </AppLayout>
    );
}

function NavItem({ href, active, icon: Icon, label, count }) {
    return <Link href={href} preserveScroll className={'group flex h-10 items-center gap-2.5 rounded-[10px] px-2.5 text-sm transition-colors ' + (active ? 'bg-[var(--dr-accent-soft)] font-semibold text-[var(--dr-accent-text)]' : 'text-[var(--dr-text-2)] hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)]')}>
        <Icon size={16} aria-hidden="true" /><span className="min-w-0 flex-1 truncate">{label}</span>{typeof count === 'number' && <span className="text-xs tabular-nums text-[var(--dr-text-3)]">{count}</span>}
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
                (active ? 'bg-[var(--dr-accent-soft)]' : 'hover:bg-[var(--dr-field)]') +
                (isDragging ? ' opacity-40' : '') +
                (isDropTarget ? (nestHover ? ' bg-[var(--dr-accent-soft)] ring-1 ring-[var(--dr-accent)]' : ' border-t-2 border-[var(--dr-accent)]') : '')
            }
        >
            <Link href={listUrl({ folder: active ? null : folder.id })} className={'flex min-w-0 flex-1 items-center gap-2.5 rounded-[10px] px-2.5 py-2 text-sm ' + (active ? 'font-semibold text-[var(--dr-accent-text)]' : 'text-[var(--dr-text-2)] hover:text-[var(--dr-text)]')} style={{ paddingLeft: 10 + (folder.depth ?? 0) * 14 }}>
                <Folder size={15} aria-hidden="true" />
                <span className="min-w-0 flex-1 truncate">{folder.name}</span>
                {typeof folder.memos_count === 'number' && <span className="text-xs tabular-nums text-[var(--dr-text-3)]">{folder.memos_count}</span>}
            </Link>
            <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); }} className="flex h-7 w-7 items-center justify-center rounded-lg text-[var(--dr-text-3)] hover:bg-[var(--dr-surface)] hover:text-[var(--dr-text)] focus-visible:flex sm:hidden sm:group-hover:flex" title="Actions du dossier"><MoreHorizontal size={14} /></button>
            {activeMenu === folder.id && <div
                className="absolute right-1 top-9 z-[70] w-48 overflow-hidden rounded-2xl border border-[var(--dr-border-2)] bg-[var(--dr-surface-2)] p-1.5 shadow-[0_24px_60px_-20px_rgba(0,0,0,0.55)]"
                onMouseDown={(event) => event.stopPropagation()}
                onClick={(event) => event.stopPropagation()}
            >
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onCreateChild(folder.id); }} className="block w-full rounded-lg px-3 py-2 text-left text-sm text-[var(--dr-text)] hover:bg-[var(--dr-field)]">Nouveau sous-dossier</button>
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onRename(folder); }} className="block w-full rounded-lg px-3 py-2 text-left text-sm text-[var(--dr-text)] hover:bg-[var(--dr-field)]">Renommer</button>
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onMove(folder); }} className="block w-full rounded-lg px-3 py-2 text-left text-sm text-[var(--dr-text)] hover:bg-[var(--dr-field)]">Déplacer</button>
                <div className="flex gap-1 px-1 py-1">
                    <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onMoveUp(); }} className="flex flex-1 items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-xs text-[var(--dr-text)] hover:bg-[var(--dr-field)]" title="Monter d'un rang"><ArrowUp size={12} /> Monter</button>
                    <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onMoveDown(); }} className="flex flex-1 items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-xs text-[var(--dr-text)] hover:bg-[var(--dr-field)]" title="Descendre d'un rang"><ArrowDown size={12} /> Descendre</button>
                </div>
                <button type="button" onClick={(event) => { event.stopPropagation(); onMenu(folder.id); onDelete(folder); }} className="block w-full rounded-lg px-3 py-2 text-left text-sm text-[var(--dr-danger)] hover:bg-[var(--dr-field)]">Supprimer</button>
            </div>}
        </div>
    </div>;
}

function ViewButton({ active, onClick, icon: Icon, label }) {
    return <button type="button" onClick={onClick} aria-label={label} aria-pressed={active} title={label} className={'relative flex h-8 w-8 items-center justify-center rounded-[9px] transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] ' + (active ? 'text-[var(--dr-text)]' : 'text-[var(--dr-text-3)] hover:text-[var(--dr-text)]')}>
        {active && <m.span layoutId="memos-view" transition={softSpring} aria-hidden="true" className="absolute inset-0 rounded-[9px] bg-[var(--dr-surface)] shadow-[var(--dr-shadow)]" />}
        <Icon size={15} className="relative" />
    </button>;
}

function BulkButton({ onClick, danger = false, accent = false, children }) {
    return <button type="button" onClick={onClick} className={'h-8 rounded-lg px-3 text-[13px] font-semibold transition-colors hover:bg-[var(--dr-field)] ' + (danger ? 'text-[var(--dr-danger)]' : accent ? 'text-[var(--dr-accent-text)]' : 'text-[var(--dr-text)]')}>{children}</button>;
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

// Carte desktop — artboard « Desktop — Fiches » : monogramme, favori et menu
// en tête, titre 17/600, dossier en accent, extrait, pied (tags + date).
// La case de sélection apparaît au survol (ou dès qu'une sélection existe).
function MemoCard({ memo, grid, trash = false, selected = false, selecting = false, menuOpen = false, folderPath = [], onSelect, onMenu, onDragStart, onDragEnd, onMove, onDuplicate, onDelete, onForceDelete }) {
    const tags = memo.tags ?? [];
    const visibleTags = tags.slice(0, grid ? 2 : 3);
    const hiddenTags = tags.length - visibleTags.length;
    const date = formatMemoDate(memo.updated_at);

    const checkbox = <button
        type="button"
        onClick={(event) => { event.stopPropagation(); onSelect(); }}
        aria-pressed={selected}
        aria-label={(selected ? 'Désélectionner « ' : 'Sélectionner « ') + memo.title + ' »'}
        className={'relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] transition-opacity hover:bg-[var(--dr-field)] focus-visible:opacity-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] ' + (selected || selecting ? 'opacity-100' : 'opacity-0 group-hover:opacity-100')}
    >
        {selected
            ? <span className="flex h-[18px] w-[18px] items-center justify-center rounded-[5px] bg-[var(--dr-accent)] text-[var(--dr-ink)]"><Check size={12} strokeWidth={3} /></span>
            : <span className="h-[18px] w-[18px] rounded-[5px] border-[1.5px] border-[var(--dr-border-2)] bg-[var(--dr-surface)]" />}
    </button>;

    const actions = <div className="flex shrink-0 items-center">
        {trash
            ? <button type="button" onClick={() => router.post('/memos/' + memo.id + '/restore', {}, { preserveScroll: true })} className="relative z-10 h-9 rounded-[10px] px-3 text-[13px] font-semibold text-[var(--dr-accent-text)] hover:bg-[var(--dr-accent-soft)]">Restaurer</button>
            : <FavoriteButton memo={memo} variant="design" className="!m-0 !h-9 !w-9 !rounded-[10px] hover:bg-[var(--dr-field)]" />}
        <div className="relative">
            <button type="button" onMouseDown={(event) => event.stopPropagation()} onClick={(event) => { event.stopPropagation(); onMenu(); }} aria-haspopup="menu" aria-expanded={menuOpen} aria-label={'Actions de « ' + memo.title + ' »'} className={'relative z-10 flex h-9 w-9 items-center justify-center rounded-[10px] transition-colors hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] ' + (menuOpen ? 'bg-[var(--dr-field)] text-[var(--dr-text)]' : 'text-[var(--dr-text-3)]')}>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.8" /><circle cx="12" cy="12" r="1.8" /><circle cx="19" cy="12" r="1.8" /></svg>
            </button>
            {menuOpen && <MemoMenu memo={memo} trash={trash} onMove={onMove} onDuplicate={onDuplicate} onDelete={onDelete} onForceDelete={onForceDelete} />}
        </div>
    </div>;

    const mono = <span aria-hidden="true" className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--dr-field)] font-['JetBrains_Mono',ui-monospace,monospace] text-[15px] font-medium text-[var(--dr-accent-text)]">{monogram(memo.title)}</span>;

    const title = <Link
        href={'/memos/' + memo.id}
        draggable={false}
        title={memo.title}
        className={'text-[17px] font-semibold leading-[1.35] tracking-[-0.01em] text-[var(--dr-text)] outline-none after:absolute after:inset-0 after:rounded-[18px] after:content-[\'\'] ' + (grid ? 'line-clamp-3' : 'line-clamp-1')}
    >{memo.title}</Link>;

    const folder = folderPath.length > 0 && <span className="truncate text-xs font-semibold text-[var(--dr-accent-text)]">{folderPath.map((item) => item.name).join(' / ')}</span>;

    const footer = <>
        {visibleTags.map((tag) => <span key={tag.id} className="rounded-full bg-[var(--dr-field)] px-2.5 py-1 text-xs text-[var(--dr-text-2)]">{tag.name}</span>)}
        {hiddenTags > 0 && <span className="px-1 text-xs text-[var(--dr-text-3)]" title={tags.slice(visibleTags.length).map((tag) => tag.name).join(', ')}>+{hiddenTags}</span>}
        {date && <time dateTime={memo.updated_at} className="ml-auto shrink-0 text-xs tabular-nums text-[var(--dr-text-3)]">{date}</time>}
    </>;

    const cardClass = 'group relative rounded-[18px] border bg-[var(--dr-surface)] shadow-[var(--dr-shadow)] transition-[border-color,transform,box-shadow] duration-200 ease-out hover:-translate-y-0.5 hover:border-[var(--dr-border-2)] motion-reduce:transition-none motion-reduce:hover:translate-y-0 has-[a:focus-visible]:ring-2 has-[a:focus-visible]:ring-[var(--dr-accent)] '
        + (menuOpen ? 'z-50 ' : 'z-0 ')
        + (selected ? 'border-[var(--dr-accent)] ring-1 ring-[var(--dr-accent)] ' : 'border-[var(--dr-border)] ');

    const shared = {
        'data-memo-card': true,
        draggable: !trash,
        onDragStart: (event) => onDragStart(event),
        onDragEnd,
        onContextMenu: (event) => { event.preventDefault(); onMenu(); },
    };

    if (!grid) {
        return <article {...shared} className={cardClass + 'flex items-center gap-3 px-4 py-3'}>
            {/* En liste, la case remplace le monogramme au survol (comme une boîte mail). */}
            <div className="relative h-10 w-10 shrink-0">
                <span className={'absolute inset-0 transition-opacity ' + (selected || selecting ? 'opacity-0' : 'group-hover:opacity-0 group-has-[button[aria-pressed]:focus-visible]:opacity-0')}>{mono}</span>
                <span className="absolute inset-0 flex items-center justify-center">{checkbox}</span>
            </div>
            <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                {title}
                <div className="flex min-w-0 items-center gap-2 text-sm text-[var(--dr-text-2)]">
                    {folder}
                    {memo.excerpt && <span className="min-w-0 flex-1 truncate">{memo.excerpt}</span>}
                </div>
            </div>
            <div className="hidden shrink-0 items-center gap-1.5 xl:flex">{footer}</div>
            {actions}
        </article>;
    }

    return <article {...shared} className={cardClass + 'flex h-full min-h-[212px] flex-col gap-3 p-[18px]'}>
        <div className="flex items-center gap-2.5">
            {mono}
            <span className="flex-1" />
            {checkbox}
            {actions}
        </div>
        <div className="flex min-w-0 flex-col gap-1.5">
            {title}
            {folder}
        </div>
        {memo.excerpt && <p className="m-0 line-clamp-3 text-sm leading-[1.55] text-[var(--dr-text-2)]">{memo.excerpt}</p>}
        <div className="mt-auto flex min-w-0 items-center gap-1.5 border-t border-[var(--dr-border)] pt-3">{footer}</div>
    </article>;
}

function MemoMenu({ memo, trash, onMove, onDuplicate, onDelete, onForceDelete }) {
    const ref = useRef(null);
    useMenuPop(ref);
    const item = 'flex w-full items-center rounded-[10px] px-3 py-2.5 text-left text-sm font-medium transition-colors';
    const normal = item + ' text-[var(--dr-text)] hover:bg-[var(--dr-field)]';
    const danger = item + ' text-[var(--dr-danger)] hover:bg-[var(--dr-field)]';

    return <div ref={ref} role="menu" className="absolute right-0 top-11 z-30 w-56 overflow-hidden rounded-2xl border border-[var(--dr-border-2)] bg-[var(--dr-surface-2)] p-1.5 shadow-[0_24px_60px_-20px_rgba(0,0,0,0.55)]" onMouseDown={(event) => event.stopPropagation()} onClick={(event) => event.stopPropagation()}>
        <Link role="menuitem" href={'/memos/' + memo.id} className={normal}>Ouvrir</Link>
        {!trash && <>
            <Link role="menuitem" href={'/memos/' + memo.id + '/edit'} className={normal}>Modifier</Link>
            <button role="menuitem" type="button" onClick={() => onMove(memo)} className={normal}>Déplacer</button>
            <button role="menuitem" type="button" onClick={() => onDuplicate(memo)} className={normal}>Dupliquer</button>
            <div className="mx-1 my-1 h-px bg-[var(--dr-border)]" />
            <button role="menuitem" type="button" onClick={() => onDelete(memo)} className={danger}>Mettre à la corbeille</button>
        </>}
        {trash && <>
            <button role="menuitem" type="button" onClick={() => router.post('/memos/' + memo.id + '/restore', {}, { preserveScroll: true })} className={item + ' text-[var(--dr-accent-text)] hover:bg-[var(--dr-accent-soft)]'}>Restaurer</button>
            <button role="menuitem" type="button" onClick={() => onForceDelete(memo)} className={danger}>Supprimer définitivement</button>
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
    const headerRef = useRef(null);

    // GSAP : l'en-tête se pose en douceur, puis les pastilles arrivent en cascade.
    useLayoutEffect(() => {
        const header = headerRef.current;
        if (!header || prefersReducedMotion()) return undefined;
        const context = gsap.context(() => {
            gsap.timeline({ defaults: { ease: 'power3.out' } })
                .fromTo('[data-anim="head"]', { autoAlpha: 0, y: 14 }, { autoAlpha: 1, y: 0, duration: 0.45, stagger: 0.06, clearProps: 'opacity,visibility,transform' })
                .fromTo('[data-anim="chip"]', { autoAlpha: 0, x: 18 }, { autoAlpha: 1, x: 0, duration: 0.38, stagger: 0.035, clearProps: 'opacity,visibility,transform' }, '-=0.25');
        }, header);
        return () => context.revert();
    }, []);

    const chips = [
        ...(filters.trash ? [{ key: 'trash', label: 'Corbeille', href: listUrl({ trash: true }), active: true }] : []),
        ...(filters.recent ? [{ key: 'recent', label: 'Récents', href: listUrl({ recent: true }), active: true }] : []),
        { key: 'all', label: 'Toutes', href: '/memos', active: !hasFilter || (Boolean(filters.q) && !filters.tag && !filters.folder && !filters.favorites && !filters.trash && !filters.recent) },
        { key: 'fav', label: 'Favoris', href: listUrl({ favorites: true }), active: Boolean(filters.favorites) },
        ...folders.map((folder) => ({ key: 'folder-' + folder.id, label: folder.name, href: listUrl({ folder: folder.id }), active: Number(filters.folder) === folder.id })),
        ...tags.map((tag) => ({ key: 'tag-' + tag.id, label: tag.name, href: listUrl({ tag: tag.slug }), active: filters.tag === tag.slug })),
    ];

    return <div className="-mx-4 -mt-5 flex flex-col font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] sm:-mx-6 sm:-mt-6">
        <header ref={headerRef} className="flex flex-col gap-3.5 px-5 pt-[18px]">
            <div data-anim="head" className="flex items-center gap-2.5">
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

            <form data-anim="head" onSubmit={onSearch} role="search" className="m-0">
                <label className="flex h-[46px] items-center gap-2.5 rounded-[14px] border border-[var(--dr-border)] bg-[var(--dr-field)] px-3.5 text-[var(--dr-text-3)]">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="M20 20l-3.5-3.5" /></svg>
                    <input data-memo-search data-dr-native type="search" value={query} onChange={(event) => setQuery(event.target.value)} aria-label="Rechercher dans les fiches" placeholder="Rechercher une commande, une notion…" className="min-w-0 flex-1 appearance-none border-0 bg-transparent p-0 text-base text-[var(--dr-text)] outline-none placeholder:text-[var(--dr-text-3)] focus:ring-0 [&::-webkit-search-cancel-button]:hidden [&::-webkit-search-decoration]:hidden" />
                    {query && <button type="button" onClick={onClearSearch} aria-label="Effacer la recherche" className="-mr-1.5 flex h-8 w-8 items-center justify-center rounded-lg text-[var(--dr-text-3)]"><X size={15} /></button>}
                </label>
            </form>

            <nav aria-label="Filtrer" className="dr-scrollbar-none -mx-5 flex gap-2 overflow-x-auto px-5">
                {chips.map((chip) => <MotionLink
                    key={chip.key}
                    data-anim="chip"
                    href={chip.href}
                    preserveScroll
                    whileTap={{ scale: 0.94 }}
                    aria-current={chip.active ? 'page' : undefined}
                    className={'relative flex h-9 shrink-0 items-center whitespace-nowrap rounded-full px-3.5 text-sm ' + (chip.active
                        ? 'font-semibold text-[var(--dr-bg)]'
                        : 'border border-[var(--dr-border)] bg-[var(--dr-surface)] text-[var(--dr-text-2)]')}
                >
                    {chip.active && <m.span layoutId="memo-chip-active" transition={softSpring} aria-hidden="true" className="absolute inset-0 rounded-full bg-[var(--dr-text)]" />}
                    <span className="relative">{chip.label}</span>
                </MotionLink>)}
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

    return <m.article
        data-memo-card
        whileTap={{ scale: 0.985 }}
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
    </m.article>;
}

const MotionLink = m.create(Link);

function SheetAction({ onClick, href, danger = false, children }) {
    const className = 'flex min-h-[48px] w-full items-center rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-field)] px-4 text-left text-[15px] font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] ' + (danger ? 'text-[var(--dr-danger)]' : 'text-[var(--dr-text)]');
    return href
        ? <MotionLink href={href} whileTap={{ scale: 0.98 }} className={className}>{children}</MotionLink>
        : <m.button type="button" whileTap={{ scale: 0.98 }} onClick={onClick} className={className}>{children}</m.button>;
}

function MemoSkeletons({ grid }) {
    return <div className={grid ? 'grid gap-4 [grid-template-columns:repeat(auto-fill,minmax(300px,1fr))]' : 'flex flex-col gap-2.5'} aria-label="Chargement">
        {Array.from({ length: 6 }).map((_, index) => <div key={index} className={'animate-pulse rounded-[18px] border border-[var(--dr-border)] bg-[var(--dr-surface)] p-[18px] ' + (grid ? 'h-[212px]' : 'h-[66px]')}>
            <div className="h-10 w-10 rounded-xl bg-[var(--dr-field)]" />
            {grid && <><div className="mt-4 h-4 w-2/3 rounded bg-[var(--dr-field)]" /><div className="mt-3 h-3 w-full rounded bg-[var(--dr-field)]" /></>}
        </div>)}
    </div>;
}

function EmptyState({ filtered, trash = false, folder = false }) {
    return <div className="rounded-[18px] border border-dashed border-[var(--dr-border-2)] bg-[var(--dr-surface)] px-8 py-12 text-center">
        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]"><FileText size={25} aria-hidden="true" /></div>
        <h2 className="mt-4 text-lg font-semibold text-[var(--dr-text)]">{trash ? 'La corbeille est vide' : filtered ? 'Aucune fiche ne correspond' : folder ? 'Ce dossier est vide' : 'Aucune fiche mémo'}</h2>
        <p className="mx-auto mt-1 max-w-sm text-sm leading-6 text-[var(--dr-text-2)]">{trash ? 'Les fiches supprimées apparaîtront ici.' : filtered ? 'Essaie une autre recherche, collection ou un autre dossier.' : folder ? 'Crée une fiche ici ou déplace-en une depuis un autre dossier.' : 'Crée ta première fiche pour construire ton espace de connaissances.'}</p>
        {!trash && <Link href={filtered && !folder ? '/memos' : (folder ? '/memos/create?folder=' + new URLSearchParams(window.location.search).get('folder') : '/memos/create')} className="mt-5 inline-flex h-[42px] items-center gap-2 rounded-xl bg-[var(--dr-accent)] px-4 text-sm font-bold text-[var(--dr-ink)]">{filtered && !folder ? 'Voir toutes les fiches' : 'Créer une fiche'}</Link>}
    </div>;
}
