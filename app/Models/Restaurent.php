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
    public function aval_slots(){

        return $this->hasMany(Slot::class,'restaurant_id','id');
    }

    public function slots_booked()
    {
        return $this->hasMany(Reservation::class, 'restaurant_id','id');
    }




    public function getAvailableSlots($day, $date)
    {
        $currentDate = date('m/d/Y');
        $currentTime = date('H:i');
        $slots = $this->aval_slots()->where('day', $day)->where('status', '=', 'active')->get();
        $bookedSlots = $this->slots_booked()->where([
            ['day', $day],
            ['reservation_date', $date],
        ])->whereNotIn('status', ['completed', 'cancelled'])->orderBy('start')->get(['start', 'end']);
        $availableSlots = collect();
        foreach ($slots as $slot) {
            $currentStart = ($date == $currentDate && $currentTime > $slot->slot_start) ? $currentTime : $slot->slot_start;
            $slotEnd = $slot->slot_end;
            foreach ($bookedSlots as $bookedSlot) {
                if ($bookedSlot->start < $slotEnd && $bookedSlot->end > $currentStart) {
                    if ($bookedSlot->start > $currentStart) {
                        $availableSlots->push([
                            'start' => $currentStart,
                            'end' => $bookedSlot->start
                        ]);
                    }
                    $currentStart = $bookedSlot->end;
                }
            }
            if ($currentStart < $slotEnd) {
                $availableSlots->push([
                    'start' => $currentStart,
                    'end' => $slotEnd
                ]);
            }
        }
        $filteredSlots = $availableSlots->reject(function($slot) use ($currentDate, $currentTime, $date) {
            return ($date == $currentDate) && ($slot['end'] <= $currentTime);
        });
        return $filteredSlots->values();
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
