import { router } from '@inertiajs/react';

/** Les liens internes d'une page de doc (/docs/…) naviguent sans recharger la page. */
export function followInternalLink(event) {
    const link = event.target.closest?.('a[href^="/docs/"]');
    if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;
    event.preventDefault();
    router.visit(link.getAttribute('href'));
}
