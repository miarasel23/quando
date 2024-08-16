<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\FloorArea;
use App\Models\Restaurant;
use App\Models\Slot;
use App\Models\TableMaster;
use App\Models\LabelTag;
use App\Models\AboutTag;
use Auth;
use Laravel\Sanctum\PersonalAccessToken;
use  Illuminate\Support\Facades\DB;


class RestaurantController extends Controller
{
     public function floor_area_create(Request $request){

        if(in_array($request->params, ['update', 'info'])){
            $old_floor = FloorArea::where('uuid', $request->uuid)->first();
        }
        $validateUser = Validator::make($request->all(), [
            'name' => in_array($request->params,['update']) ?'required' : (in_array($request->params, ['info']) ? 'nullable':'nullable') ,
            'rest_uuid' =>  in_array($request->params,['update']) ?'required' : (in_array($request->params, ['info']) ? 'nullable':'nullable') ,
            'params' => 'required',
            'uuid' =>  in_array($request->params,['update']) ?'required' : (in_array($request->params, ['info','create']) ? 'nullable':'nullable') ,
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
        if(in_array($request->params, ['update'])){
            $data = $this->floor_area_update($request);
            return $data;
        }elseif(in_array($request->params, ['info'])){
            $data = $this->floor_area_info($request->rest_uuid);
            return $data;
        }elseif(in_array($request->params, ['create'])){
            $data = FloorArea::create([
                'name' => $request->name,
                'restaurant_id' => $rest_data->id,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'data' => $data
            ], 200);
        }elseif(in_array($request->params, ['delete'])){
           $data = $this->floor_area_delete($request->uuid);
           return $data;
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => []
            ], 200);
        }
     }

     public function floor_area_update(Request $request){
        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
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
        $rest =  Restaurant::where('uuid', $uuid)->first();
        if(!empty($rest)){
            $data = FloorArea::where('restaurant_id', $rest->id)->get();
            if(!empty($data)){
                return response()->json([
                    'status' => true,
                    'message' => 'Floor Info',
                    'data' => $data
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Floor Not Found',
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

     public function floor_area_delete($uuid){
        $data=FloorArea::where('uuid', $uuid)->delete();
        if(!empty($data)){
            return response()->json([
                'status' => true,
                'message' => 'Floor Deleted Successfully',
                'data' => [],
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Floor Not Found',
                'data' => []
            ], 200);
        }
     }



     public function slot_create(Request $request){
        $validateUser = Validator::make($request->all(), [
            'day' => 'required',
            'rest_uuid' => 'required',
            'slot_start' => ['required', 'date_format:H:i', 'after_or_equal:00:00', 'before_or_equal:24:00'],
            'slot_end' => [
                'required',
                'date_format:H:i',
                'after_or_equal:00:00',
                'before_or_equal:24:00',
                'different:slot_start',
                function($attribute, $value, $fail) use ($request) {
                    if (strtotime($value) <= strtotime($request->slot_start)) {
                        $fail('The ' . $attribute . ' must be greater than slot_start.');
                    }
                }
            ],
            'interval_time' => 'required|numeric',
            'status' => 'required',
                ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
        if (!empty($rest_data)) {
            $old = Slot::where('day', $request->day)
                       ->where('restaurant_id', $rest_data->id)
                       ->where('slot_start', '<=', $request->slot_end)
                       ->where('slot_end', '>=', $request->slot_start)
                       ->first();



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
                    'interval_time' => $request->interval_time,
                    'status' => $request->status,
                 ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Slot Updated Successfully',
                    'data' => $old
                ], 200);
            }else{
                $data = Slot::create([
                    'day' => $request->day,
                    'restaurant_id' => $rest_data->id,
                    'slot_start' => $request->slot_start,
                    'slot_end' => $request->slot_end,
                    'interval_time' => $request->interval_time,
                    'status' => $request->status,
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Slot Created Successfully',
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
        $rest = Restaurant::where('uuid', $uuid)->first();

        if (!empty($rest)) {
            $data = Slot::where('restaurant_id', $rest->id)
                        ->where('status', 'active')
                        ->orderBy('day')->orderBy('slot_end') // Optional: to ensure slots are ordered by day
                        ->get()
                        ->groupBy('day');  // Group the slots by day

            if ($data->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Slot Info',
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Slot Not Found',
                    'data' => []
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
                'data' => []
            ], 200);
        }
     }


     public function slot_delete($rest_uuid, $day) {
        // Find the restaurant by UUID
        $rest = Restaurant::where('uuid', $rest_uuid)->first();

        if ($rest) {
            // Retrieve the slots for the specific restaurant and day
            $slots = Slot::where('restaurant_id', $rest->id)
                         ->where('day', $day)
                         ->get();

            if ($slots->isNotEmpty()) {
                // Delete the retrieved slots
                Slot::where('restaurant_id', $rest->id)
                    ->where('day', $day)
                    ->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Slots Deleted Successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Slots Not Found'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found'
            ]);
        }
    }
     public function single_slot_delete($uuid) {
            $slots = Slot::where('uuid',$uuid)->first();
            if (!empty($slots)) {
                // Delete the retrieved slots
               $slots->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Slots Deleted Successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Slots Not Found'
                ]);
            }
    }


     public function table_create(Request $request){

        if(in_array($request->params, ['update','info','delete'])){
            $old_table = TableMaster::where('uuid', $request->uuid)->first();
        }
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => in_array($request->params, ['info','delete','update']) ? 'required|exists:restaurants,uuid' : 'nullable',
            'table_id' => in_array($request->params, ['update','create']) ? 'required|numeric' : 'nullable',
            'table_name' => in_array($request->params,  ['update','create']) ? 'required|string' : 'nullable',
            'capacity' => in_array($request->params, ['update','create']) ? 'required|numeric' : 'nullable',
            'description' => in_array($request->params, ['update','create']) ? 'nullable|string' : 'nullable',
            'min_seats' => in_array($request->params, ['update','create']) ? 'required|string' : 'nullable',
            'max_seats' => in_array($request->params,  ['update','create']) ? 'required|string' : 'nullable',
            'reservation_online' => in_array($request->params, ['update','create']) ? 'required|string' : 'nullable',
            'floor_uuid' => in_array($request->params,  ['update','create']) ? 'required|exists:floor_areas,uuid' : 'nullable',
            'params'=>'required|string',
            'uuid'=>in_array($request->params,  ['update']) ? 'required|string' : 'nullable',
            'status' =>in_array($request->params,  ['update','create']) ? 'required|string' : 'nullable',
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
        $floor = FloorArea::where('uuid', $request->floor_uuid)->first();
        if(!empty($rest_data) && !empty($floor)){
            if(in_array($request->params, ['create'])){
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

        }elseif(in_array($request->params, ['update'])){
            $data = $this->table_update($request);
            return $data;
        }elseif(in_array($request->params, ['delete'])){
            $data = $this->table_delete($request->uuid);
            return $data;
        }elseif(in_array($request->params, ['info'])){
            $data = $this->table_info($rest_data->uuid,$floor->id);
            return $data;
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Invalid Request',
                'data' => []
            ], 200);
        }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found or Floor Not Found',
                'data' => []
            ], 200);
        }
     }

     public function table_update(Request $request){

        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
        $floor = FloorArea::where('uuid', $request->floor_uuid)->first();

        $edit_data = TableMaster::where('uuid', $request->uuid)->first();
        if(!empty($edit_data) && !empty($rest_data) && !empty($floor)){
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
                'message' => 'Restaurant Not Found or Floor Not Found or table Not Found',
                'data' => []
            ], 200);
        }
     }

     public function table_info($rest_uuid,$floor_id){
        $rest =  Restaurant::where('uuid', $rest_uuid)->first();

        if(!empty($rest)){
            $data = TableMaster::where('restaurant_id', $rest->id)->where('floor_area_id', $floor_id)->orderBy('id','desc')->get();
            if(!empty($data)){
                return response()->json([
                    'status' => true,
                    'message' => 'table Info',
                    'data' => $data
                ]);
            }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'table Not Found',
                        'data' => []
                    ]);
                }
            }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Restaurant Not Found',
                'data' => []
            ], 200);
        }

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

    public function label_tag_create(Request $request){
        if(in_array($request->params, ['update','delete','info'])){
            $old_label_tag = LabelTag::where('uuid', $request->uuid)->first();
        };
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => in_array($request->params, ['create','update']) ? 'required:exists:restaurants,uuid' : 'nullable',
            'name' => in_array($request->params, ['create','update']) ? 'required' : 'nullable',
            'status' =>in_array($request->params, ['create','update']) ? 'required' : 'nullable',
            'uuid' => in_array($request->params, ['update','delete']) ? 'required:exists:label_tags,uuid' : 'nullable',
            'params'=>'required:string'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
        if(!empty($rest_data)){
            if(in_array($request->params, ['update'])){
                $data = $this->label_tag_update($request);
                return $data;
            }elseif(in_array($request->params, ['create'])){
                $data=LabelTag::create([
                    'restaurant_id' => $rest_data->id,
                    'name' => $request->name,
                    'status' => $request->status,
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'label created Successfully',
                    'data' => $data
                ], 200);
            }elseif(in_array($request->params, ['delete'])){
                $data = $this->label_tag_delete($request->uuid);
                return $data;
            }elseif(in_array($request->params, ['info'])){
                $data = $this->label_tag_info($rest_data->uuid);
                return $data;
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Params',
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


    public function label_tag_update(Request $request){
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
        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
        $edit_data = LabelTag::where('uuid', $request->uuid)->first();
        if(!empty($edit_data) ){
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

    public function label_tag_info($rest_uuid){
        $rest =  Restaurant::where('uuid', $rest_uuid)->first();
        if(!empty($rest)){
            $data = LabelTag::where('restaurant_id', $rest->id)->get();
            if(!empty($data)){
                return response()->json([
                    'status' => true,
                    'message' => 'label Info',
                    'data' => $data
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'label Not Found',
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


    public function label_tag_delete($uuid){
        $delete =  LabelTag::where('uuid', $uuid)->delete();
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











    public function about_tag_create(Request $request){

        if(in_array($request->params, ['update','delete','info'])){
            $data =AboutTag::where('uuid', $request->uuid)->first();
        }
        $validateUser = Validator::make($request->all(), [
            'rest_uuid' => in_array($request->params, ['update','info']) ? 'required:exists:restaurants,uuid' : 'required',
            'name' =>   in_array($request->params, ['update','create']) ? 'required|string' : 'nullable',
            'status' => in_array($request->params, ['update','create']) ? 'required:string' : 'nullable',
            'uuid'=> in_array($request->params, ['update','delete']) ? 'required:exists:about_tags,uuid' : 'nullable',
            'params'=> 'required:string'
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
        if(!empty($rest_data)){


            if($request->params == 'update'){
                $data =$this->about_tag_update($request);
                return $data;
            }elseif($request->params == 'delete'){
                $data = $this->about_tag_delete($request->uuid);
                return $data;
            }elseif($request->params == 'info'){
                $data = $this->about_tag_info($rest_data->uuid);
                return $data;
            }elseif($request->params == 'create'){
                $data=AboutTag::create([
                    'restaurant_id' => $rest_data->id,
                    'name' => $request->name,
                    'status' => $request->status,
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'about tag created Successfully',
                    'data' => $data
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong',
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


    public function about_tag_update(Request $request){
        $rest_data  = Restaurant::where('uuid', $request->rest_uuid)->first();
        $edit_data = AboutTag::where('uuid', $request->uuid)->first();
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

    public function about_tag_info($rest_uuid){
        $rest =  Restaurant::where('uuid', $rest_uuid)->first();
        if(!empty($rest)){
            $data = AboutTag::where('restaurant_id', $rest->id)->get();
            if(!empty($data)){
                return response()->json([
                    'status' => true,
                    'message' => 'about label Info',
                    'data' => $data
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'about label Not Found',
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


    public function about_tag_delete($uuid){
        $delete =  AboutTag::where('uuid', $uuid)->delete();
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
