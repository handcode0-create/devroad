import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue sur DevRoad" />

            <main className="min-h-[100dvh] bg-[#0B0B0B] text-white">
                <div className="mx-auto flex min-h-[100dvh] w-full max-w-[430px] flex-col overflow-hidden bg-[#0B0B0B]">
                    <div className="flex h-[54px] shrink-0 items-center justify-between px-6 pt-2 text-[15px] font-semibold">
                        <span>9:41</span>
                        <span className="text-[11px]">▮▮▮ Wi‑Fi&nbsp;&nbsp;▣ 44</span>
                    </div>

                    <div className="relative flex flex-1 flex-col overflow-hidden px-5 pb-7">
                        <header className="flex shrink-0 items-center justify-between pt-5">
                            <Link href="/" className="flex min-h-11 items-center gap-3">
                                <img src="/icondevroad.png" alt="DevRoad" className="h-10 w-10 object-contain" />
                                <span className="text-[23px] font-extrabold tracking-[-0.045em]">
                                    Dev<span className="text-[#FF6A00]">Road</span>
                                </span>
                            </Link>

                            <span className="rounded-full border border-white/[0.10] bg-white/[0.035] px-5 py-3 text-[15px] font-semibold backdrop-blur-xl">
                                2025
                            </span>
                        </header>

                        <section className="relative mt-6 min-h-0 flex-1 overflow-hidden rounded-[30px] border border-white/[0.07] bg-[#0B0B0B] shadow-[0_25px_70px_rgba(0,0,0,.55)]">
                            <img
                                src="/devroad-splash-bg.webp"
                                alt=""
                                className="absolute inset-0 h-full w-full object-cover object-top"
                            />

                            <div className="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-[#0B0B0B]" />

                            <div className="absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t from-[#0B0B0B]/95 to-transparent" />
                        </section>

                        <section className="relative z-10 shrink-0 pt-7 text-center">
                            <h1 className="text-[clamp(2.8rem,11vw,3.65rem)] font-black leading-[.91] tracking-[-0.065em]">
                                Welcome to
                                <span className="block text-[#FF6A00]">DevRoad</span>
                            </h1>

                            <p className="mx-auto mt-5 max-w-[355px] text-[14px] leading-6 text-white/55">
                                Votre espace tout-en-un pour organiser,
                                planifier et réaliser vos projets plus facilement.
                            </p>
                        </section>

                        <div className="relative z-10 shrink-0 pt-7">
                            <Link
                                href={route('login')}
                                className="group flex h-[62px] w-full items-center justify-center gap-5 rounded-full border border-[#FF6A00] bg-[#0D0D0D] text-[16px] font-semibold text-white shadow-[0_0_30px_rgba(255,106,0,.12)] transition hover:bg-[#141414] focus:outline-none focus:ring-2 focus:ring-[#FF6A00]/50"
                            >
                                <span>Suivant</span>
                                <ArrowRight size={25} className="transition-transform group-hover:translate-x-1" />
                            </Link>

                            <div className="mt-6 flex justify-center gap-3" aria-label="Étape 1 sur 3">
                                <span className="h-2.5 w-7 rounded-full bg-[#FF6A00]" />
                                <span className="h-2.5 w-2.5 rounded-full bg-white/25" />
                                <span className="h-2.5 w-2.5 rounded-full bg-white/25" />
                            </div>

                            <div className="mx-auto mt-5 h-1 w-32 rounded-full bg-white/85" />
                        </div>
                    </div>
                </div>
            </main>
        </>
    );
}
