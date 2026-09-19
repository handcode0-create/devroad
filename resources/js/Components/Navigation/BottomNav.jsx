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
        {
            label: 'Accueil',
            href: '/dashboard',
            icon: LayoutDashboard,
        },
        {
            label: 'Roadmaps',
            href: '/roadmaps',
            icon: Map,
        },
        {
            label: 'Mémos',
            href: '/memos',
            icon: FileText,
        },
        {
            label: 'Recherche',
            href: '/search',
            icon: Search,
        },
        {
            label: 'Profil',
            href: '/profile',
            icon: CircleUserRound,
        },
    ];

    const isActive = (href) => {
        if (href === '/dashboard') {
            return url === href || url === `${href}/`;
        }

        return url.startsWith(href);
    };

    return (
        <div className="fixed bottom-4 left-1/2 z-50 w-[calc(100%-20px)] -translate-x-1/2 lg:hidden">
            <nav
                className="
                    mx-auto
                    flex
                    w-full
                    max-w-[370px]
                    items-center
                    justify-between
                    gap-1
                    rounded-[30px]
                    border
                    border-white/[0.10]
                    bg-[#111D2D]/96
                    px-2
                    py-2
                    shadow-[0_18px_45px_rgba(0,0,0,0.50)]
                    backdrop-blur-2xl
                "
            >
                {items.map((item) => {
                    const active = isActive(item.href);
                    const Icon = item.icon;

                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            aria-current={active ? 'page' : undefined}
                            aria-label={item.label}
                            className="flex flex-1 items-center justify-center"
                        >
                            <div
                                className={[
                                    'flex h-[50px] items-center justify-center rounded-full transition-all duration-200',
                                    active
                                        ? 'min-w-[72px] gap-1.5 bg-[#FF6A00] px-3 text-[#0B3A82] shadow-[0_8px_22px_rgba(255,106,0,0.35)]'
                                        : 'w-11 text-slate-500 hover:bg-white/[0.05] hover:text-slate-300',
                                ].join(' ')}
                            >
                                <Icon
                                    size={19}
                                    strokeWidth={active ? 2.4 : 1.9}
                                />

                                {active && (
                                    <span className="whitespace-nowrap text-[10px] font-bold leading-none">
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