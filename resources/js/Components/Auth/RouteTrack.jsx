import { Check, Flag } from 'lucide-react';

// La « ligne blanche » : les tirets centraux de la route du logo DevRoad.
// Le formulaire est une route ; chaque champ est une étape. La ligne devient
// orange à mesure que les champs sont remplis, jusqu'au bouton d'arrivée.
// Tout est décoratif (aria-hidden) : les libellés et les erreurs restent
// portés par les champs eux-mêmes.
export function RouteTrack({ progress = 0, children }) {
    const p = Math.max(0, Math.min(1, progress));

    return (
        <div className="relative" style={{ '--p': p }}>
            <span
                aria-hidden="true"
                className="road-line absolute bottom-6 left-[13px] top-[50px] text-white/[0.22]"
            />
            <span
                aria-hidden="true"
                className="road-line road-fill absolute bottom-6 left-[13px] top-[50px] text-[#FF6A00]"
            />

            <div className="space-y-5">{children}</div>
        </div>
    );
}

const MARKER = {
    idle: 'border-white/25 bg-[#08111F]',
    done: 'border-[#FF6A00] bg-[#FF6A00] text-[#08111F]',
    error: 'border-red-300 bg-[#08111F]',
};

// Une étape de la route : le repère est aligné sur le centre du champ
// (libellé 20 px + marge 6 px + demi-champ 24 px, moins le demi-repère 14 px).
export function RouteStop({ state = 'idle', children }) {
    return (
        <div className="relative pl-11">
            <span
                aria-hidden="true"
                className={`absolute left-0 top-[36px] flex h-7 w-7 items-center justify-center rounded-full border-2 transition-colors duration-300 ${MARKER[state]}`}
            >
                {state === 'done' && <Check size={14} strokeWidth={3} />}
                {state === 'error' && <span className="h-2 w-2 rounded-full bg-red-300" />}
            </span>

            {children}
        </div>
    );
}

// Ligne sans repère (case « se souvenir de moi », liens) : décalée comme les champs.
export function RouteAside({ children }) {
    return <div className="pl-11">{children}</div>;
}

// L'arrivée : le bouton d'envoi, précédé d'un drapeau.
export function RouteArrival({ reached = false, children }) {
    return (
        <div className="relative pl-11">
            <span
                aria-hidden="true"
                className={`absolute left-0 top-[10px] flex h-7 w-7 items-center justify-center rounded-full border-2 transition-colors duration-300 ${
                    reached
                        ? 'border-[#FF6A00] bg-[#FF6A00] text-[#08111F]'
                        : 'border-white/25 bg-[#08111F] text-white/45'
                }`}
            >
                <Flag size={13} />
            </span>

            {children}
        </div>
    );
}
