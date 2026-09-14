<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/CollectiveController';
import CollectiveForm from '@/components/collectives/CollectiveForm.vue';
import { primaryButtonClass } from '@/lib/marketing';
</script>

<template>
    <Head title="Start a collective" />

    <section class="mx-auto w-full max-w-2xl px-6 py-20 md:px-10 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Collectives
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            Start a collective
        </h1>
        <p class="text-ink-soft mt-6 text-lg leading-relaxed">
            Collectives are independent crews bound by a shared approach or
            project. You will be the founder and decide who joins. Our team may
            mark trusted collectives as verified.
        </p>

        <Form
            v-bind="store.form()"
            class="mt-14 flex flex-col gap-14"
            v-slot="{ errors, processing, progress }"
        >
            <CollectiveForm :errors="errors" />

            <div
                class="border-hairline flex flex-col gap-4 border-t pt-10 sm:flex-row sm:items-center"
            >
                <button
                    type="submit"
                    :disabled="processing"
                    :class="[primaryButtonClass, 'sm:w-auto']"
                >
                    {{ processing ? 'Creating…' : 'Create collective' }}
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
