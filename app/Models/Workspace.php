<?php

namespace App\Models;

use App\Concerns\HasUid;
use App\Enums\WorkspaceMemberRole;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['uid', 'user_id', 'currency_id', 'name', 'time_zone'])]
#[Hidden(['id'])]
class Workspace extends Model
{
    use HasUid;

    public static function createDefaultWorkspace(User $user, Currency $currency): self
    {
        $workspace = self::query()->create([
            'user_id' => $user->id,
            'currency_id' => $currency->id,
            'name' => 'Personal Workspace',
            'time_zone' => config('app.timezone') ?: 'Asia/Dhaka',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
