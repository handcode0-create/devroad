import axios from "axios";
import {
    Check,
    ChevronDown,
    Clipboard,
    Code2,
    Play,
    RotateCcw,
    Terminal,
    Trash2,
} from "lucide-react";
import { useEffect, useMemo, useRef, useState } from "react";

const TERMINAL_HELP = [
    "Commandes disponibles :",
    "  help          afficher cette aide",
    "  clear         vider le terminal",
    "  reset         restaurer le code initial",
    "  php main.php  exécuter le fichier PHP",
    "  node main.js  exécuter le fichier JavaScript",
];

export default function CodeWorkspace({ workspace, stepId }) {
    const [code, setCode] = useState(workspace.initial_code ?? "");
    const [output, setOutput] = useState([
        "DevRoad Terminal",
        workspace.label + " sandbox prêt.",
        "Tape « help » pour voir les commandes disponibles.",
    ]);
    const [command, setCommand] = useState("");
    const [running, setRunning] = useState(false);
    const [copied, setCopied] = useState(false);

    const lineNumbersRef = useRef(null);
    const terminalEndRef = useRef(null);

    const lineCount = useMemo(
        () => Math.max(1, code.split("\n").length),
        [code],
    );

    useEffect(() => {
        terminalEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [output]);

    function pushOutput(...lines) {
        setOutput((current) => [...current, ...lines]);
    }

    function syncEditorScroll(event) {
        if (lineNumbersRef.current) {
            lineNumbersRef.current.scrollTop = event.currentTarget.scrollTop;
        }
    }

    function clearTerminal() {
        setOutput([]);
    }

    function resetCode() {
        setCode(workspace.initial_code ?? "");
        pushOutput("↳ Code restauré.");
    }

    async function runCode() {
        if (running) {
            return;
        }

        setRunning(true);
        pushOutput(
            "",
            "$ " + workspace.run_command,
            "Exécution dans le sandbox DevRoad...",
        );

        try {
            const response = await axios.post(
                "/steps/" + stepId + "/run",
                {
                    language: workspace.language,
                    code,
                },
            );

            const result = response.data;

            if (result.stdout) {
                pushOutput(result.stdout);
            }

            if (result.stderr) {
                pushOutput(result.stderr);
            }

            if (!result.stdout && !result.stderr) {
                pushOutput(
                    result.status === "success"
                        ? "✓ Programme terminé sans sortie."
                        : "Aucune sortie.",
                );
            }

            pushOutput(
                "Processus terminé · code " +
                    (result.exit_code ?? "n/a") +
                    " · " +
                    (result.duration_ms ?? 0) +
                    " ms",
            );
        } catch (error) {
            const message =
                error.response?.data?.stderr ||
                error.response?.data?.message ||
                "Impossible de joindre le sandbox DevRoad.";

            pushOutput("✕ " + message);
        } finally {
            setRunning(false);
        }
    }

    async function copyCode() {
        await navigator.clipboard.writeText(code);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 1600);
    }

    function submitCommand(event) {
        event.preventDefault();

        const value = command.trim();

        if (!value) {
            return;
        }

        setCommand("");

        if (value === "clear") {
            clearTerminal();
            return;
        }

        if (value === "help") {
            pushOutput("", ...TERMINAL_HELP);
            return;
        }

        if (value === "reset") {
            resetCode();
            return;
        }

        if (
            value === workspace.run_command ||
            (workspace.language === "php" && value === "php") ||
            (workspace.language === "javascript" && value === "node")
        ) {
            runCode();
            return;
        }

        pushOutput(
            "",
            "bash: " +
                value +
                ": commande non disponible dans le terminal pédagogique.",
            "Utilise « help » pour voir les commandes supportées.",
        );
    }

    function handleEditorKeyDown(event) {
        if (event.key === "Tab") {
            event.preventDefault();

            const textarea = event.currentTarget;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;

            setCode(code.slice(0, start) + "    " + code.slice(end));

            requestAnimationFrame(() => {
                textarea.selectionStart = start + 4;
                textarea.selectionEnd = start + 4;
            });
        }

        if ((event.ctrlKey || event.metaKey) && event.key === "Enter") {
            event.preventDefault();
            runCode();
        }
    }

    return (
        <section className="overflow-hidden rounded-3xl border border-white/[0.07] bg-[#09111D] shadow-[0_20px_60px_rgba(0,0,0,0.28)]">
            <div className="flex flex-col border-b border-white/[0.06] sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-3 px-4 py-3">
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FF6A00]/10 text-[#FF8A3D]">
                        <Code2 size={16} />
                    </div>

                    <div>
                        <p className="text-xs font-bold text-white">DevLab</p>
                        <p className="text-[10px] text-slate-600">
                            {workspace.label} · {workspace.filename}
                        </p>
                    </div>
                </div>

                <div className="flex flex-wrap items-center gap-2 px-4 pb-3 sm:pb-0">
                    <button
                        type="button"
                        onClick={resetCode}
                        className="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.06] bg-white/[0.03] px-2.5 py-2 text-[11px] font-semibold text-slate-400 transition hover:bg-white/[0.06] hover:text-white"
                    >
                        <RotateCcw size={13} />
                        Réinitialiser
                    </button>

                    <button
                        type="button"
                        onClick={copyCode}
                        className="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.06] bg-white/[0.03] px-2.5 py-2 text-[11px] font-semibold text-slate-400 transition hover:bg-white/[0.06] hover:text-white"
                    >
                        <Clipboard size={13} />
                        {copied ? "Copié" : "Copier"}
                    </button>

                    <button
                        type="button"
                        onClick={runCode}
                        disabled={running}
                        className="inline-flex items-center gap-1.5 rounded-lg bg-[#FF6A00] px-3 py-2 text-[11px] font-bold text-[#08111F] transition hover:bg-[#ff781a] disabled:cursor-wait disabled:opacity-60"
                    >
                        <Play size={13} fill="currentColor" />
                        {running ? "Exécution..." : "Exécuter"}
                    </button>
                </div>
            </div>

            <div className="grid lg:grid-cols-[minmax(0,1fr)_340px]">
                <div className="min-w-0 border-b border-white/[0.06] lg:border-b-0 lg:border-r">
                    <div className="flex items-center gap-2 border-b border-white/[0.05] bg-[#0D1725] px-4 py-2.5">
                        <span className="h-2.5 w-2.5 rounded-full bg-red-400/70" />
                        <span className="h-2.5 w-2.5 rounded-full bg-amber-400/70" />
                        <span className="h-2.5 w-2.5 rounded-full bg-emerald-400/70" />
                        <span className="ml-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                            {workspace.filename}
                        </span>
                    </div>

                    <div className="flex h-[420px] bg-[#07101A]">
                        <div
                            ref={lineNumbersRef}
                            className="w-12 shrink-0 overflow-hidden border-r border-white/[0.04] bg-[#08111C] py-4 text-right font-mono text-[11px] leading-6 text-slate-700"
                        >
                            {Array.from({ length: lineCount }, (_, index) => (
                                <div key={index} className="pr-3">
                                    {index + 1}
                                </div>
                            ))}
                        </div>

                        <textarea
                            value={code}
                            onChange={(event) => setCode(event.target.value)}
                            onScroll={syncEditorScroll}
                            onKeyDown={handleEditorKeyDown}
                            spellCheck={false}
                            className="min-w-0 flex-1 resize-none border-0 bg-[#07101A] p-4 font-mono text-[12px] leading-6 text-slate-300 outline-none focus:ring-0"
                            aria-label={"Éditeur " + workspace.label}
                        />
                    </div>

                    <div className="border-t border-white/[0.05] bg-[#08111C] px-4 py-2 text-[10px] text-slate-600">
                        Ctrl/Cmd + Entrée · exécuter · Tab · indentation
                    </div>
                </div>

                <div className="flex min-h-[420px] flex-col bg-[#050B12]">
                    <div className="flex items-center gap-2 border-b border-white/[0.05] bg-[#08111C] px-4 py-2.5">
                        <Terminal size={14} className="text-[#FF8A3D]" />
                        <span className="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                            Terminal
                        </span>
                        {running && (
                            <span className="ml-auto text-[10px] font-semibold text-[#FF8A3D]">
                                en cours...
                            </span>
                        )}
                    </div>

                    <div className="flex-1 overflow-y-auto p-4 font-mono text-[11px] leading-6 text-slate-400">
                        {output.length === 0 ? (
                            <p className="text-slate-700">Terminal vide.</p>
                        ) : (
                            output.map((line, index) => (
                                <pre
                                    key={index}
                                    className="whitespace-pre-wrap break-words"
                                >
                                    {line}
                                </pre>
                            ))
                        )}
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
                                onChange={(event) => setCommand(event.target.value)}
                                placeholder={workspace.run_command}
                                className="min-w-0 flex-1 border-0 bg-transparent px-0 py-2 font-mono text-xs text-white outline-none placeholder:text-slate-700 focus:ring-0"
                                aria-label="Commande du terminal"
                            />
                            <button
                                type="submit"
                                className="rounded-lg p-2 text-slate-600 transition hover:bg-white/[0.05] hover:text-white"
                                aria-label="Exécuter la commande"
                            >
                                <ChevronDown size={15} className="rotate-[-90deg]" />
                            </button>
                        </div>
                    </form>

                    <div className="flex items-center justify-between border-t border-white/[0.05] px-4 py-2 text-[10px] text-slate-700">
                        <span>Sandbox isolé</span>
                        <button
                            type="button"
                            onClick={clearTerminal}
                            className="inline-flex items-center gap-1 transition hover:text-slate-400"
                        >
                            <Trash2 size={11} />
                            Effacer
                        </button>
                    </div>
                </div>
            </div>
        </section>
    );
}
