<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'name',
    ];

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class,'category','id')->orderBy('id', 'desc')->with('category_list','aval_slots','label_tags','about_label_tags')->where('status', 'active')->select(['id','uuid','restaurant_id','name','address','phone','email','category','description','post_code','status','avatar','website','online_order']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
