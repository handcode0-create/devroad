import LoadingSpinner from './LoadingSpinner';

export default function PageLoading({ label = 'Chargement...' }) {
    return (
        <div
            className="flex min-h-[40vh] w-full items-center justify-center"
            role="status"
            aria-live="polite"
        >
            <div className="rounded-2xl border border-white/[0.06] bg-[#0D1725] px-5 py-4 text-sm font-medium text-slate-400 shadow-[0_16px_50px_rgba(0,0,0,0.2)]">
                <LoadingSpinner label={label} />
            </div>
        </div>
    );
}
