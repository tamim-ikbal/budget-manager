<?php

namespace App\Models;

use App\Concerns\HasUid;
use App\Enums\WorkspaceMemberRole;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uid', 'workspace_id', 'user_id', 'name', 'role', 'joined_at'])]
#[Hidden(['id'])]
class WorkspaceMember extends Model
{
    use HasUid;

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => WorkspaceMemberRole::class,
            'joined_at' => 'datetime',
        ];
    }
}
