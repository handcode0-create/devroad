import { Head } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import React, { useState } from "react";
import { Box, CircleStop, ExternalLink, LoaderCircle, Play, Plus, RotateCcw, SquareTerminal, Trash2 } from "lucide-react";

export default function Index({ projects = [], templates = {}, runtime_enabled = false }) {
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
                        <div className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-[#FF8A3D]">
                            <Box size={15} />
                            DevRoad Sandbox
                        </div>
                        <h1 className="mt-2 text-3xl font-extrabold tracking-tight">Environnements de développement</h1>
                        <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
                            Lance de vrais projets React, Next.js, Node.js, PHP et Laravel dans un environnement isolé.
                        </p>
                    </div>

                    <button
                        onClick={() => setCreate(true)}
                        className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#FF6A00] px-4 text-sm font-bold text-[#08111F]"
                    >
                        <Plus size={17} />
                        Nouveau Sandbox
                    </button>
                </header>

                {!runtime_enabled && (
                    <div className="rounded-2xl border border-amber-400/15 bg-amber-400/[0.06] p-4 text-sm text-amber-200">
                        Le gestionnaire de runtime est installé, mais l’exécuteur isolé n’est pas encore activé sur cet environnement.
                    </div>
                )}

                {message && (
                    <div className="rounded-2xl border border-red-400/15 bg-red-500/[0.06] p-4 text-sm text-red-300">
                        {message}
                    </div>
                )}

                {items.length === 0 ? (
                    <button
                        onClick={() => setCreate(true)}
                        className="w-full rounded-3xl border border-dashed border-white/[0.10] bg-[#0D1725] p-12 text-center transition hover:border-[#FF6A00]/30"
                    >
                        <SquareTerminal className="mx-auto text-[#FF8A3D]" size={30} />
                        <p className="mt-4 font-semibold">Aucun Sandbox</p>
                        <p className="mt-1 text-sm text-slate-500">Crée ton premier environnement de développement.</p>
                    </button>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {items.map((project) => (
                            <article key={project.id} className="rounded-2xl border border-white/[0.07] bg-[#0D1725] p-5">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="truncate font-semibold">{project.name}</p>
                                        <p className="mt-1 text-xs text-slate-500">
                                            {templates[project.template]?.label ?? project.template} · {project.runtime} {project.runtime_version}
                                        </p>
                                    </div>
                                    <Status status={project.status} />
                                </div>

                                <div className="mt-5 flex items-center gap-2">
                                    {project.status === "running" ? (
                                        <button onClick={() => run(project, "stop")} className="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-white/[0.06] text-xs font-semibold text-slate-200">
                                            <CircleStop size={15} /> Arrêter
                                        </button>
                                    ) : project.status === "starting" ? (
                                        <div className="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl border border-[#FF6A00]/20 bg-[#FF6A00]/[0.06] text-xs font-semibold text-[#FFB078]">
                                            <LoaderCircle size={15} className="animate-spin" /> Compilation…
                                        </div>
                                    ) : (
                                        <button onClick={() => run(project, "start")} disabled={loading} className="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-[#FF6A00] text-xs font-bold text-[#08111F] disabled:opacity-50">
                                            {loading ? <LoaderCircle size={15} className="animate-spin" /> : <Play size={15} />} Démarrer
                                        </button>
                                    )}
                                    <button onClick={() => run(project, "restart")} disabled={loading || project.status === "starting"} className="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl bg-white/[0.06] text-slate-300" aria-label="Redémarrer">
                                        <RotateCcw size={15} />
                                    </button>
                                    <button onClick={() => remove(project)} disabled={loading} className="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl bg-red-500/[0.06] text-red-300" aria-label="Supprimer">
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
                                    <a href={project.preview_url} target="_blank" rel="noreferrer" className="mt-3 inline-flex items-center gap-1.5 text-xs text-[#FF8A3D]">
                                        Ouvrir le preview <ExternalLink size={13} />
                                    </a>
                                )}
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
    const socketRef = React.useRef(null);
    const outputRef = React.useRef(null);

    React.useEffect(() => {
        let cancelled = false;

        async function connect() {
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
                            setOutput((current) => current + "\n[DevRoad] " + message.message + "\n");
                            setStatus("erreur");
                            return;
                        }
                    } catch {
                        // Daytona PTY output is raw terminal data.
                    }
                    setOutput((current) => current + event.data);
                };
            } catch (error) {
                if (!cancelled) {
                    setStatus("indisponible");
                    setOutput((current) => current + "\n[DevRoad] " + error.message + "\n");
                }
            }
        }

        connect();

        return () => {
            cancelled = true;
            socketRef.current?.close();
            socketRef.current = null;
        };
    }, [project.id]);

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
        <div className="mt-4 overflow-hidden rounded-xl border border-white/[0.07] bg-[#08111F]">
            <div className="flex items-center justify-between border-b border-white/[0.06] px-3 py-2">
                <div className="flex items-center gap-2">
                    <SquareTerminal size={14} className="text-[#FF8A3D]" />
                    <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500">Terminal PTY</span>
                </div>
                <span className={"text-[10px] font-semibold " + (status === "connecté" ? "text-emerald-400" : "text-slate-600")}>
                    {status}
                </span>
            </div>

            <pre
                ref={outputRef}
                className="h-48 overflow-auto whitespace-pre-wrap p-3 font-mono text-[11px] leading-5 text-slate-300"
                aria-live="polite"
            >
                {output || "Connexion au terminal…"}
            </pre>

            <form onSubmit={submit} className="flex items-center gap-2 border-t border-white/[0.06] p-2">
                <span className="font-mono text-xs text-[#FF6A00]">›</span>
                <input
                    value={value}
                    onChange={(event) => setValue(event.target.value)}
                    onKeyDown={handleKeyDown}
                    placeholder="Tape une commande…"
                    disabled={status !== "connecté"}
                    className="min-w-0 flex-1 bg-transparent font-mono text-xs text-slate-200 outline-none placeholder:text-slate-700 disabled:opacity-50"
                    aria-label={"Entrée terminal de " + project.name}
                    autoComplete="off"
                    spellCheck="false"
                />
                <button
                    type="submit"
                    disabled={status !== "connecté" || !value.trim()}
                    className="rounded-lg bg-white/[0.06] px-2.5 py-1.5 text-[10px] font-semibold text-slate-300 disabled:opacity-30"
                >
                    Entrée
                </button>
            </form>
        </div>
    );
}

function Status({ status }) {
    const running = status === "running";
    const classes = running
        ? "bg-emerald-400/10 text-emerald-300"
        : "bg-white/[0.06] text-slate-500";

    return (
        <span className={"inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase " + classes}>
            <span className={"h-1.5 w-1.5 rounded-full " + (running ? "bg-emerald-400" : "bg-slate-600")} />
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
            <div className="w-full max-w-md rounded-2xl border border-white/[0.08] bg-[#0D1725] p-5 shadow-2xl">
                <div className="flex items-center justify-between">
                    <b>Nouveau Sandbox</b>
                    <button onClick={onClose} className="text-slate-500 hover:text-white" aria-label="Fermer">×</button>
                </div>
                <label className="mt-5 block text-xs text-slate-400">
                    Nom
                    <input value={name} onChange={(event) => setName(event.target.value)} className="mt-2 w-full rounded-xl border border-white/[0.08] bg-[#08111F] p-3 text-sm text-white outline-none focus:border-[#FF6A00]/50" />
                </label>
                <label className="mt-4 block text-xs text-slate-400">
                    Template
                    <select value={template} onChange={(event) => setTemplate(event.target.value)} className="mt-2 w-full rounded-xl border border-white/[0.08] bg-[#08111F] p-3 text-sm text-white outline-none focus:border-[#FF6A00]/50">
                        {entries.map(([key, definition]) => <option key={key} value={key}>{definition.label}</option>)}
                    </select>
                </label>
                <div className="mt-5 flex justify-end gap-2">
                    <button onClick={onClose} className="px-4 py-2 text-xs text-slate-500">Annuler</button>
                    <button disabled={loading || !name.trim()} onClick={() => onCreate(name, template)} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-xs font-bold text-[#08111F] disabled:opacity-40">
                        Créer
                    </button>
                </div>
            </div>
        </div>
    );
}
