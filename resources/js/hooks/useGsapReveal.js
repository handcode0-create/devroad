import { useLayoutEffect } from 'react';
import { gsap } from 'gsap';

export default function useGsapReveal(ref, dependencies = [], options = {}) {
    useLayoutEffect(() => {
        const root = ref.current;
        if (!root || typeof window === 'undefined') return;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) return;

        const selector = options.selector ?? ':scope > *';
        const elements = Array.from(root.querySelectorAll(selector));
        if (!elements.length) return;

        const context = gsap.context(() => {
            gsap.fromTo(
                elements,
                {
                    autoAlpha: 0,
                    y: options.y ?? 18,
                    scale: options.scale ?? 0.99,
                },
                {
                    autoAlpha: 1,
                    y: 0,
                    scale: 1,
                    duration: options.duration ?? 0.58,
                    delay: options.delay ?? 0.03,
                    stagger: options.stagger ?? 0.055,
                    ease: options.ease ?? 'power3.out',
                    clearProps: 'transform,opacity,visibility',
                },
            );
        }, root);

        return () => context.revert();
    }, dependencies);
}
