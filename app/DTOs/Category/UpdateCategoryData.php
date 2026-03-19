<?php

namespace App\DTOs\Category;

class UpdateCategoryData
{
    public function __construct(
        public string $name,
        public ?string $color,
    ) {}
}
