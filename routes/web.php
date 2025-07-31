<?php

use Illuminate\Support\Facades\Route;
use Aimeos\Shop\Controller\CatalogController;

// الصفحة الرئيسية لمتجر Aimeos
Route::match(['GET', 'HEAD', 'POST'], '/', [CatalogController::class, 'homeAction'])
    ->name('aimeos_home');

// إعادة توجيه /dashboard إلى الصفحة الرئيسية
Route::get('/dashboard', fn () => redirect()->route('aimeos_home'))
    ->name('dashboard');

// مسارات المصادقة من Breeze
require __DIR__.'/auth.php';

// في حال ثبّت Breeze واشتمل ملف web.php على مسار profile،
// غيّر المسار إلى profile/me لتجنُّب التعارض مع Aimeos:
Route::middleware('auth')->group(function () {
    Route::get('/profile/me', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/me', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/me', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});