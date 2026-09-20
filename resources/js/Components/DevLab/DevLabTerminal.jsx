import { useMemo, useRef, useState } from "react";
import { Terminal, Trash2 } from "lucide-react";

function normalizePath(value) {
    return value.replace(/^\.\//, "").replace(/\\/g, "/").trim();
}

export default function DevLabTerminal({ files, runtime, onPreview }) {
    const [lines, setLines] = useState([
        "DevRoad DevLab — terminal pédagogique",
        "Tape « help » pour afficher les commandes disponibles.",
    ]);
    const [command, setCommand] = useState("");
    const endRef = useRef(null);
    const filePaths = useMemo(() => files.map((file) => file.path).sort(), [files]);

    function write(output) {
        setLines((current) => [...current, ...String(output).split("\n")]);
        requestAnimationFrame(() => endRef.current?.scrollIntoView({ block: "nearest" }));
    }

    function execute(raw) {
        const value = raw.trim();
        if (!value) return;
        setLines((current) => [...current, "$ " + value]);
        setCommand("");

        const [name, ...args] = value.split(/\s+/);
        const argument = args.join(" ");

        if (name === "clear") { setLines([]); return; }
        if (name === "help") {
            write("Commandes : help, clear, pwd, ls, cat <fichier>, echo <texte>, runtime, preview, run");
            write("Terminal pédagogique : aucune commande système n'est exécutée sur le serveur.");
            return;
        }
        if (name === "pwd") { write("/workspace"); return; }
        if (name === "ls") { write(filePaths.length ? filePaths.join("  ") : "(aucun fichier)"); return; }
        if (name === "cat") {
            const path = normalizePath(argument);
            const file = files.find((item) => item.path === path);
            write(file ? (file.content || "(fichier vide)") : "cat: " + (path || "fichier") + ": introuvable");
            return;
        }
        if (name === "echo") { write(argument); return; }
        if (name === "runtime") {
            write("Runtime actif : " + (runtime === "browser" ? "Browser" : "Serveur (indisponible en production)"));
            return;
        }
        if (name === "preview" || name === "run") {
            if (runtime !== "browser") {
                write("Exécution serveur indisponible dans cet environnement. Aucun code utilisateur n'est exécuté côté serveur.");
                return;
            }
            onPreview();
            write("Aperçu Browser actualisé.");
            return;
        }
        write("Commande inconnue : " + name + ". Tape « help ».");
    }

    return (
        <section className="flex min-h-0 flex-col border-t border-white/[0.06] bg-[#050B12] lg:h-48">
            <div className="flex items-center justify-between border-b border-white/[0.05] px-3 py-2">
                <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                    <Terminal size={13} className="text-[#FF8A3D]" /> Terminal
                </div>
                <button type="button" onClick={() => setLines([])} className="rounded-lg p-1.5 text-slate-600 hover:bg-white/[0.04] hover:text-slate-300" aria-label="Effacer le terminal">
                    <Trash2 size={13} />
                </button>
            </div>
            <div className="min-h-0 flex-1 overflow-y-auto p-3 font-mono text-[10px] leading-5 text-slate-400">
                {lines.map((line, index) => <pre key={index} className="whitespace-pre-wrap break-words">{line}</pre>)}
                <div ref={endRef} />
            </div>
            <form onSubmit={(event) => { event.preventDefault(); execute(command); }} className="flex items-center gap-2 border-t border-white/[0.05] px-3 py-2">
                <span className="font-mono text-xs text-[#FF8A3D]">$</span>
                <input value={command} onChange={(event) => setCommand(event.target.value)} className="min-w-0 flex-1 bg-transparent font-mono text-[11px] text-white outline-none placeholder:text-slate-700" placeholder="help" aria-label="Commande DevLab" />
            </form>
        </section>
    );
}
