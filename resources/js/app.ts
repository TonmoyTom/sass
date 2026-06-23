import './echo.js'; 
import '../css/app.css';
import '../js/bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { configureEcho } from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// ─── Global full-page loader (app-level, remount-proof) ───
let loaderEl: HTMLElement | null = null;
let delayTimer: ReturnType<typeof setTimeout> | null = null;

function createLoader() {
    const el = document.createElement('div');
    el.id = 'global-loader';
    el.innerHTML = `
        <div class="gl-backdrop">
            <div class="gl-spinner"></div>
        </div>
    `;
    return el;
}

function showLoader() {
    if (loaderEl) return;
    loaderEl = createLoader();
    document.body.appendChild(loaderEl);
    requestAnimationFrame(() => loaderEl?.classList.add('gl-show'));
}

function hideLoader() {
    if (!loaderEl) return;
    const el = loaderEl;
    loaderEl = null;
    el.classList.remove('gl-show');
    setTimeout(() => el.remove(), 150);
}

router.on('start', () => {
    delayTimer = setTimeout(showLoader, 100);
});

router.on('finish', () => {
    if (delayTimer) clearTimeout(delayTimer);
    hideLoader();
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: false, // built-in off — amader custom loader
});
