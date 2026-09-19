import { Head, Link, router } from "@inertiajs/react";
import {
    ArrowRight,
    Code2,
    Eye,
    Play,
    Terminal,
} from "lucide-react";

import AppLayout from "@/Layouts/AppLayout";
import CodeWorkspace from "@/Components/Learning/CodeWorkspace";
import technologyLogos from "@/Config/technologyLogos";

export default function Index({
    roadmaps = [],
    active_roadmap = null,
    active_step = null,
}) {
    const hasRoadmaps = Array.isArray(roadmaps) && roadmaps.length > 0;

    function selectRoadmap(roadmapId) {
        router.get(
            "/devlab",
            { roadmap: roadmapId },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    return (
        <AppLayout>
            <Head title="DevLab" />

            <div className="space-y-7">
                <section className="overflow-hidden rounded-3xl border border-white/[0.06] bg-gradient-to-br from-[#111D2E] to-[#0D1725]">
                    <div className="p-6 sm:p-8">
                        <div className="max-w-3xl">
                            <div className="inline-flex items-center gap-2 rounded-full border border-[#FF6A00]/20 bg-[#FF6A00]/[0.07] px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-[#FF8A3D]">
                                <Code2 size={13} />
                                Environnement d’apprentissage
                            </div>

                            <h1 className="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                                DevLab
                            </h1>

                            <p className="mt-3 max-w-2xl text-sm leading-7 text-slate-500 sm:text-base">
                                Écris ton code, explore tes fichiers, ouvre un
                                terminal pédagogique et prévisualise ton projet
                                directement dans DevRoad.
                            </p>
                        </div>

                        <div className="mt-7 grid gap-3 sm:grid-cols-3">
                            <Feature
                                icon={Code2}
                                title="Éditeur"
                                text="Travaille directement dans ton navigateur."
                            />
                            <Feature
                                icon={Terminal}
                                title="Terminal"
                                text="Apprends les commandes utiles étape par étape."
                            />
                            <Feature
                                icon={Eye}
                                title="Aperçu"
                                text="Observe immédiatement le résultat de ton code."
                            />
                        </div>
                    </div>
                </section>

                {active_roadmap && active_step?.workspace?.enabled && (
                    <section className="overflow-hidden rounded-3xl border border-white/[0.06] bg-[#07101A]">
                        <div className="flex flex-col gap-3 border-b border-white/[0.06] bg-[#0D1725] p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                            <div className="min-w-0">
                                <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#FF8A3D]">
                                    IDE actif
                                </p>
                                <h2 className="mt-1 truncate text-lg font-bold text-white">
                                    {active_step.title}
                                </h2>
                                <p className="mt-1 text-xs text-slate-600">
                                    {active_roadmap.title} · étape {active_step.position} · {active_step.workspace.label}
                                </p>
                            </div>

                            <div className="inline-flex items-center gap-2 rounded-xl border border-white/[0.06] bg-white/[0.03] px-3 py-2 text-[10px] font-semibold text-slate-500">
                                {active_step.workspace.runtime === "server"
                                    ? "Runtime local"
                                    : "Runtime navigateur"}
                            </div>
                        </div>

                        <div className="p-3 sm:p-4 lg:p-5">
                            <CodeWorkspace
                                workspace={active_step.workspace}
                                stepId={active_step.id}
                            />
                        </div>
                    </section>
                )}

                <section>
                    <div className="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <p className="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#FF8A3D]">
                                Tes environnements
                            </p>
                            <h2 className="mt-1 text-xl font-bold text-white">
                                Choisis une roadmap
                            </h2>
                        </div>

                        <Link
                            href="/roadmaps"
                            className="text-xs font-semibold text-slate-500 transition hover:text-white"
                        >
                            Voir les roadmaps
                        </Link>
                    </div>

                    {hasRoadmaps ? (
                        <div className="grid gap-4 md:grid-cols-2">
                            {roadmaps.map((roadmap) => (
                                <RoadmapWorkspaceCard
                                    key={roadmap.id}
                                    roadmap={roadmap}
                                    active={roadmap.id === active_roadmap?.id}
                                    onSelect={selectRoadmap}
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="rounded-3xl border border-dashed border-white/[0.08] bg-[#0D1725] p-8 text-center">
                            <Code2
                                size={28}
                                className="mx-auto text-slate-700"
                            />
                            <h3 className="mt-4 text-base font-bold text-white">
                                Aucun environnement pour le moment
                            </h3>
                            <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                                Crée une roadmap et commence une première leçon
                                pour ouvrir ton IDE DevRoad.
                            </p>
                            <Link
                                href="/roadmaps/create"
                                className="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[#08111F]"
                            >
                                Créer une roadmap
                                <ArrowRight size={16} />
                            </Link>
                        </div>
                    )}
                </section>


            </div>
        </AppLayout>
    );
}

function RoadmapWorkspaceCard({ roadmap, active, onSelect }) {
    const tech = roadmap.technology
        ? formatTechnology(roadmap.technology)
        : "Technologie";

    const logo = roadmap.technology
        ? technologyLogos[roadmap.technology]
        : null;

    const target = "/devlab?roadmap=" + roadmap.id;

    return (
        <article className="overflow-hidden rounded-3xl border border-white/[0.06] bg-[#0D1725]">
            <div className="p-5 sm:p-6">
                <div className="flex items-start gap-4">
                    <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.03] p-2">
                        {logo ? (
                            <img
                                src={logo}
                                alt=""
                                className="h-full w-full object-contain"
                            />
                        ) : (
                            <Code2 size={20} className="text-[#FF8A3D]" />
                        )}
                    </div>

                    <div className="min-w-0 flex-1">
                        <p className="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                            {tech}
                        </p>
                        <h3 className="mt-1 truncate text-base font-bold text-white">
                            {roadmap.title}
                        </h3>
                        <p className="mt-1 text-xs text-slate-500">
                            {roadmap.current_step
                                ? "Étape " + roadmap.current_step.position + " · " + roadmap.current_step.title
                                : "Parcours terminé"}
                        </p>
                    </div>
                </div>

                <div className="mt-5">
                    <div className="flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.1em]">
                        <span className="text-slate-600">Progression</span>
                        <span className="text-[#FF8A3D]">
                            {roadmap.progress}%
                        </span>
                    </div>

                    <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
                        <div
                            className="h-full rounded-full bg-[#FF6A00]"
                            style={{ width: roadmap.progress + "%" }}
                        />
                    </div>
                </div>

                <div className="mt-5 flex items-center justify-between gap-3">
                    <span className="text-[10px] text-slate-600">
                        {roadmap.completed_steps_count} / {roadmap.steps_count} étapes
                    </span>

                    <button
                        type="button"
                        onClick={() => onSelect(roadmap.id)}
                        className={[
                            "inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition",
                            active
                                ? "border border-[#FF6A00]/30 bg-[#FF6A00]/10 text-[#FF8A3D]"
                                : "bg-[#FF6A00] text-[#08111F] hover:bg-[#ff781a]",
                        ].join(" ")}
                    >
                        {active ? "IDE ouvert" : "Ouvrir l'IDE"}
                        <Play size={13} fill="currentColor" />
                    </button>
                </div>
            </div>
        </article>
    );
}

function Feature({ icon: Icon, title, text }) {
    return (
        <div className="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                <Icon size={16} />
            </div>
            <p className="mt-3 text-sm font-bold text-white">{title}</p>
            <p className="mt-1 text-xs leading-5 text-slate-600">{text}</p>
        </div>
    );
}

function formatTechnology(value) {
    const labels = {
        laravel: "Laravel",
        nextjs: "Next.js",
        react: "React",
        javascript: "JavaScript",
        typescript: "TypeScript",
        php: "PHP",
        html: "HTML",
        css: "CSS",
        tailwind: "Tailwind CSS",
        node: "Node.js",
        git: "Git",
        github: "GitHub",
        docker: "Docker",
        mysql: "MySQL",
        postgresql: "PostgreSQL",
    };

    return labels[value] ?? value ?? "Technologie";
}
