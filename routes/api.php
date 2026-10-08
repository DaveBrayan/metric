<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileApiController;

/*
|--------------------------------------------------------------------------
| API Routes - Metric v2 Móvil
|--------------------------------------------------------------------------
| Rutas para la comunicación con la aplicación móvil (Flutter).
*/

// Estado del servidor e información pública
Route::get('/config/server-info', [MobileApiController::class, 'serverInfo']);

// Autenticación de colaboradores (Personal / Staff)
Route::post('/auth/login', [MobileApiController::class, 'login']);
Route::post('/login', [MobileApiController::class, 'login']);
Route::post('/auth/change-password', [MobileApiController::class, 'changePassword']);
Route::post('/change-password', [MobileApiController::class, 'changePassword']);


// Gestión de Proyectos y Módulos de Monitoreo
Route::get('/projects', [MobileApiController::class, 'projects']);
Route::get('/projects/{projectId}/modules', [MobileApiController::class, 'projectModules']);
Route::get('/projects/{projectId}/live-measurements', [MobileApiController::class, 'projectLiveMeasurements']);

// Monitoreo de Iluminación Ocupacional
Route::get('/modules/{moduleId}/illumination', [MobileApiController::class, 'getIlluminationMeasurements']);
Route::post('/modules/{moduleId}/illumination', [MobileApiController::class, 'storeIlluminationMeasurement']);
Route::delete('/modules/{moduleId}/illumination/{id}', [MobileApiController::class, 'destroyIlluminationMeasurement']);

// Monitoreo de Ventilación Ocupacional
Route::get('/modules/{moduleId}/ventilation', [MobileApiController::class, 'getVentilationMeasurements']);
Route::post('/modules/{moduleId}/ventilation', [MobileApiController::class, 'storeVentilationMeasurement']);
Route::delete('/modules/{moduleId}/ventilation/{id}', [MobileApiController::class, 'destroyVentilationMeasurement']);

// Monitoreo de Estrés Térmico (Calor)
Route::get('/modules/{moduleId}/heat-stress', [MobileApiController::class, 'getHeatStressMeasurements']);
Route::post('/modules/{moduleId}/heat-stress', [MobileApiController::class, 'storeHeatStressMeasurement']);
Route::delete('/modules/{moduleId}/heat-stress/{id}', [MobileApiController::class, 'destroyHeatStressMeasurement']);

// Monitoreo de Estrés Térmico (Frío)
Route::get('/modules/{moduleId}/cold-stress', [MobileApiController::class, 'getColdStressMeasurements']);
Route::post('/modules/{moduleId}/cold-stress', [MobileApiController::class, 'storeColdStressMeasurement']);
Route::delete('/modules/{moduleId}/cold-stress/{id}', [MobileApiController::class, 'destroyColdStressMeasurement']);

// Monitoreo de Ergonomía REBA
Route::get('/modules/{moduleId}/ergonomia-reba', [MobileApiController::class, 'getErgonomiaRebaMeasurements']);
Route::get('/modules/{moduleId}/reba', [MobileApiController::class, 'getErgonomiaRebaMeasurements']);
Route::post('/modules/{moduleId}/ergonomia-reba', [MobileApiController::class, 'storeErgonomiaRebaMeasurement']);
Route::post('/modules/{moduleId}/reba', [MobileApiController::class, 'storeErgonomiaRebaMeasurement']);
Route::delete('/modules/{moduleId}/ergonomia-reba/{id}', [MobileApiController::class, 'destroyErgonomiaRebaMeasurement']);
Route::delete('/modules/{moduleId}/reba/{id}', [MobileApiController::class, 'destroyErgonomiaRebaMeasurement']);

// Monitoreo de Ergonomía ROSA
Route::get('/modules/{moduleId}/ergonomia-rosa', [MobileApiController::class, 'getErgonomiaRosaMeasurements']);
Route::get('/modules/{moduleId}/rosa', [MobileApiController::class, 'getErgonomiaRosaMeasurements']);
Route::post('/modules/{moduleId}/ergonomia-rosa', [MobileApiController::class, 'storeErgonomiaRosaMeasurement']);
Route::post('/modules/{moduleId}/rosa', [MobileApiController::class, 'storeErgonomiaRosaMeasurement']);
Route::delete('/modules/{moduleId}/ergonomia-rosa/{id}', [MobileApiController::class, 'destroyErgonomiaRosaMeasurement']);
Route::delete('/modules/{moduleId}/rosa/{id}', [MobileApiController::class, 'destroyErgonomiaRosaMeasurement']);

// Monitoreo de Carga de Fuego por Actividad
Route::get('/modules/{moduleId}/fire-activity', [MobileApiController::class, 'getFireActivityMeasurements']);
Route::get('/modules/{moduleId}/carga-fuego-actividad', [MobileApiController::class, 'getFireActivityMeasurements']);
Route::post('/modules/{moduleId}/fire-activity', [MobileApiController::class, 'storeFireActivityMeasurement']);
Route::post('/modules/{moduleId}/carga-fuego-actividad', [MobileApiController::class, 'storeFireActivityMeasurement']);
Route::delete('/modules/{moduleId}/fire-activity/{id}', [MobileApiController::class, 'destroyFireActivityMeasurement']);
Route::delete('/modules/{moduleId}/carga-fuego-actividad/{id}', [MobileApiController::class, 'destroyFireActivityMeasurement']);

// Monitoreo de Carga de Fuego por Peso
Route::get('/modules/{moduleId}/fire-weight', [MobileApiController::class, 'getFireWeightMeasurements']);
Route::get('/modules/{moduleId}/carga-fuego-peso', [MobileApiController::class, 'getFireWeightMeasurements']);
Route::post('/modules/{moduleId}/fire-weight', [MobileApiController::class, 'storeFireWeightMeasurement']);
Route::post('/modules/{moduleId}/carga-fuego-peso', [MobileApiController::class, 'storeFireWeightMeasurement']);
Route::delete('/modules/{moduleId}/fire-weight/{id}', [MobileApiController::class, 'destroyFireWeightMeasurement']);
Route::delete('/modules/{moduleId}/carga-fuego-peso/{id}', [MobileApiController::class, 'destroyFireWeightMeasurement']);

// Monitoreo de Dosimetría de Ruido Ocupacional
Route::get('/modules/{moduleId}/dosimetry', [MobileApiController::class, 'getDosimetryMeasurements']);
Route::get('/modules/{moduleId}/dosimetria', [MobileApiController::class, 'getDosimetryMeasurements']);
Route::post('/modules/{moduleId}/dosimetry', [MobileApiController::class, 'storeDosimetryMeasurement']);
Route::post('/modules/{moduleId}/dosimetria', [MobileApiController::class, 'storeDosimetryMeasurement']);
Route::delete('/modules/{moduleId}/dosimetry/{id}', [MobileApiController::class, 'destroyDosimetryMeasurement']);
Route::delete('/modules/{moduleId}/dosimetria/{id}', [MobileApiController::class, 'destroyDosimetryMeasurement']);

// Monitoreo de Opacidad Vehicular (Emisión de Humos)
Route::get('/modules/{moduleId}/opacity', [MobileApiController::class, 'getOpacityMeasurements']);
Route::get('/modules/{moduleId}/opacidad', [MobileApiController::class, 'getOpacityMeasurements']);
Route::post('/modules/{moduleId}/opacity', [MobileApiController::class, 'storeOpacityMeasurement']);
Route::post('/modules/{moduleId}/opacidad', [MobileApiController::class, 'storeOpacityMeasurement']);
Route::delete('/modules/{moduleId}/opacity/{id}', [MobileApiController::class, 'destroyOpacityMeasurement']);
Route::delete('/modules/{moduleId}/opacidad/{id}', [MobileApiController::class, 'destroyOpacityMeasurement']);

// Monitoreo de Gases Ocupacionales y Ambientales
Route::get('/modules/{moduleId}/gases', [MobileApiController::class, 'getGasesMeasurements']);
Route::post('/modules/{moduleId}/gases', [MobileApiController::class, 'storeGasesMeasurement']);
Route::delete('/modules/{moduleId}/gases/{id}', [MobileApiController::class, 'destroyGasesMeasurement']);

// Monitoreo de Inspección Fotográfica
Route::get('/modules/{moduleId}/inspeccion-fotografica', [MobileApiController::class, 'getPhotographicInspectionMeasurements']);
Route::get('/modules/{moduleId}/photographic-inspection', [MobileApiController::class, 'getPhotographicInspectionMeasurements']);
Route::post('/modules/{moduleId}/inspeccion-fotografica', [MobileApiController::class, 'storePhotographicInspectionMeasurement']);
Route::post('/modules/{moduleId}/photographic-inspection', [MobileApiController::class, 'storePhotographicInspectionMeasurement']);
Route::delete('/modules/{moduleId}/inspeccion-fotografica/{id}', [MobileApiController::class, 'destroyPhotographicInspectionMeasurement']);
Route::delete('/modules/{moduleId}/photographic-inspection/{id}', [MobileApiController::class, 'destroyPhotographicInspectionMeasurement']);

// Monitoreo de Partículas Ocupacionales
Route::get('/modules/{moduleId}/particles', [MobileApiController::class, 'getParticlesMeasurements']);
Route::get('/modules/{moduleId}/particulas', [MobileApiController::class, 'getParticlesMeasurements']);
Route::post('/modules/{moduleId}/particles', [MobileApiController::class, 'storeParticlesMeasurement']);
Route::post('/modules/{moduleId}/particulas', [MobileApiController::class, 'storeParticlesMeasurement']);
Route::delete('/modules/{moduleId}/particles/{id}', [MobileApiController::class, 'destroyParticlesMeasurement']);
Route::delete('/modules/{moduleId}/particulas/{id}', [MobileApiController::class, 'destroyParticlesMeasurement']);

// Monitoreo de Partículas Ambientales (Calidad de Aire)
Route::get('/modules/{moduleId}/ambient-particles', [MobileApiController::class, 'getAmbientParticlesMeasurements']);
Route::get('/modules/{moduleId}/particulas-ambientales', [MobileApiController::class, 'getAmbientParticlesMeasurements']);
Route::post('/modules/{moduleId}/ambient-particles', [MobileApiController::class, 'storeAmbientParticlesMeasurement']);
Route::post('/modules/{moduleId}/particulas-ambientales', [MobileApiController::class, 'storeAmbientParticlesMeasurement']);
Route::delete('/modules/{moduleId}/ambient-particles/{id}', [MobileApiController::class, 'destroyAmbientParticlesMeasurement']);
Route::delete('/modules/{moduleId}/particulas-ambientales/{id}', [MobileApiController::class, 'destroyAmbientParticlesMeasurement']);

// Monitoreo de Vibración Ocupacional (Cuerpo Entero y Mano - Brazo)
Route::get('/modules/{moduleId}/vibration', [MobileApiController::class, 'getVibracionMeasurements']);
Route::get('/modules/{moduleId}/vibracion', [MobileApiController::class, 'getVibracionMeasurements']);
Route::post('/modules/{moduleId}/vibration', [MobileApiController::class, 'storeVibracionMeasurement']);
Route::post('/modules/{moduleId}/vibracion', [MobileApiController::class, 'storeVibracionMeasurement']);
Route::delete('/modules/{moduleId}/vibration/{id}', [MobileApiController::class, 'destroyVibracionMeasurement']);
Route::delete('/modules/{moduleId}/vibracion/{id}', [MobileApiController::class, 'destroyVibracionMeasurement']);

// Monitoreo de Contaminantes Químicos
Route::get('/modules/{moduleId}/contaminantes-quimicos', [MobileApiController::class, 'getContaminantesQuimicosMeasurements']);
Route::get('/modules/{moduleId}/quimicos', [MobileApiController::class, 'getContaminantesQuimicosMeasurements']);
Route::post('/modules/{moduleId}/contaminantes-quimicos', [MobileApiController::class, 'storeContaminantesQuimicosMeasurement']);
Route::post('/modules/{moduleId}/quimicos', [MobileApiController::class, 'storeContaminantesQuimicosMeasurement']);
Route::delete('/modules/{moduleId}/contaminantes-quimicos/{id}', [MobileApiController::class, 'destroyContaminantesQuimicosMeasurement']);
Route::delete('/modules/{moduleId}/quimicos/{id}', [MobileApiController::class, 'destroyContaminantesQuimicosMeasurement']);








