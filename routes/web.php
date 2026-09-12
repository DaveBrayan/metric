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
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\IlluminationController;
use App\Http\Controllers\VentilationController;
use App\Http\Controllers\DosimetryController;
use App\Http\Controllers\RuidoAmbientalController;
use App\Http\Controllers\OpacityController;
use App\Http\Controllers\ColdStressController;
use App\Http\Controllers\HeatStressController;

// 0. Live Zero-Build Asset Fallback (Automatic Dynamic Serving & Sync)
Route::get('/css/{file}', function ($file) {
    $res = resource_path('css/' . $file);
    if (file_exists($res)) {
        $pub = public_path('css/' . $file);
        if (!file_exists($pub) || filemtime($res) > @filemtime($pub)) {
            @copy($res, $pub);
        }
        return response()->file($res, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'no-cache, must-revalidate'
        ]);
    }
    abort(404);
})->where('file', '.*');

Route::get('/js/{file}', function ($file) {
    $res = resource_path('js/' . $file);
    if (file_exists($res)) {
        $pub = public_path('js/' . $file);
        if (!file_exists($pub) || filemtime($res) > @filemtime($pub)) {
            @copy($res, $pub);
        }
        return response()->file($res, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-cache, must-revalidate'
        ]);
    }
    abort(404);
})->where('file', '.*');

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
    Route::post('/personal/{id}/fcm-token', [StaffController::class, 'updateFcmToken'])->name('staff.fcm-token');
    Route::post('/personal/{id}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');

    // Proyectos: Proyectos Activos & Submódulo de Módulos de Monitoreo
    Route::get('/proyectos', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/proyectos', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/proyectos/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::match(['delete', 'post'], '/proyectos/{id}/delete', [ProjectController::class, 'destroy'])->name('projects.destroy.post');
    Route::match(['delete', 'post'], '/proyectos/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('/modulos', [ProjectController::class, 'modules'])->name('modules.index');
    Route::post('/modulos', [ProjectController::class, 'storeModule'])->name('modules.store');
    Route::put('/modulos/{id}', [ProjectController::class, 'updateModule'])->name('modules.update');
    Route::match(['delete', 'post'], '/modulos/{id}/delete', [ProjectController::class, 'destroyModule'])->name('modules.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}', [ProjectController::class, 'destroyModule'])->name('modules.destroy');

    // Monitoreo de Iluminación Ocupacional
    Route::get('/modulos/{id}/iluminacion', [IlluminationController::class, 'index'])->name('modules.illumination');
    Route::post('/modulos/{id}/iluminacion/mediciones', [IlluminationController::class, 'storeMeasurement'])->name('modules.illumination.measurements.store');
    Route::put('/modulos/{id}/iluminacion/mediciones/{measurementId}', [IlluminationController::class, 'updateMeasurement'])->name('modules.illumination.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/iluminacion/mediciones/{measurementId}/delete', [IlluminationController::class, 'destroyMeasurement'])->name('modules.illumination.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/iluminacion/mediciones/{measurementId}', [IlluminationController::class, 'destroyMeasurement'])->name('modules.illumination.measurements.destroy');
    Route::post('/modulos/{id}/iluminacion/header', [IlluminationController::class, 'updateHeader'])->name('modules.illumination.header.update');
    Route::post('/modulos/{id}/iluminacion/photo-report-settings', [IlluminationController::class, 'savePhotoReportSettings'])->name('modules.illumination.photo-report-settings.save');

    // Monitoreo de Ventilación Ocupacional
    Route::get('/modulos/{id}/ventilacion', [VentilationController::class, 'index'])->name('modules.ventilation');
    Route::post('/modulos/{id}/ventilacion/mediciones', [VentilationController::class, 'storeMeasurement'])->name('modules.ventilation.measurements.store');
    Route::put('/modulos/{id}/ventilacion/mediciones/{measurementId}', [VentilationController::class, 'updateMeasurement'])->name('modules.ventilation.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/ventilacion/mediciones/{measurementId}/delete', [VentilationController::class, 'destroyMeasurement'])->name('modules.ventilation.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/ventilacion/mediciones/{measurementId}', [VentilationController::class, 'destroyMeasurement'])->name('modules.ventilation.measurements.destroy');
    Route::post('/modulos/{id}/ventilacion/header', [VentilationController::class, 'updateHeader'])->name('modules.ventilation.header.update');
    Route::post('/modulos/{id}/ventilacion/photo-report-settings', [VentilationController::class, 'savePhotoReportSettings'])->name('modules.ventilation.photo-report-settings.save');

    // Monitoreo de Dosimetría de Ruido
    Route::get('/modulos/{id}/dosimetria', [DosimetryController::class, 'index'])->name('modules.dosimetry');
    Route::post('/modulos/{id}/dosimetria/mediciones', [DosimetryController::class, 'storeMeasurement'])->name('modules.dosimetry.measurements.store');
    Route::put('/modulos/{id}/dosimetria/mediciones/{measurementId}', [DosimetryController::class, 'updateMeasurement'])->name('modules.dosimetry.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/dosimetria/mediciones/{measurementId}/delete', [DosimetryController::class, 'destroyMeasurement'])->name('modules.dosimetry.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/dosimetria/mediciones/{measurementId}', [DosimetryController::class, 'destroyMeasurement'])->name('modules.dosimetry.measurements.destroy');
    Route::post('/modulos/{id}/dosimetria/header', [DosimetryController::class, 'updateHeader'])->name('modules.dosimetry.header.update');
    Route::post('/modulos/{id}/dosimetria/photo-report-settings', [DosimetryController::class, 'savePhotoReportSettings'])->name('modules.dosimetry.photo-report-settings.save');

    // Monitoreo de Ruido Ambiental
    Route::get('/modulos/{id}/ruido-ambiental', [RuidoAmbientalController::class, 'index'])->name('modules.ruido_ambiental');
    Route::post('/modulos/{id}/ruido-ambiental/mediciones', [RuidoAmbientalController::class, 'storeMeasurement'])->name('modules.ruido_ambiental.measurements.store');
    Route::put('/modulos/{id}/ruido-ambiental/mediciones/{measurementId}', [RuidoAmbientalController::class, 'updateMeasurement'])->name('modules.ruido_ambiental.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/ruido-ambiental/mediciones/{measurementId}/delete', [RuidoAmbientalController::class, 'destroyMeasurement'])->name('modules.ruido_ambiental.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/ruido-ambiental/mediciones/{measurementId}', [RuidoAmbientalController::class, 'destroyMeasurement'])->name('modules.ruido_ambiental.measurements.destroy');
    Route::post('/modulos/{id}/ruido-ambiental/header', [RuidoAmbientalController::class, 'updateHeader'])->name('modules.ruido_ambiental.header.update');
    Route::post('/modulos/{id}/ruido-ambiental/photo-report-settings', [RuidoAmbientalController::class, 'savePhotoReportSettings'])->name('modules.ruido_ambiental.photo-report-settings.save');

    // Monitoreo de Opacidad (Vehicular / Humos)
    Route::get('/modulos/{id}/opacidad', [OpacityController::class, 'index'])->name('modules.opacity');
    Route::post('/modulos/{id}/opacidad/mediciones', [OpacityController::class, 'storeMeasurement'])->name('modules.opacity.measurements.store');
    Route::put('/modulos/{id}/opacidad/mediciones/{measurementId}', [OpacityController::class, 'updateMeasurement'])->name('modules.opacity.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/opacidad/mediciones/{measurementId}/delete', [OpacityController::class, 'destroyMeasurement'])->name('modules.opacity.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/opacidad/mediciones/{measurementId}', [OpacityController::class, 'destroyMeasurement'])->name('modules.opacity.measurements.destroy');
    Route::post('/modulos/{id}/opacidad/header', [OpacityController::class, 'updateHeader'])->name('modules.opacity.header.update');
    Route::post('/modulos/{id}/opacidad/photo-report-settings', [OpacityController::class, 'savePhotoReportSettings'])->name('modules.opacity.photo-report-settings.save');

    // Monitoreo de Estrés por Frío
    Route::get('/modulos/{id}/estres-frio', [ColdStressController::class, 'index'])->name('modules.cold_stress');
    Route::post('/modulos/{id}/estres-frio/mediciones', [ColdStressController::class, 'storeMeasurement'])->name('modules.cold_stress.measurements.store');
    Route::put('/modulos/{id}/estres-frio/mediciones/{measurementId}', [ColdStressController::class, 'updateMeasurement'])->name('modules.cold_stress.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/estres-frio/mediciones/{measurementId}/delete', [ColdStressController::class, 'destroyMeasurement'])->name('modules.cold_stress.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/estres-frio/mediciones/{measurementId}', [ColdStressController::class, 'destroyMeasurement'])->name('modules.cold_stress.measurements.destroy');
    Route::post('/modulos/{id}/estres-frio/header', [ColdStressController::class, 'updateHeader'])->name('modules.cold_stress.header.update');
    Route::post('/modulos/{id}/estres-frio/photo-report-settings', [ColdStressController::class, 'savePhotoReportSettings'])->name('modules.cold_stress.photo-report-settings.save');

    // Monitoreo de Estrés por Calor (WBGT)
    Route::get('/modulos/{id}/estres-calor', [HeatStressController::class, 'index'])->name('modules.heat_stress');
    Route::post('/modulos/{id}/estres-calor/mediciones', [HeatStressController::class, 'storeMeasurement'])->name('modules.heat_stress.measurements.store');
    Route::put('/modulos/{id}/estres-calor/mediciones/{measurementId}', [HeatStressController::class, 'updateMeasurement'])->name('modules.heat_stress.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/estres-calor/mediciones/{measurementId}/delete', [HeatStressController::class, 'destroyMeasurement'])->name('modules.heat_stress.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/estres-calor/mediciones/{measurementId}', [HeatStressController::class, 'destroyMeasurement'])->name('modules.heat_stress.measurements.destroy');
    Route::post('/modulos/{id}/estres-calor/header', [HeatStressController::class, 'updateHeader'])->name('modules.heat_stress.header.update');
    Route::post('/modulos/{id}/estres-calor/photo-report-settings', [HeatStressController::class, 'savePhotoReportSettings'])->name('modules.heat_stress.photo-report-settings.save');

    // Equipos: Inventario, altas, edición y eliminación con soporte de fotos
    Route::get('/equipos', [EquipmentController::class, 'index'])->name('equipment.index');
    Route::post('/equipos', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::put('/equipos/{id}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::match(['delete', 'post'], '/equipos/{id}/delete', [EquipmentController::class, 'destroy'])->name('equipment.destroy.post');
    Route::match(['delete', 'post'], '/equipos/{id}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');

    // Sistema: Administradores y Configuración
    Route::get('/administradores', [AdminController::class, 'index'])->name('admins.index');
    Route::post('/administradores', [AdminController::class, 'store'])->name('admins.store');
    Route::put('/administradores/{id}', [AdminController::class, 'update'])->name('admins.update');
    Route::match(['delete', 'post'], '/administradores/{id}/delete', [AdminController::class, 'destroy'])->name('admins.destroy.post');
    Route::match(['delete', 'post'], '/administradores/{id}', [AdminController::class, 'destroy'])->name('admins.destroy');
    Route::post('/administradores/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('admins.reset-password');
    Route::post('/administradores/{id}/permissions', [AdminController::class, 'updatePermissions'])->name('admins.permissions');

    // Export Fallback Routes (Prevent RouteNotFoundException)
    Route::get('/modulos/{id}/iluminacion/export', function ($id) { return redirect()->route('modules.illumination', $id); })->name('modules.illumination.export');
    Route::get('/modulos/{id}/ventilacion/export', function ($id) { return redirect()->route('modules.ventilation', $id); })->name('modules.ventilation.export');
    Route::get('/modulos/{id}/dosimetria/export', function ($id) { return redirect()->route('modules.dosimetry', $id); })->name('modules.dosimetry.export');
    Route::get('/modulos/{id}/ruido-ambiental/export', function ($id) { return redirect()->route('modules.ruido_ambiental', $id); })->name('modules.ruido_ambiental.export');
    Route::get('/modulos/{id}/opacidad/export', function ($id) { return redirect()->route('modules.opacity', $id); })->name('modules.opacity.export');
    Route::get('/modulos/{id}/estres-frio/export', function ($id) { return redirect()->route('modules.cold_stress', $id); })->name('modules.cold_stress.export');
    Route::get('/modulos/{id}/estres-calor/export', function ($id) { return redirect()->route('modules.heat_stress', $id); })->name('modules.heat_stress.export');

    Route::get('/configuracion', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/configuracion', [SettingsController::class, 'update'])->name('settings.update');
});
