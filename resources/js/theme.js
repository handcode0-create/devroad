import { useEffect, useState } from 'react';

// Thèmes de la maquette « Refonte & thèmes » (valeurs des aperçus du panneau Apparence).
export const THEMES = [
    { key: 'nuit', name: 'Nuit', bg: '#08111F', surface: '#0D1725', border: 'rgba(255,255,255,0.14)', text: '#F4F6FA', text2: '#A3AEC2' },
    { key: 'minuit', name: 'Minuit', bg: '#000000', surface: '#0B0B0D', border: 'rgba(255,255,255,0.16)', text: '#F5F5F6', text2: '#A6A6B0' },
    { key: 'ardoise', name: 'Ardoise', bg: '#1A1F27', surface: '#222833', border: 'rgba(255,255,255,0.16)', text: '#EEF1F5', text2: '#B6BECA' },
    { key: 'clair', name: 'Clair', bg: '#F4F6F9', surface: '#FFFFFF', border: 'rgba(8,17,31,0.18)', text: '#0B1424', text2: '#475467' },
    { key: 'sable', name: 'Sable', bg: '#F3EEE5', surface: '#FBF8F2', border: 'rgba(60,40,20,0.20)', text: '#261D14', text2: '#5B4F42' },
];

const LIGHT = ['clair', 'sable'];
const KEYS = THEMES.map((theme) => theme.key);

export function resolveTheme(preference) {
    if (preference === 'auto') {
        const light = typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: light)').matches;
        return light ? 'clair' : 'nuit';
    }

    return KEYS.includes(preference) ? preference : 'nuit';
}

export function isLightTheme(theme) {
    return LIGHT.includes(theme);
}

/** Applique un thème immédiatement (avant même la réponse du serveur). */
export function applyTheme(preference) {
    if (typeof document === 'undefined') return;

    const root = document.documentElement;
    const changes = resolveTheme(preference) !== root.dataset.uiTheme;

    // Fondu entre l'ancien et le nouveau thème (View Transitions, natif du navigateur).
    if (changes && document.startViewTransition && !reducedMotionPreferred()) {
        document.startViewTransition(() => writeTheme(preference));
        return;
    }

    writeTheme(preference);
}

function writeTheme(preference) {
    const root = document.documentElement;
    const resolved = resolveTheme(preference);
    root.dataset.uiThemePref = preference;
    root.dataset.uiTheme = resolved;
    root.dataset.theme = isLightTheme(resolved) ? 'light' : 'dark';

    const meta = document.querySelector('meta[name="theme-color"]');
    const theme = THEMES.find((item) => item.key === resolved);
    if (meta && theme) meta.setAttribute('content', theme.bg);

    window.dispatchEvent(new CustomEvent('devroad:theme', { detail: { preference, resolved } }));
}

/** Thème réellement affiché, mis à jour à chaque changement. */
export function useResolvedTheme(preference) {
    const [resolved, setResolved] = useState(() => resolveTheme(preference));

    useEffect(() => {
        setResolved(resolveTheme(preference));
        const onTheme = (event) => setResolved(event.detail.resolved);
        window.addEventListener('devroad:theme', onTheme);

        // « Automatique » suit le changement jour/nuit du téléphone en direct.
        const media = window.matchMedia('(prefers-color-scheme: light)');
        const onSystem = () => {
            if (document.documentElement.dataset.uiThemePref === 'auto') applyTheme('auto');
        };
        media.addEventListener?.('change', onSystem);

        return () => {
            window.removeEventListener('devroad:theme', onTheme);
            media.removeEventListener?.('change', onSystem);
        };
    }, [preference]);

    return resolved;
}

export function reducedMotionPreferred() {
    if (typeof window === 'undefined') return true;

    return document.documentElement.dataset.motion === 'reduce'
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export function setReducedMotion(reduce) {
    const root = document.documentElement;
    if (reduce) root.dataset.motion = 'reduce';
    else delete root.dataset.motion;

    try {
        if (reduce) localStorage.setItem('devroad:motion', 'reduce');
        else localStorage.removeItem('devroad:motion');
    } catch {
        // Stockage indisponible (navigation privée) : le réglage vaut pour cette session.
    }
}
