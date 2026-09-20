import { FilePlus2, Folder, FolderOpen, Upload } from "lucide-react";

export default function FileExplorer({
    files,
    activeFile,
    onSelect,
    onCreate,
    onImport,
}) {
    return (
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
                        onClick={onCreate}
                        className="rounded-lg p-1.5 text-slate-600 transition hover:bg-white/[0.04] hover:text-white"
                        aria-label="Créer un fichier"
                        title="Créer un fichier"
                    >
                        <FilePlus2 size={14} />
                    </button>

                    <button
                        type="button"
                        onClick={onImport}
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
                        onClick={() => onSelect(file.path)}
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
    );
}
