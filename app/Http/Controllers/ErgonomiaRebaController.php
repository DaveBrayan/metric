<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\RebaMeasurement;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ErgonomiaRebaController extends Controller
{
    /**
     * Tabla A Lookup Matrix REBA (Hignett & McAtamney):
     * [Trunk (1-5)][Neck (1-3)][Legs (1-4)]
     */
    private static $TABLE_A = [
        // Trunk 1
        1 => [
            1 => [1 => 1, 2 => 2, 3 => 3, 4 => 4],
            2 => [1 => 2, 2 => 3, 3 => 4, 4 => 5],
            3 => [1 => 3, 2 => 4, 3 => 5, 4 => 6],
        ],
        // Trunk 2
        2 => [
            1 => [1 => 2, 2 => 3, 3 => 4, 4 => 5],
            2 => [1 => 3, 2 => 4, 3 => 5, 4 => 6],
            3 => [1 => 4, 2 => 5, 3 => 6, 4 => 7],
        ],
        // Trunk 3
        3 => [
            1 => [1 => 2, 2 => 4, 3 => 5, 4 => 6],
            2 => [1 => 4, 2 => 5, 3 => 6, 4 => 7],
            3 => [1 => 5, 2 => 6, 3 => 7, 4 => 8],
        ],
        // Trunk 4
        4 => [
            1 => [1 => 3, 2 => 5, 3 => 6, 4 => 7],
            2 => [1 => 5, 2 => 6, 3 => 7, 4 => 8],
            3 => [1 => 6, 2 => 7, 3 => 8, 4 => 9],
        ],
        // Trunk 5
        5 => [
            1 => [1 => 4, 2 => 6, 3 => 7, 4 => 8],
            2 => [1 => 6, 2 => 7, 3 => 8, 4 => 9],
            3 => [1 => 7, 2 => 8, 3 => 9, 4 => 9],
        ],
    ];

    /**
     * Tabla B Lookup Matrix REBA:
     * [Upper Arm (1-6)][Lower Arm (1-2)][Wrist (1-3)]
     */
    private static $TABLE_B = [
        // Upper Arm 1
        1 => [
            1 => [1 => 1, 2 => 2, 3 => 2],
            2 => [1 => 1, 2 => 2, 3 => 3],
        ],
        // Upper Arm 2
        2 => [
            1 => [1 => 1, 2 => 2, 3 => 3],
            2 => [1 => 2, 2 => 3, 3 => 4],
        ],
        // Upper Arm 3
        3 => [
            1 => [1 => 3, 2 => 4, 3 => 5],
            2 => [1 => 4, 2 => 5, 3 => 5],
        ],
        // Upper Arm 4
        4 => [
            1 => [1 => 4, 2 => 5, 3 => 5],
            2 => [1 => 5, 2 => 6, 3 => 7],
        ],
        // Upper Arm 5
        5 => [
            1 => [1 => 6, 2 => 7, 3 => 8],
            2 => [1 => 7, 2 => 8, 3 => 8],
        ],
        // Upper Arm 6
        6 => [
            1 => [1 => 7, 2 => 8, 3 => 8],
            2 => [1 => 8, 2 => 9, 3 => 9],
        ],
    ];

    /**
     * Tabla C Lookup Matrix REBA:
     * [Score A (1-12)][Score B (1-12)]
     */
    private static $TABLE_C = [
        1  => [1 => 1, 2 => 1, 3 => 1, 4 => 2, 5 => 3, 6 => 3, 7 => 4, 8 => 5, 9 => 6, 10 => 7, 11 => 7, 12 => 7],
        2  => [1 => 1, 2 => 2, 3 => 2, 4 => 3, 5 => 4, 6 => 4, 7 => 5, 8 => 6, 9 => 6, 10 => 7, 11 => 7, 12 => 8],
        3  => [1 => 2, 2 => 3, 3 => 3, 4 => 3, 5 => 4, 6 => 5, 7 => 6, 8 => 7, 9 => 7, 10 => 8, 11 => 8, 12 => 8],
        4  => [1 => 3, 2 => 4, 3 => 4, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 8, 10 => 9, 11 => 9, 12 => 9],
        5  => [1 => 4, 2 => 4, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 8, 9 => 9, 10 => 9, 11 => 9, 12 => 9],
        6  => [1 => 6, 2 => 6, 3 => 6, 4 => 7, 5 => 8, 6 => 8, 7 => 9, 8 => 9, 9 => 10, 10 => 10, 11 => 10, 12 => 10],
        7  => [1 => 7, 2 => 7, 3 => 7, 4 => 8, 5 => 9, 6 => 9, 7 => 9, 8 => 10, 9 => 10, 10 => 11, 11 => 11, 12 => 11],
        8  => [1 => 8, 2 => 8, 3 => 8, 4 => 9, 5 => 10, 6 => 10, 7 => 10, 8 => 10, 9 => 10, 10 => 11, 11 => 11, 12 => 11],
        9  => [1 => 9, 2 => 9, 3 => 9, 4 => 10, 5 => 10, 6 => 10, 7 => 11, 8 => 11, 9 => 11, 10 => 12, 11 => 12, 12 => 12],
        10 => [1 => 10, 2 => 10, 3 => 10, 4 => 11, 5 => 11, 6 => 11, 7 => 11, 8 => 12, 9 => 12, 10 => 12, 11 => 12, 12 => 12],
        11 => [1 => 11, 2 => 11, 3 => 11, 4 => 11, 5 => 12, 6 => 12, 7 => 12, 8 => 12, 9 => 12, 10 => 12, 11 => 12, 12 => 12],
        12 => [1 => 12, 2 => 12, 3 => 12, 4 => 12, 5 => 12, 6 => 12, 7 => 12, 8 => 12, 9 => 12, 10 => 12, 11 => 12, 12 => 12],
    ];

    /**
     * Calcula la puntuación completa REBA y determina el nivel de riesgo y acción.
     */
    public static function calculateRebaScore(array $data): array
    {
        // 1. Grupo A
        $troncoBase = (int)($data['tronco_base'] ?? 1);
        $troncoMod = !empty($data['tronco_mod']) ? 1 : 0;
        $troncoFinal = min(5, max(1, $troncoBase + $troncoMod));

        $cuelloBase = (int)($data['cuello_base'] ?? 1);
        $cuelloMod = !empty($data['cuello_mod']) ? 1 : 0;
        $cuelloFinal = min(3, max(1, $cuelloBase + $cuelloMod));

        $piernasBase = (int)($data['piernas_base'] ?? 1);
        $piernasMod = (int)($data['piernas_mod'] ?? 0); // 0, 1 (30-60°), 2 (>60°), 3 (ambos)
        if (isset($data['piernas_flexion_30_60']) || isset($data['piernas_flexion_mas_60'])) {
            $pMod30 = !empty($data['piernas_flexion_30_60']) ? 1 : 0;
            $pMod60 = !empty($data['piernas_flexion_mas_60']) ? 2 : 0;
            $piernasMod = $pMod30 + $pMod60;
        }
        $piernasFinal = max(1, $piernasBase + $piernasMod);

        $colPiernas = min(4, max(1, $piernasFinal));
        $scoreTablaA = self::$TABLE_A[$troncoFinal][$cuelloFinal][$colPiernas] ?? 1;
        $cargaFuerza = (int)($data['carga_fuerza'] ?? 0); // 0 (<5kg), 1 (5-10kg), 2 (>10kg)
        $cargaBrusca = !empty($data['carga_brusca']) ? 1 : 0;
        $scoreA = min(12, max(1, $scoreTablaA + $cargaFuerza + $cargaBrusca));

        // 2. Grupo B
        $brazoBase = (int)($data['brazo_base'] ?? 1);
        $brazoMod = 0;
        if (!empty($data['brazo_abduccion'])) $brazoMod += 1;
        if (!empty($data['brazo_hombro_elevado'])) $brazoMod += 1;
        if (!empty($data['brazo_apoyo_gravedad'])) $brazoMod -= 1;
        $brazoFinal = min(6, max(1, $brazoBase + $brazoMod));

        $antebrazoFinal = (int)($data['antebrazo_base'] ?? 1); // 1 (60-100°), 2 (<60° o >100°)
        $antebrazoFinal = min(2, max(1, $antebrazoFinal));

        $munecaBase = (int)($data['muneca_base'] ?? 1); // 1 (0-15°), 2 (>15°)
        $munecaMod = !empty($data['muneca_mod']) ? 1 : 0;
        $munecaFinal = min(3, max(1, $munecaBase + $munecaMod));

        $scoreTablaB = self::$TABLE_B[$brazoFinal][$antebrazoFinal][$munecaFinal] ?? 1;
        $agarre = (int)($data['agarre'] ?? 0); // 0 (bueno), 1 (aceptable), 2 (posible/regular), 3 (incomodo/sin agarre)
        $scoreB = min(12, max(1, $scoreTablaB + $agarre));

        // 3. Puntuación C & Actividad
        $scoreC = self::$TABLE_C[$scoreA][$scoreB] ?? 1;

        $actEstatica = !empty($data['actividad_estatica']) ? 1 : 0;
        $actRepetitiva = !empty($data['actividad_repetitiva']) ? 1 : 0;
        $actInestable = !empty($data['actividad_inestable']) ? 1 : 0;
        $scoreActividad = $actEstatica + $actRepetitiva + $actInestable;

        // 4. Score Final REBA
        $scoreFinal = min(15, max(1, $scoreC + $scoreActividad));

        // 5. Nivel de Riesgo y Acción
        if ($scoreFinal === 1) {
            $riskLevel = 'Inapreciable';
            $actionLevel = 'Nivel 0: No es necesaria acción';
            $riskTheme = 'emerald';
            $badgeClass = 'risk-inapreciable';
        } elseif ($scoreFinal <= 3) {
            $riskLevel = 'Bajo';
            $actionLevel = 'Nivel 1: Puede ser necesaria la acción';
            $riskTheme = 'lime';
            $badgeClass = 'risk-bajo';
        } elseif ($scoreFinal <= 7) {
            $riskLevel = 'Medio';
            $actionLevel = 'Nivel 2: Es necesaria la acción';
            $riskTheme = 'amber';
            $badgeClass = 'risk-medio';
        } elseif ($scoreFinal <= 10) {
            $riskLevel = 'Alto';
            $actionLevel = 'Nivel 3: Es necesaria la acción pronto';
            $riskTheme = 'orange';
            $badgeClass = 'risk-alto';
        } else {
            $riskLevel = 'Muy Alto';
            $actionLevel = 'Nivel 4: Es necesaria la acción de inmediato';
            $riskTheme = 'red';
            $badgeClass = 'risk-muy-alto';
        }

        return [
            'tronco_final' => $troncoFinal,
            'cuello_final' => $cuelloFinal,
            'piernas_final' => $piernasFinal,
            'score_tabla_a' => $scoreTablaA,
            'score_a' => $scoreA,
            'brazo_final' => $brazoFinal,
            'antebrazo_final' => $antebrazoFinal,
            'muneca_final' => $munecaFinal,
            'score_tabla_b' => $scoreTablaB,
            'score_b' => $scoreB,
            'score_c' => $scoreC,
            'score_actividad' => $scoreActividad,
            'score_final' => $scoreFinal,
            'risk_level' => $riskLevel,
            'action_level' => $actionLevel,
            'risk_theme' => $riskTheme,
            'badge_class' => $badgeClass,
        ];
    }

    /**
     * Muestra la vista principal de monitoreo de Ergonomía REBA.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->find($moduleId);

        if (!$module) {
            $module = new MeasurementModule();
            $module->id = (int)$moduleId;
            $module->name = 'Ergonomía REBA';
            $module->key = 'ergonomia_reba';
            $module->description = 'Evaluación ergonómica postural de cuerpo entero mediante el método REBA (NTP 601 / ISO 11226 / UNE-EN 1005-4).';
            $module->points_total = 8;
            $module->points_completed = 0;
            $module->start_date = Carbon::now()->subDays(3);
            $module->end_date = Carbon::now();
            $module->monitoring_type = 'Monitoreo Ergonómico Postural (Método REBA)';
            $module->installation_name = 'Planta Industrial Central — Corporación Minera';
            $module->calibration_equipment = 'Goniómetro Digital Ergonómico & Cámara de Alta Frecuencia';
        }

        $projectName = ($module->project) ? $module->project->name : 'Proyecto Industrial Alpha';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : 'Empresa Principal';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : date('Y-m-d', strtotime('-3 days'));
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : date('Y-m-d');
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : date('d/m/Y', strtotime('-3 days'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : date('d/m/Y');

        $monitoringType = $module->monitoring_type ?: 'Ergonomía REBA (NTP 601 / ISO 11226)';

        // Equipo Asignado (Goniómetro / Cámara / Dinamómetro)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Goniómetro Digital 360° & Cámara');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Baseline / Sony') : 'Baseline Ergonomics';
        $equipmentModel = $equipment ? ($equipment->model ?: 'Absolute+Axis / Alpha 7C') : 'Digital Pro 360';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'ERGO-884102') : 'ERGO-884102';
        $equipmentImage = ($equipment && $equipment->image) ? asset($equipment->image) : null;

        // Personal Asignado
        $assignedStaff = method_exists($module, 'getAssignedStaffAttribute') ? $module->getAssignedStaffAttribute() : null;
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
            $staffList = $assignedStaff;
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Ing. Reynaldo Pachabol (Ergónomo)';
            $staffList = Staff::orderBy('name')->get();
        }

        // Obtener mediciones reales de la base de datos
        $dbMeasurements = ($module->exists && method_exists($module, 'rebaMeasurements'))
            ? $module->rebaMeasurements()->with('staff')->get()
            : collect([]);

        if ($dbMeasurements->isNotEmpty()) {
            $measurementsList = $dbMeasurements->map(function ($item, $index) use ($assignedStaff, $currentUser) {
                $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
                if (empty($rawImages) && !empty($item->image_path)) {
                    $rawImages = [$item->image_path];
                }
                $imagesUrls = array_values(array_filter(array_map(function($p) {
                    return $p ? asset($p) : null;
                }, $rawImages)));

                $calc = self::calculateRebaScore([
                    'tronco_base' => $item->tronco_base,
                    'tronco_mod' => $item->tronco_mod,
                    'cuello_base' => $item->cuello_base,
                    'cuello_mod' => $item->cuello_mod,
                    'piernas_base' => $item->piernas_base,
                    'piernas_mod' => $item->piernas_mod,
                    'carga_fuerza' => $item->carga_fuerza,
                    'carga_brusca' => $item->carga_brusca,
                    'brazo_base' => $item->brazo_base,
                    'brazo_abduccion' => $item->brazo_abduccion,
                    'brazo_hombro_elevado' => $item->brazo_hombro_elevado,
                    'brazo_apoyo_gravedad' => $item->brazo_apoyo_gravedad,
                    'antebrazo_base' => $item->antebrazo_base,
                    'muneca_base' => $item->muneca_base,
                    'muneca_mod' => $item->muneca_mod,
                    'agarre' => $item->agarre,
                    'actividad_estatica' => $item->actividad_estatica,
                    'actividad_repetitiva' => $item->actividad_repetitiva,
                    'actividad_inestable' => $item->actividad_inestable,
                ]);

                return array_merge([
                    'id' => $item->id,
                    'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'date' => $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—',
                    'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                    'time' => $item->measurement_time ?: '—',
                    'area_sector' => $item->area_sector,
                    'puesto_trabajo' => $item->puesto_trabajo,
                    'factor_riesgo' => $item->factor_riesgo,
                    'num_trabajadores' => $item->num_trabajadores,
                    'nombres_trabajadores' => is_array($item->nombres_trabajadores) ? $item->nombres_trabajadores : (json_decode($item->nombres_trabajadores, true) ?: []),
                    'edad' => $item->edad,
                    'tiempo_exposicion_horas' => $item->tiempo_exposicion_horas,
                    'procedimiento_escrito' => $item->procedimiento_escrito,
                    'capacitacion' => $item->capacitacion,
                    'fuerza_agarre' => $item->fuerza_agarre,
                    'carga_peso_kg' => $item->carga_peso_kg,
                    'distancia_m' => $item->distancia_m,
                    'ayuda_mecanica' => $item->ayuda_mecanica,
                    'descripcion_carga' => $item->descripcion_carga,
                    'manifestacion_temprana' => $item->manifestacion_temprana,
                    'ubicacion_sintoma' => $item->ubicacion_sintoma,
                    'tareas' => is_array($item->tareas) ? $item->tareas : (json_decode($item->tareas, true) ?: []),
                    'observaciones' => $item->observaciones,
                    'image_path' => $item->image_path ? asset($item->image_path) : ($imagesUrls[0] ?? null),
                    'images' => $imagesUrls,
                    'images_count' => count($imagesUrls),
                    'registered_by' => $item->registered_by ?: ($item->staff ? ($item->staff->full_name ?: $item->staff->name) : ($currentUser ? $currentUser->name : 'Técnico Ergónomo')),
                    'staff_id' => $item->staff_id,
                    'location' => [
                        'lat' => $item->latitude,
                        'lng' => $item->longitude,
                        'zone' => $item->utm_zone,
                        'easting' => $item->utm_easting,
                        'northing' => $item->utm_northing,
                    ],
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'utm_zone' => $item->utm_zone,
                    'utm_easting' => $item->utm_easting,
                    'utm_northing' => $item->utm_northing,
                    // Anatómicos
                    'tronco_base' => $item->tronco_base,
                    'tronco_mod' => $item->tronco_mod,
                    'cuello_base' => $item->cuello_base,
                    'cuello_mod' => $item->cuello_mod,
                    'piernas_base' => $item->piernas_base,
                    'piernas_mod' => $item->piernas_mod,
                    'carga_fuerza' => $item->carga_fuerza,
                    'carga_brusca' => $item->carga_brusca,
                    'brazo_base' => $item->brazo_base,
                    'brazo_abduccion' => $item->brazo_abduccion,
                    'brazo_hombro_elevado' => $item->brazo_hombro_elevado,
                    'brazo_apoyo_gravedad' => $item->brazo_apoyo_gravedad,
                    'antebrazo_base' => $item->antebrazo_base,
                    'muneca_base' => $item->muneca_base,
                    'muneca_mod' => $item->muneca_mod,
                    'agarre' => $item->agarre,
                    'actividad_estatica' => $item->actividad_estatica,
                    'actividad_repetitiva' => $item->actividad_repetitiva,
                    'actividad_inestable' => $item->actividad_inestable,
                ], $calc);
            })->toArray();
        } else {
            // Mock Evaluations representativas si no hay registros aún
            $measurementsList = [
                [
                    'id' => 1,
                    'num' => '01',
                    'date' => date('d/m/Y', strtotime('-2 days')),
                    'raw_date' => date('Y-m-d', strtotime('-2 days')),
                    'time' => '09:30:00',
                    'area_sector' => 'Planta de Producción y Envasado',
                    'puesto_trabajo' => 'Operador de Envasado y Paletizado Manual',
                    'factor_riesgo' => 'Levantamiento y descenso manual de carga',
                    'num_trabajadores' => 4,
                    'nombres_trabajadores' => ['Juan Pérez', 'Mario Gómez', 'Carlos Huanca', 'Pedro Mamani'],
                    'edad' => '34',
                    'tiempo_exposicion_horas' => 8,
                    'procedimiento_escrito' => 'Si',
                    'capacitacion' => 'Si',
                    'fuerza_agarre' => 'Moderada',
                    'carga_peso_kg' => 18.5,
                    'distancia_m' => 4.2,
                    'ayuda_mecanica' => 'Sin ayuda mecánica disponible',
                    'descripcion_carga' => 'Cajas de cartón corrugado con frascos de vidrio (18.5 kg)',
                    'manifestacion_temprana' => 'Si',
                    'ubicacion_sintoma' => 'Espalda baja',
                    'tareas' => [
                        'Levantamiento de cajas desde línea transportadora a nivel de piso',
                        'Giro de tronco a 45° con carga sostenida',
                        'Apilamiento en pallet hasta 1.60 m de altura',
                    ],
                    'observaciones' => 'Se observa sobreesfuerzo lumbar continuo y posturas forzadas de flexión con torsión repetitiva.',
                    'image_path' => asset('img/reba_sample1.jpg'),
                    'images_count' => 3,
                    'registered_by' => 'Ing. Carlos Mendoza (Ergónomo Especialista)',
                    'location' => [
                        'lat' => -16.5000,
                        'lng' => -68.1500,
                        'zone' => '19K',
                        'easting' => 591240.50,
                        'northing' => 8175420.10,
                    ],
                    'tronco_base' => 3,
                    'tronco_mod' => 1,
                    'cuello_base' => 2,
                    'cuello_mod' => 1,
                    'piernas_base' => 1,
                    'piernas_mod' => 1,
                    'carga_fuerza' => 2,
                    'carga_brusca' => 1,
                    'brazo_base' => 3,
                    'brazo_abduccion' => 1,
                    'brazo_hombro_elevado' => 1,
                    'brazo_apoyo_gravedad' => 0,
                    'antebrazo_base' => 2,
                    'muneca_base' => 2,
                    'muneca_mod' => 1,
                    'agarre' => 2,
                    'actividad_estatica' => 0,
                    'actividad_repetitiva' => 1,
                    'actividad_inestable' => 1,
                ],
                [
                    'id' => 2,
                    'num' => '02',
                    'date' => date('d/m/Y', strtotime('-1 days')),
                    'raw_date' => date('Y-m-d', strtotime('-1 days')),
                    'time' => '11:15:00',
                    'area_sector' => 'Taller de Mantenimiento Mecánico',
                    'puesto_trabajo' => 'Mecánico de Mantenimiento de Molinos',
                    'factor_riesgo' => 'Posturas forzadas',
                    'num_trabajadores' => 2,
                    'nombres_trabajadores' => ['Roberto Vargas', 'Luis Condori'],
                    'edad' => '42',
                    'tiempo_exposicion_horas' => 6,
                    'procedimiento_escrito' => 'Si',
                    'capacitacion' => 'No',
                    'fuerza_agarre' => 'Intensa',
                    'carga_peso_kg' => 12.0,
                    'distancia_m' => 1.5,
                    'ayuda_mecanica' => 'Llave de impacto neumática',
                    'descripcion_carga' => 'Desmontaje de pernos de coraza y tapas laterales',
                    'manifestacion_temprana' => 'Si',
                    'ubicacion_sintoma' => 'Hombros',
                    'tareas' => [
                        'Ajuste de pernos en altura sobre hombro',
                        'Sostén de herramientas neumáticas en postura estática',
                    ],
                    'observaciones' => 'Trabajo con brazos elevados por encima de los 90° durante periodos prolongados.',
                    'image_path' => asset('img/reba_sample2.jpg'),
                    'images_count' => 2,
                    'registered_by' => 'Ing. Carlos Mendoza (Ergónomo Especialista)',
                    'location' => [
                        'lat' => -16.5020,
                        'lng' => -68.1480,
                        'zone' => '19K',
                        'easting' => 591450.20,
                        'northing' => 8175210.80,
                    ],
                    'tronco_base' => 2,
                    'tronco_mod' => 1,
                    'cuello_base' => 2,
                    'cuello_mod' => 0,
                    'piernas_base' => 2,
                    'piernas_mod' => 0,
                    'carga_fuerza' => 2,
                    'carga_brusca' => 0,
                    'brazo_base' => 4,
                    'brazo_abduccion' => 1,
                    'brazo_hombro_elevado' => 1,
                    'brazo_apoyo_gravedad' => 0,
                    'antebrazo_base' => 1,
                    'muneca_base' => 2,
                    'muneca_mod' => 1,
                    'agarre' => 1,
                    'actividad_estatica' => 1,
                    'actividad_repetitiva' => 0,
                    'actividad_inestable' => 1,
                ]
            ];

            // Calcular puntajes REBA para mock
            foreach ($measurementsList as &$m) {
                $calc = self::calculateRebaScore($m);
                $m = array_merge($m, $calc);
            }
            unset($m);
        }

        // Resumen técnico y estadísticas de riesgo
        $totalMeasurements = count($measurementsList);
        $maxScore = $totalMeasurements > 0 ? max(array_column($measurementsList, 'score_final')) : 0;
        $criticalPuesto = '';
        foreach ($measurementsList as $m) {
            if ($m['score_final'] === $maxScore) {
                $criticalPuesto = "{$m['num']} - {$m['puesto_trabajo']}";
                break;
            }
        }

        $riskCounts = [
            'inapreciable' => 0,
            'bajo' => 0,
            'medio' => 0,
            'alto' => 0,
            'muy_alto' => 0,
        ];
        foreach ($measurementsList as $m) {
            $key = str_replace(' ', '_', strtolower($m['risk_level']));
            if (isset($riskCounts[$key])) {
                $riskCounts[$key]++;
            }
        }

        $photoReportSettings = [
            'distribution' => '2x3',
            'selectedPoints' => array_column($measurementsList, 'id'),
        ];

        return view('measurements.ergonomia_reba.index', [
            'module' => $module,
            'installationName' => $installationName,
            'startDateRaw' => $startDateRaw,
            'endDateRaw' => $endDateRaw,
            'startDateFormatted' => $startDateFormatted,
            'endDateFormatted' => $endDateFormatted,
            'monitoringType' => $monitoringType,
            'equipmentName' => $equipmentName,
            'equipmentBrand' => $equipmentBrand,
            'equipmentModel' => $equipmentModel,
            'equipmentSerial' => $equipmentSerial,
            'equipmentImage' => $equipmentImage,
            'registeredByHeader' => $registeredByHeader,
            'staffList' => $staffList,
            'measurements' => $measurementsList,
            'totalMeasurements' => $totalMeasurements,
            'maxScore' => $maxScore,
            'criticalPuesto' => $criticalPuesto,
            'riskCounts' => $riskCounts,
            'photoReportSettings' => $photoReportSettings,
            'promptsData' => $module->anexo2_data['prompts_data']['general'] ?? [
                'prompt' => '',
                'analisis_tecnico' => '',
                'observaciones' => '',
                'recomendaciones' => '',
            ],
        ]);
    }

    /**
     * Guarda una nueva evaluación de puesto REBA.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'nullable|date',
            'measurement_time' => 'nullable|string|max:20',
            'area_sector' => 'required|string|max:255',
            'puesto_trabajo' => 'required|string|max:255',
            'factor_riesgo' => 'nullable|string|max:255',
            'num_trabajadores' => 'nullable|integer|min:1',
            'nombres_trabajadores' => 'nullable',
            'edad' => 'nullable|string|max:50',
            'tiempo_exposicion_horas' => 'nullable|numeric',
            'procedimiento_escrito' => 'nullable|string',
            'capacitacion' => 'nullable|string',
            'fuerza_agarre' => 'nullable|string',
            'carga_peso_kg' => 'nullable|numeric',
            'distancia_m' => 'nullable|numeric',
            'ayuda_mecanica' => 'nullable|string',
            'descripcion_carga' => 'nullable|string',
            'manifestacion_temprana' => 'nullable|string',
            'ubicacion_sintoma' => 'nullable|string',
            'tareas' => 'nullable',
            'observaciones' => 'nullable|string',
            'tronco_base' => 'required|integer',
            'cuello_base' => 'required|integer',
            'piernas_base' => 'required|integer',
            'brazo_base' => 'required|integer',
            'antebrazo_base' => 'required|integer',
            'muneca_base' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'utm_zone' => 'nullable|string',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'staff_id' => 'nullable|integer',
        ]);

        $calc = self::calculateRebaScore($request->all());

        $imagePath = null;
        $imagesPaths = [];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reba_measurements', 'public');
            $imagesPaths[] = $imagePath;
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('reba_measurements', 'public');
                    $imagesPaths[] = $path;
                    if (!$imagePath) $imagePath = $path;
                }
            }
        }

        $nombres = $request->input('nombres_trabajadores');
        if (is_string($nombres)) {
            $decoded = json_decode($nombres, true);
            $nombres = is_array($decoded) ? $decoded : array_map('trim', explode(',', $nombres));
        }

        $tareas = $request->input('tareas');
        if (is_string($tareas)) {
            $decoded = json_decode($tareas, true);
            $tareas = is_array($decoded) ? $decoded : array_map('trim', explode("\n", $tareas));
        }

        $measurement = new RebaMeasurement();
        $measurement->measurement_module_id = $module->id;
        $measurement->point_number = $request->input('point_number') ?: str_pad($module->rebaMeasurements()->count() + 1, 2, '0', STR_PAD_LEFT);
        $measurement->measurement_date = $request->input('measurement_date') ?: Carbon::now()->format('Y-m-d');
        $measurement->measurement_time = $request->input('measurement_time') ?: Carbon::now()->format('H:i:s');
        $measurement->area_sector = $request->input('area_sector');
        $measurement->puesto_trabajo = $request->input('puesto_trabajo');
        $measurement->factor_riesgo = $request->input('factor_riesgo');
        $measurement->num_trabajadores = $request->input('num_trabajadores') ?: 1;
        $measurement->nombres_trabajadores = $nombres;
        $measurement->edad = $request->input('edad');
        $measurement->tiempo_exposicion_horas = $request->input('tiempo_exposicion_horas');
        $measurement->procedimiento_escrito = $request->input('procedimiento_escrito') ?: 'Si';
        $measurement->capacitacion = $request->input('capacitacion') ?: 'Si';
        $measurement->fuerza_agarre = $request->input('fuerza_agarre');
        $measurement->carga_peso_kg = $request->input('carga_peso_kg');
        $measurement->distancia_m = $request->input('distancia_m');
        $measurement->ayuda_mecanica = $request->input('ayuda_mecanica');
        $measurement->descripcion_carga = $request->input('descripcion_carga');
        $measurement->manifestacion_temprana = $request->input('manifestacion_temprana');
        $measurement->ubicacion_sintoma = $request->input('ubicacion_sintoma');
        $measurement->tareas = $tareas;
        $measurement->observaciones = $request->input('observaciones');
        $measurement->registered_by = Auth::user() ? Auth::user()->name : 'Técnico Ergónomo';
        $measurement->staff_id = $request->input('staff_id');

        // Location
        $measurement->latitude = $request->input('latitude');
        $measurement->longitude = $request->input('longitude');
        $measurement->utm_zone = $request->input('utm_zone');
        $measurement->utm_easting = $request->input('utm_easting');
        $measurement->utm_northing = $request->input('utm_northing');

        // Body values
        $measurement->tronco_base = $request->input('tronco_base', 1);
        $measurement->tronco_mod = $request->input('tronco_mod', 0);
        $measurement->cuello_base = $request->input('cuello_base', 1);
        $measurement->cuello_mod = $request->input('cuello_mod', 0);
        $measurement->piernas_base = $request->input('piernas_base', 1);
        $pMod = (int)$request->input('piernas_mod', 0);
        if ($request->has('piernas_flexion_30_60') || $request->has('piernas_flexion_mas_60')) {
            $pMod = ($request->input('piernas_flexion_30_60') ? 1 : 0) + ($request->input('piernas_flexion_mas_60') ? 2 : 0);
        }
        $measurement->piernas_mod = $pMod;
        $measurement->carga_fuerza = $request->input('carga_fuerza', 0);
        $measurement->carga_brusca = $request->input('carga_brusca', 0);
        $measurement->brazo_base = $request->input('brazo_base', 1);
        $measurement->brazo_abduccion = $request->input('brazo_abduccion', 0);
        $measurement->brazo_hombro_elevado = $request->input('brazo_hombro_elevado', 0);
        $measurement->brazo_apoyo_gravedad = $request->input('brazo_apoyo_gravedad', 0);
        $measurement->antebrazo_base = $request->input('antebrazo_base', 1);
        $measurement->muneca_base = $request->input('muneca_base', 1);
        $measurement->muneca_mod = $request->input('muneca_mod', 0);
        $measurement->agarre = $request->input('agarre', 0);
        $measurement->actividad_estatica = $request->input('actividad_estatica', 0);
        $measurement->actividad_repetitiva = $request->input('actividad_repetitiva', 0);
        $measurement->actividad_inestable = $request->input('actividad_inestable', 0);

        // Scores
        $measurement->score_a = $calc['score_a'];
        $measurement->score_b = $calc['score_b'];
        $measurement->score_c = $calc['score_c'];
        $measurement->score_actividad = $calc['score_actividad'];
        $measurement->score_final = $calc['score_final'];
        $measurement->risk_level = $calc['risk_level'];
        $measurement->action_level = $calc['action_level'];
        $measurement->risk_theme = $calc['risk_theme'];
        $measurement->badge_class = $calc['badge_class'];

        if ($imagePath) {
            $measurement->image_path = $imagePath;
        }
        if (!empty($imagesPaths)) {
            $measurement->images = $imagesPaths;
        }

        $measurement->save();

        $module->points_completed = $module->rebaMeasurements()->count();
        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Evaluación de Ergonomía REBA registrada con éxito.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.ergonomia_reba', $moduleId)
            ->with('success', 'Evaluación REBA registrada con éxito.');
    }

    /**
     * Actualiza una evaluación existente.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $measurement = RebaMeasurement::findOrFail($measurementId);
        $currentUser = Auth::user();
        $registeredByName = $measurement->registered_by ?: ($currentUser ? $currentUser->name : 'Técnico Ergónomo');
        $staffId = $measurement->staff_id;
        if ($request->filled('staff_id')) {
            $staffId = $request->input('staff_id');
            $staff = Staff::find($staffId);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            }
        }

        // Imágenes y fotos restantes
        $existingImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        if (empty($existingImages) && !empty($measurement->image_path)) {
            $existingImages = [$measurement->image_path];
        }

        if ($request->has('remaining_images')) {
            $rawRemaining = $request->input('remaining_images');
            if (is_string($rawRemaining)) {
                $remDecoded = json_decode($rawRemaining, true);
                if (is_array($remDecoded)) {
                    $existingImages = array_values(array_filter($existingImages, function($img) use ($remDecoded) {
                        return in_array($img, $remDecoded) || in_array(basename($img), array_map('basename', $remDecoded));
                    }));
                }
            }
        }

        $newImages = [];
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reba_measurements', 'public');
            $newImages[] = Storage::url($imagePath);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('reba_measurements', 'public');
                    $newImages[] = Storage::url($path);
                }
            }
        }
        $finalImages = array_values(array_merge($existingImages, $newImages));
        $finalImagePath = $finalImages[0] ?? null;

        // Tareas y Trabajadores
        $nombres = $request->input('nombres_trabajadores');
        if (is_string($nombres)) {
            $nombres = json_decode($nombres, true) ?: array_filter(array_map('trim', explode(',', $nombres)));
        }

        $tareas = $request->input('tareas');
        if (is_string($tareas)) {
            $tareas = json_decode($tareas, true) ?: array_filter(array_map('trim', explode("\n", $tareas)));
        }

        $calc = self::calculateRebaScore($request->all());

        $piernasMod = (int)($request->input('piernas_mod') ?? $measurement->piernas_mod);
        if ($request->has('piernas_flexion_30_60') || $request->has('piernas_flexion_mas_60')) {
            $piernasMod = ($request->input('piernas_flexion_30_60') ? 1 : 0) + ($request->input('piernas_flexion_mas_60') ? 2 : 0);
        }

        $measurement->update([
            'point_number' => $request->input('point_number') ?: $measurement->point_number,
            'measurement_date' => $request->input('measurement_date') ?: $measurement->measurement_date,
            'measurement_time' => $request->input('measurement_time') ?: $measurement->measurement_time,
            'area_sector' => $request->input('area_sector') ?: $measurement->area_sector,
            'puesto_trabajo' => $request->input('puesto_trabajo') ?: $measurement->puesto_trabajo,
            'factor_riesgo' => $request->input('factor_riesgo') ?: $measurement->factor_riesgo,
            'num_trabajadores' => (int)($request->input('num_trabajadores') ?? $measurement->num_trabajadores),
            'nombres_trabajadores' => $nombres ?: $measurement->nombres_trabajadores,
            'edad' => $request->input('edad') ?: $measurement->edad,
            'tiempo_exposicion_horas' => $request->filled('tiempo_exposicion_horas') ? (float)$request->input('tiempo_exposicion_horas') : $measurement->tiempo_exposicion_horas,
            'procedimiento_escrito' => $request->input('procedimiento_escrito') ?: $measurement->procedimiento_escrito,
            'capacitacion' => $request->input('capacitacion') ?: $measurement->capacitacion,
            'fuerza_agarre' => $request->input('fuerza_agarre') ?: $measurement->fuerza_agarre,
            'carga_peso_kg' => $request->filled('carga_peso_kg') ? (float)$request->input('carga_peso_kg') : $measurement->carga_peso_kg,
            'distancia_m' => $request->filled('distancia_m') ? (float)$request->input('distancia_m') : $measurement->distancia_m,
            'ayuda_mecanica' => $request->input('ayuda_mecanica') ?: $measurement->ayuda_mecanica,
            'descripcion_carga' => $request->input('descripcion_carga') ?: $measurement->descripcion_carga,
            'manifestacion_temprana' => $request->input('manifestacion_temprana') ?: $measurement->manifestacion_temprana,
            'ubicacion_sintoma' => $request->input('ubicacion_sintoma') ?: $measurement->ubicacion_sintoma,
            'tareas' => $tareas ?: $measurement->tareas,
            'observaciones' => $request->input('observaciones') ?: $measurement->observaciones,
            'image_path' => $finalImagePath,
            'images' => !empty($finalImages) ? $finalImages : null,
            'location' => $request->input('location') ?: $measurement->location,
            'latitude' => $request->filled('latitude') ? (float)$request->input('latitude') : $measurement->latitude,
            'longitude' => $request->filled('longitude') ? (float)$request->input('longitude') : $measurement->longitude,
            'utm_zone' => $request->input('utm_zone') ?: $measurement->utm_zone,
            'utm_easting' => $request->filled('utm_easting') ? (float)$request->input('utm_easting') : $measurement->utm_easting,
            'utm_northing' => $request->filled('utm_northing') ? (float)$request->input('utm_northing') : $measurement->utm_northing,
            // Grupo A
            'tronco_base' => (int)($request->input('tronco_base') ?? $measurement->tronco_base),
            'tronco_mod' => (int)($request->input('tronco_mod') ?? $measurement->tronco_mod),
            'cuello_base' => (int)($request->input('cuello_base') ?? $measurement->cuello_base),
            'cuello_mod' => (int)($request->input('cuello_mod') ?? $measurement->cuello_mod),
            'piernas_base' => (int)($request->input('piernas_base') ?? $measurement->piernas_base),
            'piernas_mod' => $piernasMod,
            'carga_fuerza' => (int)($request->input('carga_fuerza') ?? $measurement->carga_fuerza),
            'carga_brusca' => (int)($request->input('carga_brusca') ?? $measurement->carga_brusca),
            // Grupo B
            'brazo_base' => (int)($request->input('brazo_base') ?? $measurement->brazo_base),
            'brazo_abduccion' => (int)($request->input('brazo_abduccion') ?? $measurement->brazo_abduccion),
            'brazo_hombro_elevado' => (int)($request->input('brazo_hombro_elevado') ?? $measurement->brazo_hombro_elevado),
            'brazo_apoyo_gravedad' => (int)($request->input('brazo_apoyo_gravedad') ?? $measurement->brazo_apoyo_gravedad),
            'antebrazo_base' => (int)($request->input('antebrazo_base') ?? $measurement->antebrazo_base),
            'muneca_base' => (int)($request->input('muneca_base') ?? $measurement->muneca_base),
            'muneca_mod' => (int)($request->input('muneca_mod') ?? $measurement->muneca_mod),
            'agarre' => (int)($request->input('agarre') ?? $measurement->agarre),
            // Actividad
            'actividad_estatica' => (int)($request->input('actividad_estatica') ?? $measurement->actividad_estatica),
            'actividad_repetitiva' => (int)($request->input('actividad_repetitiva') ?? $measurement->actividad_repetitiva),
            'actividad_inestable' => (int)($request->input('actividad_inestable') ?? $measurement->actividad_inestable),
            // Calculados
            'score_tabla_a' => $calc['score_tabla_a'] ?? $calc['score_a'],
            'score_a' => $calc['score_a'],
            'score_tabla_b' => $calc['score_tabla_b'] ?? $calc['score_b'],
            'score_b' => $calc['score_b'],
            'score_c' => $calc['score_c'],
            'score_actividad' => $calc['score_actividad'],
            'score_final' => $calc['score_final'],
            'risk_level' => $calc['risk_level'],
            'action_level' => $calc['action_level'],
            'risk_theme' => $calc['risk_theme'],
            'badge_class' => $calc['badge_class'],
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Evaluación REBA actualizada exitosamente.',
                'measurement' => $measurement,
                'calc' => $calc,
            ]);
        }

        return redirect()->route('modules.ergonomia_reba', $moduleId)
            ->with('success', 'Evaluación REBA actualizada exitosamente.');
    }

    /**
     * Elimina una evaluación.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $measurement = RebaMeasurement::find($measurementId);
        if ($measurement) {
            $measurement->delete();
        }

        $module = MeasurementModule::find($moduleId);
        if ($module && method_exists($module, 'rebaMeasurements')) {
            $module->points_completed = $module->rebaMeasurements()->count();
            $module->save();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Evaluación #{$measurementId} eliminada correctamente.",
            ]);
        }

        return redirect()->route('modules.ergonomia_reba', $moduleId)
            ->with('success', "Evaluación eliminada correctamente.");
    }

    /**
     * Actualiza el encabezado técnico del módulo.
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::find($moduleId);
        if ($module) {
            if ($request->has('installation_name')) {
                $module->installation_name = $request->input('installation_name');
            }
            if ($request->has('start_date')) {
                $module->start_date = $request->input('start_date');
            }
            if ($request->has('end_date')) {
                $module->end_date = $request->input('end_date');
            }
            if ($request->has('monitoring_type')) {
                $module->monitoring_type = $request->input('monitoring_type');
            }
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Datos técnicos del monitoreo guardados con éxito.',
        ]);
    }

    /**
     * Guarda la configuración del mosaico de reporte fotográfico.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        return response()->json([
            'success' => true,
            'message' => 'Configuración de catálogo fotográfico guardada con éxito.',
        ]);
    }

    /**
     * Guarda la información del Registro Anexo 2 (Factores de Riesgos Disergonómicos).
     */
    public function saveAnexo2(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $anexo2Data = $request->input('anexo2_data', []);

        $existing = $module->anexo2_data ?? [];
        if (!is_array($existing)) {
            $existing = [];
        }

        $merged = array_merge($existing, $anexo2Data);

        $module->anexo2_data = $merged;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro Anexo 2 guardado correctamente.',
            'anexo2_data' => $module->anexo2_data,
        ]);
    }

    /**
     * Guarda la información de Prompts, Análisis Técnico, Observaciones y Recomendaciones.
     */
    public function savePrompts(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $evaluationId = $request->input('evaluation_id');
        $prompt = $request->input('prompt', '');
        $analisisTecnico = $request->input('analisis_tecnico', '');
        $observaciones = $request->input('observaciones', '');
        $recomendaciones = $request->input('recomendaciones', '');

        $anexo2Data = $module->anexo2_data ?? [];
        if (!isset($anexo2Data['prompts_data']) || !is_array($anexo2Data['prompts_data'])) {
            $anexo2Data['prompts_data'] = [];
        }

        $key = $evaluationId ? (string)$evaluationId : 'general';
        $anexo2Data['prompts_data'][$key] = [
            'prompt' => $prompt,
            'analisis_tecnico' => $analisisTecnico,
            'observaciones' => $observaciones,
            'recomendaciones' => $recomendaciones,
            'updated_at' => now()->toDateTimeString(),
        ];

        $module->anexo2_data = $anexo2Data;
        $module->save();

        if ($evaluationId) {
            $measurement = $module->rebaMeasurements()->find($evaluationId);
            if ($measurement) {
                if ($observaciones) {
                    $measurement->observaciones = $observaciones;
                    $measurement->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Prompts y análisis técnico guardados correctamente.',
            'prompts_data' => $anexo2Data['prompts_data'][$key],
        ]);
    }

    /**
     * Genera automáticamente el Análisis Técnico, Observaciones y Recomendaciones utilizando Google Gemini AI (Método REBA).
     */
    public function generateAiContent(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $evaluationId = $request->input('evaluation_id');
        $customPrompt = trim($request->input('prompt', ''));

        // Cargar configuración de Gemini desde settings.json, services o .env
        $settingsFile = storage_path('app/settings.json');
        $apiKey = '';
        $model = 'gemini-3.8-flash';
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
            $apiKey = $settings['ai']['gemini_api_key'] ?? '';
            $model = $settings['ai']['gemini_model'] ?? 'gemini-3.8-flash';
        }
        if (empty($apiKey)) {
            $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '');
        }

        // Auto-reemplazo de modelos deprecados
        $deprecatedReplacements = [
            'gemini-1.5-flash' => 'gemini-3.8-flash',
            'gemini-1.5-pro' => 'gemini-3.1-pro-preview',
            'gemini-2.0-flash' => 'gemini-3.8-flash',
            'gemini-1.0-pro' => 'gemini-3.8-flash',
            'gemini-2.5-flash' => 'gemini-3.8-flash',
            'gemini-2.5-pro' => 'gemini-3.1-pro-preview',
        ];
        if (isset($deprecatedReplacements[$model])) {
            $model = $deprecatedReplacements[$model];
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'No se ha configurado la clave API de Google Gemini. Ve a la página de Configuración para ingresar tu API Key.'
            ], 422);
        }

        // Obtener datos de la evaluación
        $measurement = null;
        if ($evaluationId) {
            $measurement = $module->rebaMeasurements()->find($evaluationId);
        }
        if (!$measurement) {
            $measurement = $module->rebaMeasurements()->first();
        }

        $puesto = $measurement ? ($measurement->puesto_trabajo ?: 'Puesto Operativo') : 'Operario de Producción';
        $area = $measurement ? ($measurement->area_sector ?: 'Producción') : 'Producción';
        $trabajador = $measurement ? (is_array($measurement->nombres_trabajadores) ? implode(', ', array_filter($measurement->nombres_trabajadores)) : ($measurement->nombres_trabajadores ?: 'Personal Operativo')) : 'Carlos Rene Ichuta Ichuta';
        $tiempo = $measurement ? ($measurement->tiempo_exposicion_horas ?: 8) : 8;

        $calc = $measurement ? self::calculateRebaScore($measurement->toArray()) : self::calculateRebaScore([]);
        $scoreA = $calc['score_a'] ?? 4;
        $scoreB = $calc['score_b'] ?? 4;
        $scoreC = $calc['score_c'] ?? 4;
        $scoreActividad = $calc['score_actividad'] ?? 1;
        $scoreFinal = $calc['score_final'] ?? 5;
        $riskLevel = $calc['risk_level'] ?? 'Medio';
        $actionLevel = $calc['action_level'] ?? 'Es necesaria la acción';
        $troncoFinal = $calc['tronco_final'] ?? 2;
        $cuelloFinal = $calc['cuello_final'] ?? 2;
        $piernasFinal = $calc['piernas_final'] ?? 1;
        $brazoFinal = $calc['brazo_final'] ?? 2;
        $antebrazoFinal = $calc['antebrazo_final'] ?? 1;
        $munecaFinal = $calc['muneca_final'] ?? 1;
        $scoreTablaA = $calc['score_tabla_a'] ?? 3;
        $scoreTablaB = $calc['score_tabla_b'] ?? 3;

        $promptAnalisis = trim($request->input('prompt_analisis', ''));
        $promptObservaciones = trim($request->input('prompt_observaciones', ''));
        $promptRecomendaciones = trim($request->input('prompt_recomendaciones', ''));

        // Si no vienen en el request, consultar si hay guardados en anexo2_data
        $anexo2Data = $module->anexo2_data ?? [];
        $key = $evaluationId ? (string)$evaluationId : 'general';
        $savedPromptData = $anexo2Data['prompts_data'][$key] ?? [];

        if (empty($customPrompt) && !empty($savedPromptData['prompt'])) {
            $customPrompt = $savedPromptData['prompt'];
        }
        if (empty($promptAnalisis) && !empty($savedPromptData['analisis_tecnico'])) {
            $promptAnalisis = $savedPromptData['analisis_tecnico'];
        }
        if (empty($promptObservaciones) && !empty($savedPromptData['observaciones'])) {
            $promptObservaciones = $savedPromptData['observaciones'];
        }
        if (empty($promptRecomendaciones) && !empty($savedPromptData['recomendaciones'])) {
            $promptRecomendaciones = $savedPromptData['recomendaciones'];
        }

        $basePrompt = $customPrompt ?: "Actúa como un especialista senior en Ergonomía Ocupacional y Salud en el Trabajo (SySO).
Realiza una evaluación biomecánica y ergonómica exhaustiva del puesto de trabajo '{$puesto}' (Área: {$area}, Trabajador: {$trabajador}, Exposición: {$tiempo} hrs/día), evaluado mediante el método REBA (Rapid Entire Body Assessment - NTP 601 / ISO 11226 / UNE-EN 1005-4) con los siguientes resultados normativos:

1. PUNTUACIÓN FINAL REBA: {$scoreFinal}/15 (Nivel de Riesgo: {$riskLevel}, Acción: {$actionLevel}).
2. Puntuación Grupo A (Tronco: {$troncoFinal}, Cuello: {$cuelloFinal}, Piernas: {$piernasFinal} -> Tabla A: {$scoreTablaA}, Puntuación A con Carga/Fuerza: {$scoreA}).
3. Puntuación Grupo B (Brazo: {$brazoFinal}, Antebrazo: {$antebrazoFinal}, Muñeca: {$munecaFinal} -> Tabla B: {$scoreTablaB}, Puntuación B con Agarre: {$scoreB}).
4. Puntuación Tabla C (A vs B): {$scoreC}.
5. Puntuación de Actividad Muscular: +{$scoreActividad}.";

        $guidelinesAT = $promptAnalisis 
            ? "Pautas para este apartado:\n{$promptAnalisis}\n(Redacta formalmente en prosa técnica sin incluir títulos ni encabezados)"
            : "(Escribe aquí únicamente la redacción técnica del apartado 4. Análisis técnico del puesto en 1 o 2 párrafos formales y profesionales en prosa, sin incluir títulos)";

        $guidelinesObs = $promptObservaciones 
            ? "Pautas para este apartado:\n{$promptObservaciones}\n(Redacta cada observación en un renglón independiente comenzando con '• '. No uses asteriscos * ni texto corrido en un solo párrafo)"
            : "(Escribe aquí las observaciones del apartado 5 en líneas separadas, cada una comenzando con '• '. Redacta 3 o 4 observaciones concisas sin títulos ni asteriscos *)";

        $guidelinesRec = $promptRecomendaciones 
            ? "Pautas para este apartado:\n{$promptRecomendaciones}\n(Redacta cada recomendación en un renglón independiente comenzando con '• '. No uses asteriscos * ni texto corrido en un solo párrafo)"
            : "(Escribe aquí las recomendaciones del apartado 6 en líneas separadas, cada una comenzando con '• '. Redacta 3 o 4 recomendaciones prioritarias y preventivas sin títulos ni asteriscos *)";

        $fullPrompt = $basePrompt . "\n\n" . "INSTRUCCIONES DE FORMATO OBLIGATORIO:
Debes responder en español estructurando la respuesta EXACTAMENTE con los siguientes 3 encabezados delimitadores:

=== ANALISIS_TECNICO ===
{$guidelinesAT}

=== OBSERVACIONES ===
{$guidelinesObs}

=== RECOMENDACIONES ===
{$guidelinesRec}";

        try {
            $encodedKey = urlencode($apiKey);
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$encodedKey}";
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $fullPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.35,
                        'maxOutputTokens' => 4096,
                    ]
                ]);

            if (!$response->successful() && $response->status() === 503 && $model !== 'gemini-3.8-flash') {
                $model = 'gemini-3.8-flash';
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$encodedKey}";
                $response = Http::withoutVerifying()
                    ->timeout(60)
                    ->withHeaders([
                        'x-goog-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($url, [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $fullPrompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.35,
                            'maxOutputTokens' => 4096,
                        ]
                    ]);
            }

            if (!$response->successful()) {
                $status = $response->status();
                $err = $response->json('error.message') ?? $response->body();
                $hint = '';
                if ($status === 401 || $status === 403) {
                    $hint = ' (Asegúrate de que la clave API sea válida y tenga permisos en Google AI Studio: https://aistudio.google.com/app/apikey).';
                } elseif ($status === 503) {
                    $hint = ' (El servicio de Google Gemini está saturado temporalmente. Por favor intenta en unos segundos).';
                }
                return response()->json([
                    'success' => false,
                    'message' => "Error de la API de Gemini ({$status}): {$err}{$hint}"
                ], 400);
            }

            $rawText = trim($response->json('candidates.0.content.parts.0.text') ?? '');

            // Parsear secciones delimitadas
            $analisisTecnico = '';
            $observaciones = '';
            $recomendaciones = '';

            if (preg_match('/===\s*ANALISIS_TECNICO\s*===(.*?)(===\s*OBSERVACIONES\s*===|$)/is', $rawText, $matchAT)) {
                $analisisTecnico = trim($matchAT[1]);
            }
            if (preg_match('/===\s*OBSERVACIONES\s*===(.*?)(===\s*RECOMENDACIONES\s*===|$)/is', $rawText, $matchObs)) {
                $observaciones = trim($matchObs[1]);
            }
            if (preg_match('/===\s*RECOMENDACIONES\s*===(.*?)$/is', $rawText, $matchRec)) {
                $recomendaciones = trim($matchRec[1]);
            }

            // Limpiar asteriscos y formatear viñetas limpias
            $cleanBullets = function($text) {
                if (empty($text)) return '';
                $lines = explode("\n", $text);
                $cleaned = [];
                foreach ($lines as $line) {
                    $l = trim($line);
                    if (empty($l)) continue;
                    $l = preg_replace('/^\s*[\*\-\•\–\—]\s*/u', '', $l);
                    $l = preg_replace('/^\s*\d+[\.\)]\s*/', '', $l);
                    $l = str_replace(['**', '*', '__', '_'], '', $l);
                    $l = trim($l);
                    if (!empty($l)) {
                        $cleaned[] = '• ' . $l;
                    }
                }
                return implode("\n", $cleaned);
            };

            $cleanProse = function($text) {
                if (empty($text)) return '';
                $text = str_replace(['**', '*', '__', '_'], '', $text);
                $lines = explode("\n", $text);
                $cleanedLines = [];
                foreach ($lines as $line) {
                    $l = trim($line);
                    if (!empty($l)) {
                        $cleanedLines[] = $l;
                    }
                }
                return implode("\n\n", $cleanedLines);
            };

            $analisisTecnico = $cleanProse($analisisTecnico);
            $observaciones = $cleanBullets($observaciones);
            $recomendaciones = $cleanBullets($recomendaciones);

            // Guardar en anexo2_data
            if (!isset($anexo2Data['prompts_data']) || !is_array($anexo2Data['prompts_data'])) {
                $anexo2Data['prompts_data'] = [];
            }
            $anexo2Data['prompts_data'][$key] = [
                'prompt' => $customPrompt,
                'analisis_tecnico' => $analisisTecnico,
                'observaciones' => $observaciones,
                'recomendaciones' => $recomendaciones,
                'updated_at' => now()->toDateTimeString(),
            ];
            $module->anexo2_data = $anexo2Data;
            $module->save();

            if ($measurement && $observaciones) {
                $measurement->observaciones = $observaciones;
                $measurement->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Contenido generado y estructurado con éxito mediante Google Gemini.',
                'analisis_tecnico' => $analisisTecnico,
                'observaciones' => $observaciones,
                'recomendaciones' => $recomendaciones,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con el servicio de IA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra la página completa de Tablas y Matrices Normativas REBA con navegación interactiva por 4 pasos.
     */
    public function showTables(Request $request, $id)
    {
        $currentUser = Auth::user();
        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff', 'rebaMeasurements'])->find($id);

        if (!$module) {
            $module = new MeasurementModule();
            $module->id = (int)$id;
            $module->name = 'Monitoreo de Ergonomía REBA';
            $module->type = 'ergonomia_reba';
        }

        $project = $module->project;
        $company = $project ? $project->company : null;
        $projectName = $project ? $project->name : 'Estudio de Ergonomía Ocupacional (REBA)';
        
        // Razón social y dirección priorizan los campos definidos en el Proyecto
        $projectRazonSocial = ($project && !empty($project->razon_social)) 
            ? $project->razon_social 
            : ($company ? ($company->legal_name ?: $company->name) : 'PACHABOL');

        $projectDireccion = ($project && !empty($project->direccion)) 
            ? $project->direccion 
            : (($company && $company->address) ? $company->address : '25 de Julio Alejandria Nº 8345 UV Edificio');

        $companyName = $company ? $company->name : $projectRazonSocial;
        $companyAddress = $projectDireccion;
        $installationName = $module->installation_name ?: ($projectName . ($companyName ? " - {$companyName}" : ''));

        // Cargar evaluación específica si se pasa evaluation_id por query string
        $evaluationId = $request->query('evaluation_id') ?: $request->input('evaluation_id');
        $selectedMeasurement = null;
        if ($evaluationId) {
            $selectedMeasurement = $module->rebaMeasurements()->find($evaluationId);
        }
        if (!$selectedMeasurement && $module->exists) {
            $selectedMeasurement = $module->rebaMeasurements()->first();
        }

        // Tareas y nombres de trabajadores desde la evaluación
        $nombresTrabajador = '';
        $tarea1 = '---';
        $tarea2 = '---';
        $tarea3 = '---';

        if ($selectedMeasurement) {
            if (!empty($selectedMeasurement->nombres_trabajadores)) {
                $rawNombres = is_array($selectedMeasurement->nombres_trabajadores) 
                    ? $selectedMeasurement->nombres_trabajadores 
                    : (json_decode($selectedMeasurement->nombres_trabajadores, true) ?: [$selectedMeasurement->nombres_trabajadores]);
                $nombresTrabajador = implode(', ', array_filter($rawNombres));
            }

            if (!empty($selectedMeasurement->tareas)) {
                $rawTareas = is_array($selectedMeasurement->tareas) 
                    ? $selectedMeasurement->tareas 
                    : (json_decode($selectedMeasurement->tareas, true) ?: [$selectedMeasurement->tareas]);
                
                $cleanedTareas = array_values(array_filter(array_map('trim', $rawTareas), function($val) {
                    return $val !== '' && $val !== null;
                }));

                if (isset($cleanedTareas[0]) && $cleanedTareas[0] !== '') $tarea1 = $cleanedTareas[0];
                if (isset($cleanedTareas[1]) && $cleanedTareas[1] !== '') $tarea2 = $cleanedTareas[1];
                if (isset($cleanedTareas[2]) && $cleanedTareas[2] !== '') $tarea3 = $cleanedTareas[2];
            } elseif (!empty($selectedMeasurement->tarea_analizada)) {
                $tarea1 = trim($selectedMeasurement->tarea_analizada);
            }
        }

        // Cargar o Inicializar Datos de Anexo 2
        $anexo2Data = $module->anexo2_data ?? [];

        // Matriz de factores limpios por defecto
        $cleanFactors = [
            'A' => ['name' => 'Levantamiento y descenso', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
            'B' => ['name' => 'Empuje / arrastre', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
            'C' => ['name' => 'Transporte', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
            'D' => ['name' => 'Bipedestación', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
            'E' => ['name' => 'Movimientos repetitivos', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
            'F' => ['name' => 'Postura forzada', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
            'G' => ['name' => 'Vibraciones', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
            'H' => ['name' => 'Confort térmico', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
            'I' => ['name' => 'Estrés de contacto', 't1' => false, 't2' => false, 't3' => false, 'horas' => '', 'r1' => '', 'r2' => '', 'r3' => ''],
        ];

        $savedFactors = $module->anexo2_data['factors'] ?? [];
        $mergedFactors = $cleanFactors;
        if (is_array($savedFactors) && !empty($savedFactors)) {
            foreach ($cleanFactors as $code => $def) {
                if (isset($savedFactors[$code]) && is_array($savedFactors[$code])) {
                    $mergedFactors[$code] = array_merge($def, $savedFactors[$code]);
                }
            }
        }

        // Construir datos sincronizados con proyecto y registro seleccionado
        $anexo2Data = [
            'razon_social' => $projectRazonSocial,
            'direccion' => $projectDireccion,
            'area_sector' => $selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Producción') : ($anexo2Data['area_sector'] ?? 'Producción'),
            'puesto_trabajo' => $selectedMeasurement ? ($selectedMeasurement->puesto_trabajo ?: 'Operario de Producción') : ($anexo2Data['puesto_trabajo'] ?? 'Operario de Producción'),
            'num_trabajadores' => $selectedMeasurement ? ($selectedMeasurement->num_trabajadores ?? 1) : ($anexo2Data['num_trabajadores'] ?? 1),
            'procedimiento_escrito' => $selectedMeasurement ? ($selectedMeasurement->procedimiento_escrito ?: 'SI') : ($anexo2Data['procedimiento_escrito'] ?? 'SI'),
            'capacitacion' => $selectedMeasurement ? ($selectedMeasurement->capacitacion ?: 'Si') : ($anexo2Data['capacitacion'] ?? 'Si'),
            'nombre_trabajador' => $selectedMeasurement ? $nombresTrabajador : ($anexo2Data['nombre_trabajador'] ?? ''),
            'manifestacion_temprana' => $selectedMeasurement ? ($selectedMeasurement->manifestacion_temprana ?: 'No') : ($anexo2Data['manifestacion_temprana'] ?? 'No'),
            'ubicacion_sintoma' => $selectedMeasurement ? ($selectedMeasurement->ubicacion_sintoma ?: 'Ninguna') : ($anexo2Data['ubicacion_sintoma'] ?? 'Ninguna'),
            'tareas' => [
                'tarea_1' => $tarea1,
                'tarea_2' => $tarea2,
                'tarea_3' => $tarea3,
            ],
            'factors' => $mergedFactors,
            'profesional_nombre' => $module->anexo2_data['profesional_nombre'] ?? '',
            'profesional_registro' => $module->anexo2_data['profesional_registro'] ?? '',
            'profesional_fecha' => $module->anexo2_data['profesional_fecha'] ?? '',
            'prompts_data' => $module->anexo2_data['prompts_data'] ?? [],
            'step4_data' => $module->anexo2_data['step4_data'] ?? [],
        ];

        // Obtener todas las mediciones para selectores
        $allMeasurements = $module->rebaMeasurements()->orderBy('point_number', 'asc')->get();
        foreach ($allMeasurements as $mItem) {
            $mCalc = self::calculateRebaScore($mItem->toArray());
            $mItem->calculated_score_final = $mCalc['score_final'];
            $mItem->calculated_risk_level = $mCalc['risk_level'];
        }

        // Calcular puntajes de las matrices para el registro seleccionado
        if ($selectedMeasurement) {
            $rebaScores = self::calculateRebaScore($selectedMeasurement->toArray());
        } else {
            $rebaScores = self::calculateRebaScore([]);
        }

        // Cargar datos de prompts y análisis (por defecto en blanco)
        $promptsStorage = $module->anexo2_data['prompts_data'] ?? [];
        $measKey = $selectedMeasurement ? (string)$selectedMeasurement->id : 'general';
        $promptsData = $promptsStorage[$measKey] ?? $promptsStorage['general'] ?? [
            'prompt' => '',
            'analisis_tecnico' => '',
            'observaciones' => '',
            'recomendaciones' => '',
        ];

        return view('measurements.ergonomia_reba.tables', compact(
            'module',
            'project',
            'projectName',
            'companyName',
            'companyAddress',
            'projectRazonSocial',
            'projectDireccion',
            'installationName',
            'anexo2Data',
            'selectedMeasurement',
            'allMeasurements',
            'rebaScores',
            'promptsData'
        ));
    }
}
