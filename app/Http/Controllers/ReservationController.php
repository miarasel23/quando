<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Reservation;
use App\Models\GuestInformaion;
use App\Models\TableMaster;
use App\Models\EmailSendValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;
use App\Models\OneTimeOtpStore;
use App\Traits\emaiTraits;

class ReservationController extends Controller
{

    use emaiTraits;
    public function time_hold(Request $request)
    {

        if(in_array($request->params, ['update'])){
            $old_reservation = Reservation::where('uuid', $request->uuid)->where('status', 'hold')->first();
        }
        $validateUser = Validator::make($request->all(), [
            'user_uuid' => 'string|exists:guest_informaions,uuid',
            'rest_uuid' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'date' => 'required',
            'day'=>'required',
            'number_of_people' => 'required',
            'status' => 'required',
            'uuid' =>  in_array($request->params, ['update']) ? 'required|exists:reservations,uuid' : 'nullable|string|max:255',
            'params' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
       $rest_data = Restaurant::where('uuid', $request->rest_uuid)->first();
       if($request->user_uuid != null){
           $user_data = GuestInformaion::where('uuid', $request->user_uuid)->first();
       }
       $tabledata = Reservation::where([
        ['start', '=', $request->start_time],
        ['end', '=', $request->end_time],
        ['day', '=', $request->day],
        ['reservation_date', '=', $request->date],
        ['restaurant_id', '=', $rest_data->id]
       ])
       ->whereNotIn('status', ['cancelled', 'completed'])
       ->get();
       $allTables = TableMaster::where('restaurant_id', $rest_data->id)->orderBy('max_seats', 'asc')  // Order by max_seats in ascending order
       ->get();
        if (count($tabledata) > 0 && count($allTables) > 0) {
            $reservedTableIds = $tabledata->pluck('table_master_id')->toArray();
            $availableTables = $allTables->filter(function ($table) use ($reservedTableIds) {
                return !in_array($table->id, $reservedTableIds);
            })->values();
        } else {
            $availableTables = $allTables;
        }
        $assigned_table = $availableTables->first(function ($tableAssing) use ($request) {
            return $tableAssing->max_seats >= $request->number_of_people;
        });

        if(!empty($assigned_table) && in_array($request->params, ['create'])){
            $user = Reservation::create([
                'restaurant_id' => $rest_data->id,
                'user_id' =>$request->user_uuid != null ? $user_data->id :null,
                'reservation_time' => $request->start_time,
                'table_master_id' => $assigned_table->id,
                'start' => $request->start_time,
                'end' => $request->end_time,
                'reservation_date' => $request->date,
                'number_of_people' => $request->number_of_people,
                'day' => $request->day,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Reservation Hold Successfully',
                'data' => $user
            ], 200);
        }elseif(isset($old_reservation) && !empty($old_reservation) && in_array($request->params, ['update'])){
            $old_reservation->update([
                'start' => $request->start_time,
                'end' => $request->end_time,
                'reservation_date' => $request->date,
                'number_of_people' => $request->number_of_people,
                'reservation_time' => $request->start_time,
                'day' => $request->day,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Reservation Hold Successfully',
                'data' => $old_reservation
            ], 200);
        }

        else{
            return response()->json([
                'status' => false,
                'message' => 'No Tables Available',
            ], 401);
        }




    }


    public function reservation_book(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'reservation_uuid' => 'required|exists:reservations,uuid',
            'guest_id' => 'integer|exists:guest_informaions,id',
            'rest_uuid' => 'required|exists:restaurants,uuid',
            'start_time' => 'required',
            'end_time' => 'required',
            'date' => 'required',
            'day'=>'required',
            'number_of_people' => 'required',
            'noted' => 'nullable',
            'status'=>'required'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $current_date = \Carbon\Carbon::now()->format('Y-m-d');
        $count = 0;
        $user = GuestInformaion::where('id', $request->guest_id)->first();
        $one_time_password = OneTimeOtpStore::where('email',$user->email)->orderBy('id','desc')->first();

        if(!empty($one_time_password)){
            $one_time_passs = $one_time_password->otp;
        }else{
            $one_time_passs = "";
        }


        $reservation = Reservation::where('uuid', $request->reservation_uuid)->first();
        $restaurant = Restaurant::where('uuid', $request->rest_uuid)->first();
          if($reservation != null){
            $reservation->update([
                'status' => $restaurant->reservation_status == 'automatic' ? 'confirmed' :'pending',
                'guest_information_id' => $request->guest_id,
                'reservation_time' => $request->start_time,
                'table_master_id' => $reservation->table_master_id,
                'start' => $request->start_time,
                'end' => $request->end_time,
                'reservation_date' => $request->date,
                'number_of_people' => $request->number_of_people,
                'noted' => $request->noted,
                'day' => $request->day,

            ]);
            $reservationDetails = Reservation::where('uuid', $request->reservation_uuid)->with('guest_information', 'table_master', 'restaurant')->first();
            if(isset($reservationDetails) && !empty($reservationDetails)){

            if(!empty($user)){

                $email_send_history = EmailSendValidation::where('email', $user->email)->get();
                    if( $email_send_history->count() >= 0){

                    foreach ($email_send_history as $key => $value) {
                        if(\Carbon\Carbon::parse($value->created_at)->format('Y-m-d') == $current_date){
                            $count++;
                        }

                     }
                        if($count < 20){
                            $this->sendEmailForReservation($reservationDetails,    $reservationDetails->status == 'confirmed' ? 'Reservation Confirmation' : 'Reservation pending',$one_time_passs);
                            EmailSendValidation::create([
                                'email' => $user->email,
                                'limit' => 1,
                                'status'=>'success',
                            ]);

                            $this->sendEmailForReservationOwner($reservationDetails, $reservationDetails->status == 'confirmed' ? 'Reservation Confirmation' : 'Reservation pending',);
                            EmailSendValidation::create([
                                'email' => $reservationDetails->restaurant->email,
                                'limit' => 1,
                                'status'=>'success',
                            ]);


                        }else{
                            EmailSendValidation::create([
                                'email' => $user->email,
                                'limit' => 1,
                                'status'=>'failed',
                            ]);
                        }
                    }


             }
           }

            return response()->json([
                'status' => true,
                'message' => 'Reservation Booked Successfully',
                'data' => $reservation
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Reservation Not Found',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Reservation List',
            'data' => $reservation
        ], 200);
    }


    public function reservation_removed(Request $request){
        $validateUser = Validator::make($request->all(), [
            'reservation_uuid' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $reservation = Reservation::where('uuid', $request->reservation_uuid)->first();
        if($reservation != null){
            if($reservation->status == 'hold'){
                $reservation->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Reservation Hold Removed Successfully',
                ], 200);
            }

        }else{
            return response()->json([
                'status' => false,
                'message' => 'Reservation Not Found',
            ], 404);
        }
    }


    public function reservation_info( $uuid){

        $user = GuestInformaion::where('uuid', $uuid)->first();
        if($user != null){
        $reservation = Reservation::where('guest_information_id', $user->id)->with('guest_information','table_master','restaurant','cancel_guest','cancel_rest')->get();
        if($reservation != null){
            return response()->json([
                'status' => true,
                'message' => 'Reservation Info',
                'data' => $reservation
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Reservation Not Found',
            ], 404);
        }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Guest Not Found',
            ], 404);
        }
    }


    public function reservation_list(){
        $reservation = Reservation::where('status','!=', 'hold')->with('guest_information','table_master','restaurant','cancel_guest','cancel_rest')->get();
        if($reservation != null){
            return response()->json([
                'status' => true,
                'message' => 'Reservation Info',
                'data' => $reservation
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Reservation Not Found',
            ], 404);
        }
    }

    public function reservation_for_restaurant(Request $request){
        if (in_array($request->params, ['cancel', 'checkin', 'checkout','reject','accept'])) {
            $data = Reservation::where('uuid', $request->uuid)->first();
        }
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => in_array($request->params, ['info']) ? 'required|exists:restaurants,uuid' : 'required',
            'checkin_time' => in_array($request->params, ['checkin']) ? 'required' : 'nullable',
            'checkout_time' => in_array($request->params, ['checkout']) ? 'required' : 'nullable',
            'uuid' => in_array($request->params, ['checkin', 'checkout', 'cancel','reject','accept']) ? 'required|exists:reservations,uuid' : 'nullable',
            'user_uuid' => in_array($request->params, [ 'cancel','reject','accept']) ? 'required' : 'nullable',
            'params' => 'required|string'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        if (in_array($request->params, ["info"])) {
            $perPage = $request->input('per_page', 100000);
            $rest_data = Restaurant::where('uuid', $request->rest_uuid)->first();

            if ($rest_data != null) {
                $reservation = Reservation::where('restaurant_id', $rest_data->id)->where('status', '!=','hold')
                    ->whereHas('guest_information', function ($query) {
                        $query->where('status', 'active');
                    })
                    ->with('guest_information', 'table_master', 'restaurant','cancel_guest','cancel_rest')->orderBy('id', 'desc')
                    ->paginate($perPage);

                if ($reservation != null) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Reservation Info',
                        'data' => $reservation
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Reservation Not Found',
                    ], 404);
                }
            }
        } elseif (in_array($request->params, ["checkin"])) {
            if ($data != null && $data->status == 'pending') {
                $data->check_in_time = $request->checkin_time;
                $data->status = 'check_in';
                $data->noted = $request->note;
                $data->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Reservation Checked In Successfully',
                    'data' => $data
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Reservation Not Found',
                ], 404);
            }
        } elseif (in_array($request->params, ['checkout'])) {
            if ($data != null && $data->status == 'check_in') {
                $data->check_out_time = $request->checkout_time;
                $data->status = 'completed';
                $data->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Reservation Checked Out Successfully',
                    'data' => $data
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Reservation Not Found',
                ], 404);
            }
        }elseif(in_array($request->params, ['cancel'])){
            if ($data != null && $data->status == 'checkin' || $data->status == 'pending') {

                $data->status = 'cancelled';
                $data->noted = $request->note;
                $data->updated_by = $request->user_uuid;
                $data->save();
                $reservationDetails = Reservation::where('uuid', $request->uuid)->with('guest_information', 'table_master', 'restaurant')->first();
                $this->sendEmailForReservationCancel($reservationDetails,'Reservation Cancelled');
                return response()->json([
                    'status' => true,
                    'message' => 'Reservation Cancelled Successfully',
                    'data' => $data
                ], 200);
            }
        }elseif (in_array($request->params, ['accept', 'reject']))
            {

                if ($data != null && $data->status == 'pending') {
                $data->status = $request->params == 'accept' ? 'confirmed' : 'reject';
                $data->noted = $request->note;
                $data->updated_by = $request->user_uuid;
                $data->save();
                $reservationDetails = Reservation::where('uuid', $request->uuid)->with('guest_information',
                'table_master', 'restaurant')->first();
                $this->sendEmailForReservationCancel($reservationDetails, $request->status == 'accept' ? 'Reservation
                Accepted' : 'Reservation Rejected');
                return response()->json([
                'status' => true,
                'message' => 'Reservation update Successfully',
                'data' => $data
                ], 200);
                }
            }
        else {
        return response()->json([
        'status' => false,
        'message' => 'Reservation Not Found',
        ], 404);
        }
    }

}
