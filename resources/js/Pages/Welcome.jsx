import { Head, Link } from '@inertiajs/react';
import { ArrowRight, CheckSquare, Lightbulb, Target, Users } from 'lucide-react';

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue sur DevRoad" />

            <main className="min-h-[100dvh] overflow-hidden bg-[#0B0B0B] text-white">
                <div className="relative min-h-[100dvh] bg-[radial-gradient(circle_at_75%_25%,rgba(255,106,0,.10),transparent_28%),radial-gradient(circle_at_15%_80%,rgba(255,106,0,.06),transparent_30%),linear-gradient(135deg,#0B0B0B_0%,#0D1016_52%,#08090C_100%)]">
                    <div className="pointer-events-none absolute inset-0 opacity-40 [background-image:linear-gradient(rgba(255,255,255,.018)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.018)_1px,transparent_1px)] [background-size:72px_72px]" />
                    <div className="pointer-events-none absolute -right-40 top-20 h-[520px] w-[520px] rounded-full bg-[#FF6A00]/[0.07] blur-[120px]" />

                    <div className="relative mx-auto flex min-h-[100dvh] w-full max-w-[1500px] flex-col px-5 pb-6 pt-6 sm:px-8 sm:pt-8 lg:px-12 xl:px-16">
                        <header className="flex items-center justify-between">
                            <Link href="/" className="flex min-h-11 items-center gap-3">
                                <img
                                    src="/icondevroad.png"
                                    alt="DevRoad"
                                    className="h-10 w-10 object-contain sm:h-11 sm:w-11"
                                />
                                <span className="text-xl font-extrabold tracking-[-0.04em] sm:text-2xl">
                                    Dev<span className="text-[#FF6A00]">Road</span>
                                </span>
                            </Link>

                            <span className="rounded-full border border-white/[0.09] bg-white/[0.035] px-4 py-2 text-sm font-semibold text-white/90 backdrop-blur-xl sm:px-5 sm:py-2.5">
                                2025
                            </span>
                        </header>

                        <section className="grid flex-1 items-center gap-10 py-10 lg:grid-cols-[.9fr_1.1fr] lg:gap-16 lg:py-12">
                            <div className="mx-auto w-full max-w-[620px]">
                                <div className="mb-7 inline-flex items-center gap-2.5 rounded-full border border-white/[0.07] bg-white/[0.035] px-4 py-2.5 text-sm font-medium text-white/90 shadow-[0_10px_30px_rgba(0,0,0,.18)] backdrop-blur-xl">
                                    <span className="h-2.5 w-2.5 rounded-full bg-[#FF6A00] shadow-[0_0_14px_rgba(255,106,0,.65)]" />
                                    Bienvenue
                                </div>

                                <h1 className="text-[clamp(3.2rem,7vw,6.5rem)] font-black leading-[.9] tracking-[-0.065em]">
                                    Welcome to
                                    <span className="block text-[#FF6A00]">DevRoad</span>
                                </h1>

                                <p className="mt-7 max-w-[580px] text-base leading-7 text-[#A5A9B4] sm:text-lg sm:leading-8 lg:text-xl">
                                    Votre espace tout-en-un pour organiser,
                                    planifier et réaliser vos projets plus
                                    facilement. Des idées claires, une meilleure
                                    productivité et un avenir plus structuré.
                                </p>

                                <div className="mt-8 grid grid-cols-2 gap-x-6 gap-y-5 sm:gap-x-10 sm:gap-y-6">
                                    <Feature icon={CheckSquare} label={<>Organisez<br />vos projets</>} tone="orange" />
                                    <Feature icon={Users} label={<>Collaborez<br />en équipe</>} tone="green" />
                                    <Feature icon={Lightbulb} label={<>Boostez<br />votre productivité</>} tone="purple" />
                                    <Feature icon={Target} label={<>Atteignez<br />vos objectifs</>} tone="pink" />
                                </div>

                                <Link
                                    href={route('login')}
                                    className="group mt-9 flex min-h-[62px] w-full items-center justify-center gap-5 rounded-full border border-white/[0.06] bg-white/[0.96] px-7 text-base font-bold text-[#0B0B0B] shadow-[0_14px_45px_rgba(0,0,0,.32)] transition hover:-translate-y-0.5 hover:bg-white sm:min-h-[68px] sm:text-lg"
                                >
                                    <span>Suivant</span>
                                    <ArrowRight size={22} className="transition-transform group-hover:translate-x-1" />
                                </Link>

                                <div className="mt-7 flex items-center gap-2">
                                    <span className="h-2.5 w-7 rounded-full bg-[#FF6A00] shadow-[0_0_14px_rgba(255,106,0,.55)]" />
                                    <span className="h-2.5 w-2.5 rounded-full bg-white/15" />
                                    <span className="h-2.5 w-2.5 rounded-full bg-white/15" />
                                </div>
                            </div>

                            <div className="relative mx-auto flex w-full max-w-[700px] items-center justify-center lg:min-h-[650px]">
                                <div className="absolute h-[72%] w-[62%] rounded-full bg-[#FF6A00]/[0.09] blur-[100px]" />

                                <div className="relative w-[min(100%,440px)] rotate-[-2deg] rounded-[42px] border border-white/[0.12] bg-[#161616] p-2.5 shadow-[0_45px_100px_rgba(0,0,0,.60)] sm:p-3">
                                    <div className="overflow-hidden rounded-[34px] border border-white/[0.08] bg-[#0F0F0F]">
                                        <div className="relative aspect-[9/16]">
                                            <div className="absolute left-1/2 top-3 z-10 h-7 w-28 -translate-x-1/2 rounded-full bg-black shadow-lg" />

                                            <div className="flex h-full flex-col bg-[radial-gradient(circle_at_50%_25%,rgba(255,106,0,.13),transparent_27%),#0D0D0D] px-7 pb-7 pt-12 sm:px-9">
                                                <div className="flex items-center justify-between text-[10px] text-white/45">
                                                    <span>10:41</span>
                                                    <span>● ● ▰</span>
                                                </div>

                                                <div className="flex flex-1 flex-col items-center justify-center text-center">
                                                    <img
                                                        src="/icondevroad.png"
                                                        alt=""
                                                        className="mb-7 h-20 w-20 object-contain drop-shadow-[0_0_30px_rgba(255,106,0,.22)]"
                                                    />

                                                    <h2 className="text-3xl font-black tracking-[-0.045em]">
                                                        Welcome to
                                                        <span className="block text-[#FF6A00]">DevRoad</span>
                                                    </h2>

                                                    <p className="mt-4 max-w-[270px] text-xs leading-5 text-white/50">
                                                        Votre allié pour transformer vos idées
                                                        en projets concrets.
                                                    </p>

                                                    <div className="mt-8 w-full rounded-full border border-white/[0.10] bg-white/[0.025] px-5 py-3 text-xs text-white/45">
                                                        Votre route commence ici
                                                    </div>
                                                </div>

                                                <Link
                                                    href={route('login')}
                                                    className="flex h-12 items-center justify-center rounded-full border border-[#FF6A00]/50 bg-[#111] text-sm font-semibold text-white shadow-[0_0_30px_rgba(255,106,0,.10)]"
                                                >
                                                    Suivant
                                                    <ArrowRight size={17} className="ml-2 text-[#FF6A00]" />
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="absolute -bottom-2 -left-2 hidden w-[220px] rotate-[5deg] rounded-3xl border border-white/[0.08] bg-[#121212]/90 p-5 shadow-2xl backdrop-blur-xl sm:block lg:left-0">
                                    <p className="text-[10px] font-semibold uppercase tracking-[.18em] text-white/35">
                                        DevRoad
                                    </p>
                                    <p className="mt-2 text-sm font-semibold leading-5 text-white/80">
                                        Construisez. Apprenez. Avancez.
                                    </p>
                                    <div className="mt-4 h-1 w-12 rounded-full bg-[#FF6A00]" />
                                </div>
                            </div>
                        </section>

                        <footer className="flex items-center justify-between border-t border-white/[0.06] pt-5 text-xs text-white/35">
                            <span>© DevRoad</span>
                            <span className="hidden sm:inline">La route vers vos projets.</span>
                        </footer>
                    </div>
                </div>
            </main>
        </>
    );
}

function Feature({ icon: Icon, label, tone }) {
    const tones = {
        orange: 'border-orange-500/10 bg-orange-500/[0.08] text-[#FF6A00]',
        green: 'border-emerald-400/10 bg-emerald-400/[0.08] text-emerald-400',
        purple: 'border-violet-400/10 bg-violet-400/[0.08] text-violet-400',
        pink: 'border-rose-400/10 bg-rose-400/[0.08] text-rose-400',
    };

    return (
        <div className="flex min-w-0 items-center gap-3">
            <span className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border ${tones[tone]}`}>
                <Icon size={21} strokeWidth={2} />
            </span>
            <span className="text-sm font-medium leading-5 text-white/85 sm:text-base">
                {label}
            </span>
        </div>
    );
}
