const PREVIEW_CSP = "default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data: blob: https:; font-src data: https:; connect-src 'none'; object-src 'none'; base-uri 'none'; form-action 'none'";

function secureSrcDoc(srcDoc) {
    const meta = "<meta http-equiv=\"Content-Security-Policy\" content=\"" + PREVIEW_CSP + "\">";

    if (/<head[^>]*>/i.test(srcDoc)) {
        return srcDoc.replace(/<head[^>]*>/i, (tag) => tag + meta);
    }

    return meta + srcDoc;
}

import { Eye, Play, X } from "lucide-react";

export default function PreviewPane({ previewVersion, srcDoc, onRefresh, onClose }) {
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

            <iframe
                key={previewVersion}
                title="Prévisualisation DevRoad"
                sandbox="allow-scripts"
                srcDoc={secureSrcDoc(srcDoc)}
                className="h-[420px] w-full rounded-2xl border border-[var(--dr-border)] bg-white"
            />
        </div>
    );
}
