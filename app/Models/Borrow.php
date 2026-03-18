<?php

namespace App\Models;

use App\Concerns\HasUid;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uid', 'workspace_id', 'budget_id', 'workspace_member_id', 'amount', 'entry_date', 'note', 'paid_at', 'created_by'])]
#[Hidden(['id'])]
class Borrow extends Model
{
    use HasUid;

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function workspaceMember(): BelongsTo
    {
        return $this->belongsTo(WorkspaceMember::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}
