import { Head, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import LoadingSpinner from '@/Components/Ui/LoadingSpinner';

export default function AppSplash() {
    const [progress, setProgress] = useState(0);
    const redirected = useRef(false);

    useEffect(() => {
        const startedAt = Date.now();
        const duration = 1100;

        const interval = window.setInterval(() => {
            const elapsed = Date.now() - startedAt;
            setProgress(Math.min(100, Math.round((elapsed / duration) * 100)));

            if (elapsed >= duration && !redirected.current) {
                redirected.current = true;
                window.clearInterval(interval);
                router.visit(route('dashboard'), {
                    replace: true,
                    preserveScroll: false,
                    preserveState: false,
                });
            }
        }, 40);

        return () => window.clearInterval(interval);
    }, []);

    return (
        <>
            <Head title="DevRoad" />

            <main className="relative flex min-h-[100dvh] items-center justify-center overflow-hidden bg-[#08111F] px-6 text-white">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_42%,rgba(255,106,0,0.12),transparent_34%),radial-gradient(circle_at_50%_100%,rgba(255,106,0,0.05),transparent_45%)]" aria-hidden="true" />

                <div className="relative z-10 flex w-full max-w-[320px] flex-col items-center text-center">
                    <div className="relative flex h-24 w-24 items-center justify-center">
                        <div className="absolute inset-0 animate-ping rounded-[28px] bg-[#FF6A00]/10 motion-reduce:animate-none" />
                        <div className="relative flex h-20 w-20 items-center justify-center rounded-[24px] border border-[#FF6A00]/20 bg-[#0D1725] shadow-[0_0_55px_rgba(255,106,0,0.16)]">
                            <img src="/icondevroad.png" alt="DevRoad" className="h-12 w-12 object-contain" />
                        </div>
                    </div>

                    <h1 className="mt-7 text-[30px] font-extrabold tracking-[-0.055em]">
                        Dev<span className="text-[#FF6A00]">Road</span>
                    </h1>

                    <p className="mt-2 text-sm text-slate-500">
                        Préparation de ton espace développeur...
                    </p>

                    <div className="mt-8 w-full">
                        <div className="h-1 overflow-hidden rounded-full bg-white/[0.06]">
                            <div
                                className="h-full rounded-full bg-[#FF6A00] transition-[width] duration-75 ease-linear"
                                style={{ width: `${progress}%` }}
                            />
                        </div>

                        <div className="mt-3 flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                            <LoadingSpinner size={12} label="Chargement" />
                            <span>{progress}%</span>
                        </div>
                    </div>
                </div>
            </main>
        </>
    );
}
