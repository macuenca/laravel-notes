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

Route::get('/', function () {
    return view('welcome');
});

/**
 * All API endpoints are grouped under the 'api/v<version number>'
 * prefix and protected by the token authentication driver.
 */
Route::group(['prefix' => 'api/v1','middleware' => 'auth:api'], function () {
    Route::resource('notes', 'NoteController');
});

Route::auth();

Route::get('/home', 'HomeController@index');
