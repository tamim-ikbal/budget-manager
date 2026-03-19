<?php

use App\Models\Currency;
use App\Models\User;
use App\Models\Workspace;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $currency = Currency::query()
        ->where('is_active', true)
        ->orderByDesc('is_default')
        ->orderBy('id')
        ->firstOrFail();
    $workspace = Workspace::createDefaultWorkspace($user, $currency);

    $this->actingAs($user);

    $response = $this->withHeader('X-Workspace-Uid', $workspace->uid)
        ->get(route('dashboard'));
    $response->assertOk();
});
