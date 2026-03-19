<?php

use App\Enums\WorkspaceMemberRole;
use App\Models\Currency;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    $workspace = Workspace::query()->where('user_id', $user->id)->firstOrFail();
    $defaultCurrencyId = Currency::query()
        ->where('is_active', true)
        ->where('is_default', true)
        ->value('id');

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    expect($workspace->name)->toBe('Personal Workspace');
    expect($workspace->currency_id)->toBe($defaultCurrencyId);

    $this->assertDatabaseHas('workspace_members', [
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'name' => $user->name,
        'role' => WorkspaceMemberRole::Owner->value,
    ]);
    expect(WorkspaceMember::query()->where('workspace_id', $workspace->id)->count())->toBe(1);
});

test('registration fails when no active currency exists', function () {
    Currency::query()->update([
        'is_active' => false,
        'is_default' => false,
    ]);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'No Currency',
        'email' => 'nocurrency@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(Workspace::query()->count())->toBe(0);
});

test('registration uses active default currency when available', function () {
    Currency::query()->update([
        'is_active' => false,
        'is_default' => false,
    ]);

    $fallbackCurrency = Currency::factory()->active()->create([
        'code' => 'AAA',
        'name' => 'AAA Currency',
        'symbol' => 'A',
        'is_default' => false,
    ]);

    $defaultCurrency = Currency::factory()->default()->create([
        'code' => 'BBB',
        'name' => 'BBB Currency',
        'symbol' => 'B',
    ]);

    $this->post(route('register.store'), [
        'name' => 'Default Currency User',
        'email' => 'default-currency@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'default-currency@example.com')->firstOrFail();
    $workspace = Workspace::query()->where('user_id', $user->id)->firstOrFail();

    expect($workspace->currency_id)->toBe($defaultCurrency->id);
    expect($workspace->currency_id)->not->toBe($fallbackCurrency->id);
});

test('registration falls back to first active currency when no active default exists', function () {
    Currency::query()->update([
        'is_active' => false,
        'is_default' => false,
    ]);

    $firstActive = Currency::factory()->active()->create([
        'code' => 'CCC',
        'name' => 'CCC Currency',
        'symbol' => 'C',
        'is_default' => false,
    ]);

    Currency::factory()->active()->create([
        'code' => 'DDD',
        'name' => 'DDD Currency',
        'symbol' => 'D',
        'is_default' => false,
    ]);

    $this->post(route('register.store'), [
        'name' => 'Fallback Currency User',
        'email' => 'fallback-currency@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'fallback-currency@example.com')->firstOrFail();
    $workspace = Workspace::query()->where('user_id', $user->id)->firstOrFail();

    expect($workspace->currency_id)->toBe($firstActive->id);
});
