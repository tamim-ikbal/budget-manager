<?php

namespace App\Http\Controllers;

use App\Actions\Category\DestroyCategoryAction;
use App\Actions\Category\StoreCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\DTOs\Category\StoreCategoryData;
use App\DTOs\Category\UpdateCategoryData;
use App\Http\Middleware\ResolveCurrentWorkspace;
use App\Http\Requests\Category\DestroyCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $this->resolveCurrentWorkspace($request);

        $categories = Category::query()
            ->whereBelongsTo($workspace)
            ->orderBy('name')
            ->get();

        return Inertia::render('categories/Index', [
            'categories' => CategoryResource::collection($categories)->resolve(),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(StoreCategoryRequest $request, StoreCategoryAction $storeCategory): RedirectResponse
    {
        $workspace = $this->resolveCurrentWorkspace($request);
        $validated = $request->validated();

        $storeCategory($workspace, new StoreCategoryData(
            name: $validated['name'],
            color: $validated['color'] ?? null,
        ));

        return back()->with('status', 'Category created successfully.');
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategoryAction $updateCategory,
    ): RedirectResponse {
        $workspace = $this->resolveCurrentWorkspace($request);
        $this->ensureCategoryBelongsToWorkspace($category, $workspace);

        $validated = $request->validated();

        $updateCategory($category, new UpdateCategoryData(
            name: $validated['name'],
            color: $validated['color'] ?? null,
        ));

        return back()->with('status', 'Category updated successfully.');
    }

    public function destroy(
        DestroyCategoryRequest $request,
        Category $category,
        DestroyCategoryAction $destroyCategory,
    ): RedirectResponse {
        $workspace = $this->resolveCurrentWorkspace($request);
        $this->ensureCategoryBelongsToWorkspace($category, $workspace);

        $destroyCategory($category);

        return back()->with('status', 'Category deleted successfully.');
    }

    protected function resolveCurrentWorkspace(Request $request): Workspace
    {
        $workspace = $request->attributes->get(ResolveCurrentWorkspace::CURRENT_WORKSPACE_ATTRIBUTE);

        if (! $workspace instanceof Workspace) {
            abort(403);
        }

        return $workspace;
    }

    protected function ensureCategoryBelongsToWorkspace(Category $category, Workspace $workspace): void
    {
        if ($category->workspace_id !== $workspace->id) {
            abort(404);
        }
    }
}
