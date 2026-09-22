<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { store as approve } from '@/actions/App/Http/Controllers/Admin/ChapterApprovalController';
import {
    destroy as removeAdmin,
    store as addAdmin,
} from '@/actions/App/Http/Controllers/Admin/ChapterAdminController';
import { store as deactivate } from '@/actions/App/Http/Controllers/Admin/ChapterDeactivationController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import AuditTimeline from '@/components/admin/AuditTimeline.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/utils';
import { index } from '@/routes/admin/chapters';
import type { Option, TimelineEntry } from '@/types';

type Member = {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'member';
    joined_at: string;
};

const props = defineProps<{
    chapter: {
        id: number;
        name: string;
        slug: string;
        city: string;
        country: string;
        description: string;
        status: string;
        created_at: string | null;
    };
    members: Member[];
    history: TimelineEntry[];
    statuses: Option[];
    requiredAdmins: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Chapters', href: index() }],
    },
});

const admins = computed(() =>
    props.members.filter((member) => member.role === 'admin'),
);
const regularMembers = computed(() =>
    props.members.filter((member) => member.role === 'member'),
);
const statusLabel = computed(
    () =>
        props.statuses.find((status) => status.value === props.chapter.status)
            ?.label,
);
const hasEnoughAdmins = computed(
    () => admins.value.length >= props.requiredAdmins,
);
</script>

<template>
    <Head :title="chapter.name" />

    <div
        class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
    >
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold tracking-tight">
                    {{ chapter.name }}
                </h1>
                <StatusBadge :status="chapter.status" :label="statusLabel" />
            </div>
            <p class="text-muted-foreground text-sm">
                {{ chapter.city }}, {{ chapter.country }} · Created
                {{ formatDate(chapter.created_at) }}
            </p>
            <p class="max-w-2xl text-sm">{{ chapter.description }}</p>
        </div>

        <div class="flex shrink-0 gap-2">
            <ActionDialog
                v-if="chapter.status === 'pending'"
                :form="approve.form(chapter.slug)"
                title="Approve this chapter?"
                :description="
                    hasEnoughAdmins
                        ? 'The chapter will go live and appear in the public directory.'
                        : `A chapter needs at least ${requiredAdmins} ${requiredAdmins === 1 ? 'admin' : 'admins'} before it can be approved. It has ${admins.length}.`
                "
                trigger-label="Approve"
                trigger-variant="default"
                submit-label="Approve chapter"
                reason="none"
            />
            <ActionDialog
                v-if="chapter.status === 'active'"
                :form="deactivate.form(chapter.slug)"
                title="Deactivate this chapter?"
                description="The chapter will be hidden from the public directory. Members keep their accounts."
                trigger-label="Deactivate"
                trigger-variant="destructive"
                submit-label="Deactivate chapter"
                submit-variant="destructive"
                reason="required"
                reason-placeholder="Why is this chapter being deactivated? This is recorded in the audit log."
            />
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Chapter admins</CardTitle>
                <CardDescription>
                    Admins run the chapter's events and content.
                    {{
                        requiredAdmins === 1
                            ? 'One is needed for approval.'
                            : `${requiredAdmins} are needed for approval.`
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <ul v-if="admins.length" class="divide-y rounded-md border">
                    <li
                        v-for="admin in admins"
                        :key="admin.id"
                        class="flex items-center justify-between gap-4 px-3 py-2"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ admin.name }}
                            </p>
                            <p class="text-muted-foreground truncate text-xs">
                                {{ admin.email }}
                            </p>
                        </div>
                        <ActionDialog
                            :form="
                                removeAdmin.form({
                                    chapter: chapter.slug,
                                    user: admin.id,
                                })
                            "
                            :title="`Remove ${admin.name} as an admin?`"
                            description="They will stay in the chapter as a regular member."
                            trigger-label="Remove"
                            trigger-variant="ghost"
                            submit-label="Remove admin"
                            submit-variant="destructive"
                            reason="none"
                        />
                    </li>
                </ul>
                <p v-else class="text-muted-foreground text-sm">
                    This chapter has no admins.
                </p>

                <Form
                    v-bind="addAdmin.form(chapter.slug)"
                    :options="{ preserveScroll: true }"
                    reset-on-success
                    class="grid gap-2"
                    v-slot="{ errors, processing }"
                >
                    <Label for="admin-email">Add an admin by email</Label>
                    <div class="flex gap-2">
                        <Input
                            id="admin-email"
                            name="email"
                            type="email"
                            required
                            placeholder="organizer@example.com"
                        />
                        <Button type="submit" :disabled="processing">
                            Add
                        </Button>
                    </div>
                    <InputError :message="errors.email" />
                </Form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Members</CardTitle>
                <CardDescription>
                    {{ regularMembers.length }} regular
                    {{ regularMembers.length === 1 ? 'member' : 'members' }}.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ul
                    v-if="regularMembers.length"
                    class="divide-y rounded-md border"
                >
                    <li
                        v-for="member in regularMembers"
                        :key="member.id"
                        class="flex items-center justify-between gap-4 px-3 py-2"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ member.name }}
                            </p>
                            <p class="text-muted-foreground truncate text-xs">
                                Joined {{ formatDate(member.joined_at) }}
                            </p>
                        </div>
                        <Form
                            v-bind="addAdmin.form(chapter.slug)"
                            :options="{ preserveScroll: true }"
                            v-slot="{ processing }"
                        >
                            <input
                                type="hidden"
                                name="email"
                                :value="member.email"
                            />
                            <Button
                                type="submit"
                                variant="ghost"
                                size="sm"
                                :disabled="processing"
                            >
                                Make admin
                            </Button>
                        </Form>
                    </li>
                </ul>
                <p v-else class="text-muted-foreground text-sm">
                    No regular members.
                </p>
            </CardContent>
        </Card>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>History</CardTitle>
            <CardDescription>
                Every admin action on this chapter, newest first.
            </CardDescription>
        </CardHeader>
        <CardContent>
            <AuditTimeline :entries="history" />
        </CardContent>
    </Card>
</template>
