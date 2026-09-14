<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { update as decide } from '@/actions/App/Http/Controllers/CollectiveApplicationDecisionController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDate } from '@/lib/utils';
import { index as applicationsIndex } from '@/routes/collective-applications';
import { create, edit, show } from '@/routes/collectives';

type PendingApplication = {
    id: number;
    applicant: string;
    message: string;
    created_at: string | null;
};

type DecidedApplication = {
    id: number;
    applicant: string;
    status: 'accepted' | 'declined';
    decided_at: string | null;
};

defineProps<{
    collectives: {
        id: number;
        name: string;
        slug: string;
        is_open_for_applications: boolean;
        pending: PendingApplication[];
        recent: DecidedApplication[];
    }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Applications', href: applicationsIndex() }],
    },
});

const decisions = [
    { value: 'accepted', label: 'Accept', variant: 'default' },
    { value: 'declined', label: 'Decline', variant: 'outline' },
] as const;
</script>

<template>
    <Head title="Collective applications" />

    <div class="mx-auto w-full max-w-3xl space-y-6 px-4 py-6">
        <Heading
            title="Collective applications"
            description="People asking to join the collectives you founded. Accepting adds them as a member."
        />

        <Card v-if="collectives.length === 0">
            <CardHeader>
                <CardTitle>You haven't founded a collective yet</CardTitle>
                <CardDescription>
                    Start one and people can apply to join it.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child>
                    <Link :href="create()">Start a collective</Link>
                </Button>
            </CardContent>
        </Card>

        <Card v-for="collective in collectives" :key="collective.id">
            <CardHeader>
                <CardTitle>
                    <Link :href="show(collective.slug)" class="hover:underline">
                        {{ collective.name }}
                    </Link>
                </CardTitle>
                <CardDescription v-if="!collective.is_open_for_applications">
                    Not open to applications right now.
                    <Link
                        :href="edit(collective.slug)"
                        class="text-foreground underline underline-offset-4"
                    >
                        Change this
                    </Link>
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <ul v-if="collective.pending.length" class="space-y-4">
                    <li
                        v-for="application in collective.pending"
                        :key="application.id"
                        class="rounded-md border p-4"
                    >
                        <div
                            class="flex flex-wrap items-baseline justify-between gap-2"
                        >
                            <p class="font-medium">
                                {{ application.applicant }}
                            </p>
                            <p class="text-muted-foreground text-xs">
                                Received
                                {{ formatDate(application.created_at) }}
                            </p>
                        </div>
                        <p
                            class="bg-muted mt-3 rounded-md px-3 py-2 text-sm whitespace-pre-line"
                        >
                            {{ application.message }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <Form
                                v-for="decision in decisions"
                                :key="decision.value"
                                v-bind="decide.form(application.id)"
                                :options="{ preserveScroll: true }"
                                v-slot="{ errors, processing }"
                            >
                                <input
                                    type="hidden"
                                    name="decision"
                                    :value="decision.value"
                                />
                                <Button
                                    type="submit"
                                    size="sm"
                                    :variant="decision.variant"
                                    :disabled="processing"
                                >
                                    {{ decision.label }}
                                </Button>
                                <InputError
                                    class="mt-2"
                                    :message="errors.decision"
                                />
                            </Form>
                        </div>
                    </li>
                </ul>
                <p v-else class="text-muted-foreground text-sm">
                    No applications waiting.
                </p>

                <div v-if="collective.recent.length">
                    <h3 class="text-muted-foreground mb-2 text-sm font-medium">
                        Decided in the last 30 days
                    </h3>
                    <ul class="divide-y rounded-md border">
                        <li
                            v-for="application in collective.recent"
                            :key="application.id"
                            class="flex items-center justify-between gap-4 px-3 py-2 text-sm"
                        >
                            <span>{{ application.applicant }}</span>
                            <span class="flex items-center gap-3">
                                <Badge
                                    :variant="
                                        application.status === 'accepted'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        application.status === 'accepted'
                                            ? 'Accepted'
                                            : 'Declined'
                                    }}
                                </Badge>
                                <span class="text-muted-foreground text-xs">
                                    {{ formatDate(application.decided_at) }}
                                </span>
                            </span>
                        </li>
                    </ul>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
