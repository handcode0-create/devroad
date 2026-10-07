import { Head } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { appendTerminalOutput } from "@/Components/Sandbox/terminalText";
import React, { useEffect, useState } from "react";
import { Box, ChevronDown, CircleStop, ExternalLink, History, LoaderCircle, Play, Plus, RefreshCw, RotateCcw, SquareTerminal, Trash2 } from "lucide-react";

export default function Index({ projects = [], templates = {}, runtime_configured = false, runtime_message = "" }) {
    const [items, setItems] = useState(projects);
    const [create, setCreate] = useState(false);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState("");
    const [selected, setSelected] = useState(null);

    async function request(url, options = {}) {
        const response = await fetch(url, {
            credentials: "same-origin",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content ?? "",
                ...(options.headers ?? {}),
            },
            ...options,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message ?? "Une erreur est survenue.");
        }

        return data;
    }

    async function createProject(name, template) {
        setLoading(true);
        setMessage("");
        try {
            const data = await request("/sandbox/projects", {
                method: "POST",
                body: JSON.stringify({ name, template }),
            });
            setItems((current) => [data.project, ...current]);
            setCreate(false);
        } catch (error) {
            setMessage(error.message);
        } finally {
            setLoading(false);
        }
    }

    async function refreshProject(project) {
        const data = await request("/sandbox/projects/" + project.id + "/status");
        setItems((current) => current.map((item) => item.id === project.id ? { ...item, ...data.project } : item));

        if (data.error) {
            setMessage(data.error);
        }

        return data.project;
    }

    const startupMessages = {
        provisioning: "Création de l’environnement…",
        installing: "Installation des dépendances…",
        starting_server: "Démarrage du serveur…",
        ready: "Environnement prêt.",
    };

    async function waitForStartup(projectId) {
        const startedAt = Date.now();

        while (Date.now() - startedAt < 20 * 60 * 1000) {
            await new Promise((resolve) => setTimeout(resolve, 2000));

            try {
                const data = await request("/sandbox/projects/" + projectId + "/status");
                setItems((current) => current.map((item) => item.id === projectId ? { ...item, ...data.project } : item));

                if (data.project.status === "running") {
                    setMessage("Environnement prêt.");
                    return;
                }

                if (data.project.status === "starting") {
                    setMessage(startupMessages[data.phase] || "Préparation de l’environnement…");
                }

                if (data.project.status === "error") {
                    setMessage(data.error || "La préparation du Sandbox a échoué.");
                    return;
                }
            } catch (error) {
                setMessage(error.message);
                return;
            }
        }

        setMessage("Le démarrage du Sandbox prend trop de temps. Vérifie son état avant de relancer.");
    }

    async function run(project, action) {
        setLoading(true);
        setMessage("");
        try {
            const data = await request("/sandbox/projects/" + project.id + "/" + action, { method: "POST" });
            setItems((current) => current.map((item) => item.id === project.id ? { ...item, ...data.project } : item));

            if (action === "start" && data.queued) {
                setMessage("Préparation de l’environnement…");
                setLoading(false);
                await waitForStartup(project.id);
                return;
            }
        } catch (error) {
            setMessage(error.message);
        } finally {
            setLoading(false);
        }
    }

    async function command(project, value) {
        if (!value.trim()) return;

        setLoading(true);
        setMessage("");
        try {
            const data = await request("/sandbox/projects/" + project.id + "/command", {
                method: "POST",
                body: JSON.stringify({ command: value, timeout: 120 }),
            });
            setSelected((current) => ({
                ...(current ?? project),
                terminal: {
                    command: value,
                    output: data.output ?? "",
                    exit_code: data.exit_code,
                },
            }));
        } catch (error) {
            setMessage(error.message);
        } finally {
            setLoading(false);
        }
    }

    async function remove(project) {
        if (!window.confirm("Supprimer « " + project.name + " » ?")) return;

        setLoading(true);
        setMessage("");
        try {
            await request("/sandbox/projects/" + project.id, { method: "DELETE" });
            setItems((current) => current.filter((item) => item.id !== project.id));
        } catch (error) {
            setMessage(error.message);
        } finally {
            setLoading(false);
        }
    }

    return (
        <AppLayout>
            <Head title="Sandbox" />

            <div className="space-y-6">
                <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-[var(--dr-accent-text)]">
                            <Box size={15} />
                            DevRoad Sandbox
                        </div>
                        <h1 className="mt-2 text-3xl font-extrabold tracking-tight">Environnements de développement</h1>
                        <p className="mt-2 max-w-2xl text-sm leading-6 text-[var(--dr-text-2)]">
                            Lance de vrais projets React, Next.js, Node.js, PHP et Laravel dans un environnement isolé.
                        </p>
                    </div>

                    <button
                        onClick={() => setCreate(true)}
                        className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#FF6A00] px-4 text-sm font-bold text-[var(--dr-ink)]"
                    >
                        <Plus size={17} />
                        Nouveau Sandbox
                    </button>
                </header>

                {!runtime_configured && (
                    <div className="rounded-2xl border border-amber-400/15 bg-amber-400/[0.06] p-4 text-sm text-[var(--dr-warning)]">
                        {runtime_message || "Le runtime Sandbox n’est pas encore prêt sur cet environnement."}
                    </div>
                )}

                {message && (
                    <div className="rounded-2xl border border-red-400/15 bg-red-500/[0.06] p-4 text-sm text-[var(--dr-danger)]">
                        {message}
                    </div>
                )}

                {items.length === 0 ? (
                    <button
                        onClick={() => setCreate(true)}
                        className="w-full rounded-3xl border border-dashed border-[var(--dr-border-2)] bg-[var(--dr-surface)] p-12 text-center transition hover:border-[#FF6A00]/30"
                    >
                        <SquareTerminal className="mx-auto text-[var(--dr-accent-text)]" size={30} />
                        <p className="mt-4 font-semibold">Aucun Sandbox</p>
                        <p className="mt-1 text-sm text-[var(--dr-text-3)]">Crée ton premier environnement de développement.</p>
                    </button>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {items.map((project) => (
                            <article key={project.id} className="min-w-0 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="truncate font-semibold">{project.name}</p>
                                        <p className="mt-1 text-xs text-[var(--dr-text-3)]">
                                            {templates[project.template]?.label ?? project.template} · {project.runtime} {project.runtime_version}
                                        </p>
                                    </div>
                                    <Status status={project.status} />
                                </div>

                                <div className="mt-5 flex items-center gap-2">
                                    {project.status === "running" ? (
                                        <button onClick={() => run(project, "stop")} className="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-[var(--dr-hover)] text-xs font-semibold text-[var(--dr-text)]">
                                            <CircleStop size={15} /> Arrêter
                                        </button>
                                    ) : project.status === "starting" ? (
                                        <div className="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl border border-[#FF6A00]/20 bg-[#FF6A00]/[0.06] text-xs font-semibold text-[var(--dr-accent-text)]">
                                            <LoaderCircle size={15} className="animate-spin" /> Compilation…
                                        </div>
                                    ) : (
                                        <button onClick={() => run(project, "start")} disabled={loading} className="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-[#FF6A00] text-xs font-bold text-[var(--dr-ink)] disabled:opacity-50">
                                            {loading ? <LoaderCircle size={15} className="animate-spin" /> : <Play size={15} />} Démarrer
                                        </button>
                                    )}
                                    <button onClick={() => run(project, "restart")} disabled={loading || project.status === "starting"} className="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl bg-[var(--dr-hover)] text-[var(--dr-text-2)]" aria-label="Redémarrer">
                                        <RotateCcw size={15} />
                                    </button>
                                    <button onClick={() => remove(project)} disabled={loading} className="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl bg-red-500/[0.06] text-[var(--dr-danger)]" aria-label="Supprimer">
                                        <Trash2 size={15} />
                                    </button>
                                </div>

                                {project.status === "running" && (
                                    <TerminalBox
                                        project={project}
                                        loading={loading}
                                        result={selected?.id === project.id ? selected.terminal : null}
                                        onRun={command}
                                    />
                                )}

                                {project.preview_url && (
                                    <a href={project.preview_url} target="_blank" rel="noreferrer" className="mt-3 inline-flex items-center gap-1.5 text-xs text-[var(--dr-accent-text)]">
                                        Ouvrir le preview <ExternalLink size={13} />
                                    </a>
                                )}

                                <ProcessHistory project={project} />
                            </article>
                        ))}
                    </div>
                )}

                {create && (
                    <CreateModal templates={templates} loading={loading} onClose={() => setCreate(false)} onCreate={createProject} />
                )}
            </div>
        </AppLayout>
    );
}


function TerminalBox({ project }) {
    const [value, setValue] = useState("");
    const [output, setOutput] = useState("");
    const [status, setStatus] = useState("connexion");
    const [attempt, setAttempt] = useState(0);
    const socketRef = React.useRef(null);
    const outputRef = React.useRef(null);

    React.useEffect(() => {
        let cancelled = false;

        async function connect() {
            setStatus("connexion");
            try {
                const response = await fetch("/sandbox/projects/" + project.id + "/terminal", {
                    method: "POST",
                    credentials: "same-origin",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content ?? "",
                    },
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) throw new Error(data.message ?? "Terminal indisponible.");
                if (cancelled) return;

                const socket = new WebSocket(data.url);
                socketRef.current = socket;

                socket.onopen = () => setStatus("connecté");
                socket.onclose = () => setStatus("déconnecté");
                socket.onerror = () => setStatus("erreur");
                socket.onmessage = (event) => {
                    if (typeof event.data !== "string") return;
                    try {
                        const message = JSON.parse(event.data);
                        if (message.type === "ready") {
                            setStatus("connecté");
                            return;
                        }
                        if (message.type === "error") {
                            setOutput((current) => appendTerminalOutput(current, "\n[DevRoad] " + message.message + "\n"));
                            setStatus("erreur");
                            return;
                        }
                    } catch {
                        // Daytona PTY output is raw terminal data.
                    }
                    setOutput((current) => appendTerminalOutput(current, event.data));
                };
            } catch (error) {
                if (!cancelled) {
                    setStatus("indisponible");
                    setOutput((current) => appendTerminalOutput(current, "\n[DevRoad] " + error.message + "\n"));
                }
            }
        }

        connect();

        return () => {
            cancelled = true;
            socketRef.current?.close();
            socketRef.current = null;
        };
    }, [project.id, attempt]);

    React.useEffect(() => {
        if (outputRef.current) outputRef.current.scrollTop = outputRef.current.scrollHeight;
    }, [output]);

    function submit(event) {
        event.preventDefault();
        const command = value.trim();
        if (!command || socketRef.current?.readyState !== WebSocket.OPEN) return;

        socketRef.current.send(command + "\n");
        setValue("");
    }

    function handleKeyDown(event) {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            event.currentTarget.form?.requestSubmit();
        }
        if (event.ctrlKey && event.key.toLowerCase() === "c") {
            event.preventDefault();
            socketRef.current?.send("\u0003");
        }
    }

    return (
        <div className="mt-4 overflow-hidden rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)]">
            <div className="flex items-center justify-between border-b border-[var(--dr-border)] px-3 py-2">
                <div className="flex items-center gap-2">
                    <SquareTerminal size={14} className="text-[var(--dr-accent-text)]" />
                    <span className="text-[10px] font-bold uppercase tracking-wider text-[var(--dr-text-3)]">Terminal PTY</span>
                </div>
                <div className="flex items-center gap-2">
                    {status !== "connecté" && status !== "connexion" && (
                        <button
                            type="button"
                            onClick={() => setAttempt((count) => count + 1)}
                            className="inline-flex items-center gap-1 rounded-md bg-[var(--dr-hover)] px-2 py-1 text-[10px] font-semibold text-[var(--dr-text-2)]"
                        >
                            <RefreshCw size={10} />
                            Reconnecter
                        </button>
                    )}
                    <span className={"text-[10px] font-semibold " + (status === "connecté" ? "text-[var(--dr-success)]" : "text-[var(--dr-text-3)]")}>
                        {status}
                    </span>
                </div>
            </div>

            <pre
                ref={outputRef}
                className="h-48 overflow-auto whitespace-pre-wrap p-3 font-mono text-[11px] leading-5 text-[var(--dr-text-2)]"
                aria-live="polite"
            >
                {output || "Connexion au terminal…"}
            </pre>

            <form onSubmit={submit} className="flex items-center gap-2 border-t border-[var(--dr-border)] p-2">
                <span className="font-mono text-xs text-[var(--dr-accent-text)]">›</span>
                <input
                    value={value}
                    onChange={(event) => setValue(event.target.value)}
                    onKeyDown={handleKeyDown}
                    placeholder="Tape une commande…"
                    disabled={status !== "connecté"}
                    className="min-w-0 flex-1 bg-transparent font-mono text-xs text-[var(--dr-text)] outline-none placeholder:text-[var(--dr-text-3)] disabled:opacity-50"
                    aria-label={"Entrée terminal de " + project.name}
                    autoComplete="off"
                    spellCheck="false"
                />
                <button
                    type="submit"
                    disabled={status !== "connecté" || !value.trim()}
                    className="rounded-lg bg-[var(--dr-hover)] px-2.5 py-1.5 text-[10px] font-semibold text-[var(--dr-text-2)] disabled:opacity-30"
                >
                    Entrée
                </button>
            </form>
        </div>
    );
}

const processStyles = {
    running: { label: "En cours", dot: "bg-emerald-400", text: "text-[var(--dr-success)]" },
    completed: { label: "Terminé", dot: "bg-[#FF6A00]", text: "text-[var(--dr-accent-text)]" },
    failed: { label: "Échec", dot: "bg-red-400", text: "text-[var(--dr-danger)]" },
    stopped: { label: "Arrêté", dot: "bg-[var(--dr-border-2)]", text: "text-[var(--dr-text-3)]" },
};

function formatDuration(ms) {
    if (ms === null || ms === undefined) return null;
    if (ms < 1000) return ms + " ms";
    const seconds = Math.round(ms / 1000);
    if (seconds < 60) return seconds + " s";
    return Math.floor(seconds / 60) + " min " + String(seconds % 60).padStart(2, "0") + " s";
}

function formatWhen(iso) {
    if (!iso) return "";
    const date = new Date(iso);
    const diff = Math.round((Date.now() - date.getTime()) / 1000);
    if (diff < 60) return "à l’instant";
    if (diff < 3600) return "il y a " + Math.floor(diff / 60) + " min";
    if (diff < 86400) return "il y a " + Math.floor(diff / 3600) + " h";
    return date.toLocaleDateString("fr-FR", { day: "numeric", month: "short" });
}

// Historique des processus du Sandbox (installation, serveur, commandes).
// Chargé uniquement à l'ouverture pour limiter la consommation de données.
function ProcessHistory({ project }) {
    const [open, setOpen] = useState(false);
    const [processes, setProcesses] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");
    const [expanded, setExpanded] = useState(null);

    async function load() {
        setLoading(true);
        setError("");
        try {
            const response = await fetch("/sandbox/projects/" + project.id + "/processes", {
                credentials: "same-origin",
                headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message ?? "Historique indisponible.");
            setProcesses(data.processes ?? []);
        } catch (exception) {
            setError(exception.message);
        } finally {
            setLoading(false);
        }
    }

    // Recharge quand le Sandbox change d'état (démarrage, arrêt…) si le panneau est ouvert.
    useEffect(() => {
        if (open) load();
    }, [open, project.status]);

    return (
        <div className="mt-4 min-w-0 border-t border-[var(--dr-border)] pt-3">
            <div className="flex items-center gap-2">
                <button
                    type="button"
                    onClick={() => setOpen((value) => !value)}
                    aria-expanded={open}
                    className="inline-flex min-h-10 flex-1 items-center gap-2 rounded-lg text-left text-xs font-semibold text-[var(--dr-text-2)] hover:text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#FF6A00]/60"
                >
                    <History size={14} className="text-[var(--dr-accent-text)]" />
                    Historique
                    {processes && <span className="text-[var(--dr-text-3)]">({processes.length})</span>}
                    <ChevronDown size={14} className={"ml-auto transition " + (open ? "rotate-180" : "")} />
                </button>
                {open && (
                    <button
                        type="button"
                        onClick={load}
                        disabled={loading}
                        aria-label="Actualiser l’historique"
                        className="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg text-[var(--dr-text-3)] hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)] disabled:opacity-50"
                    >
                        <RefreshCw size={14} className={loading ? "animate-spin" : ""} />
                    </button>
                )}
            </div>

            {open && (
                <div className="mt-2 space-y-1.5">
                    {error && <p className="text-xs text-[var(--dr-danger)]">{error}</p>}

                    {!error && processes === null && loading && (
                        <p className="flex items-center gap-2 py-2 text-xs text-[var(--dr-text-3)]"><LoaderCircle size={13} className="animate-spin" /> Chargement…</p>
                    )}

                    {!error && processes?.length === 0 && (
                        <p className="py-2 text-xs text-[var(--dr-text-3)]">Aucun processus pour l’instant. Démarre le Sandbox pour voir l’installation et le serveur ici.</p>
                    )}

                    {processes?.map((process) => {
                        const style = processStyles[process.status] ?? processStyles.stopped;
                        const details = process.output || process.error;
                        const isOpen = expanded === process.id;
                        const meta = [
                            process.port ? "port " + process.port : null,
                            process.exit_code !== null && process.exit_code !== undefined ? "code " + process.exit_code : null,
                            formatDuration(process.duration_ms),
                            formatWhen(process.started_at),
                        ].filter(Boolean);

                        return (
                            <div key={process.id} className="rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] px-3 py-2.5">
                                <button
                                    type="button"
                                    onClick={() => details && setExpanded(isOpen ? null : process.id)}
                                    disabled={!details}
                                    aria-expanded={details ? isOpen : undefined}
                                    className="flex w-full items-start gap-2.5 text-left disabled:cursor-default"
                                >
                                    <span className={"mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full " + style.dot + (process.status === "running" ? " animate-pulse motion-reduce:animate-none" : "")} />
                                    <span className="min-w-0 flex-1">
                                        <span className="flex items-center justify-between gap-2">
                                            <span className="truncate text-xs font-semibold text-[var(--dr-text)]">{process.name}</span>
                                            <span className={"shrink-0 text-[10px] font-bold uppercase tracking-[0.08em] " + style.text}>{style.label}</span>
                                        </span>
                                        <code className="mt-0.5 block truncate font-mono text-[11px] text-[var(--dr-text-3)]">{process.command}</code>
                                        {meta.length > 0 && <span className="mt-1 block text-[10px] text-[var(--dr-text-3)]">{meta.join(" · ")}</span>}
                                    </span>
                                </button>

                                {isOpen && details && (
                                    <pre className="mt-2 max-h-56 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/30 p-2.5 font-mono text-[11px] leading-5 text-[var(--dr-text-2)]">
                                        {process.error ? "Erreur : " + process.error + (process.output ? "\n\n" + process.output : "") : process.output}
                                    </pre>
                                )}
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}

function Status({ status }) {
    const running = status === "running";
    const classes = running
        ? "bg-emerald-400/10 text-[var(--dr-success)]"
        : "bg-[var(--dr-hover)] text-[var(--dr-text-3)]";

    return (
        <span className={"inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase " + classes}>
            <span className={"h-1.5 w-1.5 rounded-full " + (running ? "bg-emerald-400" : "bg-[var(--dr-border-2)]")} />
            {status}
        </span>
    );
}

function CreateModal({ templates, loading, onClose, onCreate }) {
    const entries = Object.entries(templates);
    const [name, setName] = useState("Mon projet");
    const [template, setTemplate] = useState(entries[0]?.[0] ?? "react");

    return (
        <div className="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true">
            <div className="w-full max-w-md rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 shadow-2xl">
                <div className="flex items-center justify-between">
                    <b>Nouveau Sandbox</b>
                    <button onClick={onClose} className="text-[var(--dr-text-3)] hover:text-[var(--dr-text)]" aria-label="Fermer">×</button>
                </div>
                <label className="mt-5 block text-xs text-[var(--dr-text-2)]">
                    Nom
                    <input value={name} onChange={(event) => setName(event.target.value)} className="mt-2 w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] p-3 text-sm text-[var(--dr-text)] outline-none focus:border-[#FF6A00]/50" />
                </label>
                <label className="mt-4 block text-xs text-[var(--dr-text-2)]">
                    Template
                    <select value={template} onChange={(event) => setTemplate(event.target.value)} className="mt-2 w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] p-3 text-sm text-[var(--dr-text)] outline-none focus:border-[#FF6A00]/50">
                        {entries.map(([key, definition]) => <option key={key} value={key}>{definition.label}</option>)}
                    </select>
                </label>
                <div className="mt-5 flex justify-end gap-2">
                    <button onClick={onClose} className="px-4 py-2 text-xs text-[var(--dr-text-3)]">Annuler</button>
                    <button disabled={loading || !name.trim()} onClick={() => onCreate(name, template)} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-xs font-bold text-[var(--dr-ink)] disabled:opacity-40">
                        Créer
                    </button>
                </div>
            </div>
        </div>
    );
}
