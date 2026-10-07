import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { MotionProvider } from '@/Components/Ui/Motion';
import { ConfirmProvider } from '@/Components/Ui/ConfirmProvider';

const appName = import.meta.env.VITE_APP_NAME || 'DevRoad';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<MotionProvider><ConfirmProvider><App {...props} /></ConfirmProvider></MotionProvider>);
    },
    progress: {
        color: '#4B5563',
    },
});
