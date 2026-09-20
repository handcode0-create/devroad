import { Link } from '@inertiajs/react';
import { Check, Lock } from 'lucide-react';

// Grain très léger (SVG en data-URI, aucun fichier à télécharger).
const GRAIN =
    "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E\")";

export function Brand({ className = '' }) {
    return (
        <Link
            href="/"
            aria-label="DevRoad, retour à l'accueil"
            className={`inline-flex items-center gap-2.5 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6A00]/60 ${className}`}
        >
            <img src="/icondevroad.png" alt="" width="36" height="36" className="h-9 w-9 object-contain" />
            <span className="font-display text-xl font-extrabold tracking-[-0.02em] text-white">
                Dev<span className="text-[#FF6A00]">Road</span>
            </span>
        </Link>
    );
}

// Les étapes de l'exemple reprennent les états réels de l'application :
// terminée, en cours, verrouillée.
const STEPS = [
    { title: 'Introduction à Laravel', state: 'done' },
    { title: 'Installation et configuration', state: 'done' },
    { title: 'Architecture du projet', state: 'done' },
    { title: 'Routage', state: 'current' },
    { title: 'Controllers', state: 'locked' },
];

// La route parcourue s'arrête sur l'étape en cours (index 3 sur 4 intervalles).
const PROGRESS = 3 / 4;

function RoadPreview() {
    return (
        <figure aria-label="Exemple de roadmap : Laravel">
            <figcaption className="mb-4 text-sm text-slate-400">Exemple : la roadmap Laravel</figcaption>

            <div className="relative" style={{ '--p': PROGRESS }}>
                <span aria-hidden="true" className="road-line absolute bottom-6 left-[13px] top-6 text-white/[0.2]" />
                <span
                    aria-hidden="true"
                    className="road-line road-fill road-draw absolute bottom-6 left-[13px] top-6 text-[#FF6A00]"
                />

                <ol>
                    {STEPS.map((step) => (
                        <li key={step.title} className="relative flex h-12 items-center gap-4">
                            <span
                                aria-hidden="true"
                                className={`relative flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 ${
                                    step.state === 'done'
                                        ? 'border-[#FF6A00] bg-[#FF6A00] text-[#0D1725]'
                                        : step.state === 'current'
                                          ? 'border-[#FF6A00] bg-[#0D1725]'
                                          : 'border-white/25 bg-[#0D1725] text-slate-400'
                                }`}
                            >
                                {step.state === 'done' && <Check size={14} strokeWidth={3} />}
                                {step.state === 'current' && <span className="h-2.5 w-2.5 rounded-full bg-[#FF6A00]" />}
                                {step.state === 'locked' && <Lock size={12} />}
                            </span>

                            <span
                                className={`text-[0.9375rem] ${
                                    step.state === 'current'
                                        ? 'font-semibold text-white'
                                        : step.state === 'done'
                                          ? 'text-slate-200'
                                          : 'text-slate-400'
                                }`}
                            >
                                {step.title}
                            </span>

                            <span className="sr-only">
                                {step.state === 'done' && 'terminée'}
                                {step.state === 'current' && 'en cours'}
                                {step.state === 'locked' && 'verrouillée'}
                            </span>

                            {step.state === 'current' && (
                                <span aria-hidden="true" className="ml-auto text-sm font-medium text-[#FF6A00]">
                                    En cours
                                </span>
                            )}
                        </li>
                    ))}
                </ol>
            </div>
        </figure>
    );
}

// Panneau de marque des écrans d'authentification (visible dès 1024 px).
export default function BrandPanel() {
    return (
        <aside className="relative hidden overflow-hidden border-r border-white/[0.06] bg-[#0D1725] lg:flex lg:min-h-dvh lg:flex-col lg:justify-between lg:gap-14 lg:px-14 lg:py-12 xl:px-20">
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 opacity-[0.07]"
                style={{ backgroundImage: GRAIN }}
            />

            <div className="relative">
                <Brand />
            </div>

            <div className="relative">
                {/* Les trois mots s'éclairent : le parcours va du plan à la progression. */}
                <p className="font-display text-[3.5rem] font-extrabold leading-[0.95] tracking-[-0.04em] text-white xl:text-[4.5rem]">
                    <span className="block text-white/[0.5]">Planifie.</span>
                    <span className="block text-white/[0.75]">Apprends.</span>
                    <span className="block">Progresse.</span>
                </p>

                <p className="mt-8 max-w-md text-base leading-7 text-slate-400">
                    Des roadmaps claires, des fiches mémo efficaces et un suivi de progression pour atteindre
                    tes objectifs.
                </p>
            </div>

            <div className="relative max-w-md">
                <RoadPreview />
            </div>
        </aside>
    );
}
