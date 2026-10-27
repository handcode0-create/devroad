import { Head, Link } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import QRCode from 'qrcode';
import {
    Check,
    Copy,
    Download,
    EllipsisVertical,
    ExternalLink,
    Share,
    Share2,
    SquarePlus,
    TriangleAlert,
} from 'lucide-react';
import { buttonClass } from '@/Components/Ui/buttons';
import {
    canPromptInstall,
    detectDevice,
    isStandalone,
    promptInstall,
    subscribe,
    wasInstalled,
} from '@/pwa';

// Page /installer : installation de DevRoad sans Play Store.
// Elle est ouverte depuis un lien partagé (WhatsApp…) ou un QR code imprimé, puis adapte
// les instructions à l'appareil : bouton natif (Android, ordinateur), guide Safari (iPhone),
// ou sortie du navigateur intégré (Facebook, Instagram, TikTok…) où l'installation est impossible.

const QR_COLORS = { dark: '#08111F', light: '#FFFFFF' };

function useInstallState() {
    const [state, setState] = useState(() => ({
        canPrompt: canPromptInstall(),
        installed: wasInstalled() || isStandalone(),
    }));

    useEffect(() => subscribe(() => setState({
        canPrompt: canPromptInstall(),
        installed: wasInstalled() || isStandalone(),
    })), []);

    return state;
}

function Step({ number, icon: Icon, children }) {
    return (
        <li className="flex items-start gap-3">
            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--dr-accent-soft)] text-sm font-bold text-[var(--dr-accent-text)]">
                {number}
            </span>
            <p className="pt-1 text-[15px] leading-6 text-[var(--dr-text-2)]">
                {children}
                {Icon && (
                    <Icon size={18} aria-hidden="true" className="ml-1.5 inline-block -translate-y-px text-[var(--dr-text)]" />
                )}
            </p>
        </li>
    );
}

function Card({ children, className = '' }) {
    return (
        <section className={`rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 shadow-[var(--dr-shadow)] ${className}`}>
            {children}
        </section>
    );
}

function CardTitle({ children }) {
    return <h2 className="font-display text-lg font-extrabold tracking-[-0.02em] text-[var(--dr-text)]">{children}</h2>;
}

function CopyLinkButton({ url }) {
    const [copied, setCopied] = useState(false);

    const copy = async () => {
        try {
            await navigator.clipboard.writeText(url);
        } catch (e) {
            window.prompt('Copie ce lien :', url);
        }
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <button type="button" onClick={copy} className={`${buttonClass('secondary')} w-full`}>
            {copied ? <Check size={18} aria-hidden="true" /> : <Copy size={18} aria-hidden="true" />}
            {copied ? 'Lien copié' : 'Copier le lien'}
        </button>
    );
}

function InstalledCard() {
    return (
        <Card>
            <div className="flex items-center gap-3">
                <span className="flex h-10 w-10 items-center justify-center rounded-full bg-[rgba(52,211,153,0.15)] text-[var(--dr-success)]">
                    <Check size={22} aria-hidden="true" />
                </span>
                <CardTitle>DevRoad est installé</CardTitle>
            </div>
            <p className="mt-3 text-[15px] leading-6 text-[var(--dr-text-2)]">
                Retrouve l’icône DevRoad sur ton écran d’accueil. Elle s’ouvre en plein écran, comme une application.
            </p>
            <Link href={route('dashboard')} className={`${buttonClass('primary')} mt-5 h-12 w-full text-base`}>
                Ouvrir DevRoad
            </Link>
        </Card>
    );
}

function InAppBrowserCard({ device, pageUrl }) {
    const host = typeof window !== 'undefined' ? window.location.host : '';
    const path = typeof window !== 'undefined' ? window.location.pathname + window.location.search : '/installer';
    const chromeIntent = `intent://${host}${path}#Intent;scheme=https;package=com.android.chrome;end`;

    return (
        <Card>
            <div className="flex items-center gap-3">
                <span className="flex h-10 w-10 items-center justify-center rounded-full bg-[rgba(251,191,36,0.15)] text-[var(--dr-warning)]">
                    <TriangleAlert size={20} aria-hidden="true" />
                </span>
                <CardTitle>Ouvre ce lien dans {device.os === 'ios' ? 'Safari' : 'Chrome'}</CardTitle>
            </div>
            <p className="mt-3 text-[15px] leading-6 text-[var(--dr-text-2)]">
                Tu es dans le navigateur intégré d’une application (Facebook, Instagram, TikTok…). L’installation n’y est pas possible.
            </p>

            {device.os === 'android' ? (
                <a href={chromeIntent} className={`${buttonClass('primary')} mt-5 h-12 w-full text-base`}>
                    <ExternalLink size={18} aria-hidden="true" />
                    Ouvrir dans Chrome
                </a>
            ) : (
                <ol className="mt-4 space-y-3">
                    <Step number={1} icon={EllipsisVertical}>Touche le menu de l’application</Step>
                    <Step number={2}>Choisis « Ouvrir dans Safari » (ou « Ouvrir dans le navigateur »)</Step>
                </ol>
            )}

            <div className="mt-3">
                <CopyLinkButton url={pageUrl} />
            </div>
        </Card>
    );
}

function NativePromptCard({ isDesktop }) {
    const [result, setResult] = useState(null);

    const install = async () => {
        setResult(await promptInstall());
    };

    return (
        <Card>
            <CardTitle>{isDesktop ? 'Installer sur cet ordinateur' : 'Installation en un clic'}</CardTitle>
            <p className="mt-2 text-[15px] leading-6 text-[var(--dr-text-2)]">
                Confirme l’installation dans la fenêtre qui s’ouvre. L’icône DevRoad apparaît ensuite {isDesktop ? 'avec tes applications' : 'sur ton écran d’accueil'}.
            </p>
            <button type="button" onClick={install} className={`${buttonClass('primary')} mt-5 h-12 w-full text-base`}>
                <Download size={20} aria-hidden="true" />
                Installer DevRoad
            </button>
            {result === 'dismissed' && (
                <p className="mt-3 text-sm text-[var(--dr-text-3)]">Installation annulée. Recharge la page pour réessayer.</p>
            )}
        </Card>
    );
}

function AndroidManualCard({ browser }) {
    return (
        <Card>
            <CardTitle>Ajoute DevRoad à ton écran d’accueil</CardTitle>
            {browser === 'samsung' ? (
                <ol className="mt-4 space-y-3">
                    <Step number={1}>Touche le menu ≡ en bas à droite de Samsung Internet</Step>
                    <Step number={2}>Choisis « Ajouter la page à », puis « Écran d’accueil »</Step>
                    <Step number={3}>Confirme avec « Ajouter »</Step>
                </ol>
            ) : (
                <ol className="mt-4 space-y-3">
                    <Step number={1} icon={EllipsisVertical}>Touche le menu en haut à droite du navigateur</Step>
                    <Step number={2}>Choisis « Installer l’application » ou « Ajouter à l’écran d’accueil »</Step>
                    <Step number={3}>Confirme avec « Installer »</Step>
                </ol>
            )}
            <p className="mt-4 text-sm leading-5 text-[var(--dr-text-3)]">
                L’option n’apparaît pas ? Ouvre cette page dans Google Chrome.
            </p>
        </Card>
    );
}

function IosCard({ browser }) {
    const inSafari = browser === 'safari';

    return (
        <Card>
            <CardTitle>Ajoute DevRoad à ton écran d’accueil</CardTitle>
            <ol className="mt-4 space-y-3">
                <Step number={1} icon={Share}>
                    {inSafari
                        ? 'Touche le bouton Partager, en bas de Safari'
                        : 'Touche le bouton Partager, dans la barre d’adresse en haut'}
                </Step>
                <Step number={2} icon={SquarePlus}>Fais défiler et choisis « Sur l’écran d’accueil »</Step>
                <Step number={3}>Touche « Ajouter » en haut à droite</Step>
            </ol>
            {!inSafari && (
                <p className="mt-4 text-sm leading-5 text-[var(--dr-text-3)]">
                    L’option n’apparaît pas ? Ouvre cette page dans Safari.
                </p>
            )}
        </Card>
    );
}

function ShareCard({ installUrl, emphasize }) {
    const [svg, setSvg] = useState('');

    useEffect(() => {
        QRCode.toString(installUrl, { type: 'svg', margin: 1, errorCorrectionLevel: 'M', color: QR_COLORS })
            .then(setSvg)
            .catch(() => setSvg(''));
    }, [installUrl]);

    const qrSrc = useMemo(() => (svg ? `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}` : ''), [svg]);

    const download = () => {
        const blob = new Blob([svg], { type: 'image/svg+xml' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'devroad-qr-installation.svg';
        link.click();
        setTimeout(() => URL.revokeObjectURL(link.href), 1000);
    };

    const canShare = typeof navigator !== 'undefined' && typeof navigator.share === 'function';
    const share = () => {
        navigator.share({
            title: 'DevRoad',
            text: 'Apprends le développement avec DevRoad, gratuitement. Installe l’app ici :',
            url: installUrl,
        }).catch(() => {});
    };

    return (
        <Card>
            <CardTitle>{emphasize ? 'Scanne ce code avec ton téléphone' : 'Partage DevRoad à un camarade'}</CardTitle>
            <p className="mt-2 text-[15px] leading-6 text-[var(--dr-text-2)]">
                {emphasize
                    ? 'Ouvre l’appareil photo de ton téléphone et vise le code : la page d’installation s’ouvre.'
                    : 'Montre-lui ce code ou envoie-lui le lien sur WhatsApp.'}
            </p>

            <div className="mx-auto mt-5 w-full max-w-[220px] rounded-2xl bg-white p-3">
                {qrSrc ? (
                    <img src={qrSrc} alt="QR code vers la page d’installation de DevRoad" className="aspect-square w-full" />
                ) : (
                    <div className="aspect-square w-full animate-pulse rounded-lg bg-black/5" aria-hidden="true" />
                )}
            </div>

            <div className="mt-5 grid gap-3">
                {canShare && (
                    <button type="button" onClick={share} className={`${buttonClass('secondary')} w-full`}>
                        <Share2 size={18} aria-hidden="true" />
                        Partager le lien
                    </button>
                )}
                <CopyLinkButton url={installUrl} />
                <button type="button" onClick={download} disabled={!svg} className={`${buttonClass('secondary')} w-full`}>
                    <Download size={18} aria-hidden="true" />
                    Télécharger le QR code (affiche, flyer)
                </button>
            </div>
        </Card>
    );
}

export default function Install({ installUrl }) {
    const device = useMemo(() => detectDevice(), []);
    const { canPrompt, installed } = useInstallState();
    const pageUrl = typeof window !== 'undefined' ? window.location.href : installUrl;
    const isDesktop = device.os === 'desktop';

    let installCard;
    if (installed) {
        installCard = <InstalledCard />;
    } else if (device.inApp) {
        installCard = <InAppBrowserCard device={device} pageUrl={pageUrl} />;
    } else if (canPrompt) {
        installCard = <NativePromptCard isDesktop={isDesktop} />;
    } else if (device.os === 'ios') {
        installCard = <IosCard browser={device.browser} />;
    } else if (device.os === 'android') {
        installCard = <AndroidManualCard browser={device.browser} />;
    } else {
        installCard = null;
    }

    return (
        <>
            <Head title="Installer l’application" />

            <main className="min-h-dvh bg-[var(--dr-bg)] font-sans text-[var(--dr-text)]">
                <div className="mx-auto flex w-full max-w-[460px] flex-col gap-4 px-4 pb-[max(2rem,env(safe-area-inset-bottom))] pt-[max(2rem,env(safe-area-inset-top))]">
                    <header className="mb-2 flex flex-col items-center text-center">
                        <img
                            src="/icons/icon-192.png"
                            alt=""
                            aria-hidden="true"
                            className="h-20 w-20 rounded-[22px] shadow-[0_0_32px_rgba(255,106,0,.18)]"
                        />
                        <h1 className="mt-5 font-display text-[28px] font-extrabold leading-tight tracking-[-0.04em]">
                            Installer Dev<span className="text-[var(--dr-accent)]">Road</span>
                        </h1>
                        <p className="mt-2 max-w-[340px] text-[15px] leading-6 text-[var(--dr-text-2)]">
                            Gratuit, sans Play Store ni App Store. DevRoad s’ajoute à ton écran d’accueil et s’ouvre comme une vraie application.
                        </p>
                        <ul className="mt-4 flex flex-wrap justify-center gap-2 text-xs font-semibold text-[var(--dr-text-2)]">
                            {['100 % gratuit', 'Léger en data', 'Plein écran'].map((label) => (
                                <li key={label} className="rounded-full border border-[var(--dr-border-2)] px-3 py-1.5">
                                    {label}
                                </li>
                            ))}
                        </ul>
                    </header>

                    {installCard}

                    <ShareCard installUrl={installUrl} emphasize={isDesktop && !installed} />

                    {!installed && (
                        <Link
                            href={route('dashboard')}
                            className="mt-1 text-center text-sm font-medium text-[var(--dr-text-3)] transition hover:text-[var(--dr-text)]"
                        >
                            Continuer sans installer
                        </Link>
                    )}
                </div>
            </main>
        </>
    );
}
