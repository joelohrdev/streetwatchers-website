<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Heading from '@/components/Heading.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes/admin';
import { index as reportsIndex } from '@/routes/admin/reports';

type StatusCount = {
    value: string;
    label: string;
    count: number;
};

const props = defineProps<{
    stats: {
        users: number;
        collectives: number;
        openReports: number;
        chapters: StatusCount[];
        photos: StatusCount[];
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Admin', href: dashboard() }],
    },
});

const breakdowns = computed(() => [
    {
        title: 'Chapters',
        description: 'Chapters by approval status.',
        counts: props.stats.chapters,
    },
    {
        title: 'Photos',
        description: 'Photos by moderation status.',
        counts: props.stats.photos,
    },
]);

const total = (counts: StatusCount[]) =>
    counts.reduce((sum, status) => sum + status.count, 0);
</script>

<template>
    <Head title="Admin" />

    <Heading
        title="Admin"
        description="A snapshot of the platform and what needs attention."
    />

    <div class="grid gap-4 sm:grid-cols-3">
        <Card>
            <CardHeader>
                <CardDescription>Users</CardDescription>
                <CardTitle class="text-3xl tabular-nums">
                    {{ stats.users }}
                </CardTitle>
            </CardHeader>
        </Card>
        <Card>
            <CardHeader>
                <CardDescription>Collectives</CardDescription>
                <CardTitle class="text-3xl tabular-nums">
                    {{ stats.collectives }}
                </CardTitle>
            </CardHeader>
        </Card>
        <Link
            :href="reportsIndex({ query: { status: 'open' } })"
            class="rounded-xl transition-shadow hover:shadow-md"
        >
            <Card class="h-full">
                <CardHeader>
                    <CardDescription>Open reports</CardDescription>
                    <CardTitle class="text-3xl tabular-nums">
                        {{ stats.openReports }}
                    </CardTitle>
                </CardHeader>
            </Card>
        </Link>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <Card v-for="breakdown in breakdowns" :key="breakdown.title">
            <CardHeader>
                <CardTitle>
                    {{ breakdown.title }}
                    <span
                        class="text-muted-foreground font-normal tabular-nums"
                    >
                        · {{ total(breakdown.counts) }}
                    </span>
                </CardTitle>
                <CardDescription>{{ breakdown.description }}</CardDescription>
            </CardHeader>
            <CardContent>
                <ul class="divide-y rounded-md border">
                    <li
                        v-for="status in breakdown.counts"
                        :key="status.value"
                        class="flex items-center justify-between px-3 py-2"
                    >
                        <StatusBadge
                            :status="status.value"
                            :label="status.label"
                        />
                        <span class="text-sm font-medium tabular-nums">
                            {{ status.count }}
                        </span>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
