import { ChevronDown, Terminal, Trash2 } from "lucide-react";

export default function TerminalPanel({
    terminal,
    command,
    onCommandChange,
    onSubmit,
    onClear,
    terminalEndRef,
}) {
    return (
        <div className="flex min-h-[250px] flex-col bg-[#050B12]">
            <div className="flex-1 overflow-y-auto p-4 font-mono text-[11px] leading-6 text-slate-400">
                {terminal.map((line, index) => (
                    <pre
                        key={index}
                        className="whitespace-pre-wrap break-words"
                    >
                        {line}
                    </pre>
                ))}
                <div ref={terminalEndRef} />
            </div>

            <form
                onSubmit={onSubmit}
                className="border-t border-white/[0.05] bg-[#07101A] p-3"
            >
                <div className="flex items-center gap-2">
                    <span className="font-mono text-xs text-[#FF8A3D]">$</span>
                    <input
                        value={command}
                        onChange={(event) => onCommandChange(event.target.value)}
                        placeholder="help"
                        className="min-w-0 flex-1 border-0 bg-transparent px-0 py-2 font-mono text-xs text-white outline-none placeholder:text-slate-700 focus:ring-0"
                        aria-label="Commande du terminal"
                    />
                    <button
                        type="submit"
                        className="rounded-lg p-2 text-slate-600 hover:bg-white/[0.05] hover:text-white"
                        aria-label="Exécuter"
                    >
                        <ChevronDown
                            size={15}
                            className="rotate-[-90deg]"
                        />
                    </button>
                </div>
            </form>

            <div className="flex items-center justify-between border-t border-white/[0.05] px-4 py-2 text-[10px] text-slate-700">
                <span>Shell pédagogique</span>
                <button
                    type="button"
                    onClick={onClear}
                    className="inline-flex items-center gap-1 hover:text-slate-400"
                >
                    <Trash2 size={11} />
                    Effacer
                </button>
            </div>
        </div>
    );
}
