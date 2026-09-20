import { FileCode2, FilePlus2, Upload } from "lucide-react";

export default function EditorTabs({
    files,
    activeFile,
    onSelect,
    onCreate,
    onImport,
}) {
    return (
        <div className="flex gap-1 overflow-x-auto">
            {files.map((file) => (
                <button
                    key={file.path}
                    type="button"
                    onClick={() => onSelect(file.path)}
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
                onClick={onCreate}
                className="shrink-0 rounded-lg border border-dashed border-white/[0.08] px-3 py-2 text-slate-600 transition hover:border-[#FF6A00]/30 hover:bg-[#FF6A00]/[0.06] hover:text-[#FF8A3D]"
                aria-label="Nouveau fichier"
                title="Nouveau fichier"
            >
                <FilePlus2 size={14} />
            </button>

            <button
                type="button"
                onClick={onImport}
                className="shrink-0 rounded-lg border border-dashed border-white/[0.08] px-3 py-2 text-slate-600 transition hover:border-[#FF6A00]/30 hover:bg-[#FF6A00]/[0.06] hover:text-[#FF8A3D]"
                aria-label="Importer des fichiers"
                title="Importer des fichiers"
            >
                <Upload size={14} />
            </button>
        </div>
    );
}
