<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Models\Buyer;
use App\Mail\ActivateBuyerAccount;
use Validator;
use DB;
use Mail;

class RegisterBuyerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */


    public function buyer_login()
    {
       return view('pages.buyer_login');
    }
    
    public function buyer_login_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect(route('buyer_login'))
                        ->withErrors($validator)
                        ->withInput();
        }
        $credentials = $request->only('email', 'password');

        if (Auth::guard('buyerweb')->attempt($credentials,false)) {
            //
            return redirect(route('buyer_profile'));
        } else {
            $validator->errors()->add('email', 'You have enter wrong email or password!');
            return redirect(route('buyer_login'))
                        ->withErrors($validator)
                        ->withInput();
        }

    }

    /**
     * Function for buyer registration get method
     */
    public function buyer_registration()
    {
        return view('pages.buyer_registration');
    }

    /**
     * Function for buyer registration post method
     * @return json
     */
    public function buyer_registration_post(Request $request)
    {
		
		$registration_date = date('Y-m-d');

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|max:120|unique:buyer',
            'mobile' => 'required',
            'password' => 'required|confirmed|min:6'
        ]);

        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $mobile = $request->input('mobile');
        $business_name = $request->input('business_name');
        $password = $request->input('password');

        $activation_token = str_random(25);

        $model = new Buyer;
        $model->buyer_code = "";
        $model->fname = $first_name;
        $model->lname = $last_name;
        $model->email = $email;
        $model->password = Hash::make($password);
        $model->mobile = $mobile;
        $model->business_name = $business_name;
        $model->buyer_type = 'Business';
        $model->registration_date = $registration_date;
        $model->activation_code = $activation_token;
        $model->buyer_status = '0';

        $model->save();

        $user_code = 'B'. substr(strtoupper($first_name), 0, 1) . sprintf("%06d", $model->buyer_id);

        $model->buyer_code = $user_code;

        $model->save();

        $activation_url = route('activate-buyer-account',['token'=>$activation_token]);
        
        Mail::to($model->email)->send(new ActivateBuyerAccount($model, $activation_url));

        return response()->json([
                                    'type' => 'success',
                                    'msg' => 'Successfully Added'
                                ]);
        
    }

    /**
     * Function for buyer individuals registration post method
     * @return json
     */
    public function buyer_registration_in_post(Request $request)
    {
        
        $registration_date = date('Y-m-d');

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|max:120|unique:buyer',
            'mobile' => 'required',
            'password' => 'required|confirmed|min:6'
        ]);

        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $mobile = $request->input('mobile');
        $password = $request->input('password');

        $activation_token = str_random(25);

        $model = new Buyer;
        $model->buyer_code = "";
        $model->fname = $first_name;
        $model->lname = $last_name;
        $model->email = $email;
        $model->password = Hash::make($password);
        $model->mobile = $mobile;
        $model->buyer_type = 'Individual';
        $model->registration_date = $registration_date;
        $model->activation_code = $activation_token;
        $model->buyer_status = '0';

        $model->save();

        $user_code = 'B'. substr(strtoupper($first_name), 0, 1) . sprintf("%06d", $model->buyer_id);

        $model->buyer_code = $user_code;

        $model->save();

        $activation_url = route('activate-buyer-account',['token'=>$activation_token]);
        
        Mail::to($model->email)->send(new ActivateBuyerAccount($model, $activation_url));

        return response()->json([
                                    'type' => 'success',
                                    'msg' => 'Successfully Added'
                                ]);
        
    }

    /**
     * Function for activate seller account
     * @param string $token registration activation token
     */
    public function get_activate_account($token)
    {
        $user = Buyer::where('activation_code', $token)->first();
        $user->activation_code = NULL;
        $user->buyer_status = '1';
        $user->save();
        return redirect(route('buyer_login'));
    }

    /**
     * Function for seller logout
     */
    public function getLogout() {
        Auth::guard('buyerweb')->logout();
        // $request->session()->flush();
        // $request->session()->regenerate();
        return redirect(route('buyer_login'));
    }

}
