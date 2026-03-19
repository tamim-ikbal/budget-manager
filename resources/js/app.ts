import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import { Toaster } from '@/components/ui/sonner';
import { initializeTheme } from '@/composables/useAppearance';
import {
    getStoredCurrentWorkspaceUid,
    initializeCurrentWorkspace,
} from '@/composables/useCurrentWorkspace';
import type { Auth } from '@/types';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    defaults: {
        visitOptions: (_href, options) => {
            const workspaceUid = getStoredCurrentWorkspaceUid();

            if (!workspaceUid) {
                return options;
            }

            return {
                ...options,
                headers: {
                    ...(options.headers ?? {}),
                    'X-Workspace-Uid': workspaceUid,
                },
            };
        },
    },
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const auth = (props.initialPage.props.auth ?? null) as Auth | null;

        initializeCurrentWorkspace(
            auth?.workspaces ?? [],
            auth?.currentWorkspace ?? null,
        );

        createApp({ render: () => [h(App, props), h(Toaster)] })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
