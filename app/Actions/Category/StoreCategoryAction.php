<?php

namespace App\Actions\Category;

use App\DTOs\Category\StoreCategoryData;
use App\Models\Category;
use App\Models\Workspace;

class StoreCategoryAction
{
    public function __invoke(Workspace $workspace, StoreCategoryData $data): Category
    {
        return Category::query()->create([
            'workspace_id' => $workspace->id,
            'name' => $data->name,
            'color' => $data->color,
        ]);
    }
}
