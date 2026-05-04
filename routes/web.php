<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Mother\DashboardController;
use Illuminate\Support\Facades\Route;

// ── Guest ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Register — pilih role
    Route::get('/register', [RegisterController::class, 'chooseRole'])->name('register');

    // Register — ibu hamil (3 step)
    Route::get('/register/mother', [RegisterController::class, 'showMother'])->name('register.mother');
    Route::post('/register/mother', [RegisterController::class, 'storeMother'])->name('register.mother.store');
    Route::get('/register/mother/identity', [RegisterController::class, 'showIdentity'])->name('register.mother.identity');
    Route::post('/register/mother/identity', [RegisterController::class, 'storeIdentity'])->name('register.mother.identity.store');
    Route::get('/register/mother/hpht', [RegisterController::class, 'showHpht'])->name('register.mother.hpht');
    Route::post('/register/mother/hpht', [RegisterController::class, 'storeHpht'])->name('register.mother.hpht.store');

    // Register — tenaga medis
    Route::get('/register/medical', [RegisterController::class, 'showMedical'])->name('register.medical');
    Route::post('/register/medical', [RegisterController::class, 'storeMedical'])->name('register.medical.store');
});

// ── Logout ────────────────────────────────────────────────────────────────────
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/waiting-approval', function () {
        return view('auth.waiting-approval');
    })->name('waiting-approval');

    // Dashboard placeholder
    Route::prefix('mother')->name('mother.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Placeholder — akan diisi saat konversi halaman berikutnya
        Route::get('/chat',        fn() => 'Chat')->name('chat');
        Route::get('/articles',    fn() => 'Articles')->name('articles');
        Route::get('/articles/{article}', fn() => 'Article Detail')->name('articles.show');
        Route::get('/setting',     fn() => 'Setting')->name('setting');
        Route::get('/anc/history', fn() => 'ANC History')->name('anc.history');
        Route::get('/anc/{schedule}/form', fn() => 'ANC Form')->name('anc.form');
        Route::post('/ttd/take',   fn() => back())->name('ttd.take');
    });
    Route::get('/midwife/dashboard', fn() => 'Midwife Dashboard')->name('midwife.dashboard');
    Route::get('/doctor/dashboard', fn() => 'Doctor Dashboard')->name('doctor.dashboard');
});
