import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue sur DevRoad" />

            <main className="relative min-h-[100dvh] overflow-hidden bg-[#0B0B0B] text-white">
                {/* Full-screen splash artwork */}
                <img
                    src="/devroad-splash-bg.webp"
                    alt=""
                    aria-hidden="true"
                    className="absolute inset-0 h-full w-full object-cover object-top"
                />

                {/* Dark cinematic overlay: preserves the artwork while making the content readable */}
                <div
                    className="absolute inset-0 bg-gradient-to-b from-[#0B0B0B]/10 via-[#0B0B0B]/10 via-[42%] to-[#0B0B0B]/98"
                    aria-hidden="true"
                />
                <div
                    className="absolute inset-x-0 bottom-0 h-[62%] bg-gradient-to-t from-[#0B0B0B] via-[#0B0B0B]/85 to-transparent"
                    aria-hidden="true"
                />

                <div className="relative z-10 mx-auto flex min-h-[100dvh] w-full max-w-[430px] flex-col px-5">
                    {/* iPhone-style top area */}
                    <div className="flex h-[54px] shrink-0 items-center justify-between px-1 pt-2 text-[15px] font-semibold">
                        <span>9:41</span>
                        <span className="text-[11px]">▮▮▮ Wi‑Fi&nbsp;&nbsp;▣ 44</span>
                    </div>

                    {/* Header stays directly over the artwork */}
                    <header className="flex shrink-0 items-center justify-between pt-5">
                        <Link href="/" className="flex min-h-11 items-center gap-3">
                            <img
                                src="/icondevroad.png"
                                alt="DevRoad"
                                className="h-10 w-10 object-contain"
                            />
                            <span className="text-[23px] font-extrabold tracking-[-0.045em]">
                                Dev<span className="text-[#FF6A00]">Road</span>
                            </span>
                        </Link>

                        <span className="rounded-full border border-white/15 bg-black/25 px-5 py-3 text-[15px] font-semibold shadow-lg backdrop-blur-xl">
                            2025
                        </span>
                    </header>

                    {/* Content is intentionally laid over the bottom of the image */}
                    <section className="mt-auto shrink-0 pb-7 pt-[35vh]">
                        <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/30 px-5 py-3 text-[15px] font-medium backdrop-blur-xl">
                            <span className="h-2.5 w-2.5 rounded-full bg-[#FF6A00] shadow-[0_0_12px_rgba(255,106,0,.75)]" />
                            Bienvenue
                        </div>

                        <h1 className="text-[clamp(3rem,12vw,3.7rem)] font-black leading-[.9] tracking-[-0.07em]">
                            Welcome to
                            <span className="block text-[#FF6A00]">DevRoad</span>
                        </h1>

                        <p className="mt-5 max-w-[370px] text-[15px] leading-6 text-white/65">
                            Votre espace tout-en-un pour organiser, planifier et
                            réaliser vos projets plus facilement.
                        </p>

                        <Link
                            href={route('login')}
                            className="group mt-7 flex h-[62px] w-full items-center justify-center gap-5 rounded-full bg-[#FF6A00] text-[16px] font-bold text-white shadow-[0_12px_40px_rgba(0,0,0,.45),0_0_30px_rgba(255,106,0,.18)] transition hover:bg-[#ff7514] focus:outline-none focus:ring-2 focus:ring-white/60"
                        >
                            <span>Suivant</span>
                            <ArrowRight size={25} className="transition-transform group-hover:translate-x-1" />
                        </Link>

                        <div className="mt-6 flex items-center justify-center gap-3" aria-label="Étape 1 sur 3">
                            <span className="h-2.5 w-7 rounded-full bg-[#FF6A00]" />
                            <span className="h-2.5 w-2.5 rounded-full bg-white/25" />
                            <span className="h-2.5 w-2.5 rounded-full bg-white/25" />
                        </div>

                        <div className="mx-auto mt-5 h-1 w-32 rounded-full bg-white/85" />
                    </section>
                </div>
            </main>
        </>
    );
}
