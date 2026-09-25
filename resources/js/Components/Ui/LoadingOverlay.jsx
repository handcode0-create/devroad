import LoadingSpinner from './LoadingSpinner';

export default function LoadingOverlay({ visible = false, label = 'Chargement...' }) {
    if (!visible) return null;

    return (
        <div
            className="fixed inset-0 z-[200] flex items-center justify-center bg-[#050B12]/45 px-4 backdrop-blur-[2px]"
            role="status"
            aria-live="polite"
            aria-busy="true"
        >
            <div className="w-full max-w-[280px] rounded-2xl border border-white/[0.08] bg-[#0D1725]/95 px-5 py-4 shadow-[0_24px_80px_rgba(0,0,0,0.42)]">
                <div className="h-1 overflow-hidden rounded-full bg-white/[0.06]">
                    <div className="h-full w-1/3 animate-pulse rounded-full bg-[#FF6A00] shadow-[0_0_18px_rgba(255,106,0,0.8)] motion-reduce:animate-none" />
                </div>

                <div className="mt-4 flex items-center justify-center text-sm font-medium text-slate-300">
                    <LoadingSpinner size={17} label={label} />
                </div>
            </div>
        </div>
    );
}
