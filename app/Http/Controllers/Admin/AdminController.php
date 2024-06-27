<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurent;
use App\Models\Category;
use Auth;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;
use App\Traits\ImageUploadTraits;


class AdminController extends Controller
{

    use ImageUploadTraits;
    // ********************************************  LOGIN ***********************************************
     public function Login(Request $request){
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
            if(!Auth::attempt($request->only(['email', 'password']))){

                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }
           if(Auth::attempt($request->only(['email', 'password']))){
                $user = Auth::user();
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
            $user = User::where('uuid', $request->uuid)->first();
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

    // ***************************************************** RESTAURENT ***********************************************



    public function restaurent_create(Request $request){
            $validateUser = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:restaurents',
            'phone' => [
                'required',
                'unique:restaurents',
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/', // Example regex for phone validation, adjust as necessary
            ],
            'address' => 'required',
            'post_code' => 'required',
            'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validation rules for image
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validateUser->errors()
            ], 422);
        }


        $restaurent = Restaurent::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'post_code' => $request['post_code'],
            'avatar' => $request->hasFile('avatar') ? $this->verifyAndUpload('avatar',$request['avatar'], null, null) : null,
            'description' => $request['description'],
            'category' => $request['category'],
            'restaurent_id' => '123',
            'status' => 'active',
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Restaurant Created Successfully',
            'data' => $restaurent
        ], 200);

    }

    public function restaurent_update(Request $request){
        $validateUser = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:restaurents,email,' . $request->id, // Allow updating current restaurant
            'phone' => [
                'required',
                'unique:restaurents,phone,' . $request->id, // Allow updating current restaurant
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/', // Example regex for phone validation, adjust as necessary
            ],
            'address' => 'required',
            'post_code' => 'required',
            'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validation rules for image
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validateUser->errors()
            ], 422);
        }
        // Find the restaurant to update
        $restaurent = Restaurent::find($request->id);
        if (!$restaurent) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }
        // Update the restaurant with validated data
        $restaurent->name = $request->name;
        $restaurent->email = $request->email;
        $restaurent->phone = $request->phone;
        $restaurent->description = $request->description;
        $restaurent->category = $request->category;
        $restaurent->address = $request->address;
        $restaurent->post_code = $request->post_code;
        // dd($request->avatar);
        if($request->hasFile('avatar')){
            $restaurent->avatar =  $this->updateImage('avatar',$request->avatar,  $restaurent->avatar,null, null);
        }
        $restaurent->save();
        return response()->json([
            'status' => true,
            'message' => 'Restaurant updated successfully',
            'data' => $restaurent
        ], 200);
    }


    public function restaurent_info($uuid){
        $user = User::where('uuid', $uuid)->first();
        if(!empty($user) && $user->user_type == 'super_admin'){
            $restaurent = Restaurent::orderBy('id', 'desc')->get();
            if (empty($restaurent)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'data' => $restaurent
            ], 200);

        }
        if(!empty($user) && $user->user_type != 'super_admin'){
            $restaurent = Restaurent::where('uuid',$user->res_uuid)->get();
            if (empty($restaurent)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'data' => $restaurent
            ], 200);

        }
    }




    public function category(){
        $category = Category::orderBy('id', 'desc')->get();
        if ($category->count() == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $category
        ], 200);
    }

    public function restaurent_list(){
        $restaurent = Restaurent::orderBy('id', 'desc')->with('category_list')->where('status', 'active')->get();

        // dd($restaurent);
        if ($restaurent->count() == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $restaurent
        ], 200);
    }






}
