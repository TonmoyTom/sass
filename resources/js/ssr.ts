import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { createSSRApp, DefineComponent, h } from 'vue';
import { route, ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';

createServer((page) =>
    createInertiaApp({
        page,
        title: (title) => title,
        render: renderToString,
        resolve: (name) => {
            const pages = import.meta.glob<DefineComponent>(
                './Pages/**/*.vue',
                { eager: true },
            );
            return pages[`./Pages/${name}.vue`];
        },
        setup({ App, props, plugin }) {
            // Script setup e direct route() call (template er baire) er jonno
            (globalThis as any).route = (
                name: any,
                params: any,
                absolute: any,
                config: any,
            ) => (route as any)(name, params, absolute, config ?? Ziggy);

            return createSSRApp({
                render: () => h(App, props),
            })
                .use(plugin)
                .use(ZiggyVue, Ziggy as any); // template e $route/route() er jonno
        },
    }),
);
