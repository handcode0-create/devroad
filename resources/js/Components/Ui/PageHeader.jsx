import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export default function PageHeader({ title, subtitle, backHref, backLabel = 'Retour', actions }) {
    return (
        <header className="mb-6 font-['Figtree',system-ui,sans-serif] lg:mb-7">
            {backHref && (
                <Link
                    href={backHref}
                    className="-ml-1 mb-3 inline-flex min-h-9 items-center gap-1.5 rounded-lg px-1 text-sm font-medium text-[var(--dr-text-2)] transition hover:text-[var(--dr-text)] sm:mb-4"
                >
                    <ArrowLeft size={16} aria-hidden="true" />
                    {backLabel}
                </Link>
            )}

            <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div className="min-w-0 max-w-3xl">
                    <h1 className="m-0 break-words font-['Manrope',sans-serif] text-[30px] font-extrabold leading-[1.08] tracking-[-0.03em] text-[var(--dr-text)] lg:text-[38px]">
                        {title}
                    </h1>

                    {subtitle && (
                        <p className="m-0 mt-1.5 max-w-2xl text-[15px] leading-[1.5] text-[var(--dr-text-2)]">
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
