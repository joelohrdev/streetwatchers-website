<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Flag,
    Gauge,
    Globe,
    Inbox,
    LayoutGrid,
    MapPinned,
    Newspaper,
    PenLine,
    Settings,
    Tags,
    Users,
    UsersRound,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard, home } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as articlesIndex } from '@/routes/admin/articles';
import { index as chaptersIndex } from '@/routes/admin/chapters';
import { index as collectivesIndex } from '@/routes/admin/collectives';
import { index as contactMessagesIndex } from '@/routes/admin/contact-messages';
import { index as correspondentsIndex } from '@/routes/admin/correspondents';
import { index as reportsIndex } from '@/routes/admin/reports';
import { edit as settingsEdit } from '@/routes/admin/settings';
import { index as tagsIndex } from '@/routes/admin/tags';
import { index as usersIndex } from '@/routes/admin/users';
import type { NavItem } from '@/types';

type NavGroup = {
    label: string;
    items: NavItem[];
};

const page = usePage();
const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

/** The sidebar only appears in the admin panel, so its top links lead back to the rest of the site. */
const mainNavItems: NavItem[] = [
    { title: 'View site', href: home(), icon: Globe },
    { title: 'Your memberships', href: dashboard(), icon: LayoutGrid },
];

/** A section link that stays highlighted on its sub-pages, e.g. a single chapter. */
function section(
    title: string,
    href: NavItem['href'],
    icon: NavItem['icon'],
): NavItem {
    return { title, href, icon, isActive: isCurrentOrParentUrl(href) };
}

/** Super admins get every admin section in the main sidebar rather than a separate area. */
const adminNavGroups = computed<NavGroup[]>(() => {
    if (page.props.auth.user.role !== 'super_admin') {
        return [];
    }

    return [
        {
            label: 'Admin',
            items: [
                {
                    title: 'Overview',
                    href: adminDashboard(),
                    icon: Gauge,
                    isActive: isCurrentUrl(adminDashboard()),
                },
                {
                    ...section('Messages', contactMessagesIndex(), Inbox),
                    badge: page.props.unreadContactMessages,
                },
                section('Settings', settingsEdit(), Settings),
            ],
        },
        {
            label: 'Trust & safety',
            items: [
                section('Reports', reportsIndex(), Flag),
                section('Users', usersIndex(), Users),
            ],
        },
        {
            label: 'Community',
            items: [
                section('Chapters', chaptersIndex(), MapPinned),
                section('Collectives', collectivesIndex(), UsersRound),
            ],
        },
        {
            label: 'Editorial',
            items: [
                section('Correspondents', correspondentsIndex(), PenLine),
                section('Articles', articlesIndex(), Newspaper),
                section('Tags', tagsIndex(), Tags),
            ],
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="adminDashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain
                v-for="group in adminNavGroups"
                :key="group.label"
                :label="group.label"
                :items="group.items"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
