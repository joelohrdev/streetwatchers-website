<script setup lang="ts">
import { Eye, EyeOff } from '@lucide/vue';
import { ref } from 'vue';

/**
 * A labeled, underlined input in the public site's style. Extra attributes such as name,
 * autocomplete and required go straight to the input. Password fields get a show/hide toggle.
 */
defineOptions({ inheritAttrs: false });

const {
    id,
    label,
    error,
    type = 'text',
} = defineProps<{
    id: string;
    label: string;
    error?: string;
    type?: string;
}>();

const revealed = ref(false);
</script>

<template>
    <div>
        <div class="flex items-center justify-between gap-4">
            <label
                :for="id"
                class="text-ink-soft text-xs tracking-[0.14em] uppercase"
            >
                {{ label }}
            </label>
            <slot name="action" />
        </div>
        <div
            class="border-hairline focus-within:border-ink mt-3 flex items-center gap-3 border-b pb-2 transition-colors"
        >
            <input
                :id="id"
                v-bind="$attrs"
                :type="type === 'password' && revealed ? 'text' : type"
                class="text-ink placeholder:text-ink-soft/60 read-only:text-ink-soft w-full bg-transparent text-base read-only:cursor-default focus:outline-none"
            />
            <button
                v-if="type === 'password'"
                type="button"
                class="text-ink-soft hover:text-ink transition-colors"
                :aria-label="revealed ? 'Hide password' : 'Show password'"
                :aria-pressed="revealed"
                @click="revealed = !revealed"
            >
                <EyeOff v-if="revealed" class="size-4" />
                <Eye v-else class="size-4" />
            </button>
        </div>
        <p v-if="error" class="text-ink mt-2 text-sm">{{ error }}</p>
    </div>
</template>
