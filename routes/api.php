<?php

use Illuminate\Http\Request;

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

Route::namespace('Api')->middleware('auth:api')->group(function(){
    Route::get('posts', 'PostController@index');
    Route::get('posts/{id}', 'PostController@show');
    Route::post('posts', 'PostController@store');
    Route::put('posts/{id}', 'PostController@update');
    Route::delete('posts/{id}', 'PostController@destroy');
});
