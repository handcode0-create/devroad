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
        <div className="flex min-h-[250px] flex-col bg-[var(--dr-bg)]">
            <div className="flex-1 overflow-y-auto p-4 font-mono text-[11px] leading-6 text-[var(--dr-text-2)]">
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
                className="border-t border-[var(--dr-border)] bg-[var(--dr-bg)] p-3"
            >
                <div className="flex items-center gap-2">
                    <span className="font-mono text-xs text-[var(--dr-accent-text)]">$</span>
                    <input
                        value={command}
                        onChange={(event) => onCommandChange(event.target.value)}
                        placeholder="help"
                        className="min-w-0 flex-1 border-0 bg-transparent px-0 py-2 font-mono text-xs text-[var(--dr-text)] outline-none placeholder:text-[var(--dr-text-3)] focus:ring-0"
                        aria-label="Commande du terminal"
                    />
                    <button
                        type="submit"
                        className="rounded-lg p-2 text-[var(--dr-text-3)] hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)]"
                        aria-label="Exécuter"
                    >
                        <ChevronDown
                            size={15}
                            className="rotate-[-90deg]"
                        />
                    </button>
                </div>
            </form>

            <div className="flex items-center justify-between border-t border-[var(--dr-border)] px-4 py-2 text-[10px] text-[var(--dr-text-3)]">
                <span>Shell pédagogique</span>
                <button
                    type="button"
                    onClick={onClear}
                    className="inline-flex items-center gap-1 hover:text-[var(--dr-text-2)]"
                >
                    <Trash2 size={11} />
                    Effacer
                </button>
            </div>
        </div>
    );
}
