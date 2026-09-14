<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, MapPin } from '@lucide/vue';
import { computed } from 'vue';
import type { CollectiveViewer } from '@/components/marketing/CollectiveJoinPanel.vue';
import CollectiveJoinPanel from '@/components/marketing/CollectiveJoinPanel.vue';
import CollectiveLogo from '@/components/marketing/CollectiveLogo.vue';
import PlaceholderPhoto from '@/components/marketing/PlaceholderPhoto.vue';
import { index } from '@/routes/collectives';

type RecentPhoto = {
    id: number;
    title: string;
    image_url: string;
    author: string;
};

const props = defineProps<{
    collective: {
        id: number;
        name: string;
        slug: string;
        description: string;
        based_in: string | null;
        website_url: string | null;
        instagram_url: string | null;
        logo_url: string | null;
        is_verified: boolean;
        is_open_for_applications: boolean;
        members_count: number;
        created_at: string | null;
    };
    founders: string[];
    members: string[];
    recentPhotos: RecentPhoto[];
    viewer: CollectiveViewer;
    pendingApplicationsCount: number | null;
    status: string | null;
}>();

const foundedYear = computed(() =>
    props.collective.created_at
        ? new Date(props.collective.created_at).getFullYear()
        : null,
);

const stats = computed(() => [
    {
        label: props.collective.members_count === 1 ? 'Member' : 'Members',
        value: props.collective.members_count,
    },
    ...(foundedYear.value
        ? [{ label: 'Founded', value: foundedYear.value }]
        : []),
]);

const links = computed(() =>
    [
        { label: 'Website', href: props.collective.website_url },
        { label: 'Instagram', href: props.collective.instagram_url },
    ].filter((link): link is { label: string; href: string } =>
        Boolean(link.href),
    ),
);
</script>

<template>
    <Head :title="collective.name" />

    <section
        class="mx-auto w-full max-w-6xl px-6 pt-12 pb-16 md:px-10 md:pt-16"
    >
        <Link
            :href="index()"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            All collectives
        </Link>

        <div
            class="mt-12 flex flex-col gap-8 md:flex-row md:items-center md:gap-10"
        >
            <CollectiveLogo
                :name="collective.name"
                :logo-url="collective.logo_url"
                class="size-24 shrink-0 text-3xl md:size-28"
            />
            <div>
                <p
                    v-if="collective.based_in"
                    class="font-display text-ink-soft flex items-center gap-2 text-xs font-semibold tracking-[0.18em] uppercase"
                >
                    <MapPin class="size-4" />
                    {{ collective.based_in }}
                </p>
                <h1
                    class="font-display mt-3 text-3xl font-extrabold tracking-tight text-balance uppercase md:text-5xl"
                >
                    {{ collective.name }}
                </h1>
                <p
                    v-if="collective.is_verified"
                    class="font-display border-ink text-ink mt-4 inline-block border px-2 py-1 text-[0.6875rem] font-semibold tracking-[0.14em] uppercase"
                >
                    Verified collective
                </p>
            </div>
        </div>

        <dl class="mt-10 flex flex-wrap gap-x-12 gap-y-6">
            <div v-for="stat in stats" :key="stat.label">
                <dt
                    class="font-display text-ink-soft text-xs font-semibold tracking-[0.14em] uppercase"
                >
                    {{ stat.label }}
                </dt>
                <dd
                    class="font-display mt-1 text-2xl font-extrabold tabular-nums"
                >
                    {{ stat.value }}
                </dd>
            </div>
        </dl>

        <CollectiveJoinPanel
            class="mt-12"
            :collective="collective"
            :viewer="viewer"
            :pending-applications-count="pendingApplicationsCount"
            :status="status"
        />
    </section>

    <section class="border-hairline border-t">
        <div
            class="mx-auto grid w-full max-w-6xl gap-16 px-6 py-16 md:grid-cols-[3fr_2fr] md:px-10 md:py-20"
        >
            <div>
                <h2
                    class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
                >
                    About
                </h2>
                <p class="mt-6 text-lg leading-relaxed whitespace-pre-line">
                    {{ collective.description }}
                </p>

                <ul v-if="links.length" class="mt-8 flex flex-wrap gap-8">
                    <li v-for="link in links" :key="link.label">
                        <a
                            :href="link.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-display border-ink text-ink inline-flex items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
                        >
                            {{ link.label }}
                            <ArrowUpRight class="size-4" />
                        </a>
                    </li>
                </ul>
            </div>

            <aside class="space-y-12">
                <div>
                    <h2
                        class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
                    >
                        {{ founders.length === 1 ? 'Founder' : 'Founders' }}
                    </h2>
                    <ul v-if="founders.length" class="mt-6 space-y-2">
                        <li v-for="founder in founders" :key="founder">
                            {{ founder }}
                        </li>
                    </ul>
                    <p v-else class="text-ink-soft mt-6">
                        No founders are listed.
                    </p>
                </div>

                <div>
                    <h2
                        class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
                    >
                        Members
                    </h2>
                    <ul v-if="members.length" class="mt-6 space-y-2">
                        <li v-for="member in members" :key="member">
                            {{ member }}
                        </li>
                    </ul>
                    <p v-else class="text-ink-soft mt-6">
                        No other members yet.
                    </p>
                </div>
            </aside>
        </div>
    </section>

    <section v-if="recentPhotos.length" class="border-hairline border-t">
        <div class="mx-auto w-full max-w-6xl px-6 py-16 md:px-10 md:py-20">
            <h2
                class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
            >
                Recent work
            </h2>
            <div
                class="mt-10 grid grid-cols-1 gap-x-10 gap-y-12 sm:grid-cols-2 lg:grid-cols-3"
            >
                <figure v-for="photo in recentPhotos" :key="photo.id">
                    <PlaceholderPhoto
                        ratio="aspect-[4/5]"
                        :src="photo.image_url"
                        :alt="`${photo.title} by ${photo.author}`"
                        :label="photo.title"
                    />
                    <figcaption class="text-ink-soft mt-4 text-sm">
                        <span class="text-ink">{{ photo.title }}</span>
                        <span class="text-ink-soft/80 block">
                            {{ photo.author }}
                        </span>
                    </figcaption>
                </figure>
            </div>
        </div>
    </section>
</template>
