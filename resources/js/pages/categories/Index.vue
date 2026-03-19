<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import InputError from '@/components/InputError.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type CategoryItem = {
    uid: string;
    name: string;
    color: string | null;
};

type Props = {
    categories: CategoryItem[];
    status?: string | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Category',
        href: CategoryController.index(),
    },
];

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const selectedCategory = ref<CategoryItem | null>(null);

const createForm = useForm({
    name: '',
    color: '',
});

const editForm = useForm({
    name: '',
    color: '',
});

watch(
    () => props.status,
    (status) => {
        if (status) {
            toast.success(status);
        }
    },
);

function firstError(errors: Record<string, string>): string {
    return Object.values(errors)[0] ?? 'Something went wrong.';
}

function submitCreate(): void {
    createForm.post(CategoryController.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            createDialogOpen.value = false;
            createForm.reset();
        },
        onError: (errors) => {
            toast.error(firstError(errors));
        },
    });
}

function openEditDialog(category: CategoryItem): void {
    selectedCategory.value = category;
    editForm.name = category.name;
    editForm.color = category.color ?? '';
    editForm.clearErrors();
    editDialogOpen.value = true;
}

function submitEdit(): void {
    if (!selectedCategory.value) {
        return;
    }

    editForm.put(CategoryController.update.url(selectedCategory.value.uid), {
        preserveScroll: true,
        onSuccess: () => {
            editDialogOpen.value = false;
            selectedCategory.value = null;
        },
        onError: (errors) => {
            toast.error(firstError(errors));
        },
    });
}

function openDeleteDialog(category: CategoryItem): void {
    selectedCategory.value = category;
    deleteDialogOpen.value = true;
}

function submitDelete(): void {
    if (!selectedCategory.value) {
        return;
    }

    router.delete(CategoryController.destroy.url(selectedCategory.value.uid), {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialogOpen.value = false;
            selectedCategory.value = null;
        },
        onError: (errors) => {
            toast.error(firstError(errors));
        },
    });
}
</script>

<template>
    <Head title="Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <section class="rounded-xl border p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h1 class="text-lg font-semibold">Category</h1>
                    <Button @click="createDialogOpen = true">Create</Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Color</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="props.categories.length === 0"
                            :colspan="3"
                        >
                            No categories found.
                        </TableEmpty>

                        <TableRow
                            v-for="category in props.categories"
                            :key="category.uid"
                        >
                            <TableCell>{{ category.name }}</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="size-3 rounded-full border"
                                        :style="{
                                            backgroundColor:
                                                category.color ?? 'transparent',
                                        }"
                                    />
                                    <span>{{ category.color ?? '-' }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openEditDialog(category)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="openDeleteDialog(category)"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </section>
        </div>

        <Dialog v-model:open="createDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create category</DialogTitle>
                    <DialogDescription>
                        Add a new category to the current workspace.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submitCreate">
                    <div class="space-y-2">
                        <Label for="create-category-name">Name</Label>
                        <Input
                            id="create-category-name"
                            v-model="createForm.name"
                            autocomplete="off"
                            placeholder="Category name"
                        />
                        <InputError :message="createForm.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="create-category-color">Color</Label>
                        <Input
                            id="create-category-color"
                            v-model="createForm.color"
                            autocomplete="off"
                            placeholder="#22c55e"
                        />
                        <InputError :message="createForm.errors.color" />
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="createForm.processing">
                            Create
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="editDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit category</DialogTitle>
                    <DialogDescription>
                        Update the selected category for this workspace.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submitEdit">
                    <div class="space-y-2">
                        <Label for="edit-category-name">Name</Label>
                        <Input
                            id="edit-category-name"
                            v-model="editForm.name"
                            autocomplete="off"
                            placeholder="Category name"
                        />
                        <InputError :message="editForm.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-category-color">Color</Label>
                        <Input
                            id="edit-category-color"
                            v-model="editForm.color"
                            autocomplete="off"
                            placeholder="#22c55e"
                        />
                        <InputError :message="editForm.errors.color" />
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="editForm.processing">
                            Save
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <AlertDialog v-model:open="deleteDialogOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete category</AlertDialogTitle>
                    <AlertDialogDescription>
                        This action cannot be undone.
                        {{ selectedCategory?.name }} will be removed.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="submitDelete">
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
