<?php

use App\Http\Controllers\ExampleController;
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

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::name('solapi.')->controller(ExampleController::class)->prefix('solapi')->group(function () {
    Route::get('/get-messages', 'get_messages')->name('get_messages');
    Route::post('/send', 'send')->name('send');
    Route::get('/get-balance', 'get_balance')->name('get_balance');
});
