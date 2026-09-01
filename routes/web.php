<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProjectController;

// 1. Rutas Públicas de Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirección inicial hacia el login si no está autenticado
Route::middleware(['auth'])->group(function () {
    // Main Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Organización: 1. Empresas, 2. Responsables, 3. Personal
    Route::get('/empresas', [CompanyController::class, 'index'])->name('companies.index');
    Route::post('/empresas', [CompanyController::class, 'store'])->name('companies.store');
    Route::put('/empresas/{id}', [CompanyController::class, 'update'])->name('companies.update');
    Route::match(['delete', 'post'], '/empresas/{id}/delete', [CompanyController::class, 'destroy'])->name('companies.destroy.post');
    Route::match(['delete', 'post'], '/empresas/{id}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    Route::get('/responsables', [ManagerController::class, 'index'])->name('managers.index');
    Route::post('/responsables', [ManagerController::class, 'store'])->name('managers.store');
    Route::put('/responsables/{id}', [ManagerController::class, 'update'])->name('managers.update');
    Route::match(['delete', 'post'], '/responsables/{id}/delete', [ManagerController::class, 'destroy'])->name('managers.destroy.post');
    Route::match(['delete', 'post'], '/responsables/{id}', [ManagerController::class, 'destroy'])->name('managers.destroy');
    Route::post('/responsables/{id}/reset-password', [ManagerController::class, 'resetPassword'])->name('managers.reset-password');

    Route::get('/personal', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/personal', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/personal/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::match(['delete', 'post'], '/personal/{id}/delete', [StaffController::class, 'destroy'])->name('staff.destroy.post');
    Route::match(['delete', 'post'], '/personal/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/personal/{id}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');

    // Proyectos: Proyectos Activos & Submódulo de Módulos de Monitoreo
    Route::get('/proyectos', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/proyectos', [ProjectController::class, 'store'])->name('projects.store');

    Route::get('/modulos', [ProjectController::class, 'modules'])->name('modules.index');

    // Sistema: Administradores y Configuración
    Route::get('/administradores', [AdminController::class, 'index'])->name('admins.index');
    Route::post('/administradores', [AdminController::class, 'store'])->name('admins.store');
    Route::put('/administradores/{id}', [AdminController::class, 'update'])->name('admins.update');
    Route::match(['delete', 'post'], '/administradores/{id}/delete', [AdminController::class, 'destroy'])->name('admins.destroy.post');
    Route::match(['delete', 'post'], '/administradores/{id}', [AdminController::class, 'destroy'])->name('admins.destroy');
    Route::post('/administradores/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('admins.reset-password');
    Route::post('/administradores/{id}/permissions', [AdminController::class, 'updatePermissions'])->name('admins.permissions');

    Route::get('/configuracion', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/configuracion', [SettingsController::class, 'update'])->name('settings.update');
});
