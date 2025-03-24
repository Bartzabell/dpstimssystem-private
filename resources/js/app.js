import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'DPST-IMS';

createInertiaApp({
    title: (title) => `${title} | DPST-IMS`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        // Register all components globally
        const components = import.meta.glob('./Components/**/*.vue', { eager: true });
        Object.entries(components).forEach(([path, definition]) => {
            // Extract component name from path
            const componentName = path.split('/').pop().replace(/\.\w+$/, '');
            // Register component globally
            app.component(componentName, definition.default);
        });
        
        return app
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
            // Testingss
    },
    progress: {
        color: '#4B5563',
    },
});
