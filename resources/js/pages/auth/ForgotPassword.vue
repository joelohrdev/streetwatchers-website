<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AuthPanel from '@/components/marketing/AuthPanel.vue';
import FormField from '@/components/marketing/FormField.vue';
import StatusNote from '@/components/marketing/StatusNote.vue';
import { inlineLinkClass, primaryButtonClass } from '@/lib/marketing';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Forgot password" />

    <AuthPanel
        title="Forgot your password?"
        description="Enter the email you joined with and we will send you a link to choose a new password."
    >
        <StatusNote v-if="status">{{ status }}</StatusNote>

        <Form
            v-bind="email.form()"
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
                :error="errors.email"
            />

            <div class="flex flex-col gap-6">
                <button
                    type="submit"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                    :class="primaryButtonClass"
                >
                    {{ processing ? 'Sending…' : 'Email me a reset link' }}
                </button>

                <p class="text-ink-soft text-sm">
                    Remembered it?
                    <Link :href="login()" :class="inlineLinkClass">
                        Back to log in
                    </Link>
                </p>
            </div>
        </Form>
    </AuthPanel>
</template>
