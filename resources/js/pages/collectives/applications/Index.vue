<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { update as decide } from '@/actions/App/Http/Controllers/CollectiveApplicationDecisionController';
import {
    inlineLinkClass,
    primaryButtonClass,
    secondaryButtonClass,
} from '@/lib/marketing';
import { formatDate } from '@/lib/utils';
import { create, edit, show } from '@/routes/collectives';

type PendingApplication = {
    id: number;
    applicant: string;
    message: string;
    created_at: string | null;
};

type DecidedApplication = {
    id: number;
    applicant: string;
    status: 'accepted' | 'declined';
    decided_at: string | null;
};

defineProps<{
    collectives: {
        id: number;
        name: string;
        slug: string;
        is_open_for_applications: boolean;
        pending: PendingApplication[];
        recent: DecidedApplication[];
    }[];
}>();

const decisions = [
    { value: 'accepted', label: 'Accept', buttonClass: primaryButtonClass },
    { value: 'declined', label: 'Decline', buttonClass: secondaryButtonClass },
] as const;
</script>

<template>
    <Head title="Collective applications" />

    <section class="mx-auto w-full max-w-3xl px-6 py-20 md:px-10 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Your collectives
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            Applications
        </h1>
        <p class="text-ink-soft mt-6 text-lg leading-relaxed">
            People asking to join the collectives you founded. Accepting adds
            them as a member.
        </p>

        <div
            v-if="collectives.length === 0"
            class="border-hairline mt-14 border-t pt-10"
        >
            <h2
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                You haven't founded a collective yet
            </h2>
            <p class="text-ink-soft mt-3 leading-relaxed">
                Start one and people can apply to join it.
            </p>
            <div class="mt-8 sm:max-w-xs">
                <Link :href="create()" :class="primaryButtonClass">
                    Start a collective
                </Link>
            </div>
        </div>

        <div
            v-for="collective in collectives"
            :key="collective.id"
            class="border-hairline mt-14 border-t pt-10"
        >
            <h2
                class="font-display text-xl font-extrabold tracking-tight uppercase"
            >
                <Link :href="show(collective.slug)" class="hover:underline">
                    {{ collective.name }}
                </Link>
            </h2>
            <p
                v-if="!collective.is_open_for_applications"
                class="text-ink-soft mt-3 text-sm"
            >
                Not open to applications right now.
                <Link :href="edit(collective.slug)" :class="inlineLinkClass">
                    Change this
                </Link>
            </p>

            <ul
                v-if="collective.pending.length"
                class="border-hairline mt-8 border-t"
            >
                <li
                    v-for="application in collective.pending"
                    :key="application.id"
                    class="border-hairline border-b py-8"
                >
                    <div
                        class="flex flex-wrap items-baseline justify-between gap-2"
                    >
                        <p
                            class="font-display text-sm font-extrabold tracking-[0.06em] uppercase"
                        >
                            {{ application.applicant }}
                        </p>
                        <p class="text-ink-soft text-xs">
                            Received {{ formatDate(application.created_at) }}
                        </p>
                    </div>
                    <p class="mt-4 leading-relaxed whitespace-pre-line">
                        {{ application.message }}
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <Form
                            v-for="decision in decisions"
                            :key="decision.value"
                            v-bind="decide.form(application.id)"
                            :options="{ preserveScroll: true }"
                            v-slot="{ errors, processing }"
                        >
                            <input
                                type="hidden"
                                name="decision"
                                :value="decision.value"
                            />
                            <button
                                type="submit"
                                :disabled="processing"
                                :class="[decision.buttonClass, 'w-auto']"
                            >
                                {{ decision.label }}
                            </button>
                            <p
                                v-if="errors.decision"
                                class="text-ink mt-2 text-sm"
                            >
                                {{ errors.decision }}
                            </p>
                        </Form>
                    </div>
                </li>
            </ul>
            <p v-else class="text-ink-soft mt-8">No applications waiting.</p>

            <div v-if="collective.recent.length" class="mt-10">
                <h3
                    class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
                >
                    Decided in the last 30 days
                </h3>
                <ul class="border-hairline mt-4 border-t">
                    <li
                        v-for="application in collective.recent"
                        :key="application.id"
                        class="border-hairline flex items-center justify-between gap-4 border-b py-3 text-sm"
                    >
                        <span>{{ application.applicant }}</span>
                        <span class="text-ink-soft flex items-center gap-4">
                            <span class="text-ink">
                                {{
                                    application.status === 'accepted'
                                        ? 'Accepted'
                                        : 'Declined'
                                }}
                            </span>
                            <span class="text-xs">
                                {{ formatDate(application.decided_at) }}
                            </span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
