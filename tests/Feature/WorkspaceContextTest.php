<?php

use App\Enums\WorkspaceMemberRole;
use App\Models\Currency;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
});

function activeCurrencyId(): int
{
    return Currency::query()
        ->where('is_active', true)
        ->orderByDesc('is_default')
        ->orderBy('id')
        ->value('id');
}

function createOwnedWorkspace(User $user, string $name): Workspace
{
    $workspace = Workspace::query()->create([
        'user_id' => $user->id,
        'currency_id' => activeCurrencyId(),
        'name' => $name,
        'time_zone' => 'Asia/Dhaka',
    ]);

    WorkspaceMember::query()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'name' => $user->name,
        'role' => WorkspaceMemberRole::Owner,
        'joined_at' => now(),
    ]);

    return $workspace;
}

test('workspace context uses header selected workspace when user has access', function () {
    $user = User::factory()->create();

    $firstWorkspace = createOwnedWorkspace($user, 'Alpha Workspace');
    $secondWorkspace = createOwnedWorkspace($user, 'Beta Workspace');

    $response = $this->actingAs($user)
        ->withHeader('X-Workspace-Uid', $secondWorkspace->uid)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('auth.workspaces', 2)
            ->where('auth.currentWorkspace.uid', $secondWorkspace->uid)
            ->where('auth.currentWorkspace.name', $secondWorkspace->name)
            ->where('auth.workspaces.0.uid', $firstWorkspace->uid)
            ->where('auth.workspaces.1.uid', $secondWorkspace->uid),
        );
});

test('workspace context falls back to first workspace when header is missing', function () {
    $user = User::factory()->create();

    $firstWorkspace = createOwnedWorkspace($user, 'First Workspace');
    createOwnedWorkspace($user, 'Second Workspace');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.currentWorkspace.uid', $firstWorkspace->uid),
        );
});

test('workspace context falls back to first workspace when header workspace is inaccessible', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $accessibleWorkspace = createOwnedWorkspace($user, 'Accessible Workspace');
    $inaccessibleWorkspace = createOwnedWorkspace($otherUser, 'Inaccessible Workspace');

    $response = $this->actingAs($user)
        ->withHeader('X-Workspace-Uid', $inaccessibleWorkspace->uid)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.currentWorkspace.uid', $accessibleWorkspace->uid),
        );
});

test('workspace context returns forbidden when user has no workspace membership', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertForbidden();
});
