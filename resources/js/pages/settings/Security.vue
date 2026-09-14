<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import FormField from '@/components/marketing/FormField.vue';
import { primaryButtonClass } from '@/lib/marketing';

defineProps<{
    passwordRules: string;
}>();
</script>

<template>
    <Head title="Password settings" />

    <div>
        <h2
            class="font-display text-lg font-extrabold tracking-tight uppercase"
        >
            Password
        </h2>
        <p class="text-ink-soft mt-2 leading-relaxed">
            Use a long, unique password to keep your account secure.
        </p>

        <Form
            v-bind="SecurityController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="mt-10 flex flex-col gap-10"
            v-slot="{ errors, processing }"
        >
            <FormField
                id="current_password"
                label="Current password"
                type="password"
                name="current_password"
                autocomplete="current-password"
                placeholder="Your current password"
                :error="errors.current_password"
            />

            <FormField
                id="password"
                label="New password"
                type="password"
                name="password"
                autocomplete="new-password"
                placeholder="Choose a new password"
                :passwordrules="passwordRules"
                :error="errors.password"
            />

            <FormField
                id="password_confirmation"
                label="Confirm new password"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                placeholder="Type it again"
                :passwordrules="passwordRules"
                :error="errors.password_confirmation"
            />

            <div>
                <button
                    type="submit"
                    :disabled="processing"
                    data-test="update-password-button"
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    {{ processing ? 'Saving…' : 'Save password' }}
                </button>
            </div>
        </Form>
    </div>
</template>
