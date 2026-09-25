import { useEffect, useMemo, useRef, useState } from 'react';
import { AlignCenter, AlignJustify, AlignLeft, AlignRight, CaseLower, CaseUpper, CheckSquare, Code2, FileText, Heading1, Heading2, Heading3, Image as ImageIcon, List, ListOrdered, Minus, Paperclip, Plus, Quote, Sparkles, Type } from 'lucide-react';

const FONTS = [
    { value: 'Inter', label: 'Inter' },
    { value: 'Poppins', label: 'Poppins' },
    { value: 'Space Grotesk', label: 'Space Grotesk' },
    { value: 'JetBrains Mono', label: 'JetBrains Mono' },
    { value: 'Manrope', label: 'Manrope' },
    { value: 'Lora', label: 'Lora' },
];

export const MEMO_FONT_STACKS = {
    Inter: "'Inter', system-ui, sans-serif",
    Poppins: "'Poppins', system-ui, sans-serif",
    'Space Grotesk': "'Space Grotesk', system-ui, sans-serif",
    'JetBrains Mono': "'JetBrains Mono', ui-monospace, SFMono-Regular, monospace",
    Manrope: "'Manrope', system-ui, sans-serif",
    Lora: "'Lora', Georgia, serif",
};

function stripInlineCaseMarkers(value) {
    return String(value ?? '').replace(/\[\[upper\]\]|\[\[\/upper\]\]|\[\[lower\]\]|\[\[\/lower\]\]|\[\[capitalize\]\]|\[\[\/capitalize\]\]/g, '');
}

export function normalizeMemoContent(value) {
    return String(value ?? '')
        .replace(/\[\[upper\]\]([\s\S]*?)\[\[\/upper\]\]/g, (_, text) => text.toUpperCase())
        .replace(/\[\[lower\]\]([\s\S]*?)\[\[\/lower\]\]/g, (_, text) => text.toLowerCase())
        .replace(/\[\[capitalize\]\]([\s\S]*?)\[\[\/capitalize\]\]/g, (_, text) => text.replace(/(^|[\s-])\p{L}/gu, (match) => match.toUpperCase()));
}
const DEFAULT_FORMATTING = { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 };
const BLOCKS = [
    { key: 'h1', label: 'Titre 1', prefix: '# ', icon: Heading1 },
    { key: 'h2', label: 'Titre 2', prefix: '## ', icon: Heading2 },
    { key: 'h3', label: 'Titre 3', prefix: '### ', icon: Heading3 },
    { key: 'bullet', label: 'Liste', prefix: '- ', icon: List },
    { key: 'number', label: 'Liste numérotée', prefix: '1. ', icon: ListOrdered },
    { key: 'check', label: 'Checklist', prefix: '- [ ] ', icon: CheckSquare },
    { key: 'quote', label: 'Citation', prefix: '> ', icon: Quote },
];

export function defaultMemoFormatting(value = {}) { return { ...DEFAULT_FORMATTING, ...value }; }
export function autoMemoTitle(content) { const firstLine = String(content ?? '').split('\n').map((line) => stripInlineCaseMarkers(line).replace(/<[^>]*>/g, '').replace(/^\s*#+\s*/, '').trim()).find(Boolean); return firstLine ? firstLine.replace(/[`*_>#]/g, '').trim().slice(0, 255) : ''; }

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function memoValueToHtml(value) {
    const raw = String(value ?? '');
    if (/<(?:p|div|h[1-6]|strong|b|ul|ol|li|blockquote|pre|code|br|hr|a|img|figure)\\b/i.test(raw)) {
        return sanitizeMemoHtml(raw);
    }

    return raw
        .split(/\\n{2,}/)
        .map((block) => {
            const line = block.trim();
            if (!line) return '';
            if (/^###\\s+/.test(line)) return '<h4>' + escapeHtml(line.replace(/^###\\s+/, '')) + '</h4>';
            if (/^##\\s+/.test(line)) return '<h3>' + escapeHtml(line.replace(/^##\\s+/, '')) + '</h3>';
            if (/^#\\s+/.test(line)) return '<h2>' + escapeHtml(line.replace(/^#\\s+/, '')) + '</h2>';
            if (/^>\\s?/.test(line)) return '<blockquote>' + escapeHtml(line.replace(/^>\\s?/, '')) + '</blockquote>';
            if (/^[-*]\\s+/.test(line)) return '<ul>' + line.split(/\\n/).map((item) => '<li>' + escapeHtml(item.replace(/^[-*]\\s+/, '')) + '</li>').join('') + '</ul>';
            if (/^\\d+\\.\\s+/.test(line)) return '<ol>' + line.split(/\\n/).map((item) => '<li>' + escapeHtml(item.replace(/^\\d+\\.\\s+/, '')) + '</li>').join('') + '</ol>';
            return '<p>' + escapeHtml(line).replace(/\\*\\*([^*]+)\\*\\*/g, '<strong>$1</strong>') + '</p>';
        })
        .join('');
}

export function sanitizeMemoHtml(value) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(String(value ?? ''), 'text/html');
    const allowed = new Set(['P','DIV','BR','H2','H3','H4','STRONG','B','EM','I','U','S','UL','OL','LI','BLOCKQUOTE','PRE','CODE','HR','SPAN','FONT','A','IMG','FIGURE']);
    const localAttachmentUrl = (url, allowDownload = true) => {
        const value = String(url ?? '');
        return allowDownload
            ? /^\/memos\/attachments\/[0-9]+(?:\/download)?$/.test(value)
            : /^\/memos\/attachments\/[0-9]+$/.test(value);
    };

    doc.body.querySelectorAll('*').forEach((node) => {
        if (!allowed.has(node.tagName)) {
            node.replaceWith(...Array.from(node.childNodes));
            return;
        }

        const attributes = Array.from(node.attributes);
        while (node.attributes.length > 0) {
            node.removeAttribute(node.attributes[0].name);
        }

        attributes.forEach((attribute) => {
            const name = attribute.name.toLowerCase();
            const value = attribute.value;

            if (node.tagName === 'IMG' && ['src', 'alt'].includes(name)) {
                if (name === 'src' && localAttachmentUrl(value, false)) node.setAttribute(name, value);
                if (name === 'alt') node.setAttribute(name, value);
                return;
            }

            if (node.tagName === 'A' && name === 'href' && localAttachmentUrl(value)) {
                node.setAttribute('href', value);
                return;
            }

            if (node.tagName === 'FIGURE' && name === 'data-attachment-id' && /^\d+$/.test(value)) {
                node.setAttribute(name, value);
                return;
            }

            if (node.tagName === 'FONT' && ['face', 'size'].includes(name)) {
                node.setAttribute(name, value);
            }
        });
    });

    return doc.body.innerHTML;
}

export default function MemoEditor({ content, onContentChange, formatting, onFormattingChange, onAutoTitle, attachments = [] }) {
    const style = useMemo(() => defaultMemoFormatting(formatting), [formatting]);
    const editorRef = useRef(null);
    const [slashOpen, setSlashOpen] = useState(false);
    const initializedRef = useRef(false);
    const lastEmittedContentRef = useRef(null);

    useEffect(() => {
        const editor = editorRef.current;
        if (!editor) return;
        const next = sanitizeMemoHtml(content ?? '');
        if (!initializedRef.current) return;
        if (lastEmittedContentRef.current === next) return;
        if (sanitizeMemoHtml(editor.innerHTML) === next) {
            lastEmittedContentRef.current = next;
            return;
        }
        editor.innerHTML = memoValueToHtml(content ?? '');
        lastEmittedContentRef.current = next;
    }, [content]);

    function update(key, value) {
        onFormattingChange({ ...style, [key]: value });
    }

    function focusEditor() {
        editorRef.current?.focus();
    }

    function emitContent() {
        const html = sanitizeMemoHtml(editorRef.current?.innerHTML ?? '');
        lastEmittedContentRef.current = html;
        onContentChange(html);
        return html;
    }

    function restoreSelection() {
        focusEditor();
    }

    function runCommand(command, value = null) {
        focusEditor();
        document.execCommand(command, false, value);
        emitContent();
        restoreSelection();
    }

    function applyCase(mode) {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0 || selection.isCollapsed) {
            update('textTransform', mode);
            return;
        }

        const range = selection.getRangeAt(0);
        if (!editorRef.current?.contains(range.commonAncestorContainer)) return;

        const fragment = range.cloneContents();
        const walker = document.createTreeWalker(fragment, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach((node) => {
            if (mode === 'uppercase') node.nodeValue = node.nodeValue.toUpperCase();
            if (mode === 'lowercase') node.nodeValue = node.nodeValue.toLowerCase();
            if (mode === 'capitalize') node.nodeValue = node.nodeValue.replace(/(^|[\s-])\p{L}/gu, (match) => match.toUpperCase());
        });

        range.deleteContents();
        range.insertNode(fragment);
        selection.removeAllRanges();
        selection.addRange(range);
        onFormattingChange({ ...style, textTransform: 'none' });
        emitContent();
    }

    function applyBlock(type) {
        focusEditor();
        if (type === 'h1') document.execCommand('formatBlock', false, 'h2');
        if (type === 'h2') document.execCommand('formatBlock', false, 'h3');
        if (type === 'h3') document.execCommand('formatBlock', false, 'h4');
        if (type === 'bullet') document.execCommand('insertUnorderedList');
        if (type === 'number') document.execCommand('insertOrderedList');
        if (type === 'quote') document.execCommand('formatBlock', false, 'blockquote');
        if (type === 'check') document.execCommand('insertUnorderedList');
        emitContent();
    }

    function applyFont(font) {
        focusEditor();
        document.execCommand('fontName', false, font);
        emitContent();
    }

    function adjustSize(delta) {
        const next = Math.min(32, Math.max(12, Number(style.fontSize) + delta));
        update('fontSize', next);
    }

    function insertCode() {
        focusEditor();
        document.execCommand('formatBlock', false, 'pre');
        emitContent();
    }

    function insertSeparator() {
        focusEditor();
        document.execCommand('insertHorizontalRule');
        emitContent();
    }

    function insertText(text) {
        focusEditor();
        document.execCommand('insertText', false, text);
        emitContent();
    }

    function currentSlashQuery() {
        const text = editorRef.current?.innerText ?? '';
        const line = text.split(/\n/).pop()?.trim() ?? '';
        const match = line.match(/^\/([a-z0-9-]*)$/i);
        return match ? match[1].toLowerCase() : null;
    }

    function insertInlineHtml(html) {
        focusEditor();
        document.execCommand('insertHTML', false, html);
        emitContent();
    }

    function insertAttachmentInline(attachment) {
        if (!attachment) return;
        if (attachment.is_image) {
            insertInlineHtml('<figure data-attachment-id="' + attachment.id + '"><img src="' + attachment.url + '" alt="' + escapeHtml(attachment.name) + '"></figure>');
        } else {
            insertInlineHtml('<p><a href="' + attachment.url + '">📎 ' + escapeHtml(attachment.name) + '</a></p>');
        }
        setSlashOpen(false);
    }

    function applySlashCommand(command) {
        const selection = window.getSelection();
        const node = selection?.anchorNode;
        const element = node?.nodeType === Node.ELEMENT_NODE ? node : node?.parentElement;
        const host = element?.closest?.('p,div,h2,h3,h4,blockquote,pre,li');
        if (host && /^\/[a-z0-9-]*$/i.test(host.innerText.trim())) {
            host.innerHTML = '';
            const range = document.createRange();
            range.selectNodeContents(host);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
        }

        const actions = {
            h1: () => applyBlock('h1'),
            h2: () => applyBlock('h2'),
            h3: () => applyBlock('h3'),
            todo: () => applyBlock('check'),
            bullet: () => applyBlock('bullet'),
            number: () => applyBlock('number'),
            quote: () => applyBlock('quote'),
            code: insertCode,
            divider: insertSeparator,
            image: () => insertAttachmentInline(attachments.find((item) => item.is_image)),
            file: () => insertAttachmentInline(attachments.find((item) => !item.is_image)),
        };
        actions[command]?.();
        setSlashOpen(false);
    }

    function handleInput() {
        setSlashOpen(currentSlashQuery() !== null);
        emitContent();
    }

    function handleKeyDown(event) {
        if (slashOpen && event.key === 'Enter') {
            const query = currentSlashQuery();
            if (query) {
                event.preventDefault();
                applySlashCommand(query);
                return;
            }
        }
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') {
            event.preventDefault();
            runCommand('bold');
            return;
        }
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'i') {
            event.preventDefault();
            runCommand('italic');
        }
    }

    function initializeEditor(element) {
        if (!element || initializedRef.current) return;
        initializedRef.current = true;
        element.innerHTML = memoValueToHtml(content);
    }

    return <div className="overflow-hidden rounded-2xl border border-white/[0.07] bg-[#08111F]">
        <div className="flex flex-wrap items-center gap-2 border-b border-white/[0.06] bg-[#0D1725] p-2">
            <div className="flex items-center gap-1 rounded-lg border border-white/[0.06] bg-[#08111F] px-1.5">
                <Type size={14} className="ml-1 text-slate-600" />
                <select value={style.fontFamily} onChange={(e) => applyFont(e.target.value)} className="h-8 border-0 bg-transparent px-1 text-xs text-slate-300 outline-none" aria-label="Police">
                    {FONTS.map((font) => <option key={font.value} value={font.value}>{font.label}</option>)}
                </select>
            </div>
            <div className="flex items-center rounded-lg border border-white/[0.06] bg-[#08111F]">
                <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => adjustSize(-1)} className="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-white"><Minus size={13} /></button>
                <span className="min-w-8 text-center text-[11px] text-slate-400">{style.fontSize}</span>
                <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => adjustSize(1)} className="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-white"><Plus size={13} /></button>
            </div>
            <div className="flex rounded-lg border border-white/[0.06] bg-[#08111F] p-0.5">
                <FormatButton active={style.textTransform === 'none'} onClick={() => applyCase('none')} label="Normal"><Type size={14} /></FormatButton>
                <FormatButton onClick={() => applyCase('uppercase')} label="Majuscules"><CaseUpper size={14} /></FormatButton>
                <FormatButton onClick={() => applyCase('lowercase')} label="Minuscules"><CaseLower size={14} /></FormatButton>
            </div>
            <div className="flex rounded-lg border border-white/[0.06] bg-[#08111F] p-0.5">
                <FormatButton onClick={() => update('textAlign', 'left')} label="Gauche"><AlignLeft size={14} /></FormatButton>
                <FormatButton onClick={() => update('textAlign', 'center')} label="Centre"><AlignCenter size={14} /></FormatButton>
                <FormatButton onClick={() => update('textAlign', 'right')} label="Droite"><AlignRight size={14} /></FormatButton>
                <FormatButton onClick={() => update('textAlign', 'justify')} label="Justifié"><AlignJustify size={14} /></FormatButton>
            </div>
            <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => runCommand('bold')} className="flex h-8 items-center rounded-lg border border-white/[0.06] bg-[#08111F] px-2 text-xs font-bold text-slate-500 hover:text-white" title="Gras">B</button>
            <div className="h-6 w-px bg-white/[0.06]" />
            <select defaultValue="" onChange={(e) => { if (e.target.value) applyBlock(e.target.value); e.target.value = ''; }} className="h-8 rounded-lg border border-white/[0.06] bg-[#08111F] px-2 text-xs text-slate-400 outline-none" aria-label="Insérer un bloc">
                <option value="">Bloc…</option>
                <option value="h1">Titre 1</option><option value="h2">Titre 2</option><option value="h3">Titre 3</option>
                <option value="bullet">Liste</option><option value="number">Liste numérotée</option><option value="check">Checklist</option><option value="quote">Citation</option>
            </select>
            <FormatButton onClick={insertCode} label="Bloc de code"><Code2 size={14} /></FormatButton>
            <FormatButton onClick={() => insertAttachmentInline(attachments.find((item) => item.is_image))} label="Image inline"><ImageIcon size={14} /></FormatButton>
            <FormatButton onClick={() => insertAttachmentInline(attachments.find((item) => !item.is_image))} label="Fichier inline"><Paperclip size={14} /></FormatButton>
            <FormatButton onClick={insertSeparator} label="Séparateur"><Minus size={14} /></FormatButton>
            <button type="button" onClick={() => insertText('')} className="hidden" aria-hidden="true" />
            <button type="button" onClick={() => onAutoTitle?.(autoMemoTitle(editorRef.current?.innerText ?? ''))} className="ml-auto inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#FF6A00]/20 bg-[#FF6A00]/10 px-2.5 text-xs font-semibold text-[#FF8A3D]"><Sparkles size={13} />Titre automatique</button>
        </div>
        {slashOpen && <SlashMenu query={currentSlashQuery() ?? ''} attachments={attachments} onSelect={applySlashCommand} onInsertAttachment={insertAttachmentInline} />}
        <div className="flex items-center gap-2 border-b border-white/[0.05] px-3 py-2 text-[10px] text-slate-600"><FileText size={12} /><span>Éditeur riche · les titres, listes et styles sont appliqués directement, sans dièses ni marqueurs.</span></div>
        <div
            ref={(element) => { editorRef.current = element; initializeEditor(element); }}
            contentEditable
            suppressContentEditableWarning
            onInput={handleInput}
            onKeyDown={handleKeyDown}
            role="textbox"
            aria-multiline="true"
            aria-label="Contenu de la fiche"
            spellCheck
            style={{ fontFamily: MEMO_FONT_STACKS[style.fontFamily] ?? MEMO_FONT_STACKS.Inter, fontSize: style.fontSize, fontWeight: style.fontWeight, textTransform: style.textTransform, textAlign: style.textAlign }}
            className="min-h-[400px] w-full resize-y overflow-y-auto bg-[#08111F] p-4 leading-7 text-slate-200 outline-none empty:before:text-slate-600 empty:before:content-[attr(data-placeholder)]"
            data-placeholder="Écris une note puis applique les formats directement, comme dans Notion."
        />
    </div>;
}
function SlashMenu({ query, attachments, onSelect, onInsertAttachment }) {
    const commands = [
        ['h1', 'Titre 1', 'Grand titre'], ['h2', 'Titre 2', 'Titre secondaire'], ['h3', 'Titre 3', 'Petit titre'],
        ['todo', 'Checklist', 'Tâche à cocher'], ['bullet', 'Liste', 'Liste à puces'], ['number', 'Liste numérotée', 'Liste ordonnée'],
        ['quote', 'Citation', 'Bloc de citation'], ['code', 'Code', 'Bloc monospace'], ['divider', 'Séparateur', 'Ligne horizontale'],
        ['image', 'Image', 'Insérer une image'], ['file', 'Fichier', 'Insérer un fichier'],
    ];
    const filtered = commands.filter(([key, label]) => !query || key.includes(query) || label.toLowerCase().includes(query));
    return <div className="border-b border-white/[0.05] bg-[#0D1725] p-2">
        <div className="mb-1 px-2 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-600">Commandes rapides</div>
        <div className="grid gap-1 sm:grid-cols-2">
            {filtered.map(([key, label, description]) => <button key={key} type="button" onMouseDown={(event) => event.preventDefault()} onClick={() => onSelect(key)} className="flex items-center gap-3 rounded-lg px-2.5 py-2 text-left hover:bg-white/[0.05]">
                <span className="flex h-7 w-7 items-center justify-center rounded-md bg-white/[0.04] text-xs font-semibold text-[#FF8A3D]">/</span>
                <span className="min-w-0"><span className="block text-xs font-semibold text-slate-200">{label}</span><span className="block truncate text-[10px] text-slate-600">{description}</span></span>
            </button>)}
        </div>
        {query === 'image' && attachments.filter((item) => item.is_image).length > 0 && <div className="mt-2 flex gap-2 overflow-x-auto border-t border-white/[0.05] pt-2">{attachments.filter((item) => item.is_image).slice(0, 6).map((item) => <button key={item.id} type="button" onClick={() => onInsertAttachment(item)} className="shrink-0 overflow-hidden rounded-lg border border-white/[0.06]"><img src={item.url} alt={item.name} className="h-14 w-14 object-cover" /></button>)}</div>}
    </div>;
}
function FormatButton({ active, onClick, label, children }) { return <button type="button" onMouseDown={(event) => event.preventDefault()} onClick={onClick} aria-label={label} title={label} className={'flex h-8 w-8 items-center justify-center rounded-lg border border-white/[0.06] bg-[#08111F] transition ' + (active ? 'text-white' : 'text-slate-500 hover:text-white')}>{children}</button>; }