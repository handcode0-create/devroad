import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';

function OnboardingProgress() {
    const { url } = usePage();
    const step = url.startsWith('/register') ? 3 : 2;

    return (
        <div className="mt-7 flex items-center justify-center gap-3" aria-label={`Étape ${step} sur 3`}>
            <span className={`h-2.5 rounded-full ${step === 1 ? 'w-8 bg-[#FF6A00]' : 'w-2.5 bg-white/25'}`} />
            <span className={`h-2.5 rounded-full ${step === 2 ? 'w-8 bg-[#FF6A00]' : 'w-2.5 bg-white/25'}`} />
            <span className={`h-2.5 rounded-full ${step === 3 ? 'w-8 bg-[#FF6A00]' : 'w-2.5 bg-white/25'}`} />
        </div>
    );
}

export default function GuestLayout({ children, title, description }) {
    return (
        <div className="min-h-screen bg-[#080B14] text-slate-100">
            <div className="mx-auto flex min-h-screen w-full max-w-[1440px] flex-col lg:flex-row">
                <aside className="relative hidden overflow-hidden border-r border-white/[0.06] lg:flex lg:w-[43%] lg:flex-col lg:justify-between lg:p-10 xl:p-14">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(255,106,0,0.14),transparent_30%),radial-gradient(circle_at_80%_85%,rgba(37,99,235,0.10),transparent_30%)]" />
                    <div className="relative">
                        <Link href="/" className="inline-flex items-center gap-3">
                            <ApplicationLogo className="h-10 w-10 object-contain" />
                            <span className="text-2xl font-extrabold tracking-tight">
                                Dev<span className="text-[#FF6A00]">Road</span>
                            </span>
                        </Link>
                    </div>

                    <div className="relative max-w-xl">
                        <div className="mb-5 inline-flex items-center rounded-full border border-[#FF6A00]/20 bg-[#FF6A00]/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-[#FF8A3D]">
                            Learning workspace
                        </div>
                        <h1 className="text-4xl font-bold tracking-tight text-white xl:text-5xl">
                            Construis tes compétences,
                            <span className="block text-[#FF6A00]">étape par étape.</span>
                        </h1>
                        <p className="mt-5 max-w-lg text-base leading-7 text-slate-400">
                            Retrouve tes roadmaps, tes leçons, tes exercices et tes mémos dans un espace pensé pour apprendre avec méthode.
                        </p>

                        <div className="mt-8 grid max-w-lg grid-cols-3 gap-3">
                            {[
                                ['01', 'Roadmaps'],
                                ['02', 'Leçons'],
                                ['03', 'Progression'],
                            ].map(([number, label]) => (
                                <div key={number} className="rounded-2xl border border-white/[0.07] bg-white/[0.03] p-4 backdrop-blur-sm">
                                    <div className="text-xs font-bold text-[#FF6A00]">{number}</div>
                                    <div className="mt-2 text-sm font-medium text-slate-300">{label}</div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <p className="relative text-xs text-slate-500">
                        Apprends à ton rythme. Construis du concret.
                    </p>
                </aside>

                <main className="flex min-h-screen flex-1 items-center justify-center px-5 py-8 sm:px-8 lg:px-12">
                    <div className="w-full max-w-md">
                        <div className="mb-8 lg:hidden">
                            <Link href="/" className="inline-flex items-center gap-3">
                                <ApplicationLogo className="h-9 w-9 object-contain" />
                                <span className="text-xl font-extrabold tracking-tight">
                                    Dev<span className="text-[#FF6A00]">Road</span>
                                </span>
                            </Link>
                        </div>

                        <div className="mb-7">
                            <h2 className="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                {title}
                            </h2>
                            <p className="mt-2 text-sm leading-6 text-slate-400">
                                {description}
                            </p>
                        </div>

                        <div className="rounded-3xl border border-white/[0.08] bg-[#0D1220]/90 p-5 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-7">
                            {children}
                        </div>

                        <OnboardingProgress />
                    </div>
                </main>
            </div>
        </div>
    );
}
