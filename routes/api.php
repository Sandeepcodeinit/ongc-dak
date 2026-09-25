<?php

use App\Http\Controllers\API\ApiLoginController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiLoginController::class, 'login'])->name('api.login');