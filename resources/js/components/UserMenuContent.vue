<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Check, LogOut, Settings } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { setStoredCurrentWorkspaceUid } from '@/composables/useCurrentWorkspace';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { Auth, User, WorkspaceSummary } from '@/types';

type Props = {
    user: User;
};

const page = usePage();
const auth = computed(() => page.props.auth as Auth);
const workspaces = computed(() => auth.value.workspaces ?? []);
const currentWorkspace = computed(() => auth.value.currentWorkspace);

const handleLogout = () => {
    router.flushAll();
};

const switchWorkspace = (workspaceUid: string): void => {
    if (currentWorkspace.value?.uid === workspaceUid) {
        return;
    }

    setStoredCurrentWorkspaceUid(workspaceUid);

    router.reload();
};

const isCurrentWorkspace = (workspace: WorkspaceSummary): boolean =>
    currentWorkspace.value?.uid === workspace.uid;

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup v-if="workspaces.length > 0">
        <DropdownMenuLabel class="text-xs font-medium text-muted-foreground">
            Workspaces
        </DropdownMenuLabel>
        <DropdownMenuItem
            v-for="workspace in workspaces"
            :key="workspace.uid"
            class="cursor-pointer justify-between gap-3"
            @click="switchWorkspace(workspace.uid)"
        >
            <div class="flex min-w-0 flex-col">
                <span class="truncate">{{ workspace.name }}</span>
                <span class="text-xs text-muted-foreground capitalize">
                    {{ workspace.role }}
                </span>
            </div>
            <Check v-if="isCurrentWorkspace(workspace)" class="h-4 w-4" />
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator v-if="workspaces.length > 0" />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
