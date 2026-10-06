import { useEffect, useMemo, useRef, useState } from "react";
import { ChevronDown, GripHorizontal, Monitor, Play, Terminal as TerminalIcon } from "lucide-react";
import EditorTabs from "@/Components/DevLab/EditorTabs";
import CodeEditor from "@/Components/DevLab/CodeEditor";
import FileExplorer from "@/Components/DevLab/FileExplorer";
import PreviewPane from "@/Components/DevLab/PreviewPane";
import MobileSymbolBar from "@/Components/DevLab/MobileSymbolBar";
import RuntimeSelector from "@/Components/DevLab/RuntimeSelector";
import DevLabTerminal from "@/Components/DevLab/DevLabTerminal";

export default function DevLabWorkspace({ project, onSave, onNewFile, onRename, onDuplicate, onDelete, onImportFiles }) {
    const files = project.files ?? [];
    const [active, setActive] = useState(files[0]?.path ?? "");
    const [draft, setDraft] = useState(files[0]?.content ?? "");
    const [saved, setSaved] = useState(true);
    const [copied, setCopied] = useState(false);
    const [panel, setPanel] = useState("editor");
    const [previewVersion, setPreviewVersion] = useState(0);
    const [importing, setImporting] = useState(false);
    const [mobileSheetHeight, setMobileSheetHeight] = useState(68);
    const [mobileSheetDragging, setMobileSheetDragging] = useState(false);
    const mobileSheetRef = useRef(null);
    const mobileSheetDragRef = useRef(null);
    const editorRef = useRef(null);
    const importRef = useRef(null);
    // Historique d'annulation par fichier : { past: [], future: [], lastAt, lastKind }.
    // Le navigateur perd le sien dès que le contenu est modifié par le code
    // (complétion, symboles, changement d'onglet) : on tient donc le nôtre.
    const historyRef = useRef(new Map());
    // Brouillons non encore sauvegardés, pour ne rien perdre en changeant d'onglet.
    const pendingRef = useRef(new Map());
    const [, setHistoryVersion] = useState(0);

    const current = files.find((file) => file.path === active) ?? files[0];

    useEffect(() => {
        setActive(files[0]?.path ?? "");
    }, [project.id]);

    useEffect(() => {
        const file = files.find((item) => item.path === active) ?? files[0];
        const pending = file ? pendingRef.current.get(file.id) : undefined;
        setDraft(pending ?? file?.content ?? "");
        setSaved(pending === undefined);
    }, [active, project.id]);

    useEffect(() => {
        historyRef.current = new Map();
        pendingRef.current = new Map();
        setHistoryVersion((value) => value + 1);
    }, [project.id]);

    function historyFor(path) {
        if (!historyRef.current.has(path)) {
            historyRef.current.set(path, { past: [], future: [], lastAt: 0, lastKind: null });
        }
        return historyRef.current.get(path);
    }

    /** Position du curseur après annulation : fin de la zone qui a changé. */
    function changedCaret(from, to) {
        let prefix = 0;
        while (prefix < from.length && prefix < to.length && from[prefix] === to[prefix]) prefix += 1;
        let suffix = 0;
        while (suffix < from.length - prefix && suffix < to.length - prefix && from[from.length - 1 - suffix] === to[to.length - 1 - suffix]) suffix += 1;
        return to.length - suffix;
    }

    /**
     * Applique un nouveau contenu en l'enregistrant dans l'historique.
     * Les frappes rapprochées d'un même mot sont regroupées (une annulation =
     * un mot) ; un espace, un retour à la ligne ou une insertion par le code
     * (complétion, symbole) commence une nouvelle étape.
     */
    function commit(value, kind = "type") {
        if (!current || value === draft) return;
        const history = historyFor(current.path);
        const now = Date.now();
        // Caractère qui vient d'être tapé (juste avant le curseur).
        const caret = editorRef.current?.selectionStart ?? value.length;
        const lastChar = value.length > draft.length ? value.charAt(caret - 1) : "";
        const boundary = kind !== "type" || history.lastKind !== kind || now - history.lastAt > 800 || /\s/.test(lastChar);

        if (boundary || history.past.length === 0) {
            history.past.push({ content: draft });
            if (history.past.length > 200) history.past.shift();
        }
        history.future = [];
        history.lastAt = now;
        history.lastKind = kind;

        pendingRef.current.set(current.id, value);
        setDraft(value);
        setSaved(false);
        setHistoryVersion((v) => v + 1);
    }

    function restore(snapshot) {
        const caret = changedCaret(draft, snapshot.content);
        pendingRef.current.set(current.id, snapshot.content);
        setDraft(snapshot.content);
        setSaved(false);
        setHistoryVersion((v) => v + 1);
        requestAnimationFrame(() => {
            const textarea = editorRef.current;
            if (!textarea) return;
            textarea.focus();
            textarea.setSelectionRange(caret, caret);
        });
    }

    function undo() {
        if (!current) return;
        const history = historyFor(current.path);
        const previous = history.past.pop();
        if (!previous) return;
        history.future.push({ content: draft });
        history.lastKind = null;
        restore(previous);
    }

    function redo() {
        if (!current) return;
        const history = historyFor(current.path);
        const next = history.future.pop();
        if (!next) return;
        history.past.push({ content: draft });
        history.lastKind = null;
        restore(next);
    }

    const currentHistory = current ? historyRef.current.get(current.path) : null;
    const canUndo = Boolean(currentHistory?.past.length);
    const canRedo = Boolean(currentHistory?.future.length);

    /** Change d'onglet en sauvegardant d'abord le brouillon en attente (aucune perte). */
    function selectFile(path) {
        if (path === active) return;
        if (current && !saved) {
            const file = current;
            const content = draft;
            onSave(file, { path: file.path, content })
                .then(() => {
                    if (pendingRef.current.get(file.id) === content) pendingRef.current.delete(file.id);
                })
                .catch(() => {});
        }
        setActive(path);
    }

    useEffect(() => {
        if (saved || !current) return;
        const timer = window.setTimeout(async () => {
            try {
                await onSave(current, { path: current.path, content: draft });
                if (pendingRef.current.get(current.id) === draft) pendingRef.current.delete(current.id);
                setSaved(true);
            } catch {}
        }, 900);
        return () => window.clearTimeout(timer);
    }, [draft, saved, current?.id, project.id]);

    const srcDoc = useMemo(() => {
        const getContent = (file) => file && file.id === current?.id ? draft : file?.content ?? "";
        const htmlFile = files.find((file) => file.path.toLowerCase() === "index.html");
        const html = getContent(htmlFile) || (current?.path?.toLowerCase().endsWith(".html") ? draft : "");
        const css = getContent(files.find((file) => file.path.toLowerCase().endsWith(".css")));
        const js = getContent(files.find((file) => file.path.toLowerCase().endsWith(".js")));

        if (!html) {
            return "<!doctype html><html><body style='font-family:system-ui;padding:24px'><pre>Aucun aperçu HTML disponible.</pre></body></html>";
        }

        // Les fichiers CSS/JS du projet sont injectés directement : on retire les
        // balises qui pointent vers eux (bloquées par la sécurité de l'aperçu).
        const isLocal = (url) => !/^(?:[a-z]+:)?\/\//i.test(url) && !url.startsWith("data:");
        return html
            .replace(/<link\b[^>]*\bhref=["']([^"']+)["'][^>]*>/gi, (tag, href) => (/stylesheet/i.test(tag) && isLocal(href) ? "" : tag))
            .replace(/<script\b[^>]*\bsrc=["']([^"']+)["'][^>]*>\s*<\/script>/gi, (tag, src) => (isLocal(src) ? "" : tag))
            .replace("</head>", "<style>" + css + "</style></head>")
            .replace("</body>", "<script>" + js.replaceAll("</script>", "") + "</script></body>");
    }, [files, current, draft]);

    function getMobileSheetExpandedHeight() {
        if (typeof window === "undefined") return 420;
        return Math.min(Math.max(Math.round(window.innerHeight * 0.55), 320), 560);
    }

    function openMobileSheet() {
        setMobileSheetHeight(getMobileSheetExpandedHeight());
    }

    function closeMobileSheet() {
        setMobileSheetHeight(68);
    }

    function selectMobilePanel(nextPanel) {
        setPanel(nextPanel);
        if (nextPanel === "editor") {
            closeMobileSheet();
            requestAnimationFrame(() => editorRef.current?.focus());
            return;
        }
        openMobileSheet();
    }

    function refreshPreview() {
        setPreviewVersion((value) => value + 1);
        setPanel("preview");
        openMobileSheet();
    }

    function closePreview() {
        setPanel("editor");
        closeMobileSheet();
        requestAnimationFrame(() => editorRef.current?.focus());
    }

    function handleMobileSheetPointerDown(event) {
        if (event.pointerType === "mouse" && event.button !== 0) return;

        mobileSheetDragRef.current = {
            startY: event.clientY,
            startHeight: mobileSheetHeight,
        };
        setMobileSheetDragging(true);
        event.currentTarget.setPointerCapture?.(event.pointerId);
    }

    function handleMobileSheetPointerMove(event) {
        const drag = mobileSheetDragRef.current;
        if (!drag) return;

        const expandedHeight = getMobileSheetExpandedHeight();
        const nextHeight = Math.min(
            expandedHeight,
            Math.max(68, drag.startHeight + (drag.startY - event.clientY)),
        );

        setMobileSheetHeight(nextHeight);
    }

    function handleMobileSheetPointerEnd(event) {
        const drag = mobileSheetDragRef.current;
        if (!drag) return;

        const expandedHeight = getMobileSheetExpandedHeight();
        const midpoint = 68 + ((expandedHeight - 68) * 0.42);
        const shouldExpand = mobileSheetHeight >= midpoint;

        mobileSheetDragRef.current = null;
        setMobileSheetDragging(false);
        setMobileSheetHeight(shouldExpand ? expandedHeight : 68);
        event.currentTarget.releasePointerCapture?.(event.pointerId);
    }

    async function renameFile(file) {
        const renamed = await onRename?.(file);
        if (renamed?.path) {
            setActive(renamed.path);
            setDraft(renamed.content ?? draft);
            setSaved(true);
        }
    }

    async function duplicateFile(file) {
        const duplicated = await onDuplicate?.(file);
        if (duplicated?.path) {
            setActive(duplicated.path);
            setDraft(duplicated.content ?? "");
            setSaved(true);
        }
    }

    async function removeFile(file) {
        // Supprime avec le contenu le plus récent (brouillon compris) pour pouvoir le restaurer.
        const latest = file.id === current?.id ? draft : (pendingRef.current.get(file.id) ?? file.content);
        const deleted = await onDelete?.({ ...file, content: latest });
        if (!deleted) return;
        pendingRef.current.delete(file.id);
        historyRef.current.delete(file.path);
        const remaining = files.filter((item) => item.id !== file.id);
        const next = remaining[0];
        setActive(next?.path ?? "");
        setDraft(next?.content ?? "");
        setSaved(true);
    }

    async function save() {
        if (!current || saved) return;
        try {
            await onSave(current, { path: current.path, content: draft });
            if (pendingRef.current.get(current.id) === draft) pendingRef.current.delete(current.id);
            setSaved(true);
        } catch {}
    }

    function copy() {
        navigator.clipboard?.writeText(draft);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 1200);
    }

    function insertSymbol(symbol) {
        const textarea = editorRef.current;
        if (!textarea) {
            commit(draft + symbol, "insert");
            return;
        }

        const start = textarea.selectionStart ?? draft.length;
        const end = textarea.selectionEnd ?? start;
        const value = draft.slice(0, start) + symbol + draft.slice(end);
        commit(value, "insert");

        requestAnimationFrame(() => {
            textarea.focus();
            const position = start + symbol.length;
            textarea.setSelectionRange(position, position);
        });
    }

    async function handleImport(event) {
        const selected = Array.from(event.target.files ?? []);
        event.target.value = "";
        if (!selected.length) return;

        setImporting(true);
        try {
            const imported = [];
            let total = 0;

            for (const file of selected.slice(0, 100)) {
                if (file.size > 512 * 1024) continue;
                total += file.size;
                if (total > 5 * 1024 * 1024) break;

                const rawPath = file.webkitRelativePath || file.name;
                const path = rawPath.replace(/\\/g, "/").replace(/^\.\//, "");

                const isWindowsAbsolutePath = path.length >= 3 && path[1] === ":" && path[2] === "/" && ((path.charCodeAt(0) >= 65 && path.charCodeAt(0) <= 90) || (path.charCodeAt(0) >= 97 && path.charCodeAt(0) <= 122));
                if (!path || path.includes("..") || path.startsWith("/") || isWindowsAbsolutePath) continue;
                const blockedSegments = path.split("/").map((segment) => segment.toLowerCase());
                if (blockedSegments.some((segment) => [".env", ".git", "node_modules", "vendor", "storage"].includes(segment))) continue;

                imported.push({ path, content: await file.text() });
            }

            if (imported.length) {
                await onImportFiles(imported);
                refreshPreview();
            }
        } finally {
            setImporting(false);
        }
    }

    const runtime = project.runtime ?? "browser";

    const currentFiles = current
        ? files.map((file) => file.id === current.id ? { ...file, content: draft } : file)
        : files;

    const editorView = (
        <div className="flex min-h-0 flex-1 flex-col">
            <div className="border-b border-white/[0.06] bg-[#0D1725] p-2">
                <EditorTabs
                    files={files}
                    activeFile={active}
                    onSelect={selectFile}
                    onCreate={onNewFile}
                    onImport={() => importRef.current?.click()}
                    onRename={renameFile}
                    onDuplicate={duplicateFile}
                    onDelete={removeFile}
                />
            </div>

            <div className="min-h-0 flex-1 overflow-hidden">
                <CodeEditor
                    activeFile={active}
                    currentFile={{ ...current, content: draft }}
                    lineCount={Math.max(1, draft.split("\n").length)}
                    copied={copied}
                    editorRef={editorRef}
                    onChange={(value, kind) => commit(value, kind)}
                    onUndo={undo}
                    onRedo={redo}
                    canUndo={canUndo}
                    canRedo={canRedo}
                    onCopy={copy}
                    onDelete={() => current && removeFile(current)}
                />
            </div>

            <MobileSymbolBar onInsert={insertSymbol} />

            <div className="flex h-12 shrink-0 items-center justify-between border-t border-white/[0.06] bg-[#0D1725] px-3">
                <span className="text-[10px] text-slate-600">
                    {saved ? "Enregistré" : "Sauvegarde automatique…"}
                </span>
                <button
                    type="button"
                    onClick={save}
                    disabled={saved}
                    className="inline-flex items-center gap-1.5 rounded-lg bg-[#FF6A00] px-3 py-2 text-[10px] font-bold text-[#08111F] disabled:opacity-30"
                >
                    Enregistrer
                </button>
            </div>
        </div>
    );

    const previewView = runtime === "browser" ? (
        <PreviewPane previewVersion={previewVersion} srcDoc={srcDoc} onRefresh={refreshPreview} onClose={closePreview} />
    ) : (
        <div className="m-3 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-5 text-center">
            <Monitor className="mx-auto text-slate-600" size={22} />
            <p className="mt-3 text-xs font-bold text-slate-300">Runtime serveur</p>
            <p className="mt-2 text-[11px] leading-5 text-slate-600">
                L’exécution serveur sécurisée sera activée séparément. Aucun code utilisateur n’est exécuté sur Railway.
            </p>
        </div>
    );

    const terminalView = (
        <DevLabTerminal
            files={currentFiles}
            runtime={runtime}
            onPreview={refreshPreview}
        />
    );

    return (
        <div className="min-h-0 flex-1 overflow-hidden">
            <input
                ref={importRef}
                type="file"
                multiple
                webkitdirectory=""
                disabled={importing}
                className="hidden"
                onChange={handleImport}
            />

            <div className="flex h-full min-h-0 flex-col">
                <div className="flex shrink-0 items-center gap-2 border-b border-white/[0.06] bg-[#0D1725] px-2 py-2">
                    <RuntimeSelector runtime={runtime} template={project.template} />
                    <button
                        type="button"
                        onClick={() => panel === "preview" ? closePreview() : refreshPreview()}
                        className="ml-auto inline-flex min-h-10 items-center gap-1.5 rounded-xl bg-[#FF6A00] px-3 text-[10px] font-bold text-[#08111F]"
                    >
                        <Play size={13} fill="currentColor" />
                        {panel === "preview" ? "Fermer" : "Aperçu"}
                    </button>
                </div>

                <div className="hidden min-h-0 flex-1 lg:grid lg:grid-cols-[220px_minmax(0,1fr)_minmax(280px,34vw)]">
                    <FileExplorer
                        files={files}
                        activeFile={active}
                        onSelect={selectFile}
                        onCreate={onNewFile}
                        onImport={() => importRef.current?.click()}
                        onRename={renameFile}
                        onDuplicate={duplicateFile}
                        onDelete={removeFile}
                    />

                    <section className="flex min-h-0 min-w-0 flex-col overflow-hidden bg-[#06101A]">
                        {editorView}
                        <div className="hidden h-48 shrink-0 lg:block">
                            {terminalView}
                        </div>
                    </section>

                    <aside className="min-w-0 border-l border-white/[0.06] bg-[#050B12]">
                        {previewView}
                    </aside>
                </div>

                <div className="relative flex min-h-0 flex-1 flex-col overflow-hidden lg:hidden">
                    <div className="min-h-0 flex-1 overflow-hidden bg-[#06101A] pb-[68px]">
                        {editorView}
                    </div>

                    <div
                        ref={mobileSheetRef}
                        className={
                            "absolute inset-x-0 bottom-0 z-[70] flex flex-col overflow-hidden rounded-t-[24px] border border-b-0 border-white/[0.08] bg-[#07111D]/[0.98] shadow-[0_-16px_50px_rgba(0,0,0,0.38)] backdrop-blur-xl " +
                            (mobileSheetDragging ? "" : "transition-[height] duration-200 ease-out")
                        }
                        style={{
                            height: mobileSheetHeight,
                            touchAction: "none",
                        }}
                    >
                        <div
                            className="flex shrink-0 cursor-grab touch-none items-center justify-center px-4 py-2 active:cursor-grabbing"
                            onPointerDown={handleMobileSheetPointerDown}
                            onPointerMove={handleMobileSheetPointerMove}
                            onPointerUp={handleMobileSheetPointerEnd}
                            onPointerCancel={handleMobileSheetPointerEnd}
                            role="separator"
                            aria-label="Faire glisser le panneau inférieur"
                            aria-orientation="horizontal"
                        >
                            <div className="flex h-6 w-full items-center justify-center rounded-xl">
                                <GripHorizontal size={22} className="text-slate-600" />
                            </div>
                        </div>

                        <div className="flex shrink-0 border-b border-white/[0.06] bg-[#0D1725]/90 p-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))]">
                            {[
                                ["editor", "Éditeur"],
                                ["preview", "Aperçu"],
                                ["terminal", "Terminal"],
                            ].map(([key, label]) => (
                                <button
                                    key={key}
                                    type="button"
                                    onClick={() => selectMobilePanel(key)}
                                    className={
                                        "flex min-h-10 flex-1 items-center justify-center gap-1.5 rounded-lg py-2 text-xs font-semibold " +
                                        (panel === key ? "bg-[#FF6A00] text-[#08111F]" : "text-slate-500")
                                    }
                                >
                                    {key === "terminal" && <TerminalIcon size={13} />}
                                    {label}
                                </button>
                            ))}
                        </div>

                        <div className="min-h-0 flex-1 overflow-hidden bg-[#050B12]">
                            {panel === "preview" && (
                                <div className="h-full overflow-auto">
                                    {previewView}
                                </div>
                            )}
                            {panel === "terminal" && (
                                <div className="h-full overflow-hidden">
                                    {terminalView}
                                </div>
                            )}
                            {panel === "editor" && (
                                <div className="flex h-full items-center justify-center px-8 text-center">
                                    <div>
                                        <ChevronDown size={18} className="mx-auto rotate-180 text-slate-700" />
                                        <p className="mt-2 text-[11px] text-slate-600">
                                            Faites glisser vers le haut pour ouvrir le panneau.
                                        </p>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
