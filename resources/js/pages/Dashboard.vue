<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import CollectiveLogo from '@/components/marketing/CollectiveLogo.vue';
import { inlineLinkClass } from '@/lib/marketing';
import { formatDate } from '@/lib/utils';
import {
    create as startGroup,
    index as groupDirectory,
    show as showGroup,
} from '@/routes/chapters';
import { index as applicationsInbox } from '@/routes/collective-applications';
import {
    create as startCollective,
    index as collectiveDirectory,
    show as showCollective,
} from '@/routes/collectives';
import { edit as editProfile } from '@/routes/profile';

type Group = {
    name: string;
    slug: string;
    city: string;
    country: string;
    status: 'pending' | 'active' | 'inactive';
    role: 'organizer' | 'member';
};

type Collective = {
    name: string;
    slug: string;
    logo_url: string | null;
    role: 'founder' | 'member';
    pending_applications_count: number | null;
};

type SentApplication = {
    id: number;
    collective: { name: string; slug: string };
    status: 'pending' | 'accepted' | 'declined';
    created_at: string | null;
};

const props = defineProps<{
    groups: Group[];
    collectives: Collective[];
    applications: SentApplication[];
}>();

const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0]);

const waitingApplications = computed(() =>
    props.collectives.reduce(
        (total, collective) =>
            total + (collective.pending_applications_count ?? 0),
        0,
    ),
);

const groupStatusLabel: Record<Group['status'], string | null> = {
    active: null,
    pending: 'Awaiting approval',
    inactive: 'Inactive',
};

const applicationStatusLabel: Record<SentApplication['status'], string> = {
    pending: 'Waiting for the founders',
    accepted: 'Accepted',
    declined: 'Not accepted',
};

const collectivesEnabled = computed(() => page.props.features.collectives);

const actions = computed(() => [
    { label: 'Start a group', href: startGroup() },
    ...(collectivesEnabled.value
        ? [{ label: 'Start a collective', href: startCollective() }]
        : []),
    { label: 'Account settings', href: editProfile() },
]);

const sectionHeading =
    'font-display text-xs font-semibold tracking-[0.18em] uppercase';
const rowClass =
    'border-hairline flex flex-col gap-1 border-b py-5 sm:flex-row sm:items-center sm:justify-between sm:gap-6';
const labelClass = 'text-ink-soft text-xs tracking-[0.14em] uppercase';
</script>

<template>
    <Head title="Your memberships" />

    <section class="mx-auto w-full max-w-5xl px-6 py-20 md:px-10 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Your memberships
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            Hello, {{ firstName }}
        </h1>

        <nav
            class="mt-10 flex flex-wrap gap-x-8 gap-y-4"
            aria-label="Account actions"
        >
            <Link
                v-for="action in actions"
                :key="action.label"
                :href="action.href"
                class="font-display border-ink text-ink inline-flex items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
            >
                {{ action.label }}
                <ArrowRight class="size-4" />
            </Link>
        </nav>

        <Link
            v-if="collectivesEnabled && waitingApplications > 0"
            :href="applicationsInbox()"
            class="border-ink hover:bg-ink hover:text-paper mt-12 flex items-center justify-between gap-6 border-2 p-6 transition-colors"
        >
            <span>
                <span
                    class="font-display block text-sm font-extrabold tracking-[0.06em] uppercase"
                >
                    {{ waitingApplications }}
                    {{
                        waitingApplications === 1
                            ? 'application is'
                            : 'applications are'
                    }}
                    waiting for you
                </span>
                <span class="mt-1 block text-sm opacity-80">
                    People asking to join the collectives you founded.
                </span>
            </span>
            <ArrowRight class="size-5 shrink-0" />
        </Link>
    </section>

    <section class="border-hairline border-t">
        <div
            class="mx-auto grid w-full max-w-5xl gap-16 px-6 py-16 md:px-10 md:py-20"
            :class="{ 'md:grid-cols-2': collectivesEnabled }"
        >
            <div>
                <h2 :class="sectionHeading">Your groups</h2>
                <ul v-if="groups.length" class="border-hairline mt-6 border-t">
                    <li
                        v-for="group in groups"
                        :key="group.slug"
                        :class="rowClass"
                    >
                        <div>
                            <Link
                                v-if="group.status === 'active'"
                                :href="showGroup(group.slug)"
                                class="font-display text-sm font-extrabold tracking-[0.06em] uppercase hover:underline"
                            >
                                {{ group.name }}
                            </Link>
                            <span
                                v-else
                                class="font-display text-sm font-extrabold tracking-[0.06em] uppercase"
                            >
                                {{ group.name }}
                            </span>
                            <p class="text-ink-soft mt-1 text-sm">
                                {{ group.city }}, {{ group.country }}
                            </p>
                        </div>
                        <p :class="labelClass">
                            {{
                                groupStatusLabel[group.status] ??
                                (group.role === 'organizer'
                                    ? 'Organizer'
                                    : 'Member')
                            }}
                        </p>
                    </li>
                </ul>
                <p v-else class="text-ink-soft mt-6 leading-relaxed">
                    You haven't joined a group yet.
                    <Link :href="groupDirectory()" :class="inlineLinkClass">
                        Find a group near you
                    </Link>
                </p>
            </div>

            <div v-if="collectivesEnabled">
                <h2 :class="sectionHeading">Your collectives</h2>
                <ul
                    v-if="collectives.length"
                    class="border-hairline mt-6 border-t"
                >
                    <li
                        v-for="collective in collectives"
                        :key="collective.slug"
                        :class="rowClass"
                    >
                        <div class="flex items-center gap-4">
                            <CollectiveLogo
                                :name="collective.name"
                                :logo-url="collective.logo_url"
                                class="size-10"
                            />
                            <div>
                                <Link
                                    :href="showCollective(collective.slug)"
                                    class="font-display text-sm font-extrabold tracking-[0.06em] uppercase hover:underline"
                                >
                                    {{ collective.name }}
                                </Link>
                                <p class="text-ink-soft mt-1 text-sm">
                                    {{
                                        collective.role === 'founder'
                                            ? 'Founder'
                                            : 'Member'
                                    }}
                                </p>
                            </div>
                        </div>
                        <Link
                            v-if="collective.pending_applications_count"
                            :href="applicationsInbox()"
                            :class="[
                                inlineLinkClass,
                                'self-start text-sm sm:self-auto',
                            ]"
                        >
                            {{ collective.pending_applications_count }} to
                            review
                        </Link>
                    </li>
                </ul>
                <p v-else class="text-ink-soft mt-6 leading-relaxed">
                    You're not in a collective yet.
                    <Link
                        :href="collectiveDirectory()"
                        :class="inlineLinkClass"
                    >
                        Browse collectives
                    </Link>
                </p>
            </div>
        </div>
    </section>

    <section
        v-if="collectivesEnabled && applications.length"
        class="border-hairline border-t"
    >
        <div class="mx-auto w-full max-w-5xl px-6 py-16 md:px-10 md:py-20">
            <h2 :class="sectionHeading">Applications you've sent</h2>
            <ul class="border-hairline mt-6 border-t">
                <li
                    v-for="application in applications"
                    :key="application.id"
                    :class="rowClass"
                >
                    <div>
                        <Link
                            :href="showCollective(application.collective.slug)"
                            class="font-display text-sm font-extrabold tracking-[0.06em] uppercase hover:underline"
                        >
                            {{ application.collective.name }}
                        </Link>
                        <p class="text-ink-soft mt-1 text-sm">
                            Sent {{ formatDate(application.created_at) }}
                        </p>
                    </div>
                    <p :class="labelClass">
                        {{ applicationStatusLabel[application.status] }}
                    </p>
                </li>
            </ul>
        </div>
    </section>
</template>
