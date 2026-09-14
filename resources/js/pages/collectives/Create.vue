<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/CollectiveController';
import CollectiveForm from '@/components/collectives/CollectiveForm.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { create } from '@/routes/collectives';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Start a collective', href: create() }],
    },
});
</script>

<template>
    <Head title="Start a collective" />

    <div class="mx-auto w-full max-w-3xl px-4 py-6">
        <Heading
            title="Start a collective"
            description="Collectives are independent crews bound by a shared approach or project. You will be the founder and decide who joins. Our team may mark trusted collectives as verified."
        />

        <Form
            v-bind="store.form()"
            class="space-y-6"
            v-slot="{ errors, processing, progress }"
        >
            <CollectiveForm :errors="errors" />

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
                    Create collective
                </Button>
            </div>
        </Form>
    </div>
</template>
