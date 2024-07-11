<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class ResMenu extends Model
{
    use  HasFactory,LogsActivity;



    protected $fillable = [
        'name',
        'description',
        'halal_name',
        'restaurant_id',
        'menu_catergory_id',
        'price',
        'price_symbol',
        'status'
    ];


    public function menu_category(){
        return $this->belongsTo(MenuCatergory::class,'menu_catergory_id');
    }


    public function menu_description(){
        return $this->hasOne(SectionDescription::class,'restaurant_id','restaurant_id')->where('section','menu');
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
