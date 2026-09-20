import { Link, usePage } from '@inertiajs/react';
import {
    CircleUserRound,
    FileText,
    LayoutDashboard,
    Map,
    Search,
} from 'lucide-react';

export default function BottomNav() {
    const { url } = usePage();

    const items = [
        { label: 'Accueil', href: '/dashboard', icon: LayoutDashboard },
        { label: 'Roadmaps', href: '/roadmaps', icon: Map },
        { label: 'Mémos', href: '/memos', icon: FileText },
        { label: 'Recherche', href: '/search', icon: Search },
        { label: 'Profil', href: '/profile', icon: CircleUserRound },
    ];

    const isActive = (href) => {
        if (href === '/dashboard') {
            return url === href || url === `${href}/`;
        }

        return url.startsWith(href);
    };

    return (
        <div
            className="fixed left-1/2 z-50 w-[calc(100%-16px)] -translate-x-1/2 sm:w-[calc(100%-24px)] lg:hidden"
            style={{ bottom: 'max(1rem, env(safe-area-inset-bottom))' }}
        >
            <nav className="theme-bottom-nav mx-auto flex w-full max-w-[420px] items-center justify-between gap-0.5 rounded-[28px] border border-white/[0.10] bg-[#111D2D]/95 px-1.5 py-1.5 shadow-[0_18px_45px_rgba(0,0,0,0.50)] backdrop-blur-2xl supports-[backdrop-filter]:bg-[#111D2D]/85">
                {items.map((item) => {
                    const active = isActive(item.href);
                    const Icon = item.icon;

                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            aria-current={active ? 'page' : undefined}
                            aria-label={item.label}
                            className="flex min-w-0 flex-1 items-center justify-center"
                        >
                            <div
                                className={[
                                    'flex h-11 min-w-0 items-center justify-center rounded-full transition-all duration-200 sm:h-12',
                                    active
                                        ? 'w-full max-w-[92px] gap-1.5 bg-[#FF6A00] px-2.5 text-[#08111F] shadow-[0_8px_22px_rgba(255,106,0,0.30)]'
                                        : 'w-11 text-slate-500 hover:bg-white/[0.05] hover:text-slate-300 theme-bottom-nav-item',
                                ].join(' ')}
                            >
                                <Icon size={18} strokeWidth={active ? 2.4 : 1.9} className="shrink-0" />
                                {active && (
                                    <span className="truncate text-[10px] font-bold leading-none">
                                        {item.label}
                                    </span>
                                )}
                            </div>
                        </Link>
                    );
                })}
            </nav>
        </div>
    );
}
