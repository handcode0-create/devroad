import { useLayoutEffect } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

if (typeof window !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);
}

export default function useGsapScrollReveal(ref, dependencies = [], options = {}) {
    useLayoutEffect(() => {
        const root = ref.current;
        if (!root || typeof window === 'undefined') return;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) return;

        const selector = options.selector ?? '[data-gsap-reveal]';
        const elements = Array.from(root.querySelectorAll(selector));
        if (!elements.length) return;

        const context = gsap.context(() => {
            gsap.set(elements, {
                autoAlpha: 0,
                y: options.y ?? 22,
            });

            elements.forEach((element, index) => {
                gsap.to(element, {
                    autoAlpha: 1,
                    y: 0,
                    duration: options.duration ?? 0.58,
                    delay: (options.delay ?? 0) + index * (options.stagger ?? 0.055),
                    ease: options.ease ?? 'power3.out',
                    clearProps: 'transform,opacity,visibility',
                    scrollTrigger: {
                        trigger: element,
                        start: options.start ?? 'top 88%',
                        once: true,
                    },
                });
            });
        }, root);

        return () => context.revert();
    }, dependencies);
}
