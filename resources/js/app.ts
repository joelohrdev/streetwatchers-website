import { createInertiaApp } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'StreetWatchers';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('admin/'):
                return AdminLayout;
            case name.startsWith('settings/'):
                return [MarketingLayout, SettingsLayout];
            // Everything outside the admin panel, including member pages, uses the public site's look.
            default:
                return MarketingLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will listen for flash toast data from the server...
initializeFlashToast();
