import { useLayoutEffect, useRef } from 'react';
import { gsap } from 'gsap';
import { Flip } from 'gsap/Flip';
import { reducedMotionPreferred } from '@/theme';

if (typeof window !== 'undefined') {
    gsap.registerPlugin(Flip);
}

export const CARD_SELECTOR = '[data-memo-card]';

export function prefersReducedMotion() {
    return reducedMotionPreferred();
}

/**
 * Animations de la liste des fiches :
 * - entrée en cascade quand le contenu change (filtre, dossier, recherche, page) ;
 * - morphing Flip des cartes au passage liste ↔ grille.
 *
 * `captureLayout()` doit être appelé juste AVANT le changement de vue.
 */
export function useMemoListMotion(listRef, view, contentKey) {
    const flipState = useRef(null);
    const lastContentKey = useRef(null);

    function captureLayout() {
        const list = listRef.current;
        if (!list || prefersReducedMotion()) return;
        flipState.current = Flip.getState(list.querySelectorAll(CARD_SELECTOR));
    }

    // Changement de vue : les cartes glissent de leur ancienne position à la nouvelle.
    useLayoutEffect(() => {
        const state = flipState.current;
        flipState.current = null;
        if (!state || !listRef.current) return;

        const tween = Flip.from(state, {
            targets: listRef.current.querySelectorAll(CARD_SELECTOR),
            duration: 0.5,
            ease: 'power3.inOut',
            stagger: 0.018,
            nested: true,
            prune: true,
        });

        return () => tween.progress(1).kill();
    }, [view]);

    // Nouveau contenu : apparition en cascade (limitée aux 12 premières cartes).
    useLayoutEffect(() => {
        const list = listRef.current;
        const previous = lastContentKey.current;
        lastContentKey.current = contentKey;
        if (!list || previous === contentKey || prefersReducedMotion()) return;

        const cards = Array.from(list.querySelectorAll(CARD_SELECTOR)).slice(0, 12);
        if (!cards.length) return;

        const tween = gsap.fromTo(cards,
            { autoAlpha: 0, y: 14, scale: 0.985 },
            {
                autoAlpha: 1,
                y: 0,
                scale: 1,
                duration: 0.42,
                ease: 'power3.out',
                stagger: 0.045,
                delay: previous === null ? 0.12 : 0,
                clearProps: 'opacity,visibility,transform',
            });

        return () => tween.progress(1).kill();
    }, [contentKey]);

    return { captureLayout };
}

/** Apparition d'un menu contextuel depuis son coin d'ancrage. */
export function useMenuPop(ref, origin = 'top right') {
    useLayoutEffect(() => {
        if (!ref.current || prefersReducedMotion()) return;

        const tween = gsap.fromTo(ref.current,
            { autoAlpha: 0, scale: 0.94, y: -6, transformOrigin: origin },
            { autoAlpha: 1, scale: 1, y: 0, duration: 0.18, ease: 'power2.out', clearProps: 'transform,opacity,visibility' });

        return () => tween.kill();
    }, []);
}

/** « Pop » du marque-page quand on ajoute ou retire un favori. */
export function popFavorite(element, added) {
    if (!element || prefersReducedMotion()) return;

    gsap.timeline()
        .to(element, { scale: added ? 0.7 : 0.85, duration: 0.08, ease: 'power2.in' })
        .to(element, { scale: added ? 1.3 : 1, rotate: added ? -12 : 0, duration: 0.22, ease: 'back.out(3)' })
        .to(element, { scale: 1, rotate: 0, duration: 0.2, ease: 'power2.out', clearProps: 'transform' });
}
