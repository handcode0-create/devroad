import { Head, Link, router } from "@inertiajs/react";
import {
    ArrowLeft,
    ArrowRight,
    Check,
    CheckCircle2,
    Circle,
    Clock3,
    Lock,
} from "lucide-react";

import AppLayout from "@/Layouts/AppLayout";

export default function Show({ step, roadmap, previous_step, next_step }) {
    const completed = step.status === "completed";
    const inProgress = step.status === "in_progress";
    const blocked = step.status === "blocked";

    function changeStatus(status) {
        router.patch(
            `/steps/${step.id}/status`,
            {
                status,
            },
            {
                preserveScroll: true,
            },
        );
    }

    return (
        <AppLayout>
            <Head title={step.title} />

            <div className="mx-auto max-w-4xl space-y-6">
                {/* Breadcrumb */}
                <Link
                    href={`/roadmaps/${roadmap.id}`}
                    className="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-white"
                >
                    <ArrowLeft size={16} />
                    {roadmap.title}
                </Link>

                {/* Header */}
                <section className="rounded-3xl border border-white/[0.06] bg-gradient-to-br from-[#111D2E] to-[#0D1725] p-6 sm:p-8">
                    <div className="flex flex-wrap items-center gap-3">
                        <span className="rounded-full bg-[#FF6A00]/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#FF8A3D]">
                            Étape {step.position}
                        </span>

                        <StatusBadge status={step.status} />
                    </div>

                    <h1 className="mt-5 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        {step.title}
                    </h1>

                    {step.description && (
                        <p className="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                            {step.description}
                        </p>
                    )}
                </section>

                {/* Course content */}
                <article className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-8">
                    <div className="max-w-none">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-600">
                            Contenu du cours
                        </p>

                        <div className="mt-6 space-y-5 text-sm leading-7 text-slate-400">
                            <p>
                                Bienvenue dans cette étape de ton parcours{" "}
                                <span className="font-semibold text-white">
                                    {roadmap.title}
                                </span>
                                .
                            </p>

                            {step.description ? (
                                <p>{step.description}</p>
                            ) : (
                                <p>
                                    Le contenu pédagogique de cette étape sera
                                    ajouté ici. Cette zone servira à présenter
                                    les explications, exemples, commandes,
                                    exercices et ressources du cours.
                                </p>
                            )}

                            <div className="rounded-2xl border border-[#FF6A00]/10 bg-[#FF6A00]/[0.035] p-5">
                                <h2 className="font-semibold text-white">
                                    Objectif
                                </h2>

                                <p className="mt-2 text-sm leading-6 text-slate-500">
                                    Comprendre la notion, pratiquer et être
                                    capable de passer à l'étape suivante.
                                </p>
                            </div>
                        </div>
                    </div>
                </article>

                {/* Completion */}
                {!blocked && (
                    <section className="rounded-2xl border border-white/[0.06] bg-[#111D2D] p-4 sm:p-5">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p className="text-sm font-semibold text-white">
                                    Ton avancement
                                </p>

                                <p className="mt-1 text-xs text-slate-500">
                                    Marque cette étape comme terminée quand tu
                                    as fini le cours.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() =>
                                    changeStatus(
                                        completed ? "in_progress" : "completed",
                                    )
                                }
                                className={[
                                    "inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition",
                                    completed
                                        ? "border border-white/[0.08] bg-white/[0.04] text-slate-300 hover:bg-white/[0.07]"
                                        : "bg-[#FF6A00] text-white shadow-[0_8px_22px_rgba(255,106,0,0.24)] hover:bg-[#ff781a]",
                                ].join(" ")}
                            >
                                <Check size={17} />

                                {completed
                                    ? "Remettre en cours"
                                    : "Marquer comme terminée"}
                            </button>
                        </div>
                    </section>
                )}

                {/* Previous / Next */}
                <section className="grid gap-3 sm:grid-cols-2">
                    {previous_step ? (
                        <Link
                            href={`/steps/${previous_step.id}`}
                            className="group rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4 transition hover:border-white/[0.10] hover:bg-[#101B2C]"
                        >
                            <p className="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                Précédent
                            </p>

                            <div className="mt-2 flex items-center justify-between gap-3">
                                <span className="truncate text-sm font-semibold text-white">
                                    {previous_step.title}
                                </span>

                                <ArrowLeft
                                    size={16}
                                    className="shrink-0 text-slate-600 transition group-hover:-translate-x-0.5 group-hover:text-[#FF8A3D]"
                                />
                            </div>
                        </Link>
                    ) : (
                        <div />
                    )}

                    {next_step && (
                        <Link
                            href={`/steps/${next_step.id}`}
                            className="group rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4 text-right transition hover:border-white/[0.10] hover:bg-[#101B2C]"
                        >
                            <p className="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                Suivant
                            </p>

                            <div className="mt-2 flex items-center justify-end gap-3">
                                <span className="truncate text-sm font-semibold text-white">
                                    {next_step.title}
                                </span>

                                <ArrowRight
                                    size={16}
                                    className="shrink-0 text-slate-600 transition group-hover:translate-x-0.5 group-hover:text-[#FF8A3D]"
                                />
                            </div>
                        </Link>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}

function StatusBadge({ status }) {
    const config = {
        completed: {
            label: "Terminée",
            className: "bg-emerald-500/10 text-emerald-400",
            icon: CheckCircle2,
        },

        in_progress: {
            label: "En cours",
            className: "bg-[#FF6A00]/10 text-[#FF8A3D]",
            icon: Clock3,
        },

        blocked: {
            label: "Bloquée",
            className: "bg-red-500/10 text-red-400",
            icon: Lock,
        },

        todo: {
            label: "À faire",
            className: "bg-white/[0.05] text-slate-500",
            icon: Circle,
        },
    };

    const current = config[status] ?? config.todo;
    const Icon = current.icon;

    return (
        <span
            className={[
                "inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-semibold",
                current.className,
            ].join(" ")}
        >
            <Icon size={13} />
            {current.label}
        </span>
    );
}
