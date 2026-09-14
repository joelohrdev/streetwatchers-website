<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { destroy as unpublish } from '@/actions/App/Http/Controllers/Admin/ArticlePublicationController';
import ActionDialog from '@/components/admin/ActionDialog.vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import Pagination from '@/components/admin/Pagination.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/utils';
import { index } from '@/routes/admin/articles';
import type { Paginated } from '@/types';

type ArticleRow = {
    id: number;
    title: string;
    slug: string;
    author: string;
    published_at: string | null;
    created_at: string | null;
};

type Publication = 'published' | 'draft';

const props = defineProps<{
    articles: Paginated<ArticleRow>;
    filters: { search: string; publication: Publication | null };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Articles', href: index() }],
    },
});

const publicationOptions: { label: string; value: Publication | null }[] = [
    { label: 'All', value: null },
    { label: 'Published', value: 'published' },
    { label: 'Draft', value: 'draft' },
];

const tabs = computed(() =>
    publicationOptions.map((option) => ({
        label: option.label,
        href: index({ mergeQuery: { publication: option.value, page: null } }),
        active: props.filters.publication === option.value,
    })),
);
</script>

<template>
    <Head title="Articles" />

    <Heading
        title="Articles"
        description="Every correspondent article on the platform. Unpublish any article, whoever wrote it."
    />

    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <FilterTabs label="Filter articles by publication" :tabs="tabs" />

        <Form
            v-bind="index.form()"
            :options="{ preserveState: true, preserveScroll: true }"
            class="flex w-full max-w-md gap-2"
        >
            <input
                v-if="filters.publication"
                type="hidden"
                name="publication"
                :value="filters.publication"
            />
            <Input
                name="search"
                type="search"
                :default-value="filters.search"
                placeholder="Search by title"
                aria-label="Search articles"
            />
            <Button type="submit" variant="outline">Search</Button>
        </Form>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-muted-foreground text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">Article</th>
                    <th class="px-4 py-3 font-medium">Author</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Published</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="article in articles.data" :key="article.id">
                    <td class="px-4 py-3 font-medium">{{ article.title }}</td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ article.author }}
                    </td>
                    <td class="px-4 py-3">
                        <StatusBadge
                            :status="
                                article.published_at ? 'published' : 'draft'
                            "
                        />
                    </td>
                    <td class="text-muted-foreground px-4 py-3">
                        {{ formatDate(article.published_at) }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end">
                            <ActionDialog
                                v-if="article.published_at"
                                :form="unpublish.form(article.slug)"
                                :title="`Unpublish “${article.title}”?`"
                                description="The article will be taken offline and returned to draft. The author keeps the article."
                                trigger-label="Unpublish"
                                trigger-variant="ghost"
                                submit-label="Unpublish article"
                                submit-variant="destructive"
                                reason="required"
                                reason-placeholder="Why is this article being unpublished? This is recorded in the audit log."
                            />
                        </div>
                    </td>
                </tr>
                <tr v-if="articles.data.length === 0">
                    <td
                        colspan="5"
                        class="text-muted-foreground px-4 py-10 text-center"
                    >
                        No articles match this filter.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :paginator="articles" />
</template>
