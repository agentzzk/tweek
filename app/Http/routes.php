<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//public routes
Route::get('/', function () {
   if(Auth::check()) {
       return Redirect::to('/app');
   }
   return view('welcome');
});

//twitter auth
Route::auth();
Route::get('/login', 'Auth\AuthController@userLogin')->name('login');
Route::get('/login/callback', 'Auth\AuthController@redirectAfterLogin');
Route::get('/login/callback/auth', 'Auth\AuthController@authUser')->name('auth');

//application - routes only accessible if user is authenticated
Route::group(['prefix' => '/app', 'middleware' => 'auth'], function () {
    Route::get('/', 'UserController@home')->name('app.home');
    Route::get('/testing', 'UserController@testing');

    Route::resource('/sub', 'SubController');
    Route::post('/sub/addSub', 'SubController@store')->name('add.sub');
    Route::get('/sub/deletesub/{sub}', 'SubController@delete')->name('delete.sub');
});
