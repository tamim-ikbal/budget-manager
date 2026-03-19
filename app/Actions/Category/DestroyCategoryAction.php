<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

class DestroyCategoryAction
{
    public function __invoke(Category $category): void
    {
        if ($category->expenses()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'This category is already used by one or more expenses.',
            ]);
        }

        $category->delete();
    }
}
