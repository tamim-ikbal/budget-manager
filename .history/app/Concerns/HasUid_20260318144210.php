<?php

namespace App\Concerns;

trait HasUid
{
    public function boot()
    {
        static::creating(function ($model) {
            $model->uid = $model->generateUid();
        });
    }

    public function generateUid()
    {
        return uniqid();
    }
}
