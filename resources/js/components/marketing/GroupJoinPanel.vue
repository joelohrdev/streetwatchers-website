<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { Check } from '@lucide/vue';
import { ref } from 'vue';
import {
    destroy as leaveGroup,
    store as joinGroup,
} from '@/actions/App/Http/Controllers/ChapterMembershipController';
import StatusNote from '@/components/marketing/StatusNote.vue';
import { primaryButtonClass, secondaryButtonClass } from '@/lib/marketing';
import { create as joinViaAccount } from '@/routes/chapters/membership';

export type GroupMembership = 'guest' | 'none' | 'member' | 'organiser';

const { group, membership, status } = defineProps<{
    group: { name: string; slug: string };
    membership: GroupMembership;
    status?: string | null;
}>();

const confirmingLeave = ref(false);
</script>

<template>
    <div id="join" class="border-ink border-2 p-6 md:p-8">
        <StatusNote v-if="status === 'group-joined'">
            Welcome to {{ group.name }}. Keep an eye on this page for upcoming
            walks and events.
        </StatusNote>
        <StatusNote v-else-if="status === 'group-left'">
            You have left {{ group.name }}.
        </StatusNote>
        <StatusNote v-else-if="status === 'organiser-stepped-down'">
            You've stepped down as an organiser. You're still a member of
            {{ group.name }}.
        </StatusNote>

        <template v-if="membership === 'guest'">
            <h2
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                Join this group
            </h2>
            <p class="text-ink-soft mt-3 max-w-xl leading-relaxed">
                You need a StreetWatchers account to join {{ group.name }}.
                Create one for free, or log in if you already have one. We will
                bring you straight back here.
            </p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <Link
                    :href="
                        joinViaAccount(group.slug, {
                            query: { via: 'register' },
                        })
                    "
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    Create free account
                </Link>
                <Link
                    :href="
                        joinViaAccount(group.slug, { query: { via: 'login' } })
                    "
                    :class="[secondaryButtonClass, 'sm:w-auto']"
                >
                    Log in to join
                </Link>
            </div>
        </template>

        <template v-else-if="membership === 'none'">
            <h2
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                Join this group
            </h2>
            <p class="text-ink-soft mt-3 max-w-xl leading-relaxed">
                Joining is free and lets the organisers know you want to come
                along to their walks and events.
            </p>
            <Form
                v-bind="joinGroup.form(group.slug)"
                :options="{ preserveScroll: true }"
                class="mt-6"
                v-slot="{ processing }"
            >
                <button
                    type="submit"
                    :disabled="processing"
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    {{ processing ? 'Joining…' : `Join ${group.name}` }}
                </button>
            </Form>
        </template>

        <template v-else>
            <p
                class="font-display flex items-center gap-2 text-lg font-extrabold tracking-tight uppercase"
            >
                <Check class="size-5" />
                {{
                    membership === 'organiser'
                        ? 'You organise this group'
                        : 'You are a member'
                }}
            </p>

            <p
                v-if="membership === 'organiser'"
                class="text-ink-soft mt-3 max-w-xl leading-relaxed"
            >
                You run {{ group.name }}'s walks and events.
            </p>

            <Form
                v-else
                v-bind="leaveGroup.form(group.slug)"
                :options="{ preserveScroll: true }"
                class="mt-4"
                v-slot="{ errors, processing }"
            >
                <div
                    v-if="confirmingLeave"
                    class="flex flex-wrap items-center gap-4 text-sm"
                >
                    <span class="text-ink">Leave {{ group.name }}?</span>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="text-ink border-ink cursor-pointer border-b"
                    >
                        {{ processing ? 'Leaving…' : 'Yes, leave' }}
                    </button>
                    <button
                        type="button"
                        class="text-ink-soft hover:text-ink cursor-pointer"
                        @click="confirmingLeave = false"
                    >
                        Cancel
                    </button>
                </div>
                <button
                    v-else
                    type="button"
                    class="text-ink-soft hover:text-ink cursor-pointer text-sm transition-colors"
                    @click="confirmingLeave = true"
                >
                    Leave group
                </button>
                <p v-if="errors.membership" class="text-ink mt-2 text-sm">
                    {{ errors.membership }}
                </p>
            </Form>
        </template>
    </div>
</template>
