<script setup lang="ts">
import AppContent from '@/components/Admin/Layout/AppContent.vue';
import AppShell from '@/components/Admin/Layout/AppShell.vue';
import AppSidebar from '@/components/Admin/Layout/AppSidebar.vue';
import AppSidebarHeader from '@/components/Admin/Layout/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
