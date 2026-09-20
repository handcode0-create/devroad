import { Check, Clipboard, FileCode2, Trash2 } from "lucide-react";

export default function CodeEditor({
    activeFile,
    currentFile,
    lineCount,
    copied,
    onChange,
    onCopy,
    onDelete,
    editorRef,
}) {
    return (
        <>
            <div className="flex items-center justify-between border-b border-white/[0.05] bg-[#0D1725] px-3 py-2">
                <div className="flex items-center gap-2 text-[10px] text-slate-600">
                    <FileCode2 size={12} />
                    {activeFile}
                    <span>{lineCount} lignes</span>
                </div>

                <div className="flex items-center gap-1">
                    <button
                        type="button"
                        onClick={onCopy}
                        className="rounded-lg p-2 text-slate-600 hover:bg-white/[0.05] hover:text-white"
                        aria-label="Copier"
                    >
                        {copied ? <Check size={13} /> : <Clipboard size={13} />}
                    </button>

                    <button
                        type="button"
                        onClick={onDelete}
                        className="rounded-lg p-2 text-slate-600 hover:bg-red-500/10 hover:text-red-400"
                        aria-label="Supprimer le fichier"
                    >
                        <Trash2 size={13} />
                    </button>
                </div>
            </div>

            <div className="flex h-[min(390px,45vh)] bg-[#06101A] lg:h-[390px]">
                <div className="w-11 shrink-0 overflow-hidden border-r border-white/[0.04] bg-[#08111C] py-3 text-right font-mono text-[10px] leading-6 text-slate-700">
                    {Array.from({ length: lineCount }, (_, index) => (
                        <div key={index} className="pr-2">
                            {index + 1}
                        </div>
                    ))}
                </div>

                <textarea
                    ref={editorRef}
                    value={currentFile?.content ?? ""}
                    onChange={(event) => onChange(event.target.value)}
                    spellCheck={false}
                    className="min-w-0 flex-1 resize-none border-0 bg-[#06101A] p-4 font-mono text-[12px] leading-6 text-slate-300 outline-none focus:ring-0"
                    aria-label={"Éditeur " + activeFile}
                />
            </div>
        </>
    );
}
