<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\ParticulasAmbientalesMeasurement;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ParticulasAmbientalesController extends Controller
{
    /**
     * Límites Máximos Permisibles para Calidad de Aire (Ley 1333 RMCA / OMS).
     */
    public const PM10_LIMIT_24H = 150.0; // µg/m³ (Ley 1333 RMCA Bolivia)
    public const PM25_LIMIT_24H = 25.0;  // µg/m³ (Ley 1333 / OMS)
    public const PTS_LIMIT_24H = 260.0;  // µg/m³ (Partículas Totales en Suspensión)

    /**
     * Muestra la página principal del módulo de Partículas Ambientales.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Resuelve información técnica del encabezado: Razón Social de la empresa o proyecto
        $project = $module->project;
        $company = $project ? $project->company : null;
        $razonSocial = '';
        if ($project) {
            if (!empty($project->razon_social)) {
                $razonSocial = $project->razon_social;
            } elseif ($company && !empty($company->legal_name)) {
                $razonSocial = $company->legal_name;
            } elseif ($company && !empty($company->name)) {
                $razonSocial = $company->name;
            } else {
                $razonSocial = $project->name;
            }
        }

        $oldDefault = ($project ? $project->name : '') . ($company ? " - {$company->name}" : '');
        if (empty($module->installation_name) || $module->installation_name === $oldDefault) {
            $installationName = $razonSocial ?: ($module->installation_name ?: 'PLANTA INDUSTRIAL — MONITOREO AMBIENTAL');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        // Fechas del módulo
        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Monitoreo Ambiental Perimetral';

        // Equipo asignado para calidad de aire ambiental
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Muestreador de Alto Volumen Hi-Vol PM10 / PM2.5');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'TISCH Environmental') : 'TISCH Environmental';
        $equipmentModel = $equipment ? ($equipment->model ?: 'TE-6070V-BL') : 'TE-6070V-BL';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'HV-2408-01') : 'HV-2408-01';
        $equipmentImage = ($equipment && $equipment->image) ? asset($equipment->image) : null;

        // Personal asignado para selector y encabezado técnico
        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
            $staffList = $assignedStaff;
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico Ambiental';
            $staffList = Staff::orderBy('name')->get();
        }

        // Obtener mediciones registradas en base de datos
        $dbMeasurements = $module->particulasAmbientalesMeasurements()->with('staff')->get();

        $measurements = $dbMeasurements->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateObj = $item->fecha_inicio ?: $item->measurement_date;
            $dateFormatted = $dateObj ? Carbon::parse($dateObj)->format('d/m/Y') : '—';
            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

            if (($lat === null || $lng === null) && !empty($item->location)) {
                $loc = trim($item->location);
                if (str_starts_with($loc, '{') || str_starts_with($loc, '[')) {
                    $d = json_decode($loc, true);
                    if (is_array($d)) {
                        if (!empty($d['latitude']) && !empty($d['longitude'])) {
                            $lat = (float) $d['latitude'];
                            $lng = (float) $d['longitude'];
                        } elseif (!empty($d['easting']) && !empty($d['northing'])) {
                            $conv = $this->utmToLatLng($d['easting'], $d['northing'], $d['utm_zone'] ?? $d['zone'] ?? '19K');
                            $lat = $conv['lat'];
                            $lng = $conv['lng'];
                        }
                    }
                }
            }

            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_urls)) {
                $rawImages = is_array($item->image_urls) ? $item->image_urls : (json_decode($item->image_urls, true) ?: []);
            }
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function ($p) {
                return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
            }, $rawImages)));

            $pm10Prom = $item->pm10_prom !== null ? (float)$item->pm10_prom : null;
            $pstProm = $item->pst_prom !== null ? (float)$item->pst_prom : null;
            $pm25Prom = $item->pm25_prom !== null ? (float)$item->pm25_prom : null;

            $pm10Cumple = $pm10Prom !== null ? ($pm10Prom <= self::PM10_LIMIT_24H) : true;
            $pstCumple = $pstProm !== null ? ($pstProm <= self::PTS_LIMIT_24H) : true;
            $pm25Cumple = $pm25Prom !== null ? ($pm25Prom <= self::PM25_LIMIT_24H) : true;

            $fechaInicioStr = $item->fecha_inicio ? Carbon::parse($item->fecha_inicio)->format('Y-m-d') : ($item->measurement_date ? Carbon::parse($item->measurement_date)->format('Y-m-d') : '');
            $fechaFinStr = $item->fecha_fin ? Carbon::parse($item->fecha_fin)->format('Y-m-d') : $fechaInicioStr;

            $pointNum = (!empty($item->point_number) && preg_match('/^PA-\d+/i', $item->point_number)) 
                ? strtoupper($item->point_number) 
                : ('PA-' . ($index + 1));

            return [
                'id' => $item->id,
                'num' => $pointNum,
                'point_number' => $pointNum,
                'date' => $dateFormatted,
                'raw_date' => $fechaInicioStr,
                'time' => $item->hora_inicio ?: ($item->measurement_time ?: '08:00'),
                'fecha_inicio' => $fechaInicioStr,
                'hora_inicio' => $item->hora_inicio ?: '08:00',
                'fecha_fin' => $fechaFinStr,
                'hora_fin' => $item->hora_fin ?: '08:00',
                'diferencia_horas' => $item->diferencia_horas !== null ? (float)$item->diferencia_horas : 24.0,
                'area' => $item->area ?: 'General',
                'punto_medicion' => $item->punto_medicion ?: ($item->workstation ?: $pointNum),
                'workstation' => $item->punto_medicion ?: ($item->workstation ?: $pointNum),
                'temp_max' => $item->temp_max !== null ? (float)$item->temp_max : 24.0,
                'temp_min' => $item->temp_min !== null ? (float)$item->temp_min : 12.0,
                'temperatura' => $item->temperatura !== null ? (float)$item->temperatura : 18.0,
                'presion_atm' => $item->presion_atm !== null ? (float)$item->presion_atm : 495.0,
                'vel_viento' => $item->vel_viento !== null ? (float)$item->vel_viento : 2.5,
                'dir_viento' => $item->dir_viento ?: 'NE',
                'hr_percent' => $item->hr_percent !== null ? (float)$item->hr_percent : 45.0,
                'pm10_filtro_inicial' => $item->pm10_filtro_inicial !== null ? (float)$item->pm10_filtro_inicial : null,
                'pm10_filtro_final' => $item->pm10_filtro_final !== null ? (float)$item->pm10_filtro_final : null,
                'pm10_prom' => $pm10Prom,
                'pm10_cumple' => $pm10Cumple,
                'pst_filtro_inicial' => $item->pst_filtro_inicial !== null ? (float)$item->pst_filtro_inicial : null,
                'pst_filtro_final' => $item->pst_filtro_final !== null ? (float)$item->pst_filtro_final : null,
                'pst_prom' => $pstProm,
                'pst_cumple' => $pstCumple,
                'pm25_filtro_inicial' => $item->pm25_filtro_inicial !== null ? (float)$item->pm25_filtro_inicial : null,
                'pm25_filtro_final' => $item->pm25_filtro_final !== null ? (float)$item->pm25_filtro_final : null,
                'pm25_prom' => $pm25Prom,
                'pm25_cumple' => $pm25Cumple,
                'caudal' => $item->caudal !== null ? (float)$item->caudal : 1130.0,
                'image_path' => $imagesUrls[0] ?? null,
                'raw_image_path' => $rawImages[0] ?? null,
                'images' => $imagesUrls,
                'raw_images' => $rawImages,
                'images_count' => count($imagesUrls),
                'location' => $item->location ?: ($lat && $lng ? "{$lat}, {$lng}" : '—'),
                'latitude' => $lat ?? -16.5034,
                'longitude' => $lng ?? -68.1324,
                'utm_zone' => $item->utm_zone ?: '19K',
                'utm_easting' => $item->utm_easting !== null ? (float)$item->utm_easting : 592450.0,
                'utm_northing' => $item->utm_northing !== null ? (float)$item->utm_northing : 8175320.0,
                'observations' => $item->observations ?: 'Sin observaciones.',
                'raw_observations' => $item->observations ?: '',
                'registered_by' => $item->staff ? ($item->staff->full_name ?: $item->staff->name) : ($item->registered_by ?: ($currentUser ? $currentUser->name : 'Técnico Ambiental')),
                'staff_id' => $item->staff_id,
            ];
        });

        // Configuración guardada para el Reporte Fotográfico
        $savedSettings = $module->photo_report_settings ?: [];
        $photoReportSettings = [
            'grid' => $savedSettings['grid'] ?? '2x3',
            'orientation' => $savedSettings['orientation'] ?? 'landscape',
            'selected_points' => $savedSettings['selected_points'] ?? $measurements->pluck('id')->toArray(),
            'photo_indices' => $savedSettings['photo_indices'] ?? (object)[],
        ];

        $totalMeasurements = $measurements->count();

        // Estadísticas
        $compliantCount = $measurements->filter(fn($m) => $m['pm10_cumple'] && $m['pst_cumple'])->count();
        $complianceRate = $totalMeasurements > 0 ? round(($compliantCount / $totalMeasurements) * 100, 1) : 100.0;

        return view('measurements.particulas_ambientales.index', compact(
            'userName',
            'userRole',
            'module',
            'installationName',
            'startDateRaw',
            'endDateRaw',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'registeredByHeader',
            'equipmentName',
            'equipmentBrand',
            'equipmentModel',
            'equipmentSerial',
            'equipmentImage',
            'staffList',
            'measurements',
            'totalMeasurements',
            'compliantCount',
            'complianceRate',
            'photoReportSettings'
        ));
    }

    /**
     * Muestra el Informe Oficial de Partículas Ambientales con Stepper.
     */
    public function showReport($moduleId)
    {
        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        $project = $module->project;
        $company = $project ? $project->company : null;
        $razonSocial = '';
        if ($project) {
            if (!empty($project->razon_social)) {
                $razonSocial = $project->razon_social;
            } elseif ($company && !empty($company->legal_name)) {
                $razonSocial = $company->legal_name;
            } elseif ($company && !empty($company->name)) {
                $razonSocial = $company->name;
            } else {
                $razonSocial = $project->name;
            }
        }

        $oldDefault = ($project ? $project->name : '') . ($company ? " - {$company->name}" : '');
        if (empty($module->installation_name) || $module->installation_name === $oldDefault) {
            $installationName = $razonSocial ?: ($module->installation_name ?: 'PLANTA INDUSTRIAL — MONITOREO AMBIENTAL');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : $startDateFormatted;
        $monitoringType = $module->monitoring_type ?: 'Monitoreo Ambiental Perimetral';

        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Muestreador de Alto Volumen Hi-Vol PM10/PST');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'TISCH Environmental') : 'TISCH Environmental';
        $equipmentModel = $equipment ? ($equipment->model ?: 'TE-6070V-BL') : 'TE-6070V-BL';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'HV-2408-01') : 'HV-2408-01';

        $assignedStaff = $module->getAssignedStaffAttribute();
        $registeredByHeader = ($assignedStaff && $assignedStaff->isNotEmpty())
            ? $assignedStaff->pluck('name')->implode(', ')
            : 'PACHABOL MEDIO AMBIENTE & SEGURIDAD';

        $windNames = [
            'N' => 'Norte',
            'NNE' => 'Nor-noreste',
            'NE' => 'Noreste',
            'ENE' => 'Este-noreste',
            'E' => 'Este',
            'ESE' => 'Este-sureste',
            'SE' => 'Sureste',
            'SSE' => 'Sur-sureste',
            'S' => 'Sur',
            'SSO' => 'Sur-suroeste',
            'SSW' => 'Sur-suroeste',
            'SO' => 'Suroeste',
            'SW' => 'Suroeste',
            'OSO' => 'Oeste-suroeste',
            'WSW' => 'Oeste-suroeste',
            'O' => 'Oeste',
            'W' => 'Oeste',
            'ONO' => 'Oeste-noroeste',
            'WNW' => 'Oeste-noroeste',
            'NO' => 'Noroeste',
            'NW' => 'Noroeste',
            'NNO' => 'Nor-noroeste',
            'NNW' => 'Nor-noroeste',
        ];

        $measurementsList = $module->particulasAmbientalesMeasurements()->get()->map(function ($m, $idx) use ($windNames) {
            $fechaInicioStr = $m->fecha_inicio ? Carbon::parse($m->fecha_inicio)->format('d/m/Y') : ($m->measurement_date ? Carbon::parse($m->measurement_date)->format('d/m/Y') : Carbon::now()->format('d/m/Y'));
            $horaInicioStr = $m->hora_inicio ? substr($m->hora_inicio, 0, 5) : ($m->measurement_time ? substr($m->measurement_time, 0, 5) : '09:45');
            
            $fechaFinStr = $m->fecha_fin ? Carbon::parse($m->fecha_fin)->format('d/m/Y') : $fechaInicioStr;
            $horaFinStr = $m->hora_fin ? substr($m->hora_fin, 0, 5) : '09:50';

            $difHoras = $m->diferencia_horas !== null && (float)$m->diferencia_horas > 0 ? (float)$m->diferencia_horas : 24.0;

            // Temp y Presión Atmosférica
            $tempMax = $m->temp_max !== null ? (float)$m->temp_max : ($m->temperatura !== null ? (float)$m->temperatura : 25.0);
            $tempMin = $m->temp_min !== null ? (float)$m->temp_min : ($m->temperatura !== null ? (float)$m->temperatura : 28.0);
            $tempAvg = ($tempMax + $tempMin) / 2.0;
            $tempK = round($tempAvg + 273.0, 1); // e.g. 26.5 + 273 = 299.5 K

            $presionAtm = $m->presion_atm !== null && (float)$m->presion_atm > 0 ? (float)$m->presion_atm : 495.0;

            // Factor de estandarización: (760 / PresionAtm) * (TempK / 298.0)
            $factorStd = round((760.0 / $presionAtm) * ($tempK / 298.0), 5); // 1.54308

            // Caudal y volumen
            $caudal = $m->caudal !== null && (float)$m->caudal > 0 ? (float)$m->caudal : 6.0;
            $volumenM3 = ($caudal / 1000.0) * ($difHoras * 60.0); // e.g. (6/1000)*(24*60) = 8.64 m3

            // PM-10
            $pm10Ini = $m->pm10_filtro_inicial !== null ? (float)$m->pm10_filtro_inicial : 0.1234;
            $pm10Fin = $m->pm10_filtro_final !== null ? (float)$m->pm10_filtro_final : 0.1354;
            $diffPm10 = round(max(0, $pm10Fin - $pm10Ini), 4);
            $cLocPm10 = $volumenM3 > 0 ? ($diffPm10 * 1000000.0) / $volumenM3 : 0;
            $cStdPm10 = round($cLocPm10 * $factorStd, 2);

            // PST
            $pstIni = $m->pst_filtro_inicial !== null ? (float)$m->pst_filtro_inicial : 0.1456;
            $pstFin = $m->pst_filtro_final !== null ? (float)$m->pst_filtro_final : 0.2365;
            $diffPst = round(max(0, $pstFin - $pstIni), 4);
            $cLocPst = $volumenM3 > 0 ? ($diffPst * 1000000.0) / $volumenM3 : 0;
            $cStdPst = round($cLocPst * $factorStd, 2);

            $velViento = $m->vel_viento !== null ? (float)$m->vel_viento : 23.0;
            $rawDir = trim($m->dir_viento ?? '');
            $dirVientoNombre = $windNames[strtoupper($rawDir)] ?? ($rawDir ?: 'Noreste');

            $utmEasting = $m->utm_easting !== null ? (float)$m->utm_easting : 601056.668;
            $utmNorthing = $m->utm_northing !== null ? (float)$m->utm_northing : 8158253.239;
            $utmZone = $m->utm_zone ?: '20 K';

            $pointCode = !empty($m->point_number) && preg_match('/^PA-\d+/i', $m->point_number)
                ? strtoupper($m->point_number)
                : ('PA-' . ($idx + 1));

            return (object)[
                'id' => $m->id,
                'point_code' => $pointCode,
                'point_number' => $pointCode,
                'area' => $m->area ?: 'ALMACEN DE AGREGADOS',
                'punto_medicion' => $m->punto_medicion ?: ($m->workstation ?: ('Estación ' . ($idx + 1))),
                'fecha_inicio_fmt' => $fechaInicioStr,
                'hora_inicio_fmt' => $horaInicioStr,
                'fecha_fin_fmt' => $fechaFinStr,
                'hora_fin_fmt' => $horaFinStr,
                'diferencia_horas' => $difHoras,
                'pm10_filtro_inicial' => $pm10Ini,
                'pm10_filtro_final' => $pm10Fin,
                'diff_pm10' => $diffPm10,
                'pst_filtro_inicial' => $pstIni,
                'pst_filtro_final' => $pstFin,
                'diff_pst' => $diffPst,
                'temp_max' => $tempMax,
                'temp_min' => $tempMin,
                'temp_avg' => $tempAvg,
                'temp_k' => $tempK,
                'presion_atm' => $presionAtm,
                'factor_std' => $factorStd,
                'caudal' => $caudal,
                'volumen_m3' => $volumenM3,
                'c_loc_pm10' => $cLocPm10,
                'c_std_pm10' => $cStdPm10,
                'c_loc_pst' => $cLocPst,
                'c_std_pst' => $cStdPst,
                'limite_pm10' => 150,
                'limite_pst' => 260,
                'pm10_cumple' => $cStdPm10 <= 150,
                'pst_cumple' => $cStdPst <= 260,
                'vel_viento' => $velViento,
                'dir_viento' => $rawDir ?: 'NE',
                'dir_viento_nombre' => $dirVientoNombre,
                'utm_easting' => $utmEasting,
                'utm_northing' => $utmNorthing,
                'utm_zone' => $utmZone,
                'observations' => $m->observations ?: '',
            ];
        });

        return view('measurements.particulas_ambientales.report', compact(
            'module',
            'installationName',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'registeredByHeader',
            'equipmentName',
            'equipmentBrand',
            'equipmentModel',
            'equipmentSerial',
            'measurementsList'
        ));
    }

    /**
     * Registrar una nueva medición ambiental desde la interfaz web.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'fecha_inicio' => 'nullable|date',
            'hora_inicio' => 'nullable|string|max:20',
            'fecha_fin' => 'nullable|date',
            'hora_fin' => 'nullable|string|max:20',
            'diferencia_horas' => 'nullable|numeric',
            'area' => 'nullable|string|max:255',
            'punto_medicion' => 'nullable|string|max:255',
            'temp_max' => 'nullable|numeric',
            'temp_min' => 'nullable|numeric',
            'presion_atm' => 'nullable|numeric',
            'vel_viento' => 'nullable|numeric',
            'dir_viento' => 'nullable|string|max:50',
            'pm10_filtro_inicial' => 'nullable|numeric',
            'pm10_filtro_final' => 'nullable|numeric',
            'pm10_prom' => 'nullable|numeric',
            'pst_filtro_inicial' => 'nullable|numeric',
            'pst_filtro_final' => 'nullable|numeric',
            'pst_prom' => 'nullable|numeric',
            'caudal' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'images.*' => 'nullable|image|max:10240',
        ]);

        $puntoMedicion = $validated['punto_medicion'] ?? 'Estación de Muestreo';
        $fechaInicio = $validated['fecha_inicio'] ?? Carbon::now()->format('Y-m-d');
        $horaInicio = $validated['hora_inicio'] ?? '08:00';
        $fechaFin = $validated['fecha_fin'] ?? $fechaInicio;
        $horaFin = $validated['hora_fin'] ?? '08:00';

        $difHoras = $request->filled('diferencia_horas') ? (float)$request->input('diferencia_horas') : null;
        if ($difHoras === null && !empty($fechaInicio) && !empty($horaInicio) && !empty($fechaFin) && !empty($horaFin)) {
            try {
                $startDt = Carbon::parse("$fechaInicio $horaInicio");
                $endDt = Carbon::parse("$fechaFin $horaFin");
                $difHoras = round($endDt->diffInMinutes($startDt) / 60.0, 2);
            } catch (\Throwable $e) {
                $difHoras = 24.0;
            }
        }

        $caudal = $request->filled('caudal') ? (float)$request->input('caudal') : 1130.0;
        $volumenM3 = ($caudal > 0 && $difHoras > 0) ? ($caudal / 1000.0) * ($difHoras * 60.0) : 0;

        $pm10Ini = $request->filled('pm10_filtro_inicial') ? (float)$request->input('pm10_filtro_inicial') : null;
        $pm10Fin = $request->filled('pm10_filtro_final') ? (float)$request->input('pm10_filtro_final') : null;
        $pm10Prom = $request->filled('pm10_prom') ? (float)$request->input('pm10_prom') : null;
        if ($pm10Prom === null && $pm10Ini !== null && $pm10Fin !== null && $volumenM3 > 0 && $pm10Fin >= $pm10Ini) {
            $pm10Prom = round((($pm10Fin - $pm10Ini) * 1000000.0) / $volumenM3, 3);
        }

        $pstIni = $request->filled('pst_filtro_inicial') ? (float)$request->input('pst_filtro_inicial') : null;
        $pstFin = $request->filled('pst_filtro_final') ? (float)$request->input('pst_filtro_final') : null;
        $pstProm = $request->filled('pst_prom') ? (float)$request->input('pst_prom') : null;
        if ($pstProm === null && $pstIni !== null && $pstFin !== null && $volumenM3 > 0 && $pstFin >= $pstIni) {
            $pstProm = round((($pstFin - $pstIni) * 1000000.0) / $volumenM3, 3);
        }

        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $fname = 'part_amb_' . time() . '_' . uniqid() . '.' . $ext;
                    $file->move($uploadDir, $fname);
                    $uploadedImages[] = 'uploads/measurements/' . $fname;
                }
            }
        }

        $pointNumber = $validated['point_number'] ?? null;
        if (empty($pointNumber)) {
            $count = $module->particulasAmbientalesMeasurements()->count();
            $pointNumber = 'PA-' . ($count + 1);
        } elseif (!str_starts_with(strtoupper($pointNumber), 'PA-')) {
            $pointNumber = 'PA-' . ltrim($pointNumber, '0');
        }

        $tempMax = $request->filled('temp_max') ? (float)$request->input('temp_max') : null;
        $tempMin = $request->filled('temp_min') ? (float)$request->input('temp_min') : null;
        $tempProm = ($tempMax !== null && $tempMin !== null) ? round(($tempMax + $tempMin) / 2, 1) : ($request->filled('temperatura') ? (float)$request->input('temperatura') : null);

        $lat = $request->filled('latitude') ? (float)$request->input('latitude') : null;
        $lng = $request->filled('longitude') ? (float)$request->input('longitude') : null;
        $utmE = $request->filled('utm_easting') ? (float)$request->input('utm_easting') : null;
        $utmN = $request->filled('utm_northing') ? (float)$request->input('utm_northing') : null;
        $utmZone = $request->input('utm_zone') ?? '19K';

        if (($lat === null || $lng === null) && $utmE && $utmN) {
            $conv = $this->utmToLatLng($utmE, $utmN, $utmZone);
            $lat = $conv['lat'];
            $lng = $conv['lng'];
        }

        $data = [
            'module_id' => $module->id,
            'point_number' => $pointNumber,
            'measurement_date' => $fechaInicio,
            'measurement_time' => $horaInicio,
            'fecha_inicio' => $fechaInicio,
            'hora_inicio' => $horaInicio,
            'fecha_fin' => $fechaFin,
            'hora_fin' => $horaFin,
            'diferencia_horas' => $difHoras,
            'area' => $validated['area'] ?? 'Área Ambiental',
            'workstation' => $puntoMedicion,
            'punto_medicion' => $puntoMedicion,
            'temp_max' => $tempMax,
            'temp_min' => $tempMin,
            'temperatura' => $tempProm,
            'presion_atm' => $validated['presion_atm'] ?? null,
            'vel_viento' => $validated['vel_viento'] ?? null,
            'dir_viento' => $validated['dir_viento'] ?? null,
            'hr_percent' => $request->filled('hr_percent') ? (float)$request->input('hr_percent') : null,
            'pm10_filtro_inicial' => $pm10Ini,
            'pm10_filtro_final' => $pm10Fin,
            'pm10_prom' => $pm10Prom,
            'pst_filtro_inicial' => $pstIni,
            'pst_filtro_final' => $pstFin,
            'pst_prom' => $pstProm,
            'caudal' => $caudal,
            'latitude' => $lat,
            'longitude' => $lng,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmE,
            'utm_northing' => $utmN,
            'observations' => $validated['observations'] ?? null,
            'staff_id' => $validated['staff_id'] ?? null,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'image_urls' => !empty($uploadedImages) ? $uploadedImages : null,
            'image_path' => !empty($uploadedImages) ? $uploadedImages[0] : null,
            'registered_by' => Auth::user() ? Auth::user()->name : 'Técnico Ambiental',
        ];

        $module->particulasAmbientalesMeasurements()->create($data);

        $module->points_completed = $module->particulasAmbientalesMeasurements()->count();
        $module->save();

        return redirect()->route('modules.particulas_ambientales', $moduleId)->with('success', 'Estación de partículas ambientales registrada con éxito.');
    }

    /**
     * Actualizar una medición ambiental desde la interfaz web.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->particulasAmbientalesMeasurements()->findOrFail($measurementId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'fecha_inicio' => 'nullable|date',
            'hora_inicio' => 'nullable|string|max:20',
            'fecha_fin' => 'nullable|date',
            'hora_fin' => 'nullable|string|max:20',
            'diferencia_horas' => 'nullable|numeric',
            'area' => 'nullable|string|max:255',
            'punto_medicion' => 'nullable|string|max:255',
            'temp_max' => 'nullable|numeric',
            'temp_min' => 'nullable|numeric',
            'presion_atm' => 'nullable|numeric',
            'vel_viento' => 'nullable|numeric',
            'dir_viento' => 'nullable|string|max:50',
            'pm10_filtro_inicial' => 'nullable|numeric',
            'pm10_filtro_final' => 'nullable|numeric',
            'pm10_prom' => 'nullable|numeric',
            'pst_filtro_inicial' => 'nullable|numeric',
            'pst_filtro_final' => 'nullable|numeric',
            'pst_prom' => 'nullable|numeric',
            'caudal' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'images.*' => 'nullable|image|max:10240',
        ]);

        $puntoMedicion = $validated['punto_medicion'] ?? $measurement->punto_medicion;
        $fechaInicio = $validated['fecha_inicio'] ?? ($measurement->fecha_inicio ? $measurement->fecha_inicio->format('Y-m-d') : Carbon::now()->format('Y-m-d'));
        $horaInicio = $validated['hora_inicio'] ?? ($measurement->hora_inicio ?: '08:00');
        $fechaFin = $validated['fecha_fin'] ?? ($measurement->fecha_fin ? $measurement->fecha_fin->format('Y-m-d') : $fechaInicio);
        $horaFin = $validated['hora_fin'] ?? ($measurement->hora_fin ?: '08:00');

        $difHoras = $request->filled('diferencia_horas') ? (float)$request->input('diferencia_horas') : $measurement->diferencia_horas;
        if ($difHoras === null && !empty($fechaInicio) && !empty($horaInicio) && !empty($fechaFin) && !empty($horaFin)) {
            try {
                $startDt = Carbon::parse("$fechaInicio $horaInicio");
                $endDt = Carbon::parse("$fechaFin $horaFin");
                $difHoras = round($endDt->diffInMinutes($startDt) / 60.0, 2);
            } catch (\Throwable $e) {
                $difHoras = 24.0;
            }
        }

        $caudal = $request->filled('caudal') ? (float)$request->input('caudal') : ($measurement->caudal ?: 1130.0);
        $volumenM3 = ($caudal > 0 && $difHoras > 0) ? ($caudal / 1000.0) * ($difHoras * 60.0) : 0;

        $pm10Ini = $request->filled('pm10_filtro_inicial') ? (float)$request->input('pm10_filtro_inicial') : $measurement->pm10_filtro_inicial;
        $pm10Fin = $request->filled('pm10_filtro_final') ? (float)$request->input('pm10_filtro_final') : $measurement->pm10_filtro_final;
        $pm10Prom = $request->filled('pm10_prom') ? (float)$request->input('pm10_prom') : $measurement->pm10_prom;
        if ($pm10Prom === null && $pm10Ini !== null && $pm10Fin !== null && $volumenM3 > 0 && $pm10Fin >= $pm10Ini) {
            $pm10Prom = round((($pm10Fin - $pm10Ini) * 1000000.0) / $volumenM3, 3);
        }

        $pstIni = $request->filled('pst_filtro_inicial') ? (float)$request->input('pst_filtro_inicial') : $measurement->pst_filtro_inicial;
        $pstFin = $request->filled('pst_filtro_final') ? (float)$request->input('pst_filtro_final') : $measurement->pst_filtro_final;
        $pstProm = $request->filled('pst_prom') ? (float)$request->input('pst_prom') : $measurement->pst_prom;
        if ($pstProm === null && $pstIni !== null && $pstFin !== null && $volumenM3 > 0 && $pstFin >= $pstIni) {
            $pstProm = round((($pstFin - $pstIni) * 1000000.0) / $volumenM3, 3);
        }

        $existingImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        $uploadedImages = $existingImages;
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $fname = 'part_amb_' . time() . '_' . uniqid() . '.' . $ext;
                    $file->move($uploadDir, $fname);
                    $uploadedImages[] = 'uploads/measurements/' . $fname;
                }
            }
        }

        $tempMax = $request->filled('temp_max') ? (float)$request->input('temp_max') : $measurement->temp_max;
        $tempMin = $request->filled('temp_min') ? (float)$request->input('temp_min') : $measurement->temp_min;
        $tempProm = ($tempMax !== null && $tempMin !== null) ? round(($tempMax + $tempMin) / 2, 1) : $measurement->temperatura;

        $lat = $request->filled('latitude') ? (float)$request->input('latitude') : $measurement->latitude;
        $lng = $request->filled('longitude') ? (float)$request->input('longitude') : $measurement->longitude;
        $utmE = $request->filled('utm_easting') ? (float)$request->input('utm_easting') : $measurement->utm_easting;
        $utmN = $request->filled('utm_northing') ? (float)$request->input('utm_northing') : $measurement->utm_northing;
        $utmZone = $request->input('utm_zone') ?? ($measurement->utm_zone ?: '19K');

        if (($lat === null || $lng === null) && $utmE && $utmN) {
            $conv = $this->utmToLatLng($utmE, $utmN, $utmZone);
            $lat = $conv['lat'];
            $lng = $conv['lng'];
        }

        $data = [
            'point_number' => $validated['point_number'] ?? $measurement->point_number,
            'measurement_date' => $fechaInicio,
            'measurement_time' => $horaInicio,
            'fecha_inicio' => $fechaInicio,
            'hora_inicio' => $horaInicio,
            'fecha_fin' => $fechaFin,
            'hora_fin' => $horaFin,
            'diferencia_horas' => $difHoras,
            'area' => $validated['area'] ?? $measurement->area,
            'workstation' => $puntoMedicion,
            'punto_medicion' => $puntoMedicion,
            'temp_max' => $tempMax,
            'temp_min' => $tempMin,
            'temperatura' => $tempProm,
            'presion_atm' => $validated['presion_atm'] ?? $measurement->presion_atm,
            'vel_viento' => $validated['vel_viento'] ?? $measurement->vel_viento,
            'dir_viento' => $validated['dir_viento'] ?? $measurement->dir_viento,
            'hr_percent' => $request->filled('hr_percent') ? (float)$request->input('hr_percent') : $measurement->hr_percent,
            'pm10_filtro_inicial' => $pm10Ini,
            'pm10_filtro_final' => $pm10Fin,
            'pm10_prom' => $pm10Prom,
            'pst_filtro_inicial' => $pstIni,
            'pst_filtro_final' => $pstFin,
            'pst_prom' => $pstProm,
            'caudal' => $caudal,
            'latitude' => $lat,
            'longitude' => $lng,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmE,
            'utm_northing' => $utmN,
            'observations' => $validated['observations'] ?? $measurement->observations,
            'staff_id' => $validated['staff_id'] ?? $measurement->staff_id,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'image_urls' => !empty($uploadedImages) ? $uploadedImages : null,
            'image_path' => !empty($uploadedImages) ? $uploadedImages[0] : null,
        ];

        $measurement->update($data);

        return redirect()->route('modules.particulas_ambientales', $moduleId)->with('success', 'Estación de partículas ambientales actualizada correctamente.');
    }

    /**
     * Eliminar una medición.
     */
    public function destroyMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->particulasAmbientalesMeasurements()->findOrFail($measurementId);
        $measurement->delete();

        $module->points_completed = $module->particulasAmbientalesMeasurements()->count();
        $module->save();

        return redirect()->route('modules.particulas_ambientales', $moduleId)->with('success', 'Estación eliminada correctamente.');
    }

    /**
     * Guarda datos del encabezado técnico vía AJAX.
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        if ($request->has('installation_name')) {
            $module->installation_name = $request->input('installation_name');
        }
        if ($request->has('start_date') && !empty($request->input('start_date'))) {
            $module->start_date = Carbon::parse($request->input('start_date'));
        }
        if ($request->has('end_date') && !empty($request->input('end_date'))) {
            $module->end_date = Carbon::parse($request->input('end_date'));
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $request->input('monitoring_type');
        }
        if ($request->filled('equipment_id')) {
            $module->equipment_id = $request->input('equipment_id');
        }
        if ($request->filled('field_staff_ids')) {
            $ids = $request->input('field_staff_ids');
            $module->field_staff_ids = is_array($ids) ? array_map('intval', $ids) : [intval($ids)];
        }

        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Encabezado técnico actualizado correctamente.',
                'data' => [
                    'installation_name' => $module->installation_name,
                    'start_date' => $module->start_date ? $module->start_date->format('Y-m-d') : '',
                    'end_date' => $module->end_date ? $module->end_date->format('Y-m-d') : '',
                    'monitoring_type' => $module->monitoring_type
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Encabezado técnico actualizado correctamente.');
    }

    /**
     * Guarda la configuración del reporte fotográfico vía AJAX.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $settings = $request->input('settings', []);
        $module->photo_report_settings = [
            'grid' => $settings['grid'] ?? '2x3',
            'orientation' => $settings['orientation'] ?? 'landscape',
            'selected_points' => $settings['selected_points'] ?? [],
            'photo_indices' => $settings['photo_indices'] ?? (object)[],
        ];
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración de catálogo fotográfico guardada con éxito.'
        ]);
    }

    /**
     * Guarda datos del informe técnico.
     */
    public function saveReportData(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        if ($request->has('installation_name')) {
            $module->installation_name = $request->input('installation_name');
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $request->input('monitoring_type');
        }

        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Datos del informe guardados con éxito.'
        ]);
    }

    /**
     * Helper UTM a LatLng para mapas de geolocalización.
     */
    private function utmToLatLng($easting, $northing, $zoneStr = '19K')
    {
        $zoneNumber = 19;
        $isSouth = true;
        if (preg_match('/^([0-9]{1,2})\s*([C-X]?)$/i', trim($zoneStr), $matches)) {
            $zoneNumber = (int) $matches[1];
            $band = strtoupper($matches[2] ?? '');
            if ($band !== '') {
                $isSouth = ($band < 'N');
            }
        }

        $a = 6378137.0;
        $f = 1 / 298.257223563;
        $k0 = 0.9996;
        $e = sqrt(2 * $f - $f * $f);
        $e1 = (1 - sqrt(1 - $e * $e)) / (1 + sqrt(1 - $e * $e));

        $x = (float)$easting - 500000.0;
        $y = $isSouth ? ((float)$northing - 10000000.0) : (float)$northing;

        $m = $y / $k0;
        $mu = $m / ($a * (1 - ($e * $e) / 4 - 3 * pow($e, 4) / 64 - 5 * pow($e, 6) / 256));

        $phi1Rad = $mu
            + (3 * $e1 / 2 - 27 * pow($e1, 3) / 32) * sin(2 * $mu)
            + (21 * pow($e1, 2) / 16 - 55 * pow($e1, 4) / 32) * sin(4 * $mu)
            + (151 * pow($e1, 3) / 96) * sin(6 * $mu)
            + (1097 * pow($e1, 4) / 512) * sin(8 * $mu);

        $n1 = $a / sqrt(1 - pow($e * sin($phi1Rad), 2));
        $t1 = tan($phi1Rad) * tan($phi1Rad);
        $c1 = ($e * $e / (1 - $e * $e)) * cos($phi1Rad) * cos($phi1Rad);
        $r1 = $a * (1 - $e * $e) / pow(1 - pow($e * sin($phi1Rad), 2), 1.5);
        $d = $x / ($n1 * $k0);

        $latRad = $phi1Rad - ($n1 * tan($phi1Rad) / $r1) * (
            pow($d, 2) / 2
            - (5 + 3 * $t1 + 10 * $c1 - 4 * $c1 * $c1 - 9 * ($e * $e / (1 - $e * $e))) * pow($d, 4) / 24
            + (61 + 90 * $t1 + 298 * $c1 + 45 * $t1 * $t1 - 252 * ($e * $e / (1 - $e * $e)) - 3 * $c1 * $c1) * pow($d, 6) / 720
        );
        $lat = rad2deg($latRad);

        $lngRad = (
            $d
            - (1 + 2 * $t1 + $c1) * pow($d, 3) / 6
            + (5 - 2 * $c1 + 28 * $t1 - 3 * $c1 * $c1 + 8 * ($e * $e / (1 - $e * $e)) + 24 * $t1 * $t1) * pow($d, 5) / 120
        ) / cos($phi1Rad);
        $lng = (($zoneNumber - 1) * 6 - 180 + 3) + rad2deg($lngRad);

        return ['lat' => $lat, 'lng' => $lng];
    }
}
