<?php

namespace App\Concerns;

use Illuminate\Support\Str;

trait HasUid
{
    public static function bootHasUid(): void
    {
        static::creating(function ($model) {
            if (! $model->uid) {
                $model->uid = $model->generateUid();
            }
        });
    }

    public function generateUid(): string
    {
        return (string) Str::uuid();
    }
}
