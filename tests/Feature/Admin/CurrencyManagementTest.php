<?php

use App\Enums\UserRole;
use App\Models\Currency;
use App\Models\User;
use App\Models\Workspace;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
});

function adminUser(): User
{
    return User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);
}

test('admin can view currencies page', function () {
    $response = $this->actingAs(adminUser())
        ->get(route('admin.currencies.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/currencies/Index')
            ->has('currencies')
            ->where('status', null),
        );
});

test('admin can view admin dashboard', function () {
    $response = $this->actingAs(adminUser())
        ->get(route('admin.dashboard'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Dashboard'),
        );
});

test('non admin cannot access currencies page', function () {
    $response = $this->actingAs(User::factory()->create([
        'role' => UserRole::USER,
    ]))->get(route('admin.currencies.index'));

    $response->assertForbidden();
});

test('admin can create currency', function () {
    $response = $this->actingAs(adminUser())
        ->from(route('admin.currencies.index'))
        ->post(route('admin.currencies.store'), [
            'code' => 'eur',
            'name' => 'Euro',
            'symbol' => '€',
            'decimal_places' => 2,
            'is_active' => true,
        ]);

    $response
        ->assertRedirect(route('admin.currencies.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('currencies', [
        'code' => 'EUR',
        'name' => 'Euro',
        'symbol' => '€',
        'decimal_places' => 2,
        'is_active' => true,
        'is_default' => false,
    ]);
});

test('admin can update currency including status in update endpoint', function () {
    $currency = Currency::query()->where('is_default', false)->firstOrFail();

    $response = $this->actingAs(adminUser())
        ->from(route('admin.currencies.index'))
        ->patch(route('admin.currencies.update', ['currency' => $currency->uid]), [
            'code' => $currency->code,
            'name' => 'Updated Currency Name',
            'symbol' => $currency->symbol,
            'decimal_places' => 2,
            'is_active' => false,
        ]);

    $response
        ->assertRedirect(route('admin.currencies.index'))
        ->assertSessionHasNoErrors();

    expect($currency->fresh()->is_active)->toBe(0);
    expect($currency->fresh()->name)->toBe('Updated Currency Name');
});

test('default currency cannot be deactivated through update endpoint', function () {
    $currency = Currency::query()->where('is_default', true)->firstOrFail();

    $response = $this->actingAs(adminUser())
        ->from(route('admin.currencies.index'))
        ->patch(route('admin.currencies.update', ['currency' => $currency->uid]), [
            'code' => $currency->code,
            'name' => $currency->name,
            'symbol' => $currency->symbol,
            'decimal_places' => $currency->decimal_places,
            'is_active' => false,
        ]);

    $response
        ->assertRedirect(route('admin.currencies.index'))
        ->assertSessionHasErrors('is_active');

    expect($currency->fresh()->is_active)->toBe(1);
});

test('admin can set active currency as default', function () {
    $oldDefault = Currency::query()->where('is_default', true)->firstOrFail();

    $targetCurrency = Currency::query()
        ->where('is_default', false)
        ->where('is_active', true)
        ->firstOrFail();

    $response = $this->actingAs(adminUser())
        ->from(route('admin.currencies.index'))
        ->patch(route('admin.currencies.default.update', ['currency' => $targetCurrency->uid]));

    $response
        ->assertRedirect(route('admin.currencies.index'))
        ->assertSessionHasNoErrors();

    expect($targetCurrency->fresh()->is_default)->toBe(1);
    expect($oldDefault->fresh()->is_default)->toBe(0);
    expect(Currency::query()->where('is_default', true)->count())->toBe(1);
});

test('inactive currency cannot be set as default', function () {
    $targetCurrency = Currency::query()->where('is_default', false)->firstOrFail();

    $targetCurrency->update([
        'is_active' => false,
    ]);

    $response = $this->actingAs(adminUser())
        ->from(route('admin.currencies.index'))
        ->patch(route('admin.currencies.default.update', ['currency' => $targetCurrency->uid]));

    $response
        ->assertRedirect(route('admin.currencies.index'))
        ->assertSessionHasErrors('currency');

    expect($targetCurrency->fresh()->is_default)->toBe(0);
});

test('admin can delete non-default currency', function () {
    $targetCurrency = Currency::query()->where('is_default', false)->firstOrFail();

    $response = $this->actingAs(adminUser())
        ->from(route('admin.currencies.index'))
        ->delete(route('admin.currencies.destroy', ['currency' => $targetCurrency->uid]));

    $response
        ->assertRedirect(route('admin.currencies.index'))
        ->assertSessionHasNoErrors();

    expect($targetCurrency->fresh())->toBeNull();
});

test('default currency cannot be deleted', function () {
    $defaultCurrency = Currency::query()->where('is_default', true)->firstOrFail();

    $response = $this->actingAs(adminUser())
        ->from(route('admin.currencies.index'))
        ->delete(route('admin.currencies.destroy', ['currency' => $defaultCurrency->uid]));

    $response
        ->assertRedirect(route('admin.currencies.index'))
        ->assertSessionHasErrors('currency');

    expect($defaultCurrency->fresh())->not->toBeNull();
});

test('currency in use by workspace cannot be deleted', function () {
    $targetCurrency = Currency::query()->where('is_default', false)->firstOrFail();
    $workspaceOwner = User::factory()->create();

    Workspace::query()->create([
        'user_id' => $workspaceOwner->id,
        'currency_id' => $targetCurrency->id,
        'name' => 'Workspace Using Currency',
        'time_zone' => 'Asia/Dhaka',
    ]);

    $response = $this->actingAs(adminUser())
        ->from(route('admin.currencies.index'))
        ->delete(route('admin.currencies.destroy', ['currency' => $targetCurrency->uid]));

    $response
        ->assertRedirect(route('admin.currencies.index'))
        ->assertSessionHasErrors('currency');

    expect($targetCurrency->fresh())->not->toBeNull();
});
