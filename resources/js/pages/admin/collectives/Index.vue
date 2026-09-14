<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { destroy as removeCollective } from '@/actions/App/Http/Controllers/Admin/CollectiveController';
import {
    destroy as unverify,
    store as verify,
} from '@/actions/App/Http/Controllers/Admin/CollectiveVerificationController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import Pagination from '@/components/admin/Pagination.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/utils';
import { index } from '@/routes/admin/collectives';
import type { Paginated } from '@/types';

type CollectiveRow = {
    id: number;
    name: string;
    slug: string;
    website_url: string | null;
    is_verified: boolean;
    is_open_for_applications: boolean;
    members_count: number;
    created_at: string | null;
};

defineProps<{
    collectives: Paginated<CollectiveRow>;
    filters: { search: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Collectives', href: index() }],
    },
});
</script>

<template>
    <Head title="Collectives" />

    <Heading
        title="Collectives"
        description="Verify trusted collectives and remove ones that break the rules. Membership approval stays with each collective's founders."
    />

    <Form
        v-bind="index.form()"
        :options="{ preserveState: true, preserveScroll: true }"
        class="flex max-w-md gap-2"
    >
        <Input
            name="search"
            type="search"
            :default-value="filters.search"
            placeholder="Search by name"
            aria-label="Search collectives"
        />
        <Button type="submit" variant="outline">Search</Button>
    </Form>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-muted-foreground text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">Collective</th>
                    <th class="px-4 py-3 text-right font-medium">Members</th>
                    <th class="px-4 py-3 font-medium">Applications</th>
                    <th class="px-4 py-3 font-medium">Created</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="collective in collectives.data" :key="collective.id">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">
                                {{ collective.name }}
                            </span>
                            <StatusBadge
                                v-if="collective.is_verified"
                                status="verified"
                            />
                        </div>
                        <a
                            v-if="collective.website_url"
                            :href="collective.website_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-muted-foreground text-xs hover:underline"
                        >
                            {{ collective.website_url }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        {{ collective.members_count }}
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{
                            collective.is_open_for_applications
                                ? 'Open'
                                : 'Closed'
                        }}
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ formatDate(collective.created_at) }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <ActionDialog
                                v-if="collective.is_verified"
                                :form="unverify.form(collective.slug)"
                                :title="`Remove verification from ${collective.name}?`"
                                description="The verified badge will no longer appear in the public directory."
                                trigger-label="Unverify"
                                trigger-variant="ghost"
                                submit-label="Unverify"
                                reason="none"
                            />
                            <ActionDialog
                                v-else
                                :form="verify.form(collective.slug)"
                                :title="`Verify ${collective.name}?`"
                                description="A verified badge will appear next to this collective in the public directory."
                                trigger-label="Verify"
                                trigger-variant="outline"
                                submit-label="Verify"
                                reason="none"
                            />
                            <ActionDialog
                                :form="removeCollective.form(collective.slug)"
                                :title="`Remove ${collective.name}?`"
                                description="The collective is soft deleted: it disappears from the platform, but the record is kept and can be restored."
                                trigger-label="Remove"
                                trigger-variant="ghost"
                                submit-label="Remove collective"
                                submit-variant="destructive"
                                reason="required"
                                reason-placeholder="Why is this collective being removed? This is recorded in the audit log."
                            />
                        </div>
                    </td>
                </tr>
                <tr v-if="collectives.data.length === 0">
                    <td
                        colspan="5"
                        class="text-muted-foreground px-4 py-10 text-center"
                    >
                        No collectives found.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :paginator="collectives" />
</template>
