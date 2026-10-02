<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\RosaMeasurement;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ErgonomiaRosaController extends Controller
{
    /**
     * Tabla A1 Lookup Matrix ROSA: Asiento (Seat Pan)
     * [Seat Height (1-5)][Seat Depth (1-3)] -> Score Asiento (1-7)
     */
    private static $TABLE_A1 = [
        1 => [1 => 1, 2 => 2, 3 => 3],
        2 => [1 => 2, 2 => 3, 3 => 4],
        3 => [1 => 3, 2 => 4, 3 => 5],
        4 => [1 => 4, 2 => 5, 3 => 6],
        5 => [1 => 5, 2 => 6, 3 => 7],
    ];

    /**
     * Tabla A2 Lookup Matrix ROSA: Soporte (Armrest & Backrest)
     * [Armrest (1-5)][Backrest (1-5)] -> Score Soporte (1-9)
     */
    private static $TABLE_A2 = [
        1 => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5],
        2 => [1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6],
        3 => [1 => 3, 2 => 4, 3 => 5, 4 => 6, 5 => 7],
        4 => [1 => 4, 2 => 5, 3 => 6, 4 => 7, 5 => 8],
        5 => [1 => 5, 2 => 6, 3 => 7, 4 => 8, 5 => 9],
    ];

    /**
     * Tabla A Lookup Matrix ROSA: Silla (Chair)
     * [Pan (1-7)][Support (1-9)] -> Score Silla Base (1-9)
     */
    private static $TABLE_A = [
        1 => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9],
        2 => [1 => 2, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9],
        3 => [1 => 3, 2 => 3, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9],
        4 => [1 => 4, 2 => 4, 3 => 4, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9],
        5 => [1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9],
        6 => [1 => 6, 2 => 6, 3 => 6, 4 => 6, 5 => 6, 6 => 6, 7 => 7, 8 => 8, 9 => 9],
        7 => [1 => 7, 2 => 7, 3 => 7, 4 => 7, 5 => 7, 6 => 7, 7 => 7, 8 => 8, 9 => 9],
    ];

    /**
     * Tabla B Lookup Matrix ROSA: Pantalla y Teléfono (Monitor & Phone)
     * [Monitor (1-7)][Telephone (1-7)] -> Score B (1-9)
     */
    private static $TABLE_B = [
        1 => [1 => 1, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 6],
        2 => [1 => 1, 2 => 2, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 6],
        3 => [1 => 2, 2 => 2, 3 => 3, 4 => 3, 5 => 4, 6 => 5, 7 => 6],
        4 => [1 => 3, 2 => 3, 3 => 3, 4 => 4, 5 => 4, 6 => 5, 7 => 6],
        5 => [1 => 4, 2 => 4, 3 => 4, 4 => 4, 5 => 5, 6 => 5, 7 => 6],
        6 => [1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5, 6 => 6, 7 => 6],
        7 => [1 => 6, 2 => 6, 3 => 6, 4 => 6, 5 => 6, 6 => 6, 7 => 7],
    ];

    /**
     * Tabla C Lookup Matrix ROSA: Ratón y Teclado (Mouse & Keyboard)
     * [Mouse (1-7)][Keyboard (1-7)] -> Score C (1-9)
     */
    private static $TABLE_C = [
        1 => [1 => 1, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 6],
        2 => [1 => 1, 2 => 2, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 6],
        3 => [1 => 2, 2 => 2, 3 => 3, 4 => 3, 5 => 4, 6 => 5, 7 => 6],
        4 => [1 => 3, 2 => 3, 3 => 3, 4 => 4, 5 => 4, 6 => 5, 7 => 6],
        5 => [1 => 4, 2 => 4, 3 => 4, 4 => 4, 5 => 5, 6 => 5, 7 => 6],
        6 => [1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5, 6 => 6, 7 => 6],
        7 => [1 => 6, 2 => 6, 3 => 6, 4 => 6, 5 => 6, 6 => 6, 7 => 7],
    ];

    /**
     * Tabla D Lookup Matrix ROSA: Periféricos & Pantalla/Teléfono
     * [Score B (1-8)][Score C (1-8)] -> Score D (1-10)
     */
    private static $TABLE_D = [
        1 => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
        2 => [1 => 2, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
        3 => [1 => 3, 2 => 3, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
        4 => [1 => 4, 2 => 4, 3 => 4, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
        5 => [1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
        6 => [1 => 6, 2 => 6, 3 => 6, 4 => 6, 5 => 6, 6 => 6, 7 => 7, 8 => 8],
        7 => [1 => 7, 2 => 7, 3 => 7, 4 => 7, 5 => 7, 6 => 7, 7 => 7, 8 => 8],
        8 => [1 => 8, 2 => 8, 3 => 8, 4 => 8, 5 => 8, 6 => 8, 7 => 8, 8 => 8],
    ];

    /**
     * Tabla E Lookup Matrix ROSA: Puntuación ROSA Inicial
     * [Score A (Chair) (1-10)][Score D (Peripherals) (1-10)] -> Score E (1-10)
     */
    private static $TABLE_E = [
        1  => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
        2  => [1 => 2, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
        3  => [1 => 3, 2 => 3, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
        4  => [1 => 4, 2 => 4, 3 => 4, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
        5  => [1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
        6  => [1 => 6, 2 => 6, 3 => 6, 4 => 6, 5 => 6, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
        7  => [1 => 7, 2 => 7, 3 => 7, 4 => 7, 5 => 7, 6 => 7, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
        8  => [1 => 8, 2 => 8, 3 => 8, 4 => 8, 5 => 8, 6 => 8, 7 => 8, 8 => 8, 9 => 9, 10 => 10],
        9  => [1 => 9, 2 => 9, 3 => 9, 4 => 9, 5 => 9, 6 => 9, 7 => 9, 8 => 9, 9 => 9, 10 => 10],
        10 => [1 => 10, 2 => 10, 3 => 10, 4 => 10, 5 => 10, 6 => 10, 7 => 10, 8 => 10, 9 => 10, 10 => 10],
    ];

    /**
     * Calcula la puntuación completa ROSA (Rapid Office Strain Assessment) y clasifica el riesgo.
     */
    public static function calculateRosaScore(array $data): array
    {
        // 1. Silla (Tabla A)
        $alturaBase = (int)($data['altura_asiento_base'] ?? 1);
        $alturaMod = (int)($data['altura_asiento_mod'] ?? 0);
        $alturaFinal = min(5, max(1, $alturaBase + $alturaMod));

        $profundidadBase = (int)($data['profundidad_base'] ?? 1);
        $profundidadMod = (int)($data['profundidad_mod'] ?? 0);
        $profundidadFinal = min(3, max(1, $profundidadBase + $profundidadMod));

        $asientoScore = self::$TABLE_A1[$alturaFinal][$profundidadFinal] ?? 1;

        $reposabrazosBase = (int)($data['reposabrazos_base'] ?? 1);
        $reposabrazosMod = (int)($data['reposabrazos_mod'] ?? 0);
        $reposabrazosFinal = min(5, max(1, $reposabrazosBase + $reposabrazosMod));

        $respaldoBase = (int)($data['respaldo_base'] ?? 1);
        $respaldoMod = (int)($data['respaldo_mod'] ?? 0);
        $respaldoFinal = min(5, max(1, $respaldoBase + $respaldoMod));

        $soporteScore = self::$TABLE_A2[$reposabrazosFinal][$respaldoFinal] ?? 1;

        $asientoIdx = min(7, max(1, $asientoScore));
        $soporteIdx = min(9, max(1, $soporteScore));
        $sillaBaseScore = self::$TABLE_A[$asientoIdx][$soporteIdx] ?? 1;

        $sillaTiempo = (int)($data['silla_tiempo_uso'] ?? 0);
        $scoreA = min(10, max(1, $sillaBaseScore + $sillaTiempo));

        // 2. Pantalla y Teléfono (Tabla B)
        $pantallaBase = (int)($data['pantalla_base'] ?? 1);
        $pantallaMod = (int)($data['pantalla_mod'] ?? 0);
        $pantallaTiempo = (int)($data['pantalla_tiempo'] ?? 0);
        $pantallaScore = min(7, max(1, $pantallaBase + $pantallaMod + $pantallaTiempo));

        $telefonoBase = (int)($data['telefono_base'] ?? 1);
        $telefonoMod = (int)($data['telefono_mod'] ?? 0);
        $telefonoTiempo = (int)($data['telefono_tiempo'] ?? 0);
        $telefonoScore = min(7, max(1, $telefonoBase + $telefonoMod + $telefonoTiempo));

        $scoreB = self::$TABLE_B[$pantallaScore][$telefonoScore] ?? 1;

        // 3. Ratón y Teclado (Tabla C)
        $ratonBase = (int)($data['raton_base'] ?? 1);
        $ratonMod = (int)($data['raton_mod'] ?? 0);
        $ratonTiempo = (int)($data['raton_tiempo'] ?? 0);
        $ratonScore = min(7, max(1, $ratonBase + $ratonMod + $ratonTiempo));

        $tecladoBase = (int)($data['teclado_base'] ?? 1);
        $tecladoMod = (int)($data['teclado_mod'] ?? 0);
        $tecladoTiempo = (int)($data['teclado_tiempo'] ?? 0);
        $tecladoScore = min(7, max(1, $tecladoBase + $tecladoMod + $tecladoTiempo));

        $scoreC = self::$TABLE_C[$ratonScore][$tecladoScore] ?? 1;

        // 4. Periféricos & Pantalla/Teléfono (Tabla D)
        $scoreBIdx = min(8, max(1, $scoreB));
        $scoreCIdx = min(8, max(1, $scoreC));
        $scoreD = self::$TABLE_D[$scoreBIdx][$scoreCIdx] ?? 1;

        // 5. ROSA Inicial y Final (Tabla E)
        $scoreAIdx = min(10, max(1, $scoreA));
        $scoreDIdx = min(10, max(1, $scoreD));
        $scoreE = self::$TABLE_E[$scoreAIdx][$scoreDIdx] ?? 1;

        // 6. Actividad Muscular
        $scoreActividad = 0;

        // 7. Score Final ROSA (1-10)
        $scoreFinal = min(10, max(1, $scoreE));

        // 8. Clasificación de Riesgo y Acción
        if ($scoreFinal <= 2) {
            $riskLevel = 'Inapreciable';
            $actionLevel = 'Nivel 1: Postura óptima, no se requiere acción';
            $riskTheme = 'emerald';
            $badgeClass = 'risk-inapreciable';
        } elseif ($scoreFinal <= 4) {
            $riskLevel = 'Bajo';
            $actionLevel = 'Nivel 2: Riesgo bajo, considerar mejoras';
            $riskTheme = 'lime';
            $badgeClass = 'risk-bajo';
        } elseif ($scoreFinal === 5) {
            $riskLevel = 'Medio';
            $actionLevel = 'Nivel 3: Nivel de alerta, es necesaria acción pronto';
            $riskTheme = 'amber';
            $badgeClass = 'risk-medio';
        } elseif ($scoreFinal <= 8) {
            $riskLevel = 'Alto';
            $actionLevel = 'Nivel 4: Es necesaria la acción ergonómica pronto';
            $riskTheme = 'orange';
            $badgeClass = 'risk-alto';
        } else {
            $riskLevel = 'Muy Alto';
            $actionLevel = 'Nivel 5: Es necesaria la intervención ergonómica de inmediato';
            $riskTheme = 'red';
            $badgeClass = 'risk-muy-alto';
        }

        return [
            'altura_final' => $alturaFinal,
            'profundidad_final' => $profundidadFinal,
            'asiento_score' => $asientoScore,
            'reposabrazos_final' => $reposabrazosFinal,
            'respaldo_final' => $respaldoFinal,
            'soporte_score' => $soporteScore,
            'silla_base_score' => $sillaBaseScore,
            'score_a' => $scoreA,
            'pantalla_score' => $pantallaScore,
            'telefono_score' => $telefonoScore,
            'score_b' => $scoreB,
            'raton_score' => $ratonScore,
            'teclado_score' => $tecladoScore,
            'score_c' => $scoreC,
            'score_d' => $scoreD,
            'score_e' => $scoreE,
            'score_actividad' => $scoreActividad,
            'score_final' => $scoreFinal,
            'risk_level' => $riskLevel,
            'action_level' => $actionLevel,
            'risk_theme' => $riskTheme,
            'badge_class' => $badgeClass,
        ];
    }

    /**
     * Muestra la vista principal de monitoreo de Ergonomía ROSA.
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
            $module->name = 'Ergonomía ROSA';
            $module->key = 'ergonomia_rosa';
            $module->description = 'Evaluación ergonómica de puestos de oficina y pantallas mediante el método ROSA (Rapid Office Strain Assessment - ISO 9241 / NTP 601).';
            $module->points_total = 8;
            $module->points_completed = 0;
            $module->start_date = Carbon::now()->subDays(3);
            $module->end_date = Carbon::now();
            $module->monitoring_type = 'Monitoreo Ergonómico Puestos PVD (Método ROSA)';
            $module->installation_name = 'Oficinas Centrales — Corporación Minera';
            $module->calibration_equipment = 'Luxómetro Digital, Goniómetro & Cámara Ergonómica';
        }

        $projectName = ($module->project) ? $module->project->name : 'Proyecto Industrial Alpha';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : 'Empresa Principal';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : date('Y-m-d', strtotime('-3 days'));
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : date('Y-m-d');
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : date('d/m/Y', strtotime('-3 days'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : date('d/m/Y');

        $monitoringType = $module->monitoring_type ?: 'Ergonomía ROSA (Puestos con PVD - ISO 9241)';

        // Equipo Asignado
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Cinta Métrica & Goniómetro 360°');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Baseline / Sony') : 'Baseline Ergonomics';
        $equipmentModel = $equipment ? ($equipment->model ?: 'Office Ergocheck Pro') : 'ROSA Professional Kit';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'ROSA-992104') : 'ROSA-992104';
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
        $dbMeasurements = ($module->exists && method_exists($module, 'rosaMeasurements'))
            ? $module->rosaMeasurements()->with('staff')->get()
            : collect([]);

        if ($dbMeasurements->isNotEmpty()) {
            $measurementsList = $dbMeasurements->map(function ($item, $index) use ($assignedStaff, $currentUser) {
                $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
                if (empty($rawImages) && !empty($item->image_path)) {
                    $rawImages = [$item->image_path];
                }
                $imagesUrls = array_values(array_filter(array_map(function($p) {
                    return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
                }, $rawImages)));

                $calc = self::calculateRosaScore([
                    'altura_asiento_base' => $item->altura_asiento_base,
                    'altura_asiento_mod' => $item->altura_asiento_mod,
                    'profundidad_base' => $item->profundidad_base,
                    'profundidad_mod' => $item->profundidad_mod,
                    'reposabrazos_base' => $item->reposabrazos_base,
                    'reposabrazos_mod' => $item->reposabrazos_mod,
                    'respaldo_base' => $item->respaldo_base,
                    'respaldo_mod' => $item->respaldo_mod,
                    'silla_tiempo_uso' => $item->silla_tiempo_uso,
                    'pantalla_base' => $item->pantalla_base,
                    'pantalla_mod' => $item->pantalla_mod,
                    'pantalla_tiempo' => $item->pantalla_tiempo,
                    'telefono_base' => $item->telefono_base,
                    'telefono_mod' => $item->telefono_mod,
                    'telefono_tiempo' => $item->telefono_tiempo,
                    'raton_base' => $item->raton_base,
                    'raton_mod' => $item->raton_mod,
                    'raton_tiempo' => $item->raton_tiempo,
                    'teclado_base' => $item->teclado_base,
                    'teclado_mod' => $item->teclado_mod,
                    'teclado_tiempo' => $item->teclado_tiempo,
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
                    'image_path' => $item->image_path ? (str_starts_with($item->image_path, 'http') ? $item->image_path : asset($item->image_path)) : ($imagesUrls[0] ?? null),
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
                    // Parámetros ROSA
                    'altura_asiento_base' => $item->altura_asiento_base,
                    'altura_asiento_mod' => $item->altura_asiento_mod,
                    'profundidad_base' => $item->profundidad_base,
                    'profundidad_mod' => $item->profundidad_mod,
                    'reposabrazos_base' => $item->reposabrazos_base,
                    'reposabrazos_mod' => $item->reposabrazos_mod,
                    'respaldo_base' => $item->respaldo_base,
                    'respaldo_mod' => $item->respaldo_mod,
                    'silla_tiempo_uso' => $item->silla_tiempo_uso,
                    'pantalla_base' => $item->pantalla_base,
                    'pantalla_mod' => $item->pantalla_mod,
                    'pantalla_tiempo' => $item->pantalla_tiempo,
                    'telefono_base' => $item->telefono_base,
                    'telefono_mod' => $item->telefono_mod,
                    'telefono_tiempo' => $item->telefono_tiempo,
                    'raton_base' => $item->raton_base,
                    'raton_mod' => $item->raton_mod,
                    'raton_tiempo' => $item->raton_tiempo,
                    'teclado_base' => $item->teclado_base,
                    'teclado_mod' => $item->teclado_mod,
                    'teclado_tiempo' => $item->teclado_tiempo,
                    'actividad_estatica' => $item->actividad_estatica,
                    'actividad_repetitiva' => $item->actividad_repetitiva,
                    'actividad_inestable' => $item->actividad_inestable,
                ], $calc);
            })->toArray();
        } else {
            // Mock Evaluations si no hay registros aún
            $measurementsList = [
                [
                    'id' => 1,
                    'num' => '01',
                    'date' => date('d/m/Y', strtotime('-2 days')),
                    'raw_date' => date('Y-m-d', strtotime('-2 days')),
                    'time' => '09:15:00',
                    'area_sector' => 'Gerencia de Finanzas y Contabilidad',
                    'puesto_trabajo' => 'Analista Contable y Tributario',
                    'factor_riesgo' => 'Uso intensivo de pantalla / VDT',
                    'num_trabajadores' => 2,
                    'nombres_trabajadores' => ['Laura Valdivia', 'Patricia Morales'],
                    'edad' => '29',
                    'tiempo_exposicion_horas' => 8,
                    'procedimiento_escrito' => 'Si',
                    'capacitacion' => 'Si',
                    'fuerza_agarre' => 'Leve',
                    'carga_peso_kg' => 0.0,
                    'distancia_m' => 0.0,
                    'ayuda_mecanica' => 'Ninguna',
                    'descripcion_carga' => 'Trabajo informático en estación con doble monitor y software ERP',
                    'manifestacion_temprana' => 'Si',
                    'ubicacion_sintoma' => 'Cuello',
                    'tareas' => [
                        'Digitación continua de comprobantes contables y facturación',
                        'Revisión analítica de estados financieros en pantalla doble',
                    ],
                    'observaciones' => 'Se observa cuello en rotación hacia monitor secundario y flexión de muñeca al digitar.',
                    'image_path' => null,
                    'images_count' => 0,
                    'registered_by' => 'Ing. Carlos Mendoza (Ergónomo Especialista)',
                    'location' => [
                        'lat' => -16.5015,
                        'lng' => -68.1492,
                        'zone' => '19K',
                        'easting' => 591320.10,
                        'northing' => 8175310.40,
                    ],
                    'altura_asiento_base' => 2,
                    'altura_asiento_mod' => 1,
                    'profundidad_base' => 2,
                    'profundidad_mod' => 0,
                    'reposabrazos_base' => 2,
                    'reposabrazos_mod' => 1,
                    'respaldo_base' => 2,
                    'respaldo_mod' => 1,
                    'silla_tiempo_uso' => 1,
                    'pantalla_base' => 2,
                    'pantalla_mod' => 1,
                    'pantalla_tiempo' => 1,
                    'telefono_base' => 1,
                    'telefono_mod' => 0,
                    'telefono_tiempo' => -1,
                    'raton_base' => 2,
                    'raton_mod' => 1,
                    'raton_tiempo' => 1,
                    'teclado_base' => 2,
                    'teclado_mod' => 1,
                    'teclado_tiempo' => 1,
                    'actividad_estatica' => 1,
                    'actividad_repetitiva' => 1,
                    'actividad_inestable' => 0,
                ],
                [
                    'id' => 2,
                    'num' => '02',
                    'date' => date('d/m/Y', strtotime('-1 days')),
                    'raw_date' => date('Y-m-d', strtotime('-1 days')),
                    'time' => '11:40:00',
                    'area_sector' => 'Departamento de Recursos Humanos',
                    'puesto_trabajo' => 'Especialista en Selección y Bienestar',
                    'factor_riesgo' => 'Posturas forzadas',
                    'num_trabajadores' => 1,
                    'nombres_trabajadores' => ['Claudia Mendez'],
                    'edad' => '33',
                    'tiempo_exposicion_horas' => 6,
                    'procedimiento_escrito' => 'Si',
                    'capacitacion' => 'No',
                    'fuerza_agarre' => 'Leve',
                    'carga_peso_kg' => 0.0,
                    'distancia_m' => 0.0,
                    'ayuda_mecanica' => 'Ninguna',
                    'descripcion_carga' => 'Atención de llamadas, entrevistas virtuales y gestión documental',
                    'manifestacion_temprana' => 'Si',
                    'ubicacion_sintoma' => 'Hombros',
                    'tareas' => [
                        'Entrevistas virtuales prolongadas mediante auriculares',
                        'Redacción de reportes psicotécnicos en ordenador',
                    ],
                    'observaciones' => 'Pantalla situada a altura inferior a la línea visual óptima generando flexión cervical.',
                    'image_path' => null,
                    'images_count' => 0,
                    'registered_by' => 'Ing. Carlos Mendoza (Ergónomo Especialista)',
                    'location' => [
                        'lat' => -16.5025,
                        'lng' => -68.1485,
                        'zone' => '19K',
                        'easting' => 591390.80,
                        'northing' => 8175240.20,
                    ],
                    'altura_asiento_base' => 1,
                    'altura_asiento_mod' => 0,
                    'profundidad_base' => 1,
                    'profundidad_mod' => 0,
                    'reposabrazos_base' => 1,
                    'reposabrazos_mod' => 0,
                    'respaldo_base' => 1,
                    'respaldo_mod' => 0,
                    'silla_tiempo_uso' => 1,
                    'pantalla_base' => 2,
                    'pantalla_mod' => 1,
                    'pantalla_tiempo' => 1,
                    'telefono_base' => 1,
                    'telefono_mod' => 0,
                    'telefono_tiempo' => 0,
                    'raton_base' => 1,
                    'raton_mod' => 0,
                    'raton_tiempo' => 1,
                    'teclado_base' => 1,
                    'teclado_mod' => 0,
                    'teclado_tiempo' => 1,
                    'actividad_estatica' => 1,
                    'actividad_repetitiva' => 0,
                    'actividad_inestable' => 0,
                ]
            ];

            // Calcular puntajes ROSA para mock
            foreach ($measurementsList as &$m) {
                $calc = self::calculateRosaScore($m);
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

        return view('measurements.ergonomia_rosa.index', [
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
            'rosaScores' => [
                'score_final' => $maxScore ?: 6,
                'score_a_tiempo' => 5,
                'score_b' => 2,
                'score_c' => 5,
                'score_d' => 5,
            ],
            'selectedMeasurement' => $dbMeasurements->first(),
        ]);
    }

    /**
     * Guarda una nueva evaluación de Ergonomía ROSA desde el portal web.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $currentUser = Auth::user();
        $registeredByName = $currentUser ? $currentUser->name : 'Técnico Ergónomo';
        $staffId = null;
        if ($request->filled('staff_id')) {
            $staffId = $request->input('staff_id');
            $staff = Staff::find($staffId);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            }
        }

        $totalCount = $module->rosaMeasurements()->count();
        $nextNumber = str_pad($totalCount + 1, 2, '0', STR_PAD_LEFT);
        $pointNumber = $request->input('point_number') ?: $nextNumber;

        // Imágenes subidas
        $uploadedImages = [];
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rosa_measurements', 'public');
            $uploadedImages[] = Storage::url($imagePath);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('rosa_measurements', 'public');
                    $uploadedImages[] = Storage::url($path);
                }
            }
        }

        // Tareas y Trabajadores
        $nombres = $request->input('nombres_trabajadores');
        if (is_string($nombres)) {
            $nombres = json_decode($nombres, true) ?: array_filter(array_map('trim', explode(',', $nombres)));
        }

        $tareas = $request->input('tareas');
        if (is_string($tareas)) {
            $tareas = json_decode($tareas, true) ?: array_filter(array_map('trim', explode("\n", $tareas)));
        }

        $calc = self::calculateRosaScore($request->all());

        $measurement = $module->rosaMeasurements()->create([
            'point_number' => $pointNumber,
            'measurement_date' => $request->input('measurement_date') ?: Carbon::now()->toDateString(),
            'measurement_time' => $request->input('measurement_time') ?: Carbon::now()->format('H:i:s'),
            'area_sector' => $request->input('area_sector'),
            'puesto_trabajo' => $request->input('puesto_trabajo'),
            'factor_riesgo' => $request->input('factor_riesgo'),
            'num_trabajadores' => (int)($request->input('num_trabajadores') ?? 1),
            'nombres_trabajadores' => $nombres,
            'edad' => $request->input('edad'),
            'tiempo_exposicion_horas' => $request->filled('tiempo_exposicion_horas') ? (float)$request->input('tiempo_exposicion_horas') : null,
            'procedimiento_escrito' => $request->input('procedimiento_escrito'),
            'capacitacion' => $request->input('capacitacion'),
            'fuerza_agarre' => $request->input('fuerza_agarre'),
            'carga_peso_kg' => $request->filled('carga_peso_kg') ? (float)$request->input('carga_peso_kg') : null,
            'distancia_m' => $request->filled('distancia_m') ? (float)$request->input('distancia_m') : null,
            'ayuda_mecanica' => $request->input('ayuda_mecanica'),
            'descripcion_carga' => $request->input('descripcion_carga'),
            'manifestacion_temprana' => $request->input('manifestacion_temprana'),
            'ubicacion_sintoma' => $request->input('ubicacion_sintoma'),
            'tareas' => $tareas,
            'observaciones' => $request->input('observaciones'),
            'image_path' => $uploadedImages[0] ?? null,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $request->input('location'),
            'latitude' => $request->filled('latitude') ? (float)$request->input('latitude') : null,
            'longitude' => $request->filled('longitude') ? (float)$request->input('longitude') : null,
            'utm_zone' => $request->input('utm_zone'),
            'utm_easting' => $request->filled('utm_easting') ? (float)$request->input('utm_easting') : null,
            'utm_northing' => $request->filled('utm_northing') ? (float)$request->input('utm_northing') : null,
            // Silla
            'altura_asiento_base' => (int)($request->input('altura_asiento_base') ?? 1),
            'altura_asiento_mod' => (int)($request->input('altura_asiento_mod') ?? 0),
            'profundidad_base' => (int)($request->input('profundidad_base') ?? 1),
            'profundidad_mod' => (int)($request->input('profundidad_mod') ?? 0),
            'reposabrazos_base' => (int)($request->input('reposabrazos_base') ?? 1),
            'reposabrazos_mod' => (int)($request->input('reposabrazos_mod') ?? 0),
            'respaldo_base' => (int)($request->input('respaldo_base') ?? 1),
            'respaldo_mod' => (int)($request->input('respaldo_mod') ?? 0),
            'silla_tiempo_uso' => (int)($request->input('silla_tiempo_uso') ?? 0),
            // Pantalla y Teléfono
            'pantalla_base' => (int)($request->input('pantalla_base') ?? 1),
            'pantalla_mod' => (int)($request->input('pantalla_mod') ?? 0),
            'pantalla_tiempo' => (int)($request->input('pantalla_tiempo') ?? 0),
            'telefono_base' => (int)($request->input('telefono_base') ?? 1),
            'telefono_mod' => (int)($request->input('telefono_mod') ?? 0),
            'telefono_tiempo' => (int)($request->input('telefono_tiempo') ?? 0),
            // Ratón y Teclado
            'raton_base' => (int)($request->input('raton_base') ?? 1),
            'raton_mod' => (int)($request->input('raton_mod') ?? 0),
            'raton_tiempo' => (int)($request->input('raton_tiempo') ?? 0),
            'teclado_base' => (int)($request->input('teclado_base') ?? 1),
            'teclado_mod' => (int)($request->input('teclado_mod') ?? 0),
            'teclado_tiempo' => (int)($request->input('teclado_tiempo') ?? 0),
            // Actividad
            'actividad_estatica' => (int)($request->input('actividad_estatica') ?? 0),
            'actividad_repetitiva' => (int)($request->input('actividad_repetitiva') ?? 0),
            'actividad_inestable' => (int)($request->input('actividad_inestable') ?? 0),
            // Calculados
            'score_a' => $calc['score_a'],
            'score_b' => $calc['score_b'],
            'score_c' => $calc['score_c'],
            'score_d' => $calc['score_d'],
            'score_e' => $calc['score_e'],
            'score_actividad' => $calc['score_actividad'],
            'score_final' => $calc['score_final'],
            'risk_level' => $calc['risk_level'],
            'action_level' => $calc['action_level'],
            'risk_theme' => $calc['risk_theme'],
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ]);

        $module->points_completed = $module->rosaMeasurements()->count();
        $module->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Evaluación ROSA creada exitosamente.',
                'measurement' => $measurement,
                'calc' => $calc,
            ]);
        }

        return redirect()->route('modules.ergonomia_rosa', $moduleId)->with('success', 'Evaluación ROSA registrada correctamente.');
    }

    /**
     * Actualiza una evaluación existente de Ergonomía ROSA.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->rosaMeasurements()->findOrFail($measurementId);

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
            $imagePath = $request->file('image')->store('rosa_measurements', 'public');
            $newImages[] = Storage::url($imagePath);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('rosa_measurements', 'public');
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

        $calc = self::calculateRosaScore($request->all());

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
            // Silla
            'altura_asiento_base' => (int)($request->input('altura_asiento_base') ?? $measurement->altura_asiento_base),
            'altura_asiento_mod' => (int)($request->input('altura_asiento_mod') ?? $measurement->altura_asiento_mod),
            'profundidad_base' => (int)($request->input('profundidad_base') ?? $measurement->profundidad_base),
            'profundidad_mod' => (int)($request->input('profundidad_mod') ?? $measurement->profundidad_mod),
            'reposabrazos_base' => (int)($request->input('reposabrazos_base') ?? $measurement->reposabrazos_base),
            'reposabrazos_mod' => (int)($request->input('reposabrazos_mod') ?? $measurement->reposabrazos_mod),
            'respaldo_base' => (int)($request->input('respaldo_base') ?? $measurement->respaldo_base),
            'respaldo_mod' => (int)($request->input('respaldo_mod') ?? $measurement->respaldo_mod),
            'silla_tiempo_uso' => (int)($request->input('silla_tiempo_uso') ?? $measurement->silla_tiempo_uso),
            // Pantalla y Teléfono
            'pantalla_base' => (int)($request->input('pantalla_base') ?? $measurement->pantalla_base),
            'pantalla_mod' => (int)($request->input('pantalla_mod') ?? $measurement->pantalla_mod),
            'pantalla_tiempo' => (int)($request->input('pantalla_tiempo') ?? $measurement->pantalla_tiempo),
            'telefono_base' => (int)($request->input('telefono_base') ?? $measurement->telefono_base),
            'telefono_mod' => (int)($request->input('telefono_mod') ?? $measurement->telefono_mod),
            'telefono_tiempo' => (int)($request->input('telefono_tiempo') ?? $measurement->telefono_tiempo),
            // Ratón y Teclado
            'raton_base' => (int)($request->input('raton_base') ?? $measurement->raton_base),
            'raton_mod' => (int)($request->input('raton_mod') ?? $measurement->raton_mod),
            'raton_tiempo' => (int)($request->input('raton_tiempo') ?? $measurement->raton_tiempo),
            'teclado_base' => (int)($request->input('teclado_base') ?? $measurement->teclado_base),
            'teclado_mod' => (int)($request->input('teclado_mod') ?? $measurement->teclado_mod),
            'teclado_tiempo' => (int)($request->input('teclado_tiempo') ?? $measurement->teclado_tiempo),
            // Actividad
            'actividad_estatica' => (int)($request->input('actividad_estatica') ?? $measurement->actividad_estatica),
            'actividad_repetitiva' => (int)($request->input('actividad_repetitiva') ?? $measurement->actividad_repetitiva),
            'actividad_inestable' => (int)($request->input('actividad_inestable') ?? $measurement->actividad_inestable),
            // Calculados
            'score_a' => $calc['score_a'],
            'score_b' => $calc['score_b'],
            'score_c' => $calc['score_c'],
            'score_d' => $calc['score_d'],
            'score_e' => $calc['score_e'],
            'score_actividad' => $calc['score_actividad'],
            'score_final' => $calc['score_final'],
            'risk_level' => $calc['risk_level'],
            'action_level' => $calc['action_level'],
            'risk_theme' => $calc['risk_theme'],
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Evaluación ROSA actualizada correctamente.',
                'measurement' => $measurement,
                'calc' => $calc,
            ]);
        }

        return redirect()->route('modules.ergonomia_rosa', $moduleId)->with('success', 'Evaluación ROSA actualizada correctamente.');
    }

    /**
     * Elimina una evaluación de Ergonomía ROSA.
     */
    public function destroyMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->rosaMeasurements()->findOrFail($measurementId);
        $measurement->delete();

        $module->points_completed = $module->rosaMeasurements()->count();
        $module->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Evaluación ROSA eliminada correctamente.',
                'points_completed' => $module->points_completed,
            ]);
        }

        return redirect()->route('modules.ergonomia_rosa', $moduleId)->with('success', 'Evaluación ROSA eliminada correctamente.');
    }

    /**
     * Actualiza los datos del encabezado técnico (instalación, fechas, tipo de monitoreo).
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        if ($request->has('installation_name')) {
            $module->installation_name = $request->input('installation_name');
        }
        if ($request->has('start_date')) {
            $module->start_date = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        }
        if ($request->has('end_date')) {
            $module->end_date = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $request->input('monitoring_type');
        }

        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Encabezado técnico actualizado correctamente.',
            'module' => $module,
        ]);
    }

    /**
     * Guarda la configuración del reporte fotográfico de Ergonomía ROSA.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $settings = $request->input('settings', []);

        $module->photo_report_settings = $settings;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración de mosaico fotográfico guardada con éxito.',
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
            $measurement = $module->rosaMeasurements()->find($evaluationId);
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
     * Genera automáticamente el Análisis Técnico, Observaciones y Recomendaciones utilizando Google Gemini AI.
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
            $measurement = $module->rosaMeasurements()->find($evaluationId);
        }
        if (!$measurement) {
            $measurement = $module->rosaMeasurements()->first();
        }

        $puesto = $measurement ? ($measurement->puesto_trabajo ?: 'Puesto Administrativo') : 'Gerente General';
        $area = $measurement ? ($measurement->area_sector ?: 'Administración') : 'Administración';
        $trabajador = $measurement ? (is_array($measurement->nombres_trabajadores) ? implode(', ', array_filter($measurement->nombres_trabajadores)) : ($measurement->nombres_trabajadores ?: 'Personal Administrativo')) : 'Carlos Rene Ichuta Ichuta';
        $tiempo = $measurement ? ($measurement->tiempo_exposicion_horas ?: 8) : 8;

        $calc = $measurement ? self::calculateRosaScore($measurement->toArray()) : self::calculateRosaScore([]);
        $scoreA = $calc['score_a'] ?? 5;
        $scoreB = $calc['score_b'] ?? 2;
        $scoreC = $calc['score_c'] ?? 5;
        $scoreD = $calc['score_d'] ?? 5;
        $scoreFinal = $calc['score_final'] ?? 6;
        $riskLevel = $calc['risk_level'] ?? 'Medio';
        $actionLevel = $calc['action_level'] ?? 'Requiere intervención ergonómica';

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
Realiza una evaluación biomecánica y ergonómica exhaustiva del puesto de trabajo '{$puesto}' (Área: {$area}, Trabajador: {$trabajador}, Exposición: {$tiempo} hrs/día), evaluado mediante el método ROSA (Rapid Office Strain Assessment - ISO 9241 e ISO 11226) con los siguientes resultados normativos:

1. PUNTUACIÓN FINAL ROSA: {$scoreFinal}/10 (Nivel de Riesgo: {$riskLevel}, Acción: {$actionLevel}).
2. Puntuación Silla con factor tiempo (Tabla A): {$scoreA}.
3. Puntuación Teléfono y Pantalla (Tabla B): {$scoreB}.
4. Puntuación Ratón y Teclado (Tabla C): {$scoreC}.
5. Puntuación Pantalla y Periféricos (Tabla D): {$scoreD}.";

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
                // Reintentar automáticamente con gemini-3.8-flash si el modelo anterior estaba saturado
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
                $text = str_replace('**', '', $text);
                $text = preg_replace('/^\s*[\*\-]\s*/m', '• ', $text);
                $text = preg_replace('/\s+[\*]\s+/', "\n• ", $text);
                $text = preg_replace('/\s+•\s+/', "\n• ", $text);
                $lines = array_filter(array_map('trim', explode("\n", $text)));
                $formatted = [];
                foreach ($lines as $line) {
                    $line = preg_replace('/^[\*\-\s]+/', '', $line);
                    $line = str_replace('*', '', $line);
                    $line = trim($line);
                    if (!empty($line)) {
                        if (!str_starts_with($line, '•')) {
                            $line = '• ' . $line;
                        }
                        $formatted[] = $line;
                    }
                }
                return implode("\n", $formatted);
            };

            if (!empty($analisisTecnico)) {
                $analisisTecnico = str_replace(['**', '*'], '', $analisisTecnico);
            }
            if (!empty($observaciones)) {
                $observaciones = $cleanBullets($observaciones);
            }
            if (!empty($recomendaciones)) {
                $recomendaciones = $cleanBullets($recomendaciones);
            }

            // Fallback si no vinieron los delimitadores exactos
            if (empty($analisisTecnico) && empty($observaciones) && empty($recomendaciones)) {
                $analisisTecnico = $rawText;
            }

            // Guardar en la base de datos (anexo2_data)
            $anexo2Data = $module->anexo2_data ?? [];
            if (!isset($anexo2Data['prompts_data']) || !is_array($anexo2Data['prompts_data'])) {
                $anexo2Data['prompts_data'] = [];
            }

            $key = $evaluationId ? (string)$evaluationId : 'general';
            $anexo2Data['prompts_data'][$key] = [
                'prompt' => $customPrompt ?: $basePrompt,
                'analisis_tecnico' => $analisisTecnico,
                'observaciones' => $observaciones,
                'recomendaciones' => $recomendaciones,
                'updated_at' => now()->toDateTimeString(),
            ];

            $module->anexo2_data = $anexo2Data;
            $module->save();

            if ($measurement && !empty($observaciones)) {
                $measurement->observaciones = $observaciones;
                $measurement->save();
            }

            return response()->json([
                'success' => true,
                'message' => '¡Contenido generado y guardado exitosamente con Google Gemini AI!',
                'data' => [
                    'prompt' => $customPrompt ?: $basePrompt,
                    'analisis_tecnico' => $analisisTecnico,
                    'observaciones' => $observaciones,
                    'recomendaciones' => $recomendaciones,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con la API de Gemini: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra la página completa de Tablas y Matrices Normativas ROSA con navegación interactiva por pasos.
     */
    public function showTables(Request $request, $id)
    {
        $currentUser = Auth::user();
        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff', 'rosaMeasurements'])->find($id);

        if (!$module) {
            $module = new MeasurementModule();
            $module->id = (int)$id;
            $module->name = 'Monitoreo de Ergonomía ROSA';
            $module->type = 'ergonomia_rosa';
        }

        $project = $module->project;
        $company = $project ? $project->company : null;
        $projectName = $project ? $project->name : 'Estudio de Ergonomía en Oficinas (ROSA)';
        
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
            $selectedMeasurement = $module->rosaMeasurements()->find($evaluationId);
        }
        if (!$selectedMeasurement && $module->exists) {
            $selectedMeasurement = $module->rosaMeasurements()->first();
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

        // Matriz de factores limpios por defecto (sin ninguna selección por defecto)
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

        // Construir datos sincronizados con proyecto y registro seleccionado
        $anexo2Data = [
            'razon_social' => $projectRazonSocial,
            'direccion' => $projectDireccion,
            'area_sector' => $selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Administración') : ($anexo2Data['area_sector'] ?? 'Administración'),
            'puesto_trabajo' => $selectedMeasurement ? ($selectedMeasurement->puesto_trabajo ?: 'Gerente general') : ($anexo2Data['puesto_trabajo'] ?? 'Gerente general'),
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
            'factors' => $cleanFactors,
            'profesional_nombre' => $module->anexo2_data['profesional_nombre'] ?? '',
            'profesional_registro' => $module->anexo2_data['profesional_registro'] ?? '',
            'profesional_fecha' => $module->anexo2_data['profesional_fecha'] ?? '',
            'prompts_data' => $module->anexo2_data['prompts_data'] ?? [],
            'step4_data' => $module->anexo2_data['step4_data'] ?? [],
            'step5_data' => $module->anexo2_data['step5_data'] ?? [],
        ];

        // Obtener todas las mediciones para selectores
        $allMeasurements = $module->rosaMeasurements()->orderBy('point_number', 'asc')->get();
        foreach ($allMeasurements as $mItem) {
            $mCalc = self::calculateRosaScore($mItem->toArray());
            $mItem->calculated_score_final = $mCalc['score_final'];
            $mItem->calculated_risk_level = $mCalc['risk_level'];
        }

        // Calcular puntajes de las matrices para el registro seleccionado
        if ($selectedMeasurement) {
            $alturaBase = (int)($selectedMeasurement->altura_asiento_base ?? 1);
            $alturaMod = (int)($selectedMeasurement->altura_asiento_mod ?? 0);
            $alturaFinal = max(1, $alturaBase + $alturaMod);
            $profundidadBase = (int)($selectedMeasurement->profundidad_base ?? 1);
            $profundidadMod = (int)($selectedMeasurement->profundidad_mod ?? 0);
            $profundidadFinal = max(1, $profundidadBase + $profundidadMod);
            // Asiento: altura + profundidad (A-1 + A-2) -> Rango 2 a 8
            $asientoScore = min(8, max(2, $alturaFinal + $profundidadFinal));

            $reposabrazosBase = (int)($selectedMeasurement->reposabrazos_base ?? 1);
            $reposabrazosMod = (int)($selectedMeasurement->reposabrazos_mod ?? 0);
            $reposabrazosFinal = max(1, $reposabrazosBase + $reposabrazosMod);
            $respaldoBase = (int)($selectedMeasurement->respaldo_base ?? 1);
            $respaldoMod = (int)($selectedMeasurement->respaldo_mod ?? 0);
            $respaldoFinal = max(1, $respaldoBase + $respaldoMod);
            // Reposabrazos + respaldo (A-3 + A-4) -> Rango 2 a 9
            $soporteScore = min(9, max(2, $reposabrazosFinal + $respaldoFinal));

            $matA = [
                2 => [2=>2, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6, 8=>7, 9=>8],
                3 => [2=>2, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6, 8=>7, 9=>8],
                4 => [2=>3, 3=>3, 4=>3, 5=>4, 6=>5, 7=>6, 8=>7, 9=>8],
                5 => [2=>4, 3=>4, 4=>4, 5=>4, 6=>5, 7=>6, 8=>7, 9=>8],
                6 => [2=>5, 3=>5, 4=>5, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9],
                7 => [2=>6, 3=>6, 4=>6, 5=>7, 6=>7, 7=>8, 8=>8, 9=>9],
                8 => [2=>7, 3=>7, 4=>7, 5=>7, 6=>8, 7=>8, 8=>9, 9=>9],
            ];
            $sillaBaseScore = $matA[$asientoScore][$soporteScore] ?? 2;
            $sillaTiempo = (int)($selectedMeasurement->silla_tiempo_uso ?? 0);
            $scoreA = min(10, max(1, $sillaBaseScore + $sillaTiempo));

            $telefonoBase = (int)($selectedMeasurement->telefono_base ?? 1);
            $telefonoMod = (int)($selectedMeasurement->telefono_mod ?? 0);
            $telefonoTiempo = (int)($selectedMeasurement->telefono_tiempo ?? 0);
            // Teléfono (B-1) -> Rango 0 a 6
            $telefonoScore = min(6, max(0, $telefonoBase + $telefonoMod + $telefonoTiempo));

            $pantallaBase = (int)($selectedMeasurement->pantalla_base ?? 1);
            $pantallaMod = (int)($selectedMeasurement->pantalla_mod ?? 0);
            $pantallaTiempo = (int)($selectedMeasurement->pantalla_tiempo ?? 0);
            // Pantalla (B-2) -> Rango 0 a 8
            $pantallaScore = min(8, max(0, $pantallaBase + $pantallaMod + $pantallaTiempo));

            $matB = [
                0 => [0=>1, 1=>1, 2=>1, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6, 8=>6],
                1 => [0=>1, 1=>1, 2=>2, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6, 8=>6],
                2 => [0=>1, 1=>2, 2=>2, 3=>3, 4=>3, 5=>4, 6=>6, 7=>7, 8=>7],
                3 => [0=>2, 1=>2, 2=>3, 3=>3, 4=>4, 5=>5, 6=>6, 7=>8, 8=>8],
                4 => [0=>3, 1=>3, 2=>4, 3=>4, 4=>5, 5=>6, 6=>7, 7=>8, 8=>8],
                5 => [0=>4, 1=>4, 2=>5, 3=>5, 4=>6, 5=>7, 6=>8, 7=>9, 8=>9],
                6 => [0=>5, 1=>5, 2=>6, 3=>7, 4=>8, 5=>8, 6=>9, 7=>9, 8=>9],
            ];
            $scoreB = $matB[$telefonoScore][$pantallaScore] ?? 1;

            $ratonBase = (int)($selectedMeasurement->raton_base ?? 1);
            $ratonMod = (int)($selectedMeasurement->raton_mod ?? 0);
            $ratonTiempo = (int)($selectedMeasurement->raton_tiempo ?? 0);
            // Ratón (C-1) -> Rango 0 a 7
            $ratonScore = min(7, max(0, $ratonBase + $ratonMod + $ratonTiempo));

            $tecladoBase = (int)($selectedMeasurement->teclado_base ?? 1);
            $tecladoMod = (int)($selectedMeasurement->teclado_mod ?? 0);
            $tecladoTiempo = (int)($selectedMeasurement->teclado_tiempo ?? 0);
            // Teclado (C-2) -> Rango 0 a 7
            $tecladoScore = min(7, max(0, $tecladoBase + $tecladoMod + $tecladoTiempo));

            $matC = [
                0 => [0=>1, 1=>1, 2=>1, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6],
                1 => [0=>1, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7],
                2 => [0=>1, 1=>2, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7],
                3 => [0=>2, 1=>3, 2=>3, 3=>3, 4=>5, 5=>6, 6=>7, 7=>8],
                4 => [0=>3, 1=>4, 2=>4, 3=>5, 4=>5, 5=>6, 6=>7, 7=>8],
                5 => [0=>4, 1=>5, 2=>5, 3=>6, 4=>6, 5=>7, 6=>8, 7=>9],
                6 => [0=>5, 1=>6, 2=>6, 3=>7, 4=>7, 5=>8, 6=>8, 7=>9],
                7 => [0=>6, 1=>7, 2=>7, 3=>8, 4=>8, 5=>9, 6=>9, 7=>9],
            ];
            $scoreC = $matC[$ratonScore][$tecladoScore] ?? 1;

            $scoreBIdx = min(9, max(1, $scoreB));
            $scoreCIdx = min(9, max(1, $scoreC));

            $matD = [];
            for ($r = 1; $r <= 9; $r++) {
                $matD[$r] = [];
                for ($c = 1; $c <= 9; $c++) {
                    $matD[$r][$c] = max($r, $c);
                }
            }
            $scoreD = $matD[$scoreBIdx][$scoreCIdx] ?? 1;

            $scoreAIdx = min(10, max(1, $scoreA));
            $scoreDIdx = min(10, max(1, $scoreD));

            $matE = [];
            for ($r = 1; $r <= 10; $r++) {
                $matE[$r] = [];
                for ($c = 1; $c <= 10; $c++) {
                    $matE[$r][$c] = max($r, $c);
                }
            }
            $scoreE = $matE[$scoreAIdx][$scoreDIdx] ?? 1;

            $rosaScores = [
                'asiento' => $asientoScore,
                'soporte' => $soporteScore,
                'score_a_base' => $sillaBaseScore,
                'score_a_tiempo' => $scoreA,
                'telefono' => $telefonoScore,
                'pantalla' => $pantallaScore,
                'score_b' => $scoreB,
                'raton' => $ratonScore,
                'teclado' => $tecladoScore,
                'score_c' => $scoreC,
                'score_d' => $scoreD,
                'score_final' => $scoreE,
            ];
        } else {
            $rosaScores = [
                'asiento' => 4,
                'soporte' => 6,
                'score_a_base' => 5,
                'score_a_tiempo' => 6,
                'telefono' => 3,
                'pantalla' => 1,
                'score_b' => 2,
                'raton' => 3,
                'teclado' => 4,
                'score_c' => 5,
                'score_d' => 5,
                'score_final' => 6,
            ];
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

        return view('measurements.ergonomia_rosa.tables', compact(
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
            'rosaScores',
            'promptsData'
        ));
    }
}
