import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck } from 'lucide-react';

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
                        item === step ? 'w-8 bg-[#FF6A00] shadow-[0_0_14px_rgba(255,106,0,.45)]' : 'w-2 bg-white/20',
                    ].join(' ')}
                />
            ))}
        </div>
    );
}

export default function GuestLayout({ children, title, description }) {
    const { url } = usePage();
    const isRegister = url.startsWith('/register');
    const step = isRegister ? 3 : 2;

    return (
        <div className="relative min-h-[100dvh] overflow-hidden bg-[#07090D] text-white">
            <img
                src="https://i.ibb.co/nN1K8BBp/64-FDEBF3-54-EF-4-C0-C-AE1-D-2-F66195-B8375.png"
                alt=""
                aria-hidden="true"
                className="absolute inset-0 h-full w-full object-cover object-center"
            />

            <div className="absolute inset-0 bg-[#05070A]/70" aria-hidden="true" />
            <div
                className="absolute inset-0 bg-[radial-gradient(circle_at_50%_15%,rgba(255,106,0,.18),transparent_32%),linear-gradient(to_bottom,rgba(5,7,10,.25),rgba(5,7,10,.96)_88%)]"
                aria-hidden="true"
            />

            <div className="relative z-10 mx-auto flex min-h-[100dvh] w-full max-w-[1180px] flex-col px-5 py-6 sm:px-8 lg:px-10">
                <header className="flex items-center justify-between">
                    <Link href="/" className="group inline-flex items-center gap-2.5">
                        <ApplicationLogo className="h-9 w-9 object-contain drop-shadow-[0_0_18px_rgba(255,106,0,.25)]" />
                        <span className="text-xl font-extrabold tracking-[-0.04em]">
                            Dev<span className="text-[#FF6A00]">Road</span>
                        </span>
                    </Link>

                    <span className="rounded-full border border-white/10 bg-black/25 px-4 py-2 text-xs font-semibold text-white/70 backdrop-blur-xl">
                        2025
                    </span>
                </header>

                <main className="flex flex-1 items-center justify-center py-8 sm:py-12">
                    <div className="w-full max-w-[470px]">
                        <div className="mb-5 flex items-center justify-between px-1">
                            <Link
                                href="/"
                                className="inline-flex items-center gap-2 text-xs font-medium text-white/50 transition hover:text-white"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Retour
                            </Link>

                            <span className="text-xs font-semibold text-white/45">
                                Étape {step} sur 3
                            </span>
                        </div>

                        <div className="overflow-hidden rounded-[30px] border border-white/[0.11] bg-[#0A0D12]/80 shadow-[0_30px_100px_rgba(0,0,0,.55)] backdrop-blur-2xl">
                            <div className="h-1 w-full bg-white/[0.04]">
                                <div
                                    className={`h-full bg-[#FF6A00] shadow-[0_0_18px_rgba(255,106,0,.65)] transition-all duration-500 ${isRegister ? 'w-full' : 'w-2/3'}`}
                                />
                            </div>

                            <div className="p-6 sm:p-8">
                                <div className="mb-7">
                                    <div className="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl border border-[#FF6A00]/20 bg-[#FF6A00]/10 text-[#FF8A3D] shadow-[0_0_25px_rgba(255,106,0,.08)]">
                                        <ShieldCheck className="h-5 w-5" />
                                    </div>

                                    <h1 className="text-[28px] font-black tracking-[-0.045em] text-white sm:text-[32px]">
                                        {title}
                                    </h1>
                                    <p className="mt-2.5 max-w-[390px] text-sm leading-6 text-white/50">
                                        {description}
                                    </p>
                                </div>

                                {children}
                            </div>

                            <div className="border-t border-white/[0.07] px-6 py-5 sm:px-8">
                                <OnboardingProgress />
                            </div>
                        </div>

                        <p className="mt-5 text-center text-[11px] leading-5 text-white/30">
                            Tes données sont protégées et utilisées uniquement pour ton expérience DevRoad.
                        </p>
                    </div>
                </main>
            </div>
        </div>
    );
}
