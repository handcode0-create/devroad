// PWA DevRoad : enregistrement du service worker, invite d'installation et détection de l'appareil.
//
// L'événement « beforeinstallprompt » (Chrome/Edge/Samsung sur Android et desktop) est déclenché
// très tôt : on le capture dès le chargement du module pour pouvoir proposer l'installation plus tard,
// depuis n'importe quelle page (dont /installer). Safari (iOS) ne le déclenche jamais : l'installation
// se fait à la main via Partager > « Sur l'écran d'accueil ».

let deferredPrompt = null;
let installed = false;
const listeners = new Set();

function notify() {
    listeners.forEach((listener) => listener());
}

if (typeof window !== 'undefined') {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        notify();
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        installed = true;
        notify();
    });
}

export function registerServiceWorker() {
    if (typeof window === 'undefined' || !('serviceWorker' in navigator)) return;
    if (!import.meta.env.PROD) return;

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' }).catch(() => {});
    });
}

export function subscribe(listener) {
    listeners.add(listener);
    return () => listeners.delete(listener);
}

export function canPromptInstall() {
    return deferredPrompt !== null;
}

export function wasInstalled() {
    return installed;
}

// Affiche l'invite native. Renvoie 'accepted', 'dismissed' ou 'unavailable'.
export async function promptInstall() {
    if (!deferredPrompt) return 'unavailable';
    const prompt = deferredPrompt;
    deferredPrompt = null;
    notify();
    prompt.prompt();
    const { outcome } = await prompt.userChoice;
    return outcome;
}

export function isStandalone() {
    if (typeof window === 'undefined') return false;
    return window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

// Environnement de l'appareil, pour choisir les instructions d'installation.
// - inApp : navigateur intégré (Facebook, Instagram, TikTok, Snapchat, LinkedIn, WebView Android…)
//   où l'installation est impossible : il faut ouvrir le lien dans Chrome ou Safari.
export function detectDevice() {
    if (typeof navigator === 'undefined') {
        return { os: 'other', browser: 'other', inApp: false };
    }

    const ua = navigator.userAgent || '';
    const ios = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const android = /Android/i.test(ua);
    const inApp = /FBAN|FBAV|FB_IAB|Instagram|BytedanceWebview|musical_ly|TikTok|Snapchat|LinkedInApp|Line\/|GSA\/|; wv\)/i.test(ua);

    let browser = 'other';
    if (ios) {
        if (/CriOS/i.test(ua)) browser = 'chrome';
        else if (/FxiOS/i.test(ua)) browser = 'firefox';
        else if (/EdgiOS/i.test(ua)) browser = 'edge';
        else if (/Safari/i.test(ua)) browser = 'safari';
    } else if (/SamsungBrowser/i.test(ua)) {
        browser = 'samsung';
    } else if (/Firefox/i.test(ua)) {
        browser = 'firefox';
    } else if (/Edg\//i.test(ua)) {
        browser = 'edge';
    } else if (/OPR\/|Opera/i.test(ua)) {
        browser = 'opera';
    } else if (/Chrome/i.test(ua)) {
        browser = 'chrome';
    }

    return { os: ios ? 'ios' : android ? 'android' : 'desktop', browser, inApp };
}
