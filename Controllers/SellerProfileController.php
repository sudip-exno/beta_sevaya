<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Models\Seller;
use App\Models\SellerRate;
use App\Models\Country;
use App\Models\City;
use App\Models\Service;
use App\Models\Portfolio;
use App\Models\PortfolioFile;
use App\Models\TagMaster;
use App\Models\SellerTag;
use App\Models\TagRelation;
use Validator;
use DB;
use Auth;
use Image;

class SellerProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SellerProfileController
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

	

    /**
     * Function for seller profile get method
     */
    public function getSellerProfile()
    {
        $data = array();
        $data['user'] = Auth::guard('sellerweb')->user();
        $data['countries'] = Country::select('id', 'name')->get()->toArray();

        $country_id = isset(Auth::guard('sellerweb')->user()->country_id) ? Auth::guard('sellerweb')->user()->country_id : "";
        if($country_id != "") {
            $data['cities'] = $x = City::select('id', 'name')->where('country_id', $country_id)->get()->toArray();
        }  else{
            $data['cities'] = [];
        }

        $business_country_id = isset(Auth::guard('sellerweb')->user()->business_country_id) ? Auth::guard('sellerweb')->user()->business_country_id : "";
        if($business_country_id != "") {
            $data['businesscities'] = City::select('id', 'name')->where('country_id', $business_country_id)->get()->toArray();
        } else {
            $data['businesscities'] = [];
        }   

        return view('seller.seller_profile',$data);
    }

	/**
     * Function for seller registration post method
     * @return json
     */
    public function postProfile(Request $request)
    {
        $user = Auth::guard('sellerweb')->user();
        $id = $user->seller_id;

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
            return redirect(route('seller_profile'))
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



        $model = Seller::where('seller_id', $id)->first();
        $model->seller_code = "";
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

        return redirect(route('seller_profile'));
        
    }

    public function getSellerRate()
    {
        $data = array();
        $data['user'] = $user = Auth::guard('sellerweb')->user();

        //$data['service_rate'] = SellerRate::where('seller_id', $user->seller_id)->get();
		$data['service_rate'] = DB::table('seller_rate')
            ->join('service', 'seller_rate.service_id', '=', 'service.service_id')
			->where('seller_id', $user->seller_id)
            ->get();

        return view('seller.seller_rate_list',$data);
    }

    public function getAddSellerRate()
    {
        $data = array();
        $data['user'] = $user = Auth::guard('sellerweb')->user();

        $data['service'] = Service::where('isdeleted', '<>', '1')->get();
        // $data['service_rate'] = SellerRate::where('seller_id', $user->seller_id)->get();

        return view('seller.seller_rate_add',$data);
    }

    public function postAddSellerRate(Request $request)
    {
        $user = Auth::guard('sellerweb')->user();
        $seller_id = $user->seller_id;

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:service,service_id',
            'rate_type' => 'required',
            'rate' => 'required|numeric',
        ]);

        
        if ($validator->fails()) {
            return redirect(route('add_seller_rate'))
                        ->withErrors($validator)
                        ->withInput();
        }

        $service_id = $request->input('service_id');
        $rate_type = $request->input('rate_type');
        $rate = $request->input('rate');

        $model = new SellerRate;
        $model->seller_id = $seller_id;
        $model->service_id = $service_id;
        $model->rate = $rate;
        $model->rate_type = $rate_type;

        $model->save();

        return redirect(route('seller_rate'));
    }

    public function getUpdateSellerRate()
    {
        $cid = $_GET['cid'];
        $data = array();
        $data['user'] = $user = Auth::guard('sellerweb')->user();

        $data['service'] = Service::where('isdeleted', '<>', '1')->get();

        $data['model'] = SellerRate::where('id', $cid)->first();
        // $data['service_rate'] = SellerRate::where('seller_id', $user->seller_id)->get();

        return view('seller.seller_rate_update',$data);
    }

    public function postUpdateSellerRate(Request $request,$id)
    {
        $user = Auth::guard('sellerweb')->user();
        $seller_id = $user->seller_id;

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:service,service_id',
            'rate_type' => 'required',
            'rate' => 'required|numeric',
        ]);

        
        if ($validator->fails()) {
            return redirect(route('add_seller_rate'))
                        ->withErrors($validator)
                        ->withInput();
        }

        $service_id = $request->input('service_id');
        $rate_type = $request->input('rate_type');
        $rate = $request->input('rate');

        $model = SellerRate::where('id',$id)->first();
        $model->seller_id = $seller_id;
        $model->service_id = $service_id;
        $model->rate = $rate;
        $model->rate_type = $rate_type;

        $model->save();

        return redirect(route('seller_rate'));
    }
	
	public function getSellerPortfolio()
    {
        $data = array();
        $data['user'] = $user = Auth::guard('sellerweb')->user();

        $data['portfolio'] = Portfolio::where('seller_id', $user->seller_id)->get();

        return view('seller.seller_prortfolio',$data);
    }
	
    public function getPortfolioAdd()
    {
        $data = array();

        $data['user'] = $user = Auth::guard('sellerweb')->user();

        return view('seller.portfolioadd',$data);
    }

    public function postPortfolioAdd(Request $request)
    {
        $user = Auth::guard('sellerweb')->user();
        $seller_id = $user->seller_id;

        $validator = Validator::make($request->all(), [
            'portfolio_title' => 'required',
            'description' => 'required',
            'portfolioFiles' => 'sometimes|nullable|image|mimes:jpg,png,jpeg,gif,JPG,PNG,JPEG,GIF,doc,DOC,docx,docx,pdf,PDF',
        ]);

        $fileArr = array();

        

        if ($validator->fails()) {
            return redirect(route('seller-portfolio-add'))
                        ->withErrors($validator)
                        ->withInput();
        }

        $description = $request->input('description');
        $title = $request->input('portfolio_title');

        $model = new Portfolio;
        $model->seller_id = $seller_id;
        $model->title = $title;
        $model->description = $description;
        // $model->rate = $rate;
        // $model->rate_type = $rate_type;

        $model->save();

        if ($request->file('portfolioFiles')) {
            $file = $request->file('portfolioFiles');

            $extention = $file->getClientOriginalExtension();

            $filename = str_random(25) . '.' . $extention;
            $file->move(public_path() . '/uploads/portfolio/', $filename);

            $portfolioFiles = new PortfolioFile();
            $portfolioFiles->portfolio_id = $model->id;
            $portfolioFiles->file_name = $filename;
            $portfolioFiles->file_type = strtolower($extention);
            // $image->status = 1;
            $portfolioFiles->save();
        }

        return redirect(route('seller-portfolio'));
    }

    public function getPortfolioDelete($id)
    {
        Portfolio::where('id', $id)->delete();

        PortfolioFile::where('portfolio_id', $id)->delete();

        return redirect(route('seller-portfolio'));
    }

	public function getSellerTags()
    {
        $data = array();
        $data['user'] = $user = Auth::guard('sellerweb')->user();

        $allTag = TagMaster::where('tag_status',1)->get();
        $sellerTag = SellerTag::where('seller_id',$user->seller_id)->get();

        $sellerTagCheck = array();
        $sellerTagGroupCheck = array();

        foreach ($sellerTag as $key1 => $value) {
           array_push($sellerTagCheck, $value->tag_id);
           if(isset($value->getTagRelDet->group_id) && !in_array($value->getTagRelDet->group_id, $sellerTagGroupCheck)) {
                array_push($sellerTagGroupCheck, $value->getTagRelDet->group_id);
           }
        }

        $selectTagArr = array();

        foreach ($allTag as $key => $value) {
            if(!in_array($value->tag_id, $sellerTagCheck)) {
                $selectTagArr[$key]['id'] = $value->tag_id;
                $selectTagArr[$key]['tag_name'] = $value->tag_name;
            }
        }

        $suggestTag = TagRelation::whereIn('group_id',$sellerTagGroupCheck)->get();

        $data['sellerTagCheck'] = $sellerTagCheck;
        $data['selectTagArr'] = $selectTagArr;
        $data['sellerTag'] = $sellerTag;
        $data['suggestTag'] = $suggestTag;

        return view('seller.seller_tags',$data);
    }

    public function postTagAdd(Request $request)
    {
        $user = Auth::guard('sellerweb')->user();
        $seller_id = $user->seller_id;

        $validator = Validator::make($request->all(), [
            'tag_id' => 'required|exists:tag_master,tag_id',
        ]);

        
        if ($validator->fails()) {
            return redirect(route('seller-tags'))
                        ->withErrors($validator)
                        ->withInput();
        }

        $tag_id = $request->input('tag_id');

        $model = new SellerTag;
        $model->seller_id = $seller_id;
        $model->tag_id = $tag_id;

        $model->save();

        return redirect(route('seller-tags'));
    }

    public function getTagDelete($id)
    {
        SellerTag::where('seller_tag_id', $id)->delete();

        return redirect(route('seller-tags'));
    }
	
}
