<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AuthPanel from '@/components/marketing/AuthPanel.vue';
import StatusNote from '@/components/marketing/StatusNote.vue';
import { secondaryButtonClass } from '@/lib/marketing';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Verify your email" />

    <AuthPanel
        title="Check your inbox"
        description="We sent you a link to confirm your email address. Open it to finish setting up your account."
    >
        <StatusNote v-if="status === 'verification-link-sent'">
            A new verification link is on its way to the email address you
            joined with.
        </StatusNote>

        <Form
            v-bind="send.form()"
            class="flex flex-col gap-6"
            v-slot="{ processing }"
        >
            <button
                type="submit"
                :disabled="processing"
                :class="secondaryButtonClass"
            >
                {{ processing ? 'Sending…' : 'Resend the email' }}
            </button>

            <p class="text-ink-soft text-sm">
                Signed up with the wrong address?
                <Link
                    :href="logout()"
                    as="button"
                    class="text-ink border-ink cursor-pointer border-b"
                >
                    Log out
                </Link>
            </p>
        </Form>
    </AuthPanel>
</template>
