import { Head, useForm } from "@inertiajs/react";
import { Check, GraduationCap, Sparkles, Target, Trophy } from "lucide-react";
import AuthLayout, { AuthLink } from "@/Components/Auth/AuthLayout";
import AuthButton from "@/Components/Auth/AuthButton";

const levelOptions = [
    {
        id: "beginner",
        title: "Débutant",
        description:
            "Je découvre le développement ou je souhaite consolider les fondamentaux.",
        icon: Sparkles,
    },
    {
        id: "intermediate",
        title: "Intermédiaire",
        description:
            "Je développe déjà des projets et je veux structurer et approfondir mes compétences.",
        icon: Target,
    },
    {
        id: "professional",
        title: "Professionnel",
        description:
            "Je travaille déjà comme développeur et je veux progresser sur des sujets avancés.",
        icon: Trophy,
    },
];

export default function LevelSelection({ level = "" }) {
    const { data, setData, post, processing, errors } = useForm({
        level: level ?? "",
    });

    function submit(event) {
        event.preventDefault();
        post(route("onboarding.level.store"));
    }

    return (
        <AuthLayout
            title="Quel est ton niveau ?"
            subtitle="Choisis le niveau qui correspond le mieux à ton expérience actuelle. Tu pourras toujours le modifier plus tard."
            footer={
                <AuthLink href={route("logout")} method="post" as="button">
                    Quitter l’inscription
                </AuthLink>
            }
        >
            <Head title="Choisir mon niveau" />

            <form onSubmit={submit} noValidate>
                <div className="space-y-3">
                    {levelOptions.map(({ id, title, description, icon: Icon }) => {
                        const active = data.level === id;

                        return (
                            <label
                                key={id}
                                className={[
                                    "group relative flex cursor-pointer items-center gap-4 overflow-hidden rounded-2xl border p-4 transition",
                                    active
                                        ? "border-[#FF6A00]/60 bg-[#FF6A00]/[0.08] shadow-[0_12px_30px_rgba(255,106,0,0.08)]"
                                        : "border-white/[0.07] bg-[#0D1725] hover:border-white/[0.14] hover:bg-[#101B2C]",
                                ].join(" ")}
                            >
                                <input
                                    type="checkbox"
                                    checked={active}
                                    onChange={() => setData("level", active ? "" : id)}
                                    className="sr-only"
                                />

                                <span
                                    className={[
                                        "flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border transition",
                                        active
                                            ? "border-[#FF6A00]/30 bg-[#FF6A00]/15 text-[#FF8A3D]"
                                            : "border-white/[0.07] bg-white/[0.03] text-slate-500",
                                    ].join(" ")}
                                >
                                    <Icon size={19} strokeWidth={2} />
                                </span>

                                <span className="min-w-0 flex-1">
                                    <span className="block text-sm font-semibold text-white">
                                        {title}
                                    </span>
                                    <span className="mt-1 block text-xs leading-5 text-slate-500">
                                        {description}
                                    </span>
                                </span>

                                <span
                                    className={[
                                        "flex h-5 w-5 shrink-0 items-center justify-center rounded-md border transition",
                                        active
                                            ? "border-[#FF6A00] bg-[#FF6A00] text-white"
                                            : "border-white/[0.14] bg-white/[0.02] text-transparent",
                                    ].join(" ")}
                                    aria-hidden="true"
                                >
                                    <Check size={13} strokeWidth={3} />
                                </span>
                            </label>
                        );
                    })}
                </div>

                <div className="mt-5 flex items-start gap-3 rounded-xl border border-white/[0.06] bg-white/[0.02] p-3.5">
                    <GraduationCap className="mt-0.5 shrink-0 text-slate-500" size={17} />
                    <p className="text-xs leading-5 text-slate-500">
                        Ce choix sert à personnaliser ton parcours. Il ne bloque
                        aucun cours et peut être modifié depuis ton profil.
                    </p>
                </div>

                {errors.level && (
                    <p className="mt-4 text-sm text-red-400">{errors.level}</p>
                )}

                <div className="mt-6">
                    <AuthButton
                        processing={processing}
                        busyLabel="Enregistrement..."
                        disabled={!data.level}
                    >
                        Continuer
                    </AuthButton>
                </div>
            </form>
        </AuthLayout>
    );
}
