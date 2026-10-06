import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Sheet, m, softSpring } from '@/Components/Ui/Motion';

const MotionLink = m.create(Link);

// Barre de navigation mobile — maquette « Refonte & thèmes » (Claude Design).
const ICONS = {
    home: 'M3 11l9-7 9 7v9a1 1 0 01-1 1h-5v-6h-6v6H4a1 1 0 01-1-1z',
    map: 'M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14',
    plus: 'M12 5v14M5 12h14',
    memo: 'M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h6',
    tools: 'M8 7l-5 5 5 5M16 7l5 5-5 5',
    box: 'M12 3l8 4.5v9L12 21l-8-4.5v-9zM12 12l8-4.5M12 12L4 7.5M12 12v9',
    search: 'M11 4a7 7 0 100 14 7 7 0 000-14zM20 20l-3.5-3.5',
    newMemo: 'M7 3h7l5 5v13H7zM14 3v5h5M13 12v6M10 15h6',
    user: 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0',
};

function Icon({ path, size = 22, stroke = 1.9 }) {
    return (
        <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <path d={path} />
        </svg>
    );
}

const SHEETS = {
    create: {
        title: 'Créer',
        items: [
            { label: 'Nouvelle fiche', hint: 'Une commande, une notion, une astuce', href: '/memos/create', icon: ICONS.newMemo },
            { label: 'Nouveau parcours', hint: 'Une roadmap avec ses étapes', href: '/roadmaps/create', icon: ICONS.map },
        ],
    },
    tools: {
        title: 'Outils',
        items: [
            { label: 'DevLab', hint: 'Éditeur de code dans le navigateur', href: '/devlab', icon: ICONS.tools },
            { label: 'Sandbox', hint: 'Projets dans un environnement isolé', href: '/sandbox', icon: ICONS.box },
            { label: 'Recherche', hint: 'Fiches, parcours et étapes', href: '/search', icon: ICONS.search },
            { label: 'Profil et compte', hint: 'Informations, mot de passe, déconnexion', href: '/profile', icon: ICONS.user },
        ],
    },
};

export default function BottomNav() {
    const { url } = usePage();
    const [sheet, setSheet] = useState(null);
    // Garde le contenu affiché pendant l'animation de fermeture du panneau.
    const [shown, setShown] = useState(null);

    useEffect(() => setSheet(null), [url]);

    function openSheet(key) {
        setShown(SHEETS[key]);
        setSheet(key);
    }

    const starts = (...prefixes) => prefixes.some((prefix) => url === prefix || url.startsWith(prefix + '/') || url.startsWith(prefix + '?'));

    const tabs = [
        { key: 'home', label: 'Accueil', href: '/dashboard', icon: ICONS.home, active: starts('/dashboard') },
        { key: 'map', label: 'Parcours', href: '/roadmaps', icon: ICONS.map, active: starts('/roadmaps', '/steps') },
        { key: 'create', label: 'Créer', icon: ICONS.plus, create: true },
        { key: 'memo', label: 'Fiches', href: '/memos', icon: ICONS.memo, active: starts('/memos') },
        { key: 'tools', label: 'Outils', icon: ICONS.tools, sheet: 'tools', active: starts('/devlab', '/sandbox', '/search', '/profile') },
    ];

    const tabClass = (active) => 'relative flex h-[52px] w-[58px] flex-col items-center justify-center gap-[3px] rounded-[14px] text-[11px] font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] '
        + (active ? 'text-[var(--dr-accent-text)]' : 'text-[var(--dr-text-3)]');

    // Pastille de l'onglet actif : glisse d'un onglet à l'autre (layout partagé).
    const content = (tab) => <>
        {tab.active && <m.span layoutId="bottom-nav-active" transition={softSpring} aria-hidden="true" className="absolute inset-0 rounded-[14px] bg-[var(--dr-accent-soft)]" />}
        <span className="relative flex flex-col items-center gap-[3px]">
            <Icon path={tab.icon} />
            <span>{tab.label}</span>
        </span>
    </>;

    return (
        <>
            <nav
                aria-label="Navigation principale"
                className="fixed left-3 right-3 z-50 mx-auto flex h-[68px] max-w-[420px] items-center justify-around rounded-3xl border border-[var(--dr-border-2)] bg-[var(--dr-surface-2)] px-1.5 font-['Figtree',system-ui,sans-serif] shadow-[var(--dr-nav-shadow)] lg:hidden"
                style={{ bottom: 'max(16px, env(safe-area-inset-bottom))' }}
            >
                {tabs.map((tab) => {
                    if (tab.create) {
                        return (
                            <m.button key={tab.key} whileTap={{ scale: 0.88 }} whileHover={{ scale: 1.04 }} type="button" onClick={() => openSheet('create')} aria-label="Créer" aria-haspopup="dialog" className="flex h-[52px] w-[52px] items-center justify-center rounded-2xl bg-[var(--dr-accent)] text-[var(--dr-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]">
                                <m.span className="flex" animate={{ rotate: sheet === 'create' ? 45 : 0 }} transition={softSpring}>
                                    <Icon path={tab.icon} stroke={2.6} />
                                </m.span>
                            </m.button>
                        );
                    }

                    if (tab.sheet) {
                        return (
                            <m.button key={tab.key} whileTap={{ scale: 0.92 }} type="button" onClick={() => openSheet(tab.sheet)} aria-label={tab.label} aria-haspopup="dialog" className={tabClass(tab.active)}>
                                {content(tab)}
                            </m.button>
                        );
                    }

                    return (
                        <MotionLink key={tab.key} whileTap={{ scale: 0.92 }} href={tab.href} aria-label={tab.label} aria-current={tab.active ? 'page' : undefined} className={tabClass(tab.active)}>
                            {content(tab)}
                        </MotionLink>
                    );
                })}
            </nav>

            <Sheet open={Boolean(sheet)} onClose={() => setSheet(null)} label={shown?.title} className="gap-3 pb-[calc(24px+env(safe-area-inset-bottom))]">
                {shown && <>
                    <h2 className="m-0 font-['Manrope',sans-serif] text-2xl font-extrabold tracking-[-0.02em]">{shown.title}</h2>
                    <div className="flex flex-col gap-2">
                        {shown.items.map((item, index) => (
                            <MotionLink
                                key={item.href}
                                href={item.href}
                                initial={{ opacity: 0, y: 14 }}
                                animate={{ opacity: 1, y: 0, transition: { ...softSpring, delay: 0.05 + index * 0.04 } }}
                                whileTap={{ scale: 0.98 }}
                                className="flex items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-field)] p-3.5 text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]"
                            >
                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-[11px] bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]">
                                    <Icon path={item.icon} size={18} stroke={2} />
                                </span>
                                <span className="flex flex-1 flex-col gap-0.5">
                                    <span className="text-[15px] font-semibold">{item.label}</span>
                                    <span className="text-[13px] text-[var(--dr-text-3)]">{item.hint}</span>
                                </span>
                            </MotionLink>
                        ))}
                    </div>
                </>}
            </Sheet>
        </>
    );
}
