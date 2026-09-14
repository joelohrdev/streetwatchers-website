<script setup lang="ts">
defineProps<{
    statement: string;
}>();
</script>

<template>
    <section class="border-hairline border-t">
        <p
            class="font-display mx-auto w-full max-w-3xl px-6 py-28 text-center text-2xl leading-relaxed text-balance md:px-10 md:py-40 md:text-3xl md:leading-relaxed"
        >
            {{ statement }}
        </p>
    </section>
</template>
