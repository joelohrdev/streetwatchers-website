import { ref } from 'vue';

/**
 * Google Analytics, loaded only for visitors who allow it. The choice is remembered in this browser, and the
 * footer's "Cookie settings" link lets a visitor change it. Page views after the first are picked up by
 * Google Analytics itself from Inertia's browser history changes (GA4's enhanced measurement), so there's no
 * manual page tracking here.
 */

type Consent = 'granted' | 'denied';

declare global {
    interface Window {
        dataLayer: unknown[];
    }
}

const STORAGE_KEY = 'streetwatchers.analytics-consent';

/** The visitor's choice, or null while they haven't made one. Shared by every caller. */
const consent = ref<Consent | null>(null);
/** Whether the banner is showing: no choice yet, or reopened from "Cookie settings". */
const bannerOpen = ref(false);

let measurementId: string | null = null;
let loaded = false;

function readStoredConsent(): Consent | null {
    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);

        return stored === 'granted' || stored === 'denied' ? stored : null;
    } catch {
        return null;
    }
}

function storeConsent(value: Consent): void {
    try {
        window.localStorage.setItem(STORAGE_KEY, value);
    } catch {
        // Storage can be blocked, in which case the banner simply asks again next visit.
    }
}

function gtag(..._args: unknown[]): void {
    // Google's script expects the arguments object itself, not an array.
    // eslint-disable-next-line prefer-rest-params
    window.dataLayer.push(arguments);
}

function loadGoogleAnalytics(): void {
    if (loaded || !measurementId) {
        return;
    }

    loaded = true;
    window.dataLayer = window.dataLayer || [];
    gtag('consent', 'default', {
        analytics_storage: 'granted',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
    });
    gtag('js', new Date());
    gtag('config', measurementId);

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`;
    document.head.appendChild(script);
}

/** Removes Google Analytics' _ga cookies from this domain and its parent. */
function clearAnalyticsCookies(): void {
    const host = window.location.hostname;
    const domains = [
        host,
        `.${host}`,
        `.${host.split('.').slice(-2).join('.')}`,
    ];

    document.cookie
        .split(';')
        .map((cookie) => cookie.split('=')[0].trim())
        .filter((name) => name === '_ga' || name.startsWith('_ga_'))
        .forEach((name) => {
            domains.forEach((domain) => {
                document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=${domain}`;
            });
            document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
        });
}

export function useAnalyticsConsent() {
    /**
     * Call once the page is running in the browser. Loads Google Analytics straight away for a visitor who
     * allowed it before, and opens the banner for one who hasn't chosen yet.
     */
    function start(id: string): void {
        measurementId = id;
        consent.value = readStoredConsent();

        if (consent.value === 'granted') {
            loadGoogleAnalytics();
        }

        bannerOpen.value = consent.value === null;
    }

    function allow(): void {
        consent.value = 'granted';
        storeConsent('granted');
        bannerOpen.value = false;
        loadGoogleAnalytics();
    }

    function decline(): void {
        const wasGranted = consent.value === 'granted';

        consent.value = 'denied';
        storeConsent('denied');
        bannerOpen.value = false;

        if (wasGranted && loaded) {
            gtag('consent', 'update', { analytics_storage: 'denied' });
            clearAnalyticsCookies();
        }
    }

    function reopen(): void {
        bannerOpen.value = true;
    }

    return { consent, bannerOpen, start, allow, decline, reopen };
}
