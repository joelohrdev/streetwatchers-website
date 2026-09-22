<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Megaphone } from '@lucide/vue';
import { ref } from 'vue';
import { update } from '@/actions/App/Http/Controllers/Admin/SettingController';
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
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { edit } from '@/routes/admin/settings';

const props = defineProps<{
    settings: {
        new_photos_require_review: boolean;
        announcement_banner: string | null;
        group_radius_miles: number;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Settings', href: edit() }],
    },
});

const requireReview = ref(props.settings.new_photos_require_review);
const announcement = ref(props.settings.announcement_banner ?? '');
</script>

<template>
    <Head title="Settings" />

    <Heading
        title="Platform settings"
        description="Site-wide defaults that apply to every member."
    />

    <Form
        v-bind="update.form()"
        :options="{ preserveScroll: true }"
        class="max-w-2xl space-y-6"
        v-slot="{ errors, processing }"
    >
        <Card>
            <CardHeader>
                <CardTitle>Photo review</CardTitle>
                <CardDescription>
                    Choose whether new uploads wait for moderation.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-2">
                <!-- The Checkbox doesn't submit a value itself, so this always sends true or false. -->
                <input
                    type="hidden"
                    name="new_photos_require_review"
                    :value="requireReview ? '1' : '0'"
                />
                <div class="flex items-start gap-3">
                    <Checkbox
                        id="new-photos-require-review"
                        :model-value="requireReview"
                        @update:model-value="requireReview = $event === true"
                    />
                    <div class="grid gap-1">
                        <Label for="new-photos-require-review">
                            New photos require review before publishing
                        </Label>
                        <p class="text-muted-foreground text-sm">
                            When off, new photos are published as soon as they
                            are uploaded.
                        </p>
                    </div>
                </div>
                <InputError :message="errors.new_photos_require_review" />
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Announcement banner</CardTitle>
                <CardDescription>
                    Shown at the top of every page. Leave empty to hide the
                    banner.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-2">
                    <Label for="announcement-banner">Message</Label>
                    <Textarea
                        id="announcement-banner"
                        v-model="announcement"
                        name="announcement_banner"
                        rows="3"
                        maxlength="500"
                        placeholder="e.g. Photo walk this Saturday in every chapter city."
                    />
                    <p class="text-muted-foreground text-xs">
                        {{ announcement.length }}/500
                    </p>
                    <InputError :message="errors.announcement_banner" />
                </div>

                <div class="grid gap-2">
                    <p class="text-sm font-medium">Preview</p>
                    <div
                        v-if="announcement.trim()"
                        class="flex items-center justify-center gap-2 rounded-md bg-neutral-900 px-4 py-2 text-center text-sm text-white dark:bg-neutral-100 dark:text-neutral-900"
                    >
                        <Megaphone class="size-4 shrink-0" />
                        <p>{{ announcement.trim() }}</p>
                    </div>
                    <p
                        v-else
                        class="text-muted-foreground rounded-md border border-dashed px-4 py-2 text-center text-sm"
                    >
                        No banner will be shown.
                    </p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Group spacing</CardTitle>
                <CardDescription>
                    A new group can't be proposed within this distance of an
                    active group or one waiting for approval.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-2">
                <Label for="group-radius-miles">Minimum distance</Label>
                <div class="flex items-center gap-2">
                    <Input
                        id="group-radius-miles"
                        type="number"
                        name="group_radius_miles"
                        min="1"
                        max="250"
                        step="1"
                        required
                        :default-value="settings.group_radius_miles"
                        class="w-24"
                    />
                    <span class="text-muted-foreground text-sm">miles</span>
                </div>
                <InputError :message="errors.group_radius_miles" />
            </CardContent>
        </Card>

        <Button type="submit" :disabled="processing">Save settings</Button>
    </Form>
</template>
