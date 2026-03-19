<?php

namespace App\Actions\Currency;

use App\DTOs\Currency\StoreCurrencyData;
use App\Models\Currency;
use Illuminate\Validation\ValidationException;

class StoreCurrencyAction
{
    public function __invoke(StoreCurrencyData $data): Currency
    {
        $defaultCurrencyExists = Currency::query()
            ->where('is_default', true)
            ->exists();

        if (! $defaultCurrencyExists && ! $data->isActive) {
            throw ValidationException::withMessages([
                'is_active' => 'At least one active default currency is required.',
            ]);
        }

        return Currency::query()->create([
            'code' => $data->code,
            'name' => $data->name,
            'symbol' => $data->symbol,
            'decimal_places' => $data->decimalPlaces,
            'is_active' => $data->isActive,
            'is_default' => ! $defaultCurrencyExists,
        ]);
    }
}
