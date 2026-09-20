import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

function OnboardingProgress() {
    return (
        <div className="mt-6 flex items-center justify-center gap-3" aria-label="Étape 1 sur 3">
            <span className="h-2.5 w-8 rounded-full bg-[#FF6A00]" />
            <span className="h-2.5 w-2.5 rounded-full bg-white/25" />
            <span className="h-2.5 w-2.5 rounded-full bg-white/25" />
        </div>
    );
}

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue sur DevRoad" />

            <main className="relative min-h-[100dvh] overflow-hidden bg-[#0B0B0B] text-white">
                <img
                    src="https://i.ibb.co/nN1K8BBp/64-FDEBF3-54-EF-4-C0-C-AE1-D-2-F66195-B8375.png"
                    alt=""
                    aria-hidden="true"
                    className="absolute inset-0 h-full w-full object-cover object-top"
                />

                <div
                    className="absolute inset-0 bg-gradient-to-b from-[#0B0B0B]/10 via-[#0B0B0B]/10 via-[42%] to-[#0B0B0B]/98"
                    aria-hidden="true"
                />
                <div
                    className="absolute inset-x-0 bottom-0 h-[62%] bg-gradient-to-t from-[#0B0B0B] via-[#0B0B0B]/85 to-transparent"
                    aria-hidden="true"
                />

                <div className="relative z-10 mx-auto flex min-h-[100dvh] w-full max-w-[430px] flex-col items-center px-5 text-center">
                    <header className="flex w-full shrink-0 flex-col items-center pt-[9vh]">
                        <Link href="/" className="flex flex-col items-center gap-2">
                            <img
                                src="/icondevroad.png"
                                alt="DevRoad"
                                className="h-14 w-14 object-contain"
                            />
                            <span className="text-[30px] font-extrabold tracking-[-0.055em]">
                                Dev<span className="text-[#FF6A00]">Road</span>
                            </span>
                        </Link>
                    </header>

                    <section className="mt-auto w-full shrink-0 pb-7 pt-[24vh]">
                        <div className="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/30 px-5 py-3 text-[15px] font-medium backdrop-blur-xl">
                            <span className="h-2.5 w-2.5 rounded-full bg-[#FF6A00] shadow-[0_0_12px_rgba(255,106,0,.75)]" />
                            Bienvenue
                        </div>

                        <h1 className="text-[clamp(3rem,13vw,3.7rem)] font-black leading-[.92] tracking-[-0.07em]">
                            Welcome to
                            <span className="block text-[#FF6A00]">DevRoad</span>
                        </h1>

                        <p className="mx-auto mt-5 max-w-[370px] text-[15px] leading-6 text-white/65">
                            Votre espace tout-en-un pour organiser, planifier et
                            réaliser vos projets plus facilement.
                        </p>

                        <Link
                            href={route('login')}
                            className="group mx-auto mt-7 flex h-[62px] w-full items-center justify-center gap-5 rounded-full bg-[#FF6A00] text-[16px] font-bold text-white shadow-[0_12px_40px_rgba(0,0,0,.45),0_0_30px_rgba(255,106,0,.18)] transition hover:bg-[#ff7514] focus:outline-none focus:ring-2 focus:ring-white/60"
                        >
                            <span>Suivant</span>
                            <ArrowRight size={25} className="transition-transform group-hover:translate-x-1" />
                        </Link>

                        <OnboardingProgress />

                        <div className="mx-auto mt-5 h-1 w-32 rounded-full bg-white/85" />

                        <a
                            href="https://handcode.site"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="mt-4 inline-flex items-center justify-center text-[11px] font-medium tracking-wide text-white/35 transition hover:text-white/70"
                        >
                            Propulsed by <span className="ml-1 text-white/55">handCode</span>
                        </a>
                    </section>
                </div>
            </main>
        </>
    );
}
