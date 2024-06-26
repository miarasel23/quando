<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class TableMaster extends Model
{
    use HasFactory;


    protected $fillable = [
        'table_name',
        'restaurant_id',
        'capacity',
        'description',
        'min_seats',
        'max_seats',
        'reservation_online',
        'floor_area_id',
        'table_id',
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
