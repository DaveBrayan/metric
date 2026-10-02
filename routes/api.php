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




