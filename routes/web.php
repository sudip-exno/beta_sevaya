<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
Route::get('/', function () {
    return view('welcome');
}); 
*/
// Route::get('login','PagesController@login')->name('login');

Route::post('login_validation','Auth\PagesController@login_validation');

Route::get('/', 'PagesController@index')->name('index');

Route::get('/about', 'PagesController@about')->name('about');

Route::post('/city-list', 'PagesController@getCityList')->name('city-list');

Route::get('/blog', 'PagesController@blog')->name('blog');

Route::get('/services', 'PagesController@services')->name('services');

Route::get('/seller-registration', 'RegisterSellerController@seller_registration')->name('seller_registration')->middleware('guest:sellerweb','guest:buyerweb');

Route::get('/seller-login', 'RegisterSellerController@seller_login')->name('seller_login')->middleware('guest:sellerweb','guest:buyerweb');
Route::get('/buyer-login', 'RegisterBuyerController@buyer_login')->name('buyer_login')->middleware('guest:sellerweb','guest:buyerweb');

Route::post('/seller_login_post', 'RegisterSellerController@seller_login_post')->name('seller_login_post')->middleware('guest:sellerweb','guest:buyerweb');
Route::post('/buyer_login_post', 'RegisterBuyerController@buyer_login_post')->name('buyer_login_post')->middleware('guest:sellerweb','guest:buyerweb');

Route::post('/seller_registration_post', 'RegisterSellerController@seller_registration_post')->name('seller_registration_post')->middleware('guest:sellerweb','guest:buyerweb');
Route::post('/seller_registration_in_post', 'RegisterSellerController@seller_registration_in_post')->name('seller_registration_in_post')->middleware('guest:sellerweb','guest:buyerweb');

Route::get('/buyer-registration', 'RegisterBuyerController@buyer_registration')->name('buyer_registration')->middleware('guest:sellerweb','guest:buyerweb');

Route::post('/buyer_registration_post', 'RegisterBuyerController@buyer_registration_post')->name('buyer_registration_post')->middleware('guest:sellerweb','guest:buyerweb');
Route::post('/buyer_registration_in_post', 'RegisterBuyerController@buyer_registration_in_post')->name('buyer_registration_in_post')->middleware('guest:sellerweb','guest:buyerweb');

Route::get('activate-seller-account/{token}', 'RegisterSellerController@get_activate_account')->name('activate-seller-account')->middleware('guest:sellerweb','guest:buyerweb');
Route::get('activate-buyer-account/{token}', 'RegisterBuyerController@get_activate_account')->name('activate-buyer-account')->middleware('guest:sellerweb','guest:buyerweb');
Route::get('seller-logout', 'RegisterSellerController@getLogout')->name('seller-logout')->middleware('auth:sellerweb');
Route::get('buyer-logout', 'RegisterBuyerController@getLogout')->name('buyer-logout')->middleware('auth:buyerweb');


Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => 'auth:sellerweb'], function () {
	Route::get('seller-profile', 'SellerProfileController@getSellerProfile')->name('seller_profile');
	Route::post('/seller_post_profile', 'SellerProfileController@postProfile')->name('seller_post_profile');
	Route::get('seller-rate', 'SellerProfileController@getSellerRate')->name('seller_rate');
	Route::get('add-seller-rate', 'SellerProfileController@getAddSellerRate')->name('add_seller_rate');
	Route::post('post-seller-rate', 'SellerProfileController@postAddSellerRate')->name('seller_post_rate');
	Route::get('seller-rate-update', 'SellerProfileController@getUpdateSellerRate')->name('sellerrateupdate');
	Route::post('seller-rate-update-post/{id}', 'SellerProfileController@postUpdateSellerRate')->name('seller_post_rate_update');
	Route::get('seller-portfolio', 'SellerProfileController@getSellerPortfolio')->name('seller-portfolio');
	Route::get('seller-portfolio-add', 'SellerProfileController@getPortfolioAdd')->name('seller-portfolio-add');
	Route::post('portfolio_form_post', 'SellerProfileController@postPortfolioAdd')->name('portfolio_form_post');
	Route::post('seller_tag_add', 'SellerProfileController@postTagAdd')->name('seller_tag_add');
	Route::get('seller-portfolio-delete/{id}', 'SellerProfileController@getPortfolioDelete')->name('seller-portfolio-delete');
	Route::get('seller-tag-delete/{id}', 'SellerProfileController@getTagDelete')->name('seller-tag-delete');

	Route::get('seller-tags', 'SellerProfileController@getSellerTags')->name('seller-tags');
});

Route::group(['middleware' => 'auth:buyerweb'], function () {
	Route::get('buyer-profile', 'BuyerProfileController@getBuyerProfile')->name('buyer_profile');
	Route::post('/buyer_post_profile', 'BuyerProfileController@postProfile')->name('buyer_post_profile');
});