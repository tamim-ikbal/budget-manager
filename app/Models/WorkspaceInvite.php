<?php

namespace App\Models;

use App\Concerns\HasUid;
use App\Enums\WorkspaceInviteRole;
use App\Enums\WorkspaceInviteStatus;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uid', 'workspace_id', 'email', 'role', 'token', 'status', 'expires_at'])]
#[Hidden(['id'])]
class WorkspaceInvite extends Model
{
    use HasUid;

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => WorkspaceInviteRole::class,
            'status' => WorkspaceInviteStatus::class,
            'expires_at' => 'datetime',
        ];
    }
}
