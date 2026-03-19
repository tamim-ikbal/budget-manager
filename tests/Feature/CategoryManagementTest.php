<?php

use App\Enums\WorkspaceMemberRole;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
});

function activeCurrencyIdForCategory(): int
{
    $activeCurrencyId = Currency::query()
        ->where('is_active', true)
        ->orderByDesc('is_default')
        ->orderBy('id')
        ->value('id');

    if (! is_int($activeCurrencyId)) {
        throw new RuntimeException('An active currency is required for category tests.');
    }

    return $activeCurrencyId;
}

function createWorkspaceForCategoryOwner(User $owner, string $name): Workspace
{
    $workspace = Workspace::query()->create([
        'user_id' => $owner->id,
        'currency_id' => activeCurrencyIdForCategory(),
        'name' => $name,
        'time_zone' => 'Asia/Dhaka',
    ]);

    WorkspaceMember::query()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $owner->id,
        'name' => $owner->name,
        'role' => WorkspaceMemberRole::Owner,
        'joined_at' => now(),
    ]);

    return $workspace;
}

function addWorkspaceMember(Workspace $workspace, User $member): WorkspaceMember
{
    return WorkspaceMember::query()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $member->id,
        'name' => $member->name,
        'role' => WorkspaceMemberRole::Member,
        'joined_at' => now(),
    ]);
}

test('owner can view category page', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForCategoryOwner($owner, 'Category Workspace');

    $category = Category::query()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Groceries',
        'color' => '#22c55e',
    ]);

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->get(route('categories.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('categories/Index')
            ->has('categories', 1)
            ->where('categories.0.uid', $category->uid)
            ->where('categories.0.name', 'Groceries')
            ->where('status', null),
        );
});

test('member can view category page', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $workspace = createWorkspaceForCategoryOwner($owner, 'Shared Category Workspace');

    addWorkspaceMember($workspace, $member);

    $response = $this->actingAs($member)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->get(route('categories.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('categories/Index'),
        );
});

test('owner can create category', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForCategoryOwner($owner, 'Create Category Workspace');

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->from(route('categories.index'))
        ->post(route('categories.store'), [
            'name' => 'Utilities',
            'color' => '#0ea5e9',
        ]);

    $response
        ->assertRedirect(route('categories.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'workspace_id' => $workspace->id,
        'name' => 'Utilities',
        'color' => '#0ea5e9',
    ]);
});

test('owner can create duplicate category names in same workspace', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForCategoryOwner($owner, 'Duplicate Category Workspace');

    Category::query()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Food',
        'color' => '#f97316',
    ]);

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->from(route('categories.index'))
        ->post(route('categories.store'), [
            'name' => 'Food',
            'color' => '#84cc16',
        ]);

    $response
        ->assertRedirect(route('categories.index'))
        ->assertSessionHasNoErrors();

    expect(Category::query()
        ->where('workspace_id', $workspace->id)
        ->where('name', 'Food')
        ->count())->toBe(2);
});

test('owner can update category', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForCategoryOwner($owner, 'Update Category Workspace');

    $category = Category::query()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Transport',
        'color' => '#94a3b8',
    ]);

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->from(route('categories.index'))
        ->patch(route('categories.update', ['category' => $category->uid]), [
            'name' => 'Transportation',
            'color' => '#64748b',
        ]);

    $response
        ->assertRedirect(route('categories.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Transportation',
        'color' => '#64748b',
    ]);
});

test('owner can delete unused category', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForCategoryOwner($owner, 'Delete Category Workspace');

    $category = Category::query()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Subscriptions',
        'color' => null,
    ]);

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->from(route('categories.index'))
        ->delete(route('categories.destroy', ['category' => $category->uid]));

    $response
        ->assertRedirect(route('categories.index'))
        ->assertSessionHasNoErrors();

    expect($category->fresh())->toBeNull();
});

test('member cannot create update or delete categories', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $workspace = createWorkspaceForCategoryOwner($owner, 'Member Restrictions Workspace');

    addWorkspaceMember($workspace, $member);

    $category = Category::query()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Health',
        'color' => '#ef4444',
    ]);

    $createResponse = $this->actingAs($member)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->post(route('categories.store'), [
            'name' => 'Entertainment',
            'color' => '#8b5cf6',
        ]);

    $createResponse->assertForbidden();

    $updateResponse = $this->actingAs($member)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->patch(route('categories.update', ['category' => $category->uid]), [
            'name' => 'Medical',
            'color' => '#dc2626',
        ]);

    $updateResponse->assertForbidden();

    $deleteResponse = $this->actingAs($member)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->delete(route('categories.destroy', ['category' => $category->uid]));

    $deleteResponse->assertForbidden();
});

test('category listing is scoped to current workspace', function () {
    $owner = User::factory()->create();
    $firstWorkspace = createWorkspaceForCategoryOwner($owner, 'First Workspace');
    $secondWorkspace = createWorkspaceForCategoryOwner($owner, 'Second Workspace');

    Category::query()->create([
        'workspace_id' => $firstWorkspace->id,
        'name' => 'First Category',
        'color' => '#16a34a',
    ]);

    $secondWorkspaceCategory = Category::query()->create([
        'workspace_id' => $secondWorkspace->id,
        'name' => 'Second Category',
        'color' => '#f59e0b',
    ]);

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $secondWorkspace->uid)
        ->get(route('categories.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('categories/Index')
            ->has('categories', 1)
            ->where('categories.0.uid', $secondWorkspaceCategory->uid),
        );
});

test('cannot delete category that is already used by expense', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForCategoryOwner($owner, 'Expense Category Workspace');

    $category = Category::query()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Bills',
        'color' => '#3b82f6',
    ]);

    $budget = Budget::query()->create([
        'workspace_id' => $workspace->id,
        'title' => 'Monthly Budget',
        'budget' => 5000,
        'currency' => 'USD',
        'created_by' => $owner->id,
    ]);

    Expense::query()->create([
        'workspace_id' => $workspace->id,
        'budget_id' => $budget->id,
        'category_id' => $category->id,
        'title' => 'Electricity Bill',
        'amount' => 120,
        'currency' => 'USD',
        'expense_date' => now(),
        'note' => null,
        'created_by_user_id' => $owner->id,
    ]);

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->from(route('categories.index'))
        ->delete(route('categories.destroy', ['category' => $category->uid]));

    $response
        ->assertRedirect(route('categories.index'))
        ->assertSessionHasErrors('category');

    expect($category->fresh())->not->toBeNull();
});

test('cannot update category from another workspace context', function () {
    $owner = User::factory()->create();
    $firstWorkspace = createWorkspaceForCategoryOwner($owner, 'Workspace One');
    $secondWorkspace = createWorkspaceForCategoryOwner($owner, 'Workspace Two');

    $secondWorkspaceCategory = Category::query()->create([
        'workspace_id' => $secondWorkspace->id,
        'name' => 'Workspace Two Category',
        'color' => '#22d3ee',
    ]);

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $firstWorkspace->uid)
        ->patch(route('categories.update', ['category' => $secondWorkspaceCategory->uid]), [
            'name' => 'Updated Name',
            'color' => '#0ea5e9',
        ]);

    $response->assertNotFound();
});
