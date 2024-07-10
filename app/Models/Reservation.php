<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Reservation extends Model
{
    use HasFactory,LogsActivity;


    protected $fillable = [

        'guest_information_id',
        'table_master_id',
        'restaurant_id',
        'reservation_date',
        'reservation_time',
        'start',
        'end',
        'number_of_people',
        'status',
        'day',
        'updated_by',
    ];


    public function guest_information()
    {
        return $this->belongsTo(GuestInformaion::class, 'guest_information_id', 'id');
    }

    public function table_master()
    {
        return $this->belongsTo(TableMaster::class, 'table_master_id', 'id');
    }


    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'id');
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
