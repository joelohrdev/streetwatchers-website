<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { destroy } from '@/actions/App/Http/Controllers/Admin/TagController';
import { store as merge } from '@/actions/App/Http/Controllers/Admin/TagMergeController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import Pagination from '@/components/admin/Pagination.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/admin/tags';
import type { Paginated } from '@/types';

type TagRow = {
    id: number;
    name: string;
    photos_count: number;
};

const props = defineProps<{
    tags: Paginated<TagRow>;
    allTags: { id: number; name: string }[];
    filters: { search: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Tags', href: index() }],
    },
});

const mergeTargets = (tagId: number) =>
    props.allTags.filter((tag) => tag.id !== tagId);

const selectClass =
    'border-input dark:bg-input/30 focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] md:text-sm';
</script>

<template>
    <Head title="Tags" />

    <Heading
        title="Tags"
        description="Merge duplicate tags and clean up tags nobody uses."
    />

    <Form
        v-bind="index.form()"
        :options="{ preserveState: true, preserveScroll: true }"
        class="flex max-w-md gap-2"
    >
        <Label for="tag-search" class="sr-only">Search tags</Label>
        <Input
            id="tag-search"
            name="search"
            type="search"
            :default-value="filters.search"
            placeholder="Search tags"
        />
        <Button type="submit" variant="outline">Search</Button>
    </Form>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-muted-foreground text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">Tag</th>
                    <th class="px-4 py-3 text-right font-medium">Photos</th>
                    <th class="px-4 py-3 text-right font-medium">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="tag in tags.data" :key="tag.id">
                    <td class="px-4 py-3 font-medium">{{ tag.name }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        {{ tag.photos_count }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            <ActionDialog
                                :form="merge.form(tag.id)"
                                :title="`Merge “${tag.name}” into another tag?`"
                                :description="`All ${tag.photos_count} photos tagged “${tag.name}” move to the tag you choose, then “${tag.name}” is deleted.`"
                                trigger-label="Merge"
                                trigger-variant="ghost"
                                submit-label="Merge tags"
                                reason="none"
                                :slot-fields="['target_tag_id']"
                            >
                                <template #default="{ errors }">
                                    <div class="grid gap-2">
                                        <Label :for="`merge-target-${tag.id}`">
                                            Merge into
                                        </Label>
                                        <select
                                            :id="`merge-target-${tag.id}`"
                                            name="target_tag_id"
                                            required
                                            :class="selectClass"
                                        >
                                            <option value="" disabled selected>
                                                Choose a tag
                                            </option>
                                            <option
                                                v-for="target in mergeTargets(
                                                    tag.id,
                                                )"
                                                :key="target.id"
                                                :value="target.id"
                                            >
                                                {{ target.name }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="errors.target_tag_id"
                                        />
                                    </div>
                                </template>
                            </ActionDialog>
                            <ActionDialog
                                v-if="tag.photos_count === 0"
                                :form="destroy.form(tag.id)"
                                :title="`Delete “${tag.name}”?`"
                                description="No photos use this tag, so nothing else changes."
                                trigger-label="Delete"
                                trigger-variant="ghost"
                                submit-label="Delete tag"
                                submit-variant="destructive"
                                reason="none"
                            />
                            <span
                                v-else
                                class="text-muted-foreground inline-flex h-8 items-center px-3 text-xs"
                                title="Tags in use must be merged into another tag instead of deleted."
                            >
                                In use
                            </span>
                        </div>
                    </td>
                </tr>
                <tr v-if="tags.data.length === 0">
                    <td
                        colspan="3"
                        class="text-muted-foreground px-4 py-10 text-center"
                    >
                        No tags found.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="text-muted-foreground text-sm">
        Tags used by photos can't be deleted. Merge them into another tag
        instead.
    </p>

    <Pagination :paginator="tags" />
</template>
