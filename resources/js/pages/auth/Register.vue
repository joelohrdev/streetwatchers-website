<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import FormField from '@/components/marketing/FormField.vue';
import { inlineLinkClass, primaryButtonClass } from '@/lib/marketing';
import { login } from '@/routes';
import { index as groupDirectory } from '@/routes/chapters';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

const benefits = [
    {
        title: 'Share your frames',
        body: 'Post candid photographs of everyday public life, with the story behind each one.',
    },
    {
        title: 'Walk with a group',
        body: 'Join a local group for photo walks, edits and print swaps, or start one in your city.',
    },
    {
        title: 'Find your collective',
        body: 'Team up with photographers who share your way of seeing in an independent collective.',
    },
];
</script>

<template>
    <Head title="Join StreetWatchers" />

    <section
        class="mx-auto grid w-full max-w-6xl gap-16 px-6 py-20 md:grid-cols-2 md:gap-20 md:px-10 md:py-28"
    >
        <div>
            <p
                class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
            >
                Join StreetWatchers
            </p>
            <h1
                class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
            >
                Find the extraordinary in everyday streets
            </h1>
            <p class="text-ink-soft mt-6 max-w-md text-lg leading-relaxed">
                A global community for candid, unstaged photography. Membership
                is free.
            </p>

            <ul class="border-hairline mt-12 max-w-md border-t">
                <li
                    v-for="benefit in benefits"
                    :key="benefit.title"
                    class="border-hairline border-b py-6"
                >
                    <p
                        class="font-display text-sm font-extrabold tracking-[0.06em] uppercase"
                    >
                        {{ benefit.title }}
                    </p>
                    <p class="text-ink-soft mt-2 leading-relaxed">
                        {{ benefit.body }}
                    </p>
                </li>
            </ul>

            <Link
                :href="groupDirectory()"
                class="font-display border-ink text-ink mt-10 inline-flex items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
            >
                Browse groups first
                <ArrowRight class="size-4" />
            </Link>
        </div>

        <div class="md:border-hairline md:border-l md:pl-20">
            <h2
                class="font-display text-lg font-extrabold tracking-tight uppercase"
            >
                Create your account
            </h2>

            <Form
                v-bind="store.form()"
                :reset-on-success="['password', 'password_confirmation']"
                class="mt-10 flex flex-col gap-10"
                v-slot="{ errors, processing }"
            >
                <FormField
                    id="name"
                    label="Name"
                    name="name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Your full name"
                    :error="errors.name"
                />

                <FormField
                    id="email"
                    label="Email"
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                    :error="errors.email"
                />

                <FormField
                    id="password"
                    label="Password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    :passwordrules="passwordRules"
                    placeholder="Choose a password"
                    :error="errors.password"
                />

                <FormField
                    id="password_confirmation"
                    label="Confirm password"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    :passwordrules="passwordRules"
                    placeholder="Type it again"
                    :error="errors.password_confirmation"
                />

                <div class="flex flex-col gap-6">
                    <button
                        type="submit"
                        :disabled="processing"
                        data-test="register-user-button"
                        :class="primaryButtonClass"
                    >
                        {{
                            processing
                                ? 'Creating account…'
                                : 'Join StreetWatchers'
                        }}
                    </button>

                    <p class="text-ink-soft text-sm">
                        Already a member?
                        <Link :href="login()" :class="inlineLinkClass">
                            Log in
                        </Link>
                    </p>
                </div>
            </Form>
        </div>
    </section>
</template>
