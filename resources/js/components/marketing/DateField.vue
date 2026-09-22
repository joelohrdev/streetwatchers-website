<script setup lang="ts">
import { getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { CalendarDays } from '@lucide/vue';
import type { DateValue } from 'reka-ui';
import { computed, ref, shallowRef } from 'vue';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';

/**
 * A labeled date picker in the public site's style: shadcn's Calendar in a Popover. The chosen date is
 * posted under `name` as YYYY-MM-DD. Past dates can't be picked.
 */
const { id, label, name, value, error } = defineProps<{
    id: string;
    label: string;
    name: string;
    value?: string;
    error?: string;
}>();

const selected = shallowRef<DateValue | undefined>(
    value ? parseDate(value) : undefined,
);
const open = ref(false);

const earliest = today(getLocalTimeZone());

const longDate = new Intl.DateTimeFormat(undefined, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const displayValue = computed(() =>
    selected.value
        ? longDate.format(selected.value.toDate(getLocalTimeZone()))
        : null,
);

function choose(date: DateValue | undefined): void {
    selected.value = date;
    open.value = false;
}
</script>

<template>
    <div>
        <label
            :for="id"
            class="text-ink-soft text-xs tracking-[0.14em] uppercase"
        >
            {{ label }}
        </label>
        <input type="hidden" :name="name" :value="selected?.toString() ?? ''" />
        <Popover v-model:open="open">
            <PopoverTrigger
                :id="id"
                type="button"
                class="border-hairline focus-visible:border-ink mt-3 flex w-full cursor-pointer items-center justify-between gap-3 border-b pb-2 text-left text-base transition-colors focus:outline-none"
            >
                <span :class="displayValue ? 'text-ink' : 'text-ink-soft/60'">
                    {{ displayValue ?? 'Choose a date' }}
                </span>
                <CalendarDays class="text-ink-soft size-4 shrink-0" />
            </PopoverTrigger>
            <PopoverContent
                align="start"
                class="border-ink bg-paper text-ink w-auto rounded-none border-2 p-0 shadow-none"
            >
                <Calendar
                    :model-value="selected"
                    :min-value="earliest"
                    initial-focus
                    @update:model-value="choose"
                />
            </PopoverContent>
        </Popover>
        <p v-if="error" class="text-ink mt-2 text-sm">{{ error }}</p>
    </div>
</template>
