import { useEffect, useState } from "react";
import { FileCode2, FilePlus2, MoreVertical, Pencil, Trash2, Upload, Copy } from "lucide-react";

export default function EditorTabs({
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

    useEffect(() => {
        const close = () => setOpenMenu(null);
        document.addEventListener("mousedown", close);
        return () => document.removeEventListener("mousedown", close);
    }, []);

    return (
        <div className="flex gap-1 overflow-x-auto">
            {files.map((file) => (
                <div key={file.path} className="relative flex shrink-0 items-center">
                    <button
                        type="button"
                        onClick={() => onSelect(file.path)}
                        className={[
                            "inline-flex items-center gap-2 rounded-lg py-2 pl-3 pr-9 text-[11px] font-semibold",
                            activeFile === file.path
                                ? "bg-[#FF6A00] text-[#08111F]"
                                : "bg-white/[0.03] text-slate-500 hover:text-white",
                        ].join(" ")}
                    >
                        <FileCode2 size={13} />
                        <span className="max-w-[150px] truncate">{file.path}</span>
                    </button>

                    <button
                        type="button"
                        onClick={(event) => {
                            event.stopPropagation();
                            setOpenMenu((current) => current === file.path ? null : file.path);
                        }}
                        className="absolute right-1 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 hover:bg-black/10 hover:text-white"
                        aria-label={"Actions de " + file.path}
                        title="Actions du fichier"
                    >
                        <MoreVertical size={13} />
                    </button>

                    {openMenu === file.path && (
                        <div
                            className="absolute left-0 top-10 z-[90] w-44 rounded-xl border border-white/[0.08] bg-[#0D1725] p-1.5 shadow-2xl"
                            onMouseDown={(event) => event.stopPropagation()}
                            onClick={(event) => event.stopPropagation()}
                        >
                            <Action icon={Pencil} text="Renommer" onClick={() => { setOpenMenu(null); onRename?.(file); }} />
                            <Action icon={Copy} text="Dupliquer" onClick={() => { setOpenMenu(null); onDuplicate?.(file); }} />
                            <Action danger icon={Trash2} text="Supprimer" onClick={() => { setOpenMenu(null); onDelete?.(file); }} />
                        </div>
                    )}
                </div>
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

function Action({ icon: Icon, text, onClick, danger = false }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={"flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs " + (danger ? "text-red-300 hover:bg-red-400/[0.08]" : "text-slate-300 hover:bg-white/[0.05]")}
        >
            <Icon size={13} />
            {text}
        </button>
    );
}
