<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { store as dissolve } from '@/actions/App/Http/Controllers/Admin/CritiqueGroupDissolutionController';
import {
    destroy as removeMember,
    update as moveMember,
} from '@/actions/App/Http/Controllers/Admin/CritiqueGroupMemberController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import Pagination from '@/components/admin/Pagination.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/utils';
import { index } from '@/routes/admin/critique-groups';
import type { Option, Paginated } from '@/types';

type GroupSummary = {
    id: number;
    name: string;
    week_start: string;
    week_end: string;
    max_members: number;
    status: string;
};

type GroupMember = {
    id: number;
    name: string;
    email: string;
    joined_at: string;
};

type GroupRow = GroupSummary & {
    submissions_count: number;
    members: GroupMember[];
};

type OpenGroup = GroupSummary & {
    members_count: number;
};

const props = defineProps<{
    groups: Paginated<GroupRow>;
    openGroups: OpenGroup[];
    filters: { status: string | null };
    statuses: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Critique groups', href: index() }],
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

const moveTargets = (groupId: number) =>
    props.openGroups.filter((group) => group.id !== groupId);

const selectClass =
    'border-input dark:bg-input/30 focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] md:text-sm';
</script>

<template>
    <Head title="Critique groups" />

    <Heading
        title="Critique groups"
        description="Oversee weekly critique circles: dissolve groups and move or remove members."
    />

    <FilterTabs label="Filter critique groups by status" :tabs="tabs" />

    <div v-if="groups.data.length" class="grid gap-6 xl:grid-cols-2">
        <Card v-for="group in groups.data" :key="group.id">
            <CardHeader>
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <CardTitle>{{ group.name }}</CardTitle>
                            <StatusBadge
                                :status="group.status"
                                :label="statusLabel(group.status)"
                            />
                        </div>
                        <CardDescription>
                            {{ formatDate(group.week_start) }} –
                            {{ formatDate(group.week_end) }} ·
                            {{ group.members.length }}/{{
                                group.max_members
                            }}
                            members · {{ group.submissions_count }}
                            {{
                                group.submissions_count === 1
                                    ? 'submission'
                                    : 'submissions'
                            }}
                        </CardDescription>
                    </div>
                    <ActionDialog
                        v-if="group.status !== 'closed'"
                        :form="dissolve.form(group.id)"
                        title="Dissolve this group?"
                        description="The group will be closed. Members and submissions are kept for the record."
                        trigger-label="Dissolve"
                        trigger-variant="destructive"
                        submit-label="Dissolve group"
                        submit-variant="destructive"
                    />
                </div>
            </CardHeader>
            <CardContent>
                <ul
                    v-if="group.members.length"
                    class="divide-y rounded-md border"
                >
                    <li
                        v-for="member in group.members"
                        :key="member.id"
                        class="flex items-center justify-between gap-4 px-3 py-2"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ member.name }}
                            </p>
                            <p class="text-muted-foreground truncate text-xs">
                                {{ member.email }} · Joined
                                {{ formatDate(member.joined_at) }}
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <ActionDialog
                                :form="
                                    moveMember.form({
                                        critiqueGroup: group.id,
                                        user: member.id,
                                    })
                                "
                                :title="`Move ${member.name} to another group?`"
                                description="Their past submissions stay with this group."
                                trigger-label="Move"
                                trigger-variant="ghost"
                                submit-label="Move member"
                                reason="none"
                                :slot-fields="['target_group_id']"
                            >
                                <template #default="{ errors }">
                                    <div class="grid gap-2">
                                        <Label
                                            :for="`target-group-${member.id}`"
                                        >
                                            Destination group
                                        </Label>
                                        <select
                                            :id="`target-group-${member.id}`"
                                            name="target_group_id"
                                            required
                                            :class="selectClass"
                                        >
                                            <option value="" disabled selected>
                                                Choose a group
                                            </option>
                                            <option
                                                v-for="target in moveTargets(
                                                    group.id,
                                                )"
                                                :key="target.id"
                                                :value="target.id"
                                            >
                                                {{ target.name }} ({{
                                                    target.members_count
                                                }}/{{ target.max_members }})
                                            </option>
                                        </select>
                                        <p
                                            v-if="
                                                moveTargets(group.id).length ===
                                                0
                                            "
                                            class="text-muted-foreground text-sm"
                                        >
                                            There are no other open groups.
                                        </p>
                                        <InputError
                                            :message="errors.target_group_id"
                                        />
                                    </div>
                                </template>
                            </ActionDialog>
                            <ActionDialog
                                :form="
                                    removeMember.form({
                                        critiqueGroup: group.id,
                                        user: member.id,
                                    })
                                "
                                :title="`Remove ${member.name} from this group?`"
                                trigger-label="Remove"
                                trigger-variant="ghost"
                                submit-label="Remove member"
                                submit-variant="destructive"
                                reason="none"
                            />
                        </div>
                    </li>
                </ul>
                <p v-else class="text-muted-foreground text-sm">
                    This group has no members.
                </p>
            </CardContent>
        </Card>
    </div>
    <p
        v-else
        class="text-muted-foreground rounded-lg border px-4 py-10 text-center text-sm"
    >
        No critique groups match this filter.
    </p>

    <Pagination :paginator="groups" />
</template>
