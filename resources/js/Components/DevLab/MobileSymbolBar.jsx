const SYMBOLS = ["{", "}", "(", ")", "[", "]", ";", "=", "<", ">", "/", "'", '"', ":", "_"];

export default function MobileSymbolBar({ onInsert }) {
    return (
        <div className="border-t border-white/[0.06] bg-[#07101A] px-2 py-2 lg:hidden">
            <div className="flex gap-1 overflow-x-auto pb-0.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                {SYMBOLS.map((symbol) => (
                    <button key={symbol} type="button"
                        onMouseDown={(event) => event.preventDefault()}
                        onClick={() => onInsert(symbol)}
                        className="flex h-9 min-w-9 shrink-0 items-center justify-center rounded-lg border border-white/[0.06] bg-[#0D1725] px-2 font-mono text-xs font-semibold text-slate-300 transition active:scale-95 active:bg-[#FF6A00] active:text-[#08111F]"
                        aria-label={"Insérer " + symbol}>
                        {symbol}
                    </button>
                ))}
            </div>
        </div>
    );
}
