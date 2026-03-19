<?php

namespace App\Actions\Category;

use App\DTOs\Category\UpdateCategoryData;
use App\Models\Category;

class UpdateCategoryAction
{
    public function __invoke(Category $category, UpdateCategoryData $data): Category
    {
        $category->update([
            'name' => $data->name,
            'color' => $data->color,
        ]);

        return $category->refresh();
    }
}
