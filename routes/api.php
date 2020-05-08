<?php

use Illuminate\Http\JsonResponse;
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


// EVERYONE
Route::post('/auth/login', 'AuthController@authenticate');
Route::post('/auth/register', 'AuthController@register');

Route::post('/analysis', 'AnalysisController@analyze');
Route::post('/translate', 'AnalysisController@translate');

// ADMIN
Route::group(['middleware' => ['jwt.auth', 'admin']], function () {
    // aqui van las rutas que solo puede lanzar el admin
});

// CLIENT or ADMIN
Route::group(['middleware' => ['jwt.auth']], function () {
    Route::post('/auth/logout', 'AuthController@logout');
    Route::post('/auth/refresh', 'AuthController@refresh')->name('refresh');
    
    // aqui van las rutas de cualquier usuario que este registrado
});

// Models (admin)
Route::post('/models/training', 'ModelController@trainingModel');
Route::post('/models/predict', 'ModelController@predictModel');
Route::post('/models/updateModel', 'ModelController@updateModelInUse');

// scrapers (admin)
Route::post('/scrapers/weathers/forecast', 'ScraperController@weatherForecast');
Route::post('/scrapers/weathers/history', 'ScraperController@weatherHistory');
Route::post('/scrapers/flights/history', 'ScraperController@flightsHistory');
Route::post('/scrapers/flights/forecast', 'ScraperController@flightsForecast');
Route::post('/scrapers/airportia/url', 'ScraperController@airportUrl');
Route::get('/scrapers/{id}', 'ScraperController@scrapers');
Route::post('/scrapers/comments', 'ScraperController@comments');

Route::get('/location/city', 'LocationController@getCity');
Route::get('/location/country', 'LocationController@getCountry');

Route::post('/weather', 'WeatherController@insert');
Route::post('/comment', 'CommentController@insert');

Route::post('/prueba', 'ModelController@selectedAttributes');
