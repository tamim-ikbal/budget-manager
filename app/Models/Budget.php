<?php

namespace App\Models;

use App\Concerns\HasUid;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uid', 'workspace_id', 'title', 'budget', 'currency', 'created_by'])]
#[Hidden(['id'])]
class Budget extends Model
{
    use HasUid;

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
