<?php

// ЭТУ СТРОКУ ДОБАВЬТЕ В САМОЕ НАЧАЛО (после <?php)
Auth::routes();

// ВСЕ ОСТАЛЬНЫЕ ВАШИ МАРШРУТЫ ОСТАЮТСЯ
use App\Http\Controllers\PcCheckController;
use App\Http\Controllers\AllowedSoftwareController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');  // ДОБАВЬТЕ ->middleware('auth')

// Проверки ПК
Route::resource('pc-checks', PcCheckController::class)->middleware('auth');  // ДОБАВЬТЕ middleware
Route::get('pc-checks/{pcCheck}/export', [PcCheckController::class, 'export'])->name('pc-checks.export')->middleware('auth');

// Управление разрешённым ПО
Route::resource('allowed-software', AllowedSoftwareController::class)->middleware('auth');  // ДОБАВЬТЕ middleware
Route::post('allowed-software/check-similar', [AllowedSoftwareController::class, 'checkSimilar'])->name('allowed-software.check-similar')->middleware('auth');
Route::post('allowed-software/smart-search', [AllowedSoftwareController::class, 'smartSearch'])->name('allowed-software.smart-search')->middleware('auth');
Route::post('allowed-software/import', [AllowedSoftwareController::class, 'import'])->name('allowed-software.import')->middleware('auth');
Route::get('allowed-software/export', [AllowedSoftwareController::class, 'export'])->name('allowed-software.export')->middleware('auth');
