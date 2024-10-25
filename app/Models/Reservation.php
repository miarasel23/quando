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
        'check_in_time',
        'check_out_time',
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


    public function cancel_guest()
    {
        return $this->belongsTo(GuestInformaion::class, 'updated_by', 'uuid');
    }


  public function cancel_rest()
    {
        return $this->belongsTo(User::class, 'updated_by', 'uuid');
    }




    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }


    private static function generateReservationId()
    {
        $letters = strtoupper(Str::random(4));  // Generate 4 random uppercase letters
        $numbers = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);  // Generate 4 random numbers padded with zeros
        return $letters . $numbers;
    }



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->reservation_id)) {
                $model->reservation_id = self::generateReservationId();
            }
        });
    }
}
