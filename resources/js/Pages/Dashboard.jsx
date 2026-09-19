import { Head, Link, router, usePage } from "@inertiajs/react";
import { useState } from "react";
import {
    ArrowRight,
    BookOpen,
    CheckCircle2,
    FileText,
    Map,
    Search,
    Sparkles,
    TrendingUp,
} from "lucide-react";

import AppLayout from "@/Layouts/AppLayout";
import technologyLogos from "@/Config/technologyLogos";

export default function Dashboard({ stats, recent_roadmaps, continue_roadmap }) {
    const { auth } = usePage().props;

    const user = auth?.user;

    const [search, setSearch] = useState("");

    const safeStats = {
        roadmaps: Number(stats?.roadmaps ?? 0),
        memos: Number(stats?.memos ?? 0),
        steps_total: Number(stats?.steps_total ?? 0),
        steps_completed: Number(stats?.steps_completed ?? 0),
        progress: Number(stats?.progress ?? 0),
    };

    const firstName = user?.name?.trim()?.split(" ")[0] ?? "Développeur";

    const recentRoadmaps = Array.isArray(recent_roadmaps)
        ? recent_roadmaps
        : [];

    const latestRoadmap = continue_roadmap ?? recentRoadmaps[0] ?? null;

    function submitSearch(event) {
        event.preventDefault();

        const query = search.trim();

        if (!query) {
            router.visit("/search");
            return;
        }

        router.get(
            "/search",
            {
                q: query,
                type: "all",
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }

    return (
        <AppLayout>
            <Head title="Accueil" />

            <div className="space-y-7">
                {/* =========================================================
                    HEADER
                ========================================================= */}
                <section>
                    <div className="flex flex-col gap-5">
                        <div>
                            <p className="text-sm font-medium text-slate-400">
                                Bonjour {firstName} 👋
                            </p>

                            <h1 className="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                Ton parcours de développeur.
                            </h1>

                            <p className="mt-2 max-w-xl text-sm leading-6 text-slate-500">
                                Continue à apprendre, organise tes connaissances
                                et progresse étape par étape.
                            </p>
                        </div>

                        {/* =================================================
                            SEARCH
                        ================================================= */}
                        <form onSubmit={submitSearch} className="relative">
                            <Search
                                size={19}
                                strokeWidth={2}
                                className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"
                            />

                            <input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Rechercher une roadmap, une techno, un mémo..."
                                className="
                                    h-12
                                    w-full
                                    rounded-2xl
                                    border
                                    border-white/[0.07]
                                    bg-[#101A2A]
                                    pl-11
                                    pr-24
                                    text-sm
                                    text-white
                                    outline-none
                                    transition
                                    placeholder:text-slate-600
                                    focus:border-[#FF6A00]/40
                                    focus:ring-2
                                    focus:ring-[#FF6A00]/10
                                "
                            />

                            <button
                                type="submit"
                                className="
                                    absolute
                                    right-1.5
                                    top-1/2
                                    -translate-y-1/2
                                    rounded-xl
                                    bg-[#FF6A00]
                                    px-3.5
                                    py-2
                                    text-xs
                                    font-bold
                                    text-white
                                    transition
                                    hover:bg-[#ff781a]
                                "
                            >
                                Rechercher
                            </button>
                        </form>
                    </div>
                </section>

                {/* =========================================================
                    PRIMARY CTA
                ========================================================= */}
                {latestRoadmap ? (
                    <section className="overflow-hidden rounded-2xl border border-white/[0.06] bg-gradient-to-br from-[#111D2E] to-[#0D1725]">
                        <div className="flex flex-col gap-5 p-5 sm:p-6 md:flex-row md:items-center md:justify-between">
                            <div className="min-w-0">
                                <div className="flex items-center gap-2">
                                    <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                                        <TrendingUp size={18} />
                                    </span>

                                    <span className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                        À reprendre
                                    </span>
                                </div>

                                <h2 className="mt-3 truncate text-xl font-bold text-white">
                                    {latestRoadmap.current_step?.title ??
                                        latestRoadmap.title}
                                </h2>

                                <p className="mt-1 text-sm text-slate-500">
                                    {latestRoadmap.current_step
                                        ? `Étape ${latestRoadmap.current_step.position} · ${latestRoadmap.title}`
                                        : `${latestRoadmap.completed_steps_count ?? 0} étape${(latestRoadmap.completed_steps_count ?? 0) > 1 ? "s" : ""} sur ${latestRoadmap.steps_count ?? 0}`}
                                </p>

                                <div className="mt-4 h-2 overflow-hidden rounded-full bg-white/[0.06]">
                                    <div
                                        className="h-full rounded-full bg-[#FF6A00]"
                                        style={{
                                            width: `${clampProgress(
                                                latestRoadmap.progress,
                                            )}%`,
                                        }}
                                    />
                                </div>
                            </div>

                            <div className="flex flex-wrap items-center gap-3">
                                <Link
                                    href={latestRoadmap.current_step
                                        ? `/steps/${latestRoadmap.current_step.id}`
                                        : `/roadmaps/${latestRoadmap.id}`}
                                    className="
                                        inline-flex
                                        items-center
                                        gap-2
                                        rounded-xl
                                        bg-[#FF6A00]
                                        px-4
                                        py-2.5
                                        text-sm
                                        font-semibold
                                        text-white
                                        shadow-[0_8px_22px_rgba(255,106,0,0.24)]
                                        transition
                                        hover:bg-[#ff781a]
                                    "
                                >
                                    Reprendre
                                    <ArrowRight size={16} />
                                </Link>

                                <Link
                                    href="/roadmaps/create"
                                    className="
                                        inline-flex
                                        items-center
                                        gap-2
                                        rounded-xl
                                        border
                                        border-white/[0.08]
                                        bg-white/[0.03]
                                        px-4
                                        py-2.5
                                        text-sm
                                        font-semibold
                                        text-slate-300
                                        transition
                                        hover:bg-white/[0.06]
                                        hover:text-white
                                    "
                                >
                                    <Map size={16} />
                                    Nouvelle roadmap
                                </Link>
                            </div>
                        </div>
                    </section>
                ) : (
                    <section className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-6">
                        <div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                                    <Sparkles size={19} />
                                </div>

                                <h2 className="mt-4 text-lg font-bold text-white">
                                    Commence ton premier parcours
                                </h2>

                                <p className="mt-1 max-w-lg text-sm leading-6 text-slate-500">
                                    Crée une roadmap pour structurer ce que tu
                                    veux apprendre.
                                </p>
                            </div>

                            <Link
                                href="/roadmaps/create"
                                className="
                                    inline-flex
                                    items-center
                                    justify-center
                                    gap-2
                                    rounded-xl
                                    bg-[#FF6A00]
                                    px-4
                                    py-2.5
                                    text-sm
                                    font-semibold
                                    text-white
                                    transition
                                    hover:bg-[#ff781a]
                                "
                            >
                                <Map size={16} />
                                Créer ma roadmap
                            </Link>
                        </div>
                    </section>
                )}

                {/* =========================================================
                    GLOBAL PROGRESS
                ========================================================= */}
                <section className="overflow-hidden rounded-2xl border border-white/[0.06] bg-gradient-to-br from-[#111D2E] to-[#0D1725]">
                    <div className="p-5 sm:p-6">
                        <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <div>
                                <div className="flex items-center gap-2">
                                    <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                                        <TrendingUp size={18} />
                                    </span>

                                    <span className="text-sm font-semibold text-white">
                                        Progression globale
                                    </span>
                                </div>

                                <p className="mt-4 text-sm leading-6 text-slate-400">
                                    Tu as complété{" "}
                                    <strong className="text-white">
                                        {safeStats.steps_completed}
                                    </strong>{" "}
                                    étape
                                    {safeStats.steps_completed > 1
                                        ? "s"
                                        : ""}{" "}
                                    sur{" "}
                                    <strong className="text-white">
                                        {safeStats.steps_total}
                                    </strong>
                                    .
                                </p>
                            </div>

                            <ProgressRing progress={safeStats.progress} />
                        </div>
                    </div>
                </section>

                {/* =========================================================
                    STATS
                ========================================================= */}
                <section className="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <StatCard
                        icon={Map}
                        label="Roadmaps"
                        value={safeStats.roadmaps}
                        tone="orange"
                    />

                    <StatCard
                        icon={FileText}
                        label="Fiches mémo"
                        value={safeStats.memos}
                        tone="blue"
                    />

                    <StatCard
                        icon={BookOpen}
                        label="Étapes"
                        value={safeStats.steps_total}
                        tone="purple"
                    />

                    <StatCard
                        icon={CheckCircle2}
                        label="Terminées"
                        value={safeStats.steps_completed}
                        tone="green"
                    />
                </section>

                {/* =========================================================
                    ROADMAPS
                ========================================================= */}
                <section>
                    <div className="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <h2 className="text-lg font-bold text-white">
                                Mes roadmaps
                            </h2>

                            <p className="mt-1 text-xs text-slate-500">
                                Continue là où tu t'es arrêté.
                            </p>
                        </div>

                        <Link
                            href="/roadmaps"
                            className="shrink-0 text-xs font-semibold text-[#FF8A3D] transition hover:text-[#FFA66E]"
                        >
                            Voir tout
                        </Link>
                    </div>

                    {recentRoadmaps.length > 0 ? (
                        <div className="space-y-3">
                            {recentRoadmaps.map((roadmap) => (
                                <RoadmapCard
                                    key={roadmap.id}
                                    roadmap={roadmap}
                                />
                            ))}
                        </div>
                    ) : (
                        <EmptyRoadmaps />
                    )}
                </section>

                {/* =========================================================
                    QUICK ACTIONS
                ========================================================= */}
                <section>
                    <div className="mb-4">
                        <h2 className="text-lg font-bold text-white">
                            Accès rapides
                        </h2>

                        <p className="mt-1 text-xs text-slate-500">
                            Les actions que tu utiliseras le plus.
                        </p>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-3">
                        <QuickAction
                            href="/memos/create"
                            icon={FileText}
                            title="Nouvelle fiche mémo"
                            description="Note une commande, une notion ou une astuce."
                        />

                        <QuickAction
                            href="/roadmaps"
                            icon={Map}
                            title="Explorer mes roadmaps"
                            description="Retrouve tous tes parcours d'apprentissage."
                        />

                        <QuickAction
                            href="/search"
                            icon={Search}
                            title="Recherche globale"
                            description="Recherche dans tes roadmaps, mémos et étapes."
                        />
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}

/*
|--------------------------------------------------------------------------
| Progress Ring
|--------------------------------------------------------------------------
*/

function ProgressRing({ progress }) {
    const safeProgress = clampProgress(progress);

    return (
        <div className="flex items-center gap-4">
            <div className="relative h-20 w-20 shrink-0">
                <svg viewBox="0 0 36 36" className="h-full w-full -rotate-90">
                    <circle
                        cx="18"
                        cy="18"
                        r="15"
                        fill="none"
                        stroke="rgba(255,255,255,0.06)"
                        strokeWidth="3"
                    />

                    <circle
                        cx="18"
                        cy="18"
                        r="15"
                        fill="none"
                        stroke="#FF6A00"
                        strokeWidth="3"
                        strokeLinecap="round"
                        strokeDasharray={`${safeProgress} 100`}
                    />
                </svg>

                <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-base font-bold text-white">
                        {safeProgress}%
                    </span>
                </div>
            </div>

            <div>
                <p className="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-600">
                    Avancement
                </p>

                <p className="mt-1 text-base font-bold text-white">
                    {getProgressLabel(safeProgress)}
                </p>
            </div>
        </div>
    );
}

/*
|--------------------------------------------------------------------------
| Stat Card
|--------------------------------------------------------------------------
*/

function StatCard({ icon: Icon, label, value, tone }) {
    const tones = {
        orange: {
            background: "bg-[#FF6A00]/10",
            text: "text-[#FF8A3D]",
        },
        blue: {
            background: "bg-blue-500/10",
            text: "text-blue-400",
        },
        purple: {
            background: "bg-violet-500/10",
            text: "text-violet-400",
        },
        green: {
            background: "bg-emerald-500/10",
            text: "text-emerald-400",
        },
    };

    const currentTone = tones[tone] ?? tones.orange;

    return (
        <div className="rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4 sm:p-5">
            <div
                className={[
                    "flex h-10 w-10 items-center justify-center rounded-xl",
                    currentTone.background,
                    currentTone.text,
                ].join(" ")}
            >
                <Icon size={19} strokeWidth={2} />
            </div>

            <div className="mt-4">
                <p className="text-2xl font-bold tracking-tight text-white">
                    {value}
                </p>

                <p className="mt-1 text-xs font-medium text-slate-500">
                    {label}
                </p>
            </div>
        </div>
    );
}

/*
|--------------------------------------------------------------------------
| Roadmap Card
|--------------------------------------------------------------------------
*/

function RoadmapCard({ roadmap }) {
    const progress = clampProgress(roadmap.progress);
    const logo = getTechnologyLogo(roadmap.technology);

    return (
        <Link
            href={`/roadmaps/${roadmap.id}`}
            className="
                group
                block
                rounded-2xl
                border
                border-white/[0.06]
                bg-[#0D1725]
                p-4
                transition
                hover:border-white/[0.11]
                hover:bg-[#101B2C]
            "
        >
            <div className="flex items-center gap-4">
                {/* Technology */}
                <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.03] p-2">
                    {logo ? (
                        <img
                            src={logo}
                            alt={`${roadmap.title} logo`}
                            className="h-full w-full object-contain"
                        />
                    ) : (
                        <span className="text-lg font-bold text-[#FF8A3D]">
                            {getInitial(roadmap.title)}
                        </span>
                    )}
                </div>

                <div className="min-w-0 flex-1">
                    <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0">
                            <h3 className="truncate text-sm font-semibold text-white">
                                {roadmap.title}
                            </h3>

                            <p className="mt-1 text-xs text-slate-500">
                                {formatStatus(roadmap.status)}
                            </p>
                        </div>

                        <span className="shrink-0 text-xs font-semibold text-slate-400">
                            {progress}%
                        </span>
                    </div>

                    <div className="mt-3 h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
                        <div
                            className="h-full rounded-full bg-[#FF6A00] transition-all duration-500"
                            style={{
                                width: `${progress}%`,
                            }}
                        />
                    </div>

                    <div className="mt-2 flex items-center justify-between">
                        <span className="text-[11px] text-slate-600">
                            {roadmap.completed_steps_count ?? 0} /{" "}
                            {roadmap.steps_count ?? 0} étapes
                        </span>

                        <ArrowRight
                            size={15}
                            className="text-slate-600 transition group-hover:translate-x-0.5 group-hover:text-[#FF8A3D]"
                        />
                    </div>
                </div>
            </div>
        </Link>
    );
}

/*
|--------------------------------------------------------------------------
| Quick Action
|--------------------------------------------------------------------------
*/

function QuickAction({ href, icon: Icon, title, description }) {
    return (
        <Link
            href={href}
            className="
                group
                rounded-2xl
                border
                border-white/[0.06]
                bg-[#0D1725]
                p-4
                transition
                hover:border-white/[0.11]
                hover:bg-[#101B2C]
            "
        >
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                <Icon size={19} strokeWidth={2} />
            </div>

            <h3 className="mt-4 text-sm font-semibold text-white">{title}</h3>

            <p className="mt-1 text-xs leading-5 text-slate-500">
                {description}
            </p>
        </Link>
    );
}

/*
|--------------------------------------------------------------------------
| Empty state
|--------------------------------------------------------------------------
*/

function EmptyRoadmaps() {
    return (
        <div className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-8 text-center">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                <Map size={25} />
            </div>

            <h3 className="mt-4 text-base font-semibold text-white">
                Aucune roadmap pour le moment
            </h3>

            <p className="mx-auto mt-1 max-w-sm text-sm leading-6 text-slate-500">
                Crée ton premier parcours d'apprentissage pour commencer à
                structurer ta progression.
            </p>

            <Link
                href="/roadmaps/create"
                className="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#ff781a]"
            >
                <Map size={16} />
                Créer une roadmap
            </Link>
        </div>
    );
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function getTechnologyLogo(title) {
    if (!title) {
        return null;
    }

    const normalized = title
        .toLowerCase()
        .trim()
        .replace(/\s+/g, "")
        .replace(/_/g, "-");

    return (
        technologyLogos[normalized] ??
        technologyLogos[normalized.replace(/\./g, "")] ??
        null
    );
}

function getInitial(title) {
    return title?.trim()?.charAt(0)?.toUpperCase() ?? "D";
}

function clampProgress(value) {
    const progress = Number(value ?? 0);

    return Math.min(Math.max(progress, 0), 100);
}

function formatStatus(status) {
    const labels = {
        draft: "Brouillon",
        active: "En cours",
        completed: "Terminée",
        archived: "Archivée",
    };

    return labels[status] ?? status;
}

function getProgressLabel(progress) {
    if (progress >= 80) {
        return "Excellent";
    }

    if (progress >= 50) {
        return "Bon rythme";
    }

    if (progress > 0) {
        return "Continue";
    }

    return "À commencer";
}
