<?php

namespace App\Actions\Currency;

use App\DTOs\Currency\UpdateCurrencyData;
use App\Models\Currency;
use Illuminate\Validation\ValidationException;

class UpdateCurrencyAction
{
    public function __invoke(Currency $currency, UpdateCurrencyData $data): Currency
    {
        if (! $data->isActive) {
            if ($currency->is_default) {
                throw ValidationException::withMessages([
                    'is_active' => 'Default currency cannot be set to inactive.',
                ]);
            }

            $activeCurrencyCount = Currency::query()
                ->where('is_active', true)
                ->count();

            if ($currency->is_active && $activeCurrencyCount <= 1) {
                throw ValidationException::withMessages([
                    'is_active' => 'At least one active currency is required.',
                ]);
            }
        }

        $currency->update([
            'code' => $data->code,
            'name' => $data->name,
            'symbol' => $data->symbol,
            'decimal_places' => $data->decimalPlaces,
            'is_active' => $data->isActive,
        ]);

        return $currency->refresh();
    }
}
