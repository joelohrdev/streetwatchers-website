<script setup lang="ts">
import { Check, Copy, Download, Share2 } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';

/**
 * Ways to share a public page: copy its short link, the device's own share sheet, links to share on social
 * networks, and a QR code. The network links are plain URLs, so no third-party script loads on our pages.
 */
const { url, text, qrCodeSrc, qrCodeDownloadSrc, label } = defineProps<{
    /** The short link to share. */
    url: string;
    /** What the post or message says alongside the link. */
    text: string;
    qrCodeSrc: string;
    qrCodeDownloadSrc: string;
    /** What the page is, for the QR code's alt text, e.g. "the Glasgow Streetwatchers page". */
    label: string;
}>();

const copied = ref(false);
// The share sheet only exists in some browsers, mostly on phones, so it's checked once the page is running.
const canUseShareSheet = ref(false);

onMounted(() => {
    canUseShareSheet.value = typeof navigator.share === 'function';
});

async function copyLink(): Promise<void> {
    try {
        await navigator.clipboard.writeText(url);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch {
        // Clipboard access can be blocked. The link is on screen to copy by hand.
    }
}

async function openShareSheet(): Promise<void> {
    try {
        await navigator.share({ title: text, text, url });
    } catch {
        // Closing the share sheet without choosing an app rejects the promise. Nothing to do.
    }
}

const networks = computed(() => {
    const encodedUrl = encodeURIComponent(url);
    const encodedText = encodeURIComponent(text);
    const encodedTextAndUrl = encodeURIComponent(`${text} ${url}`);

    return [
        {
            label: 'WhatsApp',
            href: `https://wa.me/?text=${encodedTextAndUrl}`,
        },
        {
            label: 'Facebook',
            href: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
        },
        {
            label: 'X',
            href: `https://x.com/intent/post?text=${encodedText}&url=${encodedUrl}`,
        },
        {
            label: 'Bluesky',
            href: `https://bsky.app/intent/compose?text=${encodedTextAndUrl}`,
        },
        {
            label: 'Threads',
            href: `https://www.threads.net/intent/post?text=${encodedTextAndUrl}`,
        },
        {
            label: 'Email',
            href: `mailto:?subject=${encodedText}&body=${encodedUrl}`,
        },
    ];
});

const actionClass =
    'font-display border-ink text-ink inline-flex cursor-pointer items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase';
</script>

<template>
    <div>
        <h2
            class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Share
        </h2>

        <div
            class="border-hairline mt-6 flex items-center justify-between gap-3 border-b pb-2"
        >
            <span class="text-ink truncate text-sm">{{ url }}</span>
            <button
                type="button"
                class="text-ink-soft hover:text-ink inline-flex shrink-0 cursor-pointer items-center gap-1.5 text-sm transition-colors"
                @click="copyLink"
            >
                <Check v-if="copied" class="size-4" />
                <Copy v-else class="size-4" />
                <span aria-live="polite">{{
                    copied ? 'Copied' : 'Copy link'
                }}</span>
            </button>
        </div>

        <button
            v-if="canUseShareSheet"
            type="button"
            :class="[actionClass, 'mt-6']"
            @click="openShareSheet"
        >
            <Share2 class="size-4" />
            Share…
        </button>

        <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm">
            <li v-for="network in networks" :key="network.label">
                <a
                    :href="network.href"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-ink-soft hover:text-ink transition-colors"
                >
                    {{ network.label }}
                </a>
            </li>
        </ul>

        <img
            :src="qrCodeSrc"
            :alt="`QR code linking to ${label}`"
            width="160"
            height="160"
            class="border-hairline mt-8 size-40 border"
        />
        <a :href="qrCodeDownloadSrc" download :class="[actionClass, 'mt-4']">
            <Download class="size-4" />
            Download QR code
        </a>
    </div>
</template>
