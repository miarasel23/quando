<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'day',
        'slot_start',
        'slot_end',
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
