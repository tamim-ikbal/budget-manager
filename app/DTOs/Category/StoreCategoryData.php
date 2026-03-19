<?php

namespace App\DTOs\Category;

class StoreCategoryData
{
    public function __construct(
        public string $name,
        public ?string $color,
    ) {}
}
