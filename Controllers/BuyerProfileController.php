<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Models\Buyer;
use App\Models\Country;
use App\Models\City;
use Validator;
use DB;
use Auth;
use Image;

class BuyerProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | BuyerProfileController
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

	

    /**
     * Function for buyer profile get method
     */
    public function getBuyerProfile()
    {
        $data = array();
        $data['user'] = Auth::guard('buyerweb')->user();
        $data['countries'] = Country::select('id', 'name')->get()->toArray();

        $country_id = isset(Auth::guard('buyerweb')->user()->country_id) ? Auth::guard('buyerweb')->user()->country_id : "";
        if($country_id != "") {
            $data['cities'] = $x = City::select('id', 'name')->where('country_id', $country_id)->get()->toArray();
        }  else{
            $data['cities'] = [];
        }

        $business_country_id = isset(Auth::guard('buyerweb')->user()->business_country_id) ? Auth::guard('buyerweb')->user()->business_country_id : "";
        if($business_country_id != "") {
            $data['businesscities'] = City::select('id', 'name')->where('country_id', $business_country_id)->get()->toArray();
        } else {
            $data['businesscities'] = [];
        }   

        return view('buyer.buyer_profile',$data);
    }

	/**
     * Function for buyer registration post method
     * @return json
     */
    public function postProfile(Request $request)
    {
        $user = Auth::guard('buyerweb')->user();
        $id = $user->buyer_id;

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|max:120',
            'mobile' => 'required',
            'zipcode' => 'max:6',
            'address1' => 'max:120',
            'address2' => 'max:120',
            'business_name' => 'max:100',
            'business_address1' => 'max:120',
            'business_address2' => 'max:120',
            'business_zip_code' => 'max:6',
            'profile_title' => 'max:255',
            'profile_description' => 'max:500',
            'expertise' => 'max:500',
            'experience' => 'max:255',
            'profile_picture' => 'sometimes|nullable|image|mimes:jpg,png,jpeg,gif,JPG,PNG,JPEG,GIF',
        ]);

        
        if ($validator->fails()) {
            return redirect(route('buyer_profile'))
                        ->withErrors($validator)
                        ->withInput();
        }

        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $mobile = $request->input('mobile');
        $dob = $request->input('dob')!= NULL && $request->input('dob') !="" ? date("Y-m-d",strtotime($request->input('dob'))) : NULL;
        $country_id = $request->input('country_id') !="" && $request->input('country_id') != NULL ? $request->input('country_id') : NULL;
        $city_id = $request->input('city_id') != NULL && $request->input('city_id') != "" ? $request->input('city_id') : NULL;
        $address1 = $request->input('address1');
        $address2 = $request->input('address2');
        $business_name = $request->input('business_name');
        $business_country_id = $request->input('business_country_id') != NULL && $request->input('business_country_id') != "" ? $request->input('business_country_id') : NULL;
        $business_city_id = $request->input('business_city_id') != "" && $request->input('business_city_id') != NULL ? $request->input('business_city_id') : NULL;
        $business_address1 = $request->input('business_address1');
        $business_address2 = $request->input('business_address2');
        $profile_title = $request->input('profile_title');
        $profile_description = $request->input('profile_description');
        $expertise = $request->input('expertise');
        $experience = $request->input('experience');

        $filename = '';

        if ($request->file('profile_picture')) {
            $selectedImage = '';
            $file = $request->file('profile_picture');

            $filename = str_random(25) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path() . '/uploads/users/original/', $filename);
//                        $thumb_img = Image::make($file->getRealPath())->resize(100, 100);

            $destinationPath = public_path('/uploads/users/');
            $thumb_img = Image::make(public_path() . '/uploads/users/original/' . $filename)->resize(100, 100);
            $thumb_img->save($destinationPath . 'small/' . $filename, 80);
        }



        $model = Buyer::where('buyer_id', $id)->first();
        $model->buyer_code = "";
        $model->fname = $first_name;
        $model->lname = $last_name;
        $model->email = $email;
        $model->mobile = $mobile;
        $model->dob = $dob;
        $model->country_id = $country_id;
        $model->city_id = $city_id;
        $model->address1 = $address1;
        $model->address2 = $address2;
        $model->business_name = $business_name;
        $model->business_country_id = $business_country_id;
        $model->business_city_id = $business_city_id;
        $model->business_address1 = $business_address1;
        $model->business_address2 = $business_address2;
        $model->profile_title = $profile_title;
        $model->profile_description = $profile_description;
        $model->expertise = $expertise;
        $model->experience = $experience;
        $model->profile_pic = $filename;

        $model->save();

        return redirect(route('buyer_profile'));
        
    }
}
