import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export default function PageHeader({ title, subtitle, backHref, backLabel = 'Retour', actions }) {
    return (
        <header className="mb-5 sm:mb-6">
            {backHref && (
                <Link
                    href={backHref}
                    className="mb-3 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-slate-400 transition hover:text-white sm:mb-4"
                >
                    <ArrowLeft size={16} aria-hidden="true" />
                    {backLabel}
                </Link>
            )}

            <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div className="min-w-0 max-w-3xl">
                    <h1 className="break-words text-2xl font-bold tracking-tight text-white sm:text-3xl lg:text-4xl">
                        {title}
                    </h1>

                    {subtitle && (
                        <p className="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500 sm:mt-2">
                            {subtitle}
                        </p>
                    )}
                </div>

                {actions && (
                    <div className="flex w-full min-w-0 flex-wrap items-center gap-2 sm:w-auto sm:shrink-0 sm:justify-end">
                        {actions}
                    </div>
                )}
            </div>
        </header>
    );
}
