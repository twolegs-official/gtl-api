<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\YoutubeScrapController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
 * token 인증 예시
Route::middleware('auth:sanctum')->get('/ ', function (Request $request) {
    return $request->user();
});
*/

Route::get('/test ', function (Request $request) {
    return 'test!';
});

Route::get('/scrap-youtube-detail', [YoutubeScrapController::class, 'detail']);



