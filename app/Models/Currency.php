<?php

namespace App\Models;

use App\Concerns\HasUid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['uid', 'code', 'name', 'symbol', 'decimal_places', 'is_active', 'is_default'])]

#[Hidden(['id'])]
class Currency extends Model
{
    use HasUid;

    public function workspaces():HasMany
    {
        return $this->hasMany(Workspace::class);
    }   
}
