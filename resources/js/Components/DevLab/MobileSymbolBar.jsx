const SYMBOLS = ["{", "}", "(", ")", "[", "]", ";", "=", "<", ">", "/", "'", '"', ":", "_"];

export default function MobileSymbolBar({ onInsert }) {
    return (
        <div className="border-t border-[var(--dr-border)] bg-[var(--dr-bg)] px-2 py-2 lg:hidden">
            <div className="flex gap-1 overflow-x-auto pb-0.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                {SYMBOLS.map((symbol) => (
                    <button key={symbol} type="button"
                        onMouseDown={(event) => event.preventDefault()}
                        onClick={() => onInsert(symbol)}
                        className="flex h-9 min-w-9 shrink-0 items-center justify-center rounded-lg border border-[var(--dr-border)] bg-[var(--dr-surface)] px-2 font-mono text-xs font-semibold text-[var(--dr-text-2)] transition active:scale-95 active:bg-[#FF6A00] active:text-[var(--dr-ink)]"
                        aria-label={"Insérer " + symbol}>
                        {symbol}
                    </button>
                ))}
            </div>
        </div>
    );
}
