<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Slot;
use App\Models\Category;
use App\Models\Reservation;
use App\Models\TableMaster;
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

    // ***************************************************** Restaurant ***********************************************



    public function restaurant_create(Request $request){
            $validateUser = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:restaurants',
            'phone' => [
                'required',
                'unique:restaurants',
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/', // Example regex for phone validation, adjust as necessary
            ],
            'address' => 'required',
            'post_code' => 'required',
            'created_by' => 'required',
            'website' => 'nullable|url',
            'avatar' => 'image|mimes:jpeg,png,jpg,svg|max:2048', // Validation rules for image
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validateUser->errors()
            ], 422);
        }


        $restaurant = Restaurant::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'post_code' => $request['post_code'],
            'avatar' => $request->hasFile('avatar') ? $this->verifyAndUpload('avatar',$request['avatar'], null, null) : null,
            'description' => $request['description'],
            'category' => $request['category'],
            'restaurant_id' => '123',
            'created_by' => $request['created_by'],
            'website' => $request['website'],
            'status' => 'active',
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Restaurant Created Successfully',
            'data' => $restaurant
        ], 200);

    }

    public function restaurant_update(Request $request){
        $validateUser = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:restaurants,email,' . $request->id, // Allow updating current restaurant
            'phone' => [
                'required',
                'unique:restaurants,phone,' . $request->id, // Allow updating current restaurant
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/', // Example regex for phone validation, adjust as necessary
            ],
            'address' => 'required',
            'post_code' => 'required',
            'updated_by' => 'required',
            'website' => 'nullable|url',
            'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validation rules for image
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validateUser->errors()
            ], 422);
        }
        // Find the restaurant to update
        $restaurant = Restaurant::find($request->id);
        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }
        // Update the restaurant with validated data
        $restaurant->name = $request->name;
        $restaurant->email = $request->email;
        $restaurant->phone = $request->phone;
        $restaurant->description = $request->description;
        $restaurant->category = $request->category;
        $restaurant->address = $request->address;
        $restaurant->post_code = $request->post_code;
        $restaurant->website = $request->website;
        $restaurant->updated_by = $request->updated_by;
        if($request->hasFile('avatar')){
            $restaurant->avatar =  $this->updateImage('avatar',$request->avatar,  $restaurant->avatar,null, null);
        }
        $restaurant->save();
        return response()->json([
            'status' => true,
            'message' => 'Restaurant updated successfully',
            'data' => $restaurant
        ], 200);
    }


    public function restaurant_info($uuid){
        $user = User::where('uuid', $uuid)->first();
        if(!empty($user) && $user->user_type == 'super_admin'){
            $restaurant = Restaurant::orderBy('id', 'desc')->get();
            if (empty($restaurant)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'data' => $restaurant
            ], 200);

        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
        if(!empty($user) && $user->user_type != 'super_admin'){
            $restaurant = Restaurant::where('uuid',$user->res_uuid)->get();
            if (empty($restaurant)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'data' => $restaurant
            ], 200);

        }else{
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
    }




    public function category(){
        $category = Category::orderBy('id', 'desc')->with('restaurants')->select(['id','name'])->get();
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

    public function restaurant_list(Request $request){

        $perPage = $request->input('per_page', 10);
        $restaurant = Restaurant::orderBy('id', 'desc')->with('category_list','aval_slots','label_tags','about_label_tags')->where('status', 'active')->select(['id','uuid','restaurant_id','name','address','phone','email','category','description','post_code','status','avatar','website','online_order'])->paginate($perPage);
        if ($restaurant->count() == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $restaurant
        ], 200);
    }
    public function restaurant_search_list(Request $request){
        $perPage = $request->input('per_page', 10);
        $name = $request->input('name');
        $postCode = $request->input('post_code');
        $query  = Restaurant::orderBy('id', 'desc')->with('category_list','aval_slots','label_tags','about_label_tags')->where('status', 'active')->select(['id','uuid','restaurant_id','name','address','phone','email','category','description','post_code','status','avatar','website','online_order']);
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($postCode) {
            $query->where('post_code', 'like', '%' . $postCode . '%');
        }
        // if(!$name && !$postCode){
        //     return response()->json([
        //         'status' => false,
        //         'data' => [],
        //         'pagination' => [
        //             'total' => 0,
        //             'per_page' => $perPage,
        //             'current_page' => 1,
        //             'last_page' => 1,
        //             'next_page_url' => null,
        //             'prev_page_url' => null,
        //         ]
        //     ], 200);
        // }
        $restaurants = $query->paginate($perPage);
        if ($restaurants->count() == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $restaurants
        ], 200);
    }





    public function restaurant_single_info(Request $request,$uuid){

        $restaurant =  Restaurant::where('uuid', $uuid)->with('category_list','aval_slots','label_tags','about_label_tags')->where('status', 'active')->select(['id','uuid','restaurant_id','name','address','phone','email','category','description','post_code','status','avatar','website','online_order'])->first();
        if (empty($restaurant)) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }


        $validateUser = Validator::make($request->all(), [

            'start_time' => 'string',
            'end_time' => 'string',
            'date' => 'string',
            'day'=>'string',

        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $tabledata = Reservation::where([
            ['day', '=', $request->day],
            ['reservation_date', '=', $request->date],
            ['restaurant_id', '=', $restaurant->id]
           ])
           ->whereNotIn('status', ['cancelled', 'completed'])
           ->get();

           $allTables = TableMaster::where('restaurant_id', $restaurant->id)->get();
            if (count($tabledata) > 0 && count($allTables) > 0) {
                $reservedTableIds = $tabledata->pluck('table_master_id')->toArray();
                $availableTables = $allTables->filter(function ($table) use ($reservedTableIds) {
                    return !in_array($table->id, $reservedTableIds);
                })->values();
            } else {
                $availableTables = $allTables;
            }

            if(count($availableTables) > 0){
                $availableSlots = Slot::where('restaurant_id', $restaurant->id)->where('day',$request->day)->where('status','active')->select([
                    'interval_time' ])->first();

                    if(!empty($availableSlots)){
                        $availableSlots = $restaurant->getAvailableSlots($request->day , $request->date ,$availableSlots->interval_time);
                    }
                    return response()->json([
                        'status' => true,
                        'data' => $restaurant,
                        'available_slots' => $availableSlots != null ? $availableSlots: [] ,
                    ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'No Tables Available'
                ], 404);
            }
    }
}
