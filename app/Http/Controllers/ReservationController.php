<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurent;
use App\Models\Reservation;
use App\Models\GuestInformaion;
use App\Models\TableMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function time_hold(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'user_uuid' => 'string',
            'rest_uuid' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'date' => 'required',
            'day'=>'required',
            'number_of_people' => 'required',
            'status' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
       $rest_data = Restaurent::where('uuid', $request->rest_uuid)->first();
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
       $allTables = TableMaster::where('restaurant_id', $rest_data->id)->get();
        if (count($tabledata) > 0 && count($allTables) > 0) {
            $reservedTableIds = $tabledata->pluck('table_master_id')->toArray();
            $availableTables = $allTables->filter(function ($table) use ($reservedTableIds) {
                return !in_array($table->id, $reservedTableIds);
            })->values();
        } else {
            $availableTables = $allTables;
        }
        if(count($availableTables) > 0){
            $user = Reservation::create([
                'restaurant_id' => $rest_data->id,
                'user_id' =>$request->user_uuid != null ? $user_data->id :null,
                'reservation_time' => $request->start_time,
                'table_master_id' => $availableTables[0]->id,
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
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'No Tables Available',
                ], 401);
            }
    }


    public function reservation_book(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'reservation_uuid' => 'required',
            'guest_id' => 'integer',
            'status'=>'required'
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
            $reservation->update([
                'status' => 'pending',
                'guest_information_id' => $request->guest_id

            ]);
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
            $reservation->delete();
            return response()->json([
                'status' => true,
                'message' => 'Reservation Hold Removed Successfully',
            ], 200);
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
        $reservation = Reservation::where('guest_information_id', $user->id)->with('guest_information','table_master','restaurant')->get();
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

}
