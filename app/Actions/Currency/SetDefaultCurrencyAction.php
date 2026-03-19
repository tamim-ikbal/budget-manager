<?php

namespace App\Actions\Currency;

use App\DTOs\Currency\SetDefaultCurrencyData;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetDefaultCurrencyAction
{
    public function __invoke(SetDefaultCurrencyData $data): void
    {
        if (! $data->currency->is_active) {
            throw ValidationException::withMessages([
                'currency' => 'Only an active currency can be set as default.',
            ]);
        }

        DB::transaction(function () use ($data): void {
            Currency::query()->update(['is_default' => false]);

            $data->currency->update(['is_default' => true]);
        });
    }
}
