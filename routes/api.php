<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;



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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::put('/user/{id}', [AuthController::class, 'updateUserById']);

Route::get('/user/{id}', [AuthController::class, 'getUserById']);

Route::delete('/users/{id}', [AuthController::class, 'deleteUserById']);


Route::get('testing', function (){
    return 'tanga tanga mo bobo';
});
