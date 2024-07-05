<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;
use DB;

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
        'created_by',
        'website',
        'updated_by',
        'online_order',
        'status',
    ];

    public function category_list(){

        return $this->belongsTo(Category::class,'category','id')->select(['id','name']);
    }
    public function aval_slots(){

        return $this->hasMany(Slot::class,'restaurant_id','id')->where('status','active')->select(['id','uuid','restaurant_id','day','slot_start', 'slot_end']);
    }

    public function slots_booked()
    {
        return $this->hasMany(Reservation::class, 'restaurant_id','id');
    }


    public function label_taqs(){
        return $this->hasMany(LabelTaq::class,'restaurant_id','id')->where('status','active')->select(['id','uuid','name','status','restaurant_id']);
    }

    public function about_label_taqs(){
        return $this->hasMany(AboutTaq::class,'restaurant_id','id')->where('status','active')->select(['id','uuid','name','status','restaurant_id']);
    }



    public function getAvailableSlots($day, $date,$interval)
    {
        // $currentDate = date('m/d/Y');
        // $currentTime = date('H:i');
        // $slots = $this->aval_slots()->where('day', $day)->where('status', '=', 'active')->get();
        // $bookedSlots = $this->slots_booked()->where([
        //     ['day', $day],
        //     ['reservation_date', $date],
        // ])->whereNotIn('status', ['completed', 'cancelled'])->orderBy('start')->get(['start', 'end']);
        // $availableSlots = collect();
        // foreach ($slots as $slot) {
        //     $currentStart = ($date == $currentDate && $currentTime > $slot->slot_start) ? $currentTime : $slot->slot_start;
        //     $slotEnd = $slot->slot_end;
        //     foreach ($bookedSlots as $bookedSlot) {
        //         if ($bookedSlot->start < $slotEnd && $bookedSlot->end > $currentStart) {
        //             if ($bookedSlot->start > $currentStart) {
        //                 $availableSlots->push([
        //                     'start' => $currentStart,
        //                     'end' => $bookedSlot->start
        //                 ]);
        //             }
        //             $currentStart = $bookedSlot->end;
        //         }
        //     }
        //     if ($currentStart < $slotEnd) {
        //         $availableSlots->push([
        //             'start' => $currentStart,
        //             'end' => $slotEnd
        //         ]);
        //     }
        // }
        // $filteredSlots = $availableSlots->reject(function($slot) use ($currentDate, $currentTime, $date) {
        //     return ($date == $currentDate) && ($slot['end'] <= $currentTime);
        // });
        // return $filteredSlots->values();






        $intervalTime = $interval; // default to 15 minutes if not provided

         $slots = $this->aval_slots()->where('day', $day)->where('status', '=', 'active')->get();
         foreach ($slots as $slot) {
            $startTime = Carbon::parse( $slot->slot_start); // 9:00 AM
            $endTime =  Carbon::parse( $slot->slot_end); // 12:00 AM (midnight)

            // Calculate total duration in minutes
            $totalDuration = $startTime->diffInMinutes($endTime);
            // Calculate the number of intervals
            $numberOfIntervals = $totalDuration / $intervalTime;
            // Get all booked slots for the given day and date
            $bookedSlots = DB::table('reservations')
                ->where('day', $day)
                ->where('reservation_date', $date)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->select('start', 'end')
                ->get();
            $availableSlots = collect();
            for ($i = 0; $i < $numberOfIntervals; $i++) {
                $slotStart = $startTime->copy()->addMinutes($i * $intervalTime);
                $slotEnd = $slotStart->copy()->addMinutes($intervalTime);
                $isAvailable = true;
                foreach ($bookedSlots as $bookedSlot) {
                    $bookedStart = Carbon::parse($bookedSlot->start);
                    $bookedEnd = Carbon::parse($bookedSlot->end);

                    if ($slotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
                        $isAvailable = false;
                        break;
                    }
                }
                if ($isAvailable) {
                    $availableSlots->push([
                        'start' => $slotStart->format('H:i:s'),
                        'end' => $slotEnd->format('H:i:s')
                    ]);
                }
            }
            // Calculate total available minutes
            $totalAvailableMinutes = $availableSlots->count() * $intervalTime;
            return $availableSlots;
         }



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
