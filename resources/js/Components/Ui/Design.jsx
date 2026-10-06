import { Link } from '@inertiajs/react';
import { useId } from 'react';
import { m, softSpring } from '@/Components/Ui/Motion';
import technologyLogos from '@/Config/technologyLogos';

/*
 * Primitives du design system DevRoad (maquette Claude Design).
 * Les composants ne connaissent que les rôles de couleur (--dr-*) :
 * chaque thème (Nuit, Minuit, Ardoise, Clair, Sable) les redéfinit.
 */

export const MotionLink = m.create(Link);

const focus = 'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]';

export const ui = {
    focus,
    card: 'rounded-[18px] border border-[var(--dr-border)] bg-[var(--dr-surface)] shadow-[var(--dr-shadow)]',
    primary: 'inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[var(--dr-accent)] px-4 text-sm font-bold text-[var(--dr-ink)] transition-[filter,opacity] hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50 ' + focus,
    secondary: 'inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-[var(--dr-border-2)] bg-[var(--dr-surface)] px-4 text-sm font-semibold text-[var(--dr-text)] transition-colors hover:bg-[var(--dr-field)] disabled:cursor-not-allowed disabled:opacity-50 ' + focus,
    ghost: 'inline-flex h-11 items-center justify-center gap-2 rounded-xl px-3 text-sm font-semibold text-[var(--dr-text-2)] transition-colors hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)] ' + focus,
    danger: 'inline-flex h-11 items-center justify-center gap-2 rounded-xl px-3 text-sm font-semibold text-[var(--dr-danger)] transition-colors hover:bg-[var(--dr-field)] ' + focus,
    iconButton: 'inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] text-[var(--dr-text-2)] transition-colors hover:text-[var(--dr-text)] ' + focus,
    field: 'h-12 w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-field)] px-3.5 text-[15px] text-[var(--dr-text)] outline-none transition-colors placeholder:text-[var(--dr-text-3)] focus:border-[var(--dr-accent)] focus:ring-0',
    eyebrow: 'text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-text-3)]',
    sectionTitle: "font-['Manrope',sans-serif] text-lg font-extrabold tracking-[-0.02em] text-[var(--dr-text)]",
};

export const clamp = (value) => Math.min(100, Math.max(0, Math.round(Number(value ?? 0)) || 0));

export function monogram(title) {
    const match = String(title ?? '').match(/[\p{L}\p{N}]/u);
    return match ? match[0].toUpperCase() : '#';
}

export function technologyLogo(technology) {
    if (!technology) return null;
    const key = String(technology).toLowerCase().trim().replace(/\s+/g, '').replace(/_/g, '-');
    return technologyLogos[key] ?? technologyLogos[key.replace(/\./g, '')] ?? null;
}

/** Titre de page : Manrope, sous-titre, actions, lien retour facultatif. */
export function PageHead({ title, subtitle, actions, back, eyebrow, className = '' }) {
    return (
        <header className={'flex flex-col gap-4 ' + className}>
            {back && (
                <Link href={back.href} className={'-ml-1 inline-flex min-h-9 w-fit items-center gap-1.5 rounded-lg px-1 text-sm font-medium text-[var(--dr-text-2)] hover:text-[var(--dr-text)] ' + focus}>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6" /></svg>
                    {back.label}
                </Link>
            )}
            <div className="flex flex-wrap items-end justify-between gap-4">
                <div className="flex min-w-0 flex-col gap-1.5">
                    {eyebrow && <span className="text-xs font-bold uppercase tracking-[0.14em] text-[var(--dr-accent-text)]">{eyebrow}</span>}
                    <h1 className="m-0 font-['Manrope',sans-serif] text-[30px] font-extrabold leading-[1.08] tracking-[-0.03em] text-[var(--dr-text)] [overflow-wrap:anywhere] lg:text-[38px]">{title}</h1>
                    {subtitle && <p className="m-0 max-w-2xl text-[15px] leading-[1.5] text-[var(--dr-text-2)]">{subtitle}</p>}
                </div>
                {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
            </div>
        </header>
    );
}

export function ProgressBar({ value, delay = 0.2, className = 'h-1.5' }) {
    const progress = clamp(value);
    return (
        <span className={'block overflow-hidden rounded-full bg-[var(--dr-field)] ' + className} role="progressbar" aria-valuemin={0} aria-valuemax={100} aria-valuenow={progress}>
            <m.span className="block h-full rounded-full bg-[var(--dr-accent)]" initial={{ width: 0 }} animate={{ width: progress + '%' }} transition={{ ...softSpring, delay }} />
        </span>
    );
}

/** Anneau de progression (carte « Reprendre » de la maquette). */
export function ProgressRing({ value, size = 72, stroke = 7, children }) {
    const progress = clamp(value);
    const r = (size - stroke) / 2;
    const c = 2 * Math.PI * r;
    return (
        <span className="relative inline-flex shrink-0 items-center justify-center" style={{ width: size, height: size }}>
            <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`} aria-hidden="true" className="-rotate-90">
                <circle cx={size / 2} cy={size / 2} r={r} fill="none" stroke="var(--dr-field)" strokeWidth={stroke} />
                <m.circle cx={size / 2} cy={size / 2} r={r} fill="none" stroke="var(--dr-accent)" strokeWidth={stroke} strokeLinecap="round" strokeDasharray={c} initial={{ strokeDashoffset: c }} animate={{ strokeDashoffset: c - (c * progress) / 100 }} transition={{ ...softSpring, delay: 0.15 }} />
            </svg>
            <span className="absolute inset-0 flex items-center justify-center text-sm font-bold tabular-nums text-[var(--dr-text)]">{children ?? progress + '%'}</span>
        </span>
    );
}

/** Tuile technologie : logo officiel s'il existe, sinon monogramme. */
export function TechTile({ technology, title, size = 48 }) {
    const logo = technologyLogo(technology);
    return (
        <span aria-hidden="true" className="flex shrink-0 items-center justify-center overflow-hidden rounded-[14px] bg-[var(--dr-field)] font-['JetBrains_Mono',ui-monospace,monospace] text-[var(--dr-accent-text)]" style={{ width: size, height: size, padding: logo ? size * 0.2 : 0, fontSize: size * 0.36 }}>
            {logo ? <img src={logo} alt="" className="h-full w-full object-contain" /> : monogram(title ?? technology)}
        </span>
    );
}

/** Contrôle segmenté avec pastille partagée (liens ou boutons). */
export function Segmented({ items, value, onChange, label, className = '' }) {
    const pill = 'segmented-' + useId();
    return (
        <div role="tablist" aria-label={label} className={'flex gap-1 overflow-x-auto rounded-xl bg-[var(--dr-field)] p-1 dr-scrollbar-none ' + className}>
            {items.map((item) => {
                const active = item.key === value;
                const inner = <>
                    {active && <m.span layoutId={pill} transition={softSpring} aria-hidden="true" className="absolute inset-0 rounded-[9px] bg-[var(--dr-surface)] shadow-[var(--dr-shadow)]" />}
                    <span className="relative">{item.label}</span>
                    {typeof item.count === 'number' && <span className="relative ml-1.5 text-[11px] tabular-nums text-[var(--dr-text-3)]">{item.count}</span>}
                </>;
                const cls = 'relative flex h-[34px] shrink-0 items-center rounded-[9px] px-3.5 text-[13px] transition-colors ' + focus + ' ' + (active ? 'font-semibold text-[var(--dr-text)]' : 'text-[var(--dr-text-2)] hover:text-[var(--dr-text)]');
                return item.href
                    ? <Link key={item.key} role="tab" aria-selected={active} href={item.href} preserveScroll preserveState className={cls}>{inner}</Link>
                    : <button key={item.key} type="button" role="tab" aria-selected={active} onClick={() => onChange?.(item.key)} className={cls}>{inner}</button>;
            })}
        </div>
    );
}

export function EmptyState({ icon, title, text, action }) {
    return (
        <div className="flex flex-col items-center rounded-[18px] border border-dashed border-[var(--dr-border-2)] bg-[var(--dr-surface)] px-6 py-12 text-center">
            {icon && <span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]">{icon}</span>}
            <h2 className="mb-0 mt-4 text-lg font-semibold text-[var(--dr-text)]">{title}</h2>
            {text && <p className="mx-auto mb-0 mt-1 max-w-sm text-sm leading-6 text-[var(--dr-text-2)]">{text}</p>}
            {action && <div className="mt-5">{action}</div>}
        </div>
    );
}

const STATUS = {
    done: { label: 'Terminé', className: 'bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]' },
    progress: { label: 'En cours', className: 'bg-[var(--dr-field)] text-[var(--dr-text)]' },
    todo: { label: 'À démarrer', className: 'bg-[var(--dr-field)] text-[var(--dr-text-2)]' },
};

/** État d'un parcours déduit de sa progression (plus fiable que le statut saisi). */
export function roadmapState(roadmap) {
    const progress = clamp(roadmap?.progress);
    if (progress >= 100 && (roadmap?.steps_count ?? 0) > 0) return 'done';
    if (progress > 0) return 'progress';
    return 'todo';
}

export function StatePill({ state }) {
    const item = STATUS[state] ?? STATUS.todo;
    return <span className={'inline-flex h-6 items-center rounded-full px-2.5 text-xs font-semibold ' + item.className}>{item.label}</span>;
}

export function Svg({ d, size = 18, stroke = 2, className = '' }) {
    const paths = Array.isArray(d) ? d : [d];
    return (
        <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" className={className}>
            {paths.map((path) => <path key={path} d={path} />)}
        </svg>
    );
}

export const ICON = {
    plus: 'M12 5v14M5 12h14',
    arrow: 'M5 12h14M13 6l6 6-6 6',
    map: 'M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14',
    check: 'M5 12l5 5L20 7',
    edit: 'M4 20h4L19 9l-4-4L4 16zM14 6l4 4',
    trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
    copy: 'M9 9h11v11H9zM5 15H4V4h11v1',
    code: 'M8 7l-5 5 5 5M16 7l5 5-5 5',
    book: 'M4 5a2 2 0 012-2h13v16H6a2 2 0 00-2 2zM4 19V5',
    folder: 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z',
    search: ['M11 4a7 7 0 100 14 7 7 0 000-14z', 'M20 20l-3.5-3.5'],
    bookmark: 'M6 3h12v18l-6-4-6 4z',
    clock: ['M12 7v5l3 2', 'M12 3a9 9 0 100 18 9 9 0 000-18z'],
    chevronRight: 'M9 6l6 6-6 6',
    info: ['M12 3a9 9 0 100 18 9 9 0 000-18z', 'M12 11v5M12 8h.01'],
    link: 'M10 14a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1M14 10a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1',
};
