<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';
import WorkspaceSettingsController from '@/actions/App/Http/Controllers/Settings/WorkspaceSettingsController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/workspace-settings';
import type { BreadcrumbItem, WorkspaceSummary } from '@/types';

type Props = {
    workspace: WorkspaceSummary;
    status?: string | null;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Workspace settings',
        href: edit(),
    },
];

watch(
    () => props.status,
    (status) => {
        if (status) {
            toast.success(status);
        }
    },
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Workspace settings" />

        <h1 class="sr-only">Workspace settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Workspace"
                    description="Update your current workspace details"
                />

                <Form
                    v-bind="WorkspaceSettingsController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="workspace-name">Name</Label>
                        <Input
                            id="workspace-name"
                            name="name"
                            :default-value="workspace.name"
                            required
                            autocomplete="off"
                            placeholder="Workspace name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
