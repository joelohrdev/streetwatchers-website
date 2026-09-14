<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import AuthPanel from '@/components/marketing/AuthPanel.vue';
import FormField from '@/components/marketing/FormField.vue';
import { primaryButtonClass } from '@/lib/marketing';
import { store } from '@/routes/password/confirm';
</script>

<template>
    <Head title="Confirm password" />

    <AuthPanel
        title="Confirm your password"
        description="This is a secure area. Enter your password again to continue."
    >
        <Form
            v-bind="store.form()"
            reset-on-success
            class="flex flex-col gap-10"
            v-slot="{ errors, processing }"
        >
            <FormField
                id="password"
                label="Password"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="current-password"
                placeholder="Your password"
                :error="errors.password"
            />

            <button
                type="submit"
                :disabled="processing"
                data-test="confirm-password-button"
                :class="primaryButtonClass"
            >
                {{ processing ? 'Checking…' : 'Confirm password' }}
            </button>
        </Form>
    </AuthPanel>
</template>
