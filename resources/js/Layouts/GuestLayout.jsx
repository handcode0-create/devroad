import ApplicationLogo from '@/Components/ApplicationLogo';
import OnboardingSwipe from '@/Components/OnboardingSwipe';
import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, ShieldCheck } from 'lucide-react';

function OnboardingProgress() {
    const { url } = usePage();
    const step = url.startsWith('/register') ? 3 : 2;

    return (
        <div className="flex items-center justify-center gap-2.5" aria-label={`Étape ${step} sur 3`}>
            {[1, 2, 3].map((item) => (
                <span
                    key={item}
                    className={[
                        'h-2 rounded-full transition-all duration-300',
                        item === step
                            ? 'w-8 bg-[#FF6A00] shadow-[0_0_14px_rgba(255,106,0,.45)]'
                            : 'w-2 bg-white/20',
                    ].join(' ')}
                />
            ))}
        </div>
    );
}

export default function GuestLayout({ children, title, description }) {
    const { url } = usePage();
    const isRegister = url.startsWith('/register');
    const step = isRegister ? 2 : 1;

    const previous = isRegister ? route('login') : '/';
    const next = isRegister ? null : route('register');

    return (
        <OnboardingSwipe step={step} className="min-h-[100dvh]">
            <div className="relative min-h-[100dvh] overflow-hidden bg-[#07090D] text-white">
                <img
                    src="https://i.ibb.co/nN1K8BBp/64-FDEBF3-54-EF-4-C0-C-AE1-D-2-F66195-B8375.png"
                    alt=""
                    aria-hidden="true"
                    className="absolute inset-0 h-full w-full object-cover object-center"
                />

                <div className="absolute inset-0 bg-[#05070A]/60" aria-hidden="true" />
                <div
                    className="absolute inset-0 bg-[radial-gradient(circle_at_50%_20%,rgba(255,106,0,.18),transparent_34%),linear-gradient(to_bottom,rgba(5,7,10,.15),rgba(5,7,10,.97)_90%)]"
                    aria-hidden="true"
                />

                <div className="relative z-10 mx-auto flex min-h-[100dvh] w-full max-w-[430px] flex-col px-5 py-6">
                    <header className="flex items-center justify-center">
                        <Link href="/" className="inline-flex flex-col items-center gap-1.5">
                            <ApplicationLogo className="h-10 w-10 object-contain drop-shadow-[0_0_18px_rgba(255,106,0,.25)]" />
                            <span className="text-xl font-extrabold tracking-[-0.04em]">
                                Dev<span className="text-[#FF6A00]">Road</span>
                            </span>
                        </Link>
                    </header>

                    <main className="flex flex-1 items-end justify-center pb-5 pt-8">
                        <div className="w-full">
                            <div className="mb-4 flex items-center justify-between px-1">
                                <Link
                                    href={previous}
                                    className="inline-flex items-center gap-1.5 text-xs font-medium text-white/45 transition hover:text-white"
                                >
                                    <ArrowLeft className="h-3.5 w-3.5" />
                                    Retour
                                </Link>

                                {next ? (
                                    <Link
                                        href={next}
                                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-[#FF8A3D] transition hover:text-[#FF6A00]"
                                    >
                                        Suivant
                                        <ArrowRight className="h-3.5 w-3.5" />
                                    </Link>
                                ) : (
                                    <span className="text-xs font-semibold text-white/35">Dernière étape</span>
                                )}
                            </div>

                            <div className="overflow-hidden rounded-[30px] border border-white/[0.16] bg-[#0A0D12]/72 shadow-[0_30px_100px_rgba(0,0,0,.6)] backdrop-blur-2xl">
                                <div className="h-1 w-full bg-white/[0.05]">
                                    <div
                                        className={`h-full bg-[#FF6A00] shadow-[0_0_18px_rgba(255,106,0,.65)] transition-all duration-500 ${isRegister ? 'w-full' : 'w-2/3'}`}
                                    />
                                </div>

                                <div className="p-5 sm:p-7">
                                    <div className="mb-6">
                                        <div className="mb-4 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#FF8A3D]">
                                            <ShieldCheck className="h-4 w-4" />
                                            {isRegister ? 'Créer un compte' : 'Bienvenue 👋'}
                                        </div>

                                        <h1 className="text-[29px] font-black tracking-[-0.05em] text-white sm:text-[32px]">
                                            {title}
                                        </h1>
                                        <p className="mt-2 text-sm leading-6 text-white/50">
                                            {description}
                                        </p>
                                    </div>

                                    {children}
                                </div>

                                <div className="border-t border-white/[0.08] px-5 py-4 sm:px-7">
                                    <OnboardingProgress />
                                </div>
                            </div>

                            <a
                                href="https://handcode.site"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-4 flex items-center justify-center text-[11px] font-medium tracking-wide text-white/30 transition hover:text-white/65"
                            >
                                Propulsed by <span className="ml-1 text-white/50">handCode</span>
                            </a>
                        </div>
                    </main>
                </div>
            </div>
        </OnboardingSwipe>
    );
}
