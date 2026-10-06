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
                                ? "bg-[#FF6A00] text-[var(--dr-ink)]"
                                : "bg-[var(--dr-hover)] text-[var(--dr-text-3)] hover:text-[var(--dr-text)]",
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
                        className="absolute right-1 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-[var(--dr-text-3)] hover:bg-black/10 hover:text-[var(--dr-text)]"
                        aria-label={"Actions de " + file.path}
                        title="Actions du fichier"
                    >
                        <MoreVertical size={13} />
                    </button>

                    {openMenu === file.path && (
                        <div
                            className="absolute left-0 top-10 z-[90] w-44 rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-1.5 shadow-2xl"
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
                className="shrink-0 rounded-lg border border-dashed border-[var(--dr-border)] px-3 py-2 text-[var(--dr-text-3)] transition hover:border-[#FF6A00]/30 hover:bg-[#FF6A00]/[0.06] hover:text-[var(--dr-accent-text)]"
                aria-label="Nouveau fichier"
                title="Nouveau fichier"
            >
                <FilePlus2 size={14} />
            </button>

            <button
                type="button"
                onClick={onImport}
                className="shrink-0 rounded-lg border border-dashed border-[var(--dr-border)] px-3 py-2 text-[var(--dr-text-3)] transition hover:border-[#FF6A00]/30 hover:bg-[#FF6A00]/[0.06] hover:text-[var(--dr-accent-text)]"
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
            className={"flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs " + (danger ? "text-[var(--dr-danger)] hover:bg-red-400/[0.08]" : "text-[var(--dr-text-2)] hover:bg-[var(--dr-hover)]")}
        >
            <Icon size={13} />
            {text}
        </button>
    );
}
