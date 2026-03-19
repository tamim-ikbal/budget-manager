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

function activeCurrencyIdForWorkspaceSettings(): int
{
    return Currency::query()
        ->where('is_active', true)
        ->orderByDesc('is_default')
        ->orderBy('id')
        ->value('id');
}

function createWorkspaceForOwner(User $owner, string $name): Workspace
{
    $workspace = Workspace::query()->create([
        'user_id' => $owner->id,
        'currency_id' => activeCurrencyIdForWorkspaceSettings(),
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

test('workspace settings page is displayed for owner', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForOwner($owner, 'Owner Workspace');

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->get(route('workspace-settings.edit'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Workspace')
            ->where('workspace.uid', $workspace->uid)
            ->where('workspace.name', $workspace->name)
            ->where('workspace.role', WorkspaceMemberRole::Owner->value),
        );
});

test('workspace name can be updated by owner', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForOwner($owner, 'Old Workspace Name');

    $response = $this->actingAs($owner)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->patch(route('workspace-settings.update'), [
            'name' => 'Updated Workspace Name',
        ]);

    $response
        ->assertRedirect(route('workspace-settings.edit'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'name' => 'Updated Workspace Name',
    ]);
});

test('workspace name cannot be updated by member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $workspace = createWorkspaceForOwner($owner, 'Shared Workspace');

    WorkspaceMember::query()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $member->id,
        'name' => $member->name,
        'role' => WorkspaceMemberRole::Member,
        'joined_at' => now(),
    ]);

    $response = $this->actingAs($member)
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->patch(route('workspace-settings.update'), [
            'name' => 'Unauthorized Rename',
        ]);

    $response->assertForbidden();
});

test('workspace name is validated when updating', function () {
    $owner = User::factory()->create();
    $workspace = createWorkspaceForOwner($owner, 'Validation Workspace');

    $response = $this->actingAs($owner)
        ->from(route('workspace-settings.edit'))
        ->withHeader('X-Workspace-Uid', $workspace->uid)
        ->patch(route('workspace-settings.update'), [
            'name' => '',
        ]);

    $response
        ->assertRedirect(route('workspace-settings.edit'))
        ->assertSessionHasErrors('name');

    expect($workspace->fresh()->name)->toBe('Validation Workspace');
});
