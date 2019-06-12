<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;
use App\Models\City;
use Validator;
use DB;

class PagesController extends Controller
{
    public function login(){
    return view('login');
    //return view('welcome');
    }


function login_validation(Request $request)
{
    $buyer = array(
    'email'     => Input::post('email'),
    'password'  => Input::post('password')
    );
    if (Auth::attempt($buyer)) {
    echo 'SUCCESS!';
    } 
    else{
     echo "Failed";
    }
}
    


    public function index(){
        return view('pages.index');
        //return view('welcome');
    }

    public function about(){
        //$title = "About us";
        //echo $title;
        //return view('welcome');
        //return view('pages.about')->with('title',$title);
		return view('pages.about');
    }

    public function blog(){
        		return view('pages.blog');
    }

    public function services(){
        $data = array(
            'title' => 'Services',
            'services' => ['Web Design','Development','SEO']
        );
        //var_dump($data);exit;
       return view('services')->with('data',$data);

    }

    public function getCityList()
    {
        $country_id = Input::post('country_id');
        $cities = City::select('id', 'name')->where('country_id', $country_id)->get()->toArray();
        return response()->json([
                                    'type' => 'success',
                                    'cities' => $cities
                                ]);
    }
    
}
