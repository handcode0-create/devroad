// Service worker DevRoad : affiche les rappels push et ouvre l'app au clic.
self.addEventListener('push', (event) => {
    let data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) {}
    event.waitUntil(self.registration.showNotification(data.title || 'DevRoad', {
        body: data.body || 'C’est l’heure d’apprendre.',
        icon: '/icondevroad.png',
        badge: '/icondevroad.png',
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
