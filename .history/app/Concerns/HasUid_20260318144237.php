<?php

namespace App\Concerns;

trait HasUid
{
    public function boot()
    {
        static::creating(function ($model) {
            if (!$model->uid) {
                $model->uid = $model->generateUid();
            }
        });
    }

    public function generateUid()
    {
        return uniqid();
    }
}
