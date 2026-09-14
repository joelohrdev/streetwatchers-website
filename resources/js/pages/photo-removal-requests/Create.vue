<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/PhotoRemovalRequestController';
import { home } from '@/routes';
import { create } from '@/routes/photo-removal-requests';

defineProps<{
    submitted: boolean;
}>();

const fieldClass =
    'border-hairline focus-within:border-ink mt-3 border-b pb-2 transition-colors';
const inputClass =
    'text-ink placeholder:text-ink-soft/60 w-full bg-transparent text-base focus:outline-none';
const labelClass = 'text-ink-soft text-xs tracking-[0.14em] uppercase';
</script>

<template>
    <Head title="Request photo removal" />

    <section class="mx-auto w-full max-w-2xl px-6 py-24 md:px-10 md:py-32">
        <h1
            class="font-display text-2xl font-extrabold tracking-tight uppercase md:text-4xl"
        >
            Request photo removal
        </h1>

        <div v-if="submitted" class="border-hairline mt-12 border-t pt-12">
            <h2
                class="font-display text-lg font-semibold tracking-tight uppercase"
            >
                Request received
            </h2>
            <p class="text-ink-soft mt-4 text-lg leading-relaxed">
                Thank you. Our moderation team will review your request and
                reply to the email address you gave us. You do not need to
                submit it again.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                <Link
                    :href="home()"
                    class="font-display border-ink bg-ink text-paper hover:bg-paper hover:text-ink inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                >
                    Back to StreetWatchers
                </Link>
                <Link
                    :href="create()"
                    class="font-display border-ink/25 text-ink hover:border-ink inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                >
                    Submit another request
                </Link>
            </div>
        </div>

        <template v-else>
            <p class="text-ink-soft mt-6 text-lg leading-relaxed">
                If you appear in a photo on StreetWatchers and want it taken
                down, tell us which photo and why. You do not need an account.
                We review every request and reply by email.
            </p>

            <Form
                v-bind="store.form()"
                class="mt-14 flex flex-col gap-10"
                v-slot="{ errors, processing }"
            >
                <div>
                    <label for="removal-email" :class="labelClass">
                        Your email
                    </label>
                    <div :class="fieldClass">
                        <input
                            id="removal-email"
                            name="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                            :class="inputClass"
                        />
                    </div>
                    <p v-if="errors.email" class="text-ink mt-2 text-sm">
                        {{ errors.email }}
                    </p>
                </div>

                <div>
                    <label for="removal-photo" :class="labelClass">
                        Photo ID or link
                    </label>
                    <div :class="fieldClass">
                        <input
                            id="removal-photo"
                            name="photo_reference"
                            type="text"
                            required
                            placeholder="https://streetwatchers.com/photos/1234"
                            :class="inputClass"
                        />
                    </div>
                    <p
                        v-if="errors.photo_reference"
                        class="text-ink mt-2 text-sm"
                    >
                        {{ errors.photo_reference }}
                    </p>
                </div>

                <div>
                    <label for="removal-reason" :class="labelClass">
                        Why should it be removed?
                    </label>
                    <div :class="fieldClass">
                        <textarea
                            id="removal-reason"
                            name="reason"
                            rows="5"
                            required
                            placeholder="For example: I am identifiable in this photo and did not consent to it being published."
                            :class="[inputClass, 'resize-y']"
                        />
                    </div>
                    <p v-if="errors.reason" class="text-ink mt-2 text-sm">
                        {{ errors.reason }}
                    </p>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="font-display border-ink/25 text-ink hover:border-ink inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors disabled:opacity-50"
                    >
                        {{ processing ? 'Sending…' : 'Send request' }}
                    </button>
                </div>
            </Form>
        </template>
    </section>
</template>
