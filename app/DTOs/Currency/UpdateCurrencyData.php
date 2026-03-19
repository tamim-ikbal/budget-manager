<?php

namespace App\DTOs\Currency;

class UpdateCurrencyData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $symbol,
        public int $decimalPlaces,
        public bool $isActive,
    ) {}
}
