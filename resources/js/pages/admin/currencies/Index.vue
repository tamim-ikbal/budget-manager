<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import CurrencyController from '@/actions/App/Http/Controllers/Admin/CurrencyController';
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
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type CurrencyItem = {
    uid: string;
    code: string;
    name: string;
    symbol: string;
    decimal_places: number;
    is_active: boolean | number;
    is_default: boolean | number;
};

type Props = {
    currencies: CurrencyItem[];
    status?: string | null;
};

const props = defineProps<Props>();

const currencies = computed(() =>
    props.currencies.map((currency) => ({
        ...currency,
        is_active: Boolean(currency.is_active),
        is_default: Boolean(currency.is_default),
    })),
);

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const selectedCurrency = ref<(typeof currencies.value)[number] | null>(null);

const createForm = useForm({
    code: '',
    name: '',
    symbol: '',
    decimal_places: 2,
    is_active: true,
});

const editForm = useForm({
    code: '',
    name: '',
    symbol: '',
    decimal_places: 2,
    is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Currencies',
        href: CurrencyController.index(),
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

function firstError(errors: Record<string, string>): string {
    return Object.values(errors)[0] ?? 'Something went wrong.';
}

function submitCreate(): void {
    createForm.code = createForm.code.toUpperCase();

    createForm.post(CurrencyController.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            createDialogOpen.value = false;
            createForm.reset();
            createForm.decimal_places = 2;
            createForm.is_active = true;
        },
        onError: (errors) => {
            toast.error(firstError(errors));
        },
    });
}

function openEditDialog(currency: (typeof currencies.value)[number]): void {
    selectedCurrency.value = currency;
    editForm.code = currency.code;
    editForm.name = currency.name;
    editForm.symbol = currency.symbol;
    editForm.decimal_places = currency.decimal_places;
    editForm.is_active = currency.is_active;
    editForm.clearErrors();
    editDialogOpen.value = true;
}

function submitEdit(): void {
    if (!selectedCurrency.value) {
        return;
    }

    editForm.code = editForm.code.toUpperCase();

    editForm.put(CurrencyController.update.url(selectedCurrency.value.uid), {
        preserveScroll: true,
        onSuccess: () => {
            editDialogOpen.value = false;
            selectedCurrency.value = null;
        },
        onError: (errors) => {
            toast.error(firstError(errors));
        },
    });
}

function openDeleteDialog(currency: (typeof currencies.value)[number]): void {
    selectedCurrency.value = currency;
    deleteDialogOpen.value = true;
}

function submitDelete(): void {
    if (!selectedCurrency.value) {
        return;
    }

    router.delete(CurrencyController.destroy.url(selectedCurrency.value.uid), {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialogOpen.value = false;
            selectedCurrency.value = null;
        },
        onError: (errors) => {
            toast.error(firstError(errors));
        },
    });
}

function toggleActive(
    currency: (typeof currencies.value)[number],
    checked: boolean,
): void {
    router.patch(
        CurrencyController.update.url(currency.uid),
        {
            code: currency.code,
            name: currency.name,
            symbol: currency.symbol,
            decimal_places: currency.decimal_places,
            is_active: checked,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                toast.error(firstError(errors));
            },
        },
    );
}

function toggleDefault(
    currency: (typeof currencies.value)[number],
    checked: boolean,
): void {
    if (!checked || currency.is_default) {
        return;
    }

    router.patch(
        CurrencyController.setDefault.url(currency.uid),
        {},
        {
            preserveScroll: true,
            onError: (errors) => {
                toast.error(firstError(errors));
            },
        },
    );
}

function onCreateActiveChange(checked: boolean): void {
    createForm.is_active = checked;
}

function onEditActiveChange(checked: boolean): void {
    editForm.is_active = checked;
}

function onRowActiveChange(
    currency: (typeof currencies.value)[number],
    checked: boolean,
): void {
    toggleActive(currency, checked);
}

function onRowDefaultChange(
    currency: (typeof currencies.value)[number],
    checked: boolean,
): void {
    toggleDefault(currency, checked);
}
</script>

<template>
    <Head title="Admin Currencies" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <section class="rounded-xl border p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h1 class="text-lg font-semibold">Currencies</h1>
                    <Button @click="createDialogOpen = true">Create</Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Code</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Symbol</TableHead>
                            <TableHead>Decimals</TableHead>
                            <TableHead>Active</TableHead>
                            <TableHead>Default</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="currencies.length === 0" :colspan="7">
                            No currencies found.
                        </TableEmpty>

                        <TableRow
                            v-for="currency in currencies"
                            :key="currency.uid"
                        >
                            <TableCell>{{ currency.code }}</TableCell>
                            <TableCell>{{ currency.name }}</TableCell>
                            <TableCell>{{ currency.symbol }}</TableCell>
                            <TableCell>{{ currency.decimal_places }}</TableCell>
                            <TableCell>
                                <Switch
                                    :checked="currency.is_active"
                                    :disabled="currency.is_default"
                                    @update:checked="
                                        onRowActiveChange(
                                            currency,
                                            Boolean($event),
                                        )
                                    "
                                />
                            </TableCell>
                            <TableCell>
                                <Switch
                                    :checked="currency.is_default"
                                    :disabled="
                                        currency.is_default ||
                                        !currency.is_active
                                    "
                                    @update:checked="
                                        onRowDefaultChange(
                                            currency,
                                            Boolean($event),
                                        )
                                    "
                                />
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openEditDialog(currency)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        :disabled="currency.is_default"
                                        @click="openDeleteDialog(currency)"
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
                    <DialogTitle>Create Currency</DialogTitle>
                    <DialogDescription>
                        Add a new currency to the system.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-2">
                    <div class="grid gap-2">
                        <Label for="create-code">Code</Label>
                        <Input
                            id="create-code"
                            v-model="createForm.code"
                            placeholder="USD"
                        />
                        <InputError :message="createForm.errors.code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-name">Name</Label>
                        <Input
                            id="create-name"
                            v-model="createForm.name"
                            placeholder="US Dollar"
                        />
                        <InputError :message="createForm.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-symbol">Symbol</Label>
                        <Input
                            id="create-symbol"
                            v-model="createForm.symbol"
                            placeholder="$"
                        />
                        <InputError :message="createForm.errors.symbol" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-decimal">Decimal places</Label>
                        <Input
                            id="create-decimal"
                            v-model.number="createForm.decimal_places"
                            type="number"
                            min="0"
                            max="4"
                        />
                        <InputError
                            :message="createForm.errors.decimal_places"
                        />
                    </div>

                    <div
                        class="flex items-center justify-between rounded-md border p-3"
                    >
                        <Label for="create-active">Active</Label>
                        <Switch
                            id="create-active"
                            :checked="createForm.is_active"
                            @update:checked="
                                onCreateActiveChange(Boolean($event))
                            "
                        />
                    </div>
                    <InputError :message="createForm.errors.is_active" />
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="createDialogOpen = false">
                        Cancel
                    </Button>
                    <Button
                        :disabled="createForm.processing"
                        @click="submitCreate"
                    >
                        Create
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="editDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Currency</DialogTitle>
                    <DialogDescription>
                        Update the selected currency.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-2">
                    <div class="grid gap-2">
                        <Label for="edit-code">Code</Label>
                        <Input id="edit-code" v-model="editForm.code" />
                        <InputError :message="editForm.errors.code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-name">Name</Label>
                        <Input id="edit-name" v-model="editForm.name" />
                        <InputError :message="editForm.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-symbol">Symbol</Label>
                        <Input id="edit-symbol" v-model="editForm.symbol" />
                        <InputError :message="editForm.errors.symbol" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-decimal">Decimal places</Label>
                        <Input
                            id="edit-decimal"
                            v-model.number="editForm.decimal_places"
                            type="number"
                            min="0"
                            max="4"
                        />
                        <InputError :message="editForm.errors.decimal_places" />
                    </div>

                    <div
                        class="flex items-center justify-between rounded-md border p-3"
                    >
                        <Label for="edit-active">Active</Label>
                        <Switch
                            id="edit-active"
                            :checked="editForm.is_active"
                            @update:checked="
                                onEditActiveChange(Boolean($event))
                            "
                        />
                    </div>
                    <InputError :message="editForm.errors.is_active" />
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="editDialogOpen = false">
                        Cancel
                    </Button>
                    <Button :disabled="editForm.processing" @click="submitEdit">
                        Save
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <AlertDialog v-model:open="deleteDialogOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Currency</AlertDialogTitle>
                    <AlertDialogDescription>
                        This action cannot be undone. The currency will be
                        permanently removed if it is not in use.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="submitDelete">
                        Confirm Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>
