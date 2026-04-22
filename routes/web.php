<?php
// routes/web.php

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
