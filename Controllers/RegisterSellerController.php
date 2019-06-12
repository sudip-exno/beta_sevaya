<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Models\Seller;
use App\Mail\ActivateSellerAccount;
use Validator;
use DB;
use Mail;

class RegisterSellerController extends Controller
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

	public function seller_login()
    {
       return view('pages.seller_login');
    }
	
	public function seller_login_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect(route('seller_login'))
                        ->withErrors($validator)
                        ->withInput();
        }
        $credentials = $request->only('email', 'password');

        if (Auth::guard('sellerweb')->attempt($credentials,false)) {
            //
            return redirect(route('seller_profile'));
        } else {
            $validator->errors()->add('email', 'You have enter wrong email or password!');
            return redirect(route('seller_login'))
                        ->withErrors($validator)
                        ->withInput();
        }

    }

    /**
     * Function for seller registration get method
     */
    public function seller_registration()
    {
        return view('pages.seller_registration');
    }

	/**
     * Function for seller registration post method
     * @return json
     */
    public function seller_registration_post(Request $request)
    {
		$registration_date = date('Y-m-d');

        $validator = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|max:120|unique:seller',
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

        $model = new Seller;
        $model->seller_code = "";
        $model->fname = $first_name;
        $model->lname = $last_name;
        $model->email = $email;
        $model->password = Hash::make($password);
        $model->mobile = $mobile;
        $model->business_name = $business_name;
        $model->seller_type = 'Business';
        $model->registration_date = $registration_date;
        $model->activation_code = $activation_token;
        $model->seller_status = '0';

        $model->save();

        $user_code = 'S'. substr(strtoupper($first_name), 0, 1) . sprintf("%06d", $model->seller_id);

        $model->seller_code = $user_code;

        $model->save();

        $activation_url = route('activate-seller-account',['token'=>$activation_token]);
        
        Mail::to($model->email)->send(new ActivateSellerAccount($model, $activation_url));

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
        $user = Seller::where('activation_code', $token)->first();
        $user->activation_code = NULL;
        $user->seller_status = '1';
        $user->save();
        return redirect(route('seller_login'));
    }

    /**
     * Function for seller individuals registration post method
     * @return json
     */
    public function seller_registration_in_post(Request $request)
    {		
		$registration_date = date('Y-m-d');

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|max:120|unique:seller',
            'mobile' => 'required',
            'password' => 'required|confirmed|min:6'
        ]);

        $first_name_in = $request->input('first_name');
        $last_name_in = $request->input('last_name');
        $email_in = $request->input('email');
        $mobile_in = $request->input('mobile');
        $pass_in = $request->input('password');

        $activation_token = str_random(25);

        $model = new Seller;
        $model->seller_code = "";
        $model->fname = $first_name_in;
        $model->lname = $last_name_in;
        $model->email = $email_in;
        $model->password = Hash::make($pass_in);
        $model->mobile = $mobile_in;
        $model->seller_type = 'Individual';
        $model->registration_date = $registration_date;
        $model->activation_code = $activation_token;
        $model->seller_status = '0';

        $model->save();

        $user_code = 'S'. substr(strtoupper($first_name_in), 0, 1) . sprintf("%06d", $model->seller_id);

        $model->seller_code = $user_code;

        $model->save();

        $activation_url = route('activate-seller-account',['token'=>$activation_token]);
        
        Mail::to($model->email)->send(new ActivateSellerAccount($model, $activation_url));

        return response()->json([
                                    'type' => 'success',
                                    'msg' => 'Successfully Added'
                                ]);

    }

    /**
     * Function for seller logout
     */
    public function getLogout() {
        Auth::guard("sellerweb")->logout();
        return redirect(route('seller_login'));
    }
}
