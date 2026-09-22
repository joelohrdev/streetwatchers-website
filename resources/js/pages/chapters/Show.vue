<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, CalendarDays, MapPin } from '@lucide/vue';
import { computed } from 'vue';
import type { MapChapter } from '@/components/marketing/ChapterMap.vue';
import type { GroupMembership } from '@/components/marketing/GroupJoinPanel.vue';
import GroupJoinPanel from '@/components/marketing/GroupJoinPanel.vue';
import ChapterMap from '@/components/marketing/ChapterMap.vue';
import { formatDistance } from '@/lib/geo';
import { inlineLinkClass, secondaryButtonClass } from '@/lib/marketing';
import { formatMeetupDay, formatMeetupHours } from '@/lib/meetups';
import { create, index, show } from '@/routes/chapters';
import {
    create as planMeetup,
    show as showMeetup,
} from '@/routes/chapters/events';
import { index as members } from '@/routes/chapters/members';

type NearbyChapter = MapChapter & { distance: number };

type UpcomingEvent = {
    id: number;
    title: string;
    location_name: string;
    starts_at: string;
    ends_at: string;
    timezone: string;
    is_cancelled: boolean;
};

const props = defineProps<{
    chapter: MapChapter & {
        description: string;
        members_count: number;
        photos_count: number;
        created_at: string | null;
    };
    organisers: string[];
    upcomingEvents: UpcomingEvent[];
    nearby: NearbyChapter[];
    nearbyRadiusMiles: number;
    membership: GroupMembership;
    status: string | null;
}>();

const mapChapters = computed<MapChapter[]>(() => [
    props.chapter,
    ...props.nearby,
]);

const foundedYear = computed(() =>
    props.chapter.created_at
        ? new Date(props.chapter.created_at).getFullYear()
        : null,
);

const stats = computed(() => [
    {
        label: props.chapter.members_count === 1 ? 'Member' : 'Members',
        value: props.chapter.members_count,
    },
    {
        label: props.chapter.photos_count === 1 ? 'Photo' : 'Photos',
        value: props.chapter.photos_count,
    },
    ...(foundedYear.value
        ? [{ label: 'Founded', value: foundedYear.value }]
        : []),
]);
</script>

<template>
    <Head :title="chapter.name" />

    <section
        class="mx-auto w-full max-w-6xl px-6 pt-12 pb-16 md:px-10 md:pt-16"
    >
        <Link
            :href="index()"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            All groups
        </Link>

        <p
            class="font-display text-ink-soft mt-12 flex items-center gap-2 text-xs font-semibold tracking-[0.18em] uppercase"
        >
            <MapPin class="size-4" />
            {{ chapter.city }}, {{ chapter.country }}
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight text-balance uppercase md:text-5xl"
        >
            {{ chapter.name }}
        </h1>

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

        <GroupJoinPanel
            class="mt-12"
            :group="chapter"
            :membership="membership"
            :status="status"
        />
    </section>

    <section class="border-hairline border-t">
        <div
            class="mx-auto grid w-full max-w-6xl gap-16 px-6 py-16 md:grid-cols-[3fr_2fr] md:px-10 md:py-20"
        >
            <div class="space-y-16">
                <div>
                    <h2
                        class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
                    >
                        About
                    </h2>
                    <p class="mt-6 text-lg leading-relaxed whitespace-pre-line">
                        {{ chapter.description }}
                    </p>
                </div>

                <div>
                    <h2
                        class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
                    >
                        Upcoming walks and events
                    </h2>
                    <ul
                        v-if="upcomingEvents.length"
                        class="border-hairline divide-hairline mt-6 divide-y border-y"
                    >
                        <li v-for="event in upcomingEvents" :key="event.id">
                            <Link
                                :href="showMeetup([chapter.slug, event.id])"
                                class="hover:bg-ink/[0.03] focus-visible:outline-ink flex gap-6 py-5 transition-colors focus-visible:outline-2"
                            >
                                <CalendarDays
                                    class="text-ink-soft mt-0.5 size-5 shrink-0"
                                />
                                <div>
                                    <p class="font-semibold">
                                        <span
                                            v-if="event.is_cancelled"
                                            class="font-display mr-2 text-xs tracking-[0.14em] uppercase"
                                            >Cancelled</span
                                        >
                                        <span
                                            :class="{
                                                'text-ink-soft line-through':
                                                    event.is_cancelled,
                                            }"
                                            >{{ event.title }}</span
                                        >
                                    </p>
                                    <p class="text-ink-soft mt-1 text-sm">
                                        <time :datetime="event.starts_at">
                                            {{
                                                formatMeetupDay(
                                                    event.starts_at,
                                                    event.timezone,
                                                )
                                            }}
                                            ·
                                            {{
                                                formatMeetupHours(
                                                    event.starts_at,
                                                    event.ends_at,
                                                    event.timezone,
                                                )
                                            }}
                                        </time>
                                        · {{ event.location_name }}
                                    </p>
                                </div>
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="text-ink-soft mt-6">
                        No events are scheduled yet. Check back soon.
                    </p>
                    <div
                        v-if="membership === 'organiser'"
                        class="mt-8 flex flex-col gap-3 sm:flex-row"
                    >
                        <Link
                            :href="planMeetup(chapter.slug)"
                            :class="[secondaryButtonClass, 'sm:w-auto']"
                        >
                            Plan a meetup
                        </Link>
                        <Link
                            :href="members(chapter.slug)"
                            :class="[secondaryButtonClass, 'sm:w-auto']"
                        >
                            Members and organisers
                        </Link>
                    </div>
                </div>
            </div>

            <aside class="space-y-12">
                <div>
                    <h2
                        class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
                    >
                        Organisers
                    </h2>
                    <ul v-if="organisers.length" class="mt-6 space-y-2">
                        <li v-for="organiser in organisers" :key="organiser">
                            {{ organiser }}
                        </li>
                    </ul>
                    <p v-else class="text-ink-soft mt-6">
                        This group is looking for organisers.
                    </p>
                </div>

                <div>
                    <h2
                        class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
                    >
                        Where
                    </h2>
                    <div class="border-hairline mt-6 h-72 border">
                        <ChapterMap
                            :chapters="mapChapters"
                            :focus="chapter"
                            :label="`Map showing ${chapter.name} and nearby groups`"
                        />
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="border-hairline border-t">
        <div class="mx-auto w-full max-w-6xl px-6 py-16 md:px-10 md:py-20">
            <h2
                class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
            >
                Nearby groups
            </h2>
            <ul
                v-if="nearby.length"
                class="mt-8 grid grid-cols-1 gap-px sm:grid-cols-2 lg:grid-cols-4"
            >
                <li
                    v-for="other in nearby"
                    :key="other.id"
                    class="border-hairline -mt-px -ml-px border"
                >
                    <Link
                        :href="show(other.slug)"
                        class="hover:bg-ink/[0.03] focus-visible:outline-ink flex h-full flex-col p-6 transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2"
                    >
                        <span
                            class="font-display text-sm font-extrabold tracking-[0.06em] uppercase"
                        >
                            {{ other.name }}
                        </span>
                        <span class="text-ink-soft mt-1 text-sm">
                            {{ other.city }}, {{ other.country }}
                        </span>
                        <span class="text-ink-soft mt-4 text-xs tabular-nums">
                            {{ formatDistance(other.distance) }}
                        </span>
                    </Link>
                </li>
            </ul>
            <p v-else class="text-ink-soft mt-6">
                No other groups within {{ nearbyRadiusMiles }} miles.
                <Link :href="index()" :class="inlineLinkClass">
                    See all groups
                </Link>
            </p>
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
                    Not your city?
                </h2>
                <p class="text-ink-soft mt-2">
                    Find another group, or start one where you live.
                </p>
            </div>
            <div class="flex flex-wrap gap-8">
                <Link
                    :href="index()"
                    class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 border-b-2 border-transparent pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
                >
                    All groups
                </Link>
                <Link
                    :href="create()"
                    class="font-display border-ink text-ink inline-flex items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
                >
                    Start a group
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </div>
    </section>
</template>
