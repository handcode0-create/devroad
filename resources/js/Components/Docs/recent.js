// Pages de documentation lues récemment (sur cet appareil uniquement).
const KEY = 'devroad:docs:recent';

export function readRecentDocs() {
    try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch { return []; }
}

export function rememberDoc(item) {
    try {
        const next = [item, ...readRecentDocs().filter((entry) => entry.url !== item.url)].slice(0, 8);
        localStorage.setItem(KEY, JSON.stringify(next));
    } catch {}
}
