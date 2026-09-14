<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PlaceholderPhoto from '@/components/marketing/PlaceholderPhoto.vue';
import SiteLogo from '@/components/marketing/SiteLogo.vue';
import { register } from '@/routes';

defineProps<{
    mission: string;
}>();
</script>

<template>
    <section
        class="mx-auto w-full max-w-6xl px-6 pt-16 pb-24 md:px-10 md:pt-24 md:pb-32"
    >
        <figure>
            <PlaceholderPhoto
                ratio="aspect-[4/5] md:aspect-[16/9]"
                src="/hero.webp"
                alt="Black-and-white photograph of a sunlit, cracked crosswalk at Superior Street in Chicago, with two people walking past a corner restaurant."
                framed
                priority
            />
        </figure>

        <div class="mt-16 flex flex-col gap-8 md:mt-20 md:max-w-3xl">
            <h1>
                <SiteLogo class="w-full max-w-sm md:max-w-xl" />
                <span class="sr-only">StreetWatchers</span>
            </h1>
            <p class="text-ink-soft text-lg leading-relaxed md:text-xl">
                {{ mission }}
            </p>
            <div>
                <Link
                    :href="register()"
                    class="font-display border-ink bg-ink text-paper hover:bg-paper hover:text-ink inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                >
                    Join the Community
                </Link>
            </div>
        </div>
    </section>
</template>
