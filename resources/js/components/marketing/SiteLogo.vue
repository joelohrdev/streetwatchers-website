<script setup lang="ts">
/**
 * The StreetWatchers logo. Both files are trimmed crops of `public/logo.png`,
 * so re-export them from the source artwork if the logo ever changes.
 *
 * `lockup` is the mark plus the wordmark; `mark` is the viewfinder monogram on
 * its own, for tight spots where the full lockup would be unreadable.
 */
const { variant = 'lockup' } = defineProps<{
    variant?: 'lockup' | 'mark';
}>();
</script>

<template>
    <img
        :src="variant === 'mark' ? '/logo-mark.png' : '/logo-lockup.png'"
        alt="StreetWatchers"
        class="block max-w-full"
    />
</template>
