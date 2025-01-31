<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\GuestInformaion;
use App\Models\Enquiry;
use App\Models\EmailSendValidation;
use App\Models\OneTimeOtpStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;
use App\Traits\emaiTraits;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Carbon;

class UserController extends Controller
{



    use emaiTraits;


    public function user_activation_sent_link(Request $request){
     $data = $this->sendEmail($request,'user');
            return response()->json([
            'status' => true,
            'message' => 'activation link sent successfully',
            'data' => $data
        ], 200);
    }

    public function user_create(Request $request)
    {

        if(in_array( $request->params,['update', 'info'])){
            $old_user = User::where('uuid', $request->uuid)->first();
        }
        $validateUser = Validator::make($request->all(), [
                'name' =>  in_array($request->params, ['info']) ? 'nullable' : 'required',
                'email' => in_array($request->params, ['update']) ?
                'required|email|unique:users,email,' . $old_user->id :
                (in_array($request->params, ['info']) ?
                'nullable' :'required|email|unique:users'),
                'res_uuid' =>  in_array($request->params, [ 'info']) ? 'nullable' :  'required',
                'user_type' =>  in_array($request->params, [ 'info']) ? 'nullable' :  'required',
                'params' => 'required',
                'phone' =>  in_array($request->params, ['update']) ?  [
                    'required',
                    'unique:users,phone,' . $old_user->id,
                    'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/',
                ]: (in_array($request->params, ['info']) ?
                'nullable' : [
                    'required',
                    'unique:users',
                    'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/' ,
                ]),
                'password' =>  in_array($request->params, [ 'info']) ? 'nullable' : (  in_array($request->params, [ 'update']) ? 'required' : 'nullable'),
            ]);
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            if(in_array($request->params, ['update'])){
                $data = $this->user_update($request);
                return $data;
            }
            if(in_array($request->params, ['info'])){
                $data = $this->user_info($request->uuid);
                return $data;
            };
            if(in_array($request->params, ['create'])){
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
    }
    public function user_update(Request $request)
    {
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


    public function restaurant_user_list(Request $request){
        $validateUser = Validator::make($request->all(), [
                'user_uuid' =>  'required|exists:users,uuid',
                'rest_uuid' => 'required|exists:restaurants,uuid',
            ]);
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
        $user= User::where('uuid', $request->user_uuid)->first();
        if (!empty($user) && $user->status == 'active' && $user->user_type == 'admin'  or $user->user_type == 'super_admin') {
            $user_staf = User::where('res_uuid', $request->rest_uuid)->get();
            return response()->json([
                'status' => true,
                'message' => 'User Info',
                'data' => $user_staf
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found'
            ], 404);
        }
    }






    public function guest_register(Request $request){
        if(in_array($request->params, ['update', 'info'])){
            $old_guest = GuestInformaion::where('uuid', $request->uuid)->first();
        }
        $current_date = \Carbon\Carbon::now()->format('Y-m-d');
        $count = 0;
        $one_time_password = $request->first_name[0].$request->last_name[1].rand(1000,9999);
        if(in_array($request->params, ['create'])){
            $old_guest_active = GuestInformaion::where([
                ['phone', $request->phone],
                ])->orWhere([
                    ['email', $request->email],
                    ])->first();


            $phone_guest_active = GuestInformaion::where([
                        ['phone', $request->phone],
                        ])->first();
                if(!empty($old_guest_active)){

                    if($old_guest_active->status == 'inactive' && $request->register_type=='register'){

                        $email_send_history = EmailSendValidation::where('email', $old_guest_active->email)->get();
                            if( $email_send_history->count() >= 0){
                            foreach ($email_send_history as $key => $value) {


                                if(\Carbon\Carbon::parse($value->created_at)->format('Y-m-d') == $current_date){
                                    $count++;
                                }

                             }
                                if($count < 3){
                                    $this->sendEmail($old_guest_active,'Activate Your Account');
                                    EmailSendValidation::create([
                                        'email' => $old_guest_active->email,
                                        'limit' => 1,
                                        'status'=>'success',
                                    ]);
                                }else{
                                    EmailSendValidation::create([
                                        'email' => $old_guest_active->email,
                                        'limit' => 1,
                                        'status'=>'failed',
                                    ]);

                                    return response()->json([
                                        'status' => true,
                                        'message' => 'Sorry! You have exceeded the limit'
                                    ], 200);
                                }
                            }
                     }else{


                     }


                     if(!empty($phone_guest_active)){
                        return response()->json([
                            'status' => true,
                            'message' => 'This phone number already exists.',
                            'data' => $old_guest_active
                        ], 200);
                     }elseif($old_guest_active ->status == 'active' ){
                        return response()->json([
                            'status' => true,
                            'message' => 'This is already a registered user.',
                            'data' => $old_guest_active
                        ], 200);

                     }else{
                        return response()->json([
                            'status' => false,
                            'message' => 'This is already a registered user.',
                            'data' => $old_guest_active
                        ], 200);
                     }

            }
        }
        $validateUser = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' =>  in_array($request->params, ['update']) ?  [
                'required',
                'unique:guest_informaions,phone,' . $old_guest->id,
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/',
            ]: (in_array($request->params, ['info']) ?
            'nullable' : [
                'required',
                'unique:guest_informaions',
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/' ,
            ]),
            'email' => in_array($request->params, ['update']) ?
                'required|email|unique:guest_informaions,email,' . $old_guest->id :
                (in_array($request->params, ['info']) ?
                'nullable' :'required|email|unique:guest_informaions'),
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'post_code' => 'nullable|string',
            'params' => 'required',
            'register_type' => 'required',
            'password' =>  in_array($request->params, [ 'info']) ? 'nullable' : (  in_array($request->params, [ 'update']) ? 'required' : 'nullable'),

        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        };
        if (in_array($request->params, ['update'])) {
            $old_guest->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'post_code' => $request->post_code,
                'password' => Hash::make($request->password),
                'status' => $request->password ?   $old_guest ->status == 'active' ? 'active' : 'active' : 'active'
            ]);
            return response()->json([
                'status' => true,
                'message' => 'This is already a registered user',
            ], 200);

        }
        if (in_array($request->params, ['create'])) {
            $user = GuestInformaion::where('email', $request->email)->orWhere('phone', $request->phone)->first();
            if (!empty($user)) {
                $user->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'address' => $request->address,
                    'city' => $request->city,
                    'country' => $request->country,
                    'post_code' => $request->post_code,
                    'password' =>  $request->register_type=='register' ? Hash::make($request->password) : Hash::make($one_time_password),
                    'status' => $user ->status ?   $user ->status == 'active' ? 'active' : 'inactive' : 'inactive'
                ]);
                 if($user->status == 'inactive' && $request->register_type=='register'){

                    $email_send_history = EmailSendValidation::where('email', $user->email)->get();
                        if( $email_send_history->count() >= 0){

                        foreach ($email_send_history as $key => $value) {
                            if(\Carbon\Carbon::parse($value->created_at)->format('Y-m-d') == $current_date){
                                $count++;
                            }

                         }
                            if($count < 1){
                                $this->sendEmail($old_guest_active,'Activate Your Account');
                                EmailSendValidation::create([
                                    'email' => $old_guest_active->email,
                                    'limit' => 1,
                                    'status'=>'success',
                                ]);
                            }else{
                                EmailSendValidation::create([
                                    'email' => $old_guest_active->email,
                                    'limit' => 1,
                                    'status'=>'failed',
                                ]);
                            }
                        }
                 }else{
                    OneTimeOtpStore::create([
                        'email' => $user->email,
                        'otp' => $one_time_password
                    ]);
                 }


            }else{
                $user = GuestInformaion::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'address' => $request->address,
                    'city' => $request->city,
                    'country' => $request->country,
                    'post_code' => $request->post_code,
                    'password' =>  $request->register_type =='register' ? Hash::make($request->password) : Hash::make($one_time_password),
                    'status' =>  'inactive'
                ]);

                if($user->status == 'inactive' && $request->register_type=='register'){
                    $current_date = date('Y-m-d');
                    $email_send_history = EmailSendValidation::where('email', $user->email)->get();
                        if( $email_send_history->count() >= 0 ){

                            foreach ($email_send_history as $key => $value) {
                                if(\Carbon\Carbon::parse($value->created_at)->format('Y-m-d') == $current_date){
                                    $count++;
                                }
                            }

                        }
                        if($count < 1){
                            $this->sendEmail($user,'Activate Your Account');
                            EmailSendValidation::create([
                                'email' => $user->email,
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
                    }else{
                        OneTimeOtpStore::create([
                            'email' => $user->email,
                            'otp' => $one_time_password
                        ]);

                    }

            }

        }
        if(in_array($request->params, ['info'])){
            $data = $this->profile($request->uuid);
            return $data;
        }
        return response()->json([
            'status' => true,
            'message' => 'Register Successfully',
            'data' => $request->params == 'create' ? $user : $old_guest
        ], 200);
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


public function gest_list(){

    $user = GuestInformaion::orderBy('id', 'desc')->with('guest_reservations')->get();
    if (!empty($user)) {
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


public function forget_password(Request $request){
    $validateUser = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);
    if($validateUser->fails()){
        return response()->json([
            'status' => false,
            'message' => 'validation error',
            'errors' => $validateUser->errors()
        ], 401);
    }


    $current_date = \Carbon\Carbon::now()->format('Y-m-d');
    $count = 0;
    $user = GuestInformaion::where('email', $request->email)->first();
      // Generate OTP and store in cache
    $otp = rand(100000, 999999);
    if (!empty($user)) {
            $email_send_history = EmailSendValidation::where('email', $user->email)->get();
                if( $email_send_history->count() >= 0){
                foreach ($email_send_history as $key => $value) {
                    if(\Carbon\Carbon::parse($value->created_at)->format('Y-m-d') == $current_date){
                        $count++;
                    }
                 }
                    if($count < 1){
                        $this->sendEmailForgetPassword($request,'Forgot password code',$otp);
                        EmailSendValidation::create([
                            'email' => $user->email,
                            'limit' => 1,
                            'status'=>'success',
                        ]);

                        OneTimeOtpStore::create([
                            'email' => $user->email,
                            'otp' => $otp
                        ]);
                    }else{
                        EmailSendValidation::create([
                            'email' => $user->email,
                            'limit' => 1,
                            'status'=>'failed',
                        ]);
                        return response()->json([
                            'status' => true,
                            'message' => 'Sorry! You have exceeded the limit'
                        ], 200);
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'OTP send on your email'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User Not Found'
                ], 404);
            }
   }
public function resend_email(Request $request){
    $validateUser = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);
    if($validateUser->fails()){
        return response()->json([
            'status' => false,
            'message' => 'validation error',
            'errors' => $validateUser->errors()
        ], 401);
    }


    $current_date = \Carbon\Carbon::now()->format('Y-m-d');
    $count = 0;
    $user = GuestInformaion::where('email', $request->email)->first();
    $one_time_password = OneTimeOtpStore::where('email', $request->email)->orderBy('id', 'desc')->first();
      // Generate OTP and store in cache

          if (!empty($user) && $user->status != 'active') {
            $email_send_history = EmailSendValidation::where('email', $user->email)->get();
                if( $email_send_history->count() >= 0){
                foreach ($email_send_history as $key => $value) {
                    if(\Carbon\Carbon::parse($value->created_at)->format('Y-m-d') == $current_date){
                        $count++;
                    }
                 }
                    if($count < 100){
                        $this->resendEmail($user,'Activate Your Account',$one_time_password->otp);
                        EmailSendValidation::create([
                            'email' => $user->email,
                            'limit' => 1,
                            'status'=>'success',
                        ]);
                    }else{
                        EmailSendValidation::create([
                            'email' => $user->email,
                            'limit' => 1,
                            'status'=>'failed',
                        ]);
                        return response()->json([
                            'status' => false,
                            'message' => 'Sorry! You have exceeded the limit'
                        ], 200);
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'OTP send on your email'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User Not Found'
                ], 404);
            }
   }


   public function verify_otp(Request $request){

    $validateUser = Validator::make($request->all(), [
        'email' => 'required|email',
        'otp' => 'required',
    ]);
    if($validateUser->fails()){
        return response()->json([
            'status' => false,
            'message' => 'validation error',
            'errors' => $validateUser->errors()
        ], 401);
    }
    $user = GuestInformaion::where('email', $request->email)->first();
    $otp =  OneTimeOtpStore::where([
        ['email' , $request->email],
        ['otp' , $request->otp],
        ['status' , 'active'],
       ])->orderBy('id','desc')->first();



    if (!empty($user) && !empty($otp) && $request->otp == $otp->otp) {
        OneTimeOtpStore::where([
            ['email' , $request->email],
            ['otp' , $request->otp],
            ['status' , 'active'],
           ])->update([
            'status' => 'used',
           ]);
        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully'
        ], 200);
    } else {
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP'
        ], 401);
    }
   }


   public function password_update(Request $request){
    $validateUser = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);
    if($validateUser->fails()){
        return response()->json([
            'status' => false,
            'message' => 'validation error',
            'errors' => $validateUser->errors()
        ], 401);
    }
    $user = GuestInformaion::where('email', $request->email)->first();
    if (!empty($user)) {
        $user->password = Hash::make($request->password);
    $user->save();
    return response()->json([
        'status' => true,
        'message' => 'Password reset successfully'
    ], 200);
    }
     else {
        return response()->json([
            'status' => false,
            'message' => 'User Not Found'
        ], 404);
     }
    }
   public function contact_us(Request $request){
    $validateUser = Validator::make($request->all(), [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email',
        'phone' => 'required',
        'post_code' => 'required',
        'restaurant_name' => 'required',
        'message' =>  $request->params == 'contact_us' ? 'required' : 'nullable',
        'params' => 'required',
    ]);
    if($validateUser->fails()){
        return response()->json([
            'status' => false,
            'message' => 'validation error',
            'errors' => $validateUser->errors()
        ], 401);
    }


    $data_insert = Enquiry::create([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'post_code' => $request->post_code,
        'restaurant_name' => $request->restaurant_name,
        'message' => $request->message,
        'params' => $request->params,
    ]);
    if($data_insert && $request->params == 'contact_us'){
     $data = $this->sendEmailForEnquiry($request,'Inquiry');
    }

    if($data_insert){
        return response()->json([
            'status' => true,
            'message' => 'Inquiry send successfully',
         ], 200);
    }
     else {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong'
        ], 404);
     }
    }


}
