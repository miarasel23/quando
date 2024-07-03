<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurent;
use Auth;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //

    public function user_create(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'res_uuid' => 'required',
            'user_type' => 'required',
            'phone' => [

                'unique:users',
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/', // Example regex for phone validation, adjust as necessary
            ],
            'password' => 'required'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $user = User::create([
            'name' => $request->name,
            'res_uuid' => $request->res_uuid,
            'user_type' => $request->user_type,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            'data' => $user
        ], 200);
    }

    public function user_update(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $request->id,
            'res_uuid' => 'required',
            'phone' => [
                'unique:users',
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/', // Example regex for phone validation, adjust as necessary
            ],
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $user = User::where('uuid', $request->uuid)->first();
        if (!empty($user)) {
            $user->update([
                'name' => $request->name,
                'res_uuid' => $request->res_uuid,
                'user_type' => $request->user_type,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => $request->password !="" ? Hash::make($request->password) : $user->password,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully',
                'data' => $user
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found'
            ], 404);
        }
    }

    public function user_info($uuid){
        $user = User::where('uuid', $uuid)->first();
        if (!empty($user) && $user->status == 'active') {
            return response()->json([
                'status' => true,
                'message' => 'User Info',
                'data' => $user
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found'
            ], 404);
        }
    }



}
