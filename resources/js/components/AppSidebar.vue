<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    FolderGit2,
    LayoutGrid,
    Settings,
    Tags,
} from 'lucide-vue-next';
import { computed } from 'vue';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
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
import { dashboard } from '@/routes';
import { edit as editWorkspaceSettings } from '@/routes/workspace-settings';
import type { Auth, NavGroup, NavItem } from '@/types';

const page = usePage();

const mainNavGroups = computed<NavGroup[]>(() => {
    const auth = page.props.auth as Auth;

    const platformItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    const workspaceItems: NavItem[] = [
        {
            title: 'Category',
            href: CategoryController.index(),
            icon: Tags,
        },
        {
            title: 'Settings',
            href: editWorkspaceSettings(),
            icon: Settings,
        },
    ];

    const groups: NavGroup[] = [
        {
            title: 'Platform',
            items: platformItems,
        },
    ];

    if (auth.workspaces.length > 0) {
        groups.push({
            title: 'Workspace',
            items: workspaceItems,
        });
    }

    return groups;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
