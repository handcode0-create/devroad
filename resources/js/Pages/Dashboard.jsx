import { Head, Link, usePage } from "@inertiajs/react";
import { useLayoutEffect, useRef } from "react";
import { gsap } from "gsap";
import AppLayout from "@/Layouts/AppLayout";
import { m, softSpring } from "@/Components/Ui/Motion";
import { reducedMotionPreferred } from "@/theme";

// Accueil — artboard « Mobile — Accueil » (Claude Design), étendu en 2 colonnes sur desktop.
const MotionLink = m.create(Link);

const ICONS = {
    search: "M11 4a7 7 0 100 14 7 7 0 000-14zM20 20l-3.5-3.5",
    newMemo: "M7 3h7l5 5v13H7zM14 3v5h5M13 12v6M10 15h6",
    code: "M8 7l-5 5 5 5M16 7l5 5-5 5",
    map: "M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14",
    box: "M12 3l8 4.5v9L12 21l-8-4.5v-9zM12 12l8-4.5M12 12L4 7.5M12 12v9",
    arrow: "M5 12h14M13 6l6 6-6 6",
    road: ["M8 20L11 4", "M16 20L13 4", "M12 16v1M12 11v1M12 7v1"],
};

function Svg({ d, size = 18, stroke = 2, className = "" }) {
    const paths = Array.isArray(d) ? d : [d];
    return (
        <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" className={className}>
            {paths.map((path) => <path key={path} d={path} />)}
        </svg>
    );
}

function monogram(title) {
    const match = String(title ?? "").match(/[\p{L}\p{N}]/u);
    return match ? match[0].toUpperCase() : "#";
}

function initials(name) {
    return String(name ?? "").trim().split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]?.toUpperCase()).join("") || "DR";
}

function relativeDay(value) {
    if (!value) return "";
    const date = new Date(value);
    const today = new Date();
    const startOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    const days = Math.round((startOfDay(today) - startOfDay(date)) / 86400000);
    if (days === 0) return "Aujourd’hui";
    if (days === 1) return "Hier";
    return date.toLocaleDateString("fr-FR", { day: "numeric", month: "short" });
}

function todayLabel() {
    const label = new Date().toLocaleDateString("fr-FR", { weekday: "long", day: "numeric", month: "long" });
    return label.charAt(0).toUpperCase() + label.slice(1);
}

const clamp = (value) => Math.min(100, Math.max(0, Math.round(Number(value ?? 0)) || 0));

export default function Dashboard({ stats, recent_roadmaps, continue_roadmap, recent_memos = [] }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    const firstName = user?.name?.trim()?.split(" ")[0] ?? "développeur";
    const roadmaps = Array.isArray(recent_roadmaps) ? recent_roadmaps : [];
    const memos = Array.isArray(recent_memos) ? recent_memos : [];
    const resume = continue_roadmap ?? null;
    const progress = clamp(stats?.progress);
    const rootRef = useRef(null);

    // GSAP : les blocs de l'accueil se posent en cascade.
    useLayoutEffect(() => {
        if (!rootRef.current || reducedMotionPreferred()) return undefined;
        const context = gsap.context(() => {
            gsap.fromTo("[data-anim='block']", { autoAlpha: 0, y: 18 }, { autoAlpha: 1, y: 0, duration: 0.5, stagger: 0.07, ease: "power3.out", clearProps: "opacity,visibility,transform" });
        }, rootRef);
        return () => context.revert();
    }, []);

    return (
        <AppLayout mobileHeader={false}>
            <Head title="Accueil" />

            <div ref={rootRef} className="-mx-4 -mt-5 flex flex-col gap-[22px] px-5 pt-[18px] font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] sm:-mx-6 sm:-mt-6 lg:mx-0 lg:mt-0 lg:gap-7 lg:px-0 lg:pt-0">
                {/* En-tête mobile de la maquette */}
                <header className="-mb-2 flex items-center gap-2.5 lg:hidden">
                    <Link href="/dashboard" className="flex flex-1 items-center gap-2.5" aria-label="DevRoad — Accueil">
                        <img src="/icondevroad.png" alt="" width="40" height="40" className="h-9 w-9 shrink-0 object-contain" />
                        <span className="font-['Manrope',sans-serif] text-[19px] font-extrabold tracking-[-0.02em]">Dev<span className="text-[var(--dr-accent-text)]">Road</span></span>
                    </Link>
                    <MotionLink whileTap={{ scale: 0.92 }} href="/search" aria-label="Rechercher" className="flex h-11 w-11 items-center justify-center rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] text-[var(--dr-text-2)]">
                        <Svg d={ICONS.search} size={19} />
                    </MotionLink>
                    <m.button whileTap={{ scale: 0.92 }} type="button" onClick={() => window.dispatchEvent(new Event("devroad:appearance"))} aria-label="Profil et apparence" aria-haspopup="dialog" className="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--dr-accent)] text-sm font-bold text-[var(--dr-ink)]">
                        {initials(user?.name)}
                    </m.button>
                </header>

                <div data-anim="block" className="flex flex-col gap-1">
                    <span className="text-sm text-[var(--dr-text-2)]">{todayLabel()}</span>
                    <h1 className="m-0 font-['Manrope',sans-serif] text-[30px] font-extrabold leading-[1.1] tracking-[-0.03em] lg:text-[38px]">Bonjour {firstName}</h1>
                </div>

                <div className="grid gap-[22px] lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)] lg:gap-6">
                    <div className="flex min-w-0 flex-col gap-[22px] lg:gap-6">
                        <ResumeCard resume={resume} hasRoadmaps={roadmaps.length > 0} />

                        <section data-anim="block" aria-label="Raccourcis" className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                            <Shortcut href="/memos/create" icon={ICONS.newMemo} label="Nouvelle fiche" />
                            <Shortcut href="/devlab" icon={ICONS.code} label="Ouvrir DevLab" />
                            <Shortcut href="/roadmaps/create" icon={ICONS.map} label="Nouveau parcours" className="hidden lg:flex" />
                            <Shortcut href="/sandbox" icon={ICONS.box} label="Sandbox" className="hidden lg:flex" />
                        </section>

                        {roadmaps.length > 0 && <section data-anim="block" aria-label="Parcours récents" className="hidden flex-col gap-2.5 lg:flex">
                            <SectionTitle title="Parcours récents" href="/roadmaps" />
                            {roadmaps.map((roadmap) => <RoadmapRow key={roadmap.id} roadmap={roadmap} />)}
                        </section>}
                    </div>

                    <div className="flex min-w-0 flex-col gap-[22px] lg:gap-6">
                        <section data-anim="block" aria-label="Fiches récentes" className="flex flex-col gap-2.5">
                            <SectionTitle title="Fiches récentes" href="/memos" />
                            {memos.length > 0
                                ? memos.map((memo) => (
                                    <MotionLink key={memo.id} whileTap={{ scale: 0.985 }} href={"/memos/" + memo.id} className="flex items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-3.5 py-3 text-[var(--dr-text)]">
                                        <span aria-hidden="true" className="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-[11px] bg-[var(--dr-field)] font-['JetBrains_Mono',ui-monospace,monospace] text-sm text-[var(--dr-accent-text)]">{monogram(memo.title)}</span>
                                        <span className="flex min-w-0 flex-1 flex-col gap-0.5">
                                            <span className="truncate text-[15px] font-semibold">{memo.title}</span>
                                            <span className="truncate text-xs text-[var(--dr-text-3)]">{[relativeDay(memo.updated_at), memo.folder ?? memo.tag].filter(Boolean).join(" · ")}</span>
                                        </span>
                                    </MotionLink>
                                ))
                                : <EmptyRow text="Aucune fiche pour l’instant." action="Écrire ma première fiche" href="/memos/create" />}
                        </section>

                        <section data-anim="block" aria-label="Progression globale" className="hidden flex-col gap-4 rounded-[22px] border border-[var(--dr-border)] bg-[var(--dr-surface)] p-[18px] shadow-[var(--dr-shadow)] lg:flex">
                            <div className="flex items-baseline justify-between">
                                <h2 className="m-0 text-[17px] font-bold">Progression globale</h2>
                                <span className="text-sm font-bold tabular-nums text-[var(--dr-accent-text)]">{progress}%</span>
                            </div>
                            <div className="h-2 overflow-hidden rounded-full bg-[var(--dr-field)]" role="progressbar" aria-valuenow={progress} aria-valuemin={0} aria-valuemax={100} aria-label="Progression globale">
                                <m.div className="h-full rounded-full bg-[var(--dr-accent)]" initial={{ width: 0 }} animate={{ width: progress + "%" }} transition={{ ...softSpring, delay: 0.3 }} />
                            </div>
                            <dl className="m-0 grid grid-cols-3 gap-3">
                                <Stat label="Parcours" value={stats?.roadmaps ?? 0} />
                                <Stat label="Fiches" value={stats?.memos ?? 0} />
                                <Stat label="Étapes faites" value={(stats?.steps_completed ?? 0) + "/" + (stats?.steps_total ?? 0)} />
                            </dl>
                        </section>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function ResumeCard({ resume, hasRoadmaps }) {
    const radius = 29;
    const circumference = 2 * Math.PI * radius;

    if (!resume) {
        return (
            <section data-anim="block" aria-label="Commencer" className="flex flex-col gap-4 rounded-[22px] border border-[var(--dr-border)] bg-[var(--dr-surface)] p-[18px] shadow-[var(--dr-shadow)]">
                <span className="text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-accent-text)]">{hasRoadmaps ? "Bravo" : "Pour commencer"}</span>
                <span className="text-[17px] font-bold leading-[1.3]">{hasRoadmaps ? "Toutes tes étapes sont terminées." : "Crée ton premier parcours."}</span>
                <span className="text-[13px] text-[var(--dr-text-2)]">{hasRoadmaps ? "Lance un nouveau parcours pour continuer à progresser." : "Une roadmap découpe ce que tu veux apprendre en étapes simples."}</span>
                <MotionLink whileTap={{ scale: 0.97 }} href="/roadmaps/create" className="flex h-12 items-center justify-center gap-2 rounded-[14px] bg-[var(--dr-accent)] text-[15px] font-bold text-[var(--dr-ink)]">
                    {hasRoadmaps ? "Nouveau parcours" : "Créer ma roadmap"}<Svg d={ICONS.arrow} size={16} stroke={2.4} />
                </MotionLink>
            </section>
        );
    }

    const progress = clamp(resume.progress);
    const step = resume.current_step;

    return (
        <section data-anim="block" aria-label="Reprendre" className="flex flex-col gap-4 rounded-[22px] border border-[var(--dr-border)] bg-[var(--dr-surface)] p-[18px] shadow-[var(--dr-shadow)]">
            <div className="flex items-center gap-4">
                <div className="relative h-[68px] w-[68px] shrink-0">
                    <svg width="68" height="68" viewBox="0 0 68 68" aria-hidden="true">
                        <circle cx="34" cy="34" r={radius} fill="none" stroke="var(--dr-field)" strokeWidth="7" />
                        <m.circle
                            cx="34" cy="34" r={radius} fill="none" stroke="var(--dr-accent)" strokeWidth="7" strokeLinecap="round"
                            strokeDasharray={circumference}
                            initial={{ strokeDashoffset: circumference }}
                            animate={{ strokeDashoffset: circumference * (1 - progress / 100) }}
                            transition={{ ...softSpring, delay: 0.2 }}
                            transform="rotate(-90 34 34)"
                        />
                    </svg>
                    <span className="absolute inset-0 flex items-center justify-center text-base font-bold tabular-nums">{progress}%</span>
                </div>
                <div className="flex min-w-0 flex-1 flex-col gap-[3px]">
                    <span className="text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-accent-text)]">Reprendre</span>
                    <span className="text-[17px] font-bold leading-[1.3]">{resume.title}</span>
                    <span className="text-[13px] text-[var(--dr-text-2)]">{resume.completed_steps_count ?? 0} {(resume.completed_steps_count ?? 0) > 1 ? "étapes" : "étape"} sur {resume.steps_count ?? 0}</span>
                </div>
            </div>
            {step && <div className="flex items-center gap-3 rounded-[14px] bg-[var(--dr-field)] px-3.5 py-3">
                <span aria-hidden="true" className="h-2 w-2 shrink-0 rounded-full bg-[var(--dr-accent)] shadow-[0_0_0_4px_var(--dr-accent-soft)]" />
                <span className="flex min-w-0 flex-1 flex-col">
                    <span className="text-xs text-[var(--dr-text-3)]">Étape suivante</span>
                    <span className="truncate text-sm font-semibold">{step.title}</span>
                </span>
            </div>}
            <MotionLink whileTap={{ scale: 0.97 }} href={step ? "/steps/" + step.id : "/roadmaps/" + resume.id} className="flex h-12 items-center justify-center gap-2 rounded-[14px] bg-[var(--dr-accent)] text-[15px] font-bold text-[var(--dr-ink)]">
                Continuer<Svg d={ICONS.arrow} size={16} stroke={2.4} />
            </MotionLink>
        </section>
    );
}

function Shortcut({ href, icon, label, className = "" }) {
    return (
        <MotionLink whileTap={{ scale: 0.96 }} href={href} className={"flex flex-col gap-2.5 rounded-[18px] border border-[var(--dr-border)] bg-[var(--dr-surface)] p-3.5 text-[var(--dr-text)] " + className}>
            <span className="flex h-9 w-9 items-center justify-center rounded-[11px] bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]"><Svg d={icon} /></span>
            <span className="text-sm font-semibold">{label}</span>
        </MotionLink>
    );
}

function SectionTitle({ title, href }) {
    return (
        <div className="flex items-baseline justify-between">
            <h2 className="m-0 text-[17px] font-bold">{title}</h2>
            <Link href={href} className="text-sm font-semibold text-[var(--dr-accent-text)]">Tout voir</Link>
        </div>
    );
}

function RoadmapRow({ roadmap }) {
    const progress = clamp(roadmap.progress);
    return (
        <MotionLink whileTap={{ scale: 0.985 }} href={"/roadmaps/" + roadmap.id} className="flex items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-3.5 py-3 text-[var(--dr-text)]">
            <span aria-hidden="true" className="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-[11px] bg-[var(--dr-field)] font-['JetBrains_Mono',ui-monospace,monospace] text-sm text-[var(--dr-accent-text)]">{monogram(roadmap.title)}</span>
            <span className="flex min-w-0 flex-1 flex-col gap-1.5">
                <span className="flex items-baseline justify-between gap-3">
                    <span className="truncate text-[15px] font-semibold">{roadmap.title}</span>
                    <span className="shrink-0 text-xs font-semibold tabular-nums text-[var(--dr-text-2)]">{progress}%</span>
                </span>
                <span className="h-1.5 overflow-hidden rounded-full bg-[var(--dr-field)]">
                    <m.span className="block h-full rounded-full bg-[var(--dr-accent)]" initial={{ width: 0 }} animate={{ width: progress + "%" }} transition={{ ...softSpring, delay: 0.25 }} />
                </span>
                {roadmap.current_step && <span className="truncate text-xs text-[var(--dr-text-3)]">Étape suivante : {roadmap.current_step.title}</span>}
            </span>
        </MotionLink>
    );
}

function Stat({ label, value }) {
    return (
        <div className="flex flex-col gap-0.5 rounded-[14px] bg-[var(--dr-field)] px-3 py-2.5">
            <dt className="text-xs text-[var(--dr-text-3)]">{label}</dt>
            <dd className="m-0 text-lg font-bold tabular-nums">{value}</dd>
        </div>
    );
}

function EmptyRow({ text, action, href }) {
    return (
        <div className="flex items-center justify-between gap-3 rounded-2xl border border-dashed border-[var(--dr-border-2)] px-3.5 py-3.5 text-sm text-[var(--dr-text-2)]">
            <span>{text}</span>
            <Link href={href} className="shrink-0 font-semibold text-[var(--dr-accent-text)]">{action}</Link>
        </div>
    );
}
