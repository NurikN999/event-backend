<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify', [AuthController::class, 'verify']);
Route::get('/privacy-policy', function() {
    $path = public_path('pp_app.html');
    $file = File::get($path);
    $response = Response::make($file, 200);
    $response->header('Content-Type', 'text/html');
    return $response;
});

Route::group(['middleware' => 'jwt.auth'], function () {

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('can:is-admin');
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
    });

});
