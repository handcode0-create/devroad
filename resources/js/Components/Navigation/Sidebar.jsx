import { Link, usePage } from "@inertiajs/react";
import {
    BookOpen,
    ChevronRight,
    CircleUserRound,
    FileText,
    LayoutDashboard,
    Plus,
    Search,
    Settings,
    Sparkles,
    Map,
} from "lucide-react";

export default function Sidebar({ user }) {
    const { url } = usePage();

    const navigation = [
        {
            label: "Accueil",
            href: "/dashboard",
            icon: LayoutDashboard,
        },
        {
            label: "Roadmaps",
            href: "/roadmaps",
            icon: Map,
        },
        {
            label: "Mémos",
            href: "/memos",
            icon: FileText,
        },
        {
            label: "Recherche",
            href: "/search",
            icon: Search,
        },
        {
            label: "Profil",
            href: "/profile",
            icon: CircleUserRound,
        },
    ];

    const isActive = (href) => {
        if (href === "/dashboard") {
            return url === href || url === `${href}/`;
        }

        return url.startsWith(href);
    };

    return (
        <aside className="fixed inset-y-0 left-0 z-40 hidden w-[260px] border-r border-white/[0.06] bg-[#08111F] lg:flex lg:flex-col">
            {/* ============================================================
                BRAND
            ============================================================ */}
            <div className="flex h-[88px] items-center border-b border-white/[0.06] px-6">
                <Link href="/dashboard" className="flex items-center gap-3">
                    {/* DevRoad mark */}
                    <div className="relative flex h-10 w-10 items-center justify-center">
                        <div className="absolute inset-0 rotate-[30deg] rounded-[10px] bg-[#FF6A00] shadow-[0_0_24px_rgba(255,106,0,0.18)]" />

                        <div className="relative h-[22px] w-[22px] rotate-[30deg] rounded-[5px] border-[4px] border-white" />
                    </div>

                    <div>
                        <div className="text-xl font-extrabold tracking-tight text-white">
                            Dev<span className="text-[#FF6A00]">Road</span>
                        </div>

                        <p className="text-[10px] font-medium tracking-wide text-slate-500">
                            Planifie. Apprends. Progresse.
                        </p>
                    </div>
                </Link>
            </div>

            {/* ============================================================
                NAVIGATION
            ============================================================ */}
            <div className="flex-1 overflow-y-auto px-4 py-6">
                <p className="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                    Navigation
                </p>

                <nav className="space-y-2">
                    {navigation.map((item) => {
                        const active = isActive(item.href);
                        const Icon = item.icon;

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={[
                                    "group flex items-center gap-3 rounded-full px-3 py-2 transition-all duration-200",
                                    active
                                        ? "bg-[#FF6A00]/10 text-white shadow-[inset_0_0_0_1px_rgba(255,106,0,0.08)]"
                                        : "text-slate-400 hover:bg-white/[0.04] hover:text-white",
                                ].join(" ")}
                            >
                                {/* Icon pill */}
                                <span
                                    className={[
                                        "flex h-10 w-10 shrink-0 items-center justify-center rounded-full transition-all duration-200",
                                        active
                                            ? "bg-[#FF6A00] text-white shadow-[0_6px_20px_rgba(255,106,0,0.28)]"
                                            : "bg-white/[0.04] text-slate-400 group-hover:bg-white/[0.07] group-hover:text-white",
                                    ].join(" ")}
                                >
                                    <Icon size={19} strokeWidth={2} />
                                </span>

                                <span className="flex-1 text-sm font-semibold">
                                    {item.label}
                                </span>

                                {active && (
                                    <ChevronRight
                                        size={16}
                                        strokeWidth={2}
                                        className="mr-1 text-[#FF8A3D]"
                                    />
                                )}
                            </Link>
                        );
                    })}
                </nav>

                {/* ========================================================
                    QUICK ACTION
                ======================================================== */}
                <div className="mt-8">
                    <p className="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Actions
                    </p>

                    <Link
                        href="/roadmaps/create"
                        className="group flex items-center gap-3 rounded-2xl border border-[#FF6A00]/20 bg-[#FF6A00]/[0.07] p-3 transition-all duration-200 hover:border-[#FF6A00]/40 hover:bg-[#FF6A00]/[0.11]"
                    >
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#FF6A00] text-white shadow-[0_8px_20px_rgba(255,106,0,0.25)]">
                            <Plus size={18} strokeWidth={2.2} />
                        </span>

                        <div className="min-w-0">
                            <p className="text-sm font-semibold text-white">
                                Nouvelle roadmap
                            </p>

                            <p className="mt-0.5 text-[11px] text-slate-500">
                                Commencer un parcours
                            </p>
                        </div>
                    </Link>
                </div>

                {/* ========================================================
                    MOTIVATION CARD
                ======================================================== */}
                <div className="mt-6 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4">
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[#FF6A00]/10 text-[#FF8A3D]">
                        <Sparkles size={18} strokeWidth={2} />
                    </div>

                    <p className="mt-3 text-sm font-semibold text-white">
                        Continue à apprendre.
                    </p>

                    <p className="mt-1 text-xs leading-5 text-slate-500">
                        Les petites avancées régulières construisent les grandes
                        compétences.
                    </p>
                </div>
            </div>

            {/* ============================================================
                USER
            ============================================================ */}
            <div className="border-t border-white/[0.06] p-4">
                <Link
                    href="/profile"
                    className={[
                        "group flex items-center gap-3 rounded-full p-2 transition-all duration-200",
                        isActive("/profile")
                            ? "bg-[#FF6A00]/10"
                            : "hover:bg-white/[0.04]",
                    ].join(" ")}
                >
                    <UserAvatar user={user} />

                    <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-semibold text-white">
                            {user?.name ?? "Utilisateur"}
                        </p>

                        <p className="truncate text-xs text-slate-500">
                            {user?.email ?? "Compte développeur"}
                        </p>
                    </div>

                    <Settings
                        size={17}
                        strokeWidth={2}
                        className="mr-2 text-slate-500 transition group-hover:text-white"
                    />
                </Link>
            </div>
        </aside>
    );
}

function UserAvatar({ user }) {
    const name = user?.name ?? "Utilisateur";

    const initials = name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join("");

    return (
        <div className="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/10 bg-gradient-to-br from-[#FF8A3D] to-[#FF6A00] text-xs font-bold text-white shadow-[0_4px_15px_rgba(255,106,0,0.18)]">
            {user?.avatar ? (
                <img
                    src={user.avatar}
                    alt={name}
                    className="h-full w-full object-cover"
                />
            ) : (
                initials || "U"
            )}
        </div>
    );
}
