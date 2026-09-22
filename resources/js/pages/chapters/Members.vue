<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { computed, ref } from 'vue';
import {
    destroy as stepDown,
    store as makeOrganiser,
} from '@/actions/App/Http/Controllers/ChapterOrganiserController';
import { show as showGroup } from '@/routes/chapters';

type Member = {
    id: number;
    name: string;
    is_organiser: boolean;
    is_you: boolean;
    joined_at: string | null;
};

const props = defineProps<{
    group: { name: string; slug: string };
    members: Member[];
}>();

const organiserCount = computed(
    () => props.members.filter((member) => member.is_organiser).length,
);

const confirmingStepDown = ref(false);

const joined = new Intl.DateTimeFormat(undefined, {
    month: 'short',
    year: 'numeric',
});

const actionClass =
    'font-display text-ink border-ink cursor-pointer border-b text-xs font-semibold tracking-[0.14em] uppercase disabled:opacity-50';
</script>

<template>
    <Head :title="`Members of ${group.name}`" />

    <section class="mx-auto w-full max-w-3xl px-6 py-20 md:px-10 md:py-28">
        <Link
            :href="showGroup(group.slug)"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            {{ group.name }}
        </Link>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            Members and organisers
        </h1>
        <p class="text-ink-soft mt-6 text-lg leading-relaxed">
            Organisers plan meetups and can make other members organisers.
            Sharing the job keeps the group going when someone is away.
        </p>

        <ul class="border-hairline divide-hairline mt-12 divide-y border-y">
            <li
                v-for="member in members"
                :key="member.id"
                class="flex flex-wrap items-center justify-between gap-4 py-5"
            >
                <div>
                    <p class="font-semibold">
                        {{ member.name }}
                        <span v-if="member.is_you" class="text-ink-soft">
                            (you)</span
                        >
                    </p>
                    <p class="text-ink-soft mt-1 text-sm">
                        {{ member.is_organiser ? 'Organiser' : 'Member'
                        }}<template v-if="member.joined_at">
                            · joined
                            {{ joined.format(new Date(member.joined_at)) }}
                        </template>
                    </p>
                </div>

                <Form
                    v-if="!member.is_organiser"
                    v-bind="makeOrganiser.form(group.slug)"
                    :options="{ preserveScroll: true }"
                    v-slot="{ processing }"
                >
                    <input type="hidden" name="user_id" :value="member.id" />
                    <button
                        type="submit"
                        :disabled="processing"
                        :class="actionClass"
                    >
                        Make organiser
                    </button>
                </Form>

                <Form
                    v-else-if="member.is_you && organiserCount > 1"
                    v-bind="stepDown.form(group.slug)"
                    v-slot="{ processing }"
                    class="flex items-center gap-4 text-sm"
                >
                    <template v-if="confirmingStepDown">
                        <span>Step down and stay on as a member?</span>
                        <button
                            type="submit"
                            :disabled="processing"
                            :class="actionClass"
                        >
                            Yes, step down
                        </button>
                        <button
                            type="button"
                            class="text-ink-soft hover:text-ink cursor-pointer"
                            @click="confirmingStepDown = false"
                        >
                            Cancel
                        </button>
                    </template>
                    <button
                        v-else
                        type="button"
                        class="text-ink-soft hover:text-ink cursor-pointer transition-colors"
                        @click="confirmingStepDown = true"
                    >
                        Step down
                    </button>
                </Form>
            </li>
        </ul>

        <p v-if="organiserCount === 1" class="text-ink-soft mt-6 text-sm">
            To step down, first make another member an organiser. A group always
            needs at least one.
        </p>
    </section>
</template>
