import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export default function PageHeader({ title, subtitle, backHref, backLabel = 'Retour', actions }) {
    return (
        <header className="mb-6">
            {backHref && (
                <Link
                    href={backHref}
                    className="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 transition hover:text-white"
                >
                    <ArrowLeft size={16} aria-hidden="true" />
                    {backLabel}
                </Link>
            )}

            <div className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                    <h1 className="text-2xl font-bold tracking-tight text-white sm:text-3xl">{title}</h1>

                    {subtitle && (
                        <p className="mt-1.5 text-sm leading-6 text-slate-500">{subtitle}</p>
                    )}
                </div>

                {actions && <div className="flex shrink-0 items-center gap-2">{actions}</div>}
            </div>
        </header>
    );
}
