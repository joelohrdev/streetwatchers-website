<script setup lang="ts">
import { Check, ChevronDown } from '@lucide/vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxPortal,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
} from 'reka-ui';
import { computed, ref } from 'vue';

type Option = { value: string; label: string };

/**
 * A labeled, searchable select in the public site's style. The chosen value is posted under `name`,
 * and typing filters the options ignoring case and accents, so "cote" finds "Côte d’Ivoire".
 */
const {
    id,
    label,
    name,
    options,
    value,
    error,
    placeholder,
    required = false,
} = defineProps<{
    id: string;
    label: string;
    name: string;
    options: Option[];
    /** The option selected to begin with, such as a saved value when editing. */
    value?: string;
    error?: string;
    placeholder?: string;
    required?: boolean;
}>();

const selected = ref<string | undefined>(value);
const searchTerm = ref('');

const normalize = (text: string): string =>
    text
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase();

const filteredOptions = computed(() => {
    const term = normalize(searchTerm.value.trim());

    return term === ''
        ? options
        : options.filter((option) => normalize(option.label).includes(term));
});

const labelFor = (value: string | undefined): string =>
    options.find((option) => option.value === value)?.label ?? '';
</script>

<template>
    <div>
        <label
            :for="id"
            class="text-ink-soft text-xs tracking-[0.14em] uppercase"
        >
            {{ label }}
        </label>
        <ComboboxRoot
            v-model="selected"
            :name="name"
            :required="required"
            ignore-filter
            open-on-click
        >
            <ComboboxAnchor
                class="border-hairline focus-within:border-ink mt-3 flex items-center gap-3 border-b pb-2 transition-colors"
            >
                <ComboboxInput
                    :id="id"
                    v-model="searchTerm"
                    :display-value="labelFor"
                    :placeholder="placeholder"
                    autocomplete="off"
                    class="text-ink placeholder:text-ink-soft/60 w-full bg-transparent text-base focus:outline-none"
                />
                <ComboboxTrigger
                    class="text-ink-soft hover:text-ink transition-colors"
                    :aria-label="`Show ${label.toLowerCase()} options`"
                >
                    <ChevronDown class="size-4" />
                </ComboboxTrigger>
            </ComboboxAnchor>
            <ComboboxPortal>
                <ComboboxContent
                    position="popper"
                    :side-offset="8"
                    class="border-ink bg-paper text-ink z-50 w-(--reka-combobox-trigger-width) border-2"
                >
                    <ComboboxViewport class="max-h-72">
                        <ComboboxEmpty class="text-ink-soft px-4 py-3 text-sm">
                            Nothing matches “{{ searchTerm }}”.
                        </ComboboxEmpty>
                        <ComboboxItem
                            v-for="option in filteredOptions"
                            :key="option.value"
                            :value="option.value"
                            :text-value="option.label"
                            class="data-highlighted:bg-ink data-highlighted:text-paper flex cursor-pointer items-center justify-between gap-3 px-4 py-2.5 text-sm outline-none"
                        >
                            {{ option.label }}
                            <ComboboxItemIndicator>
                                <Check class="size-4" />
                            </ComboboxItemIndicator>
                        </ComboboxItem>
                    </ComboboxViewport>
                </ComboboxContent>
            </ComboboxPortal>
        </ComboboxRoot>
        <p v-if="error" class="text-ink mt-2 text-sm">{{ error }}</p>
    </div>
</template>
