// Le PTY Daytona renvoie du texte de terminal brut : couleurs ANSI, retours chariot, effacements.
// Un <pre> ne les interprète pas, on les nettoie donc avant l'affichage.

const MAX_OUTPUT = 60000;

// CSI (couleurs, curseur, effacement), OSC (titre de fenêtre) et séquences à un caractère.
const ANSI = /\u001b\[[0-?]*[ -/]*[@-~]|\u001b\][^\u0007\u001b]*(?:\u0007|\u001b\\)|\u001b[@-Z\\-_]/g;

/** Ajoute un morceau de sortie PTY au texte déjà affiché. */
export function appendTerminalOutput(current, chunk) {
    const clean = chunk.replace(ANSI, '').replace(/\r\n/g, '\n');
    let text = current;

    for (const char of clean) {
        if (char === '\r') {
            // Retour en début de ligne : la suite écrase la ligne courante (barres de progression).
            text = text.slice(0, text.lastIndexOf('\n') + 1);
        } else if (char === '\b' || char === '\u007f') {
            if (text.length > 0 && !text.endsWith('\n')) text = text.slice(0, -1);
        } else if (char === '\u0007' || char === '\u0000') {
            // Bip et caractères nuls : ignorés.
        } else {
            text += char;
        }
    }

    return text.length > MAX_OUTPUT ? text.slice(text.length - MAX_OUTPUT) : text;
}
