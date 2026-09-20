import { useEffect, useMemo, useRef, useState } from "react";
import { Monitor, Play, Terminal as TerminalIcon } from "lucide-react";
import EditorTabs from "@/Components/DevLab/EditorTabs";
import CodeEditor from "@/Components/DevLab/CodeEditor";
import FileExplorer from "@/Components/DevLab/FileExplorer";
import PreviewPane from "@/Components/DevLab/PreviewPane";
import MobileSymbolBar from "@/Components/DevLab/MobileSymbolBar";
import RuntimeSelector from "@/Components/DevLab/RuntimeSelector";
import DevLabTerminal from "@/Components/DevLab/DevLabTerminal";

export default function DevLabWorkspace({ project, onSave, onNewFile, onDelete, onImportFiles }) {
    const files = project.files ?? [];
    const [active, setActive] = useState(files[0]?.path ?? "");
    const [draft, setDraft] = useState(files[0]?.content ?? "");
    const [saved, setSaved] = useState(true);
    const [copied, setCopied] = useState(false);
    const [panel, setPanel] = useState("editor");
    const [previewVersion, setPreviewVersion] = useState(0);
    const [importing, setImporting] = useState(false);
    const editorRef = useRef(null);
    const importRef = useRef(null);

    const current = files.find((file) => file.path === active) ?? files[0];

    useEffect(() => {
        setActive(files[0]?.path ?? "");
    }, [project.id]);

    useEffect(() => {
        const file = files.find((item) => item.path === active) ?? files[0];
        setDraft(file?.content ?? "");
        setSaved(true);
    }, [active, project.id]);

    useEffect(() => {
        if (saved || !current) return;
        const timer = window.setTimeout(async () => {
            try {
                await onSave(current, { path: current.path, content: draft });
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

        return html
            .replace("</head>", "<style>" + css + "</style></head>")
            .replace("</body>", "<script>" + js.replaceAll("</script>", "") + "</script></body>");
    }, [files, current, draft]);

    function refreshPreview() {
        setPreviewVersion((value) => value + 1);
        setPanel("preview");
    }

    function closePreview() {
        setPanel("editor");
        requestAnimationFrame(() => editorRef.current?.focus());
    }

    async function save() {
        if (!current || saved) return;
        try {
            await onSave(current, { path: current.path, content: draft });
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
            setDraft((value) => value + symbol);
            setSaved(false);
            return;
        }

        const start = textarea.selectionStart ?? draft.length;
        const end = textarea.selectionEnd ?? start;
        const value = draft.slice(0, start) + symbol + draft.slice(end);
        setDraft(value);
        setSaved(false);

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
        <>
            <div className="border-b border-white/[0.06] bg-[#0D1725] p-2">
                <EditorTabs
                    files={files}
                    activeFile={active}
                    onSelect={setActive}
                    onCreate={onNewFile}
                    onImport={() => importRef.current?.click()}
                />
            </div>

            <div className="min-h-0 flex-1 overflow-hidden">
                <CodeEditor
                    activeFile={active}
                    currentFile={{ ...current, content: draft }}
                    lineCount={Math.max(1, draft.split("\n").length)}
                    copied={copied}
                    editorRef={editorRef}
                    onChange={(value) => {
                        setDraft(value);
                        setSaved(false);
                    }}
                    onCopy={copy}
                    onDelete={() => current && onDelete(current)}
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
        </>
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
                        onSelect={setActive}
                        onCreate={onNewFile}
                        onImport={() => importRef.current?.click()}
                    />

                    <section className="min-h-0 min-w-0 overflow-hidden bg-[#06101A]">
                        {editorView}
                        <div className="hidden lg:block">
                            {terminalView}
                        </div>
                    </section>

                    <aside className="min-w-0 border-l border-white/[0.06] bg-[#050B12]">
                        {previewView}
                    </aside>
                </div>

                <div className="flex min-h-0 flex-1 flex-col overflow-hidden lg:hidden">
                    <div className="min-h-0 flex-1 overflow-hidden bg-[#06101A]">
                        {panel === "editor" && editorView}
                        {panel === "preview" && <div className="h-full overflow-auto bg-[#050B12]">{previewView}</div>}
                        {panel === "terminal" && <div className="h-full overflow-hidden bg-[#050B12]">{terminalView}</div>}
                    </div>

                    <div className="flex shrink-0 border-t border-white/[0.07] bg-[#0D1725] p-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))]">
                        {[
                            ["editor", "Éditeur"],
                            ["preview", "Aperçu"],
                            ["terminal", "Terminal"],
                        ].map(([key, label]) => (
                            <button
                                key={key}
                                type="button"
                                onClick={() => setPanel(key)}
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
                </div>
            </div>
        </div>
    );
}
