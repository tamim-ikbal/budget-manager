<?php

namespace App\Models;

use App\Concerns\HasUid;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uid', 'workspace_id', 'name', 'color'])]
#[Hidden(['id'])]
class Category extends Model
{
    use HasUid;

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
