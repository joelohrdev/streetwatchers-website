<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import Pagination from '@/components/admin/Pagination.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Heading from '@/components/Heading.vue';
import { formatDate } from '@/lib/utils';
import { index, show } from '@/routes/admin/reports';
import type { Option, Paginated } from '@/types';

type ReportRow = {
    id: number;
    reportable: {
        type: string;
        type_label: string;
        id: number;
        label: string;
    };
    reason: string;
    status: string;
    is_public_submission: boolean;
    reporter: string | null;
    created_at: string | null;
};

const props = defineProps<{
    reports: Paginated<ReportRow>;
    filters: { status: string | null; type: string | null };
    statuses: Option[];
    types: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Reports', href: index() }],
    },
});

const statusTabs = computed(() => [
    {
        label: 'All statuses',
        href: index({ mergeQuery: { status: null, page: null } }),
        active: props.filters.status === null,
    },
    ...props.statuses.map((status) => ({
        label: status.label,
        href: index({ mergeQuery: { status: status.value, page: null } }),
        active: props.filters.status === status.value,
    })),
]);

const typeTabs = computed(() => [
    {
        label: 'All content',
        href: index({ mergeQuery: { type: null, page: null } }),
        active: props.filters.type === null,
    },
    ...props.types.map((type) => ({
        label: type.label,
        href: index({ mergeQuery: { type: type.value, page: null } }),
        active: props.filters.type === type.value,
    })),
]);

const statusLabel = (value: string) =>
    props.statuses.find((status) => status.value === value)?.label;
</script>

<template>
    <Head title="Reports" />

    <Heading
        title="Reports"
        description="Review reported content and removal requests, and take action where needed."
    />

    <div class="flex flex-col gap-3">
        <FilterTabs label="Filter reports by status" :tabs="statusTabs" />
        <FilterTabs label="Filter reports by content type" :tabs="typeTabs" />
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-muted-foreground text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">Content</th>
                    <th class="px-4 py-3 font-medium">Reason</th>
                    <th class="px-4 py-3 font-medium">Reported by</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Received</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="report in reports.data" :key="report.id">
                    <td class="px-4 py-3">
                        <Link
                            :href="show(report.id)"
                            class="font-medium hover:underline"
                        >
                            {{ report.reportable.label }}
                        </Link>
                        <p class="text-muted-foreground text-xs">
                            {{ report.reportable.type_label }} #{{
                                report.reportable.id
                            }}
                        </p>
                    </td>
                    <td class="text-muted-foreground max-w-sm px-4 py-3">
                        {{ report.reason }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1">
                            <span>{{ report.reporter ?? 'Anonymous' }}</span>
                            <span
                                v-if="report.is_public_submission"
                                class="inline-flex w-fit items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                            >
                                Public form
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <StatusBadge
                            :status="report.status"
                            :label="statusLabel(report.status)"
                        />
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ formatDate(report.created_at) }}
                    </td>
                </tr>
                <tr v-if="reports.data.length === 0">
                    <td
                        colspan="5"
                        class="text-muted-foreground px-4 py-10 text-center"
                    >
                        No reports match these filters.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :paginator="reports" />
</template>
