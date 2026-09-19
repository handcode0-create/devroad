import {
    Check,
    ChevronDown,
    Clipboard,
    Code2,
    Eye,
    FileCode2,
    FilePlus2,
    Folder,
    FolderOpen,
    Play,
    RotateCcw,
    Save,
    Terminal,
    Trash2,
    Upload,
    X,
} from "lucide-react";
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
            path &&
            !path.includes("..") &&
            !path.startsWith(".") &&
            !/[<>:"|?*\\]/.test(path)
        );
    }

    function createFile() {
        const normalized = normalizeWorkspacePath(newFilePath);

        if (!normalized) {
            setCreateFileError("Indique le nom du fichier.");
            return;
        }

        if (!isValidWorkspacePath(normalized)) {
            setCreateFileError("Le chemin du fichier est invalide.");
            return;
        }

        if (files.some((file) => file.path === normalized)) {
            setCreateFileError("Ce fichier existe déjà.");
            return;
        }

        setFiles((current) => [
            ...current,
            {
                path: normalized,
                content: "",
            },
        ]);
        setActiveFile(normalized);
        pushTerminal("✓ Fichier créé : " + normalized);
        closeCreateFileModal();
    }

    function importFiles(event) {
        const selectedFiles = Array.from(event.target.files ?? []);

        if (selectedFiles.length === 0) {
            return;
        }

        let imported = 0;
        let rejected = 0;

        selectedFiles.forEach((file) => {
            if (file.size > 300 * 1024) {
                rejected += 1;
                return;
            }

            const relativePath =
                file.webkitRelativePath || file.name;
            const normalized = normalizeWorkspacePath(relativePath);

            if (!isValidWorkspacePath(normalized)) {
                rejected += 1;
                return;
            }

            const reader = new FileReader();

            reader.onload = () => {
                const content = typeof reader.result === "string"
                    ? reader.result
                    : "";

                setFiles((current) => {
                    const exists = current.some(
                        (item) => item.path === normalized,
                    );

                    if (exists) {
                        return current.map((item) =>
                            item.path === normalized
                                ? { ...item, content }
                                : item,
                        );
                    }

                    return [...current, { path: normalized, content }];
                });

                setActiveFile(normalized);
                imported += 1;

                pushTerminal("✓ Importé : " + normalized);

                if (imported + rejected === selectedFiles.length) {
                    if (rejected > 0) {
                        pushTerminal(
                            "⚠ " +
                                rejected +
                                " fichier(s) ignoré(s) : taille ou chemin invalide.",
                        );
                    }
                }
            };

            reader.onerror = () => {
                rejected += 1;
                pushTerminal("✕ Impossible de lire : " + normalized);
            };

            reader.readAsText(file);
        });

        event.target.value = "";
    }

    function deleteFile() {
        if (files.length <= 1) {
            pushTerminal("✕ Le dernier fichier ne peut pas être supprimé.");
            return;
        }

        if (!window.confirm("Supprimer " + activeFile + " ?")) {
            return;
        }

        const nextFiles = files.filter((file) => file.path !== activeFile);

        setFiles(nextFiles);
        setActiveFile(nextFiles[0].path);
        pushTerminal("✓ Fichier supprimé : " + activeFile);
    }

    function runPreview() {
        setPanel("preview");
        setPreviewVersion((value) => value + 1);
        pushTerminal("▶ Aperçu actualisé.");
    }

    async function runServer(commandOverride = null) {
        if (running) {
            return;
        }

        setRunning(true);
        setPanel("terminal");

        const commandToRun =
            commandOverride ??
            workspace.run_command ??
            "run";

        pushTerminal(
            "",
            "$ " + commandToRun,
            "Exécution avec le runtime " +
                workspace.label +
                " de DevRoad...",
        );

        try {
            const response = await fetch("/steps/" + stepId + "/run", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") ?? "",
                },
                body: JSON.stringify({
                    language: workspace.language,
                    command: commandToRun,
                    file_path: activeFile,
                    code: currentFile?.content ?? "",
                    files,
                }),
            });

            const result = await response.json();

            if (result.stdout) {
                pushTerminal(result.stdout);
            }

            if (result.stderr) {
                pushTerminal(result.stderr);
            }

            if (!result.stdout && !result.stderr) {
                pushTerminal(
                    result.status === "success"
                        ? "✓ Processus terminé sans sortie."
                        : "Aucune sortie.",
                );
            }

            pushTerminal(
                "Processus terminé · code " +
                    (result.exit_code ?? "n/a") +
                    " · " +
                    (result.duration_ms ?? 0) +
                    " ms",
            );
        } catch (error) {
            pushTerminal(
                "✕ Impossible d’exécuter le runtime DevRoad.",
            );
        } finally {
            setRunning(false);
        }
    }


    function runJavaScript() {
        if (workspace.language !== "javascript") {
            pushTerminal(
                "ℹ L’exécution navigateur est disponible pour JavaScript.",
            );
            return;
        }

        setPanel("terminal");
        pushTerminal("▶ Exécution JavaScript dans un iframe sandbox...");

        const iframe = document.createElement("iframe");

        iframe.setAttribute("sandbox", "allow-scripts");
        iframe.style.position = "fixed";
        iframe.style.width = "1px";
        iframe.style.height = "1px";
        iframe.style.opacity = "0";
        iframe.style.pointerEvents = "none";

        const safeCode = currentFile?.content ?? "";

        iframe.srcdoc = `
            <!doctype html>
            <html>
                <body>
                    <script>
                        const send = (type, value) => {
                            parent.postMessage({
                                source: "devroad-ide",
                                type,
                                value: String(value)
                            }, "*");
                        };

                        console.log = (...values) => send("log", values.join(" "));
                        console.warn = (...values) => send("log", values.join(" "));
                        console.error = (...values) => send("error", values.join(" "));

                        try {
                            ${safeCode}
                        } catch (error) {
                            send("error", error?.stack || error);
                        }
                    <\/script>
                </body>
            </html>
        `;

        document.body.appendChild(iframe);

        window.setTimeout(() => {
            iframe.remove();
        }, 1800);
    }

    function submitCommand(event) {
        event.preventDefault();

        const value = command.trim();

        if (!value) {
            return;
        }

        setCommand("");

        if (value === "clear") {
            setTerminal([]);
            return;
        }

        if (
            workspace.runtime === "server" &&
            /^(node|npm)(?:\s|$)/i.test(value) &&
            workspace.language !== "node"
        ) {
            pushTerminal(
                "⚠ Runtime actif : " + workspace.label,
                "La commande « " +
                    value +
                    " » nécessite un environnement Node.js.",
                "Sélectionne une roadmap Node.js dans le sélecteur de runtime.",
            );
            return;
        }

        if (
            workspace.runtime === "server" &&
            /^php(?:\s|$)|^composer(?:\s|$)/i.test(value) &&
            workspace.language !== "php" &&
            workspace.language !== "laravel"
        ) {
            pushTerminal(
                "⚠ Runtime actif : " + workspace.label,
                "Cette commande nécessite un environnement PHP/Laravel.",
            );
            return;
        }

        if (value === "help") {
            pushTerminal(
                "",
                ...(TERMINAL_HELP[workspace.language] ??
                    TERMINAL_HELP.browser),
            );
            return;
        }

        if (value === "reset") {
            resetWorkspace();
            return;
        }

        if (value === "save") {
            saveWorkspace();
            return;
        }

        if (workspace.runtime === "server") {
            runServer(value === "run" ? workspace.run_command : value);
            return;
        }

        pushTerminal("$ " + value);

        if (value === "pwd") {
            pushTerminal("/devroad/" + workspace.language);
            return;
        }

        if (value === "ls") {
            pushTerminal(...files.map((file) => file.path));
            return;
        }

        if (value.startsWith("cat ")) {
            const path = value.slice(4).trim();
            const target = files.find((file) => file.path === path);

            if (!target) {
                pushTerminal("cat: " + path + ": fichier introuvable");
                return;
            }

            pushTerminal(...target.content.split("\n"));
            return;
        }

        if (value.startsWith("touch ")) {
            const path = value.slice(6).trim();

            if (!path || files.some((file) => file.path === path)) {
                pushTerminal("touch: fichier invalide ou déjà existant");
                return;
            }

            setFiles((current) => [...current, { path, content: "" }]);
            pushTerminal("✓ " + path);
            return;
        }

        if (value.startsWith("mkdir ")) {
            pushTerminal(
                "✓ Dossier virtuel créé : " + value.slice(6).trim(),
            );
            return;
        }

        if (value.startsWith("rm ")) {
            const path = value.slice(3).trim();

            if (!files.some((file) => file.path === path)) {
                pushTerminal("rm: " + path + ": fichier introuvable");
                return;
            }

            if (files.length <= 1) {
                pushTerminal("rm: impossible de supprimer le dernier fichier");
                return;
            }

            const nextFiles = files.filter((file) => file.path !== path);
            setFiles(nextFiles);

            if (activeFile === path) {
                setActiveFile(nextFiles[0].path);
            }

            pushTerminal("✓ supprimé : " + path);
            return;
        }

        if (value === "preview" || value === "run") {
            if (value === "run") {
                runJavaScript();
            } else {
                runPreview();
            }
            return;
        }

        if (value === "reset") {
            resetWorkspace();
            return;
        }

        if (value === "save") {
            saveWorkspace();
            return;
        }

        if (
            value.startsWith("php artisan") ||
            value.startsWith("composer") ||
            value === "npm run build"
        ) {
            pushTerminal(
                "ℹ Le terminal Laravel complet nécessite un sandbox serveur.",
                "Le mode navigateur reste disponible pour éditer, sauvegarder et prévisualiser.",
            );
            return;
        }

        pushTerminal(
            "commande inconnue : " + value,
            "Utilise « help » pour voir les commandes disponibles.",
        );
    }

    function buildPreviewDocument() {
        const htmlFile =
            files.find((file) => file.path.endsWith(".html")) ?? null;
        const cssFiles = files.filter((file) => file.path.endsWith(".css"));
        const jsFiles = files.filter((file) => file.path.endsWith(".js"));

        if (htmlFile) {
            let html = htmlFile.content;

            if (cssFiles.length > 0 && !html.includes("</head>")) {
                html = cssFiles
                    .map((file) => "<style>" + file.content + "</style>")
                    .join("\n") + html;
            } else {
                for (const file of cssFiles) {
                    html = html.replace(
                        "</head>",
                        "<style>" + file.content + "</style></head>",
                    );
                }
            }

            for (const file of jsFiles) {
                html = html.replace(
                    "</body>",
                    "<script>" + file.content + "</script></body>",
                );
            }

            return securePreview(html);
        }

        if (workspace.language === "css") {
            return securePreview(
                "<!doctype html><html><head><style>" +
                    (currentFile?.content ?? "") +
                    "</style></head><body><main><h1>DevRoad</h1><p>Prévisualisation CSS</p><button>Continuer</button></main></body></html>",
            );
        }

        if (workspace.language === "javascript") {
            return securePreview(
                "<!doctype html><html><body><main id=\"app\"></main><script>" +
                    (currentFile?.content ?? "") +
                    "</script></body></html>",
            );
        }

        return securePreview(
            "<!doctype html><html><body style=\"font-family:system-ui;padding:2rem\"><h2>Prévisualisation indisponible</h2><p>Le navigateur ne peut pas exécuter directement Laravel/PHP.</p></body></html>",
        );
    }

    function securePreview(html) {
        return html.replace(
            "<head>",
            "<head><meta http-equiv=\"Content-Security-Policy\" content=\"default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data:; font-src data:\">",
        );
    }

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

                <div className="flex gap-1 overflow-x-auto">
                    {files.map((file) => (
                        <button
                            key={file.path}
                            type="button"
                            onClick={() => setActiveFile(file.path)}
                            className={[
                                "inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-[11px] font-semibold",
                                activeFile === file.path
                                    ? "bg-[#FF6A00] text-[#08111F]"
                                    : "bg-white/[0.03] text-slate-500 hover:text-white",
                            ].join(" ")}
                        >
                            <FileCode2 size={13} />
                            {file.path}
                        </button>
                    ))}

                    <button
                        type="button"
                        onClick={openCreateFileModal}
                        className="shrink-0 rounded-lg border border-dashed border-white/[0.08] px-3 py-2 text-slate-600 transition hover:border-[#FF6A00]/30 hover:bg-[#FF6A00]/[0.06] hover:text-[#FF8A3D]"
                        aria-label="Nouveau fichier"
                        title="Nouveau fichier"
                    >
                        <FilePlus2 size={14} />
                    </button>

                    <button
                        type="button"
                        onClick={() => importInputRef.current?.click()}
                        className="shrink-0 rounded-lg border border-dashed border-white/[0.08] px-3 py-2 text-slate-600 transition hover:border-[#FF6A00]/30 hover:bg-[#FF6A00]/[0.06] hover:text-[#FF8A3D]"
                        aria-label="Importer des fichiers"
                        title="Importer des fichiers"
                    >
                        <Upload size={14} />
                    </button>
                </div>
            </div>

            <div className="grid lg:grid-cols-[210px_minmax(0,1fr)]">
                <aside className="hidden border-r border-white/[0.06] bg-[#07101A] lg:block">
                    <div className="flex items-center justify-between border-b border-white/[0.05] px-3 py-3">
                        <div className="flex items-center gap-2">
                            <FolderOpen size={14} className="text-[#FF8A3D]" />
                            <span className="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-600">
                                Explorateur
                            </span>
                        </div>

                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                onClick={openCreateFileModal}
                                className="rounded-lg p-1.5 text-slate-600 transition hover:bg-white/[0.04] hover:text-white"
                                aria-label="Créer un fichier"
                                title="Créer un fichier"
                            >
                                <FilePlus2 size={14} />
                            </button>

                            <button
                                type="button"
                                onClick={() => importInputRef.current?.click()}
                                className="rounded-lg p-1.5 text-slate-600 transition hover:bg-white/[0.04] hover:text-[#FF8A3D]"
                                aria-label="Importer des fichiers"
                                title="Importer des fichiers"
                            >
                                <Upload size={14} />
                            </button>
                        </div>
                    </div>

                    <div className="max-h-[540px] overflow-y-auto p-2">
                        {files.map((file) => (
                            <button
                                key={file.path}
                                type="button"
                                onClick={() => setActiveFile(file.path)}
                                className={[
                                    "mb-1 flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-[11px]",
                                    activeFile === file.path
                                        ? "bg-[#FF6A00]/10 text-[#FF8A3D]"
                                        : "text-slate-500 hover:bg-white/[0.03] hover:text-white",
                                ].join(" ")}
                            >
                                <Folder size={12} className="shrink-0" />
                                <span className="truncate">{file.path}</span>
                            </button>
                        ))}
                    </div>
                </aside>

                <div className="min-w-0">
                    <div className="flex items-center justify-between border-b border-white/[0.05] bg-[#0D1725] px-3 py-2">
                        <div className="flex items-center gap-2 text-[10px] text-slate-600">
                            <FileCode2 size={12} />
                            {activeFile}
                            <span>{lineCount} lignes</span>
                        </div>

                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                onClick={copyCode}
                                className="rounded-lg p-2 text-slate-600 hover:bg-white/[0.05] hover:text-white"
                                aria-label="Copier"
                            >
                                {copied ? <Check size={13} /> : <Clipboard size={13} />}
                            </button>

                            <button
                                type="button"
                                onClick={deleteFile}
                                className="rounded-lg p-2 text-slate-600 hover:bg-red-500/10 hover:text-red-400"
                                aria-label="Supprimer le fichier"
                            >
                                <Trash2 size={13} />
                            </button>
                        </div>
                    </div>

                    <div className="flex h-[390px] bg-[#06101A]">
                        <div className="w-11 shrink-0 overflow-hidden border-r border-white/[0.04] bg-[#08111C] py-3 text-right font-mono text-[10px] leading-6 text-slate-700">
                            {Array.from({ length: lineCount }, (_, index) => (
                                <div key={index} className="pr-2">
                                    {index + 1}
                                </div>
                            ))}
                        </div>

                        <textarea
                            value={currentFile?.content ?? ""}
                            onChange={(event) =>
                                updateCurrentFile(event.target.value)
                            }
                            spellCheck={false}
                            className="min-w-0 flex-1 resize-none border-0 bg-[#06101A] p-4 font-mono text-[12px] leading-6 text-slate-300 outline-none focus:ring-0"
                            aria-label={"Éditeur " + activeFile}
                        />
                    </div>

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
                        <div className="flex min-h-[250px] flex-col bg-[#050B12]">
                            <div className="flex-1 overflow-y-auto p-4 font-mono text-[11px] leading-6 text-slate-400">
                                {terminal.map((line, index) => (
                                    <pre
                                        key={index}
                                        className="whitespace-pre-wrap break-words"
                                    >
                                        {line}
                                    </pre>
                                ))}
                                <div ref={terminalEndRef} />
                            </div>

                            <form
                                onSubmit={submitCommand}
                                className="border-t border-white/[0.05] bg-[#07101A] p-3"
                            >
                                <div className="flex items-center gap-2">
                                    <span className="font-mono text-xs text-[#FF8A3D]">
                                        $
                                    </span>
                                    <input
                                        value={command}
                                        onChange={(event) =>
                                            setCommand(event.target.value)
                                        }
                                        placeholder="help"
                                        className="min-w-0 flex-1 border-0 bg-transparent px-0 py-2 font-mono text-xs text-white outline-none placeholder:text-slate-700 focus:ring-0"
                                        aria-label="Commande du terminal"
                                    />
                                    <button
                                        type="submit"
                                        className="rounded-lg p-2 text-slate-600 hover:bg-white/[0.05] hover:text-white"
                                        aria-label="Exécuter"
                                    >
                                        <ChevronDown
                                            size={15}
                                            className="rotate-[-90deg]"
                                        />
                                    </button>
                                </div>
                            </form>

                            <div className="flex items-center justify-between border-t border-white/[0.05] px-4 py-2 text-[10px] text-slate-700">
                                <span>Shell pédagogique</span>
                                <button
                                    type="button"
                                    onClick={() => setTerminal([])}
                                    className="inline-flex items-center gap-1 hover:text-slate-400"
                                >
                                    <Trash2 size={11} />
                                    Effacer
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div className="bg-[#050B12] p-2">
                            <div className="mb-2 flex items-center justify-between px-2">
                                <div className="flex items-center gap-2">
                                    <Eye size={13} className="text-[#FF8A3D]" />
                                    <span className="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                                        Aperçu
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    onClick={runPreview}
                                    className="inline-flex items-center gap-1.5 rounded-lg bg-[#FF6A00] px-2.5 py-2 text-[10px] font-bold text-[#08111F]"
                                >
                                    <Play size={11} fill="currentColor" />
                                    Actualiser
                                </button>
                            </div>

                            <iframe
                                key={previewVersion}
                                title="Prévisualisation DevRoad"
                                sandbox="allow-scripts"
                                srcDoc={buildPreviewDocument()}
                                className="h-[420px] w-full rounded-2xl border border-white/[0.06] bg-white"
                            />
                        </div>
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
