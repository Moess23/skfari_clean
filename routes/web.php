<?php

use Illuminate\Support\Facades\Route;
use Aimeos\Shop\Controller\CatalogController;

Route::match(['GET', 'HEAD', 'POST'], '/', [CatalogController::class, 'homeAction'])
    ->name('aimeos_home');

// اجعل أي زيارة لـ /dashboard تعود للصفحة الرئيسية
Route::get('/dashboard', function () {
    return redirect()->route('aimeos_home');
})->name('dashboard');

// راوتات المصادقة من Breeze
require __DIR__ . '/auth.php';