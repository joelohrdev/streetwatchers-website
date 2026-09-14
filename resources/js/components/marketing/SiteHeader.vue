<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, Menu, X } from '@lucide/vue';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
    watch,
} from 'vue';
import NavCorners from '@/components/marketing/NavCorners.vue';
import SiteLogo from '@/components/marketing/SiteLogo.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard, home, login, logout, register } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as chapterDirectory } from '@/routes/chapters';
import { index as collectiveDirectory } from '@/routes/collectives';
import { edit as editProfile } from '@/routes/profile';

/**
 * Explore points at the matching homepage section until the feed page exists;
 * swap its href for a Wayfinder route at that point.
 */
const navLinks = [
    { label: 'Explore', href: '#featured', activeOn: [] },
    {
        label: 'Groups',
        href: chapterDirectory.url(),
        activeOn: [chapterDirectory.url()],
    },
    {
        label: 'Collectives',
        href: collectiveDirectory.url(),
        activeOn: [collectiveDirectory.url()],
    },
];

const isMenuOpen = ref(false);

// Guests see Log in and Join. Signed-in visitors get a menu under their first name with their account,
// settings, the admin panel if they are a super admin, and Log out. It shows a count when applications
// wait for them as a founder.
const page = usePage();
const isSignedIn = computed(() => Boolean(page.props.auth.user));
const isSuperAdmin = computed(
    () => isSignedIn.value && page.props.auth.user.role === 'super_admin',
);
const firstName = computed(
    () => page.props.auth.user?.name.split(' ')[0] ?? '',
);
const pendingApplications = computed(
    () => page.props.pendingCollectiveApplications ?? 0,
);

const { currentUrl } = useCurrentUrl();

function isActive(paths: string[]): boolean {
    return paths.some(
        (path) =>
            currentUrl.value === path ||
            currentUrl.value.startsWith(`${path}/`),
    );
}

const isAccountActive = computed(() =>
    isActive([dashboard.url(), '/settings']),
);

/**
 * The desktop corners frame the active item and slide to whichever item is hovered or focused,
 * returning to the active item when the pointer or focus leaves the menu.
 */
const desktopNav = ref<HTMLElement | null>(null);
const corners = reactive({
    left: 0,
    top: 0,
    width: 0,
    height: 0,
    isVisible: false,
    isSliding: false,
});

function moveCornersTo(item: HTMLElement | null): void {
    if (!item || item.offsetWidth === 0) {
        corners.isVisible = false;

        return;
    }

    // Slide only when the corners are already showing; otherwise they appear in place.
    corners.isSliding = corners.isVisible;
    corners.left = item.offsetLeft;
    corners.top = item.offsetTop;
    corners.width = item.offsetWidth;
    corners.height = item.offsetHeight;
    corners.isVisible = true;
}

function navItemFrom(event: Event): HTMLElement | null {
    return (event.target as HTMLElement).closest<HTMLElement>(
        '[data-nav-item]',
    );
}

function onNavPointer(event: Event): void {
    const item = navItemFrom(event);

    if (item) {
        moveCornersTo(item);
    }
}

// While the account menu is open, its trigger keeps the corners.
function returnCornersToActive(): void {
    moveCornersTo(
        desktopNav.value?.querySelector<HTMLElement>(
            '[data-nav-item][data-state="open"]',
        ) ??
            desktopNav.value?.querySelector<HTMLElement>(
                '[data-nav-item][data-active]',
            ) ??
            null,
    );
}

function onAccountMenuToggle(isOpen: boolean): void {
    if (!isOpen) {
        nextTick(returnCornersToActive);
    }
}

function onNavFocusOut(event: FocusEvent): void {
    if (!desktopNav.value?.contains(event.relatedTarget as Node | null)) {
        returnCornersToActive();
    }
}

function snapCornersToActive(): void {
    corners.isVisible = false;
    returnCornersToActive();
}

let resizeObserver: ResizeObserver | null = null;

onMounted(() => {
    snapCornersToActive();
    document.fonts?.ready.then(snapCornersToActive);

    resizeObserver = new ResizeObserver(snapCornersToActive);

    if (desktopNav.value) {
        resizeObserver.observe(desktopNav.value);
    }
});

onBeforeUnmount(() => resizeObserver?.disconnect());

watch(currentUrl, () => nextTick(returnCornersToActive));

const desktopItemClass =
    'font-display hover:text-ink text-xs font-semibold tracking-[0.14em] uppercase transition-colors';
const menuItemClass =
    'font-display focus:bg-ink focus:text-paper cursor-pointer rounded-none px-4 py-3 text-xs font-semibold tracking-[0.14em] uppercase';
const mobileItemClass =
    'font-display relative self-start text-sm font-semibold tracking-[0.14em] uppercase';
</script>

<template>
    <header class="border-hairline border-b">
        <div
            class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6 md:px-10"
        >
            <Link :href="home()" aria-label="StreetWatchers home">
                <SiteLogo class="h-6 md:h-7" />
            </Link>

            <nav
                ref="desktopNav"
                class="relative hidden items-center gap-10 sm:flex"
                @mouseover="onNavPointer"
                @focusin="onNavPointer"
                @mouseleave="returnCornersToActive"
                @focusout="onNavFocusOut"
            >
                <span
                    aria-hidden="true"
                    :class="[
                        'pointer-events-none absolute motion-reduce:transition-none',
                        corners.isSliding
                            ? 'transition-[left,top,width,height,opacity] duration-300 ease-out'
                            : 'transition-opacity duration-200',
                        corners.isVisible ? 'opacity-100' : 'opacity-0',
                    ]"
                    :style="{
                        left: `${corners.left}px`,
                        top: `${corners.top}px`,
                        width: `${corners.width}px`,
                        height: `${corners.height}px`,
                    }"
                >
                    <NavCorners />
                </span>

                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    data-nav-item
                    :data-active="isActive(link.activeOn) || undefined"
                    :aria-current="isActive(link.activeOn) ? 'page' : undefined"
                    :class="[
                        desktopItemClass,
                        isActive(link.activeOn) ? 'text-ink' : 'text-ink-soft',
                    ]"
                >
                    {{ link.label }}
                </a>
                <DropdownMenu
                    v-if="isSignedIn"
                    :modal="false"
                    @update:open="onAccountMenuToggle"
                >
                    <DropdownMenuTrigger
                        data-nav-item
                        :data-active="isAccountActive || undefined"
                        :class="[
                            desktopItemClass,
                            'inline-flex cursor-pointer items-center gap-2 outline-none',
                            isAccountActive ? 'text-ink' : 'text-ink-soft',
                        ]"
                    >
                        <span class="max-w-40 truncate">{{ firstName }}</span>
                        <span
                            v-if="pendingApplications > 0"
                            class="bg-ink text-paper inline-flex h-4 min-w-4 items-center justify-center px-1 text-[0.625rem] tracking-normal"
                            :aria-label="`${pendingApplications} applications waiting`"
                        >
                            {{ pendingApplications }}
                        </span>
                        <ChevronDown class="size-3.5" />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        align="end"
                        :side-offset="16"
                        class="border-ink bg-paper text-ink min-w-60 rounded-none border-2 p-0 shadow-none"
                    >
                        <DropdownMenuLabel
                            class="border-hairline border-b px-4 py-3 font-normal"
                        >
                            <span class="block truncate text-sm font-medium">
                                {{ page.props.auth.user.name }}
                            </span>
                            <span class="text-ink-soft block truncate text-xs">
                                {{ page.props.auth.user.email }}
                            </span>
                        </DropdownMenuLabel>
                        <DropdownMenuItem as-child :class="menuItemClass">
                            <Link :href="dashboard()">
                                Your StreetWatchers
                                <span
                                    v-if="pendingApplications > 0"
                                    class="ml-auto tracking-normal"
                                >
                                    {{ pendingApplications }} waiting
                                </span>
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child :class="menuItemClass">
                            <Link :href="editProfile()">Account settings</Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            v-if="isSuperAdmin"
                            as-child
                            :class="menuItemClass"
                        >
                            <Link :href="adminDashboard()">Admin</Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            as-child
                            :class="[
                                menuItemClass,
                                'border-hairline w-full border-t',
                            ]"
                        >
                            <Link :href="logout()" as="button">Log out</Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <template v-else>
                    <Link
                        :href="login()"
                        data-nav-item
                        :data-active="isActive([login.url()]) || undefined"
                        :aria-current="
                            isActive([login.url()]) ? 'page' : undefined
                        "
                        :class="[
                            desktopItemClass,
                            isActive([login.url()])
                                ? 'text-ink'
                                : 'text-ink-soft',
                        ]"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        data-nav-item
                        :data-active="isActive([register.url()]) || undefined"
                        :aria-current="
                            isActive([register.url()]) ? 'page' : undefined
                        "
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
                    :aria-current="isActive(link.activeOn) ? 'page' : undefined"
                    :class="[
                        mobileItemClass,
                        isActive(link.activeOn) ? 'text-ink' : 'text-ink-soft',
                    ]"
                    @click="isMenuOpen = false"
                >
                    {{ link.label }}
                    <NavCorners v-if="isActive(link.activeOn)" />
                </a>
                <template v-if="isSignedIn">
                    <Link
                        :href="dashboard()"
                        :aria-current="isAccountActive ? 'page' : undefined"
                        class="font-display border-ink text-ink relative self-start border-b-2 pb-0.5 text-sm font-semibold tracking-[0.14em] uppercase"
                        @click="isMenuOpen = false"
                    >
                        Your StreetWatchers
                        <template v-if="pendingApplications > 0">
                            ({{ pendingApplications }})
                        </template>
                        <NavCorners v-if="isAccountActive" />
                    </Link>
                    <Link
                        v-if="isSuperAdmin"
                        :href="adminDashboard()"
                        :class="[mobileItemClass, 'text-ink-soft']"
                        @click="isMenuOpen = false"
                    >
                        Admin
                    </Link>
                    <Link
                        :href="logout()"
                        as="button"
                        :class="[mobileItemClass, 'text-ink-soft']"
                        @click="isMenuOpen = false"
                    >
                        Log out
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="login()"
                        :aria-current="
                            isActive([login.url()]) ? 'page' : undefined
                        "
                        :class="[
                            mobileItemClass,
                            isActive([login.url()])
                                ? 'text-ink'
                                : 'text-ink-soft',
                        ]"
                        @click="isMenuOpen = false"
                    >
                        Log in
                        <NavCorners v-if="isActive([login.url()])" />
                    </Link>
                    <Link
                        :href="register()"
                        :aria-current="
                            isActive([register.url()]) ? 'page' : undefined
                        "
                        class="font-display border-ink text-ink relative self-start border-b-2 pb-0.5 text-sm font-semibold tracking-[0.14em] uppercase"
                        @click="isMenuOpen = false"
                    >
                        Join
                        <NavCorners v-if="isActive([register.url()])" />
                    </Link>
                </template>
            </div>
        </nav>
    </header>
</template>
