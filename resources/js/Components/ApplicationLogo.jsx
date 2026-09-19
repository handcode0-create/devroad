export default function ApplicationLogo({ className = "", alt = "DevRoad", ...props }) {
    return (
        <img
            {...props}
            src="/icondevroad.png"
            alt={alt}
            className={className}
        />
    );
}
