<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuestInformaion;
class DashboardController extends Controller
{
    public function activation_link(Request $request){



        $guest_information = GuestInformaion::where('uuid', $request->uuid)->first();
        if(!empty($guest_information)){
            if($guest_information->status == 'inactive'){
                $guest_information->update(['status' => 'active']);
                 $msg = 'Account Activated Successfully';
                 return view('email_template.account_activataion_message_template',compact('msg'));
            }else{
                $msg = 'Account Already Activated';
                return view('email_template.account_activataion_message_template',compact('msg'));
            }

        }else{
            $msg = 'Something went wrong';
            return view('email_template.account_activataion_message_template',compact('msg'));
        }
    }

}
