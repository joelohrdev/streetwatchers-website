<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Megaphone } from '@lucide/vue';
import { computed } from 'vue';

const page = usePage();
const announcement = computed(() => page.props.announcement);
</script>

<template>
    <div
        v-if="announcement"
        role="status"
        class="flex items-center justify-center gap-2 bg-neutral-900 px-4 py-2 text-center text-sm text-white dark:bg-neutral-100 dark:text-neutral-900"
    >
        <Megaphone class="size-4 shrink-0" />
        <p>{{ announcement }}</p>
    </div>
</template>
