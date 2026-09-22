<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { inlineLinkClass } from '@/lib/marketing';
import { formatMeetupDay, formatMeetupHours } from '@/lib/meetups';
import {
    create as startGroup,
    index as groupDirectory,
} from '@/routes/chapters';
import { show as showMeetup } from '@/routes/chapters/events';
import type { UpcomingMeetup } from '@/types';

/**
 * The next meetups across every group, soonest first, so visitors see what is happening before they join.
 */
defineProps<{
    meetups: UpcomingMeetup[];
}>();
</script>

<template>
    <section id="meetups" class="border-hairline border-t">
        <div class="mx-auto w-full max-w-6xl px-6 py-24 md:px-10 md:py-32">
            <p
                class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
            >
                Upcoming meetups
            </p>

            <ul
                v-if="meetups.length"
                class="mt-14 grid grid-cols-1 gap-px sm:grid-cols-2 lg:grid-cols-3"
            >
                <li
                    v-for="meetup in meetups"
                    :key="meetup.id"
                    class="border-hairline -mt-px -ml-px border"
                >
                    <Link
                        :href="showMeetup([meetup.group.slug, meetup.id])"
                        class="hover:bg-ink/[0.03] focus-visible:outline-ink flex h-full flex-col p-6 transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2"
                    >
                        <time
                            :datetime="meetup.starts_at"
                            class="font-display text-xs font-semibold tracking-[0.14em] uppercase"
                        >
                            {{
                                formatMeetupDay(
                                    meetup.starts_at,
                                    meetup.timezone,
                                )
                            }}
                        </time>
                        <span
                            class="font-display mt-4 text-sm font-extrabold tracking-[0.06em] uppercase"
                        >
                            {{ meetup.title }}
                        </span>
                        <span class="text-ink-soft mt-1 text-sm">
                            {{ meetup.group.name }} · {{ meetup.group.city }},
                            {{ meetup.group.country }}
                        </span>
                        <span class="text-ink-soft mt-4 text-xs tabular-nums">
                            {{
                                formatMeetupHours(
                                    meetup.starts_at,
                                    meetup.ends_at,
                                    meetup.timezone,
                                )
                            }}
                        </span>
                    </Link>
                </li>
            </ul>
            <p
                v-else
                class="text-ink-soft mt-10 max-w-xl text-lg leading-relaxed"
            >
                No meetups are scheduled right now.
                <Link :href="groupDirectory()" :class="inlineLinkClass">
                    Find a group near you</Link
                >
                or
                <Link :href="startGroup()" :class="inlineLinkClass">
                    start one in your city</Link
                >.
            </p>
        </div>
    </section>
</template>
