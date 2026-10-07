import { Link, usePage } from '@inertiajs/react';
import useNavigationLoading from '@/hooks/useNavigationLoading';
import Sidebar from '@/Components/Navigation/Sidebar';
import BottomNav from '@/Components/Navigation/BottomNav';
import DesktopTopBar from '@/Components/Navigation/DesktopTopBar';
import ToastViewport from '@/Components/Ui/ToastViewport';
import LoadingOverlay from '@/Components/Ui/LoadingOverlay';
import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { gsap } from 'gsap';
import AppearanceSheet from '@/Components/Ui/AppearanceSheet';
import { applyTheme, isLightTheme, useResolvedTheme } from '@/theme';
import { reducedMotionPreferred } from '@/theme';

export default function AppLayout({ children, mobileHeader = true }) {
    const { auth } = usePage().props;
    const loading = useNavigationLoading();
    const contentRef = useRef(null);
    const currentUrl = usePage().url;
    const currentPath = currentUrl.split('?')[0];
    const user = auth?.user;
    const themePreference = user?.theme ?? (user?.light_mode ? 'clair' : 'nuit');
    const resolvedTheme = useResolvedTheme(themePreference);
    const light = isLightTheme(resolvedTheme);
    const [appearanceOpen, setAppearanceOpen] = useState(false);

    // Garde <html data-ui-theme> aligné sur le thème enregistré du compte.
    useEffect(() => {
        applyTheme(themePreference);
    }, [themePreference]);

    // N'importe quel composant peut ouvrir le panneau « Apparence ».
    useEffect(() => {
        const open = () => setAppearanceOpen(true);
        window.addEventListener('devroad:appearance', open);
        return () => window.removeEventListener('devroad:appearance', open);
    }, []);

    useLayoutEffect(() => {
        const root = contentRef.current;
        const page = root?.firstElementChild;

        if (!root || !page || typeof window === 'undefined') return;
        if (reducedMotionPreferred()) return;

        const targets = Array.from(page.children);
        if (!targets.length) return;

        const context = gsap.context(() => {
            gsap.fromTo(
                targets,
                { autoAlpha: 0, y: 16 },
                {
                    autoAlpha: 1,
                    y: 0,
                    duration: 0.52,
                    stagger: 0.055,
                    ease: 'power3.out',
                    clearProps: 'transform,opacity,visibility',
                },
            );
        }, root);

        return () => context.revert();
    }, [currentPath]);

    return (
        <div className={[
            'min-h-[100dvh] overflow-x-clip bg-[var(--dr-bg)] text-[var(--dr-text)]',
            light ? 'theme-light' : '',
        ].join(' ')} data-theme={light ? 'light' : 'dark'}>
            <LoadingOverlay visible={loading.visible} label={loading.label} />

            <Sidebar user={user} />

            <div className="lg:pl-[260px]">
                <DesktopTopBar preference={themePreference} resolved={resolvedTheme} />

                {/* En-tête mobile — même anatomie que l'Accueil de la maquette : logo, recherche, profil. */}
                {mobileHeader && <header className="sticky top-0 z-30 border-b border-[var(--dr-border)] backdrop-blur-xl [background-color:color-mix(in_srgb,var(--dr-bg)_88%,transparent)] lg:hidden">
                    <div className="flex h-16 items-center gap-2.5 px-4 sm:px-6">
                        <Link href="/dashboard" className="flex min-h-11 min-w-0 flex-1 items-center gap-2.5" aria-label="DevRoad — Accueil">
                            <img src="/icondevroad.png" alt="" width="36" height="36" className="h-9 w-9 shrink-0 object-contain" />
                            <span className="truncate font-['Manrope',sans-serif] text-[19px] font-extrabold tracking-[-0.02em] text-[var(--dr-text)]">Dev<span className="text-[var(--dr-accent-text)]">Road</span></span>
                        </Link>
                        <Link href="/search" aria-label="Rechercher" className="flex h-11 w-11 items-center justify-center rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] text-[var(--dr-text-2)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                            <Icon name="search" size={19} />
                        </Link>
                        <button type="button" onClick={() => setAppearanceOpen(true)} aria-label="Profil et apparence" aria-haspopup="dialog" className="flex h-11 w-11 items-center justify-center rounded-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]">
                            <UserAvatar user={user} />
                        </button>
                    </div>
                </header>}

                <main className="min-h-[calc(100dvh-64px)] pb-[calc(6rem+env(safe-area-inset-bottom))] lg:min-h-[calc(100dvh-72px)] lg:pb-8">
                    <div ref={contentRef} className="mx-auto w-full max-w-[1440px] px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8 xl:px-10 2xl:px-12">
                        {children}
                    </div>
                </main>
            </div>

            <ToastViewport />
            <BottomNav />
            <AppearanceSheet open={appearanceOpen} onClose={() => setAppearanceOpen(false)} preference={themePreference} />
        </div>
    );
}

function UserAvatar({ user }) {
    const name = user?.name ?? 'Utilisateur';
    const initials = name.split(' ').filter(Boolean).slice(0, 2).map((part) => part[0]?.toUpperCase()).join('');

    return (
        <div className="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[var(--dr-accent)] text-sm font-bold text-[var(--dr-ink)]">
            {user?.avatar ? <img src={user.avatar} alt={name} className="h-full w-full object-cover" /> : initials || 'U'}
        </div>
    );
}

function Icon({ name, size = 20, className = '' }) {
    const common = {
        width: size,
        height: size,
        viewBox: '0 0 24 24',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 1.8,
        strokeLinecap: 'round',
        strokeLinejoin: 'round',
        className,
        'aria-hidden': 'true',
    };

    const icons = {
        search: (
            <>
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-4-4" />
            </>
        ),
        chevronDown: <path d="m6 9 6 6 6-6" />,
    };

    return <svg {...common}>{icons[name] ?? null}</svg>;
}
