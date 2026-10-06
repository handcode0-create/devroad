import { Link, router, usePage } from "@inertiajs/react";
import { useEffect, useRef, useState } from "react";
import { AnimatePresence, m, softSpring } from "@/Components/Ui/Motion";

// Barre latérale desktop — artboard « Desktop — Fiches » (Claude Design).
const MotionLink = m.create(Link);

const ICONS = {
    home: "M3 11l9-7 9 7v9a1 1 0 01-1 1h-5v-6h-6v6H4a1 1 0 01-1-1z",
    map: "M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14",
    memo: "M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h6",
    code: "M8 7l-5 5 5 5M16 7l5 5-5 5",
    box: "M12 3l8 4.5v9L12 21l-8-4.5v-9zM12 12l8-4.5M12 12L4 7.5M12 12v9",
    folder: "M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z",
    plus: "M12 5v14M5 12h14",
    newMemo: "M7 3h7l5 5v13H7zM14 3v5h5M13 12v6M10 15h6",
    road: ["M8 20L11 4", "M16 20L13 4", "M12 16v1M12 11v1M12 7v1"],
};

function Svg({ d, size = 18, stroke = 1.9 }) {
    const paths = Array.isArray(d) ? d : [d];
    return (
        <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            {paths.map((path) => <path key={path} d={path} />)}
        </svg>
    );
}

function initials(name) {
    return String(name ?? "").trim().split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]?.toUpperCase()).join("") || "DR";
}

export default function Sidebar({ user }) {
    const { url, props } = usePage();
    const nav = props.nav ?? {};
    const folderParam = new URLSearchParams(url.split("?")[1] ?? "").get("folder");
    const path = url.split("?")[0];
    const starts = (...prefixes) => prefixes.some((prefix) => path === prefix || path.startsWith(prefix + "/"));

    const items = [
        { label: "Accueil", href: "/dashboard", icon: ICONS.home, active: starts("/dashboard") },
        { label: "Parcours", href: "/roadmaps", icon: ICONS.map, count: nav.roadmaps, active: starts("/roadmaps", "/steps") },
        { label: "Fiches mémo", href: "/memos", icon: ICONS.memo, count: nav.memos, active: starts("/memos") && !folderParam },
        { label: "DevLab", href: "/devlab", icon: ICONS.code, active: starts("/devlab") },
        { label: "Sandbox", href: "/sandbox", icon: ICONS.box, active: starts("/sandbox") },
    ];

    const folders = orderFolders(nav.folders ?? []);
    const [dropTarget, setDropTarget] = useState(null);

    // Glisser une carte de fiche sur un dossier de la barre latérale la range dedans.
    const DND = "application/x-devroad-memo-dnd";
    const dropProps = (folderId) => ({
        onDragOver: (event) => {
            if (!event.dataTransfer?.types?.includes(DND)) return;
            event.preventDefault();
            event.dataTransfer.dropEffect = "move";
            setDropTarget(folderId);
        },
        onDragLeave: () => setDropTarget((current) => (current === folderId ? null : current)),
        onDrop: (event) => {
            setDropTarget(null);
            let item = null;
            try { item = JSON.parse(event.dataTransfer.getData(DND) || "null"); } catch {}
            if (item?.type !== "memo") return;
            event.preventDefault();
            router.patch("/memos/" + item.id + "/move", { folder_id: folderId }, { preserveScroll: true });
        },
    });
    const shownFolders = folders.slice(0, 8);

    return (
        <aside className="fixed inset-y-0 left-0 z-40 hidden w-[260px] flex-col gap-7 overflow-y-auto border-r border-[var(--dr-border)] bg-[var(--dr-bg)] px-4 py-6 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] lg:flex">
            <Link href="/dashboard" className="flex items-center gap-2.5 px-2" aria-label="DevRoad — Accueil">
                <span className="flex h-[34px] w-[34px] items-center justify-center rounded-[10px] bg-[var(--dr-accent)] text-[var(--dr-ink)]"><Svg d={ICONS.road} size={20} stroke={2.4} /></span>
                <span className="font-['Manrope',sans-serif] text-xl font-extrabold tracking-[-0.02em]">Dev<span className="text-[var(--dr-accent-text)]">Road</span></span>
            </Link>

            <CreateMenu />

            <nav aria-label="Navigation principale" className="flex flex-col gap-0.5">
                {items.map((item) => (
                    <MotionLink
                        key={item.href}
                        href={item.href}
                        whileTap={{ scale: 0.97 }}
                        aria-current={item.active ? "page" : undefined}
                        className={"relative flex h-[42px] items-center gap-3 rounded-[10px] px-3 text-sm transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] " + (item.active ? "font-bold text-[var(--dr-accent-text)]" : "font-medium text-[var(--dr-text-2)] hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)]")}
                    >
                        {item.active && <m.span layoutId="sidebar-active" transition={softSpring} aria-hidden="true" className="absolute inset-0 rounded-[10px] bg-[var(--dr-accent-soft)]" />}
                        <span className="relative flex"><Svg d={item.icon} /></span>
                        <span className="relative flex-1">{item.label}</span>
                        {typeof item.count === "number" && <span className="relative text-xs font-normal tabular-nums text-[var(--dr-text-3)]">{item.count}</span>}
                    </MotionLink>
                ))}
            </nav>

            <div className="flex flex-col gap-1.5">
                <span className="px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-text-3)]">Dossiers</span>
                {shownFolders.map((folder) => {
                    const active = String(folder.id) === folderParam && starts("/memos");
                    return (
                        <Link
                            key={folder.id}
                            href={"/memos?folder=" + folder.id}
                            aria-current={active ? "page" : undefined}
                            {...dropProps(folder.id)}
                            className={(dropTarget === folder.id ? "ring-1 ring-[var(--dr-accent)] " : "") + "flex h-[38px] items-center gap-2.5 rounded-[10px] pr-3 text-sm transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] " + (active ? "bg-[var(--dr-accent-soft)] font-semibold text-[var(--dr-accent-text)]" : "text-[var(--dr-text-2)] hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)]")}
                            style={{ paddingLeft: 12 + folder.depth * 14 }}
                        >
                            <Svg d={ICONS.folder} size={16} />
                            <span className="min-w-0 flex-1 truncate">{folder.name}</span>
                            <span className="text-xs tabular-nums text-[var(--dr-text-3)]">{folder.memos_count}</span>
                        </Link>
                    );
                })}
                {folders.length > shownFolders.length && <Link href="/memos?dossiers=1" className="flex h-[34px] items-center px-3 text-[13px] font-semibold text-[var(--dr-accent-text)]">Voir les {folders.length} dossiers</Link>}
                <Link href="/memos?nouveau-dossier=1" className="flex h-[38px] items-center gap-2.5 rounded-[10px] px-3 text-sm text-[var(--dr-text-3)] transition-colors hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)]">
                    <Svg d={ICONS.plus} size={16} stroke={2} />Nouveau dossier
                </Link>
            </div>

            <Link href="/profile" className={"mt-auto flex items-center gap-2.5 rounded-[14px] border border-[var(--dr-border)] bg-[var(--dr-surface)] p-2.5 transition-colors hover:border-[var(--dr-border-2)] " + (starts("/profile") ? "ring-1 ring-[var(--dr-accent)]" : "")}>
                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[var(--dr-accent)] text-[13px] font-bold text-[var(--dr-ink)]">{initials(user?.name)}</span>
                <span className="flex min-w-0 flex-1 flex-col">
                    <span className="truncate text-sm font-semibold">{user?.name ?? "Utilisateur"}</span>
                    <span className="text-xs text-[var(--dr-text-3)]">Profil et réglages</span>
                </span>
            </Link>
        </aside>
    );
}

/** Ordonne les dossiers en arbre (parents puis enfants) avec leur profondeur. */
function orderFolders(folders) {
    const byParent = new Map();
    folders.forEach((folder) => {
        const key = folder.parent_id ?? "root";
        if (!byParent.has(key)) byParent.set(key, []);
        byParent.get(key).push(folder);
    });
    const ordered = [];
    const walk = (parent, depth) => (byParent.get(parent) ?? []).forEach((folder) => {
        ordered.push({ ...folder, depth: Math.min(depth, 3) });
        walk(folder.id, depth + 1);
    });
    walk("root", 0);
    return ordered;
}

function CreateMenu() {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        if (!open) return undefined;
        const close = (event) => { if (!ref.current?.contains(event.target)) setOpen(false); };
        const onKey = (event) => { if (event.key === "Escape") setOpen(false); };
        document.addEventListener("mousedown", close);
        window.addEventListener("keydown", onKey);
        return () => { document.removeEventListener("mousedown", close); window.removeEventListener("keydown", onKey); };
    }, [open]);

    return (
        <div ref={ref} className="relative">
            <m.button whileTap={{ scale: 0.97 }} type="button" onClick={() => setOpen((value) => !value)} aria-haspopup="menu" aria-expanded={open} className="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[var(--dr-accent)] text-sm font-bold text-[var(--dr-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]">
                <m.span className="flex" animate={{ rotate: open ? 45 : 0 }} transition={softSpring}><Svg d={ICONS.plus} size={16} stroke={2.4} /></m.span>Créer
            </m.button>
            <AnimatePresence>
                {open && (
                    <m.div
                        role="menu"
                        initial={{ opacity: 0, y: -6, scale: 0.97 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: -6, scale: 0.97, transition: { duration: 0.12 } }}
                        transition={softSpring}
                        style={{ transformOrigin: "top center" }}
                        className="absolute left-0 right-0 top-[52px] z-50 flex flex-col gap-0.5 rounded-2xl border border-[var(--dr-border-2)] bg-[var(--dr-surface-2)] p-1.5 shadow-[0_24px_60px_-20px_rgba(0,0,0,0.55)]"
                    >
                        <Link role="menuitem" href="/memos/create" className="flex items-center gap-3 rounded-[10px] px-2.5 py-2.5 text-sm font-medium text-[var(--dr-text)] hover:bg-[var(--dr-accent-soft)]">
                            <span className="text-[var(--dr-accent-text)]"><Svg d={ICONS.newMemo} size={17} /></span>Nouvelle fiche
                        </Link>
                        <Link role="menuitem" href="/roadmaps/create" className="flex items-center gap-3 rounded-[10px] px-2.5 py-2.5 text-sm font-medium text-[var(--dr-text)] hover:bg-[var(--dr-accent-soft)]">
                            <span className="text-[var(--dr-accent-text)]"><Svg d={ICONS.map} size={17} /></span>Nouveau parcours
                        </Link>
                    </m.div>
                )}
            </AnimatePresence>
        </div>
    );
}
