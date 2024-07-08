<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurent;
use App\Models\GuestInformaion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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



    public function profile($uuid){
        $user = GuestInformaion::where('uuid', $uuid)->with('guest_reservation')->first();
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

    public function profile_update(Request $request){
        $user = GuestInformaion::where('uuid', $request->uuid)->first();
        if(!empty($user) && $user->status == 'active'){
        $validateUser = Validator::make($request->all(), [
            'uuid'=>'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => [
                'unique:guest_informaions,phone,'.$user->id,
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/', // Example regex for phone validation, adjust as necessary
            ],
            'email'=> 'required|email|unique:guest_informaions,email,'.$user->id,
            'address' => 'required',
            'city' => 'required',
            'country' => 'required',
            'post_code' => 'required',

        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        if (!empty($user)) {
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
            ]);
            return response()->json([
                'status' => true,
                'message' => ' Updated Successfully',
                'data' => $user
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found'
            ], 404);
        }
    }else{
        return response()->json([
            'status' => false,
            'message' => 'User Not Found'
        ], 404);
    }
   }

   public function guest_register(Request $request){
    $user = GuestInformaion::where('email', $request->email)->first();
    $validateUser = Validator::make($request->all(), [
        'first_name' => 'required',
        'last_name' => 'required',
        'phone' => [
            'required',
            $user != null ? Rule::unique('guest_informaions')->ignore($user->id) : 'unique:guest_informaions,phone',
            'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/', // Example regex for phone validation, adjust as necessary
        ],
        'email'=> 'required|email',
        $user != null ? Rule::unique('guest_informaions')->ignore($user->id) : 'unique:guest_informaions,email',
        'address' => 'required',
        'city' => 'required',
        'country' => 'required',
        'post_code' => 'required',
        'password' => 'required',

    ]);
    if ($validateUser->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'validation error',
            'errors' => $validateUser->errors()
        ], 401);
    }

    if(empty($user)){
        $user = GuestInformaion::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'post_code' => $request->post_code,
            'password' => Hash::make($request->password),
            'status' => 'inactive'
        ]);
    }else{
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'post_code' => $request->post_code,
            'password' => Hash::make($request->password),
            'status' => 'inactive'
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'Register Successfully',
        'data' => $user
    ], 200);
   }






   public function guest_login(Request $request){
    $validateUser = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required'
    ]);
    if($validateUser->fails()){
        return response()->json([
            'status' => false,
            'message' => 'validation error',
            'errors' => $validateUser->errors()
        ], 401);
    }
    try{
        if(!Auth::guard('guest_api')->attempt($request->only(['email', 'password']),)){
            return response()->json([
                'status' => false,
                'message' => 'Email & Password does not match with our record.',
            ], 401);
        }
       if(Auth::guard('guest_api')->attempt($request->only(['email', 'password']))){
            $user = Auth::guard('guest_api')->user();
            if($user->status == 'inactive'){
                return response()->json([
                    'status' => false,
                    'message' => 'Your Account is Inactive',
                ], 401);
            }
            $token = $user->createToken('login_access_tocken')->plainTextToken;
            return response()->json([
                'status' => true,
                'message' => 'Login Successfully',
                'data' => $user,
                'token' => $token
            ], 200);
       }
    }catch(Throwable $e){
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function logout(Request $request){
    $validateUser = Validator::make($request->all(), [
        'uuid' => 'required',
         'token' => 'required'
    ]);
    if($validateUser->fails()){
        return response()->json([
            'status' => false,
            'message' => 'validation error',
            'errors' => $validateUser->errors()
        ], 401);
    }
    try{
        $user = GuestInformaion::where('uuid', $request->uuid)->first();
        if(!empty($user)){
            $user->tokens()->where('id', $request->token)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Logout Successfully',
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ], 404);
        }
    }catch(Throwable $e){
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

}
