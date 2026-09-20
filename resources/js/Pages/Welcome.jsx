import { Head, Link } from '@inertiajs/react';
import { ArrowRight, CheckSquare, Lightbulb, Target, Users } from 'lucide-react';

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue sur DevRoad" />

            <main className="min-h-[100dvh] bg-[#0B0B0B] text-white">
                <div className="mx-auto min-h-[100dvh] w-full max-w-[430px] overflow-hidden bg-[#0B0B0B]">
                    <div className="relative aspect-[9/16] min-h-[100dvh] max-h-none overflow-hidden bg-[radial-gradient(circle_at_70%_30%,rgba(255,106,0,.10),transparent_25%),linear-gradient(145deg,#0B0B0B,#101217_58%,#08090C)]">
                        <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(115deg,transparent_0%,rgba(255,255,255,.025)_42%,transparent_75%)]" />

                        {/* iOS-style status bar */}
                        <div className="relative z-20 flex h-[52px] items-center justify-between px-6 pt-2 text-[15px] font-semibold">
                            <span>9:41</span>
                            <div className="flex items-center gap-1.5 text-xs">
                                <span className="tracking-[-.18em]">▮▮▮</span>
                                <span>Wi‑Fi</span>
                                <span className="rounded-[4px] border border-white/30 px-1 py-[1px] text-[9px]">44</span>
                            </div>
                        </div>

                        <div className="relative z-10 flex h-[calc(100%-52px)] flex-col px-5 pb-7">
                            {/* Brand header */}
                            <header className="flex items-center justify-between pt-5">
                                <Link href="/" className="flex items-center gap-3">
                                    <img
                                        src="/icondevroad.png"
                                        alt="DevRoad"
                                        className="h-10 w-10 object-contain"
                                    />
                                    <span className="text-[23px] font-extrabold tracking-[-0.045em]">
                                        Dev<span className="text-[#FF6A00]">Road</span>
                                    </span>
                                </Link>

                                <span className="rounded-full border border-white/[0.10] bg-white/[0.035] px-5 py-3 text-[15px] font-semibold shadow-[0_8px_30px_rgba(0,0,0,.18)] backdrop-blur-xl">
                                    2025
                                </span>
                            </header>

                            {/* Image / visual area — deliberately close to the reference composition */}
                            <div className="relative mt-7 h-[34%] min-h-[250px] overflow-hidden rounded-[30px] border border-white/[0.08] bg-[#111] shadow-[0_25px_60px_rgba(0,0,0,.35)]">
                                <div className="absolute inset-0 grid grid-cols-2 gap-1.5 p-1.5">
                                    <VisualTile className="row-span-2" variant="laptop" />
                                    <VisualTile variant="notebook" />
                                    <VisualTile variant="phone" />
                                    <VisualTile variant="headphones" />
                                </div>

                                <div className="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#0B0B0B] to-transparent" />

                                <div className="absolute bottom-5 left-5 flex items-center gap-2 rounded-full border border-white/[0.12] bg-black/55 px-3 py-2 backdrop-blur-xl">
                                    <img src="/icondevroad.png" alt="" className="h-5 w-5 object-contain" />
                                    <span className="text-[10px] font-semibold text-white/75">Build • Learn • Ship</span>
                                </div>
                            </div>

                            {/* Welcome content */}
                            <section className="flex flex-1 flex-col justify-center pt-7 text-center">
                                <div className="mx-auto inline-flex items-center gap-2 rounded-full border border-white/[0.08] bg-white/[0.035] px-4 py-2 text-xs font-medium text-white/80 backdrop-blur-xl">
                                    <span className="h-2 w-2 rounded-full bg-[#FF6A00] shadow-[0_0_12px_rgba(255,106,0,.75)]" />
                                    Bienvenue
                                </div>

                                <h1 className="mt-5 text-[clamp(2.45rem,10vw,3.5rem)] font-black leading-[.92] tracking-[-0.065em]">
                                    Welcome to
                                    <span className="block text-[#FF6A00]">DevRoad</span>
                                </h1>

                                <p className="mx-auto mt-4 max-w-[350px] text-[13px] leading-5 text-white/50">
                                    Votre espace tout-en-un pour organiser,
                                    planifier et réaliser vos projets plus
                                    facilement.
                                </p>

                                <div className="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-left">
                                    <Feature icon={CheckSquare} text="Organisez vos projets" tone="orange" />
                                    <Feature icon={Users} text="Collaborez en équipe" tone="green" />
                                    <Feature icon={Lightbulb} text="Boostez votre productivité" tone="purple" />
                                    <Feature icon={Target} text="Atteignez vos objectifs" tone="pink" />
                                </div>
                            </section>

                            <div className="pt-4">
                                <Link
                                    href={route('login')}
                                    className="group flex h-[58px] w-full items-center justify-center gap-5 rounded-full border border-[#FF6A00]/45 bg-[#111] text-base font-bold text-white shadow-[0_0_32px_rgba(255,106,0,.12)] transition hover:border-[#FF6A00] hover:bg-[#161616]"
                                >
                                    <span>Suivant</span>
                                    <ArrowRight size={22} className="text-[#FF6A00] transition-transform group-hover:translate-x-1" />
                                </Link>

                                <div className="mt-5 flex justify-center gap-2">
                                    <span className="h-2 w-7 rounded-full bg-[#FF6A00] shadow-[0_0_12px_rgba(255,106,0,.5)]" />
                                    <span className="h-2 w-2 rounded-full bg-white/20" />
                                    <span className="h-2 w-2 rounded-full bg-white/20" />
                                </div>

                                <div className="mx-auto mt-5 h-1 w-32 rounded-full bg-white/80" />
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </>
    );
}

function VisualTile({ variant, className = '' }) {
    const content = {
        laptop: (
            <>
                <div className="absolute left-[-15%] top-[12%] h-[42%] w-[120%] -rotate-[13deg] rounded-[18px] border border-white/10 bg-[#222] shadow-2xl">
                    <div className="absolute inset-[10%] rounded-xl bg-[#090909] shadow-inner">
                        <div className="absolute inset-x-5 top-5 h-2 rounded bg-[#FF6A00]/70" />
                        <div className="absolute left-5 top-12 h-1.5 w-24 rounded bg-white/15" />
                        <div className="absolute left-5 top-[70px] h-1.5 w-32 rounded bg-white/10" />
                    </div>
                </div>
                <div className="absolute bottom-5 left-5 text-[10px] font-semibold text-white/40">IDEAS → BUILD</div>
            </>
        ),
        notebook: (
            <>
                <div className="absolute inset-5 rotate-[8deg] rounded-2xl bg-[#181818] p-4 shadow-2xl">
                    <p className="text-[12px] font-semibold text-white/70">Ideas</p>
                    <p className="mt-1 text-[12px] text-white/45">Plan</p>
                    <p className="text-[12px] text-white/45">Build</p>
                    <p className="text-[12px] font-bold text-[#FF6A00]">Grow</p>
                    <span className="absolute bottom-3 right-3 h-1 w-7 rounded bg-[#FF6A00]" />
                </div>
            </>
        ),
        phone: (
            <>
                <div className="absolute left-1/2 top-1/2 h-[82%] w-[44%] -translate-x-1/2 -translate-y-1/2 rotate-[10deg] rounded-[20px] border border-white/15 bg-[#171717] shadow-2xl">
                    <div className="absolute left-1/2 top-2 h-4 w-12 -translate-x-1/2 rounded-full bg-black" />
                    <div className="absolute inset-x-3 bottom-3 top-8 rounded-[14px] bg-[radial-gradient(circle_at_50%_35%,rgba(255,106,0,.22),transparent_30%),#0D0D0D]" />
                    <div className="absolute bottom-7 left-1/2 h-1 w-12 -translate-x-1/2 rounded bg-white/30" />
                </div>
            </>
        ),
        headphones: (
            <>
                <div className="absolute left-1/2 top-1/2 h-20 w-20 -translate-x-1/2 -translate-y-1/2 rounded-full border-[11px] border-[#252525] shadow-[0_0_25px_rgba(255,106,0,.18)]" />
                <div className="absolute left-1/2 top-1/2 h-8 w-8 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#FF6A00]/70" />
            </>
        ),
    };

    return (
        <div className={`relative overflow-hidden rounded-[24px] bg-[#151515] ${className}`}>
            {content[variant]}
        </div>
    );
}

function Feature({ icon: Icon, text, tone }) {
    const tones = {
        orange: 'border-orange-500/10 bg-orange-500/[0.08] text-[#FF6A00]',
        green: 'border-emerald-400/10 bg-emerald-400/[0.08] text-emerald-400',
        purple: 'border-violet-400/10 bg-violet-400/[0.08] text-violet-400',
        pink: 'border-rose-400/10 bg-rose-400/[0.08] text-rose-400',
    };

    return (
        <div className="flex min-w-0 items-center gap-2.5">
            <span className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border ${tones[tone]}`}>
                <Icon size={18} strokeWidth={2} />
            </span>
            <span className="text-[11px] font-medium leading-4 text-white/80">
                {text}
            </span>
        </div>
    );
}
