<script setup lang="ts">
import { Form, Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { update } from '@/actions/App/Http/Controllers/CollectiveController';
import type { CollectiveFormDefaults } from '@/components/collectives/CollectiveForm.vue';
import CollectiveForm from '@/components/collectives/CollectiveForm.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { edit, show } from '@/routes/collectives';

const props = defineProps<{
    collective: CollectiveFormDefaults & { slug: string };
}>();

// The breadcrumb needs the collective's slug, so it is set once the props are known.
setLayoutProps({
    breadcrumbs: [
        { title: 'Edit collective', href: edit(props.collective.slug) },
    ],
});
</script>

<template>
    <Head :title="`Edit ${collective.name}`" />

    <div class="mx-auto w-full max-w-3xl px-4 py-6">
        <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
            <Link :href="show(collective.slug)">
                <ArrowLeft />
                Back to {{ collective.name }}
            </Link>
        </Button>

        <Heading
            title="Edit collective"
            description="Update how the collective appears in the directory and whether people can apply to join."
        />

        <Form
            v-bind="update.form(collective.slug)"
            class="space-y-6"
            v-slot="{ errors, processing, progress }"
        >
            <CollectiveForm :collective="collective" :errors="errors" />

            <div
                class="flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-end"
            >
                <progress
                    v-if="progress"
                    :value="progress.percentage"
                    max="100"
                    class="w-full sm:w-40"
                >
                    {{ progress.percentage }}%
                </progress>
                <Button type="submit" :disabled="processing">
                    <Spinner v-if="processing" />
                    Save changes
                </Button>
            </div>
        </Form>
    </div>
</template>
