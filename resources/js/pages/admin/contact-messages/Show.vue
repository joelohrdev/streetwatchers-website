<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Mail } from '@lucide/vue';
import { computed } from 'vue';
import { destroy } from '@/actions/App/Http/Controllers/Admin/ContactMessageController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime } from '@/lib/utils';
import { index } from '@/routes/admin/contact-messages';

const props = defineProps<{
    contactMessage: {
        id: number;
        name: string;
        email: string;
        topic: string;
        message: string;
        is_member: boolean;
        created_at: string | null;
        read_at: string | null;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Messages', href: index() }],
    },
});

const replyHref = computed(
    () =>
        `mailto:${encodeURIComponent(props.contactMessage.email)}?subject=${encodeURIComponent(`Re: ${props.contactMessage.topic}`)}`,
);
</script>

<template>
    <Head :title="`Message from ${contactMessage.name}`" />

    <div>
        <Button as-child variant="ghost" size="sm" class="-ml-2">
            <Link :href="index()">
                <ArrowLeft />
                All messages
            </Link>
        </Button>
    </div>

    <Card class="max-w-3xl">
        <CardHeader class="gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <StatusBadge status="open" :label="contactMessage.topic" />
                <StatusBadge
                    v-if="contactMessage.is_member"
                    status="verified"
                    label="Member"
                />
            </div>
            <CardTitle class="text-xl">{{ contactMessage.name }}</CardTitle>
            <p class="text-muted-foreground text-sm">
                {{ contactMessage.email }}
                <template v-if="contactMessage.created_at">
                    · Received {{ formatDateTime(contactMessage.created_at) }}
                </template>
            </p>
        </CardHeader>
        <CardContent class="space-y-6">
            <p
                class="bg-muted rounded-md px-4 py-3 leading-relaxed whitespace-pre-line"
            >
                {{ contactMessage.message }}
            </p>

            <div class="flex flex-wrap gap-2">
                <Button as-child>
                    <a :href="replyHref">
                        <Mail />
                        Reply by email
                    </a>
                </Button>
                <ActionDialog
                    :form="destroy.form(contactMessage.id)"
                    title="Delete this message?"
                    description="The message and the sender's details are permanently deleted, for example to honor a request to erase their data. The audit log only records that a message was deleted."
                    trigger-label="Delete"
                    trigger-variant="outline"
                    submit-label="Delete message"
                    submit-variant="destructive"
                    reason="none"
                />
            </div>
        </CardContent>
    </Card>
</template>
