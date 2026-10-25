<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/', [FeedController::class, 'index'])->name('feed');
    Route::post('/posts', [FeedController::class, 'store'])->name('posts.store');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
