import { useEffect, useState } from 'react';

const DAYS = [
    { value: 1, label: 'Lun' },
    { value: 2, label: 'Mar' },
    { value: 3, label: 'Mer' },
    { value: 4, label: 'Jeu' },
    { value: 5, label: 'Ven' },
    { value: 6, label: 'Sam' },
    { value: 7, label: 'Dim' },
];

function urlBase64ToUint8Array(base64) {
    const padding = '='.repeat((4 - (base64.length % 4)) % 4);
    const raw = atob((base64 + padding).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function call(method, url, body) {
    return fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(body),
        credentials: 'same-origin',
    });
}

/** Heure, jours et activation des notifications sur cet appareil. */
export default function PushReminders({ time, days, onTimeChange, onDaysChange, vapidPublicKey }) {
    const supported = typeof window !== 'undefined'
        && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    const [enabled, setEnabled] = useState(false);
    const [busy, setBusy] = useState(false);
    const [message, setMessage] = useState('');

    useEffect(() => {
        if (!supported) return;
        navigator.serviceWorker.register('/sw.js')
            .then((reg) => reg.pushManager.getSubscription())
            .then((sub) => setEnabled(Boolean(sub) && Notification.permission === 'granted'))
            .catch(() => {});
    }, [supported]);

    async function enable() {
        setBusy(true);
        setMessage('');
        try {
            if (!vapidPublicKey) throw new Error('Les notifications ne sont pas encore configurées sur le serveur.');
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') throw new Error('Notifications refusées : autorise-les dans les réglages du navigateur.');
            const reg = await navigator.serviceWorker.register('/sw.js');
            await navigator.serviceWorker.ready;
            const sub = (await reg.pushManager.getSubscription())
                ?? await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                });
            const json = sub.toJSON();
            const res = await call('POST', '/push-subscription', {
                endpoint: json.endpoint,
                keys: json.keys,
                contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
            });
            if (!res.ok) throw new Error('Impossible d’enregistrer cet appareil.');
            setEnabled(true);
            setMessage('Notifications activées sur cet appareil.');
        } catch (e) {
            setMessage(e.message || 'Échec de l’activation.');
        } finally {
            setBusy(false);
        }
    }

    async function disable() {
        setBusy(true);
        try {
            const reg = await navigator.serviceWorker.getRegistration('/sw.js');
            const sub = await reg?.pushManager.getSubscription();
            if (sub) {
                await call('DELETE', '/push-subscription', { endpoint: sub.endpoint });
                await sub.unsubscribe();
            }
            setEnabled(false);
            setMessage('Notifications coupées sur cet appareil.');
        } finally {
            setBusy(false);
        }
    }

    function toggleDay(value) {
        const next = days.includes(value) ? days.filter((d) => d !== value) : [...days, value].sort();
        if (next.length > 0) onDaysChange(next);
    }

    return (
        <div className="space-y-4 rounded-2xl border border-[var(--dr-border)] p-4">
            <div className="grid gap-4 sm:grid-cols-[10rem_1fr]">
                <label className="block text-sm font-semibold">
                    Heure du rappel
                    <input
                        type="time"
                        value={time}
                        onChange={(e) => onTimeChange(e.target.value)}
                        className="mt-1.5 block w-full rounded-xl border border-[var(--dr-border)] bg-transparent px-3 py-2"
                    />
                </label>
                <div>
                    <p className="text-sm font-semibold">Jours</p>
                    <div className="mt-1.5 flex flex-wrap gap-2">
                        {DAYS.map((d) => (
                            <button
                                key={d.value}
                                type="button"
                                onClick={() => toggleDay(d.value)}
                                aria-pressed={days.includes(d.value)}
                                className={`rounded-full px-3 py-1.5 text-xs font-bold transition ${days.includes(d.value)
                                    ? 'bg-[#FF6A00] text-[var(--dr-ink)]'
                                    : 'border border-[var(--dr-border)]'}`}
                            >
                                {d.label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            {supported ? (
                <div className="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        disabled={busy}
                        onClick={enabled ? disable : enable}
                        className="rounded-xl border border-[var(--dr-border)] px-4 py-2 text-sm font-bold disabled:opacity-50"
                    >
                        {enabled ? 'Couper sur cet appareil' : 'Activer sur cet appareil'}
                    </button>
                    {message && <span className="text-xs font-semibold text-[var(--dr-accent-text)]">{message}</span>}
                </div>
            ) : (
                <p className="text-xs text-[var(--dr-muted,inherit)]">
                    Ce navigateur ne gère pas les notifications. Sur iPhone, ajoute d’abord DevRoad à l’écran d’accueil (Partager, puis « Sur l’écran d’accueil »).
                </p>
            )}
            <p className="text-xs opacity-70">
                Le rappel sonne avec le son de notification de ton téléphone : règle-le dans les réglages de ton téléphone (notifications, DevRoad). N’oublie pas d’enregistrer les préférences pour l’heure et les jours.
            </p>
        </div>
    );
}
