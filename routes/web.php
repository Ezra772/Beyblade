<?php

use App\Livewire\Management\BitManager;
use App\Livewire\Management\BladeManager;
use App\Livewire\Management\RatchetManager;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/management/blades', BladeManager::class)->name('management.blades');
Route::get('/management/ratchets', RatchetManager::class)->name('management.ratchets');
Route::get('/management/bits', BitManager::class)->name('management.bits');
Route::get('/management/series', \App\Livewire\Management\SeriesManager::class)->name('management.series');
Route::get('/management/products', \App\Livewire\Management\ProductManager::class)->name('management.products');
Route::get('/management/beyblades', \App\Livewire\Management\BeybladeManager::class)->name('management.beyblades');
Route::get('/management/import-export', \App\Livewire\Management\ImportExport::class)->name('management.import-export');

require __DIR__.'/settings.php';
