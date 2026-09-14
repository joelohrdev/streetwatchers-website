<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import type { ButtonVariants } from '@/components/ui/button';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

/**
 * A confirmation dialog that submits one admin action, optionally asking for a reason
 * that is written to the audit log. Extra inputs go in the default slot.
 */
const {
    form,
    title,
    description,
    triggerLabel,
    triggerVariant = 'outline',
    submitLabel,
    submitVariant = 'default',
    reason = 'optional',
    reasonPlaceholder = 'Recorded in the audit log.',
    slotFields = [],
} = defineProps<{
    form: { action: string; method: 'get' | 'post' };
    title: string;
    description?: string;
    triggerLabel: string;
    triggerVariant?: ButtonVariants['variant'];
    submitLabel: string;
    submitVariant?: ButtonVariants['variant'];
    reason?: 'required' | 'optional' | 'none';
    reasonPlaceholder?: string;
    /** Fields whose errors the default slot already displays next to its inputs. */
    slotFields?: string[];
}>();

const open = ref(false);
const shownElsewhere = computed(() => new Set(['reason', ...slotFields]));
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <slot name="trigger">
                <Button :variant="triggerVariant" size="sm">
                    {{ triggerLabel }}
                </Button>
            </slot>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="form"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                reset-on-success
                @success="open = false"
                v-slot="{ errors, processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription v-if="description">
                        {{ description }}
                    </DialogDescription>
                </DialogHeader>

                <slot :errors="errors" />

                <div v-if="reason !== 'none'" class="grid gap-2">
                    <Label for="action-reason">
                        Reason
                        <span
                            v-if="reason === 'optional'"
                            class="text-muted-foreground font-normal"
                        >
                            (optional)
                        </span>
                    </Label>
                    <Textarea
                        id="action-reason"
                        name="reason"
                        rows="3"
                        :required="reason === 'required'"
                        :placeholder="reasonPlaceholder"
                    />
                    <InputError :message="errors.reason" />
                </div>

                <!-- Errors about the record itself, e.g. "Only pending chapters can be approved." -->
                <template v-for="(message, field) in errors" :key="field">
                    <InputError
                        v-if="!shownElsewhere.has(String(field))"
                        :message="message"
                    />
                </template>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :variant="submitVariant"
                        :disabled="processing"
                    >
                        {{ submitLabel }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
