<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AboutTag extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'uuid',
        'name',
        'status',
        'restaurant_id'

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