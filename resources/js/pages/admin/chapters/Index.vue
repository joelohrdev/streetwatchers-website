<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import Pagination from '@/components/admin/Pagination.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Heading from '@/components/Heading.vue';
import { formatDate } from '@/lib/utils';
import { index, show } from '@/routes/admin/chapters';
import type { Option, Paginated } from '@/types';

type ChapterRow = {
    id: number;
    name: string;
    slug: string;
    city: string;
    country: string;
    status: string;
    members_count: number;
    admins_count: number;
    created_at: string | null;
};

const props = defineProps<{
    chapters: Paginated<ChapterRow>;
    filters: { status: string | null };
    statuses: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Chapters', href: index() }],
    },
});

const tabs = computed(() => [
    {
        label: 'All',
        href: index({ mergeQuery: { status: null, page: null } }),
        active: props.filters.status === null,
    },
    ...props.statuses.map((status) => ({
        label: status.label,
        href: index({ mergeQuery: { status: status.value, page: null } }),
        active: props.filters.status === status.value,
    })),
]);

const statusLabel = (value: string) =>
    props.statuses.find((status) => status.value === value)?.label;
</script>

<template>
    <Head title="Chapters" />

    <Heading
        title="Chapters"
        description="Approve new chapters, deactivate inactive ones and manage who runs them."
    />

    <FilterTabs label="Filter chapters by status" :tabs="tabs" />

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-muted-foreground text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">Chapter</th>
                    <th class="px-4 py-3 font-medium">Location</th>
                    <th class="px-4 py-3 text-right font-medium">Members</th>
                    <th class="px-4 py-3 text-right font-medium">Admins</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="chapter in chapters.data" :key="chapter.id">
                    <td class="px-4 py-3">
                        <Link
                            :href="show(chapter.slug)"
                            class="font-medium hover:underline"
                        >
                            {{ chapter.name }}
                        </Link>
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ chapter.city }}, {{ chapter.country }}
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        {{ chapter.members_count }}
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        {{ chapter.admins_count }}
                    </td>
                    <td class="px-4 py-3">
                        <StatusBadge
                            :status="chapter.status"
                            :label="statusLabel(chapter.status)"
                        />
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ formatDate(chapter.created_at) }}
                    </td>
                </tr>
                <tr v-if="chapters.data.length === 0">
                    <td
                        colspan="6"
                        class="text-muted-foreground px-4 py-10 text-center"
                    >
                        No chapters match this filter.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :paginator="chapters" />
</template>
