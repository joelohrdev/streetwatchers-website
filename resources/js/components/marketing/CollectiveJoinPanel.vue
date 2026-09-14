<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { Check } from '@lucide/vue';
import { ref } from 'vue';
import { store as sendApplication } from '@/actions/App/Http/Controllers/CollectiveApplicationController';
import { destroy as leaveCollective } from '@/actions/App/Http/Controllers/CollectiveMembershipController';
import StatusNote from '@/components/marketing/StatusNote.vue';
import { primaryButtonClass, secondaryButtonClass } from '@/lib/marketing';
import { index as reviewApplications } from '@/routes/collective-applications';
import { edit } from '@/routes/collectives';
import { create as applyViaAccount } from '@/routes/collectives/applications';

export type CollectiveViewer =
    | 'guest'
    | 'founder'
    | 'member'
    | 'pending'
    | 'closed'
    | 'declined'
    | 'open';

const { collective, viewer, pendingApplicationsCount, status } = defineProps<{
    collective: {
        name: string;
        slug: string;
        is_open_for_applications: boolean;
    };
    viewer: CollectiveViewer;
    pendingApplicationsCount?: number | null;
    status?: string | null;
}>();

const message = ref('');
const confirmingLeave = ref(false);

const MESSAGE_LIMIT = 2000;
</script>

<template>
    <div id="apply" class="border-ink border-2 p-6 md:p-8">
        <StatusNote v-if="status === 'collective-created'">
            {{ collective.name }} is live in the directory. Turn on applications
            when you are ready for new members.
        </StatusNote>
        <StatusNote v-else-if="status === 'collective-updated'">
            Your changes have been saved.
        </StatusNote>
        <StatusNote v-else-if="status === 'application-sent'">
            Application sent. The founders of {{ collective.name }} will get
            back to you.
        </StatusNote>
        <StatusNote v-else-if="status === 'collective-left'">
            You have left {{ collective.name }}.
        </StatusNote>

        <template v-if="viewer === 'founder'">
            <p
                class="font-display flex items-center gap-2 text-lg font-extrabold tracking-tight uppercase"
            >
                <Check class="size-5" />
                You founded this collective
            </p>
            <p class="text-ink-soft mt-3 max-w-xl leading-relaxed">
                {{
                    collective.is_open_for_applications
                        ? 'It is open to applications.'
                        : 'It is not taking applications right now. You can open it up from the edit page.'
                }}
            </p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <Link
                    :href="edit(collective.slug)"
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    Edit collective
                </Link>
                <Link
                    :href="reviewApplications()"
                    :class="[secondaryButtonClass, 'sm:w-auto']"
                >
                    Review applications ({{ pendingApplicationsCount ?? 0 }})
                </Link>
            </div>
        </template>

        <template v-else-if="viewer === 'member'">
            <p
                class="font-display flex items-center gap-2 text-lg font-extrabold tracking-tight uppercase"
            >
                <Check class="size-5" />
                You are a member
            </p>
            <Form
                v-bind="leaveCollective.form(collective.slug)"
                :options="{ preserveScroll: true }"
                class="mt-4"
                v-slot="{ errors, processing }"
            >
                <div
                    v-if="confirmingLeave"
                    class="flex flex-wrap items-center gap-4 text-sm"
                >
                    <span class="text-ink">Leave {{ collective.name }}?</span>
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
                    Leave collective
                </button>
                <p v-if="errors.membership" class="text-ink mt-2 text-sm">
                    {{ errors.membership }}
                </p>
            </Form>
        </template>

        <template v-else-if="viewer === 'pending'">
            <p
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                Application sent
            </p>
            <p class="text-ink-soft mt-3 max-w-xl leading-relaxed">
                The founders will get back to you. There is nothing more you
                need to do for now.
            </p>
        </template>

        <template
            v-else-if="
                viewer === 'closed' ||
                (viewer === 'guest' && !collective.is_open_for_applications)
            "
        >
            <p
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                Not taking applications right now
            </p>
            <p class="text-ink-soft mt-3 max-w-xl leading-relaxed">
                {{ collective.name }} is not looking for new members at the
                moment. Check back later.
            </p>
        </template>

        <template v-else-if="viewer === 'guest'">
            <h2
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                Apply to join
            </h2>
            <p class="text-ink-soft mt-3 max-w-xl leading-relaxed">
                You need a StreetWatchers account to apply to
                {{ collective.name }}. Create one for free, or log in if you
                already have one. We will bring you straight back here.
            </p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <Link
                    :href="
                        applyViaAccount(collective.slug, {
                            query: { via: 'register' },
                        })
                    "
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    Create free account
                </Link>
                <Link
                    :href="
                        applyViaAccount(collective.slug, {
                            query: { via: 'login' },
                        })
                    "
                    :class="[secondaryButtonClass, 'sm:w-auto']"
                >
                    Log in to apply
                </Link>
            </div>
        </template>

        <template v-else>
            <h2
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                Apply to join
            </h2>
            <p
                v-if="viewer === 'declined'"
                class="text-ink mt-3 max-w-xl leading-relaxed"
            >
                Your last application wasn't accepted. You can apply again.
            </p>
            <p class="text-ink-soft mt-3 max-w-xl leading-relaxed">
                Send the founders a short note. They will decide whether to
                welcome you in.
            </p>
            <Form
                v-bind="sendApplication.form(collective.slug)"
                :options="{ preserveScroll: true }"
                class="mt-6 flex flex-col gap-6"
                v-slot="{ errors, processing }"
            >
                <div>
                    <label
                        for="application-message"
                        class="text-ink-soft text-xs tracking-[0.14em] uppercase"
                    >
                        Tell the founders about yourself
                    </label>
                    <div
                        class="border-hairline focus-within:border-ink mt-3 border-b pb-2 transition-colors"
                    >
                        <textarea
                            id="application-message"
                            v-model="message"
                            name="message"
                            rows="5"
                            required
                            minlength="20"
                            :maxlength="MESSAGE_LIMIT"
                            placeholder="Who you are, where you shoot and why this collective appeals to you."
                            class="text-ink placeholder:text-ink-soft/60 w-full resize-y bg-transparent text-base focus:outline-none"
                        />
                    </div>
                    <div class="mt-2 flex items-start justify-between gap-4">
                        <p v-if="errors.message" class="text-ink text-sm">
                            {{ errors.message }}
                        </p>
                        <p class="text-ink-soft ml-auto text-xs tabular-nums">
                            {{ message.length }} / {{ MESSAGE_LIMIT }}
                        </p>
                    </div>
                </div>
                <div>
                    <button
                        type="submit"
                        :disabled="processing"
                        :class="[primaryButtonClass, 'sm:w-auto']"
                    >
                        {{ processing ? 'Sending…' : 'Send application' }}
                    </button>
                </div>
            </Form>
        </template>
    </div>
</template>
