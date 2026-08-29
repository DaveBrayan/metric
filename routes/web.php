<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProjectController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Main Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Organización: 1. Empresas, 2. Regionales, 3. Responsables, 4. Personal
Route::get('/empresas', [CompanyController::class, 'index'])->name('companies.index');
Route::post('/empresas', [CompanyController::class, 'store'])->name('companies.store');

Route::get('/regionales', [RegionController::class, 'index'])->name('regions.index');
Route::post('/regionales', [RegionController::class, 'store'])->name('regions.store');

Route::get('/responsables', [ManagerController::class, 'index'])->name('managers.index');
Route::post('/responsables', [ManagerController::class, 'store'])->name('managers.store');

Route::get('/personal', [StaffController::class, 'index'])->name('staff.index');
Route::post('/personal', [StaffController::class, 'store'])->name('staff.store');

// Proyectos: Proyectos Activos & Submódulo de Módulos de Monitoreo
Route::get('/proyectos', [ProjectController::class, 'index'])->name('projects.index');
Route::post('/proyectos', [ProjectController::class, 'store'])->name('projects.store');

Route::get('/modulos', [ProjectController::class, 'modules'])->name('modules.index');

// Sistema: Administradores y Configuración
Route::get('/administradores', [AdminController::class, 'index'])->name('admins.index');
Route::post('/administradores', [AdminController::class, 'store'])->name('admins.store');

Route::get('/configuracion', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/configuracion', [SettingsController::class, 'update'])->name('settings.update');
