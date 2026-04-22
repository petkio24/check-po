<?php
// routes/web.php - добавить новые маршруты

use App\Http\Controllers\PcCheckController;
use App\Http\Controllers\AllowedSoftwareController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Проверки ПК
Route::resource('pc-checks', PcCheckController::class);
Route::get('pc-checks/{pcCheck}/export', [PcCheckController::class, 'export'])->name('pc-checks.export');

// Управление разрешённым ПО
Route::resource('allowed-software', AllowedSoftwareController::class);
Route::post('allowed-software/check-similar', [AllowedSoftwareController::class, 'checkSimilar'])->name('allowed-software.check-similar');
Route::post('allowed-software/smart-search', [AllowedSoftwareController::class, 'smartSearch'])->name('allowed-software.smart-search');
Route::post('allowed-software/import', [AllowedSoftwareController::class, 'import'])->name('allowed-software.import');
Route::get('allowed-software/export', [AllowedSoftwareController::class, 'export'])->name('allowed-software.export');
