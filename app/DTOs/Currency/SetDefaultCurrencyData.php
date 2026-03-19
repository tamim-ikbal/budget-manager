<?php

namespace App\DTOs\Currency;

use App\Models\Currency;

class SetDefaultCurrencyData
{
    public function __construct(
        public Currency $currency,
    ) {}
}
