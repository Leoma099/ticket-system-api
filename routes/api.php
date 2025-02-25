<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

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

Route::get('/account', [AccountController::class, 'index']);
Route::get('/account/{id}', [AccountController::class, 'show']);
Route::post('/account', [AccountController::class, 'store']);
Route::put('/account/{id}', [AccountController::class, 'update']);
Route::delete('/account/{id}', [AccountController::class, 'destroy']);

Route::get('/ticket', [TicketController::class, 'index']);
Route::get('/ticket/{id}', [TicketController::class, 'show']);
Route::post('/ticket', [TicketController::class, 'store']);
Route::put('/ticket/{id}', [TicketController::class, 'update']);
Route::delete('/ticket/{id}', [TicketController::class, 'destroy']);

Route::get('/user', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'show']);
Route::post('/user', [UserController::class, 'store']);
Route::put('/user/{id}', [UserController::class, 'update']);
Route::delete('/user/{id}', [UserController::class, 'destroy']);

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
