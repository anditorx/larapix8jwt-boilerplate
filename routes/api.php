<?php

use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\UserController;
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

Route::middleware('auth:sanctum')->group(function(){
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('user/photo', [UserController::class, 'updatePhoto']);
    Route::post('logout', [UserController::class, 'logout']);

    // Route::post('note', [NoteController::class, 'create']);
    // Route::get('note/{id}', [NoteController::class, 'show']);
    // Route::put('note/{id}', [NoteController::class, 'update']);
    // Route::put('note/update_photo/{id}', [NoteController::class, 'update_photo']);
    Route::resource('note', NoteController::class);
    Route::post('note/photo/{id}', [NoteController::class, 'updatePhoto']);
});

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

Route::get('note', [NoteController::class, 'index']);

