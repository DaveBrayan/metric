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


