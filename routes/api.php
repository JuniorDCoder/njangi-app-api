<?php

use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\LoansController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware(['guest'])
                ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                // ->middleware('guest')
                ->name('login');

Route::group(['prefix' => 'events'], function(){
    Route::post('/create', [EventsController::class, 'store'])
                ->name('event.create');
});


Route::group(['prefix' => 'transactions'], function(){
    Route::post('/create', [TransactionsController::class, 'store'])
                ->name('transaction.create');

    Route::get('/contributions', [TransactionsController::class, 'getContributionTransactions']);
});

Route::group(['prefix' => 'loan'], function(){
    Route::post('/request', [LoansController::class, 'store'])
                ->name('loan.create');
});

Route::get('/users', [UsersController::class, 'getAllUsers'])->name('user.get-all');
