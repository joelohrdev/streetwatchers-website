<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { CircleCheck, ImagePlus, LocateFixed, X } from '@lucide/vue';
import { onBeforeUnmount, ref, useTemplateRef } from 'vue';
import { store } from '@/actions/App/Http/Controllers/ChapterController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes';
import { create } from '@/routes/chapters';

defineProps<{
    submittedChapter: string | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Start a group', href: create() }],
    },
});

const DESCRIPTION_LIMIT = 5000;

const description = ref('');
const latitude = ref<string | number>('');
const longitude = ref<string | number>('');

const locating = ref(false);
const locationError = ref<string | null>(null);

/** Fill the coordinates from the browser, rounded to roughly neighbourhood precision. */
function useCurrentLocation(): void {
    if (!('geolocation' in navigator)) {
        locationError.value =
            'Your browser cannot share its location. Enter the coordinates instead.';

        return;
    }

    locating.value = true;
    locationError.value = null;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            latitude.value = position.coords.latitude.toFixed(4);
            longitude.value = position.coords.longitude.toFixed(4);
            locating.value = false;
        },
        () => {
            locationError.value =
                'We could not get your location. Check the browser permission or enter the coordinates instead.';
            locating.value = false;
        },
        { timeout: 10000 },
    );
}

const coverInput = useTemplateRef<HTMLInputElement>('coverInput');
const coverPreview = ref<string | null>(null);

function previewCover(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];

    clearPreview();
    coverPreview.value = file ? URL.createObjectURL(file) : null;
}

function removeCover(): void {
    clearPreview();

    if (coverInput.value) {
        coverInput.value.value = '';
    }
}

function clearPreview(): void {
    if (coverPreview.value) {
        URL.revokeObjectURL(coverPreview.value);
    }

    coverPreview.value = null;
}

onBeforeUnmount(clearPreview);
</script>

<template>
    <Head title="Start a group" />

    <div class="mx-auto w-full max-w-3xl px-4 py-6">
        <Card v-if="submittedChapter">
            <CardHeader class="items-center text-center">
                <CircleCheck class="mb-2 size-10 text-emerald-600" />
                <CardTitle class="text-xl">
                    {{ submittedChapter }} has been submitted
                </CardTitle>
                <CardDescription class="max-w-md text-balance">
                    Thanks for starting a group. Here is what happens next.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <ol class="mx-auto max-w-md space-y-3 text-sm">
                    <li class="flex gap-3">
                        <span
                            class="bg-muted flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                            >1</span
                        >
                        <span
                            >You are the group's first admin, so you will be
                            able to run its events and content once it's
                            live.</span
                        >
                    </li>
                    <li class="flex gap-3">
                        <span
                            class="bg-muted flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                            >2</span
                        >
                        <span
                            >Every group needs at least two admins before it can
                            be approved. We will get in touch to add a
                            co-organiser.</span
                        >
                    </li>
                    <li class="flex gap-3">
                        <span
                            class="bg-muted flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                            >3</span
                        >
                        <span
                            >Our team reviews the group and it appears in the
                            directory once it is approved.</span
                        >
                    </li>
                </ol>
                <div class="flex justify-center">
                    <Button as-child variant="outline">
                        <Link :href="dashboard()">Back to dashboard</Link>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <template v-else>
            <Heading
                title="Start a group"
                description="Groups are a few local photographers who walk, shoot and edit together. Tell us about yours and we will review it."
            />

            <Form
                v-bind="store.form()"
                class="space-y-6"
                v-slot="{ errors, processing, progress }"
            >
                <Card>
                    <CardHeader>
                        <CardTitle>About the group</CardTitle>
                        <CardDescription>
                            How the group appears in the directory.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="name">Group name</Label>
                            <Input
                                id="name"
                                name="name"
                                required
                                maxlength="255"
                                placeholder="Glasgow Streetwatchers"
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
                                placeholder="Who the group is for, where you like to shoot and how often you meet."
                            />
                            <div class="flex items-start justify-between gap-4">
                                <InputError :message="errors.description" />
                                <p
                                    class="text-muted-foreground ml-auto text-xs tabular-nums"
                                >
                                    {{ description.length }} /
                                    {{ DESCRIPTION_LIMIT }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Location</CardTitle>
                        <CardDescription>
                            Where the group is based. The coordinates place it
                            on the group map.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="city">City</Label>
                                <Input
                                    id="city"
                                    name="city"
                                    required
                                    autocomplete="address-level2"
                                    placeholder="Glasgow"
                                />
                                <InputError :message="errors.city" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="country">Country</Label>
                                <Input
                                    id="country"
                                    name="country"
                                    required
                                    autocomplete="country-name"
                                    placeholder="United Kingdom"
                                />
                                <InputError :message="errors.country" />
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="latitude">Latitude</Label>
                                <Input
                                    id="latitude"
                                    v-model="latitude"
                                    name="latitude"
                                    type="number"
                                    step="any"
                                    min="-90"
                                    max="90"
                                    required
                                    placeholder="55.8642"
                                />
                                <InputError :message="errors.latitude" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="longitude">Longitude</Label>
                                <Input
                                    id="longitude"
                                    v-model="longitude"
                                    name="longitude"
                                    type="number"
                                    step="any"
                                    min="-180"
                                    max="180"
                                    required
                                    placeholder="-4.2518"
                                />
                                <InputError :message="errors.longitude" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :disabled="locating"
                                @click="useCurrentLocation"
                            >
                                <Spinner v-if="locating" />
                                <LocateFixed v-else />
                                Use my current location
                            </Button>
                            <p
                                v-if="locationError"
                                class="text-sm text-red-600 dark:text-red-400"
                            >
                                {{ locationError }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Cover image</CardTitle>
                        <CardDescription>
                            Optional. A wide photo from your city works best.
                            JPG, PNG or WebP, up to 5 MB.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="coverPreview"
                            class="relative overflow-hidden rounded-lg border"
                        >
                            <img
                                :src="coverPreview"
                                alt="Cover image preview"
                                class="aspect-[3/1] w-full object-cover"
                            />
                            <Button
                                type="button"
                                variant="secondary"
                                size="icon-sm"
                                class="absolute top-2 right-2"
                                aria-label="Remove cover image"
                                @click="removeCover"
                            >
                                <X />
                            </Button>
                        </div>

                        <label
                            v-show="!coverPreview"
                            for="cover_image"
                            class="hover:bg-muted/50 flex aspect-[3/1] cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border border-dashed text-sm transition-colors"
                        >
                            <ImagePlus class="text-muted-foreground size-6" />
                            <span class="font-medium"
                                >Choose a cover image</span
                            >
                        </label>
                        <input
                            id="cover_image"
                            ref="coverInput"
                            type="file"
                            name="cover_image"
                            accept="image/jpeg,image/png,image/webp"
                            class="sr-only"
                            @change="previewCover"
                        />
                        <InputError :message="errors.cover_image" />
                    </CardContent>
                </Card>

                <div
                    class="flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-end"
                >
                    <progress
                        v-if="progress"
                        :value="progress.percentage"
                        max="100"
                        class="w-full sm:w-40"
                    >
                        {{ progress.percentage }}%
                    </progress>
                    <Button type="submit" :disabled="processing">
                        <Spinner v-if="processing" />
                        Submit for approval
                    </Button>
                </div>
            </Form>
        </template>
    </div>
</template>
