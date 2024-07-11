<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Review extends Model
{
    use  HasFactory,LogsActivity;


  protected $fillable = [
    'restaurant_id',
    'guest_informaion_id',
    'review',
    'rating',
    'status'
  ];


   public function restaurant(){

       return $this->belongsTo(Restaurant::class,'restaurant_id','id');
   }

   public function guest_informaion(){

       return $this->belongsTo(GuestInformaion::class,'guest_informaion_id','id');
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
