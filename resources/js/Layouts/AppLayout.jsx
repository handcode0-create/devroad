import { Link, router, usePage } from '@inertiajs/react';
import Sidebar from '@/Components/Navigation/Sidebar';
import BottomNav from '@/Components/Navigation/BottomNav';
import ToastViewport from '@/Components/Ui/ToastViewport';
import { Code2 } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function AppLayout({ children }) {
    const { auth } = usePage().props;
    const [navigating, setNavigating] = useState(false);
    const user = auth?.user;

    useEffect(() => {
        const removeStartListener = router.on('start', () => setNavigating(true));
        const removeFinishListener = router.on('finish', () => setNavigating(false));

        return () => {
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    return (
        <div className="min-h-[100dvh] overflow-x-clip bg-[#08111F] text-white">
            {navigating && (
                <div className="fixed inset-x-0 top-0 z-[100] h-0.5 overflow-hidden bg-transparent">
                    <div className="h-full w-1/3 animate-pulse bg-[#FF6A00] shadow-[0_0_18px_rgba(255,106,0,0.8)]" />
                </div>
            )}

            <Sidebar user={user} />

            <div className="lg:pl-[260px]">
                <header className="sticky top-0 z-30 hidden border-b border-white/[0.06] bg-[#08111F]/95 backdrop-blur-xl lg:block supports-[backdrop-filter]:bg-[#08111F]/85">
                    <div className="flex h-[72px] items-center justify-between gap-6 px-6 xl:px-8">
                        <div className="min-w-0">
                            <p className="text-sm font-medium text-slate-400">Ton espace développeur</p>
                            <h1 className="mt-0.5 truncate text-lg font-semibold tracking-tight text-white">Continue à progresser 🚀</h1>
                        </div>

                        <div className="flex shrink-0 items-center gap-3">
                            <Link href="/search" className="touch-target group flex items-center justify-center rounded-xl border border-white/[0.07] bg-[#101A2A] text-slate-400 transition hover:border-white/[0.12] hover:bg-[#142033] hover:text-white" aria-label="Rechercher">
                                <Icon name="search" size={19} />
                            </Link>

                            <Link href="/profile" className="flex min-h-11 max-w-[280px] items-center gap-3 rounded-xl border border-white/[0.07] bg-[#101A2A] px-3 py-2 transition hover:border-white/[0.12] hover:bg-[#142033]">
                                <UserAvatar user={user} />
                                <div className="hidden min-w-0 xl:block">
                                    <p className="text-xs font-medium text-slate-400">Connecté en tant que</p>
                                    <p className="max-w-[150px] truncate text-sm font-semibold text-white">{user?.name ?? 'Utilisateur'}</p>
                                </div>
                                <Icon name="chevronDown" size={16} className="ml-1 shrink-0 text-slate-500" />
                            </Link>
                        </div>
                    </div>
                </header>

                <header className="sticky top-0 z-30 border-b border-white/[0.06] bg-[#08111F]/95 backdrop-blur-xl lg:hidden supports-[backdrop-filter]:bg-[#08111F]/85">
                    <div className="flex min-h-[64px] items-center justify-between gap-3 px-3 sm:px-5">
                        <Link href="/dashboard" className="flex min-h-11 min-w-0 items-center gap-2.5" aria-label="DevRoad">
                            <img src="/icondevroad.png" alt="" className="h-9 w-9 shrink-0 object-contain" />
                            <span className="truncate text-[18px] font-extrabold tracking-tight text-white">Dev<span className="text-[#FF6A00]">Road</span></span>
                        </Link>

                        <div className="flex shrink-0 items-center gap-1.5 sm:gap-2">
                            <Link href="/devlab" className="touch-target flex items-center justify-center rounded-xl bg-[#FF6A00] text-[#08111F] shadow-[0_8px_18px_rgba(255,106,0,0.22)]" aria-label="DevLab">
                                <Code2 size={18} />
                            </Link>
                            <Link href="/search" className="touch-target flex items-center justify-center rounded-xl bg-[#101A2A] text-slate-300" aria-label="Rechercher">
                                <Icon name="search" size={18} />
                            </Link>
                            <Link href="/profile" className="touch-target flex items-center justify-center" aria-label="Profil">
                                <UserAvatar user={user} />
                            </Link>
                        </div>
                    </div>
                </header>

                <main className="min-h-[calc(100dvh-64px)] pb-[calc(6rem+env(safe-area-inset-bottom))] lg:min-h-[calc(100dvh-72px)] lg:pb-8">
                    <div className="mx-auto w-full max-w-[1440px] px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8 xl:px-10 2xl:px-12">
                        {children}
                    </div>
                </main>
            </div>

            <ToastViewport />
            <BottomNav />
        </div>
    );
}

function UserAvatar({ user }) {
    const name = user?.name ?? 'Utilisateur';
    const initials = name.split(' ').filter(Boolean).slice(0, 2).map((part) => part[0]?.toUpperCase()).join('');

    return (
        <div className="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/10 bg-gradient-to-br from-[#FF8A3D] to-[#FF6A00] text-xs font-bold text-white shadow-[0_4px_16px_rgba(255,106,0,0.25)]">
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
