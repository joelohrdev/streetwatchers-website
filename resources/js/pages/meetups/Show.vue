<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, MapPin } from '@lucide/vue';
import { ref } from 'vue';
import { store as cancelMeetup } from '@/actions/App/Http/Controllers/EventCancellationController';
import type { MeetupViewer } from '@/components/marketing/MeetupRsvpPanel.vue';
import MeetupRsvpPanel from '@/components/marketing/MeetupRsvpPanel.vue';
import StatusNote from '@/components/marketing/StatusNote.vue';
import { inlineLinkClass, secondaryButtonClass } from '@/lib/marketing';
import { formatMeetupHours, formatMeetupLongDay } from '@/lib/meetups';
import { show as showGroup } from '@/routes/chapters';
import { edit } from '@/routes/chapters/events';

defineProps<{
    group: { name: string; slug: string; city: string; country: string };
    meetup: {
        id: number;
        title: string;
        description: string;
        location_name: string;
        starts_at: string;
        ends_at: string;
        timezone: string;
        organizer: string;
        rsvps_enabled: boolean;
        rsvp_limit: number | null;
        attendees_count: number;
        is_cancelled: boolean;
        has_ended: boolean;
        is_accepting_rsvps: boolean;
    };
    viewer: MeetupViewer;
    attendees: string[] | null;
    status: string | null;
}>();

const confirmingCancel = ref(false);

const sectionLabelClass =
    'font-display text-xs font-semibold tracking-[0.18em] uppercase';
</script>

<template>
    <Head :title="meetup.title" />

    <section
        class="mx-auto w-full max-w-6xl px-6 pt-12 pb-16 md:px-10 md:pt-16"
    >
        <Link
            :href="showGroup(group.slug)"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            {{ group.name }}
        </Link>

        <StatusNote v-if="status === 'meetup-created'" class="mt-10">
            Your meetup is live. Share this page to spread the word.
        </StatusNote>
        <StatusNote v-else-if="status === 'meetup-updated'" class="mt-10">
            Your changes are saved.
        </StatusNote>
        <StatusNote v-else-if="status === 'meetup-cancelled'" class="mt-10">
            The meetup is cancelled. It stays on this page, marked as cancelled,
            so anyone planning to come finds out.
        </StatusNote>

        <p
            class="font-display text-ink-soft mt-12 text-xs font-semibold tracking-[0.18em] uppercase"
        >
            <span v-if="meetup.is_cancelled" class="text-ink">Cancelled · </span
            >Meetup
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight text-balance uppercase md:text-5xl"
            :class="{ 'text-ink-soft line-through': meetup.is_cancelled }"
        >
            {{ meetup.title }}
        </h1>

        <dl class="mt-10 grid gap-6 sm:grid-cols-2">
            <div class="flex gap-4">
                <CalendarDays class="text-ink-soft mt-0.5 size-5 shrink-0" />
                <div>
                    <dt class="sr-only">When</dt>
                    <dd>
                        <time :datetime="meetup.starts_at">
                            <span class="block font-semibold">
                                {{
                                    formatMeetupLongDay(
                                        meetup.starts_at,
                                        meetup.timezone,
                                    )
                                }}
                            </span>
                            <span class="text-ink-soft">
                                {{
                                    formatMeetupHours(
                                        meetup.starts_at,
                                        meetup.ends_at,
                                        meetup.timezone,
                                    )
                                }}
                            </span>
                        </time>
                    </dd>
                </div>
            </div>
            <div class="flex gap-4">
                <MapPin class="text-ink-soft mt-0.5 size-5 shrink-0" />
                <div>
                    <dt class="sr-only">Where</dt>
                    <dd>
                        <span class="block font-semibold">
                            {{ meetup.location_name }}
                        </span>
                        <span class="text-ink-soft">
                            {{ group.city }}, {{ group.country }}
                        </span>
                    </dd>
                </div>
            </div>
        </dl>

        <MeetupRsvpPanel
            class="mt-12"
            :group="group"
            :meetup="meetup"
            :viewer="viewer"
            :status="status"
        />
    </section>

    <section class="border-hairline border-t">
        <div
            class="mx-auto grid w-full max-w-6xl gap-16 px-6 py-16 md:grid-cols-[3fr_2fr] md:px-10 md:py-20"
        >
            <div>
                <h2 :class="sectionLabelClass">About this meetup</h2>
                <p class="mt-6 text-lg leading-relaxed whitespace-pre-line">
                    {{ meetup.description }}
                </p>
            </div>

            <aside class="space-y-12">
                <div>
                    <h2 :class="sectionLabelClass">Organised by</h2>
                    <p class="mt-6">{{ meetup.organizer }}</p>
                    <p class="text-ink-soft mt-1 text-sm">
                        for
                        <Link
                            :href="showGroup(group.slug)"
                            :class="inlineLinkClass"
                        >
                            {{ group.name }}
                        </Link>
                    </p>
                </div>

                <div v-if="attendees !== null && meetup.rsvps_enabled">
                    <h2 :class="sectionLabelClass">Who's going</h2>
                    <ul v-if="attendees.length" class="mt-6 space-y-2">
                        <li v-for="name in attendees" :key="name">
                            {{ name }}
                        </li>
                    </ul>
                    <p v-else class="text-ink-soft mt-6">No RSVPs yet.</p>
                    <p class="text-ink-soft mt-4 text-xs">
                        Only organisers can see this list.
                    </p>
                </div>

                <div
                    v-if="
                        viewer.can_manage &&
                        !meetup.is_cancelled &&
                        !meetup.has_ended
                    "
                >
                    <h2 :class="sectionLabelClass">Organiser tools</h2>
                    <div class="mt-6 flex flex-col gap-4">
                        <Link
                            :href="edit([group.slug, meetup.id])"
                            :class="secondaryButtonClass"
                        >
                            Edit meetup
                        </Link>
                        <Form
                            v-bind="cancelMeetup.form([group.slug, meetup.id])"
                            :options="{ preserveScroll: true }"
                            v-slot="{ processing }"
                        >
                            <div
                                v-if="confirmingCancel"
                                class="flex flex-wrap items-center gap-4 text-sm"
                            >
                                <span>Cancel this meetup?</span>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="text-ink border-ink cursor-pointer border-b"
                                >
                                    {{
                                        processing
                                            ? 'Cancelling…'
                                            : 'Yes, cancel it'
                                    }}
                                </button>
                                <button
                                    type="button"
                                    class="text-ink-soft hover:text-ink cursor-pointer"
                                    @click="confirmingCancel = false"
                                >
                                    Keep it
                                </button>
                            </div>
                            <button
                                v-else
                                type="button"
                                class="text-ink-soft hover:text-ink cursor-pointer text-sm transition-colors"
                                @click="confirmingCancel = true"
                            >
                                Cancel meetup
                            </button>
                        </Form>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</template>
