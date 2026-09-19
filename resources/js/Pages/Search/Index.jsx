import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { FileText, ListChecks, Map as RoadmapIcon, Search as SearchIcon } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import Pagination from '@/Components/Ui/Pagination';
import TagBadge from '@/Components/Memos/TagBadge';

const TABS = [
    { key: 'all', label: 'Tout' },
    { key: 'roadmaps', label: 'Roadmaps' },
    { key: 'memos', label: 'Mémos' },
    { key: 'steps', label: 'Étapes' },
];

const STATUS_LABELS = {
    draft: 'Brouillon',
    active: 'En cours',
    completed: 'Terminée',
    archived: 'Archivée',
    todo: 'À faire',
    in_progress: 'En cours',
    blocked: 'Bloquée',
};

export default function Index({ query = '', type = 'all', results = null, counts = null }) {
    const [term, setTerm] = useState(query ?? '');

    function submit(event) {
        event.preventDefault();

        // Même règle que le backend : 2 caractères minimum
        if (term.trim().length >= 2) {
            router.get('/search', { q: term.trim(), type }, { preserveState: true });
        }
    }

    const tabUrl = (key) => `/search?q=${encodeURIComponent(query)}&type=${key}`;
    const total = counts ? counts.roadmaps + counts.memos + counts.steps : 0;
    const searched = results !== null;

    return (
        <AppLayout>
            <Head title="Recherche" />

            <PageHeader title="Rechercher" subtitle="Dans tes roadmaps, tes fiches mémo et tes étapes." />

            <form onSubmit={submit} role="search" className="relative">
                <label htmlFor="search-input" className="sr-only">
                    Rechercher
                </label>

                <SearchIcon
                    size={19}
                    aria-hidden="true"
                    className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"
                />

                <input
                    id="search-input"
                    type="search"
                    value={term}
                    onChange={(e) => setTerm(e.target.value)}
                    placeholder="Laravel, routage, git stash..."
                    autoFocus
                    className="h-12 w-full rounded-2xl border border-white/[0.07] bg-[#101A2A] pl-11 pr-24 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-[#FF6A00]/40 focus:ring-2 focus:ring-[#FF6A00]/10"
                />

                <button
                    type="submit"
                    className="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-xl bg-[#FF6A00] px-3.5 py-2 text-xs font-bold text-[#08111F] transition hover:bg-[#ff781a]"
                >
                    Chercher
                </button>
            </form>

            {searched && (
                <nav aria-label="Type de résultat" className="-mx-4 mt-5 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
                    {TABS.map((tab) => {
                        const count = tab.key === 'all' ? total : counts[tab.key];
                        const active = type === tab.key;

                        return (
                            <Link
                                key={tab.key}
                                href={tabUrl(tab.key)}
                                aria-current={active ? 'true' : undefined}
                                className={`shrink-0 rounded-full px-4 py-2 text-xs font-semibold transition ${
                                    active
                                        ? 'bg-[#FF6A00] text-[#08111F]'
                                        : 'border border-white/[0.07] bg-[#0D1725] text-slate-400 hover:text-white'
                                }`}
                            >
                                {tab.label} <span className="opacity-70">({count})</span>
                            </Link>
                        );
                    })}
                </nav>
            )}

            <div className="mt-6 space-y-8">
                {!searched && (
                    <p className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-6 text-center text-sm text-slate-500">
                        Tape au moins 2 caractères pour lancer la recherche.
                    </p>
                )}

                {searched && total === 0 && (
                    <div className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-8 text-center">
                        <h2 className="text-base font-semibold text-white">Aucun résultat</h2>
                        <p className="mx-auto mt-1 max-w-sm text-sm leading-6 text-slate-500">
                            Rien ne correspond à « {query} ». Essaie un autre mot ou une orthographe plus courte.
                        </p>
                    </div>
                )}

                {searched && results.roadmaps?.data.length > 0 && (
                    <Section title="Roadmaps" icon={RoadmapIcon} count={counts.roadmaps}>
                        {results.roadmaps.data.map((roadmap) => (
                            <ResultLink key={roadmap.id} href={`/roadmaps/${roadmap.id}`}>
                                <p className="truncate text-sm font-semibold text-white">{roadmap.title}</p>
                                <p className="mt-1 text-xs text-slate-500">
                                    {STATUS_LABELS[roadmap.status] ?? roadmap.status} · {roadmap.progress}% ·{' '}
                                    {roadmap.steps_count} étapes
                                </p>
                            </ResultLink>
                        ))}
                        <Pagination links={results.roadmaps.links} />
                    </Section>
                )}

                {searched && results.memos?.data.length > 0 && (
                    <Section title="Fiches mémo" icon={FileText} count={counts.memos}>
                        {results.memos.data.map((memo) => (
                            <ResultLink key={memo.id} href={`/memos/${memo.id}`}>
                                <p className="truncate text-sm font-semibold text-white">{memo.title}</p>
                                <p className="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{memo.excerpt}</p>
                                {memo.tags?.length > 0 && (
                                    <div className="mt-2 flex flex-wrap gap-1.5">
                                        {memo.tags.map((tag) => (
                                            <TagBadge key={tag.id} name={tag.name} />
                                        ))}
                                    </div>
                                )}
                            </ResultLink>
                        ))}
                        <Pagination links={results.memos.links} />
                    </Section>
                )}

                {searched && results.steps?.data.length > 0 && (
                    <Section title="Étapes" icon={ListChecks} count={counts.steps}>
                        {results.steps.data.map((step) => (
                            <ResultLink key={step.id} href={`/steps/${step.id}`}>
                                <p className="truncate text-sm font-semibold text-white">{step.title}</p>
                                <p className="mt-1 text-xs text-slate-500">
                                    {step.roadmap_title} · {STATUS_LABELS[step.status] ?? step.status}
                                </p>
                            </ResultLink>
                        ))}
                        <Pagination links={results.steps.links} />
                    </Section>
                )}
            </div>
        </AppLayout>
    );
}

function Section({ title, icon: Icon, count, children }) {
    return (
        <section aria-label={title}>
            <h2 className="mb-3 flex items-center gap-2 text-sm font-semibold text-white">
                <Icon size={16} aria-hidden="true" className="text-[#FF8A3D]" />
                {title}
                <span className="text-xs font-medium text-slate-500">({count})</span>
            </h2>

            <div className="space-y-2.5">{children}</div>
        </section>
    );
}

function ResultLink({ href, children }) {
    return (
        <Link
            href={href}
            className="block rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4 transition hover:border-white/[0.11] hover:bg-[#101B2C]"
        >
            {children}
        </Link>
    );
}
