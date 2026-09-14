<script setup lang="ts">
import { ImagePlus, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, useTemplateRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

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
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>About the collective</CardTitle>
            <CardDescription>
                How the collective appears in the directory.
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    required
                    maxlength="255"
                    :default-value="collective?.name ?? ''"
                    placeholder="Night Shift Collective"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    v-model="description"
                    name="description"
                    rows="5"
                    required
                    :maxlength="DESCRIPTION_LIMIT"
                    placeholder="What brings the collective together: the approach you share or the project you are working on."
                />
                <div class="flex items-start justify-between gap-4">
                    <InputError :message="errors.description" />
                    <p
                        class="text-muted-foreground ml-auto text-xs tabular-nums"
                    >
                        {{ description.length }} / {{ DESCRIPTION_LIMIT }}
                    </p>
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="based_in">
                    Based in
                    <span class="text-muted-foreground font-normal">
                        (optional)
                    </span>
                </Label>
                <Input
                    id="based_in"
                    name="based_in"
                    maxlength="255"
                    :default-value="collective?.based_in ?? ''"
                    placeholder="Chicago, United States"
                />
                <InputError :message="errors.based_in" />
            </div>
        </CardContent>
    </Card>

    <Card>
        <CardHeader>
            <CardTitle>Links</CardTitle>
            <CardDescription>
                Optional. Where people can see more of the collective.
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <div class="grid gap-2">
                <Label for="website_url">Website</Label>
                <Input
                    id="website_url"
                    name="website_url"
                    type="url"
                    maxlength="255"
                    :default-value="collective?.website_url ?? ''"
                    placeholder="https://yourcollective.com"
                />
                <InputError :message="errors.website_url" />
            </div>

            <div class="grid gap-2">
                <Label for="instagram_url">Instagram</Label>
                <Input
                    id="instagram_url"
                    name="instagram_url"
                    type="url"
                    maxlength="255"
                    :default-value="collective?.instagram_url ?? ''"
                    placeholder="https://instagram.com/yourcollective"
                />
                <InputError :message="errors.instagram_url" />
            </div>
        </CardContent>
    </Card>

    <Card>
        <CardHeader>
            <CardTitle>Logo</CardTitle>
            <CardDescription>
                Optional. A square image works best. JPG, PNG or WebP, up to 2
                MB.
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-3">
            <input
                v-if="removeExistingLogo && !logoPreview"
                type="hidden"
                name="remove_logo"
                value="1"
            />

            <div v-if="shownLogo" class="relative size-40">
                <img
                    :src="shownLogo"
                    alt="Logo preview"
                    class="size-40 rounded-lg border object-cover"
                />
                <Button
                    type="button"
                    variant="secondary"
                    size="icon-sm"
                    class="absolute top-2 right-2"
                    aria-label="Remove logo"
                    @click="removeLogo"
                >
                    <X />
                </Button>
            </div>

            <label
                v-show="!shownLogo"
                for="logo"
                class="hover:bg-muted/50 flex size-40 cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border border-dashed text-center text-sm transition-colors"
            >
                <ImagePlus class="text-muted-foreground size-6" />
                <span class="font-medium">Choose a logo</span>
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
            <InputError :message="errors.logo ?? errors.remove_logo" />
        </CardContent>
    </Card>

    <Card>
        <CardHeader>
            <CardTitle>Applications</CardTitle>
            <CardDescription>
                Decide whether people can ask to join. You can change this at
                any time.
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-2">
            <!-- The Checkbox doesn't submit a value itself, so this always sends true or false. -->
            <input
                type="hidden"
                name="is_open_for_applications"
                :value="isOpen ? '1' : '0'"
            />
            <div class="flex items-start gap-3">
                <Checkbox
                    id="is_open_for_applications"
                    :model-value="isOpen"
                    @update:model-value="isOpen = $event === true"
                />
                <div class="grid gap-1">
                    <Label for="is_open_for_applications">
                        Open to applications
                    </Label>
                    <p class="text-muted-foreground text-sm">
                        When on, members can apply to join and you accept or
                        decline each application.
                    </p>
                </div>
            </div>
            <InputError :message="errors.is_open_for_applications" />
        </CardContent>
    </Card>
</template>
