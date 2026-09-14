<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { update as updateStatus } from '@/actions/App/Http/Controllers/Admin/UserStatusController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import Pagination from '@/components/admin/Pagination.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/utils';
import { index } from '@/routes/admin/users';
import type { Option, Paginated } from '@/types';

type UserRow = {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
    photos_count: number;
    created_at: string | null;
};

const props = defineProps<{
    users: Paginated<UserRow>;
    filters: { status: string | null; role: string | null; search: string };
    statuses: Option[];
    roles: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Users', href: index() }],
    },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth.user.id);

const search = ref(props.filters.search);
const role = ref(props.filters.role ?? '');

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

function applyFilters(): void {
    router.get(
        index.url({
            query: {
                status: props.filters.status,
                role: role.value || null,
                search: search.value.trim() || null,
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

const statusLabel = (value: string) =>
    props.statuses.find((status) => status.value === value)?.label;

const roleLabel = (value: string) =>
    props.roles.find((option) => option.value === value)?.label ?? value;
</script>

<template>
    <Head title="Users" />

    <Heading
        title="Users"
        description="Find accounts and suspend, ban or reinstate them. Blocked users are signed out and cannot log in."
    />

    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
    >
        <FilterTabs label="Filter users by status" :tabs="tabs" />

        <form
            class="flex flex-col gap-2 sm:flex-row sm:items-end"
            @submit.prevent="applyFilters"
        >
            <div class="grid gap-1">
                <Label for="user-search" class="sr-only">Search</Label>
                <Input
                    id="user-search"
                    v-model="search"
                    type="search"
                    placeholder="Search name or email"
                    class="sm:w-64"
                />
            </div>
            <div class="grid gap-1">
                <Label for="user-role" class="sr-only">Role</Label>
                <select
                    id="user-role"
                    v-model="role"
                    class="border-input dark:bg-input/30 focus-visible:border-ring focus-visible:ring-ring/50 h-9 rounded-md border bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                >
                    <option value="">All roles</option>
                    <option
                        v-for="option in roles"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>
            <Button type="submit" variant="outline">Search</Button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-muted-foreground text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">User</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 text-right font-medium">Photos</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Joined</th>
                    <th class="px-4 py-3 text-right font-medium">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="user in users.data" :key="user.id">
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ user.name }}</p>
                        <p class="text-muted-foreground text-xs">
                            {{ user.email }}
                        </p>
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ roleLabel(user.role) }}
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        {{ user.photos_count }}
                    </td>
                    <td class="px-4 py-3">
                        <StatusBadge
                            :status="user.status"
                            :label="statusLabel(user.status)"
                        />
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ formatDate(user.created_at) }}
                    </td>
                    <td class="px-4 py-3">
                        <div
                            v-if="user.id !== currentUserId"
                            class="flex justify-end gap-2"
                        >
                            <ActionDialog
                                v-if="user.status === 'active'"
                                :form="updateStatus.form(user.id)"
                                :title="`Suspend ${user.name}?`"
                                description="They will be signed out and blocked from logging in until reinstated."
                                trigger-label="Suspend"
                                trigger-variant="outline"
                                submit-label="Suspend account"
                                submit-variant="destructive"
                                reason="required"
                                reason-placeholder="Why is this account being suspended? This is recorded in the audit log."
                            >
                                <input
                                    type="hidden"
                                    name="status"
                                    value="suspended"
                                />
                            </ActionDialog>
                            <ActionDialog
                                v-if="user.status !== 'banned'"
                                :form="updateStatus.form(user.id)"
                                :title="`Ban ${user.name}?`"
                                description="They will be signed out and permanently blocked from logging in."
                                trigger-label="Ban"
                                trigger-variant="ghost"
                                submit-label="Ban account"
                                submit-variant="destructive"
                                reason="required"
                                reason-placeholder="Why is this account being banned? This is recorded in the audit log."
                            >
                                <input
                                    type="hidden"
                                    name="status"
                                    value="banned"
                                />
                            </ActionDialog>
                            <ActionDialog
                                v-if="user.status !== 'active'"
                                :form="updateStatus.form(user.id)"
                                :title="`Reinstate ${user.name}?`"
                                description="They will be able to log in and use the platform again."
                                trigger-label="Reinstate"
                                trigger-variant="outline"
                                submit-label="Reinstate account"
                                reason="required"
                                reason-placeholder="Why is this account being reinstated? This is recorded in the audit log."
                            >
                                <input
                                    type="hidden"
                                    name="status"
                                    value="active"
                                />
                            </ActionDialog>
                        </div>
                    </td>
                </tr>
                <tr v-if="users.data.length === 0">
                    <td
                        colspan="6"
                        class="text-muted-foreground px-4 py-10 text-center"
                    >
                        No users match these filters.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :paginator="users" />
</template>
