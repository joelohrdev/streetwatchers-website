<script setup lang="ts">
import { ImagePlus, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, useTemplateRef } from 'vue';
import FormField from '@/components/marketing/FormField.vue';

export type CollectiveFormDefaults = {
    name: string;
    description: string;
    based_in: string | null;
    website_url: string | null;
    instagram_url: string | null;
    logo_url: string | null;
    is_open_for_applications: boolean;
};

/**
 * The fields shared by the create and edit collective pages. It renders inside the page's
 * Inertia Form, so inputs submit by name and errors come from that form.
 */
const { collective = null, errors } = defineProps<{
    collective?: CollectiveFormDefaults | null;
    errors: Partial<Record<string, string>>;
}>();

const DESCRIPTION_LIMIT = 5000;

const description = ref(collective?.description ?? '');
const isOpen = ref(collective?.is_open_for_applications ?? false);

const logoInput = useTemplateRef<HTMLInputElement>('logoInput');
const logoPreview = ref<string | null>(null);
const removeExistingLogo = ref(false);

const shownLogo = computed(
    () =>
        logoPreview.value ??
        (removeExistingLogo.value ? null : (collective?.logo_url ?? null)),
);

function previewLogo(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];

    clearPreview();
    logoPreview.value = file ? URL.createObjectURL(file) : null;

    if (file) {
        removeExistingLogo.value = false;
    }
}

function removeLogo(): void {
    if (logoPreview.value) {
        clearPreview();

        if (logoInput.value) {
            logoInput.value.value = '';
        }

        return;
    }

    removeExistingLogo.value = true;
}

function clearPreview(): void {
    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoPreview.value = null;
}

onBeforeUnmount(clearPreview);

const sectionLabelClass =
    'font-display text-xs font-semibold tracking-[0.18em] uppercase';
const labelClass = 'text-ink-soft text-xs tracking-[0.14em] uppercase';
</script>

<template>
    <div class="flex flex-col gap-14">
        <div class="border-hairline flex flex-col gap-10 border-t pt-10">
            <div>
                <h2 :class="sectionLabelClass">About the collective</h2>
                <p class="text-ink-soft mt-3 leading-relaxed">
                    How the collective appears in the directory.
                </p>
            </div>

            <FormField
                id="name"
                label="Name"
                name="name"
                required
                maxlength="255"
                :value="collective?.name ?? ''"
                placeholder="Night Shift"
                :error="errors.name"
            />

            <div>
                <label for="description" :class="labelClass">Description</label>
                <div
                    class="border-hairline focus-within:border-ink mt-3 border-b pb-2 transition-colors"
                >
                    <textarea
                        id="description"
                        v-model="description"
                        name="description"
                        rows="5"
                        required
                        :maxlength="DESCRIPTION_LIMIT"
                        placeholder="What brings the collective together: the approach you share or the project you are working on."
                        class="text-ink placeholder:text-ink-soft/60 w-full resize-y bg-transparent text-base focus:outline-none"
                    />
                </div>
                <div class="mt-2 flex items-start justify-between gap-4">
                    <p v-if="errors.description" class="text-ink text-sm">
                        {{ errors.description }}
                    </p>
                    <p class="text-ink-soft ml-auto text-xs tabular-nums">
                        {{ description.length }} / {{ DESCRIPTION_LIMIT }}
                    </p>
                </div>
            </div>

            <FormField
                id="based_in"
                label="Based in (optional)"
                name="based_in"
                maxlength="255"
                :value="collective?.based_in ?? ''"
                placeholder="Chicago, United States"
                :error="errors.based_in"
            />
        </div>

        <div class="border-hairline flex flex-col gap-10 border-t pt-10">
            <div>
                <h2 :class="sectionLabelClass">Links</h2>
                <p class="text-ink-soft mt-3 leading-relaxed">
                    Optional. Where people can see more of the collective.
                </p>
            </div>

            <FormField
                id="website_url"
                label="Website"
                type="url"
                name="website_url"
                maxlength="255"
                :value="collective?.website_url ?? ''"
                placeholder="https://yourcollective.com"
                :error="errors.website_url"
            />

            <FormField
                id="instagram_url"
                label="Instagram"
                type="url"
                name="instagram_url"
                maxlength="255"
                :value="collective?.instagram_url ?? ''"
                placeholder="https://instagram.com/yourcollective"
                :error="errors.instagram_url"
            />
        </div>

        <div class="border-hairline flex flex-col gap-6 border-t pt-10">
            <div>
                <h2 :class="sectionLabelClass">Logo</h2>
                <p class="text-ink-soft mt-3 leading-relaxed">
                    Optional. A square image works best. JPG, PNG or WebP, up to
                    2 MB.
                </p>
            </div>

            <input
                v-if="removeExistingLogo && !logoPreview"
                type="hidden"
                name="remove_logo"
                value="1"
            />

            <div
                v-if="shownLogo"
                class="border-hairline relative size-40 border"
            >
                <img
                    :src="shownLogo"
                    alt="Logo preview"
                    class="size-full object-cover"
                />
                <button
                    type="button"
                    class="bg-paper text-ink border-ink absolute top-2 right-2 flex size-8 items-center justify-center border"
                    aria-label="Remove logo"
                    @click="removeLogo"
                >
                    <X class="size-4" />
                </button>
            </div>

            <label
                v-show="!shownLogo"
                for="logo"
                class="border-ink/25 hover:border-ink text-ink flex size-40 cursor-pointer flex-col items-center justify-center gap-3 border border-dashed text-center transition-colors"
            >
                <ImagePlus class="size-6" />
                <span
                    class="font-display text-xs font-semibold tracking-[0.14em] uppercase"
                >
                    Choose a logo
                </span>
            </label>
            <input
                id="logo"
                ref="logoInput"
                type="file"
                name="logo"
                accept="image/jpeg,image/png,image/webp"
                class="sr-only"
                @change="previewLogo"
            />
            <p
                v-if="errors.logo ?? errors.remove_logo"
                class="text-ink text-sm"
            >
                {{ errors.logo ?? errors.remove_logo }}
            </p>
        </div>

        <div class="border-hairline flex flex-col gap-6 border-t pt-10">
            <div>
                <h2 :class="sectionLabelClass">Applications</h2>
                <p class="text-ink-soft mt-3 leading-relaxed">
                    Decide whether people can ask to join. You can change this
                    at any time.
                </p>
            </div>

            <!-- The checkbox only submits when ticked, so this always sends true or false. -->
            <input
                type="hidden"
                name="is_open_for_applications"
                :value="isOpen ? '1' : '0'"
            />
            <label
                for="is_open_for_applications"
                class="flex cursor-pointer items-start gap-3"
            >
                <input
                    id="is_open_for_applications"
                    v-model="isOpen"
                    type="checkbox"
                    class="accent-ink mt-1 size-4 shrink-0"
                />
                <span>
                    <span class="text-ink block">Open to applications</span>
                    <span
                        class="text-ink-soft mt-1 block text-sm leading-relaxed"
                    >
                        When on, members can apply to join and you accept or
                        decline each application.
                    </span>
                </span>
            </label>
            <p v-if="errors.is_open_for_applications" class="text-ink text-sm">
                {{ errors.is_open_for_applications }}
            </p>
        </div>
    </div>
</template>
