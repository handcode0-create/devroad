// Service worker DevRoad
// - Hors-ligne : les pages (HTML) passent toujours par le réseau ; sans réseau, on affiche /offline.html.
//   Le HTML et les réponses Inertia ne sont jamais mis en cache (données privées, jeton CSRF).
// - Économie de data : les fichiers compilés /build/assets (noms hachés, immuables) sont servis
//   depuis le cache ; les icônes et images publiques sont servies depuis le cache puis rafraîchies.
// - Rappels push : affichage des notifications et ouverture de l'app au clic.
//
// Incrémenter VERSION à chaque changement de ce fichier pour purger les anciens caches.
const VERSION = 'v2';
const STATIC_CACHE = `devroad-static-${VERSION}`;
const RUNTIME_CACHE = `devroad-runtime-${VERSION}`;
const OFFLINE_URL = '/offline.html';
const RUNTIME_MAX_ENTRIES = 120;

const PRECACHE = [
    OFFLINE_URL,
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icondevroad.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys
            .filter((key) => key.startsWith('devroad-') && key !== STATIC_CACHE && key !== RUNTIME_CACHE)
            .map((key) => caches.delete(key)));
        if (self.registration.navigationPreload) {
            await self.registration.navigationPreload.enable();
        }
        await self.clients.claim();
    })());
});

async function trimCache(cacheName, max) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    for (let i = 0; i < keys.length - max; i++) {
        await cache.delete(keys[i]);
    }
}

// Pages : réseau d'abord, page hors-ligne en secours. Rien n'est stocké.
async function handleNavigation(event) {
    try {
        const preload = await event.preloadResponse;
        if (preload) return preload;
        return await fetch(event.request);
    } catch (error) {
        const cache = await caches.open(STATIC_CACHE);
        return (await cache.match(OFFLINE_URL)) || Response.error();
    }
}

// Assets compilés (hachés) : cache d'abord, réseau sinon.
async function cacheFirst(request) {
    const cache = await caches.open(RUNTIME_CACHE);
    const cached = await cache.match(request);
    if (cached) return cached;
    const response = await fetch(request);
    if (response.ok && response.type === 'basic') {
        cache.put(request, response.clone()).then(() => trimCache(RUNTIME_CACHE, RUNTIME_MAX_ENTRIES));
    }
    return response;
}

// Images publiques : réponse immédiate depuis le cache, mise à jour en arrière-plan.
async function staleWhileRevalidate(event) {
    const cache = await caches.open(RUNTIME_CACHE);
    const cached = await cache.match(event.request);
    const network = fetch(event.request).then((response) => {
        if (response.ok && response.type === 'basic') {
            cache.put(event.request, response.clone()).then(() => trimCache(RUNTIME_CACHE, RUNTIME_MAX_ENTRIES));
        }
        return response;
    }).catch(() => cached || Response.error());
    if (cached) {
        event.waitUntil(network.catch(() => {}));
        return cached;
    }
    return network;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (request.mode === 'navigate') {
        event.respondWith(handleNavigation(event));
        return;
    }

    // Requêtes Inertia (données de page privées) : jamais interceptées.
    if (request.headers.get('X-Inertia')) return;

    if (url.pathname.startsWith('/build/assets/')) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Images publiques : /icons/*, /assets/* (logos de technologies) et images à la racine de public/.
    const isPublicImage = /^\/(icons|assets)\//.test(url.pathname) || /^\/[^/]+\.(png|svg|webp|ico)$/.test(url.pathname);
    if (isPublicImage) {
        event.respondWith(staleWhileRevalidate(event));
    }
});

self.addEventListener('push', (event) => {
    let data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) {}
    event.waitUntil(self.registration.showNotification(data.title || 'DevRoad', {
        body: data.body || 'C’est l’heure d’apprendre.',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        tag: 'devroad-reminder',
        renotify: true,
        vibrate: [200, 100, 200],
        data: { url: data.url || '/dashboard' },
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/dashboard';
    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((list) => {
        for (const client of list) {
            if ('focus' in client) { client.navigate(url); return client.focus(); }
        }
        return self.clients.openWindow(url);
    }));
});
