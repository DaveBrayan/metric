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
use App\Http\Controllers\FireActivityController;
use App\Http\Controllers\FireWeightController;
use App\Http\Controllers\ErgonomiaRebaController;
use App\Http\Controllers\ErgonomiaRosaController;
use App\Http\Controllers\GasesController;
use App\Http\Controllers\PhotographicInspectionController;
use App\Http\Controllers\ParticulasController;
use App\Http\Controllers\ParticulasAmbientalesController;
use App\Http\Controllers\VibracionController;
use App\Http\Controllers\ContaminantesQuimicosController;

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

// 1. Rutas Públicas (Autenticación y Legal)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Política de Privacidad Pública (Requisito Google Play Store)
Route::get('/privacidad', function () {
    return view('legal.privacy-policy');
})->name('privacy.policy');
Route::get('/politica-de-privacidad', function () {
    return view('legal.privacy-policy');
});
Route::get('/privacy-policy', function () {
    return view('legal.privacy-policy');
});

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
    Route::get('/proyectos/{id}/tiempo-real', [ProjectController::class, 'liveMonitoring'])->name('projects.live_monitoring');
    Route::get('/proyectos/{id}/tiempo-real/data', [ProjectController::class, 'liveMonitoringData'])->name('projects.live_monitoring.data');

    Route::get('/modulos', [ProjectController::class, 'modules'])->name('modules.index');
    Route::post('/modulos', [ProjectController::class, 'storeModule'])->name('modules.store');
    Route::put('/modulos/{id}', [ProjectController::class, 'updateModule'])->name('modules.update');
    Route::match(['delete', 'post'], '/modulos/{id}/delete', [ProjectController::class, 'destroyModule'])->name('modules.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}', [ProjectController::class, 'destroyModule'])->name('modules.destroy');

    // Monitoreo de Iluminación Ocupacional
    Route::get('/modulos/{id}/iluminacion', [IlluminationController::class, 'index'])->name('modules.illumination');
    Route::get('/modulos/{id}/iluminacion/informe', [IlluminationController::class, 'showReport'])->name('modules.illumination.report');
    Route::post('/modulos/{id}/iluminacion/informe/save', [IlluminationController::class, 'saveReportData'])->name('modules.illumination.report.save');
    Route::post('/modulos/{id}/iluminacion/mediciones', [IlluminationController::class, 'storeMeasurement'])->name('modules.illumination.measurements.store');
    Route::put('/modulos/{id}/iluminacion/mediciones/{measurementId}', [IlluminationController::class, 'updateMeasurement'])->name('modules.illumination.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/iluminacion/mediciones/{measurementId}/delete', [IlluminationController::class, 'destroyMeasurement'])->name('modules.illumination.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/iluminacion/mediciones/{measurementId}', [IlluminationController::class, 'destroyMeasurement'])->name('modules.illumination.measurements.destroy');
    Route::post('/modulos/{id}/iluminacion/header', [IlluminationController::class, 'updateHeader'])->name('modules.illumination.header.update');
    Route::post('/modulos/{id}/iluminacion/photo-report-settings', [IlluminationController::class, 'savePhotoReportSettings'])->name('modules.illumination.photo-report-settings.save');

    // Monitoreo de Ventilación Ocupacional
    Route::get('/modulos/{id}/ventilacion', [VentilationController::class, 'index'])->name('modules.ventilation');
    Route::get('/modulos/{id}/ventilacion/informe', [VentilationController::class, 'showReport'])->name('modules.ventilation.report');
    Route::post('/modulos/{id}/ventilacion/informe/save', [VentilationController::class, 'saveReportData'])->name('modules.ventilation.report.save');
    Route::post('/modulos/{id}/ventilacion/mediciones', [VentilationController::class, 'storeMeasurement'])->name('modules.ventilation.measurements.store');
    Route::put('/modulos/{id}/ventilacion/mediciones/{measurementId}', [VentilationController::class, 'updateMeasurement'])->name('modules.ventilation.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/ventilacion/mediciones/{measurementId}/delete', [VentilationController::class, 'destroyMeasurement'])->name('modules.ventilation.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/ventilacion/mediciones/{measurementId}', [VentilationController::class, 'destroyMeasurement'])->name('modules.ventilation.measurements.destroy');
    Route::post('/modulos/{id}/ventilacion/header', [VentilationController::class, 'updateHeader'])->name('modules.ventilation.header.update');
    Route::post('/modulos/{id}/ventilacion/photo-report-settings', [VentilationController::class, 'savePhotoReportSettings'])->name('modules.ventilation.photo-report-settings.save');

    // Monitoreo de Dosimetría de Ruido
    Route::get('/modulos/{id}/dosimetria', [DosimetryController::class, 'index'])->name('modules.dosimetry');
    Route::get('/modulos/{id}/dosimetria/informe', [DosimetryController::class, 'showReport'])->name('modules.dosimetry.report');
    Route::post('/modulos/{id}/dosimetria/informe/save', [DosimetryController::class, 'saveReportData'])->name('modules.dosimetry.report.save');
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
    Route::get('/modulos/{id}/opacidad/informe', [OpacityController::class, 'showReport'])->name('modules.opacity.report');
    Route::post('/modulos/{id}/opacidad/informe/save', [OpacityController::class, 'saveReportData'])->name('modules.opacity.report.save');
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

    // Monitoreo de Carga de Fuego por Actividad (NB 58005 / NTP 453)
    Route::get('/modulos/{id}/carga-fuego-actividad', [FireActivityController::class, 'index'])->name('modules.fire_activity');
    Route::get('/modulos/{id}/carga-fuego-actividad/informe', [FireActivityController::class, 'showReport'])->name('modules.fire_activity.report');
    Route::post('/modulos/{id}/carga-fuego-actividad/informe/save', [FireActivityController::class, 'saveReportData'])->name('modules.fire_activity.report.save');
    Route::post('/modulos/{id}/carga-fuego-actividad/mediciones', [FireActivityController::class, 'storeMeasurement'])->name('modules.fire_activity.measurements.store');
    Route::put('/modulos/{id}/carga-fuego-actividad/mediciones/{measurementId}', [FireActivityController::class, 'updateMeasurement'])->name('modules.fire_activity.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/carga-fuego-actividad/mediciones/{measurementId}/delete', [FireActivityController::class, 'destroyMeasurement'])->name('modules.fire_activity.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/carga-fuego-actividad/mediciones/{measurementId}', [FireActivityController::class, 'destroyMeasurement'])->name('modules.fire_activity.measurements.destroy');
    Route::post('/modulos/{id}/carga-fuego-actividad/header', [FireActivityController::class, 'updateHeader'])->name('modules.fire_activity.header.update');
    Route::post('/modulos/{id}/carga-fuego-actividad/photo-report-settings', [FireActivityController::class, 'savePhotoReportSettings'])->name('modules.fire_activity.photo-report-settings.save');

    // Monitoreo de Carga de Fuego por Peso (NB 58005 / NTP 453)
    Route::get('/modulos/{id}/carga-fuego-peso', [FireWeightController::class, 'index'])->name('modules.fire_weight');
    Route::get('/modulos/{id}/carga-fuego-peso/informe', [FireWeightController::class, 'showReport'])->name('modules.fire_weight.report');
    Route::post('/modulos/{id}/carga-fuego-peso/informe/save', [FireWeightController::class, 'saveReportData'])->name('modules.fire_weight.report.save');
    Route::post('/modulos/{id}/carga-fuego-peso/mediciones', [FireWeightController::class, 'storeMeasurement'])->name('modules.fire_weight.measurements.store');
    Route::put('/modulos/{id}/carga-fuego-peso/mediciones/{measurementId}', [FireWeightController::class, 'updateMeasurement'])->name('modules.fire_weight.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/carga-fuego-peso/mediciones/{measurementId}/delete', [FireWeightController::class, 'destroyMeasurement'])->name('modules.fire_weight.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/carga-fuego-peso/mediciones/{measurementId}', [FireWeightController::class, 'destroyMeasurement'])->name('modules.fire_weight.measurements.destroy');
    Route::post('/modulos/{id}/carga-fuego-peso/header', [FireWeightController::class, 'updateHeader'])->name('modules.fire_weight.header.update');
    Route::post('/modulos/{id}/carga-fuego-peso/photo-report-settings', [FireWeightController::class, 'savePhotoReportSettings'])->name('modules.fire_weight.photo-report-settings.save');

    // Monitoreo de Ergonomía REBA (NTP 601 / ISO 11226)
    Route::get('/modulos/{id}/ergonomia-reba', [ErgonomiaRebaController::class, 'index'])->name('modules.ergonomia_reba');
    Route::get('/modulos/{id}/ergonomia-reba/tablas', [ErgonomiaRebaController::class, 'showTables'])->name('modules.ergonomia_reba.tables');
    Route::post('/modulos/{id}/ergonomia-reba/mediciones', [ErgonomiaRebaController::class, 'storeMeasurement'])->name('modules.ergonomia_reba.measurements.store');
    Route::put('/modulos/{id}/ergonomia-reba/mediciones/{measurementId}', [ErgonomiaRebaController::class, 'updateMeasurement'])->name('modules.ergonomia_reba.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/ergonomia-reba/mediciones/{measurementId}/delete', [ErgonomiaRebaController::class, 'destroyMeasurement'])->name('modules.ergonomia_reba.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/ergonomia-reba/mediciones/{measurementId}', [ErgonomiaRebaController::class, 'destroyMeasurement'])->name('modules.ergonomia_reba.measurements.destroy');
    Route::post('/modulos/{id}/ergonomia-reba/header', [ErgonomiaRebaController::class, 'updateHeader'])->name('modules.ergonomia_reba.header.update');
    Route::post('/modulos/{id}/ergonomia-reba/photo-report-settings', [ErgonomiaRebaController::class, 'savePhotoReportSettings'])->name('modules.ergonomia_reba.photo-report-settings.save');
    Route::post('/modulos/{id}/ergonomia-reba/anexo2', [ErgonomiaRebaController::class, 'saveAnexo2'])->name('modules.ergonomia_reba.anexo2.save');
    Route::post('/modulos/{id}/ergonomia-reba/prompts', [ErgonomiaRebaController::class, 'savePrompts'])->name('modules.ergonomia_reba.prompts.save');
    Route::post('/modulos/{id}/ergonomia-reba/generate-ai', [ErgonomiaRebaController::class, 'generateAiContent'])->name('modules.ergonomia_reba.generate-ai');

    // Monitoreo de Ergonomía ROSA (Rapid Office Strain Assessment)
    Route::get('/modulos/{id}/ergonomia-rosa', [ErgonomiaRosaController::class, 'index'])->name('modules.ergonomia_rosa');
    Route::get('/modulos/{id}/ergonomia-rosa/tablas', [ErgonomiaRosaController::class, 'showTables'])->name('modules.ergonomia_rosa.tables');
    Route::post('/modulos/{id}/ergonomia-rosa/mediciones', [ErgonomiaRosaController::class, 'storeMeasurement'])->name('modules.ergonomia_rosa.measurements.store');
    Route::put('/modulos/{id}/ergonomia-rosa/mediciones/{measurementId}', [ErgonomiaRosaController::class, 'updateMeasurement'])->name('modules.ergonomia_rosa.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/ergonomia-rosa/mediciones/{measurementId}/delete', [ErgonomiaRosaController::class, 'destroyMeasurement'])->name('modules.ergonomia_rosa.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/ergonomia-rosa/mediciones/{measurementId}', [ErgonomiaRosaController::class, 'destroyMeasurement'])->name('modules.ergonomia_rosa.measurements.destroy');
    Route::post('/modulos/{id}/ergonomia-rosa/header', [ErgonomiaRosaController::class, 'updateHeader'])->name('modules.ergonomia_rosa.header.update');
    Route::post('/modulos/{id}/ergonomia-rosa/photo-report-settings', [ErgonomiaRosaController::class, 'savePhotoReportSettings'])->name('modules.ergonomia_rosa.photo-report-settings.save');
    Route::post('/modulos/{id}/ergonomia-rosa/anexo2', [ErgonomiaRosaController::class, 'saveAnexo2'])->name('modules.ergonomia_rosa.anexo2.save');
    Route::post('/modulos/{id}/ergonomia-rosa/prompts', [ErgonomiaRosaController::class, 'savePrompts'])->name('modules.ergonomia_rosa.prompts.save');
    Route::post('/modulos/{id}/ergonomia-rosa/generate-ai', [ErgonomiaRosaController::class, 'generateAiContent'])->name('modules.ergonomia_rosa.generate-ai');

    // Monitoreo de Gases Ocupacionales y Ambientales
    Route::get('/modulos/{id}/gases', [GasesController::class, 'index'])->name('modules.gases');
    Route::get('/modulos/{id}/gases/informe', [GasesController::class, 'showReport'])->name('modules.gases.report');
    Route::post('/modulos/{id}/gases/informe/save', [GasesController::class, 'saveReportData'])->name('modules.gases.report.save');
    Route::post('/modulos/{id}/gases/mediciones', [GasesController::class, 'storeMeasurement'])->name('modules.gases.measurements.store');
    Route::put('/modulos/{id}/gases/mediciones/{measurementId}', [GasesController::class, 'updateMeasurement'])->name('modules.gases.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/gases/mediciones/{measurementId}/delete', [GasesController::class, 'destroyMeasurement'])->name('modules.gases.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/gases/mediciones/{measurementId}', [GasesController::class, 'destroyMeasurement'])->name('modules.gases.measurements.destroy');
    Route::post('/modulos/{id}/gases/header', [GasesController::class, 'updateHeader'])->name('modules.gases.header.update');
    Route::post('/modulos/{id}/gases/photo-report-settings', [GasesController::class, 'savePhotoReportSettings'])->name('modules.gases.photo-report-settings.save');

    // Monitoreo de Inspección Fotográfica
    Route::get('/modulos/{id}/inspeccion-fotografica', [PhotographicInspectionController::class, 'index'])->name('modules.photographic_inspection');
    Route::get('/modulos/{id}/inspeccion-fotografica/informe', [PhotographicInspectionController::class, 'showReport'])->name('modules.photographic_inspection.report');
    Route::post('/modulos/{id}/inspeccion-fotografica/informe/save', [PhotographicInspectionController::class, 'saveReportData'])->name('modules.photographic_inspection.report.save');
    Route::post('/modulos/{id}/inspeccion-fotografica/mediciones', [PhotographicInspectionController::class, 'storeMeasurement'])->name('modules.photographic_inspection.measurements.store');
    Route::put('/modulos/{id}/inspeccion-fotografica/mediciones/{measurementId}', [PhotographicInspectionController::class, 'updateMeasurement'])->name('modules.photographic_inspection.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/inspeccion-fotografica/mediciones/{measurementId}/delete', [PhotographicInspectionController::class, 'destroyMeasurement'])->name('modules.photographic_inspection.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/inspeccion-fotografica/mediciones/{measurementId}', [PhotographicInspectionController::class, 'destroyMeasurement'])->name('modules.photographic_inspection.measurements.destroy');
    Route::post('/modulos/{id}/inspeccion-fotografica/header', [PhotographicInspectionController::class, 'updateHeader'])->name('modules.photographic_inspection.header.update');
    Route::post('/modulos/{id}/inspeccion-fotografica/photo-report-settings', [PhotographicInspectionController::class, 'savePhotoReportSettings'])->name('modules.photographic_inspection.photo-report-settings.save');

    // Monitoreo de Partículas Ocupacionales
    Route::get('/modulos/{id}/particulas', [ParticulasController::class, 'index'])->name('modules.particulas');
    Route::get('/modulos/{id}/particulas/informe', [ParticulasController::class, 'showReport'])->name('modules.particulas.report');
    Route::post('/modulos/{id}/particulas/informe/save', [ParticulasController::class, 'saveReportData'])->name('modules.particulas.report.save');
    Route::post('/modulos/{id}/particulas/mediciones', [ParticulasController::class, 'storeMeasurement'])->name('modules.particulas.measurements.store');
    Route::put('/modulos/{id}/particulas/mediciones/{measurementId}', [ParticulasController::class, 'updateMeasurement'])->name('modules.particulas.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/particulas/mediciones/{measurementId}/delete', [ParticulasController::class, 'destroyMeasurement'])->name('modules.particulas.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/particulas/mediciones/{measurementId}', [ParticulasController::class, 'destroyMeasurement'])->name('modules.particulas.measurements.destroy');
    Route::post('/modulos/{id}/particulas/header', [ParticulasController::class, 'updateHeader'])->name('modules.particulas.header.update');
    Route::post('/modulos/{id}/particulas/photo-report-settings', [ParticulasController::class, 'savePhotoReportSettings'])->name('modules.particulas.photo-report-settings.save');

    // Monitoreo de Partículas Ambientales (Calidad de Aire)
    Route::get('/modulos/{id}/particulas-ambientales', [ParticulasAmbientalesController::class, 'index'])->name('modules.particulas_ambientales');
    Route::get('/modulos/{id}/particulas-ambientales/informe', [ParticulasAmbientalesController::class, 'showReport'])->name('modules.particulas_ambientales.report');
    Route::post('/modulos/{id}/particulas-ambientales/informe/save', [ParticulasAmbientalesController::class, 'saveReportData'])->name('modules.particulas_ambientales.report.save');
    Route::post('/modulos/{id}/particulas-ambientales/mediciones', [ParticulasAmbientalesController::class, 'storeMeasurement'])->name('modules.particulas_ambientales.measurements.store');
    Route::put('/modulos/{id}/particulas-ambientales/mediciones/{measurementId}', [ParticulasAmbientalesController::class, 'updateMeasurement'])->name('modules.particulas_ambientales.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/particulas-ambientales/mediciones/{measurementId}/delete', [ParticulasAmbientalesController::class, 'destroyMeasurement'])->name('modules.particulas_ambientales.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/particulas-ambientales/mediciones/{measurementId}', [ParticulasAmbientalesController::class, 'destroyMeasurement'])->name('modules.particulas_ambientales.measurements.destroy');
    Route::post('/modulos/{id}/particulas-ambientales/header', [ParticulasAmbientalesController::class, 'updateHeader'])->name('modules.particulas_ambientales.header.update');
    Route::post('/modulos/{id}/particulas-ambientales/photo-report-settings', [ParticulasAmbientalesController::class, 'savePhotoReportSettings'])->name('modules.particulas_ambientales.photo-report-settings.save');

    // Monitoreo de Vibración Ocupacional (Cuerpo Entero y Mano - Brazo)
    Route::get('/modulos/{id}/vibracion', [VibracionController::class, 'index'])->name('modules.vibracion');
    Route::get('/modulos/{id}/vibraciones', [VibracionController::class, 'index'])->name('modules.vibraciones');
    Route::get('/modulos/{id}/vibracion/informe', [VibracionController::class, 'showReport'])->name('modules.vibracion.report');
    Route::post('/modulos/{id}/vibracion/informe/save', [VibracionController::class, 'saveReportData'])->name('modules.vibracion.report.save');
    Route::post('/modulos/{id}/vibracion/mediciones', [VibracionController::class, 'storeMeasurement'])->name('modules.vibracion.measurements.store');
    Route::put('/modulos/{id}/vibracion/mediciones/{measurementId}', [VibracionController::class, 'updateMeasurement'])->name('modules.vibracion.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/vibracion/mediciones/{measurementId}/delete', [VibracionController::class, 'destroyMeasurement'])->name('modules.vibracion.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/vibracion/mediciones/{measurementId}', [VibracionController::class, 'destroyMeasurement'])->name('modules.vibracion.measurements.destroy');
    Route::post('/modulos/{id}/vibracion/header', [VibracionController::class, 'updateHeader'])->name('modules.vibracion.header.update');
    Route::post('/modulos/{id}/vibracion/photo-report-settings', [VibracionController::class, 'savePhotoReportSettings'])->name('modules.vibracion.photo-report-settings.save');

    // Monitoreo de Contaminantes Químicos (Gases, Vapores y Polvos)
    Route::get('/modulos/{id}/contaminantes-quimicos', [ContaminantesQuimicosController::class, 'index'])->name('modules.contaminantes_quimicos');
    Route::get('/modulos/{id}/quimicos', [ContaminantesQuimicosController::class, 'index'])->name('modules.quimicos');
    Route::post('/modulos/{id}/contaminantes-quimicos/mediciones', [ContaminantesQuimicosController::class, 'storeMeasurement'])->name('modules.contaminantes_quimicos.measurements.store');
    Route::put('/modulos/{id}/contaminantes-quimicos/mediciones/{measurementId}', [ContaminantesQuimicosController::class, 'updateMeasurement'])->name('modules.contaminantes_quimicos.measurements.update');
    Route::match(['delete', 'post'], '/modulos/{id}/contaminantes-quimicos/mediciones/{measurementId}/delete', [ContaminantesQuimicosController::class, 'destroyMeasurement'])->name('modules.contaminantes_quimicos.measurements.destroy.post');
    Route::match(['delete', 'post'], '/modulos/{id}/contaminantes-quimicos/mediciones/{measurementId}', [ContaminantesQuimicosController::class, 'destroyMeasurement'])->name('modules.contaminantes_quimicos.measurements.destroy');
    Route::post('/modulos/{id}/contaminantes-quimicos/header', [ContaminantesQuimicosController::class, 'updateHeader'])->name('modules.contaminantes_quimicos.header.update');
    Route::post('/modulos/{id}/contaminantes-quimicos/photo-report-settings', [ContaminantesQuimicosController::class, 'savePhotoReportSettings'])->name('modules.contaminantes_quimicos.photo-report-settings.save');

    // Equipos: Inventario, altas, edición, eliminación y calibraciones
    Route::get('/equipos', [EquipmentController::class, 'index'])->name('equipment.index');
    Route::post('/equipos', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::put('/equipos/{id}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::match(['delete', 'post'], '/equipos/{id}/delete', [EquipmentController::class, 'destroy'])->name('equipment.destroy.post');
    Route::match(['delete', 'post'], '/equipos/{id}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');
    Route::get('/equipos/{id}/calibraciones', [EquipmentController::class, 'getCalibrations'])->name('equipment.calibrations.index');
    Route::post('/equipos/{id}/calibraciones', [EquipmentController::class, 'storeCalibration'])->name('equipment.calibrations.store');
    Route::match(['delete', 'post'], '/equipos/calibraciones/{calibrationId}/delete', [EquipmentController::class, 'destroyCalibration'])->name('equipment.calibrations.destroy.post');
    Route::match(['delete', 'post'], '/equipos/calibraciones/{calibrationId}', [EquipmentController::class, 'destroyCalibration'])->name('equipment.calibrations.destroy');

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
    Route::get('/modulos/{id}/carga-fuego-actividad/export', function ($id) { return redirect()->route('modules.fire_activity', $id); })->name('modules.fire_activity.export');
    Route::get('/modulos/{id}/carga-fuego-peso/export', function ($id) { return redirect()->route('modules.fire_weight', $id); })->name('modules.fire_weight.export');
    Route::get('/modulos/{id}/ergonomia-reba/export', function ($id) { return redirect()->route('modules.ergonomia_reba', $id); })->name('modules.ergonomia_reba.export');
    Route::get('/modulos/{id}/ergonomia-rosa/export', function ($id) { return redirect()->route('modules.ergonomia_rosa', $id); })->name('modules.ergonomia_rosa.export');
    Route::get('/modulos/{id}/gases/export', function ($id) { return redirect()->route('modules.gases', $id); })->name('modules.gases.export');
    Route::get('/modulos/{id}/particulas/export', function ($id) { return redirect()->route('modules.particulas', $id); })->name('modules.particulas.export');
    Route::get('/modulos/{id}/particulas-ambientales/export', function ($id) { return redirect()->route('modules.particulas_ambientales', $id); })->name('modules.particulas_ambientales.export');
    Route::get('/modulos/{id}/vibracion/export', function ($id) { return redirect()->route('modules.vibracion', $id); })->name('modules.vibracion.export');
    Route::get('/modulos/{id}/contaminantes-quimicos/export', function ($id) { return redirect()->route('modules.contaminantes_quimicos', $id); })->name('modules.contaminantes_quimicos.export');
    Route::get('/modulos/{id}/inspeccion-fotografica/export', function ($id) { return redirect()->route('modules.photographic_inspection', $id); })->name('modules.photographic_inspection.export');

    Route::get('/configuracion', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/configuracion', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/configuracion/test-gemini', [SettingsController::class, 'testGeminiConnection'])->name('settings.test-gemini');
    Route::post('/configuracion/gemini-models', [SettingsController::class, 'getAvailableGeminiModels'])->name('settings.gemini-models');
});
