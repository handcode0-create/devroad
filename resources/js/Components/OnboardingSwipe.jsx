import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

const ROUTES = ['/','/login','/register'];

export default function OnboardingSwipe({ step, children, className = '' }) {
    const [offset, setOffset] = useState(0);
    const [leaving, setLeaving] = useState(false);
    const start = useRef({ x: 0, y: 0 });
    const dragging = useRef(false);

    const navigate = (direction) => {
        const targetIndex = step + direction;

        if (targetIndex < 0 || targetIndex >= ROUTES.length || leaving) {
            setOffset(0);
            return;
        }

        const nextOffset = direction > 0 ? -window.innerWidth : window.innerWidth;
        setOffset(nextOffset);
        setLeaving(true);
        sessionStorage.setItem('devroad-swipe-direction', direction > 0 ? 'next' : 'prev');

        window.setTimeout(() => {
            router.visit(ROUTES[targetIndex]);
        }, 180);
    };

    useEffect(() => {
        const direction = sessionStorage.getItem('devroad-swipe-direction');
        sessionStorage.removeItem('devroad-swipe-direction');

        if (!direction) return;

        setOffset(direction === 'next' ? window.innerWidth * 0.14 : -window.innerWidth * 0.14);
        requestAnimationFrame(() => setOffset(0));
    }, []);

    const onTouchStart = (event) => {
        if (leaving) return;
        const touch = event.touches[0];
        start.current = { x: touch.clientX, y: touch.clientY };
        dragging.current = true;
    };

    const onTouchMove = (event) => {
        if (!dragging.current || leaving) return;

        const touch = event.touches[0];
        const dx = touch.clientX - start.current.x;
        const dy = touch.clientY - start.current.y;

        if (Math.abs(dx) < Math.abs(dy) || Math.abs(dx) < 8) {
            return;
        }

        const atFirst = step === 0 && dx > 0;
        const atLast = step === 2 && dx < 0;

        if (atFirst || atLast) {
            setOffset(dx * 0.18);
            return;
        }

        setOffset(dx);
    };

    const onTouchEnd = () => {
        if (!dragging.current || leaving) return;

        const distance = offset;
        dragging.current = false;

        if (Math.abs(distance) >= 80) {
            navigate(distance < 0 ? 1 : -1);
        } else {
            setOffset(0);
        }
    };

    return (
        <div
            className={className}
            onTouchStart={onTouchStart}
            onTouchMove={onTouchMove}
            onTouchEnd={onTouchEnd}
            style={{
                transform: `translate3d(${offset}px, 0, 0)`,
                transition: dragging.current ? 'none' : 'transform 180ms cubic-bezier(.22,1,.36,1)',
                touchAction: 'pan-y',
                willChange: 'transform',
            }}
        >
            {children}
        </div>
    );
}
