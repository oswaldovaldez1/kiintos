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

Route::get('/', function () {
    //return view('error');
    abort(404);
});
Route::group(['namespace'=>'API'], function(){   
    //SERVER_ACTCOUNT
    Route::get('/activar/{key}', 'UserController@activate');
    Route::get('/recuperacion/{pin}','UserController@wrestore');
    Route::post('/recuperacion','UserController@wrestore');          
    
    Route::get('/recuperacion', function (){
        abort(404);
    });              
});
