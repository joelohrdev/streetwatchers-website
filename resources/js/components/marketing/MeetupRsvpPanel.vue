<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { Check } from '@lucide/vue';
import {
    destroy as cancelRsvp,
    store as rsvp,
} from '@/actions/App/Http/Controllers/EventRsvpController';
import StatusNote from '@/components/marketing/StatusNote.vue';
import { primaryButtonClass, secondaryButtonClass } from '@/lib/marketing';
import { create as rsvpViaAccount } from '@/routes/chapters/events/rsvp';

export type MeetupViewer = {
    is_guest: boolean;
    is_member: boolean;
    is_going: boolean;
    can_manage: boolean;
};

/**
 * Who's going, and the RSVP button. RSVPing also joins the group, and the button says so for non-members.
 */
const { group, meetup, viewer, status } = defineProps<{
    group: { name: string; slug: string };
    meetup: {
        id: number;
        rsvps_enabled: boolean;
        rsvp_limit: number | null;
        attendees_count: number;
        is_cancelled: boolean;
        has_ended: boolean;
        is_accepting_rsvps: boolean;
    };
    viewer: MeetupViewer;
    status?: string | null;
}>();

const route = (): [string, number] => [group.slug, meetup.id];
</script>

<template>
    <div class="border-ink border-2 p-6 md:p-8">
        <StatusNote v-if="status === 'rsvp-going-joined'">
            You're going, and you've joined {{ group.name }} so you'll see its
            next meetups too.
        </StatusNote>
        <StatusNote v-else-if="status === 'rsvp-going'">
            You're going. See you there.
        </StatusNote>
        <StatusNote v-else-if="status === 'rsvp-cancelled'">
            Your RSVP is cancelled. You're still a member of {{ group.name }}.
        </StatusNote>

        <p
            v-if="meetup.is_cancelled"
            class="font-display text-lg font-extrabold tracking-tight uppercase"
        >
            This meetup has been cancelled
        </p>

        <p
            v-else-if="meetup.has_ended"
            class="font-display text-lg font-extrabold tracking-tight uppercase"
        >
            This meetup has happened
        </p>

        <template v-else-if="!meetup.rsvps_enabled">
            <p
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                No RSVP needed
            </p>
            <p class="text-ink-soft mt-3 max-w-xl leading-relaxed">
                Just turn up at the meeting point.
            </p>
        </template>

        <template v-else>
            <p
                class="font-display text-xs font-semibold tracking-[0.14em] uppercase"
            >
                <span class="text-2xl font-extrabold tabular-nums">
                    {{ meetup.attendees_count }}
                </span>
                <template v-if="meetup.rsvp_limit">
                    / {{ meetup.rsvp_limit }}</template
                >
                going
            </p>

            <template v-if="viewer.is_going">
                <p
                    class="font-display mt-6 flex items-center gap-2 text-lg font-extrabold tracking-tight uppercase"
                >
                    <Check class="size-5" />
                    You're going
                </p>
                <Form
                    v-bind="cancelRsvp.form(route())"
                    :options="{ preserveScroll: true }"
                    class="mt-4"
                    v-slot="{ processing }"
                >
                    <button
                        type="submit"
                        :disabled="processing"
                        class="text-ink-soft hover:text-ink cursor-pointer text-sm transition-colors"
                    >
                        {{ processing ? 'Cancelling…' : "I can't make it" }}
                    </button>
                </Form>
            </template>

            <p
                v-else-if="!meetup.is_accepting_rsvps"
                class="font-display mt-6 text-lg font-extrabold tracking-tight uppercase"
            >
                This meetup is full
            </p>

            <template v-else-if="viewer.is_guest">
                <p class="text-ink-soft mt-4 max-w-xl leading-relaxed">
                    You need a free StreetWatchers account to RSVP. RSVPing also
                    joins {{ group.name }}. We will bring you straight back
                    here.
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="
                            rsvpViaAccount(route(), {
                                query: { via: 'register' },
                            })
                        "
                        :class="[primaryButtonClass, 'sm:w-auto']"
                    >
                        Create free account
                    </Link>
                    <Link
                        :href="
                            rsvpViaAccount(route(), { query: { via: 'login' } })
                        "
                        :class="[secondaryButtonClass, 'sm:w-auto']"
                    >
                        Log in to RSVP
                    </Link>
                </div>
            </template>

            <Form
                v-else
                v-bind="rsvp.form(route())"
                :options="{ preserveScroll: true }"
                class="mt-6"
                v-slot="{ errors, processing }"
            >
                <button
                    type="submit"
                    :disabled="processing"
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    {{
                        processing
                            ? 'Saving…'
                            : viewer.is_member
                              ? 'RSVP'
                              : `RSVP and join ${group.name}`
                    }}
                </button>
                <p v-if="errors.rsvp" class="text-ink mt-2 text-sm">
                    {{ errors.rsvp }}
                </p>
            </Form>
        </template>
    </div>
</template>
