<?php

use App\Http\Controllers\ExampleController;
use App\Http\Controllers\KakaoExampleController;
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

Route::name('solapi.')->prefix('solapi')->group(function () {
    Route::controller(ExampleController::class)->group(function () {
        Route::get('/get-messages', 'get_messages')->name('get_messages');
        Route::post('/send', 'send')->name('send');
        Route::get('/get-balance', 'get_balance')->name('get_balance');
        Route::get('/get-groups', 'get_groups')->name('get_groups');
        Route::get('/get-group/{groupId}', 'get_group')->name('get_group');
        Route::get('/get-group-messages/{groupId}', 'get_group_messages')->name('get_group_messages');
        Route::get('/get-statistics', 'get_statistics')->name('get_statistics');
    });

    Route::controller(KakaoExampleController::class)->prefix('kakao')->name('kakao.')->group(function () {
        Route::post('/send-ata', 'send_ata')->name('send_ata');
    });
});
