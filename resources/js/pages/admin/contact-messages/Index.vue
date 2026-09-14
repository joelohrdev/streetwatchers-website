<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import Pagination from '@/components/admin/Pagination.vue';
import Heading from '@/components/Heading.vue';
import { cn, formatDate } from '@/lib/utils';
import { index, show } from '@/routes/admin/contact-messages';
import type { Option, Paginated } from '@/types';

type MessageRow = {
    id: number;
    name: string;
    email: string;
    topic: string;
    excerpt: string;
    is_read: boolean;
    created_at: string | null;
};

const props = defineProps<{
    messages: Paginated<MessageRow>;
    filters: { topic: string | null; unread: boolean };
    topics: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Messages', href: index() }],
    },
});

const readTabs = computed(() => [
    {
        label: 'All',
        href: index({ mergeQuery: { unread: null, page: null } }),
        active: !props.filters.unread,
    },
    {
        label: 'Unread',
        href: index({ mergeQuery: { unread: 1, page: null } }),
        active: props.filters.unread,
    },
]);

const topicTabs = computed(() => [
    {
        label: 'All topics',
        href: index({ mergeQuery: { topic: null, page: null } }),
        active: props.filters.topic === null,
    },
    ...props.topics.map((topic) => ({
        label: topic.label,
        href: index({ mergeQuery: { topic: topic.value, page: null } }),
        active: props.filters.topic === topic.value,
    })),
]);
</script>

<template>
    <Head title="Messages" />

    <Heading
        title="Messages"
        description="Messages sent through the public contact form. Opening a message marks it as read."
    />

    <div class="flex flex-col gap-3">
        <FilterTabs label="Filter messages by read status" :tabs="readTabs" />
        <FilterTabs label="Filter messages by topic" :tabs="topicTabs" />
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-muted-foreground text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">From</th>
                    <th class="px-4 py-3 font-medium">Topic</th>
                    <th class="px-4 py-3 font-medium">Message</th>
                    <th class="px-4 py-3 font-medium">Received</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr
                    v-for="message in messages.data"
                    :key="message.id"
                    :class="cn(!message.is_read && 'bg-muted/30')"
                >
                    <td class="px-4 py-3">
                        <Link
                            :href="show(message.id)"
                            class="flex items-center gap-2 hover:underline"
                        >
                            <span
                                v-if="!message.is_read"
                                class="bg-primary size-2 shrink-0 rounded-full"
                                aria-label="Unread"
                            />
                            <span
                                :class="cn(!message.is_read && 'font-semibold')"
                            >
                                {{ message.name }}
                            </span>
                        </Link>
                        <p class="text-muted-foreground text-xs">
                            {{ message.email }}
                        </p>
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ message.topic }}
                    </td>
                    <td class="max-w-md px-4 py-3">
                        <Link :href="show(message.id)" class="line-clamp-2">
                            {{ message.excerpt }}
                        </Link>
                    </td>
                    <td
                        class="text-muted-foreground px-4 py-3 whitespace-nowrap"
                    >
                        {{ formatDate(message.created_at) }}
                    </td>
                </tr>
                <tr v-if="messages.data.length === 0">
                    <td
                        colspan="4"
                        class="text-muted-foreground px-4 py-10 text-center"
                    >
                        No messages match this filter.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :paginator="messages" />
</template>
