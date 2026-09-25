import { useLayoutEffect, useRef } from 'react';
import { gsap } from 'gsap';
import LoadingSpinner from './LoadingSpinner';

export default function LoadingOverlay({ visible = false, label = 'Chargement...' }) {
    const rootRef = useRef(null);

    useLayoutEffect(() => {
        if (!visible || !rootRef.current || typeof window === 'undefined') return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const context = gsap.context(() => {
            gsap.fromTo('[data-loading-panel]', { autoAlpha: 0, y: -10, scale: 0.97 }, { autoAlpha: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out' });
            gsap.fromTo('[data-loading-bar]', { scaleX: 0 }, { scaleX: 1, transformOrigin: 'left center', duration: 0.9, ease: 'power2.out', repeat: -1, yoyo: true });
        }, rootRef.current);

        return () => context.revert();
    }, [visible]);

    if (!visible) return null;

    return (
        <div
            className="fixed inset-0 z-[200] flex items-center justify-center bg-[#050B12]/45 px-4 backdrop-blur-[2px]"
            role="status"
            aria-live="polite"
            aria-busy="true"
        >
            <div data-loading-panel className="w-full max-w-[280px] rounded-2xl border border-white/[0.08] bg-[#0D1725]/95 px-5 py-4 shadow-[0_24px_80px_rgba(0,0,0,0.42)]">
                <div className="h-1 overflow-hidden rounded-full bg-white/[0.06]">
                    <div data-loading-bar className="h-full w-1/3 rounded-full bg-[#FF6A00] shadow-[0_0_18px_rgba(255,106,0,0.8)] motion-reduce:animate-none" />
                </div>

                <div className="mt-4 flex items-center justify-center text-sm font-medium text-slate-300">
                    <LoadingSpinner size={17} label={label} />
                </div>
            </div>
        </div>
    );
}
