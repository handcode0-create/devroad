export default function ApplicationLogo({ className = "", alt = "DevRoad", ...props }) {
    return (
        <img
            {...props}
            src="/logo.png"
            alt={alt}
            className={className}
        />
    );
}
