<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Slot;
use App\Models\FloorArea;
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
        if( in_array($request->params, ['update', 'info'])){
            $old_rest = Restaurant::where('uuid', $request->uuid)->first();
        }
        $validateUser = Validator::make($request->all(), [
            'name' =>  in_array($request->params, ['info']) ?'nullable':'required',
            'email' =>  in_array($request->params, ['update']) ?
            'required|email|unique:restaurants,email,' . $old_rest->id :
            (in_array($request->params, ['info']) ?
            'nullable' :
            'required|email|unique:restaurants'),
            'phone' =>  in_array($request->params, ['update']) ?  [
                'required',
                'unique:restaurants,phone,' . $old_rest->id,
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/',
            ]: (in_array($request->params, ['info']) ?
            'nullable' : [
                'required',
                'unique:restaurants',
                'regex:/^(\+?\d{1,3}[-.\s]?)?\d{10}$/' ,
            ]),
            'address' => in_array($request->params, ['info']) ? 'nullable' : 'required',
            'params' => 'required',
            'post_code' =>  in_array($request->params, ['info']) ? 'nullable' : 'required',
            'created_by' =>   in_array($request->params, ['info','update']) ? 'nullable' : 'required',
            'website' =>  in_array($request->params, ['info']) ? 'nullable': 'nullable|url',
            'avatar' =>  in_array($request->params, ['info']) ? '' :'image|mimes:jpeg,png,jpg,svg|max:2048',
            'uuid' => in_array($request->store_type, ['update', 'info']) ? 'required' : 'nullable',
            'status' =>  in_array($request->params, ['info']) ?'nullable':'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validateUser->errors()
            ], 422);
        }
        if($request->params == 'update'){
            $data =$this->restaurant_update($request);
            return $data;
        }elseif($request->params == 'create'){
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
                'status' => "inactive",
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Restaurant Created Successfully',
                'data' => $restaurant
            ], 200);
        }elseif($request->params == 'info'){
            $data = $this->restaurant_info($request->uuid);
            return $data;
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Invalid store type'
            ], 422);
        }
    }

    public function restaurant_update($request){
        $restaurant = Restaurant::where('uuid', $request->uuid)->first();
        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }
        $restaurant->name = $request->name;
        $restaurant->email = $request->email;
        $restaurant->phone = $request->phone;
        $restaurant->description = $request->description;
        $restaurant->category = $request->category;
        $restaurant->address = $request->address;
        $restaurant->post_code = $request->post_code;
        $restaurant->website = $request->website;
        $restaurant->updated_by = $request->updated_by;
        if($request->status == "inactive"){
            $restaurant->status = $request->status;
        }
        if($request->hasFile('avatar')){
            $restaurant->avatar =  $this->updateImage('avatar',$request->avatar,  $restaurant->avatar,null, null);
        }
        $restaurant->save();
        if($request->status == "active"){
           $floarData = FloorArea::where('restaurant_id', $restaurant->id)->where('status', 'active')->get();
           if(count($floarData) == 0){
            return response()->json([
                'status' => true,
                'message' => 'Restaurant can not be active as it has no floor area',
            ]);
           }
           $tableData = TableMaster::where('restaurant_id', $restaurant->id)->where('status', 'active')->get();
           if(count($tableData) == 0){
            return response()->json([
                'status' => true,
                'message' => 'Restaurant can not be active as it has no table',
            ]);
           }
           $slotsData = Slot::where('restaurant_id', $restaurant->id)->where('status', 'active')->get();
           if(count($slotsData) == 0){
            return response()->json([
                'status' => true,
                'message' => 'Restaurant can not be active as it has no slots',
            ]);
           }

           if( count($floarData) > 0 && count($tableData) > 0 && count($slotsData) > 0){
             $restaurant->update(['status' => 'active']);
           }
        }
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
        $restaurant = Restaurant::orderBy('id', 'desc')->with('category_list','aval_slots','label_tags','about_label_tags','photos','reviews',)->where('status', 'active')->select(['id','uuid','restaurant_id','name','address','phone','email','category','description','post_code','status','avatar','website','online_order'])->paginate($perPage);
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

    public function restaurant_top_review(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $restaurant = Restaurant::with([
                'category_list',
                'aval_slots',
                'label_tags',
                'about_label_tags',
                'photos',
                'reviews'
            ])
            ->select([
                'restaurants.id',
                'restaurants.uuid',
                'restaurants.restaurant_id',
                'restaurants.name',
                'restaurants.address',
                'restaurants.phone',
                'restaurants.email',
                'restaurants.category',
                'restaurants.description',
                'restaurants.post_code',
                'restaurants.status',
                'restaurants.avatar',
                'restaurants.website',
                'restaurants.online_order',
                \DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating')
            ])
            ->leftJoin('reviews', function ($join) {
                $join->on('restaurants.id', '=', 'reviews.restaurant_id')
                    ->where('reviews.status', 'active');
            })
            ->where('restaurants.status', 'active')
            ->groupBy([
                'restaurants.id',
                'restaurants.uuid',
                'restaurants.restaurant_id',
                'restaurants.name',
                'restaurants.address',
                'restaurants.phone',
                'restaurants.email',
                'restaurants.category',
                'restaurants.description',
                'restaurants.post_code',
                'restaurants.status',
                'restaurants.avatar',
                'restaurants.website',
                'restaurants.online_order'
            ])
            ->orderBy('avg_rating', 'desc')
            ->orderBy('restaurants.id', 'desc')
            ->paginate($perPage);

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


    public function restaurant_list_for_admin(Request $request){
        $perPage = $request->input('per_page', 10000);
        $restaurant = Restaurant::orderBy('id', 'desc')->with('category_list','aval_slots','label_tags','about_label_tags','photos','reviews',)->select(['id','uuid','restaurant_id','name','address','phone','email','category','description','post_code','status','avatar','website','online_order'])->paginate($perPage);
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
        $query  = Restaurant::orderBy('id', 'desc')->with('category_list','aval_slots','label_tags','about_label_tags','photos','reviews',)->where('status', 'active')->select(['id','uuid','restaurant_id','name','address','phone','email','category','description','post_code','status','avatar','website','online_order']);
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($postCode) {
            $query->where('post_code', 'like', '%' . $postCode . '%');
        }
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
        $restaurant = Restaurant::where('uuid', $uuid)
        ->with([
            'category_list',
            'label_tags',
            'about_label_tags',
            'menu_description',
            'photo_description',
            'reviews_description',
            'menus.menu_category',
            'photos',
            'reviews',
            'aval_slots' => function ($query) {
                $query->where('status', 'active')
                    ->orderBy('day')
                    ->orderBy('slot_end')
                    ->select(['id', 'uuid', 'restaurant_id', 'day', 'slot_start', 'slot_end']);
            }
        ])
        ->where('status', 'active')
        ->select(['id', 'uuid', 'restaurant_id', 'name', 'address', 'phone', 'email', 'category', 'description', 'post_code', 'status', 'avatar', 'website', 'online_order'])
        ->first();

        if (empty($restaurant)) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }
    $categories = [];
    foreach ($restaurant->menus as $menu) {
        $category = $menu->menu_category;
        if ($category) {
            if (!isset($categories[$category->id])) {
                $categories[$category->id] = [
                    'id' => $category->id,
                    'uuid' => $category->uuid,
                    'name' => $category->name,
                    'status' => $category->status,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                    'menus' => [],
                ];
            }

            $categories[$category->id]['menus'][] = [
                'id' => $menu->id,
                'uuid' => $menu->uuid,
                'name' => $menu->name,
                'description' => $menu->description,
                'halal_name' => $menu->halal_name,
                'restaurant_id' => $menu->restaurant_id,
                'menu_category_id' => $menu->menu_category_id,
                'price' => $menu->price,
                'price_symbol' => $menu->price_symbol,
                'status' => $menu->status,
                'created_at' => $menu->created_at,
                'updated_at' => $menu->updated_at,
            ];
        }
    }

    // Resetting the categories to be an indexed array
    $categories = array_values($categories);

            $data = [
                'id' => $restaurant->id,
                'uuid' => $restaurant->uuid,
                'restaurant_id' => $restaurant->restaurant_id,
                'name' => $restaurant->name,
                'address' => $restaurant->address,
                'phone' => $restaurant->phone,
                'email' => $restaurant->email,
                'category' => $restaurant->category,
                'description' => $restaurant->description,
                'post_code' => $restaurant->post_code,
                'status' => $restaurant->status,
                'avatar' => $restaurant->avatar,
                'website' => $restaurant->website,
                'online_order' => $restaurant->online_order,
                'label_tags' => $restaurant->label_tags,
                'about_label_tags' => $restaurant->about_label_tags,
                'menu_description' => $restaurant->menu_description,
                'photo_description' => $restaurant->photo_description,
                'reviews_description' => $restaurant->reviews_description,
                'photo' => $restaurant->photos,
                'reviews' => $restaurant->reviews,
                'aval_slots' => $restaurant->aval_slots->groupBy('day'),
                'categories' => $categories,
            ];
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
                        'data' => $data,
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
