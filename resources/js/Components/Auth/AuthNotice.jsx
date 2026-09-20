import { CheckCircle2 } from 'lucide-react';

export default function AuthNotice({ children }) {
    return (
        <div
            role="status"
            className="mb-6 flex items-start gap-2.5 rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-medium text-emerald-200"
        >
            <CheckCircle2 size={18} className="mt-0.5 shrink-0" aria-hidden="true" />
            <span>{children}</span>
        </div>
    );
}
