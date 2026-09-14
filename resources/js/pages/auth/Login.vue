<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import AuthPanel from '@/components/marketing/AuthPanel.vue';
import FormField from '@/components/marketing/FormField.vue';
import StatusNote from '@/components/marketing/StatusNote.vue';
import { inlineLinkClass, primaryButtonClass } from '@/lib/marketing';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

// Users signed out for being suspended arrive here via a redirect, so the error is on the page rather than the form.
const page = usePage();
</script>

<template>
    <Head title="Log in" />

    <AuthPanel
        title="Welcome back"
        description="Log in to share your photos and connect with your group."
    >
        <StatusNote v-if="status">{{ status }}</StatusNote>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            class="flex flex-col gap-10"
            v-slot="{ errors, processing }"
        >
            <FormField
                id="email"
                label="Email"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
                :error="errors.email ?? page.props.errors.email"
            />

            <FormField
                id="password"
                label="Password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Your password"
                :error="errors.password"
            >
                <template v-if="canResetPassword" #action>
                    <Link
                        :href="request()"
                        class="text-ink-soft hover:text-ink text-xs transition-colors"
                    >
                        Forgot your password?
                    </Link>
                </template>
            </FormField>

            <label
                for="remember"
                class="text-ink flex cursor-pointer items-center gap-3 text-sm"
            >
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    class="accent-ink size-4"
                />
                Keep me logged in
            </label>

            <div class="flex flex-col gap-6">
                <button
                    type="submit"
                    :disabled="processing"
                    data-test="login-button"
                    :class="primaryButtonClass"
                >
                    {{ processing ? 'Logging in…' : 'Log in' }}
                </button>

                <p class="text-ink-soft text-sm">
                    New to StreetWatchers?
                    <Link :href="register()" :class="inlineLinkClass">
                        Join for free
                    </Link>
                </p>
            </div>
        </Form>
    </AuthPanel>
</template>
