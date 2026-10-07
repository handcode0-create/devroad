import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// Le voile plein écran bloque l'interface : il ne doit apparaître que si l'attente devient
// vraiment perceptible. En dessous de ces délais, la barre de progression d'Inertia suffit.
const PAGE_DELAY = 600;
const SAVE_DELAY = 1000;

/**
 * Indique quand afficher le voile de chargement :
 * - changement de page (GET vers une autre URL) qui dure plus de 600 ms ;
 * - envoi d'un formulaire avec redirection qui dure plus de 1 s.
 * Jamais pour : filtres, recherche, pagination (même page), actions en place (favori, thème,
 * suppression d'une pièce jointe…) ni rechargements partiels.
 */
export default function useNavigationLoading() {
    const [state, setState] = useState({ visible: false, label: 'Chargement...' });

    useEffect(() => {
        let timer = null;

        const stop = () => {
            clearTimeout(timer);
            setState((current) => (current.visible ? { ...current, visible: false } : current));
        };

        const removeStart = router.on('start', (event) => {
            const visit = event.detail?.visit ?? {};
            const method = (visit.method ?? 'get').toUpperCase();
            const inPlace = visit.preserveScroll === true
                || visit.async === true
                || Boolean(visit.prefetch)
                || (Array.isArray(visit.only) && visit.only.length > 0)
                || (Array.isArray(visit.except) && visit.except.length > 0);

            clearTimeout(timer);
            if (inPlace) return;

            if (method === 'GET') {
                let samePage = false;
                try {
                    samePage = new URL(visit.url, window.location.href).pathname === window.location.pathname;
                } catch {
                    samePage = false;
                }
                if (samePage || visit.preserveState === true) return;

                timer = setTimeout(() => setState({ visible: true, label: 'Chargement...' }), PAGE_DELAY);
                return;
            }

            timer = setTimeout(() => setState({ visible: true, label: 'Enregistrement...' }), SAVE_DELAY);
        });
        const removeFinish = router.on('finish', stop);

        return () => {
            clearTimeout(timer);
            removeStart();
            removeFinish();
        };
    }, []);

    return state;
}
