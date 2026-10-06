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
        <aside className="hidden border-r border-[var(--dr-border)] bg-[var(--dr-bg)] lg:block">
            <div className="flex items-center justify-between border-b border-[var(--dr-border)] px-3 py-3">
                <div className="flex items-center gap-2">
                    <FolderOpen size={14} className="text-[var(--dr-accent-text)]" />
                    <span className="text-[10px] font-bold uppercase tracking-[0.14em] text-[var(--dr-text-3)]">
                        Explorateur
                    </span>
                </div>

                <div className="flex items-center gap-1">
                    <button
                        type="button"
                        onClick={onCreate}
                        className="rounded-lg p-1.5 text-[var(--dr-text-3)] transition hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)]"
                        aria-label="Créer un fichier"
                        title="Créer un fichier"
                    >
                        <FilePlus2 size={14} />
                    </button>

                    <button
                        type="button"
                        onClick={onImport}
                        className="rounded-lg p-1.5 text-[var(--dr-text-3)] transition hover:bg-[var(--dr-hover)] hover:text-[var(--dr-accent-text)]"
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
                            activeFile === file.path
                                ? "bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]"
                                : "text-[var(--dr-text-3)] hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)]",
                        ].join(" ")}
                    >
                        <button
                            type="button"
                            onClick={() => {
                                setOpenMenu(null);
                                onSelect(file.path);
                            }}
                            className="flex min-w-0 flex-1 items-center gap-2 rounded-lg px-2.5 py-2 text-left"
                        >
                            <Folder size={12} className="shrink-0" />
                            <span className="truncate">{file.path}</span>
                        </button>

                        <button
                            type="button"
                            onClick={(event) => {
                                event.stopPropagation();
                                setOpenMenu((current) =>
                                    current === file.path ? null : file.path,
                                );
                            }}
                            className="mr-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-[var(--dr-text-3)] transition hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)]"
                            aria-label={"Actions de " + file.path}
                            title="Actions du fichier"
                        >
                            <MoreVertical size={14} />
                        </button>

                        <div
                            className={[
                                "absolute right-1 top-9 z-[90] w-44 overflow-hidden rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-1.5 shadow-2xl",
                                openMenu === file.path ? "block" : "hidden",
                            ].join(" ")}
                        >
                            <button
                                type="button"
                                onClick={() => {
                                    setOpenMenu(null);
                                    onRename(file);
                                }}
                                className="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs text-[var(--dr-text-2)] hover:bg-[var(--dr-hover)]"
                            >
                                <Pencil size={13} /> Renommer
                            </button>

                            <button
                                type="button"
                                onClick={() => {
                                    setOpenMenu(null);
                                    onDuplicate(file);
                                }}
                                className="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs text-[var(--dr-text-2)] hover:bg-[var(--dr-hover)]"
                            >
                                <Copy size={13} /> Dupliquer
                            </button>

                            <button
                                type="button"
                                onClick={() => {
                                    setOpenMenu(null);
                                    onDelete(file);
                                }}
                                className="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs text-[var(--dr-danger)] hover:bg-red-400/[0.08]"
                            >
                                <Trash2 size={13} /> Supprimer
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </aside>
    );
}
