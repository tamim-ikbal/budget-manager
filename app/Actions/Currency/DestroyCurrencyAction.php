<?php

namespace App\Actions\Currency;

use App\Models\Currency;
use Illuminate\Validation\ValidationException;

class DestroyCurrencyAction
{
    public function __invoke(Currency $currency): void
    {
        if ($currency->is_default) {
            throw ValidationException::withMessages([
                'currency' => 'Default currency cannot be deleted.',
            ]);
        }

        if ($currency->workspaces()->exists()) {
            throw ValidationException::withMessages([
                'currency' => 'This currency is already used by one or more workspaces.',
            ]);
        }

        $activeCurrencyCount = Currency::query()
            ->where('is_active', true)
            ->count();

        if ($currency->is_active && $activeCurrencyCount <= 1) {
            throw ValidationException::withMessages([
                'currency' => 'At least one active currency is required.',
            ]);
        }

        $currency->delete();
    }
}
