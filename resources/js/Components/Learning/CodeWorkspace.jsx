import {
    Check,
    Code2,
    Eye,
    FilePlus2,
    Play,
    RotateCcw,
    Save,
    Terminal,
    X,
} from "lucide-react";

import CodeEditor from "@/Components/DevLab/CodeEditor";
import EditorTabs from "@/Components/DevLab/EditorTabs";
import FileExplorer from "@/Components/DevLab/FileExplorer";
import PreviewPane from "@/Components/DevLab/PreviewPane";
import TerminalPanel from "@/Components/DevLab/TerminalPanel";
import { useEffect, useMemo, useRef, useState } from "react";

const TERMINAL_HELP = {
    browser: [
        "Commandes disponibles :",
        "  help              afficher cette aide",
        "  clear             vider le terminal",
        "  pwd               afficher le dossier courant",
        "  ls                afficher les fichiers",
        "  cat <fichier>     afficher un fichier",
        "  touch <fichier>   créer un fichier",
        "  mkdir <dossier>   créer un dossier",
        "  rm <fichier>      supprimer un fichier",
        "  preview           ouvrir l’aperçu",
        "  run               exécuter JavaScript dans le navigateur",
        "  reset             restaurer le workspace initial",
    ],
    node: [
        "Commandes Node.js disponibles :",
        "  help              afficher cette aide",
        "  node main.js      exécuter le fichier courant",
        "  node --version    afficher la version Node",
        "  npm --version     afficher la version npm",
        "  npm install       installer les dépendances",
        "  npm run build     exécuter un script package.json",
    ],
    php: [
        "Commandes PHP disponibles :",
        "  help              afficher cette aide",
        "  php main.php      exécuter le fichier courant",
        "  php --version     afficher la version PHP",
    ],
    laravel: [
        "Commandes Laravel disponibles :",
        "  php artisan --version",
        "  php artisan route:list",
        "  php artisan migrate",
        "  php artisan make:model Post -m",
        "  php artisan make:controller PostController",
        "  php artisan make:request StorePostRequest",
        "  php artisan test",
    ],
};

const DEFAULT_FILES = {
    html: {
        path: "index.html",
        content:
            "<!doctype html>\n<html lang=\"fr\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>DevRoad</title>\n</head>\n<body>\n    <h1>Bonjour DevRoad</h1>\n</body>\n</html>",
    },
    css: {
        path: "styles.css",
        content:
            "body {\n    margin: 0;\n    font-family: system-ui, sans-serif;\n    background: #0B0B0B;\n    color: white;\n}\n",
    },
    javascript: {
        path: "main.js",
        content:
            "const message = \"Bonjour DevRoad\";\nconsole.log(message);",
    },
    php: {
        path: "main.php",
        content:
            "<?php\n\n$name = \"DevRoad\";\necho $name;",
    },
    laravel: {
        path: "routes/web.php",
        content:
            "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::get(\"/bonjour\", fn () => \"Bonjour DevRoad\");",
    },
};

function normalizeFiles(workspace) {
    if (Array.isArray(workspace.files) && workspace.files.length > 0) {
        return workspace.files.map((file) => ({
            path: file.path,
            content: file.content ?? "",
        }));
    }

    const fallback = DEFAULT_FILES[workspace.language] ?? DEFAULT_FILES.javascript;

    return [
        {
            path: workspace.filename ?? fallback.path,
            content: workspace.initial_code || fallback.content,
        },
    ];
}

export default function CodeWorkspace({ workspace, stepId }) {
    const storageKey = "devroad:ide:" + stepId;
    const initialFiles = useMemo(() => normalizeFiles(workspace), [workspace]);

    const [files, setFiles] = useState(initialFiles);
    const [activeFile, setActiveFile] = useState(
        initialFiles[0]?.path ?? workspace.filename ?? "main.js",
    );
    const [panel, setPanel] = useState(
        workspace.preview_enabled ? "preview" : "terminal",
    );
    const [terminal, setTerminal] = useState([
        "DevRoad Terminal",
        "IDE navigateur prêt.",
        "Aucune installation Docker requise.",
        "Tape « help » pour afficher les commandes.",
    ]);
    const [command, setCommand] = useState("");
    const [copied, setCopied] = useState(false);
    const [saved, setSaved] = useState(false);
    const [previewVersion, setPreviewVersion] = useState(0);
    const [running, setRunning] = useState(false);
    const [showCreateFileModal, setShowCreateFileModal] = useState(false);
    const [newFilePath, setNewFilePath] = useState("");
    const [createFileError, setCreateFileError] = useState("");
    const importInputRef = useRef(null);

    const terminalEndRef = useRef(null);
    const hydratedRef = useRef(false);

    const currentFile =
        files.find((file) => file.path === activeFile) ?? files[0];

    const lineCount = useMemo(
        () => Math.max(1, (currentFile?.content ?? "").split("\n").length),
        [currentFile],
    );

    useEffect(() => {
        hydratedRef.current = false;

        try {
            const stored = window.localStorage.getItem(storageKey);

            if (stored) {
                const parsed = JSON.parse(stored);

                if (Array.isArray(parsed.files) && parsed.files.length > 0) {
                    setFiles(parsed.files);
                    setActiveFile(
                        parsed.activeFile ?? parsed.files[0].path,
                    );
                }
            }
        } catch {
            // Un workspace local corrompu ne doit pas bloquer le cours.
        } finally {
            hydratedRef.current = true;
        }
    }, [storageKey]);

    useEffect(() => {
        if (!hydratedRef.current) {
            return;
        }

        window.localStorage.setItem(
            storageKey,
            JSON.stringify({
                files,
                activeFile,
            }),
        );
    }, [storageKey, files, activeFile]);

    useEffect(() => {
        terminalEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [terminal]);

    useEffect(() => {
        const handleMessage = (event) => {
            if (!event.data || event.data.source !== "devroad-ide") {
                return;
            }

            const type = event.data.type ?? "log";
            const value = event.data.value ?? "";

            if (type === "error") {
                pushTerminal("✕ " + value);
                return;
            }

            pushTerminal("› " + value);
        };

        window.addEventListener("message", handleMessage);

        return () => window.removeEventListener("message", handleMessage);
    }, []);

    function pushTerminal(...lines) {
        setTerminal((current) => [...current, ...lines]);
    }

    function updateCurrentFile(content) {
        setFiles((current) =>
            current.map((file) =>
                file.path === activeFile ? { ...file, content } : file,
            ),
        );
        setSaved(false);
    }

    function saveWorkspace() {
        window.localStorage.setItem(
            storageKey,
            JSON.stringify({
                files,
                activeFile,
            }),
        );

        setSaved(true);
        window.setTimeout(() => setSaved(false), 1400);
        pushTerminal("✓ Workspace sauvegardé localement.");
    }

    function resetWorkspace() {
        if (!window.confirm("Réinitialiser les fichiers de cette leçon ?")) {
            return;
        }

        setFiles(initialFiles);
        setActiveFile(initialFiles[0]?.path ?? activeFile);
        setTerminal((current) => [...current, "↳ Workspace réinitialisé."]);
        window.localStorage.removeItem(storageKey);
    }

    async function copyCode() {
        await navigator.clipboard.writeText(currentFile?.content ?? "");
        setCopied(true);
        window.setTimeout(() => setCopied(false), 1600);
    }

    function openCreateFileModal() {
        setNewFilePath("");
        setCreateFileError("");
        setShowCreateFileModal(true);
    }

    function closeCreateFileModal() {
        setShowCreateFileModal(false);
        setNewFilePath("");
        setCreateFileError("");
    }

    function normalizeWorkspacePath(path) {
        return path
            .trim()
            .replace(/\\/g, "/")
            .replace(/^\/+/, "")
            .replace(/\/+/g, "/");
    }

    function isValidWorkspacePath(path) {
        return (
        <section className="overflow-hidden rounded-3xl border border-white/[0.07] bg-[#09111D] shadow-[0_20px_60px_rgba(0,0,0,0.28)]">
            <input
                ref={importInputRef}
                type="file"
                multiple
                webkitdirectory=""
                directory=""
                className="hidden"
                onChange={importFiles}
            />

            <div className="flex flex-col gap-3 border-b border-white/[0.06] bg-[#0A1422] p-3 sm:p-4">
                <div className="flex items-center justify-between gap-3">
                    <div className="flex min-w-0 items-center gap-3">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                            <Code2 size={17} />
                        </div>

                        <div className="min-w-0">
                            <p className="truncate text-xs font-bold text-white">
                                DevLab
                            </p>
                            <p className="truncate text-[10px] text-slate-600">
                                IDE navigateur · sauvegarde locale
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {workspace.runtime === "server" && (
                            <button
                                type="button"
                                onClick={() => runServer()}
                                disabled={running}
                                className="inline-flex items-center gap-1.5 rounded-lg bg-[#FF6A00] px-3 py-2 text-[11px] font-bold text-[#08111F] transition hover:bg-[#ff781a] disabled:cursor-wait disabled:opacity-60"
                            >
                                <Play size={13} fill="currentColor" />
                                {running ? "Exécution..." : "Exécuter"}
                            </button>
                        )}

                        <button
                            type="button"
                            onClick={saveWorkspace}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.06] bg-white/[0.03] px-2.5 py-2 text-[11px] font-semibold text-slate-400 transition hover:bg-white/[0.06] hover:text-white"
                        >
                            {saved ? <Check size={13} /> : <Save size={13} />}
                            {saved ? "Sauvé" : "Sauver"}
                        </button>

                        <button
                            type="button"
                            onClick={resetWorkspace}
                            className="rounded-lg border border-white/[0.06] bg-white/[0.03] p-2 text-slate-500 hover:text-white"
                            aria-label="Réinitialiser"
                        >
                            <RotateCcw size={14} />
                        </button>
                    </div>
                </div>

                <EditorTabs
                    files={files}
                    activeFile={activeFile}
                    onSelect={setActiveFile}
                    onCreate={openCreateFileModal}
                    onImport={() => importInputRef.current?.click()}
                />
            </div>

            <div className="grid lg:grid-cols-[210px_minmax(0,1fr)]">
                <FileExplorer
                    files={files}
                    activeFile={activeFile}
                    onSelect={setActiveFile}
                    onCreate={openCreateFileModal}
                    onImport={() => importInputRef.current?.click()}
                />

                <div className="min-w-0">
                    <CodeEditor
                        activeFile={activeFile}
                        currentFile={currentFile}
                        lineCount={lineCount}
                        copied={copied}
                        onChange={updateCurrentFile}
                        onCopy={copyCode}
                        onDelete={deleteFile}
                    />

                    <div className="flex items-center justify-between border-t border-white/[0.05] bg-[#08111C] px-3 py-2">
                        <div className="flex gap-1">
                            <button
                                type="button"
                                onClick={() => setPanel("terminal")}
                                className={[
                                    "inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-[10px] font-bold",
                                    panel === "terminal"
                                        ? "bg-[#FF6A00] text-[#08111F]"
                                        : "text-slate-600 hover:text-white",
                                ].join(" ")}
                            >
                                <Terminal size={12} />
                                Terminal
                            </button>

                            <button
                                type="button"
                                onClick={runPreview}
                                className={[
                                    "inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-[10px] font-bold",
                                    panel === "preview"
                                        ? "bg-[#FF6A00] text-[#08111F]"
                                        : "text-slate-600 hover:text-white",
                                ].join(" ")}
                            >
                                <Eye size={12} />
                                Aperçu
                            </button>
                        </div>

                        <span className="text-[10px] text-slate-700">
                            {workspace.runtime === "server"
                                ? "Runtime local DevRoad · " + workspace.label
                                : "Runtime navigateur"}
                        </span>
                    </div>

                    {panel === "terminal" ? (
                        <TerminalPanel
                            terminal={terminal}
                            command={command}
                            onCommandChange={setCommand}
                            onSubmit={submitCommand}
                            onClear={() => setTerminal([])}
                            terminalEndRef={terminalEndRef}
                        />
                    ) : (
                        <PreviewPane
                            previewVersion={previewVersion}
                            srcDoc={buildPreviewDocument()}
                            onRefresh={runPreview}
                        />
                    )}
                </div>
            </div>

            {showCreateFileModal && (
                <div
                    className="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="devroad-create-file-title"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget) {
                            closeCreateFileModal();
                        }
                    }}
                >
                    <div className="w-full max-w-md overflow-hidden rounded-3xl border border-white/[0.08] bg-[#0D1725] shadow-[0_30px_90px_rgba(0,0,0,0.55)]">
                        <div className="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                            <div>
                                <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#FF8A3D]">
                                    Explorateur
                                </p>
                                <h2
                                    id="devroad-create-file-title"
                                    className="mt-1 text-base font-bold text-white"
                                >
                                    Nouveau fichier
                                </h2>
                            </div>

                            <button
                                type="button"
                                onClick={closeCreateFileModal}
                                className="rounded-xl p-2 text-slate-600 transition hover:bg-white/[0.05] hover:text-white"
                                aria-label="Fermer"
                            >
                                <X size={18} />
                            </button>
                        </div>

                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                createFile();
                            }}
                            className="space-y-5 p-5"
                        >
                            <div>
                                <label
                                    htmlFor="devroad-new-file-path"
                                    className="text-xs font-semibold text-slate-300"
                                >
                                    Chemin du fichier
                                </label>

                                <input
                                    id="devroad-new-file-path"
                                    autoFocus
                                    value={newFilePath}
                                    onChange={(event) => {
                                        setNewFilePath(event.target.value);
                                        setCreateFileError("");
                                    }}
                                    placeholder="src/components/Button.jsx"
                                    className="mt-2 h-11 w-full rounded-xl border border-white/[0.08] bg-[#07101A] px-3 text-sm text-white outline-none transition placeholder:text-slate-700 focus:border-[#FF6A00]/40 focus:ring-2 focus:ring-[#FF6A00]/10"
                                />

                                <p className="mt-2 text-[10px] leading-5 text-slate-600">
                                    Tu peux créer un fichier dans un dossier,
                                    par exemple <span className="text-slate-400">js/app.js</span>.
                                </p>

                                {createFileError && (
                                    <p className="mt-2 text-xs font-medium text-red-400">
                                        {createFileError}
                                    </p>
                                )}

                            </div>

                            <div className="flex justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={closeCreateFileModal}
                                    className="rounded-xl border border-white/[0.06] bg-white/[0.03] px-4 py-2.5 text-xs font-semibold text-slate-400 transition hover:bg-white/[0.06] hover:text-white"
                                >
                                    Annuler
                                </button>

                                <button
                                    type="submit"
                                    className="inline-flex items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-xs font-bold text-[#08111F] transition hover:bg-[#ff781a]"
                                >
                                    <FilePlus2 size={14} />
                                    Créer le fichier
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </section>
    );

}
