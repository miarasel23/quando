<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Photo extends Model
{
    use  HasFactory,LogsActivity;


  protected $fillable = [

    'avatar',
    'restaurant_id',
    'status'
  ];

  public function photo_description(){
    return $this->hasOne(SectionDescription::class,'restaurant_id','restaurant_id')->where('section','photo');
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
