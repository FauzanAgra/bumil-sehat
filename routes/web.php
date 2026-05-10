<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Mother\DashboardController as MotherDashboard;
use App\Http\Controllers\Mother\AncController;
use App\Http\Controllers\Mother\ArticleController;
use App\Http\Controllers\Mother\ProfileController;

use App\Http\Controllers\Midwife\DashboardController as MidwifeDashboard;
use App\Http\Controllers\Midwife\PatientController as MidwifePatient;
use App\Http\Controllers\Midwife\ScheduleController as MidwifeSchedule;
use App\Http\Controllers\Midwife\ValidationController as MidwifeValidation;
use App\Http\Controllers\Midwife\ProfileController as MidwifeSetting;

use App\Http\Controllers\Doctor\DashboardController as DoctorDashboard;
use App\Http\Controllers\Doctor\PatientController as DoctorPatient;
use App\Http\Controllers\Doctor\ProfileController as DoctorSetting;

use App\Models\User;
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
        Route::get('/dashboard', [MotherDashboard::class, 'index'])->name('dashboard');

        // ANC
        Route::prefix('anc')->name('anc.')->controller(AncController::class)->group(function () {
            Route::get('/history', 'history')->name('history');
            Route::get('/{schedule}', 'detail')->name('detail');
            Route::get('/{schedule}/form', 'form')->name('form');
            Route::post('/{schedule}/form', 'store')->name('store');
        });

        // TTD
        Route::post('/ttd/take', function () {
            $user      = auth()->user();
            $pregnancy = $user->motherProfile?->activePregnancy;
            $ttd       = $pregnancy?->ttdStatus;

            if ($ttd?->is_active) {
                $ttd->logs()->updateOrCreate(
                    ['date' => today()],
                    ['is_taken' => true]
                );
            }

            return back();
        })->name('ttd.take');

        // Chat
        Route::get('/chat', function () {
            $user      = auth()->user();
            $midwife   = $user->motherProfile?->assignedMidwife;
            $staff     = User::whereIn('role', ['midwife', 'doctor'])
                ->where('is_verified', true)
                ->with('medicalProfile.specialization')
                ->get();
            return view('dashboard.mother.chat', compact('user', 'midwife', 'staff'));
        })->name('chat');


        // Artikel
        Route::get('/articles', [ArticleController::class, 'index'])->name('articles');
        Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

        // Setting & Profil
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/setting', 'setting')->name('setting');
            Route::prefix('profile')->name('profile.')->group(function () {
                Route::get('/edit', 'edit')->name('edit');
                Route::post('/edit', 'update')->name('update');
                Route::get('/password', 'changePassword')->name('password');
                Route::post('/password', 'updatePassword')->name('password.update');
            });
        });
    });

    Route::prefix('midwife')->name('midwife.')->group(function () {
        Route::get('/dashboard', [MidwifeDashboard::class, 'index'])->name('dashboard');

        Route::get('/patients', [MidwifePatient::class, 'index'])->name('patients');
        Route::get('/patients/{motherProfile}', [MidwifePatient::class, 'show'])->name('patients.show');

        Route::get('/schedules', [MidwifeSchedule::class, 'index'])->name('schedules');
        Route::get('/schedules/create', [MidwifeSchedule::class, 'create'])->name('schedules.create');
        Route::post('/schedules', [MidwifeSchedule::class, 'store'])->name('schedules.store');
        Route::put('/schedules/{schedule}', [MidwifePatient::class, 'updateSchedule'])->name('schedules.update');

        Route::get('/anc/{ancRecord}/validate', [MidwifeValidation::class, 'show'])->name('anc.validate');
        Route::post('/anc/{ancRecord}/validate', [MidwifeValidation::class, 'validate'])->name('anc.validate.store');

        Route::prefix('profile')->name('profile.')->controller(MidwifeSetting::class)->group(function () {
            Route::get('/setting',           'show')->name('setting');
            Route::get('/edit',              'edit')->name('edit');
            Route::post('/edit',             'update')->name('update');
            Route::get('/password',          'changePassword')->name('password');
            Route::post('/password',         'updatePassword')->name('password.update');
        });

        Route::patch('/pregnancies/{pregnancy}/toggle-ttd', [MidwifePatient::class, 'toggleTtd'])->name('pregnancies.toggle-ttd');
    });

    Route::prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/dashboard', [DoctorDashboard::class, 'index'])->name('dashboard');

        Route::get('/patients', [DoctorPatient::class, 'index'])->name('patients');
        Route::get('/patients/{motherProfile}', [DoctorPatient::class, 'show'])->name('patients.show');
        Route::post('/patients/{motherProfile}/recommend', [DoctorPatient::class, 'storeRecommendation'])
            ->name('patients.recommend');

        Route::prefix('profile')->name('profile.')->controller(DoctorSetting::class)->group(function () {
            Route::get('/setting',           'show')->name('setting');
            Route::get('/edit',              'edit')->name('edit');
            Route::post('/edit',             'update')->name('update');
            Route::get('/password',          'changePassword')->name('password');
            Route::post('/password',         'updatePassword')->name('password.update');
        });
    });
});
