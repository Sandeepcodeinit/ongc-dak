<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsrController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProposalHardCopyController;
use App\Http\Controllers\UserTypeController;

Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/', function () {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        $user = AuthUser();
        $userId = (int) ($user->id ?? 0);
        $activeYn = (int) ($user->active_yn ?? 0);

        if ($userId > 0 && $activeYn === 1) {
            return redirect()->route('hard_copy_proposal');
        }

        return view('login');
    })->name('login');

    Route::get('/notfound', function () {
        return view('not_found');
    })->name('not_found');

    Route::controller(LoginController::class)->group(function () {
        Route::post('/login', 'login')->name('login.submit');
        Route::get('/logout', 'logout')->name('logout');
        Route::get('/password', 'passwordPage')->name('user.password');
        Route::post('/password-save', 'passwordSave')->name('check.password');
        Route::post('/validate-otp', 'checkOTP')->name('check.otp');
        Route::post('/validate-pan', 'checkPAN')->name('check.pan');
        Route::post('/user', 'newUser')->name('user.new');
        Route::post('/forgot_password_submit', 'forgotPassword')->name('forgot_password.submit');
        Route::get('/forgot_password_link/{info}', 'forgotPasswordLink')->name('forgot_password.link');
        Route::post('/forgot_password_reset', 'resetPassword')->name('forgot_password.reset');
    });

    Route::controller(CsrController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/dashboard-home', 'index')->name('csr.dashboard');
    });
});

Route::middleware(['throttle:60,1'])->group(function () {
    Route::controller(CsrController::class)->group(function () {
        Route::match(['get', 'post'], '/home', 'dashboard')->name('dashboard.admin');
    });

    Route::controller(UserTypeController::class)->group(function () {
        Route::get('/password_change', 'passwordIndex')->name('password.index');
        Route::post('/password_update', 'passwordChange')->name('password.change');
    });

    Route::controller(ProposalHardCopyController::class)->group(function () {
        Route::match(['get', 'post'], '/hard_copy_proposal', 'index')->name('hard_copy_proposal');
        Route::match(['get', 'post'], '/hard_copy_proposal/import', 'fileImport')->name('hard_copy_proposal.import');
        Route::get('/hard_copy_proposal/add', 'create')->name('hard_copy_proposal.add');
        Route::post('/hard_copy_proposal/store', 'store')->name('hard_copy_proposal.store');
        Route::get('/hard_copy_proposal/edit/{id}', 'edit')->name('hard_copy_proposal.edit');
        Route::put('/hard_copy_proposal/update/{id}', 'update')->name('hard_copy_proposal.update');
    });
});
