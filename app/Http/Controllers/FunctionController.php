<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuCatergory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Photo;
use App\Models\Restaurant;
use App\Models\Reservation;
use App\Models\GuestInformaion;
use App\Models\Review;
use App\Models\SectionDescription;
use App\Models\TableMaster;
use App\Models\ResMenu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;
use App\Traits\ImageUploadTraits;
class FunctionController extends Controller
{

    use ImageUploadTraits;
    public function menu_catergory()
    {
        $data= MenuCatergory::where('status', 'active')->get();
        return response()->json([
            'status' => true,
            'message' => 'menu catergory',
            'data' => $data
        ]);

    }

    public function menu_create (Request $request)
    {

      $validateUser = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'string|max:1200',
            'halal_name' => 'string|max:120',
            'category_uuid' => 'required|string',
            'rest_uuid' => 'required|string',
            'price' => 'required|numeric',
            'symbol' => 'required|string',
            'status' => 'required|string',
            'global_description' => 'string|max:1200',
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

    $rest_data = Restaurant::where('uuid', $request->rest_uuid)->where('status', 'active')->first();
    if(!empty($rest_data)){
        $menu_catergory = MenuCatergory::where('uuid', $request->category_uuid)->where('status', 'active')->first();
        if(!empty( $request->global_description)){
            $global_descrip = SectionDescription::where('section', 'menu')->where('restaurant_id', $rest_data->id)->first();
            if(!empty($global_descrip)){
                $global_descrip->update([
                    'description' => $request->global_description,

                ]);
            }else{
                SectionDescription::create([
                    'description' => $request->global_description,
                    'section' => 'menu',
                    'restaurant_id' => $rest_data->id,
                ]);
            }
        }
        if(!empty($menu_catergory)){
            $data = ResMenu::create([
                'name' => $request->name,
                'restaurant_id' => $rest_data->id,
                'description' => $request->description,
                'halal_name' => $request->halal_name,
                'menu_catergory_id' => $menu_catergory->id,
                'price' => $request->price,
                'price_symbol' => $request->symbol,
                'status' => $request->status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Menu Created Successfully',
                'data' => $data
            ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Menu Catergory Not Found',
                    'data' => []
                ], 200);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
                'data' => []
            ], 200);
        }
    }

     public function menu_update (Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'uuid' => 'required|string',
            'name' => 'required|string|max:255',
            'description' => 'string|max:1200',
            'halal_name' => 'string|max:120',
            'category_uuid' => 'required|string',
            'rest_uuid' => 'required|string',
            'price' => 'required|numeric',
            'symbol' => 'required|string',
            'status' => 'required|string',
            'global_description' => 'string|max:1200',
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data = Restaurant::where('uuid', $request->rest_uuid)->where('status', 'active')->first();
        if(!empty($rest_data)){
            $menu_catergory = MenuCatergory::where('uuid', $request->category_uuid)->where('status', 'active')->first();
            if(!empty($menu_catergory)){
                $data = ResMenu::where('uuid', $request->uuid)->where('status', 'active')->first();
                if(!empty($data)){
                    $data->name = $request->name;
                    $data->restaurant_id = $rest_data->id;
                    $data->description = $request->description;
                    $data->halal_name = $request->halal_name;
                    $data->menu_catergory_id = $menu_catergory->id;
                    $data->price = $request->price;
                    $data->price_symbol = $request->symbol;
                    $data->status = $request->status;
                    $data->save();
                    if(!empty($request->global_description)){
                        $section_description = SectionDescription::where('section', 'menu')->where('restaurant_id', $rest_data->id)->first();
                        if(!empty($section_description)){
                            $section_description->description = $request->global_description;
                            $section_description->save();
                        }else{
                            SectionDescription::create([
                                'description' => $request->global_description,
                                'section' => 'menu',
                                'restaurant_id' => $rest_data->id,
                            ]);
                        }
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'menu Updated Successfully',
                        'data' => $data
                    ], 200);
                    }else{
                        return response()->json([
                            'status' => false,
                            'message' => 'Menu Not Found',
                            'data' => []
                        ], 200);
                    }
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Menu Catergory Not Found',
                        'data' => []
                    ], 200);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Restaurant Not Found',
                    'data' => []
                ], 200);
            }
    }


    public function menu_info ( $uuid){
        $rest_data = Restaurant::where('uuid', $uuid)->where('status', 'active')->first();
        if (!empty($rest_data)) {
            $menu_items = ResMenu::where('restaurant_id', $rest_data->id)
                ->with(['menu_category', 'menu_description' => function ($query) {
                    $query->where('status', 'active'); // Apply condition to global_description
                }])
                ->where('status', 'active')
                ->get();

            if ($menu_items->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Menu info',
                    'data' => $menu_items
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Menu not found',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found',
            ]);
        }
    }

    public function menu_delete($uuid){
        $data = ResMenu::where('uuid', $uuid)->first();
        if(!empty($data)){
            $data->delete();
            return response()->json([
                'status' => true,
                'message' => 'Menu Deleted Successfully',
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Menu Not Found',
            ], 200);
        }
    }


    public function rest_photo(Request $request){
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => 'required',
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'global_description' => 'nullable|max:255',
            'status'=>'required'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data = Restaurant::where('uuid', $request->rest_uuid)->where('status', 'active')->first();
        if(!empty($rest_data)){
            $data = Photo::create([
                'avatar' => $request->hasFile('avatar') ? $this->verifyAndUpload('avatar',$request['avatar'], null, null) : null,
                'restaurant_id' => $rest_data->id,
                'status'=> $request->status
            ]);

            if(!empty($request->global_description)){
                $section_description = SectionDescription::where('section', 'photo')->where('restaurant_id', $rest_data->id)->first();
                if(!empty($section_description)){
                    $section_description->description = $request->global_description;
                    $section_description->save();
                }else{
                    SectionDescription::create([
                        'description' => $request->global_description,
                        'section' => 'photo',
                        'restaurant_id' => $rest_data->id,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Photo Added Successfully',
                'data' => $data
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
                'data' => []
            ], 200);
        }

    }

    public function rest_photo_update(Request $request){
        $validateUser = Validator::make($request->all(), [
            'uuid' => 'required',
            'global_description' => 'nullable|max:255',
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status'=>'required'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $data = Photo::where('uuid', $request->uuid)->first();
        if(!empty($data)){
            if($request->hasFile('avatar')){
                $data->avatar =  $this->updateImage('avatar',$request->avatar,  $data->avatar,null, null);
            }
            $data->status = $request->status;
            $data->save();
            if(!empty($request->global_description)){
                $section_description = SectionDescription::where('section', 'photo')->where('restaurant_id', $data->restaurant_id)->first();
                if(!empty($section_description)){
                    $section_description->description = $request->global_description;
                    $section_description->save();
                }else{
                    SectionDescription::create([
                        'description' => $request->global_description,
                        'section' => 'menu',
                        'restaurant_id' => $data->restaurant_id,
                    ]);
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Photo Updated Successfully',
                'data' => $data
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Photo Not Found',
                'data' => []
            ], 200);
        }
    }

    public function rest_photo_info($uuid){
        $rest_data = Restaurant::where('uuid', $uuid)->first();
        $data = Photo::where('restaurant_id', $rest_data->id)->with('photo_description')->get();
        if(!empty($data)){
            return response()->json([
                'status' => true,
                'message' => 'Photo info',
                'data' => $data
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Photo not found',
            ]);
        }
    }

    public function rest_photo_delete($uuid){
        $data = Photo::where('uuid', $uuid)->first();
        if(!empty($data)){
             $this->deleteImage($data->avatar);
            $data->delete();
            return response()->json([
                'status' => true,
                'message' => 'Photo Deleted Successfully',
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Photo Not Found',
            ], 200);
        }
    }


    public function review_create(Request $request){
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => 'required',
            'guest_uuid' => 'required',
            'review' => 'required',
            'rating'=>'required:numeric'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
       $rest = Restaurant::where('uuid', $request->rest_uuid)->first();
       $guest = GuestInformaion::where('uuid', $request->guest_uuid)->first();
       if(!empty($rest) && !empty($guest)){
           $data = Review::create([
               'review' => $request->review,
               'restaurant_id' => $rest->id,
               'guest_informaion_id' => $guest->id,
               'rating' => $request->rating,
               'status' => 'inactive'
           ]);
           return response()->json([
               'status' => true,
               'message' => 'Review Added Successfully',
               'data' => $data
           ], 200);
       }else{
           return response()->json([
               'status' => false,
               'message' => 'Restaurant or Guest Not Found',
               'data' => []
           ], 200);
       }
    }

    public function review_update(Request $request){
        $validateUser = Validator::make($request->all(), [
            'uuid' => 'required',
            'status' => 'required'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $data = Review::where('uuid', $request->uuid)->first();
        if(!empty($data)){
            $data->status = $request->status;
            $data->save();
            return response()->json([
                'status' => true,
                'message' => 'Review Updated Successfully',
                'data' => $data
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Review Not Found',
                'data' => []
            ], 200);
        }
    }

    public function review_info($uuid){
        $rest_data = Restaurant::where('uuid', $uuid)->first();
        if(!empty($rest_data)){
        $data = Review::where('restaurant_id', $rest_data->id)->with('guest_informaion','restaurant')->get();
        if(!empty($data)){
            return response()->json([
                'status' => true,
                'message' => 'Review info',
                'data' => $data
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Review not found',
            ]);
        }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
            ]);
        }
    }
}