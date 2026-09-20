import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import OnboardingSwipe from '@/Components/OnboardingSwipe';

function OnboardingProgress() {
    return (
        <ol
            aria-label="Étape 1 sur 3"
            className="mt-6 flex items-center justify-center gap-2"
        >
            {[0, 1, 2].map((item) => (
                <li
                    key={item}
                    aria-current={item === 0 ? 'step' : undefined}
                    className={
                        'h-2 rounded-full transition-all duration-300 ' +
                        (item === 0 ? 'w-6 bg-[#FF6A00]' : 'w-2 bg-white/25')
                    }
                />
            ))}
        </ol>
    );
}

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue sur DevRoad" />

            <OnboardingSwipe step={0} className="min-h-[100dvh]">
                <main className="relative min-h-[100dvh] overflow-hidden bg-[#0B0B0B] text-white">
                    <img
                        src="https://i.ibb.co/nN1K8BBp/64-FDEBF3-54-EF-4-C0-C-AE1-D-2-F66195-B8375.png"
                        alt=""
                        aria-hidden="true"
                        className="absolute inset-0 h-full w-full object-cover object-top"
                    />

                    <div className="absolute inset-0 bg-[#05070A]/25" aria-hidden="true" />
                    <div
                        className="absolute inset-0 bg-gradient-to-b from-[#05070A]/10 via-transparent via-[38%] to-[#05070A]/98"
                        aria-hidden="true"
                    />
                    <div
                        className="absolute inset-x-0 bottom-0 h-[58%] bg-gradient-to-t from-[#05070A] via-[#05070A]/80 to-transparent"
                        aria-hidden="true"
                    />

                    <div className="relative z-10 mx-auto flex min-h-[100dvh] w-full max-w-[430px] flex-col items-center px-5 text-center">
                        <header className="flex w-full shrink-0 flex-col items-center pt-[8vh]">
                            <Link href="/" className="flex flex-col items-center gap-2">
                                <img
                                    src="/icondevroad.png"
                                    alt="DevRoad"
                                    className="h-14 w-14 object-contain drop-shadow-[0_0_24px_rgba(255,106,0,.2)]"
                                />
                                <span className="text-[30px] font-extrabold tracking-[-0.055em]">
                                    Dev<span className="text-[#FF6A00]">Road</span>
                                </span>
                            </Link>

                            <div className="mt-5 flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.28em] text-white/40">
                                <span>Focus</span>
                                <span className="text-[#FF6A00]">•</span>
                                <span>Plan</span>
                                <span className="text-[#FF6A00]">•</span>
                                <span>Build</span>
                            </div>
                        </header>

                        <section className="mt-auto w-full shrink-0 pb-7 pt-[24vh]">
                            <div className="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/35 px-5 py-3 text-[15px] font-medium backdrop-blur-xl">
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
            </OnboardingSwipe>
        </>
    );
}
