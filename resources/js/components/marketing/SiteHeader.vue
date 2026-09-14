<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import SiteLogo from '@/components/marketing/SiteLogo.vue';
import { dashboard, home, login, register } from '@/routes';
import { index as chapterDirectory } from '@/routes/chapters';

/**
 * Explore points at the matching homepage section until the feed page exists;
 * swap its href for a Wayfinder route at that point.
 */
const navLinks = [
    { label: 'Explore', href: '#featured' },
    { label: 'Groups', href: chapterDirectory.url() },
];

const isMenuOpen = ref(false);

// Guests see Log in and Join; signed-in visitors, e.g. on the verify email page, get Dashboard instead.
const page = usePage();
const isSignedIn = computed(() => Boolean(page.props.auth.user));
</script>

<template>
    <header class="border-hairline border-b">
        <div
            class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6 md:px-10"
        >
            <Link :href="home()" aria-label="StreetWatchers home">
                <SiteLogo class="h-6 md:h-7" />
            </Link>

            <nav class="hidden items-center gap-10 sm:flex">
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="font-display text-ink-soft hover:text-ink text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                >
                    {{ link.label }}
                </a>
                <template v-if="isSignedIn">
                    <Link
                        :href="dashboard()"
                        class="font-display border-ink text-ink border-b-2 pb-0.5 text-xs font-semibold tracking-[0.14em] uppercase"
                    >
                        Dashboard
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="login()"
                        class="font-display text-ink-soft hover:text-ink text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        class="font-display border-ink text-ink border-b-2 pb-0.5 text-xs font-semibold tracking-[0.14em] uppercase"
                    >
                        Join
                    </Link>
                </template>
            </nav>

            <button
                type="button"
                class="text-ink -mr-2 p-2 sm:hidden"
                :aria-expanded="isMenuOpen"
                aria-controls="site-menu"
                :aria-label="isMenuOpen ? 'Close menu' : 'Open menu'"
                @click="isMenuOpen = !isMenuOpen"
            >
                <X v-if="isMenuOpen" class="size-5" />
                <Menu v-else class="size-5" />
            </button>
        </div>

        <nav
            v-show="isMenuOpen"
            id="site-menu"
            class="border-hairline border-t sm:hidden"
        >
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-6 py-8">
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="font-display text-ink-soft text-sm font-semibold tracking-[0.14em] uppercase"
                    @click="isMenuOpen = false"
                >
                    {{ link.label }}
                </a>
                <Link
                    v-if="isSignedIn"
                    :href="dashboard()"
                    class="font-display border-ink text-ink self-start border-b-2 pb-0.5 text-sm font-semibold tracking-[0.14em] uppercase"
                    @click="isMenuOpen = false"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="font-display text-ink-soft text-sm font-semibold tracking-[0.14em] uppercase"
                        @click="isMenuOpen = false"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        class="font-display border-ink text-ink self-start border-b-2 pb-0.5 text-sm font-semibold tracking-[0.14em] uppercase"
                        @click="isMenuOpen = false"
                    >
                        Join
                    </Link>
                </template>
            </div>
        </nav>
    </header>
</template>
