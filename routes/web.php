<?php

use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
    Route::get('/languages', 'HomeController@allLanguages');

    Route::get('/owners', 'UserController@getOwners');
    Route::get('/users', 'UserController@getUsers');
    Route::post('/users/store', 'UserController@store');
    Route::post('/users/update', 'UserController@update');
    Route::delete('/users/{id}', 'UserController@destroy');
    Route::get('/users/{keyword}/search', 'UserController@searchUsers');
    Route::get('/admin-info', 'HomeController@adminInfo');
    Route::get('/roles', 'UserController@getAllRoles');

    Route::get('/beers', 'BeerController@getBeers');
    Route::post('/beers/store', 'BeerController@store');
    Route::post('/beers/{beer}/update', 'BeerController@update');
    Route::delete('/beers/{id}', 'BeerController@destroy');
    Route::get('/beers/{keyword}/search', 'BeerController@searchBeers');


    Route::get('/beer-pouring', 'BeerPouringController@getBeerPhotos');
    Route::post('/beer-pouring/store', 'BeerPouringController@store');
    Route::post('/beer-pouring/{beer}/update', 'BeerPouringController@update');
    Route::delete('/beer-pouring/{id}', 'BeerPouringController@destroy');
    Route::get('/beer-pouring/{keyword}/search', 'BeerPouringController@searchBeerPouringTutorial');

});
