import { Link } from '@inertiajs/react';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={
                'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none ' +
                (active
                    ? 'border-indigo-400 text-[var(--dr-text)] focus:border-indigo-700'
                    : 'border-transparent text-[var(--dr-text-3)] hover:border-[var(--dr-border-2)] hover:text-[var(--dr-text)] focus:border-[var(--dr-border-2)] focus:text-[var(--dr-text)]') +
                className
            }
        >
            {children}
        </Link>
    );
}
