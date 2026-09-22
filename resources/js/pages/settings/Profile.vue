<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import FormField from '@/components/marketing/FormField.vue';
import StatusNote from '@/components/marketing/StatusNote.vue';
import { primaryButtonClass } from '@/lib/marketing';
import { send } from '@/routes/verification';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Profile settings" />

    <div>
        <h2
            class="font-display text-lg font-extrabold tracking-tight uppercase"
        >
            Profile
        </h2>
        <p class="text-ink-soft mt-2 leading-relaxed">
            The name other members see, the email we use to reach you, and your
            Instagram if you want to share it. If you organise a group, your
            Instagram shows next to your name on its pages.
        </p>

        <Form
            v-bind="ProfileController.update.form()"
            class="mt-10 flex flex-col gap-10"
            v-slot="{ errors, processing }"
        >
            <FormField
                id="name"
                label="Name"
                name="name"
                required
                autocomplete="name"
                placeholder="Your full name"
                :value="user.name"
                :error="errors.name"
            />

            <div>
                <FormField
                    id="email"
                    label="Email"
                    type="email"
                    name="email"
                    required
                    autocomplete="username"
                    placeholder="you@example.com"
                    :value="user.email"
                    :error="errors.email"
                />

                <div
                    v-if="page.props.mustVerifyEmail && !user.email_verified_at"
                    class="mt-4"
                >
                    <p class="text-ink-soft text-sm">
                        Your email address is not verified yet.
                        <Link
                            :href="send()"
                            as="button"
                            class="text-ink border-ink cursor-pointer border-b"
                        >
                            Resend the verification email
                        </Link>
                    </p>

                    <StatusNote
                        v-if="page.props.status === 'verification-link-sent'"
                        class="mt-4 mb-0"
                    >
                        A new verification link is on its way to your email
                        address.
                    </StatusNote>
                </div>
            </div>

            <FormField
                id="instagram_handle"
                label="Instagram (optional)"
                name="instagram_handle"
                autocomplete="off"
                maxlength="100"
                placeholder="@yourname"
                :value="
                    user.instagram_handle ? `@${user.instagram_handle}` : ''
                "
                :error="errors.instagram_handle"
            />

            <div>
                <button
                    type="submit"
                    :disabled="processing"
                    data-test="update-profile-button"
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    {{ processing ? 'Saving…' : 'Save' }}
                </button>
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>
