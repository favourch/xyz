<?php

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['cors']], function(){
    Route::get('/user', function (Request $request) {
    
    $token = JWTAuth::getToken();
    
    $user = JWTAuth::toUser($token);
    
    return $user;
    
})->middleware(['jwt.auth', 'cors']);



Route::post('/authenticate', 'ApiAuthController@authenticate');


Route::post('/register', ['uses'=> 'ApiAuthController@register']);



//Route::get('/joke', 'JokeController@index')->middleware('jwt.auth');

Route::resource('jokes', 'JokeController');



Route::middleware(['jwt.auth', 'role:owner'])->group(function () {
    Route::get('/dayo', function () {
        echo "i am here ";
    });

    Route::get('user/profile', function () {
        echo "i am inside the profile ";
    });
});

});

