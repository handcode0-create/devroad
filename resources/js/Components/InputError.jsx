export default function InputError({ message, className = '', ...props }) {
    return message ? (
        <p
            {...props}
            className={'text-sm text-[var(--dr-danger)] ' + className}
        >
            {message}
        </p>
    ) : null;
}
