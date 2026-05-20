<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/proyeccion-cartera', \App\Livewire\PortfolioProjection::class)->name('portfolio');
Route::get('/historico-financiero', \App\Livewire\HistoricalFinancials::class)->name('historical-financials');
Route::get('/historico-cartera', \App\Livewire\HistoricalPortfolio::class)->name('historical-portfolio');
Route::get('/budget', \App\Livewire\BudgetSimulator::class)
    ->name('budget');
Route::get('/master-budget', \App\Livewire\MasterBudget::class)->name('master.budget');
Route::get('/recuperacion-provisiones', \App\Livewire\RecupProvSimulator::class)->name('recup-prov');

require __DIR__.'/auth.php';
