<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboard;
use App\Http\Controllers\Doctor\PatientController as DoctorPatient;
use App\Http\Controllers\Doctor\ProfileController as DoctorProfile;
use App\Http\Controllers\Midwife\DashboardController as MidwifeDashboard;
use App\Http\Controllers\Midwife\PatientController as MidwifePatient;
use App\Http\Controllers\Midwife\ProfileController as MidwifeSetting;
use App\Http\Controllers\Midwife\ScheduleController as MidwifeSchedule;
use App\Http\Controllers\Midwife\ValidationController as MidwifeValidation;
use App\Http\Controllers\Mother\AncController;
use App\Http\Controllers\Mother\ArticleController;
use App\Http\Controllers\Mother\ChatController;
use App\Http\Controllers\Mother\DashboardController as MotherDashboard;
use App\Http\Controllers\Mother\ProfileController;
use App\Http\Controllers\Mother\TtdController;
use Illuminate\Support\Facades\Route;

// ── Guest ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));

    Route::get('/login',  [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'chooseRole'])->name('register');

    Route::get('/register/mother',          [RegisterController::class, 'showMother'])->name('register.mother');
    Route::post('/register/mother',         [RegisterController::class, 'storeMother'])->name('register.mother.store');
    Route::get('/register/mother/identity', [RegisterController::class, 'showIdentity'])->name('register.mother.identity');
    Route::post('/register/mother/identity', [RegisterController::class, 'storeIdentity'])->name('register.mother.identity.store');
    Route::get('/register/mother/hpht',     [RegisterController::class, 'showHpht'])->name('register.mother.hpht');
    Route::post('/register/mother/hpht',    [RegisterController::class, 'storeHpht'])->name('register.mother.hpht.store');

    Route::get('/register/medical',  [RegisterController::class, 'showMedical'])->name('register.medical');
    Route::post('/register/medical', [RegisterController::class, 'storeMedical'])->name('register.medical.store');
});

// ── Logout ────────────────────────────────────────────────────────────────────
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Waiting Approval — semua role terverifikasi butuh ini ─────────────────────
Route::get('/waiting-approval', fn() => view('auth.waiting-approval'))->name('waiting-approval')->middleware('auth');

// ── MOTHER ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:mother'])->prefix('mother')->name('mother.')->group(function () {

    Route::get('/dashboard', [MotherDashboard::class, 'index'])->name('dashboard');

    Route::prefix('anc')->name('anc.')->controller(AncController::class)->group(function () {
        Route::get('/history',         'history')->name('history');
        Route::get('/{schedule}',      'detail')->name('detail');
        Route::get('/{schedule}/form', 'form')->name('form');
        Route::post('/{schedule}/form', 'store')->name('store');
    });

    Route::post('/ttd/take', TtdController::class)->name('ttd.take');

    Route::get('/chat', ChatController::class)->name('chat');

    Route::get('/articles',              [ArticleController::class, 'index'])->name('articles');
    Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

    // Profil
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/setting', 'setting')->name('setting');
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/edit',      'edit')->name('edit');
            Route::post('/edit',     'update')->name('update');
            Route::get('/password',  'changePassword')->name('password');
            Route::post('/password', 'updatePassword')->name('password.update');
        });
    });
});

// ── MIDWIFE ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:midwife'])->prefix('midwife')->name('midwife.')->group(function () {

    Route::get('/dashboard', [MidwifeDashboard::class, 'index'])->name('dashboard');

    Route::controller(MidwifePatient::class)->group(function () {
        Route::get('/patients', 'index')->name('patients');
        Route::get('/patients/{motherProfile}', 'show')->name('patients.show');
    });

    Route::controller(MidwifeSchedule::class)->group(function () {
        Route::get('/schedules', 'index')->name('schedules');
        Route::get('/schedules/create', 'create')->name('schedules.create');
        Route::post('/schedules', 'store')->name('schedules.store');
        Route::put('/schedules/{schedule}', 'update')->name('schedules.update');
    });

    Route::controller(MidwifeValidation::class)->group(function () {
        Route::get('/anc/{ancRecord}/validate')->name('anc.validate');
        Route::post('/anc/{ancRecord}/validate')->name('anc.validate.store');
    });

    Route::patch('/pregnancies/{pregnancy}/toggle-ttd', [MidwifePatient::class, 'toggleTtd'])
        ->name('pregnancies.toggle-ttd');

    Route::controller(MidwifeSetting::class)->group(function () {
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/setting', 'show')->name('setting');
            Route::get('/edit',      'edit')->name('edit');
            Route::post('/edit',     'update')->name('update');
            Route::get('/password',  'changePassword')->name('password');
            Route::post('/password', 'updatePassword')->name('password.update');
        });
    });
});

// ── DOCTOR ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {

    Route::get('/dashboard', [DoctorDashboard::class, 'index'])->name('dashboard');

    Route::controller(DoctorPatient::class)->group(function () {
        Route::get('/patients')->name('patients');
        Route::get('/patients/{motherProfile}')->name('patients.show');
        Route::post('/patients/{motherProfile}/recommend')->name('patients.recommend');
    });

    // Profil & setting
    Route::controller(DoctorProfile::class)->group(function () {
        Route::get('/setting', 'show')->name('setting');
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/edit',      'edit')->name('edit');
            Route::post('/edit',     'update')->name('update');
            Route::get('/password',  'changePassword')->name('password');
            Route::post('/password', 'updatePassword')->name('password.update');
        });
    });
});
