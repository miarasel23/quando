<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Restaurent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'restaurent_id',
        'name',
        'address',
        'phone',
        'email',
        'avatar',
        'post_code',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
