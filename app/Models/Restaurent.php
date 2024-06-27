<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Restaurent extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'uuid',
        'restaurent_id',
        'name',
        'address',
        'phone',
        'email',
        'avatar',
        'category',
        'description',
        'post_code',
        'status',
    ];

    public function category_list(){

        return $this->belongsTo(Category::class,'category','id');
    }


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
