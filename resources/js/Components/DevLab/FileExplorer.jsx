import { useState } from "react";
import { Copy, FilePlus2, Folder, FolderOpen, MoreVertical, Pencil, Trash2, Upload } from "lucide-react";

export default function FileExplorer({
    files,
    activeFile,
    onSelect,
    onCreate,
    onImport,
    onRename,
    onDuplicate,
    onDelete,
}) {
    const [openMenu, setOpenMenu] = useState(null);
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
                    <div
                        key={file.path}
                        className={[
                            "group relative mb-1 flex w-full items-center rounded-lg text-[11px]",
                            activeFile === file.path ? "bg-[#FF6A00]/10 text-[#FF8A3D]" : "text-slate-500 hover:bg-white/[0.03] hover:text-white",
                        ].join(" ")}
                    >
                        <button
                            type="button"
                            onClick={() => { setOpenMenu(null); onSelect(file.path); }}
                            className="flex min-w-0 flex-1 items-center gap-2 rounded-lg px-2.5 py-2 text-left"
                        >
                            <Folder size={12} className="shrink-0" />
                            <span className="truncate">{file.path}</span>
                        </button>
                        <button
                            type="button"
                            onClick={(event) => { event.stopPropagation(); setOpenMenu((current) => current === file.id ? null : file.id); }}
                            className="mr-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-slate-600 transition hover:bg-white/[0.06] hover:text-white"
                            aria-label={"Actions de " + file.path}
                            title="Actions du fichier"
                        >
                            <MoreVertical size={14} />
                        </button>
                        <div
                            className={(openMenu === file.id ? "block " : "hidden ") + "absolute right-1 top-9 z-[90] w-44 overflow-hidden rounded-xl border border-white/[0.08] bg-[#0D1725] p-1.5 shadow-2xl"
                        >
                            <button type="button" onClick={() => { setOpenMenu(null); onRename(file); }} className="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs text-slate-300 hover:bg-white/[0.05]">
                                <Pencil size={13} /> Renommer
                            </button>
                            <button type="button" onClick={() => { setOpenMenu(null); onDuplicate(file); }} className="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs text-slate-300 hover:bg-white/[0.05]">
                                <Copy size={13} /> Dupliquer
                            </button>
                            <button type="button" onClick={() => { setOpenMenu(null); onDelete(file); }} className="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs text-red-300 hover:bg-red-400/[0.08]">
                                <Trash2 size={13} /> Supprimer
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </aside>
    );
}
