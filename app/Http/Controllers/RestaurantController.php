<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\FloorArea;
use App\Models\Restaurent;
use App\Models\Slot;
use App\Models\TableMaster;
use App\Models\LabelTaq;
use App\Models\AboutTaq;
use Auth;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;


class RestaurantController extends Controller
{
     public function floor_area_create(Request $request){

        $validateUser = Validator::make($request->all(), [
            'name' => 'required',
            'rest_uuid' => 'required',

        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();


        if(!empty($rest_data)){
            $data = FloorArea::create([
                'name' => $request->name,
                'restaurant_id' => $rest_data->id,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
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

     public function floor_area_update(Request $request){
        $validateUser = Validator::make($request->all(), [
            'name' => 'required',
            'rest_uuid' => 'required',
            'uuid' => 'required',

        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();
        if(!empty($rest_data)){
           FloorArea::where('uuid', $request->uuid)->update([
                'name' => $request->name,
                'restaurant_id' => $rest_data->id,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully',
                'data' =>FloorArea::where('uuid', $request->uuid)->first()
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
                'data' => []
            ], 200);
        }
     }


     public function floor_area_info( $uuid){
        $rest =  Restaurent::where('uuid', $uuid)->first();
        $data = FloorArea::where('restaurant_id', $rest->id)->get();
        return response()->json([
            'status' => true,
            'message' => 'User Info',
            'data' => $data
        ], 200);
     }

     public function floor_area_delete($uuid){
        FloorArea::where('uuid', $uuid)->delete();
        return response()->json([
            'status' => true,
            'message' => 'User Deleted Successfully',
            'data' => []
        ], 200);
     }

     public function slot_create(Request $request){
        $validateUser = Validator::make($request->all(), [
            'day' => 'required',
            'rest_uuid' => 'required',
            'slot_start' => 'required',
            'slot_end' => 'required',
            'status' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();
        if(!empty($rest_data)){
            $old = Slot::where('day', $request->day)->where('restaurant_id', $rest_data->id)->first();
            if(!empty($old)){
                 $old->update([
                    'history' => null,
                 ]);
                 $old->update([
                    'history' => json_encode($old),
                 ]);
                 $old->update([
                    'slot_start' => $request->slot_start,
                    'slot_end' => $request->slot_end,
                    'status' => $request->status,
                 ]);
                return response()->json([
                    'status' => true,
                    'message' => 'User Updated Successfully',
                    'data' => $old
                ], 200);
            }else{
                $data = Slot::create([
                    'day' => $request->day,
                    'restaurant_id' => $rest_data->id,
                    'slot_start' => $request->slot_start,
                    'slot_end' => $request->slot_end,
                    'status' => $request->status,
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'User Created Successfully',
                    'data' => $data
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


     public function slot_info( $uuid){
        $rest =  Restaurent::where('uuid', $uuid)->first();
        $data = Slot::where('restaurant_id', $rest->id)->where('status','=','active')->get();
        return response()->json([
            'status' => true,
            'message' => 'User Info',
            'data' => $data
        ], 200);
     }



     public function table_create(Request $request){


        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => 'required',
            'table_id' => 'required',
            'table_name' => 'required',
            'capacity' => 'required',
            'description' => 'max:1200',
            'min_seats' => 'required',
            'max_seats' => 'required',
            'reservation_online' => 'required',
            'floor_uuid' => 'required',
            'status' => 'required',
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();
        $floor = FloorArea::where('uuid', $request->floor_uuid)->first();
        if(!empty($rest_data)){

            $data = TableMaster::create([
                'restaurant_id' => $rest_data->id,
                'table_id' => $request->table_id,
                'table_name' => $request->table_name,
                'capacity' => $request->capacity,
                'description' => $request->description,
                'min_seats' => $request->min_seats,
                'max_seats' => $request->max_seats,
                'reservation_online' => $request->reservation_online,
                'floor_area_id' => $floor->id,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'table Created Successfully',
                'data' => $data
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurent Not Found',
                'data' => []
            ], 200);
        }
     }

     public function table_update(Request $request){

        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => 'required',
            'table_id' => 'required',
            'table_name' => 'required',
            'capacity' => 'required',
            'description' => 'max:1200',
            'min_seats' => 'required',
            'max_seats' => 'required',
            'reservation_online' => 'required',
            'floor_uuid' => 'required',
            'status' => 'required',
            'uuid' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();
        $floor = FloorArea::where('uuid', $request->floor_uuid)->first();

        $edit_data = TableMaster::where('uuid', $request->uuid)->first();
        if(!empty($edit_data)){
            $edit_data->update([
                'restaurant_id' => $rest_data->id,
                'table_id' => $request->table_id,
                'table_name' => $request->table_name,
                'capacity' => $request->capacity,
                'description' => $request->description,
                'min_seats' => $request->min_seats,
                'max_seats' => $request->max_seats,
                'reservation_online' => $request->reservation_online,
                'floor_area_id' => $floor->id,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'table Updated Successfully',
                'data' => $edit_data
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
                'data' => []
            ], 200);
        }
     }

     public function table_info($rest_uuid){
        $rest =  Restaurent::where('uuid', $rest_uuid)->first();
        $data = TableMaster::where('restaurant_id', $rest->id)->get();
        return response()->json([
            'status' => true,
            'message' => 'table Info',
            'data' => $data
        ], 200);
     }


     public function table_delete($uuid){
        $delete =  TableMaster::where('uuid', $uuid)->delete();
        if(!empty($delete)){
        return response()->json([
            'status' => true,
            'message' => 'Table Deleted Successfully',
            'data' => $delete
        ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Table Not Found',
                'data' => []
            ], 200);
        }
    }


    public function label_taq_create(Request $request){
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();
        if(!empty($rest_data)){
            $data=LabelTaq::create([
                'restaurant_id' => $rest_data->id,
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'label created Successfully',
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


    public function label_taq_update(Request $request){
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => 'required',
            'uuid' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();
        $edit_data = LabelTaq::where('uuid', $request->uuid)->first();
        if(!empty($edit_data)){
            $edit_data->update([
                'restaurant_id' => $rest_data->id,
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'label Updated Successfully',
                'data' => $edit_data
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
                'data' => []
            ], 200);
        }
    }

    public function label_taq_info($rest_uuid){
        $rest =  Restaurent::where('uuid', $rest_uuid)->first();
        $data = LabelTaq::where('restaurant_id', $rest->id)->get();
        return response()->json([
            'status' => true,
            'message' => 'label Info',
            'data' => $data
        ], 200);
    }


    public function label_taq_delete($uuid){
        $delete =  LabelTaq::where('uuid', $uuid)->delete();
        if(!empty($delete)){
        return response()->json([
            'status' => true,
            'message' => 'label Deleted Successfully',
            'data' => $delete
        ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'label Not Found',
                'data' => []
            ], 200);
        }
    }











    public function about_taq_create(Request $request){
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();
        if(!empty($rest_data)){
            $data=AboutTaq::create([
                'restaurant_id' => $rest_data->id,
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'about taq created Successfully',
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


    public function about_taq_update(Request $request){
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => 'required',
            'uuid' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurent::where('uuid', $request->rest_uuid)->first();
        $edit_data = AboutTaq::where('uuid', $request->uuid)->first();
        if(!empty($edit_data)){
            $edit_data->update([
                'restaurant_id' => $rest_data->id,
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'about label Updated Successfully',
                'data' => $edit_data
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
                'data' => []
            ], 200);
        }
    }

    public function about_taq_info($rest_uuid){
        $rest =  Restaurent::where('uuid', $rest_uuid)->first();
        $data = AboutTaq::where('restaurant_id', $rest->id)->get();
        return response()->json([
            'status' => true,
            'message' => ' about label Info',
            'data' => $data
        ], 200);
    }


    public function about_taq_delete($uuid){
        $delete =  AboutTaq::where('uuid', $uuid)->delete();
        if(!empty($delete)){
        return response()->json([
            'status' => true,
            'message' => ' about label Deleted Successfully',
            'data' => $delete
        ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => ' about label Not Found',
                'data' => []
            ], 200);
        }
    }
}
