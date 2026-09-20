// Petites règles côté navigateur, uniquement pour faire avancer la route dessinée
// à côté du formulaire. La vraie validation reste celle de Laravel.
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

export const isName = (value) => value.trim().length >= 2;
export const isEmail = (value) => EMAIL.test(value.trim());
export const isPassword = (value) => value.length >= 8;
export const isSame = (password, confirmation) =>
    confirmation.length >= 8 && password === confirmation;

// Part de la route parcourue : chaque champ complété rapproche du suivant, et le
// dernier ouvre la route jusqu'au bouton d'arrivée.
export function routeProgress(checks) {
    return checks.length === 0 ? 0 : checks.filter(Boolean).length / checks.length;
}
