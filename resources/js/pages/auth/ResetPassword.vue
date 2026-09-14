<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import AuthPanel from '@/components/marketing/AuthPanel.vue';
import FormField from '@/components/marketing/FormField.vue';
import { primaryButtonClass } from '@/lib/marketing';
import { update } from '@/routes/password';

defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();
</script>

<template>
    <Head title="Reset password" />

    <AuthPanel
        title="Choose a new password"
        description="Pick something you haven't used here before."
    >
        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            class="flex flex-col gap-10"
            v-slot="{ errors, processing }"
        >
            <FormField
                id="email"
                label="Email"
                type="email"
                name="email"
                autocomplete="email"
                :value="email"
                readonly
                :error="errors.email"
            />

            <FormField
                id="password"
                label="New password"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
                :passwordrules="passwordRules"
                placeholder="Choose a password"
                :error="errors.password"
            />

            <FormField
                id="password_confirmation"
                label="Confirm new password"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                :passwordrules="passwordRules"
                placeholder="Type it again"
                :error="errors.password_confirmation"
            />

            <button
                type="submit"
                :disabled="processing"
                data-test="reset-password-button"
                :class="primaryButtonClass"
            >
                {{ processing ? 'Saving…' : 'Save new password' }}
            </button>
        </Form>
    </AuthPanel>
</template>
