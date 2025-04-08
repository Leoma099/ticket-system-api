<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketNotificationController;
use App\Http\Controllers\MyAccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;

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

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/account', [AccountController::class, 'index']);
    Route::get('/account/{id}', [AccountController::class, 'show']);
    Route::post('/account', [AccountController::class, 'store']);
    Route::put('/account/{id}', [AccountController::class, 'update']);
    Route::delete('/account/{id}', [AccountController::class, 'destroy']);
    Route::get('/staff-accounts', [AccountController::class, 'getStaffWithTicketStats']);
    Route::get('/get-staff', [AccountController::class, 'getStaffDataInfo']);


    Route::get('/ticket', [TicketController::class, 'index']);
    Route::get('/ticket/{id}', [TicketController::class, 'show']);
    Route::post('/ticket', [TicketController::class, 'store']);
    Route::post('/ticket/walkin', [TicketController::class, 'storeWalkIn']);
    Route::put('/ticket/{id}', [TicketController::class, 'update']);
    Route::put('/ticket/{id}/pending', [TicketController::class, 'pendingStatus']);
    Route::put('/ticket/{id}/approve', [TicketController::class, 'approveStatus']);
    Route::put('/ticket/{id}/cancel', [TicketController::class, 'cancelStatus']);
    Route::delete('/ticket/{id}', [TicketController::class, 'destroy']);
    Route::get('/status', [TicketController::class, 'getStatusResolveAndUnresolved']);
    Route::get('/ticketStat', [TicketController::class, 'getTicketStatus']);
    Route::get('/ticketStats', [TicketController::class, 'getPriorityLevelStatus']);
    Route::get('/ticketAssigned', [TicketController::class, 'getAssignedTickets']);

    Route::get('/myaccount', [MyAccountController::class, 'index']);
    Route::get('/myaccount/{id}', [MyAccountController::class, 'show']);
    Route::post('/myaccount', [MyAccountController::class, 'store']);
    Route::put('/myaccount/{id}', [MyAccountController::class, 'update']);
    Route::delete('/myaccount/{id}', [MyAccountController::class, 'destroy']);
    Route::get('/myaccount', [MyAccountController::class, 'me']);

    Route::get('/ticket-notifications', [TicketNotificationController::class, 'index']);
    Route::get('/ticket-notifications/unread/count', [TicketNotificationController::class, 'unreadCount']);
    Route::put('/ticket-notification/{id}/read', [TicketNotificationController::class, 'markAsRead']);
    Route::post('/mark-notifications-read', [TicketNotificationController::class, 'markAllAsRead']);

    Route::post('logout', [AuthController::class, 'logout']);

});

Route::post('login', [AuthController::class, 'login']);

Route::get('login', function()
{
    return abort(401, 'Invalid access.');
})->name('login');