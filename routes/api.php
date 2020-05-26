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

Route::post('/send-mail', 'MailController@sendMail');

// ADMIN
Route::group(['middleware' => ['jwt.auth', 'admin']], function () {
    // aqui van las rutas que solo puede lanzar el admin
});

// CLIENT or ADMIN
Route::group(['middleware' => ['jwt.auth']], function () {
    Route::post('/auth/logout', 'AuthController@logout');
    Route::post('/auth/refresh', 'AuthController@refresh');


});

// Models (admin)
Route::post('/models/training', 'ModelController@trainingModel');
Route::post('/models/predict', 'ModelController@predictModel');
Route::post('/models/updateModel', 'ModelController@updateModelInUse');
Route::post('models/algorithms', 'ModelController@getAlgorithms');
Route::post('models/models', 'ModelController@getModels');
Route::post('models/lastModels', 'ModelController@getLastModels');
Route::post('models/deleteModel', 'ModelController@deleteModel');
Route::get('models/getModelInUse', 'ModelController@getModelInUse');

// scrapers (admin)
Route::post('/scrapers/weathers/forecast', 'ScraperController@weatherForecast');
Route::post('/scrapers/weathers/history', 'ScraperController@weatherHistory');
Route::post('/scrapers/flights/history', 'ScraperController@flightsHistory');
Route::post('/scrapers/flights/forecast', 'ScraperController@flightsForecast');
Route::post('/scrapers/airportia/url', 'ScraperController@airportUrl');
Route::post('/scrapers/comments', 'ScraperController@comments');

// airports
Route::get('/airports/coordinates', 'AirportsController@getAirportsCoordinates');
Route::get('/airports/preview', 'AirportsController@getAirportsPreview');
Route::get('/airports/airports', 'AirportsController@getAirports');
Route::get('/airports/flights/{id}', 'AirportsController@getAirportFlights');
Route::get('/airports/comments/{id}', 'AirportsController@getAirportComments');

// flights
Route::get('/flights/dailyStats', 'FlightsController@getDailyStats');
Route::post('/flights/groupFlights', 'FlightsController@getGroupFlights');
Route::post('/flights/getGroupAirports', 'FlightsController@getGroupAirports');

//cities -> comments
Route::get('/cities/top', 'CommentController@getTopDestinations');
Route::get('/comments/cities', 'CommentController@getCities');
Route::post('/comments/data', 'CommentController@getCityData');

//cities
Route::get('/cities/cities', 'CitiesController@getCities');

//countries
Route::get('/countries/countries', 'CountriesController@getCountries');



