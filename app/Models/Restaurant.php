<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;
use DB;

class Restaurant extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'uuid',
        'restaurant_id',
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
        'reservation_status',
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


    public function label_tags(){
        return $this->hasMany(LabelTag::class,'restaurant_id','id')->where('status','active')->select(['id','uuid','name','status','restaurant_id']);
    }

    public function about_label_tags(){
        return $this->hasMany(AboutTag::class,'restaurant_id','id')->where('status','active')->select(['id','uuid','name','status','restaurant_id']);
    }



    public function menu_description(){
        return $this->hasOne(SectionDescription::class,'restaurant_id','id')->where('section','=','menu')->select(['id','restaurant_id','description','section']);

    }

    public function photo_description(){
        return $this->hasOne(SectionDescription::class,'restaurant_id','id')->where('section','=','photo')->select(['id','restaurant_id','description','section']);
    }

    public function reviews_description(){
        return $this->hasOne(SectionDescription::class,'restaurant_id','id')->where('section','=','review')->select(['id','restaurant_id','description','section']);
    }

    public function menus(){
        return $this->hasMany(ResMenu::class,'restaurant_id','id')->with('menu_category')->where('status','active');
    }

    public function photos(){
        return $this->hasMany(Photo::class,'restaurant_id','id')->where('status','active')->select(['id','uuid','restaurant_id','avatar','status']);
    }

    public function reviews(){
        return $this->hasMany(Review::class,'restaurant_id','id')->with('guest_informaion')->where('status','active');
    }















    // public function getAvailableSlots($day, $date, $interval)
    // {
    //     $intervalTime = $interval; // Default to the provided interval

    //     // Get all available slots for the given day
    //     $slots = $this->aval_slots()->where('day', $day)->where('status', '=', 'active')->get();
    //     $availableSlots = collect(); // Initialize a collection to store all available slots

    //     foreach ($slots as $slot) {
    //         $startTime = Carbon::parse($slot->slot_start); // e.g., 9:00 AM
    //         $endTime = Carbon::parse($slot->slot_end); // e.g., 12:00 PM

    //         // Calculate total duration in minutes
    //         $totalDuration = $startTime->diffInMinutes($endTime);
    //         // Calculate the number of intervals
    //         $numberOfIntervals = floor($totalDuration / $intervalTime); // Use floor to avoid partial intervals

    //         // Get all booked slots for the given day and date
    //         $bookedSlots = DB::table('reservations')
    //             ->where('day', $day)
    //             ->where('reservation_date', $date)
    //             ->whereNotIn('status', ['completed', 'cancelled'])
    //             ->select('start', 'end')
    //             ->get();

    //         // Iterate through each interval
    //         for ($i = 0; $i < $numberOfIntervals; $i++) {
    //             $slotStart = $startTime->copy()->addMinutes($i * $intervalTime);
    //             $slotEnd = $slotStart->copy()->addMinutes($intervalTime);
    //             $isAvailable = true;

    //             // Check if the current slot overlaps with any booked slot
    //             foreach ($bookedSlots as $bookedSlot) {
    //                 $bookedStart = Carbon::parse($bookedSlot->start);
    //                 $bookedEnd = Carbon::parse($bookedSlot->end);

    //                 if ($slotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
    //                     $isAvailable = false;
    //                     break;
    //                 }
    //             }

    //             // If the slot is available, add it to the collection
    //             if ($isAvailable) {
    //                 $availableSlots->push([
    //                     'start' => $slotStart->format('H:i:s'),
    //                     'end' => $slotEnd->format('H:i:s')
    //                 ]);
    //             }
    //         }
    //     }

    //     // Return all available slots across all time ranges
    //     return $availableSlots;
    // }






    public function getAvailableSlots($day, $date, $interval)
{
    $intervalTime = $interval; // Default to the provided interval

    // Get all available slots for the given day
    $slots = $this->aval_slots()->where('day', $day)->where('status', '=', 'active')->get();
    $availableSlots = collect(); // Initialize a collection to store all available slots

    // Get the current time
    $currentDateTime = Carbon::now();

    foreach ($slots as $slot) {
        $startTime = Carbon::parse($slot->slot_start); // e.g., 9:00 AM
        $endTime = Carbon::parse($slot->slot_end); // e.g., 12:00 PM

        // Calculate total duration in minutes
        $totalDuration = $startTime->diffInMinutes($endTime);
        // Calculate the number of intervals
        $numberOfIntervals = floor($totalDuration / $intervalTime); // Use floor to avoid partial intervals

        // Get all booked slots for the given day and date
        $bookedSlots = DB::table('reservations')
            ->where('day', $day)
            ->where('reservation_date', $date)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->select('start', 'end')
            ->get();

        // Iterate through each interval
        for ($i = 0; $i < $numberOfIntervals; $i++) {
            $slotStart = $startTime->copy()->addMinutes($i * $intervalTime);
            $slotEnd = $slotStart->copy()->addMinutes($intervalTime);

            // Skip slots that are in the past
            if ($slotStart->lt($currentDateTime)) {
                continue;
            }

            $isAvailable = true;

            // Check if the current slot overlaps with any booked slot
            foreach ($bookedSlots as $bookedSlot) {
                $bookedStart = Carbon::parse($bookedSlot->start);
                $bookedEnd = Carbon::parse($bookedSlot->end);

                if ($slotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            // If the slot is available, add it to the collection
            if ($isAvailable) {
                $availableSlots->push([
                    'start' => $slotStart->format('H:i:s'),
                    'end' => $slotEnd->format('H:i:s')
                ]);
            }
        }
    }

    // Return all available slots across all time ranges
    return $availableSlots;
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
