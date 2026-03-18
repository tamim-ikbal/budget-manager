<?php

namespace App\Concerns;

trait HasUid
{
    public function boot()
    {
        static::creating(function ($model) {
            $model->uid = St::uuid();
        });
    }

    public function generateUid()
    {
        return uniqid();
    }
}
