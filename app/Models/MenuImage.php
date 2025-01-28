<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MenuImage extends Model
{
    use HasFactory;


    protected $fillable = [
        'restaurant_id',
        'image',
        'status',
    ];



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

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
