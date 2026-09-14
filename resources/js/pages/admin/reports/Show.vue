<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ImageOff } from '@lucide/vue';
import { computed, ref } from 'vue';
import { update as updatePhotoStatus } from '@/actions/App/Http/Controllers/Admin/ReportPhotoStatusController';
import { update as updateReport } from '@/actions/App/Http/Controllers/Admin/ReportController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import AuditTimeline from '@/components/admin/AuditTimeline.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import InputError from '@/components/InputError.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { formatDate, formatDateTime } from '@/lib/utils';
import { index } from '@/routes/admin/reports';
import type { Option, TimelineEntry } from '@/types';

type PhotoContent = {
    type: 'photo';
    id: number;
    title: string;
    caption: string | null;
    context_story: string;
    image_url: string;
    location_name: string | null;
    consent_type: string;
    status: string;
    author: string;
};

type CommentContent = {
    type: 'comment' | 'critique_comment';
    id: number;
    title: string;
    body: string;
    author: string;
};

type ArticleContent = {
    type: 'article';
    id: number;
    title: string;
    body: string;
    author: string;
    published_at: string | null;
};

type UserContent = {
    type: 'user';
    id: number;
    title: string;
    email: string;
    status: string;
};

const props = defineProps<{
    report: {
        id: number;
        reason: string;
        status: string;
        is_public_submission: boolean;
        reporter: { name: string | null; email: string } | null;
        created_at: string | null;
    };
    reportable:
        | PhotoContent
        | CommentContent
        | ArticleContent
        | UserContent
        | null;
    history: TimelineEntry[];
    statuses: Option[];
    photoStatuses: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Reports', href: index() }],
    },
});

const imageFailed = ref(false);

const statusLabel = computed(
    () =>
        props.statuses.find((status) => status.value === props.report.status)
            ?.label,
);

const selectClass =
    'border-input dark:bg-input/30 focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]';
</script>

<template>
    <Head :title="`Report #${report.id}`" />

    <div
        class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
    >
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-xl font-semibold tracking-tight">
                    Report #{{ report.id }}
                </h1>
                <StatusBadge :status="report.status" :label="statusLabel" />
                <span
                    v-if="report.is_public_submission"
                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                >
                    Public form
                </span>
            </div>
            <p class="text-muted-foreground text-sm">
                Received {{ formatDate(report.created_at) }}
            </p>
        </div>

        <div class="flex shrink-0 flex-wrap gap-2">
            <ActionDialog
                :form="updateReport.form(report.id)"
                title="Update report status"
                description="Mark the report as reviewed, actioned or dismissed."
                trigger-label="Update status"
                submit-label="Save status"
                reason="optional"
                :slot-fields="['status']"
            >
                <template #default="{ errors }">
                    <div class="grid gap-2">
                        <Label for="report-status">Status</Label>
                        <select
                            id="report-status"
                            name="status"
                            :class="selectClass"
                            :value="report.status"
                        >
                            <option
                                v-for="status in statuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>
                </template>
            </ActionDialog>

            <ActionDialog
                v-if="reportable?.type === 'photo'"
                :form="updatePhotoStatus.form(report.id)"
                title="Take action on this photo"
                description="Flagging hides the photo pending review; removing takes it down. The report is marked as actioned."
                trigger-label="Flag or remove photo"
                trigger-variant="destructive"
                submit-label="Update photo"
                submit-variant="destructive"
                reason="required"
                reason-placeholder="Why is this photo being flagged or removed? This is recorded in the audit log."
                :slot-fields="['status']"
            >
                <template #default="{ errors }">
                    <div class="grid gap-2">
                        <Label for="photo-status">Photo status</Label>
                        <select
                            id="photo-status"
                            name="status"
                            required
                            :class="selectClass"
                        >
                            <option
                                v-for="status in photoStatuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>
                </template>
            </ActionDialog>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <Card class="lg:col-span-2">
            <CardHeader>
                <CardTitle>Reported content</CardTitle>
                <CardDescription v-if="reportable">
                    {{ reportable.title }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <p
                    v-if="reportable === null"
                    class="text-muted-foreground text-sm"
                >
                    The reported content has since been deleted.
                </p>

                <div
                    v-else-if="reportable.type === 'photo'"
                    class="grid gap-6 md:grid-cols-2"
                >
                    <div
                        class="bg-muted flex aspect-[4/3] items-center justify-center overflow-hidden rounded-md"
                    >
                        <img
                            v-if="!imageFailed"
                            :src="reportable.image_url"
                            :alt="reportable.title"
                            class="size-full object-cover"
                            @error="imageFailed = true"
                        />
                        <div
                            v-else
                            class="text-muted-foreground flex flex-col items-center gap-2 text-sm"
                        >
                            <ImageOff class="size-6" />
                            Image unavailable
                        </div>
                    </div>
                    <dl class="grid content-start gap-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Status</dt>
                            <dd><StatusBadge :status="reportable.status" /></dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Photographer</dt>
                            <dd>{{ reportable.author }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Consent</dt>
                            <dd>{{ reportable.consent_type }}</dd>
                        </div>
                        <div v-if="reportable.location_name">
                            <dt class="text-muted-foreground">Location</dt>
                            <dd>{{ reportable.location_name }}</dd>
                        </div>
                        <div v-if="reportable.caption">
                            <dt class="text-muted-foreground">Caption</dt>
                            <dd>{{ reportable.caption }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Context story</dt>
                            <dd class="whitespace-pre-line">
                                {{ reportable.context_story }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div
                    v-else-if="
                        reportable.type === 'comment' ||
                        reportable.type === 'critique_comment'
                    "
                    class="space-y-2 text-sm"
                >
                    <p class="text-muted-foreground">
                        Written by {{ reportable.author }}
                    </p>
                    <blockquote
                        class="bg-muted rounded-md px-4 py-3 whitespace-pre-line"
                    >
                        {{ reportable.body }}
                    </blockquote>
                </div>

                <div
                    v-else-if="reportable.type === 'article'"
                    class="space-y-2 text-sm"
                >
                    <p class="text-muted-foreground">
                        By {{ reportable.author }} ·
                        {{
                            reportable.published_at
                                ? `Published ${formatDate(reportable.published_at)}`
                                : 'Not published'
                        }}
                    </p>
                    <p class="whitespace-pre-line">{{ reportable.body }}</p>
                </div>

                <dl
                    v-else-if="reportable.type === 'user'"
                    class="grid gap-3 text-sm"
                >
                    <div>
                        <dt class="text-muted-foreground">Email</dt>
                        <dd>{{ reportable.email }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Account status</dt>
                        <dd><StatusBadge :status="reportable.status" /></dd>
                    </div>
                </dl>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Report</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4 text-sm">
                <div>
                    <p class="text-muted-foreground">Reported by</p>
                    <template v-if="report.reporter">
                        <p v-if="report.reporter.name">
                            {{ report.reporter.name }}
                        </p>
                        <p class="text-muted-foreground">
                            {{ report.reporter.email }}
                        </p>
                    </template>
                    <p v-else>Anonymous</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Reason</p>
                    <p class="whitespace-pre-line">{{ report.reason }}</p>
                </div>
                <div v-if="report.created_at">
                    <p class="text-muted-foreground">Submitted</p>
                    <p>{{ formatDateTime(report.created_at) }}</p>
                </div>
            </CardContent>
        </Card>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>History</CardTitle>
            <CardDescription>
                Actions on this report and the reported content, newest first.
            </CardDescription>
        </CardHeader>
        <CardContent>
            <AuditTimeline :entries="history" />
        </CardContent>
    </Card>
</template>
