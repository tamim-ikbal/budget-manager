<script setup lang="ts">
import AppContent from '@/components/Admin/Layout/AppContent.vue';
import AppHeader from '@/components/Admin/Layout/AppHeader.vue';
import AppShell from '@/components/Admin/Layout/AppShell.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="header">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent variant="header">
            <slot />
        </AppContent>
    </AppShell>
</template>
