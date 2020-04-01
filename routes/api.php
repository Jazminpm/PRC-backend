<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/analysis', 'AnalysisController@analyze');
Route::post('/translate', 'AnalysisController@translate');
Route::get('/scraper/{id}', 'ScraperController@scrapers');
Route::get('/model/{id}', 'ModelController@models');

Route::get('/location/city', 'LocationController@getCity');
Route::get('/location/country', 'LocationController@getCountry');

Route::post('/weather', 'WeatherController@insert');
Route::post('/comment', 'CommentController@insert');


