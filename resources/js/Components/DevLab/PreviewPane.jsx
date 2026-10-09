import { useEffect, useRef, useState } from "react";
import axios from "axios";
import { Eye, Play, X } from "lucide-react";

export default function PreviewPane({ previewVersion, srcDoc, onRefresh, onClose }) {
    const [src, setSrc] = useState(null);
    const [failed, setFailed] = useState(false);
    const latest = useRef(srcDoc);
    latest.current = srcDoc;

    // Le HTML est déposé côté serveur puis servi depuis /preview, avec sa propre CSP.
    useEffect(() => {
        let active = true;
        setSrc(null);
        setFailed(false);
        axios.post(route('preview.store'), { html: String(latest.current ?? '') })
            .then((response) => { if (active) setSrc(response.data.url); })
            .catch(() => { if (active) setFailed(true); });

        return () => { active = false; };
    }, [previewVersion]);

    return (
        <div className="bg-[var(--dr-bg)] p-2">
            <div className="mb-2 flex items-center justify-between px-2">
                <div className="flex items-center gap-2">
                    <Eye size={13} className="text-[var(--dr-accent-text)]" />
                    <span className="text-[10px] font-bold uppercase tracking-[0.12em] text-[var(--dr-text-3)]">
                        Aperçu
                    </span>
                </div>

                <div className="flex items-center gap-1.5">
                    <button
                        type="button"
                        onClick={onRefresh}
                        className="inline-flex items-center gap-1.5 rounded-lg bg-[#FF6A00] px-2.5 py-2 text-[10px] font-bold text-[var(--dr-ink)]"
                    >
                        <Play size={11} fill="currentColor" />
                        Actualiser
                    </button>
                    {onClose && (
                        <button
                            type="button"
                            onClick={onClose}
                            className="inline-flex min-h-8 min-w-8 items-center justify-center rounded-lg border border-[var(--dr-border)] bg-[var(--dr-hover)] text-[var(--dr-text-2)] transition hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)]"
                            aria-label="Fermer l'aperçu"
                            title="Retour à l'éditeur"
                        >
                            <X size={14} />
                        </button>
                    )}
                </div>
            </div>

            {failed ? (
                <div className="flex h-[420px] w-full items-center justify-center rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-field)] px-4 text-center text-sm text-[var(--dr-text-2)]">
                    Impossible de charger l'aperçu. Réessaie avec « Actualiser ».
                </div>
            ) : (
                <iframe
                    key={previewVersion}
                    title="Prévisualisation DevRoad"
                    sandbox="allow-scripts"
                    src={src ?? 'about:blank'}
                    className="h-[420px] w-full rounded-2xl border border-[var(--dr-border)] bg-white"
                />
            )}
        </div>
    );
}
