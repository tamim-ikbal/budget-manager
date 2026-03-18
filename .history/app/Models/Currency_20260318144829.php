<?php

namespace App\Models;

use App\Concerns\HasUid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable([ 'code', 'name', 'symbol', 'decimal_places', 'is_active', 'is_default'])]

#[Hidden(['id'])]
class Currency extends Model
{
    use HasUid;
}
