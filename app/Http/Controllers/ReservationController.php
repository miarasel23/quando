<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurent;
use App\Models\Reservation;
use App\Models\GuestInformaion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function time_hold(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'user_uuid' => 'required',
            'rest_uuid' => 'required',
            'time' => 'required',
            'date' => 'required',
            'day'=>'required',
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
       $user_data = GuestInformaion::where('user_uuid', $request->user_uuid)->first();


        $user = Reservation::create([
            'time' => $request->time,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            'data' => $user
        ], 200);
    }
}
