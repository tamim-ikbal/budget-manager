<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import CurrencyController from '@/actions/App/Http/Controllers/Admin/CurrencyController';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/Admin/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type CurrencyItem = {
    uid: string;
    code: string;
    name: string;
    symbol: string;
    decimal_places: number;
    is_active: boolean;
    is_default: boolean;
};

type Props = {
    currencies: CurrencyItem[];
    status?: string | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Currencies',
        href: CurrencyController.index(),
    },
];
</script>

<template>
    <Head title="Admin Currencies" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <Alert v-if="props.status">
                <AlertTitle>Success</AlertTitle>
                <AlertDescription>{{ props.status }}</AlertDescription>
            </Alert>

            <section class="rounded-xl border p-4">
                <h2 class="mb-4 text-lg font-semibold">Create currency</h2>

                <Form
                    v-bind="CurrencyController.store.form()"
                    v-slot="{ errors, processing }"
                    class="grid gap-4 md:grid-cols-5"
                >
                    <div class="grid gap-2">
                        <Label for="code">Code</Label>
                        <Input
                            id="code"
                            name="code"
                            placeholder="USD"
                            required
                        />
                        <InputError :message="errors.code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            placeholder="US Dollar"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="symbol">Symbol</Label>
                        <Input
                            id="symbol"
                            name="symbol"
                            placeholder="$"
                            required
                        />
                        <InputError :message="errors.symbol" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="decimal_places">Decimal places</Label>
                        <Input
                            id="decimal_places"
                            name="decimal_places"
                            type="number"
                            min="0"
                            max="4"
                            :default-value="2"
                            required
                        />
                        <InputError :message="errors.decimal_places" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="is_active">Status</Label>
                        <input type="hidden" name="is_active" value="0" />
                        <label
                            for="is_active"
                            class="mt-2 inline-flex items-center gap-2 text-sm"
                        >
                            <input
                                id="is_active"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked
                            />
                            Active
                        </label>
                        <InputError :message="errors.is_active" />
                    </div>

                    <div class="md:col-span-5">
                        <Button :disabled="processing" type="submit">
                            Create currency
                        </Button>
                    </div>
                </Form>
            </section>

            <section class="rounded-xl border p-4">
                <h2 class="mb-4 text-lg font-semibold">Currency list</h2>

                <div
                    v-for="currency in props.currencies"
                    :key="currency.uid"
                    class="mb-3 rounded-lg border p-3"
                >
                    <Form
                        v-bind="CurrencyController.update.form(currency.uid)"
                        v-slot="{ errors, processing }"
                        class="grid gap-3 md:grid-cols-6"
                    >
                        <div class="grid gap-1">
                            <Label :for="`code-${currency.uid}`">Code</Label>
                            <Input
                                :id="`code-${currency.uid}`"
                                name="code"
                                :default-value="currency.code"
                                required
                            />
                        </div>

                        <div class="grid gap-1">
                            <Label :for="`name-${currency.uid}`">Name</Label>
                            <Input
                                :id="`name-${currency.uid}`"
                                name="name"
                                :default-value="currency.name"
                                required
                            />
                        </div>

                        <div class="grid gap-1">
                            <Label :for="`symbol-${currency.uid}`"
                                >Symbol</Label
                            >
                            <Input
                                :id="`symbol-${currency.uid}`"
                                name="symbol"
                                :default-value="currency.symbol"
                                required
                            />
                        </div>

                        <div class="grid gap-1">
                            <Label :for="`decimal-${currency.uid}`"
                                >Decimals</Label
                            >
                            <Input
                                :id="`decimal-${currency.uid}`"
                                type="number"
                                min="0"
                                max="4"
                                name="decimal_places"
                                :default-value="currency.decimal_places"
                                required
                            />
                        </div>

                        <div class="grid gap-1">
                            <Label :for="`active-${currency.uid}`"
                                >Status</Label
                            >
                            <input type="hidden" name="is_active" value="0" />
                            <label
                                :for="`active-${currency.uid}`"
                                class="mt-2 inline-flex items-center gap-2 text-sm"
                            >
                                <input
                                    :id="`active-${currency.uid}`"
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    :checked="currency.is_active"
                                />
                                Active
                            </label>
                        </div>

                        <div class="flex items-end gap-2">
                            <Button :disabled="processing" type="submit">
                                Save
                            </Button>
                        </div>

                        <div class="md:col-span-6">
                            <InputError :message="errors.code" />
                            <InputError :message="errors.name" />
                            <InputError :message="errors.symbol" />
                            <InputError :message="errors.decimal_places" />
                            <InputError :message="errors.is_active" />
                        </div>
                    </Form>

                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            {{
                                currency.is_default
                                    ? 'Default currency'
                                    : 'Not default'
                            }}
                        </p>

                        <Form
                            v-if="!currency.is_default"
                            v-bind="
                                CurrencyController.setDefault.form(currency.uid)
                            "
                            v-slot="{ errors, processing }"
                            class="text-right"
                        >
                            <Button
                                type="submit"
                                variant="secondary"
                                :disabled="processing || !currency.is_active"
                            >
                                Make default
                            </Button>
                            <InputError
                                :message="errors.currency"
                                class="mt-2"
                            />
                        </Form>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
