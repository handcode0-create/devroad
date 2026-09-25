import { useRef } from 'react';
import useGsapReveal from '@/hooks/useGsapReveal';

export default function GsapReveal({ children, className = '', ...options }) {
    const ref = useRef(null);
    useGsapReveal(ref, [], options);

    return (
        <div ref={ref} className={className}>
            {children}
        </div>
    );
}
