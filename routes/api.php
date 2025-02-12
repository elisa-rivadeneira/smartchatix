<?php

use App\Http\Controllers\UserEnglishController;
use Illuminate\Support\Facades\Route;

Route::get('/user-english/{id}', [UserEnglishController::class, 'show']);
Route::post('/user-english/{id}', [UserEnglishController::class, 'update']);
