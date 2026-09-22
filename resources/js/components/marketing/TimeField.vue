<script setup lang="ts">
import { computed, ref } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

/**
 * A labeled time picker in the public site's style: shadcn's Select with a choice every 15 minutes. The
 * chosen time is posted under `name` as HH:mm.
 */
const { id, label, name, value, error } = defineProps<{
    id: string;
    label: string;
    name: string;
    value?: string;
    error?: string;
}>();

const STEP_MINUTES = 15;

const selected = ref(value);

const timeLabel = new Intl.DateTimeFormat(undefined, {
    hour: 'numeric',
    minute: '2-digit',
});

/** Every 15 minutes through the day, plus the saved time if it falls between steps. */
const options = computed(() => {
    const times = Array.from({ length: (24 * 60) / STEP_MINUTES }, (_, i) => {
        const minutes = i * STEP_MINUTES;

        return `${String(Math.floor(minutes / 60)).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`;
    });

    if (value && !times.includes(value)) {
        times.push(value);
        times.sort();
    }

    return times.map((time) => {
        const [hours, minutes] = time.split(':').map(Number);

        return {
            value: time,
            label: timeLabel.format(new Date(2000, 0, 1, hours, minutes)),
        };
    });
});
</script>

<template>
    <div>
        <label
            :for="id"
            class="text-ink-soft text-xs tracking-[0.14em] uppercase"
        >
            {{ label }}
        </label>
        <Select v-model="selected" :name="name" required>
            <SelectTrigger
                :id="id"
                class="border-hairline focus-visible:border-ink text-ink data-[placeholder]:text-ink-soft/60 mt-3 h-auto w-full cursor-pointer rounded-none border-0 border-b bg-transparent px-0 pt-0 pb-2 text-base shadow-none focus-visible:ring-0"
            >
                <SelectValue placeholder="Choose a time" />
            </SelectTrigger>
            <SelectContent
                class="border-ink bg-paper text-ink max-h-72 rounded-none border-2 shadow-none"
            >
                <SelectItem
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                    class="focus:bg-ink focus:text-paper cursor-pointer rounded-none"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <p v-if="error" class="text-ink mt-2 text-sm">{{ error }}</p>
    </div>
</template>
