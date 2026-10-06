export default function TagBadge({ name }) {
    return (
        <span className="rounded-full bg-[var(--dr-hover)] px-2.5 py-1 text-[11px] font-medium text-[var(--dr-text-2)]">
            {name}
        </span>
    );
}
