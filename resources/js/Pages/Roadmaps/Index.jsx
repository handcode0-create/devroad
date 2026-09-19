import { Head, Link } from "@inertiajs/react";
import { ArrowRight, ChevronRight, Map, Plus, Search } from "lucide-react";

import AppLayout from "@/Layouts/AppLayout";
import technologyLogos from "@/Config/technologyLogos";

export default function Index({ roadmaps }) {
    const items = roadmaps?.data ?? [];

    return (
        <AppLayout>
            <Head title="Mes feuilles de route" />

            <div className="space-y-7">
                {/* Header */}
                <section>
                    <div className="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-[#FF8A3D]">
                                Parcours
                            </p>

                            <h1 className="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                Mes feuilles de route
                            </h1>

                            <p className="mt-2 max-w-xl text-sm leading-6 text-slate-500">
                                Choisis une stack et poursuis ton apprentissage
                                étape par étape.
                            </p>
                        </div>

                        <Link
                            href="/roadmaps/create"
                            className="inline-flex w-fit items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_22px_rgba(255,106,0,0.22)] transition hover:bg-[#ff781a]"
                        >
                            <Plus size={17} />
                            Nouvelle roadmap
                        </Link>
                    </div>
                </section>

                {/* Search / filter visual */}
                <section>
                    <div className="relative">
                        <Search
                            size={18}
                            className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-600"
                        />

                        <input
                            type="search"
                            placeholder="Rechercher une stack..."
                            className="
                                h-12
                                w-full
                                rounded-2xl
                                border
                                border-white/[0.07]
                                bg-[#101A2A]
                                pl-11
                                pr-4
                                text-sm
                                text-white
                                outline-none
                                placeholder:text-slate-600
                                focus:border-[#FF6A00]/40
                                focus:ring-2
                                focus:ring-[#FF6A00]/10
                            "
                        />
                    </div>
                </section>

                {/* Cards */}
                {items.length > 0 ? (
                    <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        {items.map((roadmap) => (
                            <RoadmapCard key={roadmap.id} roadmap={roadmap} />
                        ))}
                    </section>
                ) : (
                    <EmptyState />
                )}

                {/* Pagination */}
                {roadmaps?.links?.length > 3 && (
                    <Pagination links={roadmaps.links} />
                )}
            </div>
        </AppLayout>
    );
}

/*
|--------------------------------------------------------------------------
| Roadmap card
|--------------------------------------------------------------------------
*/

function RoadmapCard({ roadmap }) {
    const progress = clampProgress(roadmap.progress);
    const logo = getTechnologyLogo(roadmap.title);

    return (
        <Link
            href={`/roadmaps/${roadmap.id}`}
            className="
                group
                relative
                overflow-hidden
                rounded-2xl
                border
                border-white/[0.06]
                bg-[#0D1725]
                p-5
                transition-all
                duration-200
                hover:-translate-y-0.5
                hover:border-white/[0.11]
                hover:bg-[#101B2C]
            "
        >
            {/* top accent */}
            <div className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#FF6A00]/40 to-transparent opacity-0 transition group-hover:opacity-100" />

            <div className="flex items-start justify-between gap-4">
                {/* Logo */}
                <div className="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-white/[0.06] bg-white/[0.03] p-2.5">
                    {logo ? (
                        <img
                            src={logo}
                            alt={`${roadmap.title} logo`}
                            className="h-full w-full object-contain"
                        />
                    ) : (
                        <span className="text-xl font-bold text-[#FF8A3D]">
                            {getInitial(roadmap.title)}
                        </span>
                    )}
                </div>

                <span
                    className={`
                        rounded-full
                        px-2.5
                        py-1
                        text-[10px]
                        font-semibold
                        ${
                            roadmap.status === "active"
                                ? "bg-[#FF6A00]/10 text-[#FF8A3D]"
                                : roadmap.status === "completed"
                                  ? "bg-emerald-500/10 text-emerald-400"
                                  : "bg-white/[0.05] text-slate-500"
                        }
                    `}
                >
                    {formatStatus(roadmap.status)}
                </span>
            </div>

            <div className="mt-5">
                <h2 className="text-base font-bold text-white">
                    {roadmap.title}
                </h2>

                <p className="mt-1 text-xs text-slate-500">
                    {roadmap.steps_count ?? 0} étapes
                </p>
            </div>

            <div className="mt-5">
                <div className="flex items-center justify-between text-[11px]">
                    <span className="text-slate-600">Progression</span>

                    <span className="font-semibold text-slate-400">
                        {progress}%
                    </span>
                </div>

                <div className="mt-2 h-2 overflow-hidden rounded-full bg-white/[0.06]">
                    <div
                        className="h-full rounded-full bg-[#FF6A00] transition-all duration-500"
                        style={{
                            width: `${progress}%`,
                        }}
                    />
                </div>
            </div>

            <div className="mt-5 flex items-center justify-between border-t border-white/[0.06] pt-4">
                <span className="text-[11px] text-slate-600">
                    {roadmap.completed_steps_count ?? 0} /{" "}
                    {roadmap.steps_count ?? 0} terminées
                </span>

                <span className="flex items-center gap-1 text-xs font-semibold text-[#FF8A3D]">
                    Ouvrir
                    <ArrowRight
                        size={14}
                        className="transition-transform group-hover:translate-x-0.5"
                    />
                </span>
            </div>
        </Link>
    );
}

/*
|--------------------------------------------------------------------------
| Empty state
|--------------------------------------------------------------------------
*/

function EmptyState() {
    return (
        <section className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] px-6 py-12 text-center">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                <Map size={25} />
            </div>

            <h2 className="mt-4 text-lg font-bold text-white">
                Aucun parcours
            </h2>

            <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                Crée ta première feuille de route pour commencer à organiser ton
                apprentissage.
            </p>

            <Link
                href="/roadmaps/create"
                className="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#ff781a]"
            >
                <Plus size={17} />
                Créer une roadmap
            </Link>
        </section>
    );
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function Pagination({ links }) {
    return (
        <div className="flex flex-wrap items-center justify-center gap-2 pt-2">
            {links.map((link, index) => {
                const label = stripHtml(link.label);

                if (!link.url) {
                    return (
                        <span
                            key={index}
                            className="flex h-9 min-w-9 items-center justify-center rounded-xl bg-white/[0.03] px-3 text-xs text-slate-700"
                        >
                            {label}
                        </span>
                    );
                }

                return (
                    <Link
                        key={index}
                        href={link.url}
                        className={[
                            "flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold transition",
                            link.active
                                ? "bg-[#FF6A00] text-white"
                                : "bg-white/[0.04] text-slate-500 hover:bg-white/[0.07] hover:text-white",
                        ].join(" ")}
                    >
                        {label}
                    </Link>
                );
            })}
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
    const number = Number(value ?? 0);

    return Math.min(Math.max(number, 0), 100);
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

function stripHtml(value) {
    return value.replace(/<[^>]+>/g, "");
}
