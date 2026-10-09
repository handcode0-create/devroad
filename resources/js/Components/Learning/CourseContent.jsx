import { useMemo, useState } from "react";

/*
 * Rendu d'un chapitre de cours écrit en Markdown simplifié.
 * Gère : titres (##, ###), paragraphes, listes à puces et numérotées, blocs de code (```),
 * encadrés (> **Astuce** …), quiz à choix (:::quiz … :::), gras, italique, code en ligne,
 * ainsi qu'un sommaire généré depuis les titres « ## ».
 * Aucun HTML brut n'est interprété : tout est rendu en éléments React.
 */

const slug = (text) =>
    String(text).toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, "").replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");

const CALLOUTS = {
    astuce: { label: "Astuce", box: "border-[var(--dr-accent)] bg-[var(--dr-accent-soft)]", tag: "text-[var(--dr-accent-text)]" },
    attention: { label: "Attention", box: "border-[var(--dr-warning)] bg-[var(--dr-field)]", tag: "text-[var(--dr-warning)]" },
    erreur: { label: "Erreur fréquente", box: "border-[var(--dr-danger)] bg-[var(--dr-field)]", tag: "text-[var(--dr-danger)]" },
    exemple: { label: "Exemple", box: "border-[var(--dr-border-2)] bg-[var(--dr-field)]", tag: "text-[var(--dr-text)]" },
    retenir: { label: "À retenir", box: "border-[var(--dr-success)] bg-[var(--dr-field)]", tag: "text-[var(--dr-success)]" },
};

function calloutKind(text) {
    const first = text.replace(/^\*\*/, "").toLowerCase();
    if (first.startsWith("astuce")) return "astuce";
    if (first.startsWith("attention")) return "attention";
    if (first.startsWith("erreur")) return "erreur";
    if (first.startsWith("exemple")) return "exemple";
    if (first.startsWith("à retenir") || first.startsWith("retenir")) return "retenir";
    return null;
}

/** Texte courant : `code`, **gras**, *italique*. */
function Inline({ text }) {
    const parts = String(text).split(/(`[^`]+`|\*\*[^*]+\*\*|\*[^*\s][^*]*\*)/g);
    return parts.map((part, index) => {
        if (part.length > 2 && part.startsWith("`") && part.endsWith("`")) {
            return <code key={index} className="rounded-md bg-[var(--dr-field)] px-1.5 py-0.5 font-['JetBrains_Mono',ui-monospace,monospace] text-[0.88em] text-[var(--dr-accent-text)]">{part.slice(1, -1)}</code>;
        }
        if (part.length > 4 && part.startsWith("**") && part.endsWith("**")) {
            return <strong key={index} className="font-semibold text-[var(--dr-text)]">{part.slice(2, -2)}</strong>;
        }
        if (part.length > 2 && part.startsWith("*") && part.endsWith("*")) {
            return <em key={index}>{part.slice(1, -1)}</em>;
        }
        return part;
    });
}

/** Découpe le Markdown en blocs typés, ligne par ligne (les blocs de code gardent leurs lignes vides). */
function parse(content) {
    const lines = String(content ?? "").replace(/\r\n/g, "\n").split("\n");
    const blocks = [];
    let index = 0;

    while (index < lines.length) {
        const line = lines[index];
        const trimmed = line.trim();

        if (!trimmed) { index += 1; continue; }

        if (trimmed.startsWith("```")) {
            const language = trimmed.slice(3).trim();
            const code = [];
            index += 1;
            while (index < lines.length && !lines[index].trim().startsWith("```")) { code.push(lines[index]); index += 1; }
            index += 1;
            blocks.push({ type: "code", language, code: code.join("\n") });
            continue;
        }

        if (trimmed.toLowerCase().startsWith(":::quiz")) {
            const inner = [];
            index += 1;
            while (index < lines.length && lines[index].trim() !== ":::") { inner.push(lines[index]); index += 1; }
            index += 1;
            const question = [];
            const options = [];
            const explanation = [];
            inner.forEach((row) => {
                const text = row.trim();
                const option = text.match(/^- \[( |x|X)\] (.+)$/);
                if (option) options.push({ label: option[2], correct: option[1].toLowerCase() === "x" });
                else if (text.startsWith(">")) explanation.push(text.replace(/^>\s?/, ""));
                else if (text) question.push(text);
            });
            if (options.length) blocks.push({ type: "quiz", question: question.join(" "), options, explanation: explanation.join(" ") });
            continue;
        }

        if (trimmed.startsWith("|")) {
            const rows = [];
            while (index < lines.length && lines[index].trim().startsWith("|")) {
                const cells = lines[index].trim().replace(/^\|/, "").replace(/\|$/, "").split("|").map((cell) => cell.trim());
                if (!cells.every((cell) => /^:?-{2,}:?$/.test(cell))) rows.push(cells);
                index += 1;
            }
            if (rows.length) blocks.push({ type: "table", head: rows[0], rows: rows.slice(1) });
            continue;
        }

        if (trimmed.startsWith("### ")) { blocks.push({ type: "h3", text: trimmed.slice(4) }); index += 1; continue; }
        if (trimmed.startsWith("## ")) { blocks.push({ type: "h2", text: trimmed.slice(3) }); index += 1; continue; }

        if (trimmed.startsWith(">")) {
            const rows = [];
            while (index < lines.length && lines[index].trim().startsWith(">")) { rows.push(lines[index].trim().replace(/^>\s?/, "")); index += 1; }
            blocks.push({ type: "callout", text: rows.join(" ") });
            continue;
        }

        if (/^- /.test(trimmed)) {
            const items = [];
            while (index < lines.length && /^- /.test(lines[index].trim())) { items.push(lines[index].trim().slice(2)); index += 1; }
            blocks.push({ type: "ul", items });
            continue;
        }

        if (/^\d+\. /.test(trimmed)) {
            const items = [];
            while (index < lines.length && /^\d+\. /.test(lines[index].trim())) { items.push(lines[index].trim().replace(/^\d+\. /, "")); index += 1; }
            blocks.push({ type: "ol", items });
            continue;
        }

        const paragraph = [];
        while (index < lines.length && lines[index].trim() && !/^(#{2,3} |```|>|\||- |\d+\. |:::)/.test(lines[index].trim())) { paragraph.push(lines[index].trim()); index += 1; }
        blocks.push({ type: "p", text: paragraph.join(" ") });
    }

    return blocks;
}

function Quiz({ block, number }) {
    const [picked, setPicked] = useState(null);
    const answered = picked !== null;
    const right = answered && block.options[picked]?.correct;

    return (
        <fieldset className="m-0 min-w-0 rounded-2xl border border-[var(--dr-border-2)] bg-[var(--dr-surface)] p-4">
            <legend className="px-1 text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-accent-text)]">Vérifie-toi · Question {number}</legend>
            <p className="m-0 mb-3 text-[15px] font-semibold leading-[1.5] text-[var(--dr-text)]"><Inline text={block.question} /></p>
            <div className="flex flex-col gap-2">
                {block.options.map((option, optionIndex) => {
                    const chosen = picked === optionIndex;
                    const state = !answered ? "" : option.correct ? "border-[var(--dr-success)] bg-[var(--dr-field)]" : chosen ? "border-[var(--dr-danger)] bg-[var(--dr-field)]" : "opacity-70";
                    return (
                        <button
                            key={optionIndex}
                            type="button"
                            disabled={answered}
                            onClick={() => setPicked(optionIndex)}
                            className={"flex min-h-11 items-start gap-3 rounded-xl border border-[var(--dr-border)] px-3.5 py-2.5 text-left text-[15px] leading-[1.5] text-[var(--dr-text)] transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)] " + (answered ? "cursor-default " : "hover:bg-[var(--dr-field)] ") + state}
                        >
                            <span aria-hidden="true" className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-[var(--dr-border-2)] text-[11px] font-bold">{answered ? (option.correct ? "✓" : chosen ? "✕" : "") : String.fromCharCode(65 + optionIndex)}</span>
                            <span><Inline text={option.label} />{answered && option.correct && <span className="sr-only"> (bonne réponse)</span>}</span>
                        </button>
                    );
                })}
            </div>
            {answered && (
                <p role="status" className="m-0 mt-3 text-sm leading-[1.6] text-[var(--dr-text-2)]">
                    <strong className={right ? "text-[var(--dr-success)]" : "text-[var(--dr-danger)]"}>{right ? "Exact." : "Pas tout à fait."}</strong>{" "}
                    {block.explanation && <Inline text={block.explanation} />}
                </p>
            )}
        </fieldset>
    );
}

export default function CourseContent({ content }) {
    const blocks = useMemo(() => parse(content), [content]);
    const sections = blocks.filter((block) => block.type === "h2" && !/^(à retenir|références)/i.test(block.text));
    let quizNumber = 0;

    return (
        <div className="min-w-0 space-y-4 break-words text-[15px] leading-[1.75] text-[var(--dr-text-2)] [overflow-wrap:anywhere]">
            {sections.length > 3 && (
                <nav aria-label="Sommaire du chapitre" className="rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-4">
                    <p className="m-0 mb-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-text-3)]">Dans ce chapitre</p>
                    <ol className="m-0 grid list-none gap-1 p-0 sm:grid-cols-2">
                        {sections.map((section, sectionIndex) => (
                            <li key={section.text}>
                                <a href={"#" + slug(section.text)} className="flex min-h-9 items-center gap-2.5 rounded-lg text-sm text-[var(--dr-text-2)] hover:text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                                    <span aria-hidden="true" className="w-5 shrink-0 text-xs font-bold tabular-nums text-[var(--dr-accent-text)]">{sectionIndex + 1}</span>
                                    <span className="min-w-0">{section.text}</span>
                                </a>
                            </li>
                        ))}
                    </ol>
                </nav>
            )}

            {blocks.map((block, index) => {
                switch (block.type) {
                    case "h2":
                        return <h3 key={index} id={slug(block.text)} className="m-0 scroll-mt-24 border-t border-[var(--dr-border)] pt-6 font-['Manrope',sans-serif] text-xl font-extrabold tracking-[-0.015em] text-[var(--dr-text)]">{block.text}</h3>;
                    case "h3":
                        return <h4 key={index} className="m-0 pt-2 text-base font-bold text-[var(--dr-text)]">{block.text}</h4>;
                    case "ul":
                        return <ul key={index} className="m-0 list-disc space-y-2 pl-5 marker:text-[var(--dr-accent)]">{block.items.map((item, i) => <li key={i}><Inline text={item} /></li>)}</ul>;
                    case "ol":
                        return <ol key={index} className="m-0 list-decimal space-y-2 pl-5 marker:font-bold marker:text-[var(--dr-accent-text)]">{block.items.map((item, i) => <li key={i}><Inline text={item} /></li>)}</ol>;
                    case "table":
                        return (
                            <div key={index} className="overflow-x-auto rounded-xl border border-[var(--dr-border)]" tabIndex={0}>
                                <table className="w-full min-w-[460px] border-collapse text-left text-sm">
                                    <thead className="bg-[var(--dr-field)] text-[var(--dr-text)]">
                                        <tr>{block.head.map((cell, i) => <th key={i} scope="col" className="px-3.5 py-2.5 font-semibold"><Inline text={cell} /></th>)}</tr>
                                    </thead>
                                    <tbody>
                                        {block.rows.map((row, r) => (
                                            <tr key={r} className="border-t border-[var(--dr-border)] align-top">
                                                {row.map((cell, i) => <td key={i} className={"px-3.5 py-2.5 leading-[1.6] " + (i === 0 ? "whitespace-nowrap text-[var(--dr-text)]" : "")}><Inline text={cell} /></td>)}
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        );
                    case "code":
                        return (
                            <figure key={index} className="m-0 overflow-hidden rounded-xl border border-[var(--dr-border)] bg-[var(--dr-field)]">
                                {block.language && <figcaption className="border-b border-[var(--dr-border)] px-3.5 py-1.5 font-['JetBrains_Mono',ui-monospace,monospace] text-[11px] uppercase tracking-[0.1em] text-[var(--dr-text-3)]">{block.language}</figcaption>}
                                <pre tabIndex={0} className="m-0 overflow-x-auto p-3.5 font-['JetBrains_Mono',ui-monospace,monospace] text-[13px] leading-[1.65] text-[var(--dr-text)]"><code>{block.code}</code></pre>
                            </figure>
                        );
                    case "callout": {
                        const kind = calloutKind(block.text);
                        const style = CALLOUTS[kind] ?? CALLOUTS.exemple;
                        const body = kind ? block.text.replace(/^\*\*[^*]+\*\*\s*:?\s*/, "") : block.text;
                        return (
                            <aside key={index} className={"rounded-xl border-l-4 px-4 py-3 " + style.box}>
                                <p className={"m-0 mb-1 text-[11px] font-bold uppercase tracking-[0.12em] " + style.tag}>{style.label}</p>
                                <p className="m-0 text-[15px] leading-[1.65] text-[var(--dr-text)]"><Inline text={body} /></p>
                            </aside>
                        );
                    }
                    case "quiz":
                        quizNumber += 1;
                        return <Quiz key={index} block={block} number={quizNumber} />;
                    default:
                        return <p key={index} className="m-0"><Inline text={block.text} /></p>;
                }
            })}
        </div>
    );
}
