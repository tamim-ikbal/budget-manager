<?php

namespace App\Models;

use App\Concerns\HasUid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uid', 'user_id', 'currency_id', 'name', 'time_zone'])]
#[Hidden(['id'])]
class Workspace extends Model
{
    use HasUid;

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency():BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
