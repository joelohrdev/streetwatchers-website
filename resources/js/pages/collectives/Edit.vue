<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { update } from '@/actions/App/Http/Controllers/CollectiveController';
import type { CollectiveFormDefaults } from '@/components/collectives/CollectiveForm.vue';
import CollectiveForm from '@/components/collectives/CollectiveForm.vue';
import { primaryButtonClass } from '@/lib/marketing';
import { show } from '@/routes/collectives';

defineProps<{
    collective: CollectiveFormDefaults & { slug: string };
}>();
</script>

<template>
    <Head :title="`Edit ${collective.name}`" />

    <section class="mx-auto w-full max-w-2xl px-6 py-20 md:px-10 md:py-28">
        <Link
            :href="show(collective.slug)"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            Back to {{ collective.name }}
        </Link>

        <p
            class="font-display text-ink-soft mt-12 text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Collectives
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            Edit {{ collective.name }}
        </h1>
        <p class="text-ink-soft mt-6 text-lg leading-relaxed">
            Update how the collective appears in the directory and whether
            people can apply to join.
        </p>

        <Form
            v-bind="update.form(collective.slug)"
            class="mt-14 flex flex-col gap-14"
            v-slot="{ errors, processing, progress }"
        >
            <CollectiveForm :collective="collective" :errors="errors" />

            <div
                class="border-hairline flex flex-col gap-4 border-t pt-10 sm:flex-row sm:items-center"
            >
                <button
                    type="submit"
                    :disabled="processing"
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    {{ processing ? 'Saving…' : 'Save changes' }}
                </button>
                <progress
                    v-if="progress"
                    :value="progress.percentage"
                    max="100"
                    class="accent-ink w-full sm:w-40"
                >
                    {{ progress.percentage }}%
                </progress>
            </div>
        </Form>
    </section>
</template>
