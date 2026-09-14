<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import FormField from '@/components/marketing/FormField.vue';
import { primaryButtonClass, secondaryButtonClass } from '@/lib/marketing';

const confirming = ref(false);
</script>

<template>
    <div class="border-hairline border-t pt-12">
        <h2
            class="font-display text-lg font-extrabold tracking-tight uppercase"
        >
            Delete account
        </h2>
        <p class="text-ink-soft mt-2 leading-relaxed">
            Deleting your account permanently removes your profile, photos and
            group memberships. This cannot be undone.
        </p>

        <button
            v-if="!confirming"
            type="button"
            data-test="delete-user-button"
            :class="[secondaryButtonClass, 'mt-8 sm:w-auto']"
            @click="confirming = true"
        >
            Delete account
        </button>

        <Form
            v-else
            v-bind="ProfileController.destroy.form()"
            reset-on-success
            :options="{ preserveScroll: true }"
            class="border-ink mt-8 flex flex-col gap-8 border-l-2 pl-6"
            v-slot="{ errors, processing, reset, clearErrors }"
        >
            <p class="text-ink leading-relaxed">
                Are you sure? Enter your password to confirm you want to
                permanently delete your account.
            </p>

            <FormField
                id="delete-password"
                label="Password"
                type="password"
                name="password"
                autocomplete="current-password"
                placeholder="Your password"
                autofocus
                :error="errors.password"
            />

            <div class="flex flex-col gap-3 sm:flex-row">
                <button
                    type="submit"
                    :disabled="processing"
                    data-test="confirm-delete-user-button"
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    {{ processing ? 'Deleting…' : 'Delete my account' }}
                </button>
                <button
                    type="button"
                    :class="[secondaryButtonClass, 'sm:w-auto']"
                    @click="
                        () => {
                            clearErrors();
                            reset();
                            confirming = false;
                        }
                    "
                >
                    Cancel
                </button>
            </div>
        </Form>
    </div>
</template>
