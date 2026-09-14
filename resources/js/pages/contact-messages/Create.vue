<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/ContactMessageController';
import { home } from '@/routes';
import { index as groupDirectory } from '@/routes/chapters';
import { create as photoRemovalRequest } from '@/routes/photo-removal-requests';
import type { Option } from '@/types';

defineProps<{
    topics: Option[];
    submitted: boolean;
}>();

const fieldClass =
    'border-hairline focus-within:border-ink mt-3 border-b pb-2 transition-colors';
const inputClass =
    'text-ink placeholder:text-ink-soft/60 w-full bg-transparent text-base focus:outline-none';
const labelClass = 'text-ink-soft text-xs tracking-[0.14em] uppercase';
</script>

<template>
    <Head title="Contact us" />

    <section class="mx-auto w-full max-w-2xl px-6 py-24 md:px-10 md:py-32">
        <h1
            class="font-display text-2xl font-extrabold tracking-tight uppercase md:text-4xl"
        >
            Contact us
        </h1>

        <div v-if="submitted" class="border-hairline mt-12 border-t pt-12">
            <h2
                class="font-display text-lg font-semibold tracking-tight uppercase"
            >
                Message sent
            </h2>
            <p class="text-ink-soft mt-4 text-lg leading-relaxed">
                Thanks for getting in touch. We read every message and will
                reply to the email address you gave us.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                <Link
                    :href="home()"
                    class="font-display border-ink bg-ink text-paper hover:bg-paper hover:text-ink inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                >
                    Back to StreetWatchers
                </Link>
                <Link
                    :href="groupDirectory()"
                    class="font-display border-ink/25 text-ink hover:border-ink inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                >
                    Find a group
                </Link>
            </div>
        </div>

        <template v-else>
            <p class="text-ink-soft mt-6 text-lg leading-relaxed">
                Questions about groups, your account, press or partnerships?
                Send us a message and we will get back to you by email.
            </p>
            <p class="text-ink-soft mt-4 leading-relaxed">
                Want a photo of you taken down? Use the
                <Link
                    :href="photoRemovalRequest()"
                    class="text-ink border-ink border-b"
                    >photo removal form</Link
                >
                so our moderators see it straight away.
            </p>

            <Form
                v-bind="store.form()"
                class="relative mt-14 flex flex-col gap-10"
                v-slot="{ errors, processing }"
            >
                <!-- Spam trap: people never see or fill this field, but bots usually do. -->
                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label for="contact-website">Website</label>
                    <input
                        id="contact-website"
                        name="website"
                        type="text"
                        tabindex="-1"
                        autocomplete="off"
                    />
                </div>

                <div class="grid gap-10 sm:grid-cols-2">
                    <div>
                        <label for="contact-name" :class="labelClass">
                            Your name
                        </label>
                        <div :class="fieldClass">
                            <input
                                id="contact-name"
                                name="name"
                                type="text"
                                required
                                maxlength="255"
                                autocomplete="name"
                                placeholder="Alex Morgan"
                                :class="inputClass"
                            />
                        </div>
                        <p v-if="errors.name" class="text-ink mt-2 text-sm">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div>
                        <label for="contact-email" :class="labelClass">
                            Your email
                        </label>
                        <div :class="fieldClass">
                            <input
                                id="contact-email"
                                name="email"
                                type="email"
                                required
                                maxlength="255"
                                autocomplete="email"
                                placeholder="you@example.com"
                                :class="inputClass"
                            />
                        </div>
                        <p v-if="errors.email" class="text-ink mt-2 text-sm">
                            {{ errors.email }}
                        </p>
                    </div>
                </div>

                <div>
                    <label for="contact-topic" :class="labelClass">
                        What is it about?
                    </label>
                    <div :class="fieldClass">
                        <select
                            id="contact-topic"
                            name="topic"
                            required
                            :class="[inputClass, 'cursor-pointer']"
                        >
                            <option value="" disabled selected>
                                Choose a topic
                            </option>
                            <option
                                v-for="topic in topics"
                                :key="topic.value"
                                :value="topic.value"
                            >
                                {{ topic.label }}
                            </option>
                        </select>
                    </div>
                    <p v-if="errors.topic" class="text-ink mt-2 text-sm">
                        {{ errors.topic }}
                    </p>
                </div>

                <div>
                    <label for="contact-message" :class="labelClass">
                        Message
                    </label>
                    <div :class="fieldClass">
                        <textarea
                            id="contact-message"
                            name="message"
                            rows="6"
                            required
                            maxlength="5000"
                            placeholder="How can we help?"
                            :class="[inputClass, 'resize-y']"
                        />
                    </div>
                    <p v-if="errors.message" class="text-ink mt-2 text-sm">
                        {{ errors.message }}
                    </p>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="font-display border-ink bg-ink text-paper hover:bg-paper hover:text-ink inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors disabled:opacity-50"
                    >
                        {{ processing ? 'Sending…' : 'Send message' }}
                    </button>
                </div>
            </Form>
        </template>
    </section>
</template>
