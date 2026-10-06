import { Globe2, Server, ShieldCheck } from "lucide-react";

export default function RuntimeSelector({ runtime, template }) {
    const browser = runtime === "browser";
    return (
        <div className="flex items-center gap-2 rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] px-2.5 py-2">
            {browser ? <Globe2 size={13} className="text-[var(--dr-accent-text)]" /> : <Server size={13} className="text-[var(--dr-text-3)]" />}
            <div className="min-w-0">
                <p className="text-[9px] font-bold uppercase tracking-[0.12em] text-[var(--dr-text-3)]">Runtime</p>
                <p className="truncate text-[10px] font-semibold text-[var(--dr-text-2)]">{browser ? "Browser" : "Serveur"} · {template}</p>
            </div>
            {!browser && <ShieldCheck size={13} className="ml-auto shrink-0 text-emerald-500/70" aria-label="Runtime serveur désactivé" />}
        </div>
    );
}
