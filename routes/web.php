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

    Route::get('/beer-qa', 'BeerQAController@getQA');
    Route::post('/beer-qa/store', 'BeerQAController@storeQA');
    Route::post('/beer-qa/{beerQa}/update', 'BeerQAController@updateQA');
    Route::delete('/beer-qa/{id}', 'BeerQAController@destroyQA');
    Route::get('/beer-qa/{keyword}/search', 'BeerQAController@searchQA');

    Route::get('/beer-qa-categories', 'BeerQAController@getBeerQACategories');
    Route::get('/beer-qa-categories/all', 'BeerQAController@getAllBeerQACategories');
    Route::post('/beer-qa-categories/store', 'BeerQAController@storeBeerQACategories');
    Route::post('/beer-qa-categories/{beerQACategory}/update', 'BeerQAController@updateBeerQACategories');
    Route::delete('/beer-qa-categories/{id}', 'BeerQAController@destroyBeerQACategories');
    Route::get('/beer-qa-categories/{keyword}/search', 'BeerQAController@searchBeerQACategories');

    Route::get('/equipment-maintenance-step/{id}', 'EquipmentMaintenanceStepController@getStepsBySection');
    Route::get('/equipment-maintenance-step', 'EquipmentMaintenanceStepController@getEquipmentPhotos');
    Route::post('/equipment-maintenance-step/store', 'EquipmentMaintenanceStepController@store');
    Route::post('/equipment-maintenance-step/{equipmentTutorial}/update', 'EquipmentMaintenanceStepController@update');
    Route::delete('/equipment-maintenance-step/{id}', 'EquipmentMaintenanceStepController@destroy');
    Route::get('/equipment-maintenance-step/{sectionId}/{keyword}/search', 'EquipmentMaintenanceStepController@searchEquipmentSteps');


    Route::get('/equipment-maintenance-sections', 'EquipmentMaintenanceSectionsController@getAllSections');
    Route::post('/equipment-maintenance-sections/store', 'EquipmentMaintenanceSectionsController@store');
    Route::post('/equipment-maintenance-sections/{equipmentTutorial}/update', 'EquipmentMaintenanceSectionsController@update');
    Route::delete('/equipment-maintenance-sections/{id}', 'EquipmentMaintenanceSectionsController@destroy');
    Route::get('/equipment-maintenance-sections/{keyword}/search', 'EquipmentMaintenanceSectionsController@searchEquipmentMaintenanceSections');

    Route::get('/beer-food-categories', 'BeerFoodCategoryController@getBeerFoodCategories');
    Route::get('/beer-food-categories/all', 'BeerFoodCategoryController@getAllBeerFoodCategories');
    Route::post('/beer-food-categories/store', 'BeerFoodCategoryController@storeBeerFoodCategory');
    Route::post('/beer-food-categories/{beerFoodCategory}/update', 'BeerFoodCategoryController@updateBeerFoodCategory');
    Route::delete('/beer-food-categories/{id}', 'BeerFoodCategoryController@destroyBeerFoodCategory');
    Route::get('/beer-food-categories/{keyword}/search', 'BeerFoodCategoryController@searchBeerFoodCategories');

    Route::get('/beer-food-articles', 'BeerFoodArticleController@getAllArticles');
    Route::post('/beer-food-articles/store', 'BeerFoodArticleController@store');
    Route::post('/beer-food-articles/{beerFoodArticle}/update', 'BeerFoodArticleController@update');
    Route::delete('/beer-food-articles/{id}', 'BeerFoodArticleController@destroy');
    Route::get('/beer-food-articles/{keyword}/search', 'BeerFoodArticleController@searchArticles');

    Route::get('/locations', 'LocationController@getAllLocations');
});
