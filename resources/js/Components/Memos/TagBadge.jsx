export default function TagBadge({ name }) {
    return (
        <span className="rounded-full bg-white/[0.05] px-2.5 py-1 text-[11px] font-medium text-slate-400">
            {name}
        </span>
    );
}
