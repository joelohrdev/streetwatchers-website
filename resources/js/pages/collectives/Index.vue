<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Search } from '@lucide/vue';
import { computed, ref } from 'vue';
import CollectiveLogo from '@/components/marketing/CollectiveLogo.vue';
import { primaryButtonClass } from '@/lib/marketing';
import { cn } from '@/lib/utils';
import { create, show } from '@/routes/collectives';

type Collective = {
    id: number;
    name: string;
    slug: string;
    excerpt: string;
    based_in: string | null;
    logo_url: string | null;
    is_verified: boolean;
    is_open_for_applications: boolean;
    members_count: number;
};

const props = defineProps<{
    collectives: Collective[];
}>();

const query = ref('');
const openOnly = ref(false);
const verifiedOnly = ref(false);

const hasCollectives = computed(() => props.collectives.length > 0);

/** Lowercase and strip accents so "sao paulo" finds "São Paulo". */
const normalize = (value: string): string =>
    value
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase();

const results = computed(() => {
    const term = normalize(query.value.trim());

    return props.collectives.filter(
        (collective) =>
            (!openOnly.value || collective.is_open_for_applications) &&
            (!verifiedOnly.value || collective.is_verified) &&
            (term === '' ||
                [collective.name, collective.based_in ?? '', collective.excerpt]
                    .map(normalize)
                    .some((field) => field.includes(term))),
    );
});

const plural = (count: number, singular: string, pluralForm: string): string =>
    `${count} ${count === 1 ? singular : pluralForm}`;

const toggleClass = (active: boolean): string =>
    cn(
        'font-display border px-5 py-3 text-xs font-semibold tracking-[0.14em] uppercase transition-colors',
        active
            ? 'border-ink bg-ink text-paper'
            : 'border-ink/25 text-ink hover:border-ink',
    );
</script>

<template>
    <Head title="Find a collective" />

    <section class="mx-auto w-full max-w-6xl px-6 py-20 md:px-10 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Collective directory
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            {{
                hasCollectives
                    ? 'Find a collective'
                    : 'Start the first collective'
            }}
        </h1>
        <p class="text-ink-soft mt-6 max-w-2xl text-lg leading-relaxed">
            Groups are local and open to anyone nearby. Collectives are
            independent crews bound by a shared approach or project, and you
            join one by applying.
        </p>

        <Link
            v-if="!hasCollectives"
            :href="create()"
            :class="[primaryButtonClass, 'mt-12 w-auto']"
        >
            Start a collective
            <ArrowRight class="size-4" />
        </Link>

        <template v-else>
            <div
                class="mt-12 flex flex-col gap-6 md:flex-row md:items-end md:gap-10"
            >
                <div class="flex-1">
                    <label for="collective-search" class="sr-only">
                        Search collectives
                    </label>
                    <div
                        class="border-hairline focus-within:border-ink flex items-center gap-3 border-b pb-3"
                    >
                        <Search class="text-ink-soft size-4 shrink-0" />
                        <input
                            id="collective-search"
                            v-model="query"
                            type="search"
                            autocomplete="off"
                            placeholder="Search by name, city or what they do"
                            class="text-ink placeholder:text-ink-soft/60 w-full bg-transparent text-base focus:outline-none"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        :aria-pressed="openOnly"
                        :class="toggleClass(openOnly)"
                        @click="openOnly = !openOnly"
                    >
                        Open to applications
                    </button>
                    <button
                        type="button"
                        :aria-pressed="verifiedOnly"
                        :class="toggleClass(verifiedOnly)"
                        @click="verifiedOnly = !verifiedOnly"
                    >
                        Verified
                    </button>
                </div>
            </div>

            <p class="text-ink-soft mt-6 text-sm" aria-live="polite">
                {{ plural(results.length, 'collective', 'collectives') }}
            </p>
        </template>
    </section>

    <template v-if="hasCollectives">
        <section class="border-hairline border-t">
            <div class="mx-auto w-full max-w-6xl px-6 py-16 md:px-10 md:py-20">
                <ul
                    v-if="results.length"
                    class="grid grid-cols-1 gap-px sm:grid-cols-2 lg:grid-cols-3"
                >
                    <li
                        v-for="collective in results"
                        :key="collective.id"
                        class="border-hairline -mt-px -ml-px border"
                    >
                        <Link
                            :href="show(collective.slug)"
                            class="hover:bg-ink/[0.03] focus-visible:outline-ink flex h-full flex-col p-6 transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2"
                        >
                            <div class="flex items-start gap-4">
                                <CollectiveLogo
                                    :name="collective.name"
                                    :logo-url="collective.logo_url"
                                    class="size-12 shrink-0 text-sm"
                                />
                                <div class="min-w-0">
                                    <h3
                                        class="font-display text-sm font-extrabold tracking-[0.06em] uppercase"
                                    >
                                        {{ collective.name }}
                                    </h3>
                                    <p
                                        v-if="collective.based_in"
                                        class="text-ink-soft mt-1 text-sm"
                                    >
                                        {{ collective.based_in }}
                                    </p>
                                </div>
                            </div>
                            <p class="mt-4 flex-1 text-sm leading-relaxed">
                                {{ collective.excerpt }}
                            </p>
                            <div
                                class="text-ink-soft mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs"
                            >
                                <span>
                                    {{
                                        plural(
                                            collective.members_count,
                                            'member',
                                            'members',
                                        )
                                    }}
                                </span>
                                <span
                                    v-if="collective.is_verified"
                                    class="font-display border-ink text-ink border px-1.5 py-0.5 text-[0.625rem] font-semibold tracking-[0.14em] uppercase"
                                >
                                    Verified
                                </span>
                                <span
                                    v-if="collective.is_open_for_applications"
                                    class="font-display text-[0.625rem] font-semibold tracking-[0.14em] uppercase"
                                >
                                    Open to applications
                                </span>
                            </div>
                        </Link>
                    </li>
                </ul>

                <div v-else class="py-10 text-center">
                    <p class="text-lg">No collectives match your search.</p>
                    <p class="text-ink-soft mt-2">
                        Try a different search, or clear the filters.
                    </p>
                </div>
            </div>
        </section>

        <section class="border-hairline border-t">
            <div
                class="mx-auto flex w-full max-w-6xl flex-col items-start gap-6 px-6 py-16 md:flex-row md:items-center md:justify-between md:px-10"
            >
                <div>
                    <h2
                        class="font-display text-xl font-extrabold tracking-tight uppercase"
                    >
                        Start a collective
                    </h2>
                    <p class="text-ink-soft mt-2">
                        Bring together photographers who share your way of
                        seeing.
                    </p>
                </div>
                <Link
                    :href="create()"
                    class="font-display border-ink text-ink inline-flex items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
                >
                    Start a collective
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </section>
    </template>
</template>
