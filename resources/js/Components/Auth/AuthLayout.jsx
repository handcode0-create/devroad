import { Link, router } from '@inertiajs/react';
import BrandPanel, { Brand } from '@/Components/Auth/BrandPanel';
import OnboardingSwipe from '@/Components/OnboardingSwipe';
import LoadingOverlay from '@/Components/Ui/LoadingOverlay';
import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { gsap } from 'gsap';

// Mise en page commune des écrans d'authentification.
// Desktop : panneau de marque (5/11) + formulaire (6/11).
// Mobile : formulaire d'abord, en-tête de marque compact.
//
// swipeStep (facultatif) : position de l'écran dans le parcours d'accueil
// (0 = /, 1 = /login, 2 = /register). Il active le balayage entre ces écrans
// (OnboardingSwipe) et les trois points d'étape sur mobile.
export default function AuthLayout({ title, subtitle, footer, swipeStep, children }) {
    const [navigating, setNavigating] = useState(false);
    const contentRef = useRef(null);

    useLayoutEffect(() => {
        const root = contentRef.current;

        if (!root || typeof window === 'undefined') return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const targets = Array.from(root.querySelectorAll('[data-auth-animate]'));
        if (!targets.length) return;

        const context = gsap.context(() => {
            gsap.fromTo(
                targets,
                { autoAlpha: 0, y: 12 },
                {
                    autoAlpha: 1,
                    y: 0,
                    duration: 0.48,
                    stagger: 0.045,
                    ease: 'power3.out',
                    clearProps: 'transform,opacity,visibility',
                },
            );
        }, root);

        return () => context.revert();
    }, []);

    useEffect(() => {
        const removeStartListener = router.on('start', () => setNavigating(true));
        const removeFinishListener = router.on('finish', () => setNavigating(false));

        return () => {
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    const layout = (
        <div className="min-h-dvh bg-[#08111F] font-sans text-white lg:grid lg:grid-cols-[5fr_6fr]">
            <BrandPanel />

            <div className="flex min-h-dvh flex-col px-5 pb-[max(1.5rem,env(safe-area-inset-bottom))] pt-[max(1.25rem,env(safe-area-inset-top))] sm:px-10 lg:px-16">
                <header className="relative flex items-center justify-between">
                    <Brand className="lg:hidden" />

                    {swipeStep !== undefined && (
                        <ol
                            aria-label={`Étape ${swipeStep + 1} sur 3`}
                            className="absolute left-1/2 flex -translate-x-1/2 items-center gap-2 lg:hidden"
                        >
                            {[0, 1, 2].map((item) => (
                                <li
                                    key={item}
                                    aria-current={item === swipeStep ? 'step' : undefined}
                                    className={`h-2 rounded-full transition-all duration-300 ${
                                        item === swipeStep ? 'w-6 bg-[#FF6A00]' : 'w-2 bg-white/25'
                                    }`}
                                />
                            ))}
                        </ol>
                    )}

                    <Link
                        href="/"
                        className="ml-auto rounded-lg px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6A00]/60"
                    >
                        Accueil
                    </Link>
                </header>

                <main ref={contentRef} className="mx-auto flex w-full max-w-[27rem] flex-1 flex-col justify-center py-8 lg:py-14">
                    <h1 data-auth-animate className="font-display text-[2rem] font-extrabold leading-[1.05] tracking-[-0.03em] sm:text-[2.5rem]">
                        {title}
                    </h1>

                    {subtitle && <p data-auth-animate className="mt-3 text-base leading-7 text-slate-400">{subtitle}</p>}

                    <div data-auth-animate className="mt-9">{children}</div>

                    {footer && <p data-auth-animate className="mt-8 text-sm text-slate-400">{footer}</p>}
                </main>
            </div>
        </div>
    );

    return (
        <>
            <LoadingOverlay visible={navigating} label="Chargement..." />
            {swipeStep === undefined ? (
        layout
    ) : (
        <OnboardingSwipe step={swipeStep} className="min-h-dvh">
            {layout}
        </OnboardingSwipe>
            )}
        </>
    );
}

// Lien d'action dans une phrase du pied de page (contraste orange sur fond sombre : ≈ 6,6:1).
export function AuthLink({ href, children, ...props }) {
    return (
        <Link
            href={href}
            className="rounded font-semibold text-[#FF6A00] underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6A00]/60"
            {...props}
        >
            {children}
        </Link>
    );
}
