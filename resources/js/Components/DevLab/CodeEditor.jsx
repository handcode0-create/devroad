import { useEffect, useMemo, useRef, useState } from "react";
import Prism from "prismjs";
import "prismjs/components/prism-jsx";
import { Check, ChevronDown, Clipboard, FileCode2, Trash2 } from "lucide-react";

const HTML_TAGS = [
    "html", "head", "title", "meta", "link", "style", "script", "body",
    "header", "nav", "main", "section", "article", "aside", "footer",
    "div", "span", "p", "a", "button", "input", "textarea", "select",
    "option", "label", "form", "ul", "ol", "li", "table", "thead", "tbody",
    "tr", "th", "td", "img", "video", "audio", "canvas", "iframe",
    "h1", "h2", "h3", "h4", "h5", "h6", "strong", "em", "small", "br",
];

const CSS_PROPERTIES = [
    "display", "position", "top", "right", "bottom", "left", "z-index",
    "width", "min-width", "max-width", "height", "min-height", "max-height",
    "margin", "margin-top", "margin-right", "margin-bottom", "margin-left",
    "padding", "padding-top", "padding-right", "padding-bottom", "padding-left",
    "gap", "grid-template-columns", "grid-template-rows", "flex-direction",
    "justify-content", "align-items", "align-content", "flex-wrap",
    "color", "background", "background-color", "border", "border-radius",
    "box-shadow", "font-family", "font-size", "font-weight", "line-height",
    "letter-spacing", "text-align", "text-decoration", "opacity",
    "overflow", "overflow-x", "overflow-y", "cursor", "transition",
    "transform", "object-fit", "content",
];

const JS_COMPLETIONS = [
    "const", "let", "var", "function", "return", "if", "else", "for",
    "while", "switch", "case", "break", "continue", "async", "await",
    "class", "new", "import", "export", "from", "try", "catch", "throw",
    "true", "false", "null", "undefined", "console", "document", "window",
    "querySelector", "addEventListener", "setTimeout", "setInterval",
    "fetch", "JSON", "Array", "Object", "Math", "Promise",
];

const VOID_TAGS = new Set(["area", "base", "br", "col", "embed", "hr", "img", "input", "link", "meta", "param", "source", "track", "wbr"]);

function languageFor(path = "") {
    const lower = path.toLowerCase();
    if (lower.endsWith(".html") || lower.endsWith(".htm") || lower.endsWith(".blade.php")) return "html";
    if (lower.endsWith(".css") || lower.endsWith(".scss")) return "css";
    if (lower.endsWith(".js") || lower.endsWith(".jsx") || lower.endsWith(".mjs") || lower.endsWith(".cjs")) return "javascript";
    return "plain";
}
function prismLanguageFor(path = "") {
    const lower = path.toLowerCase();
    if (lower.endsWith(".jsx")) return "jsx";
    if (lower.endsWith(".html") || lower.endsWith(".htm") || lower.endsWith(".blade.php")) return "markup";
    if (lower.endsWith(".css") || lower.endsWith(".scss")) return "css";
    if (lower.endsWith(".js") || lower.endsWith(".mjs") || lower.endsWith(".cjs")) return "javascript";
    return "plain";
}

function highlightCode(content, path) {
    const language = prismLanguageFor(path);
    if (language === "plain" || !Prism.languages[language]) {
        return escapeHtml(content);
    }

    return Prism.highlight(content || " ", Prism.languages[language], language);
}

function escapeHtml(value = "") {
    return value
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}

function completionData(content, cursor, language) {
    if (language === "html") {
        const before = content.slice(0, cursor);
        const match = before.match(/<\/?([A-Za-z][\w-]*)?$/);
        if (!match) return null;

        const closing = before.endsWith("</") || /<\/[^>]*$/.test(before);
        const query = match[1] ?? "";
        const items = HTML_TAGS.filter((tag) => tag.startsWith(query.toLowerCase())).slice(0, 8);
        if (!items.length) return null;

        return {
            query,
            items,
            label: "HTML",
            replaceStart: cursor - (query.length + (closing ? 2 : 1)),
            apply(item) {
                if (closing) return { text: `</${item}>`, cursorOffset: item.length + 3 };
                if (VOID_TAGS.has(item)) return { text: `<${item}>`, cursorOffset: item.length + 2 };
                return { text: `<${item}></${item}>`, cursorOffset: item.length + 2 };
            },
        };
    }

    if (language === "css") {
        const before = content.slice(0, cursor);
        const blockStart = before.lastIndexOf("{");
        const blockEnd = before.lastIndexOf("}");
        if (blockStart < blockEnd) return null;

        const match = before.match(/(?:^|[;{\s])([A-Za-z-]+)$/);
        if (!match) return null;
        const query = match[1] ?? "";
        if (!query) return null;
        const items = CSS_PROPERTIES.filter((property) => property.startsWith(query.toLowerCase())).slice(0, 8);
        if (!items.length) return null;

        return {
            query,
            items,
            label: "CSS",
            replaceStart: cursor - query.length,
            apply(item) {
                return { text: `${item}: `, cursorOffset: item.length + 2 };
            },
        };
    }

    if (language === "javascript") {
        const before = content.slice(0, cursor);
        const match = before.match(/(?:^|[\s.(,=;:{}])([A-Za-z_$][\w$]*)$/);
        if (!match) return null;
        const query = match[1] ?? "";
        if (query.length < 1) return null;

        const items = JS_COMPLETIONS.filter((item) => item.toLowerCase().startsWith(query.toLowerCase())).slice(0, 8);
        if (!items.length) return null;

        return {
            query,
            items,
            label: "JS",
            replaceStart: cursor - query.length,
            apply(item) {
                if (item === "console") return { text: "console.log()", cursorOffset: item.length + 5 };
                if (item === "fetch") return { text: "fetch()", cursorOffset: item.length + 1 };
                if (item === "addEventListener") return { text: "addEventListener()", cursorOffset: item.length + 1 };
                return { text: item + " ", cursorOffset: item.length + 1 };
            },
        };
    }

    return null;
}

export default function CodeEditor({
    activeFile,
    currentFile,
    lineCount,
    copied,
    onChange,
    onCopy,
    onDelete,
    editorRef,
}) {
    const [selectedCompletion, setSelectedCompletion] = useState(0);
    const [cursorPosition, setCursorPosition] = useState(0);
    const [completionPosition, setCompletionPosition] = useState({ top: 12, left: 12 });
    const editorContainerRef = useRef(null);
    const highlightRef = useRef(null);
    const language = useMemo(() => languageFor(activeFile), [activeFile]);
    const completions = useMemo(
        () => completionData(currentFile?.content ?? "", cursorPosition, language),
        [currentFile?.content, cursorPosition, language]
    );
    const highlightedCode = useMemo(
        () => highlightCode(currentFile?.content ?? "", activeFile),
        [currentFile?.content, activeFile]
    );

    function updateCursorState(textarea = editorRef.current) {
        if (!textarea) return;

        const cursor = textarea.selectionStart ?? 0;
        setCursorPosition(cursor);

        const value = textarea.value ?? "";

        if (highlightRef.current) {
            highlightRef.current.style.transform =
                "translate(" + (-textarea.scrollLeft) + "px, " + (-textarea.scrollTop) + "px)";
        }
        const beforeCursor = value.slice(0, cursor);
        const lineIndex = beforeCursor.split("\n").length - 1;
        const columnIndex = beforeCursor.length - (beforeCursor.lastIndexOf("\n") + 1);

        const lineHeight = 24;
        const charWidth = 7.2;
        const padding = 16;
        const container = editorContainerRef.current;

        if (!container) return;

        const maxLeft = Math.max(12, container.clientWidth - 292);
        const maxTop = Math.max(12, container.clientHeight - 190);

        setCompletionPosition({
            left: Math.min(
                maxLeft,
                Math.max(12, padding + (columnIndex * charWidth) - textarea.scrollLeft),
            ),
            top: Math.min(
                maxTop,
                Math.max(12, padding + (lineIndex * lineHeight) - textarea.scrollTop + 4),
            ),
        });
    }

    useEffect(() => {
        setSelectedCompletion(0);
    }, [completions?.query, activeFile]);

    useEffect(() => {
        setCursorPosition(0);
        setCompletionPosition({ top: 12, left: 12 });
    }, [activeFile]);

    function applyCompletion(item) {
        const textarea = editorRef.current;
        if (!textarea || !completions) return;

        const cursor = textarea.selectionStart ?? 0;
        const value = currentFile?.content ?? "";
        const result = completions.apply(item);
        const nextValue = value.slice(0, completions.replaceStart) + result.text + value.slice(cursor);

        onChange(nextValue);

        requestAnimationFrame(() => {
            textarea.focus();
            const nextCursor = completions.replaceStart + result.cursorOffset;
            textarea.setSelectionRange(nextCursor, nextCursor);
            updateCursorState(textarea);
        });
    }

    function handleKeyDown(event) {
        if (!completions || !completions.items.length) return;

        if (event.key === "ArrowDown") {
            event.preventDefault();
            setSelectedCompletion((value) => Math.min(value + 1, completions.items.length - 1));
            return;
        }

        if (event.key === "ArrowUp") {
            event.preventDefault();
            setSelectedCompletion((value) => Math.max(value - 1, 0));
            return;
        }

        if (event.key === "Tab" || event.key === "Enter") {
            event.preventDefault();
            applyCompletion(completions.items[selectedCompletion]);
        }

        if (event.key === "Escape") {
            event.preventDefault();
        }
    }

    return (
        <>
            <div className="flex items-center justify-between border-b border-white/[0.05] bg-[#0D1725] px-3 py-2">
                <div className="flex min-w-0 items-center gap-2 text-[10px] text-slate-600">
                    <FileCode2 size={12} className="shrink-0" />
                    <span className="truncate">{activeFile}</span>
                    <span className="shrink-0">{lineCount} lignes</span>
                    {language !== "plain" && (
                        <span className="hidden rounded-md bg-white/[0.04] px-1.5 py-0.5 font-bold uppercase tracking-wide text-slate-500 sm:inline">
                            {language === "javascript" ? "JS" : language}
                        </span>
                    )}
                </div>

                <div className="flex items-center gap-1">
                    <button
                        type="button"
                        onClick={onCopy}
                        className="rounded-lg p-2 text-slate-600 hover:bg-white/[0.05] hover:text-white"
                        aria-label="Copier"
                    >
                        {copied ? <Check size={13} /> : <Clipboard size={13} />}
                    </button>

                    <button
                        type="button"
                        onClick={onDelete}
                        className="rounded-lg p-2 text-slate-600 hover:bg-red-500/10 hover:text-red-400"
                        aria-label="Supprimer le fichier"
                    >
                        <Trash2 size={13} />
                    </button>
                </div>
            </div>

            <div ref={editorContainerRef} className="relative flex min-h-0 flex-1 bg-[#06101A]">
                <div className="w-11 shrink-0 overflow-hidden border-r border-white/[0.04] bg-[#08111C] py-3 text-right font-mono text-[10px] leading-6 text-slate-700">
                    {Array.from({ length: lineCount }, (_, index) => (
                        <div key={index} className="pr-2">{index + 1}</div>
                    ))}
                </div>

                <div className="relative min-w-0 flex-1 overflow-hidden">
                    <pre
                        ref={highlightRef}
                        aria-hidden="true"
                        className="devlab-syntax pointer-events-none absolute left-0 top-0 m-0 min-w-full whitespace-pre p-4 font-mono text-[12px] leading-6"
                        style={{ willChange: "transform" }}
                    ><code dangerouslySetInnerHTML={{ __html: highlightedCode }} /></pre>

                    <textarea
                        ref={editorRef}
                        value={currentFile?.content ?? ""}
                        onChange={(event) => {
                            const textarea = event.currentTarget;
                            onChange(event.target.value);
                            requestAnimationFrame(() => updateCursorState(textarea));
                        }}
                        onSelect={(event) => updateCursorState(event.currentTarget)}
                        onClick={(event) => updateCursorState(event.currentTarget)}
                        onKeyUp={(event) => updateCursorState(event.currentTarget)}
                        onScroll={(event) => updateCursorState(event.currentTarget)}
                        onKeyDown={handleKeyDown}
                        spellCheck={false}
                        autoCapitalize="off"
                        autoCorrect="off"
                        wrap="off"
                        className="relative z-10 h-full w-full resize-none border-0 bg-transparent p-4 font-mono text-[12px] leading-6 text-transparent caret-[#FF6A00] outline-none selection:bg-[#FF6A00]/20 selection:text-transparent focus:ring-0"
                        aria-label={"Éditeur " + activeFile}
                    />
                </div>

                {completions && (
                    <div
                        className="absolute z-20 w-[min(280px,calc(100%-16px))] overflow-hidden rounded-xl border border-white/[0.08] bg-[#0B1523] shadow-2xl"
                        style={{ left: completionPosition.left, top: completionPosition.top }}
                    >
                        <div className="flex items-center justify-between border-b border-white/[0.06] px-3 py-2 text-[9px] font-bold uppercase tracking-[0.12em] text-slate-600">
                            <span>Complétion {completions.label}</span>
                            <span className="flex items-center gap-1"><ChevronDown size={11} /> ↑↓ · Tab</span>
                        </div>
                        <div className="max-h-44 overflow-y-auto p-1">
                            {completions.items.map((item, index) => (
                                <button
                                    key={item}
                                    type="button"
                                    onMouseDown={(event) => {
                                        event.preventDefault();
                                        applyCompletion(item);
                                    }}
                                    className={
                                        "flex w-full items-center justify-between rounded-lg px-2.5 py-2 text-left font-mono text-[11px] " +
                                        (index === selectedCompletion
                                            ? "bg-[#FF6A00] text-[#08111F]"
                                            : "text-slate-300 hover:bg-white/[0.05]")
                                    }
                                >
                                    <span>{item}</span>
                                    <span className={index === selectedCompletion ? "text-[#08111F]/60" : "text-slate-700"}>
                                        {completions.label}
                                    </span>
                                </button>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}
