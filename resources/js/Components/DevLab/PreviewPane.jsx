import { Eye, Play } from "lucide-react";

export default function PreviewPane({ previewVersion, srcDoc, onRefresh }) {
    return (
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
                    onClick={onRefresh}
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
                srcDoc={srcDoc}
                className="h-[420px] w-full rounded-2xl border border-white/[0.06] bg-white"
            />
        </div>
    );
}
