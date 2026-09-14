<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    destroy as revoke,
    store as grant,
} from '@/actions/App/Http/Controllers/Admin/CorrespondentController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import Pagination from '@/components/admin/Pagination.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { formatDate } from '@/lib/utils';
import { index } from '@/routes/admin/correspondents';
import type { Paginated } from '@/types';

type UserRow = {
    id: number;
    name: string;
    email: string;
    bio: string | null;
    articles_count: number;
    correspondent: {
        id: number;
        bio: string;
        is_active: boolean;
        updated_at: string | null;
    } | null;
};

type Program = 'active' | 'inactive' | 'none';

const props = defineProps<{
    users: Paginated<UserRow>;
    filters: { search: string; program: Program | null };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Correspondents', href: index() }],
    },
});

const programOptions: { label: string; value: Program | null }[] = [
    { label: 'All', value: null },
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Never a correspondent', value: 'none' },
];

const tabs = computed(() =>
    programOptions.map((option) => ({
        label: option.label,
        href: index({ mergeQuery: { program: option.value, page: null } }),
        active: props.filters.program === option.value,
    })),
);
</script>

<template>
    <Head title="Correspondents" />

    <Heading
        title="Correspondents"
        description="Grant or revoke correspondent status. Revoked correspondents keep their record for history."
    />

    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <FilterTabs label="Filter by correspondent status" :tabs="tabs" />

        <Form
            v-bind="index.form()"
            :options="{ preserveState: true, preserveScroll: true }"
            class="flex w-full max-w-md gap-2"
        >
            <input
                v-if="filters.program"
                type="hidden"
                name="program"
                :value="filters.program"
            />
            <Input
                name="search"
                type="search"
                :default-value="filters.search"
                placeholder="Search by name or email"
                aria-label="Search users"
            />
            <Button type="submit" variant="outline">Search</Button>
        </Form>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-muted-foreground text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">User</th>
                    <th class="px-4 py-3 font-medium">Correspondent</th>
                    <th class="px-4 py-3 text-right font-medium">Articles</th>
                    <th class="px-4 py-3 font-medium">Last changed</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
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
                    <td class="px-4 py-3">
                        <StatusBadge
                            v-if="user.correspondent?.is_active"
                            status="active"
                        />
                        <StatusBadge
                            v-else-if="user.correspondent"
                            status="inactive"
                        />
                        <span v-else class="text-muted-foreground">—</span>
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        {{ user.articles_count }}
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ formatDate(user.correspondent?.updated_at ?? null) }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end">
                            <ActionDialog
                                v-if="user.correspondent?.is_active"
                                :form="revoke.form(user.correspondent.id)"
                                :title="`Revoke correspondent status from ${user.name}?`"
                                description="They will no longer be a correspondent. Their correspondent record and articles are kept for history."
                                trigger-label="Revoke"
                                trigger-variant="ghost"
                                submit-label="Revoke status"
                                submit-variant="destructive"
                                reason="required"
                                reason-placeholder="Why is correspondent status being revoked? This is recorded in the audit log."
                            />
                            <ActionDialog
                                v-else
                                :form="grant.form()"
                                :title="`Make ${user.name} a correspondent?`"
                                description="They will be able to publish articles as a StreetWatchers correspondent."
                                trigger-label="Grant"
                                trigger-variant="outline"
                                submit-label="Grant status"
                                reason="none"
                                :slot-fields="['bio', 'user_id']"
                                v-slot="{ errors }"
                            >
                                <input
                                    type="hidden"
                                    name="user_id"
                                    :value="user.id"
                                />
                                <InputError :message="errors.user_id" />
                                <div class="grid gap-2">
                                    <Label :for="`bio-${user.id}`">
                                        Correspondent bio
                                    </Label>
                                    <Textarea
                                        :id="`bio-${user.id}`"
                                        name="bio"
                                        rows="4"
                                        required
                                        :default-value="
                                            user.correspondent?.bio ??
                                            user.bio ??
                                            ''
                                        "
                                    />
                                    <InputError :message="errors.bio" />
                                </div>
                            </ActionDialog>
                        </div>
                    </td>
                </tr>
                <tr v-if="users.data.length === 0">
                    <td
                        colspan="5"
                        class="text-muted-foreground px-4 py-10 text-center"
                    >
                        No users match this filter.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :paginator="users" />
</template>
