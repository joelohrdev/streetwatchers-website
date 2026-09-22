<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { update } from '@/actions/App/Http/Controllers/ChapterController';
import type { GroupDetails } from '@/components/marketing/GroupForm.vue';
import GroupForm from '@/components/marketing/GroupForm.vue';
import { show } from '@/routes/chapters';

defineProps<{
    chapter: GroupDetails & { slug: string };
    countries: { value: string; label: string }[];
}>();
</script>

<template>
    <Head :title="`Edit ${chapter.name}`" />

    <section class="mx-auto w-full max-w-2xl px-6 py-20 md:px-10 md:py-28">
        <Link
            :href="show(chapter.slug)"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            {{ chapter.name }}
        </Link>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            Edit group
        </h1>
        <p class="text-ink-soft mt-6 text-lg leading-relaxed">
            Changes go live straight away. The group's web address stays the
            same, so links and QR codes you've shared keep working.
        </p>

        <GroupForm
            :action="update.form(chapter.slug)"
            :group="chapter"
            :countries="countries"
            submit-label="Save changes"
        />
    </section>
</template>
