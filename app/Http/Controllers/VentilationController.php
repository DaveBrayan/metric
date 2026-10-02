<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\VentilationMeasurement;
use App\Models\MeasurementModule;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class VentilationController extends Controller
{
    public static $tiposLocalNorma = [
        '1. Aseos: Públicos' => ['min' => 10, 'max' => 15, 'intervalo' => '10 - 15'],
        '2. Aseos: En fábricas' => ['min' => 8, 'max' => 10, 'intervalo' => '8 - 10'],
        '3. Aseos: En oficinas' => ['min' => 5, 'max' => 8, 'intervalo' => '5 - 8'],
        '4. Aseos: En viviendas en campamentos' => ['min' => 3, 'max' => 4, 'intervalo' => '3 - 4'],
        '5. Aulas y ambientes educativos' => ['min' => 6, 'max' => 8, 'intervalo' => '6 - 8'],
        '6. Centros de documentación, bibliotecas, archivos' => ['min' => 4, 'max' => 8, 'intervalo' => '4 - 8'],
        '7. Lugares de expendio de bebidas o diversión' => ['min' => 6, 'max' => 8, 'intervalo' => '6 - 8'],
        '8. Salas de actos públicos' => ['min' => 6, 'max' => 12, 'intervalo' => '6 - 12'],
        '9. Duchas y vestidores' => ['min' => 10, 'max' => 15, 'intervalo' => '10 - 15'],
        '10. Vestidores (guardarropas)' => ['min' => 4, 'max' => 6, 'intervalo' => '4 - 6'],
        '11. Centros educativos o de entrenamiento:' => ['min' => 2, 'max' => 8, 'intervalo' => '2 - 8'],
        '12. Centros educativos o de entrenamiento: Aulas' => ['min' => 4, 'max' => 5, 'intervalo' => '4 - 5'],
        '13. Centros educativos o de entrenamiento: Pasillos, cajas de escaleras' => ['min' => 2, 'max' => 3, 'intervalo' => '2 - 3'],
        '14. Centros educativos o de entrenamiento: Aseos' => ['min' => 5, 'max' => 8, 'intervalo' => '5 - 8'],
        '15. Centros educativos o de entrenamiento: Gimnasios' => ['min' => 2, 'max' => 3, 'intervalo' => '2 - 3'],
        '16. Centros educativos o de entrenamiento: Piscinas de aprendizaje cubiertas' => ['min' => 2, 'max' => 3, 'intervalo' => '2 - 3'],
        '17. Centros educativos o de entrenamiento: Baño y lavados' => ['min' => 5, 'max' => 8, 'intervalo' => '5 - 8'],
        '18. Almacenes en general' => ['min' => 6, 'max' => 10, 'intervalo' => '6 - 10'],
        '19. Garajes cerrados' => ['min' => 5, 'max' => 15, 'intervalo' => '5 - 15'],
        '20. Hospitales o centros de salud: Salas de consulta y tratamiento' => ['min' => 3, 'max' => 5, 'intervalo' => '3 - 5'],
        '21. Hospitales o centros de salud: Salas de hospitalización' => ['min' => 2, 'max' => 5, 'intervalo' => '2 - 5'],
        '22. Hospitales o centros de salud: Baños' => ['min' => 5, 'max' => 8, 'intervalo' => '5 - 8'],
        '23. Hospitales o centros de salud: Aseos' => ['min' => 8, 'max' => 15, 'intervalo' => '8 - 15'],
        '24. Hospitales o centros de salud: Grupo de quirófanos' => ['min' => 5, 'max' => 12, 'intervalo' => '5 - 12'],
        '25. Hospitales o centros de salud: Otros ambientes' => ['min' => 3, 'max' => 5, 'intervalo' => '3 - 5'],
        '26. Locales de trabajo en general' => ['min' => 3, 'max' => 8, 'intervalo' => '3 - 8'],
        '27. Cines, teatros y centros de diversión:' => ['min' => 4, 'max' => 8, 'intervalo' => '4 - 8'],
        '28. Cines, teatros y centros de diversión: Con prohibición de fumar' => ['min' => 4, 'max' => 6, 'intervalo' => '4 - 6'],
        '29. Cines, teatros y centros de diversión: Sin prohibición de fumar' => ['min' => 5, 'max' => 8, 'intervalo' => '5 - 8'],
        '30. Ambientes cerrados donde se realicen montajes' => ['min' => 4, 'max' => 10, 'intervalo' => '4 - 10'],
        '31. Oficinas' => ['min' => 4, 'max' => 8, 'intervalo' => '4 - 8'],
        '32. Salas de exposiciones o de arte' => ['min' => 2, 'max' => 3, 'intervalo' => '2 - 3'],
        '33. Restaurantes o expendio de alimentos' => ['min' => 5, 'max' => 10, 'intervalo' => '5 - 10'],
        '34. Piscinas cubiertas' => ['min' => 3, 'max' => 5, 'intervalo' => '3 - 5'],
        '35. Tiendas o centros comerciales' => ['min' => 6, 'max' => 8, 'intervalo' => '6 - 8'],
        '36. Cocinas: Pequeña (2,5 m a 3,5 m altura)' => ['min' => 15, 'max' => 25, 'intervalo' => '15 - 25'],
        '37. Cocinas: Media (3 m a 4 m altura)' => ['min' => 20, 'max' => 30, 'intervalo' => '20 - 30'],
        '38. Cocinas: Grande (4 m a 6 m altura)' => ['min' => 15, 'max' => 20, 'intervalo' => '15 - 20'],
        '39. Salas de reuniones' => ['min' => 5, 'max' => 10, 'intervalo' => '5 - 10'],
        '40. Salas de medición y de verificación' => ['min' => 8, 'max' => 15, 'intervalo' => '8 - 15'],
        '41. Lavanderías: Sala de lavado' => ['min' => 15, 'max' => 20, 'intervalo' => '15 - 20'],
        '42. Lavanderías: Sala de planchado' => ['min' => 10, 'max' => 15, 'intervalo' => '10 - 15'],
        '43. Lavanderías: Sala de calandria o prensado' => ['min' => 10, 'max' => 15, 'intervalo' => '10 - 15'],
        '44. Talleres mecánicos o eléctricos' => ['min' => 3, 'max' => 8, 'intervalo' => '3 - 8'],
    ];

    /**
     * Resuelve los límites normativos para un tipo de local dado.
     */
    public static function getNormaForTipoLocal($tipoLocal)
    {
        if (empty($tipoLocal)) {
            return self::$tiposLocalNorma['26. Locales de trabajo en general'];
        }
        if (isset(self::$tiposLocalNorma[$tipoLocal])) {
            return self::$tiposLocalNorma[$tipoLocal];
        }
        $cleanSearch = preg_replace('/^\d+\.\s*/', '', trim($tipoLocal));
        foreach (self::$tiposLocalNorma as $key => $norma) {
            $cleanKey = preg_replace('/^\d+\.\s*/', '', trim($key));
            if (strcasecmp($cleanSearch, $cleanKey) === 0) {
                return $norma;
            }
        }
        return ['min' => 3, 'max' => 8, 'intervalo' => '3 - 8'];
    }

    /**
     * Display the ventilation monitoring page for a module.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Resuelve información técnica del encabezado: Razón Social de la empresa o proyecto para Instalación
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
        if (empty($module->installation_name) || $module->installation_name === $oldDefault || $module->installation_name === 'PACHABOL - PLANTA CENTRAL EMV') {
            $installationName = $razonSocial ?: ($module->installation_name ?: 'Instalación');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Seguimiento';

        // Equipo asignado (Anemómetro o Termoanemómetro)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Termo-Anemómetro');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Testo') : 'Testo';
        $equipmentModel = $equipment ? ($equipment->model ?: '410-1') : '410-1';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: '61452984') : '61452984';
        $equipmentImage = ($equipment && $equipment->image) ? asset($equipment->image) : null;

        // Personal asignado para selector y encabezado técnico
        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
            $staffList = $assignedStaff;
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico de Campo';
            $staffList = Staff::orderBy('name')->get();
        }

        // Mediciones registradas
        $measurements = $module->ventilationMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateFormatted = $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—';
            
            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

            // Extraer y convertir UTM si falta lat/lng
            if (($lat === null || $lng === null) && !empty($item->location)) {
                $loc = trim($item->location);
                if (str_starts_with($loc, '{') || str_starts_with($loc, '[')) {
                    $d = json_decode($loc, true);
                    if (is_array($d)) {
                        if (!empty($d['latitude']) && !empty($d['longitude'])) {
                            $lat = (float) $d['latitude'];
                            $lng = (float) $d['longitude'];
                        } elseif (!empty($d['easting']) && !empty($d['northing'])) {
                            $conv = $this->utmToLatLng($d['easting'], $d['northing'], $d['utm_zone'] ?? $d['zone'] ?? '20K');
                            $lat = $conv['lat'];
                            $lng = $conv['lng'];
                        }
                    }
                } elseif (preg_match('/E:\s*([0-9.]+)/i', $loc, $mE) && preg_match('/N:\s*([0-9.]+)/i', $loc, $mN)) {
                    preg_match('/Z:\s*([0-9A-Za-z]+)/i', $loc, $mZ);
                    $conv = $this->utmToLatLng((float)$mE[1], (float)$mN[1], $mZ[1] ?? '20K');
                    $lat = $conv['lat'];
                    $lng = $conv['lng'];
                }
            }

            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function($p) {
                return $p ? asset($p) : null;
            }, $rawImages)));

            $velMs = (float) ($item->vel_aire_ms ?? 0);
            $velMh = (float) ($item->vel_aire_mh ?? ($velMs * 3600.0));
            $areaLargo = (float) ($item->area_largo_m ?? 0);
            $areaAncho = (float) ($item->area_ancho_m ?? 0);
            $areaDiametro = (float) ($item->area_diametro_m ?? 0);
            // Fórmula Área de ventilación (en informe): =(DATOS!G3*DATOS!H3)+(3,1416*DATOS!I3)
            $areaM2 = ($areaLargo * $areaAncho) + (3.1416 * $areaDiametro);
            if ($areaM2 <= 0 && !empty($item->area_ventilacion_m2)) {
                $areaM2 = (float) $item->area_ventilacion_m2;
            }

            $volLargo = (float) ($item->vol_largo_m ?? 0);
            $volAncho = (float) ($item->vol_ancho_m ?? 0);
            $volAlto = (float) ($item->vol_alto_m ?? 0);
            $volM3 = ($volLargo * $volAncho * $volAlto);
            if ($volM3 <= 0 && !empty($item->volumen_m3)) {
                $volM3 = (float) $item->volumen_m3;
            }

            // Caudal de extracción o inyección de aire = F9 * G9 = vel_aire_ms * area_ventilacion
            $caudalM3h = $velMs * $areaM2;

            // Fórmula Renovaciones/h en Datos: =SI.ERROR(3600*((F3*((G3*H3)+((3,1416/4)*(I3*I3))))/(J3*K3*L3));"")
            $areaRenov = ($areaLargo * $areaAncho) + ((3.1416 / 4.0) * ($areaDiametro * $areaDiametro));
            $renovH = ($volM3 > 0) ? (3600.0 * (($velMs * $areaRenov) / $volM3)) : (float) ($item->renovaciones_h ?? 0);
            
            $tipoLocal = $item->tipo_local ?: '26. Locales de trabajo en general';
            $norma = self::getNormaForTipoLocal($tipoLocal);
            $renovMin = $item->renovaciones_min !== null ? (float)$item->renovaciones_min : ($norma['min'] ?? null);
            $renovMax = $item->renovaciones_max !== null ? (float)$item->renovaciones_max : ($norma['max'] ?? null);
            $intervalo = $item->renovaciones_intervalo ?: ($norma['intervalo'] ?? ($renovMin && $renovMax ? "{$renovMin} - {$renovMax}" : '—'));
            
            // Fórmula Cumple: =SI.ERROR(SI(DATOS!M3>=DATOS!O3;"SI";"NO");"")
            $isCompliant = ($renovMin !== null && $renovMin > 0) ? ($renovH >= $renovMin) : true;
            $cumple = $isCompliant ? 'SI' : 'NO';

            return [
                'id' => $item->id,
                'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'date' => $dateFormatted,
                'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                'time' => $item->measurement_time ?: '—',
                'local_trabajo' => $item->local_trabajo ?: 'Área Principal',
                'tipo_local' => $item->tipo_local ?: 'Locales de trabajo en general',
                'tipo_ventilacion' => $item->tipo_ventilacion ?: 'Natural',
                'elemento_ventilacion' => $item->elemento_ventilacion ?: 'Ventana',
                'temperatura_seca_c' => $item->temperatura_seca_c !== null ? number_format((float)$item->temperatura_seca_c, 1) : '—',
                'raw_temperatura_seca_c' => (float)$item->temperatura_seca_c,
                'vel_aire_ms' => number_format($velMs, 2),
                'raw_vel_aire_ms' => $velMs,
                'vel_aire_mh' => number_format($velMh, 2),
                'raw_vel_aire_mh' => $velMh,
                'area_largo_m' => (float)($item->area_largo_m ?? 0),
                'area_ancho_m' => (float)($item->area_ancho_m ?? 0),
                'area_diametro_m' => (float)($item->area_diametro_m ?? 0),
                'area_ventilacion_m2' => number_format($areaM2, 4),
                'raw_area_ventilacion_m2' => $areaM2,
                'caudal_m3h' => number_format($caudalM3h, 2),
                'raw_caudal_m3h' => $caudalM3h,
                'vol_largo_m' => (float)($item->vol_largo_m ?? 0),
                'vol_ancho_m' => (float)($item->vol_ancho_m ?? 0),
                'vol_alto_m' => (float)($item->vol_alto_m ?? 0),
                'volumen_m3' => number_format($volM3, 2),
                'raw_volumen_m3' => $volM3,
                'renovaciones_h' => number_format($renovH, 2),
                'raw_renovaciones_h' => $renovH,
                'renovaciones_min' => $renovMin,
                'renovaciones_max' => $renovMax,
                'renovaciones_intervalo' => $intervalo,
                'cumple' => $cumple,
                'is_compliant' => $isCompliant,
                'image_path' => $item->image_path ? asset($item->image_path) : ($imagesUrls[0] ?? null),
                'raw_image_path' => $item->image_path,
                'images' => $imagesUrls,
                'images_count' => count($imagesUrls),
                'location' => $item->location ?: ($lat && $lng ? "{$lat}, {$lng}" : '—'),
                'latitude' => $lat,
                'longitude' => $lng,
                'utm_zone' => $item->utm_zone ?: '20K',
                'utm_easting' => (float)$item->utm_easting,
                'utm_northing' => (float)$item->utm_northing,
                'observations' => $item->observations ?: 'Sin observaciones',
                'raw_observations' => $item->observations,
                'registered_by' => (function() use ($item, $assignedStaff, $currentUser) {
                    if (!empty($item->registered_by) && !is_numeric($item->registered_by)) {
                        return $item->registered_by;
                    }
                    if ($item->staff) {
                        return $item->staff->full_name ?: $item->staff->name;
                    }
                    if (!empty($item->staff_id)) {
                        $st = Staff::find($item->staff_id);
                        if ($st) return $st->full_name ?: $st->name;
                    }
                    if (!empty($item->registered_by) && is_numeric($item->registered_by)) {
                        $st = Staff::find((int) $item->registered_by);
                        if ($st) return $st->full_name ?: $st->name;
                        $u = \App\Models\User::find((int) $item->registered_by);
                        if ($u) return $u->name;
                    }
                    if ($assignedStaff && $assignedStaff->isNotEmpty()) {
                        return $assignedStaff->first()->full_name ?: $assignedStaff->first()->name;
                    }
                    return $currentUser ? $currentUser->name : 'Técnico de Campo';
                })(),
                'staff_id' => $item->staff_id,
            ];
        });

        // Configuración para el Reporte Fotográfico
        $savedSettings = $module->photo_report_settings ?: [];
        $photoReportSettings = [
            'grid' => $savedSettings['grid'] ?? '2x3',
            'orientation' => $savedSettings['orientation'] ?? 'landscape',
            'selected_points' => $savedSettings['selected_points'] ?? $measurements->pluck('id')->toArray(),
            'photo_indices' => $savedSettings['photo_indices'] ?? (object)[],
        ];

        $totalMeasurements = $measurements->count();

        return view('measurements.ventilaciones.index', compact(
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
            'photoReportSettings'
        ));
    }

    /**
     * Store a new ventilation measurement point.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'local_trabajo' => 'required|string|max:255',
            'tipo_local' => 'nullable|string|max:255',
            'tipo_ventilacion' => 'required|string|in:Natural,Mecánica',
            'elemento_ventilacion' => 'nullable|string|max:255',
            'temperatura_seca_c' => 'nullable|numeric',
            'vel_aire_ms' => 'required|numeric|min:0',
            'area_largo_m' => 'nullable|numeric|min:0',
            'area_ancho_m' => 'nullable|numeric|min:0',
            'area_diametro_m' => 'nullable|numeric|min:0',
            'vol_largo_m' => 'nullable|numeric|min:0',
            'vol_ancho_m' => 'nullable|numeric|min:0',
            'vol_alto_m' => 'nullable|numeric|min:0',
            'renovaciones_min' => 'nullable|numeric|min:0',
            'renovaciones_max' => 'nullable|numeric|min:0',
            'renovaciones_intervalo' => 'nullable|string|max:50',
            'cumple' => 'nullable|string|max:30',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
        ]);

        // Cálculos físicos y de ingeniería
        $velMs = (float) $validated['vel_aire_ms'];
        $velMh = $velMs * 3600.0;

        $largo = (float) ($validated['area_largo_m'] ?? 0);
        $ancho = (float) ($validated['area_ancho_m'] ?? 0);
        $diametro = (float) ($validated['area_diametro_m'] ?? 0);

        // Fórmula Área de ventilación (en informe): =(DATOS!G3*DATOS!H3)+(3,1416*DATOS!I3)
        $areaM2 = ($largo * $ancho) + (3.1416 * $diametro);
        $caudalM3h = $velMs * $areaM2;

        $volLargo = (float) ($validated['vol_largo_m'] ?? 0);
        $volAncho = (float) ($validated['vol_ancho_m'] ?? 0);
        $volAlto = (float) ($validated['vol_alto_m'] ?? 0);
        $volumenM3 = ($volLargo * $volAncho * $volAlto);

        // Fórmula Renovaciones/h en Datos: =SI.ERROR(3600*((F3*((G3*H3)+((3,1416/4)*(I3*I3))))/(J3*K3*L3));"")
        $areaRenov = ($largo * $ancho) + ((3.1416 / 4.0) * ($diametro * $diametro));
        $renovH = $volumenM3 > 0 ? (3600.0 * (($velMs * $areaRenov) / $volumenM3)) : 0.0;

        // Referenciales
        $tipoLocal = $validated['tipo_local'] ?? '26. Locales de trabajo en general';
        $norma = self::getNormaForTipoLocal($tipoLocal);
        $renovMin = $validated['renovaciones_min'] ?? ($norma['min'] ?? null);
        $renovMax = $validated['renovaciones_max'] ?? ($norma['max'] ?? null);
        $intervalo = $validated['renovaciones_intervalo'] ?? ($norma['intervalo'] ?? ($renovMin && $renovMax ? "{$renovMin} - {$renovMax}" : ''));
        
        $cumple = ($renovMin !== null && $renovMin > 0) ? ($renovH >= $renovMin ? 'SI' : 'NO') : 'SI';

        // Subida de imágenes con compresión adaptativa
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $uploadedImages[] = $this->saveAdaptiveImage($file, $uploadDir);
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                array_unshift($uploadedImages, $this->saveAdaptiveImage($file, $uploadDir));
            }
        }
        $imagePath = $uploadedImages[0] ?? null;

        // Registrador
        $registeredByName = $validated['registered_by'] ?? null;
        if (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            }
        }
        if (empty($registeredByName) || is_numeric($registeredByName)) {
            $registeredByName = Auth::user() ? Auth::user()->name : 'Técnico de Campo';
        }

        $measurementCount = $module->ventilationMeasurements()->count();
        $pointNumber = !empty($validated['point_number']) 
            ? $validated['point_number'] 
            : str_pad($measurementCount + 1, 2, '0', STR_PAD_LEFT);

        $module->ventilationMeasurements()->create([
            'point_number' => $pointNumber,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? Carbon::now()->format('H:i'),
            'local_trabajo' => $validated['local_trabajo'],
            'tipo_local' => $tipoLocal,
            'tipo_ventilacion' => $validated['tipo_ventilacion'],
            'elemento_ventilacion' => $validated['elemento_ventilacion'] ?? 'Ventana',
            'temperatura_seca_c' => $validated['temperatura_seca_c'] ?? null,
            'vel_aire_ms' => $velMs,
            'vel_aire_mh' => $velMh,
            'area_largo_m' => $largo,
            'area_ancho_m' => $ancho,
            'area_diametro_m' => $diametro,
            'area_ventilacion_m2' => $areaM2,
            'caudal_m3h' => $caudalM3h,
            'vol_largo_m' => $volLargo,
            'vol_ancho_m' => $volAncho,
            'vol_alto_m' => $volAlto,
            'volumen_m3' => $volumenM3,
            'renovaciones_h' => $renovH,
            'renovaciones_min' => $renovMin,
            'renovaciones_max' => $renovMax,
            'renovaciones_intervalo' => $intervalo,
            'cumple' => $cumple,
            'image_path' => $imagePath,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $validated['location'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'utm_zone' => $validated['utm_zone'] ?? '20K',
            'utm_easting' => $validated['utm_easting'] ?? null,
            'utm_northing' => $validated['utm_northing'] ?? null,
            'observations' => $validated['observations'] ?? null,
            'registered_by' => $registeredByName,
            'staff_id' => $validated['staff_id'] ?? null,
        ]);

        $module->points_completed = $module->ventilationMeasurements()->count();
        $module->save();

        return redirect()->route('modules.ventilation', $moduleId)
            ->with('success', "Punto de medición de ventilación #{$pointNumber} registrado exitosamente.");
    }

    /**
     * Update an existing ventilation measurement point.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->ventilationMeasurements()->findOrFail($measurementId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'local_trabajo' => 'required|string|max:255',
            'tipo_local' => 'nullable|string|max:255',
            'tipo_ventilacion' => 'required|string|in:Natural,Mecánica',
            'elemento_ventilacion' => 'nullable|string|max:255',
            'temperatura_seca_c' => 'nullable|numeric',
            'vel_aire_ms' => 'required|numeric|min:0',
            'area_largo_m' => 'nullable|numeric|min:0',
            'area_ancho_m' => 'nullable|numeric|min:0',
            'area_diametro_m' => 'nullable|numeric|min:0',
            'vol_largo_m' => 'nullable|numeric|min:0',
            'vol_ancho_m' => 'nullable|numeric|min:0',
            'vol_alto_m' => 'nullable|numeric|min:0',
            'renovaciones_min' => 'nullable|numeric|min:0',
            'renovaciones_max' => 'nullable|numeric|min:0',
            'renovaciones_intervalo' => 'nullable|string|max:50',
            'cumple' => 'nullable|string|max:30',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
        ]);

        $velMs = (float) $validated['vel_aire_ms'];
        $velMh = $velMs * 3600.0;

        $largo = (float) ($validated['area_largo_m'] ?? 0);
        $ancho = (float) ($validated['area_ancho_m'] ?? 0);
        $diametro = (float) ($validated['area_diametro_m'] ?? 0);

        // Fórmula Área de ventilación (en informe): =(DATOS!G3*DATOS!H3)+(3,1416*DATOS!I3)
        $areaM2 = ($largo * $ancho) + (3.1416 * $diametro);
        $caudalM3h = $velMs * $areaM2;

        $volLargo = (float) ($validated['vol_largo_m'] ?? 0);
        $volAncho = (float) ($validated['vol_ancho_m'] ?? 0);
        $volAlto = (float) ($validated['vol_alto_m'] ?? 0);
        $volumenM3 = ($volLargo * $volAncho * $volAlto);

        // Fórmula Renovaciones/h en Datos: =SI.ERROR(3600*((F3*((G3*H3)+((3,1416/4)*(I3*I3))))/(J3*K3*L3));"")
        $areaRenov = ($largo * $ancho) + ((3.1416 / 4.0) * ($diametro * $diametro));
        $renovH = $volumenM3 > 0 ? (3600.0 * (($velMs * $areaRenov) / $volumenM3)) : 0.0;

        $tipoLocal = $validated['tipo_local'] ?? ($measurement->tipo_local ?: '26. Locales de trabajo en general');
        $norma = self::getNormaForTipoLocal($tipoLocal);
        $renovMin = $validated['renovaciones_min'] ?? ($norma['min'] ?? $measurement->renovaciones_min);
        $renovMax = $validated['renovaciones_max'] ?? ($norma['max'] ?? $measurement->renovaciones_max);
        $intervalo = $validated['renovaciones_intervalo'] ?? ($norma['intervalo'] ?? ($renovMin && $renovMax ? "{$renovMin} - {$renovMax}" : ''));
        
        $cumple = ($renovMin !== null && $renovMin > 0) ? ($renovH >= $renovMin ? 'SI' : 'NO') : 'SI';

        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $uploadedImages[] = $this->saveAdaptiveImage($file, $uploadDir);
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                array_unshift($uploadedImages, $this->saveAdaptiveImage($file, $uploadDir));
            }
        }

        $normalizePath = function($url) {
            if (!$url || !is_string($url)) return '';
            $parsed = parse_url($url, PHP_URL_PATH);
            $path = $parsed ?: $url;
            return ltrim($path, '/\\');
        };

        $existingImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        if (empty($existingImages) && !empty($measurement->image_path)) {
            $existingImages = [$measurement->image_path];
        }

        if ($request->has('remaining_images')) {
            $rawRemaining = $request->input('remaining_images');
            $remaining = is_array($rawRemaining) ? $rawRemaining : json_decode($rawRemaining, true);
            if (is_array($remaining)) {
                $normalizedRemaining = array_map($normalizePath, $remaining);
                $filteredExisting = [];
                foreach ($existingImages as $img) {
                    $norm = $normalizePath($img);
                    if (in_array($norm, $normalizedRemaining) || in_array($img, $remaining)) {
                        $filteredExisting[] = $img;
                    }
                }
                $existingImages = $filteredExisting;
            }
        }

        if (!empty($uploadedImages)) {
            $allImages = array_values(array_merge($existingImages, $uploadedImages));
            $imagePath = $allImages[0] ?? null;
        } else {
            $allImages = array_values($existingImages);
            $imagePath = $allImages[0] ?? null;
        }

        $registeredByName = $validated['registered_by'] ?? $measurement->registered_by;
        if (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            }
        }
        if (empty($registeredByName) || is_numeric($registeredByName)) {
            if ($measurement->staff) {
                $registeredByName = $measurement->staff->full_name ?: $measurement->staff->name;
            } elseif ($module->fieldStaff) {
                $registeredByName = $module->fieldStaff->full_name ?: $module->fieldStaff->name;
            } else {
                $registeredByName = Auth::user() ? Auth::user()->name : 'Técnico de Campo';
            }
        }

        $measurement->update([
            'point_number' => $validated['point_number'] ?? $measurement->point_number,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? $measurement->measurement_time,
            'local_trabajo' => $validated['local_trabajo'],
            'tipo_local' => $tipoLocal,
            'tipo_ventilacion' => $validated['tipo_ventilacion'],
            'elemento_ventilacion' => $validated['elemento_ventilacion'] ?? $measurement->elemento_ventilacion,
            'temperatura_seca_c' => array_key_exists('temperatura_seca_c', $validated) ? $validated['temperatura_seca_c'] : $measurement->temperatura_seca_c,
            'vel_aire_ms' => $velMs,
            'vel_aire_mh' => $velMh,
            'area_largo_m' => $largo,
            'area_ancho_m' => $ancho,
            'area_diametro_m' => $diametro,
            'area_ventilacion_m2' => $areaM2,
            'caudal_m3h' => $caudalM3h,
            'vol_largo_m' => $volLargo,
            'vol_ancho_m' => $volAncho,
            'vol_alto_m' => $volAlto,
            'volumen_m3' => $volumenM3,
            'renovaciones_h' => $renovH,
            'renovaciones_min' => $renovMin,
            'renovaciones_max' => $renovMax,
            'renovaciones_intervalo' => $intervalo,
            'cumple' => $cumple,
            'image_path' => $imagePath,
            'images' => !empty($allImages) ? $allImages : null,
            'location' => $validated['location'] ?? null,
            'latitude' => array_key_exists('latitude', $validated) ? $validated['latitude'] : $measurement->latitude,
            'longitude' => array_key_exists('longitude', $validated) ? $validated['longitude'] : $measurement->longitude,
            'utm_zone' => $validated['utm_zone'] ?? $measurement->utm_zone,
            'utm_easting' => array_key_exists('utm_easting', $validated) ? $validated['utm_easting'] : $measurement->utm_easting,
            'utm_northing' => array_key_exists('utm_northing', $validated) ? $validated['utm_northing'] : $measurement->utm_northing,
            'observations' => $validated['observations'] ?? null,
            'registered_by' => $registeredByName,
            'staff_id' => $validated['staff_id'] ?? null,
        ]);

        return redirect()->route('modules.ventilation', $moduleId)
            ->with('success', "Punto de medición #{$measurement->point_number} actualizado correctamente.");
    }

    /**
     * Delete a ventilation measurement point.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->ventilationMeasurements()->findOrFail($measurementId);
        $pointNum = $measurement->point_number;
        $measurement->delete();

        $module->points_completed = $module->ventilationMeasurements()->count();
        $module->save();

        return redirect()->route('modules.ventilation', $moduleId)
            ->with('success', "Punto de medición #{$pointNum} eliminado exitosamente.");
    }

    /**
     * Update technical header metadata (Instalación, Fechas, Tipo de Monitoreo).
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'installation_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'monitoring_type' => 'nullable|string|max:255',
        ]);

        if ($request->has('installation_name')) {
            $module->installation_name = $validated['installation_name'] ?? '';
        }
        if ($request->has('start_date')) {
            $module->start_date = $validated['start_date'] ?: null;
        }
        if ($request->has('end_date')) {
            $module->end_date = $validated['end_date'] ?: null;
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $validated['monitoring_type'] ?? '';
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
                    'monitoring_type' => $module->monitoring_type,
                ]
            ]);
        }

        return redirect()->route('modules.ventilation', $moduleId)
            ->with('success', 'Datos del encabezado técnico actualizados exitosamente.');
    }

    /**
     * Save photo report settings (grid distribution, orientation, selected points, chosen photo per point).
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'grid' => 'nullable|string|in:2x3,2x4,3x3,3x4',
            'orientation' => 'nullable|string|in:landscape,portrait',
            'selected_points' => 'nullable|array',
            'photo_indices' => 'nullable|array',
        ]);

        $currentSettings = $module->photo_report_settings ?: [];

        $newSettings = [
            'grid' => $validated['grid'] ?? ($currentSettings['grid'] ?? '2x3'),
            'orientation' => $validated['orientation'] ?? ($currentSettings['orientation'] ?? 'landscape'),
            'selected_points' => array_key_exists('selected_points', $validated) ? $validated['selected_points'] : ($currentSettings['selected_points'] ?? []),
            'photo_indices' => array_key_exists('photo_indices', $validated) ? $validated['photo_indices'] : ($currentSettings['photo_indices'] ?? []),
            'updated_at' => now()->toIso8601String(),
        ];

        $module->photo_report_settings = $newSettings;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración del reporte fotográfico guardada exitosamente.',
            'settings' => $newSettings,
        ]);
    }

    /**
     * Procesa y guarda una imagen de forma adaptativa según su peso y dimensiones.
     */
    protected function saveAdaptiveImage($uploadedFile, $uploadDir)
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $safeExt = ($extension === 'jpeg' || $extension === 'jpg') ? 'jpg' : $extension;
        $fileName = 'vent_' . time() . '_' . uniqid() . '.' . $safeExt;
        $destinationPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        $relativePath = 'uploads/measurements/' . $fileName;

        $sourcePath = $uploadedFile->getRealPath();
        $fileSize = $uploadedFile->getSize();

        $imgInfo = @getimagesize($sourcePath);
        if (!$imgInfo) {
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        $srcWidth = $imgInfo[0];
        $srcHeight = $imgInfo[1];
        $imageType = $imgInfo[2];
        $maxDimension = max($srcWidth, $srcHeight);

        if ($fileSize <= 1.2 * 1024 * 1024 && $maxDimension <= 1920) {
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        if ($fileSize <= 4 * 1024 * 1024 && $maxDimension <= 2800) {
            $targetMaxDim = 2560;
            $quality = 92;
        } elseif ($fileSize <= 9 * 1024 * 1024 && $maxDimension <= 4500) {
            $targetMaxDim = 2200;
            $quality = 90;
        } else {
            $targetMaxDim = 2048;
            $quality = 88;
        }

        if ($maxDimension > $targetMaxDim) {
            if ($srcWidth >= $srcHeight) {
                $targetWidth = $targetMaxDim;
                $targetHeight = (int) round(($srcHeight * $targetMaxDim) / $srcWidth);
            } else {
                $targetHeight = $targetMaxDim;
                $targetWidth = (int) round(($srcWidth * $targetMaxDim) / $srcHeight);
            }
        } else {
            $targetWidth = $srcWidth;
            $targetHeight = $srcHeight;
        }

        $srcImage = null;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $srcImage = @imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = @imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $srcImage = @imagecreatefromwebp($sourcePath);
                }
                break;
        }

        if (!$srcImage) {
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($imageType === IMAGETYPE_PNG) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $transparent);
        } else {
            $white = imagecolorallocate($dstImage, 255, 255, 255);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $white);
        }

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);

        $outputFileName = 'vent_' . time() . '_' . uniqid() . '.jpg';
        $outputDestPath = $uploadDir . DIRECTORY_SEPARATOR . $outputFileName;
        
        $saved = imagejpeg($dstImage, $outputDestPath, $quality);

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        if ($saved && file_exists($outputDestPath)) {
            return 'uploads/measurements/' . $outputFileName;
        }

        $uploadedFile->move($uploadDir, $fileName);
        return $relativePath;
    }

    /**
     * Convierte coordenadas UTM a Latitud/Longitud WGS84.
     */
    private function utmToLatLng($utmEasting, $utmNorthing, $utmZoneStr = '20K')
    {
        $utmZoneNum = 20;
        $utmZoneLetter = 'K';
        if (is_string($utmZoneStr) && preg_match('/(\d+)\s*([A-Za-z]?)/', $utmZoneStr, $utmM)) {
            $utmZoneNum = (int) $utmM[1] ?: 20;
            $utmZoneLetter = strtoupper($utmM[2] ?? 'K');
        } elseif (is_numeric($utmZoneStr)) {
            $utmZoneNum = (int) $utmZoneStr;
        }

        $utmA = 6378137.0;
        $utmF = 1 / 298.257223563;
        $utmB = $utmA * (1 - $utmF);
        $utmE = sqrt(($utmA * $utmA - $utmB * $utmB) / ($utmA * $utmA));
        $utmEPrime = sqrt(($utmA * $utmA - $utmB * $utmB) / ($utmB * $utmB));
        $utmK0 = 0.9996;

        $utmIsSouth = $utmZoneLetter !== '' ? ($utmZoneLetter < 'N') : true;
        $utmX = (float)$utmEasting - 500000.0;
        $utmY = $utmIsSouth ? (float)$utmNorthing - 10000000.0 : (float)$utmNorthing;

        $utmMarc = $utmY / $utmK0;
        $utmE2 = $utmE * $utmE;
        $utmE4 = $utmE2 * $utmE2;
        $utmE6 = $utmE4 * $utmE2;
        $utmE1 = (1 - sqrt(1 - $utmE2)) / (1 + sqrt(1 - $utmE2));

        $utmMu = $utmMarc / ($utmA * (1 - $utmE2 / 4 - 3 * $utmE4 / 64 - 5 * $utmE6 / 256));

        $utmPhi1 = $utmMu +
            (3 * $utmE1 / 2 - 27 * pow($utmE1, 3) / 32) * sin(2 * $utmMu) +
            (21 * $utmE1 * $utmE1 / 16 - 55 * pow($utmE1, 4) / 32) * sin(4 * $utmMu) +
            (151 * pow($utmE1, 3) / 96) * sin(6 * $utmMu) +
            (1097 * pow($utmE1, 4) / 512) * sin(8 * $utmMu);

        $utmSinPhi1 = sin($utmPhi1);
        $utmCosPhi1 = cos($utmPhi1);
        $utmTanPhi1 = tan($utmPhi1);

        $utmN1 = $utmA / sqrt(1 - $utmE2 * $utmSinPhi1 * $utmSinPhi1);
        $utmT1 = $utmTanPhi1 * $utmTanPhi1;
        $utmC1 = $utmEPrime * $utmEPrime * $utmCosPhi1 * $utmCosPhi1;
        $utmR1 = $utmA * (1 - $utmE2) / pow(1 - $utmE2 * $utmSinPhi1 * $utmSinPhi1, 1.5);
        $utmD = $utmX / ($utmN1 * $utmK0);

        $utmD2 = $utmD * $utmD;
        $utmD3 = $utmD2 * $utmD;
        $utmD4 = $utmD2 * $utmD2;
        $utmD5 = $utmD4 * $utmD;
        $utmD6 = $utmD3 * $utmD3;

        $utmLat = $utmPhi1 - ($utmN1 * $utmTanPhi1 / $utmR1) * (
            $utmD2 / 2 -
            (5 + 3 * $utmT1 + 10 * $utmC1 - 4 * $utmC1 * $utmC1 - 9 * $utmEPrime * $utmEPrime) * $utmD4 / 24 +
            (61 + 90 * $utmT1 + 298 * $utmC1 + 45 * $utmT1 * $utmT1 - 252 * $utmEPrime * $utmEPrime - 3 * $utmC1 * $utmC1) * $utmD6 / 720
        );

        $utmLon0 = ($utmZoneNum - 1) * 6 - 180 + 3;
        $utmLon = ($utmLon0 * M_PI / 180.0) + (
            $utmD -
            (1 + 2 * $utmT1 + $utmC1) * $utmD3 / 6 +
            (5 - 2 * $utmC1 + 28 * $utmT1 - 3 * $utmC1 * $utmC1 + 8 * $utmEPrime * $utmEPrime + 24 * $utmT1 * $utmT1) * $utmD5 / 120
        ) / $utmCosPhi1;

        return [
            'lat' => $utmLat * 180.0 / M_PI,
            'lng' => $utmLon * 180.0 / M_PI,
        ];
    }

    /**
     * Display the official technical report page for ventilation monitoring (Landscape Sheet).
     */
    public function showReport($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Technical Header Information: Razón Social de la empresa o proyecto para Instalación
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
        if (empty($module->installation_name) || $module->installation_name === $oldDefault || $module->installation_name === 'PACHABOL - PLANTA CENTRAL EMV') {
            $installationName = $razonSocial ?: ($module->installation_name ?: 'Instalación');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : ($module->created_at ? $module->created_at->format('d/m/Y') : date('d/m/Y'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : ($module->created_at ? $module->created_at->format('d/m/Y') : date('d/m/Y'));

        $monitoringType = $module->monitoring_type ?: 'Seguimiento';

        // Assigned Equipment
        $equipment = $module->equipment;
        $reportSettings = $module->photo_report_settings ?: [];

        $equipmentName = !empty($reportSettings['equipment_name']) ? $reportSettings['equipment_name'] : ($equipment ? ($equipment->name ?: 'Termo-Anemómetro') : ($module->calibration_equipment ?: 'Termo-Anemómetro'));
        $equipmentBrand = !empty($reportSettings['equipment_brand']) ? $reportSettings['equipment_brand'] : ($equipment ? ($equipment->brand ?: 'Testo') : 'Testo');
        $equipmentModel = !empty($reportSettings['equipment_model']) ? $reportSettings['equipment_model'] : ($equipment ? ($equipment->model ?: '410-1') : '410-1');
        $equipmentSerial = !empty($reportSettings['equipment_serial']) ? $reportSettings['equipment_serial'] : ($equipment ? ($equipment->serial_number ?: '61452984') : '61452984');

        // Staff
        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico de Campo';
        }

        // Measurements List
        $dbMeasurements = $module->ventilationMeasurements()->with('staff')->get();
        $measurementsList = [];

        if ($dbMeasurements->isNotEmpty()) {
            foreach ($dbMeasurements as $index => $item) {
                $velMs = (float) ($item->vel_aire_ms ?? 0);
                $areaLargo = (float) ($item->area_largo_m ?? 0);
                $areaAncho = (float) ($item->area_ancho_m ?? 0);
                $areaDiametro = (float) ($item->area_diametro_m ?? 0);
                $areaM2 = ($areaLargo * $areaAncho) + (3.1416 * $areaDiametro);
                if ($areaM2 <= 0 && !empty($item->area_ventilacion_m2)) {
                    $areaM2 = (float) $item->area_ventilacion_m2;
                }

                $volLargo = (float) ($item->vol_largo_m ?? 0);
                $volAncho = (float) ($item->vol_ancho_m ?? 0);
                $volAlto = (float) ($item->vol_alto_m ?? 0);
                $volM3 = ($volLargo * $volAncho * $volAlto);
                if ($volM3 <= 0 && !empty($item->volumen_m3)) {
                    $volM3 = (float) $item->volumen_m3;
                }

                $caudal = $velMs * $areaM2;
                $renovH = ($volM3 > 0) ? (3600.0 * ($caudal / $volM3)) : (float) ($item->renovaciones_h ?? 0);

                $norma = self::getNormaForTipoLocal($item->tipo_local);
                $minNorma = (float) ($item->renovaciones_min ?? $norma['min']);
                $maxNorma = (float) ($item->renovaciones_max ?? $norma['max']);
                $intervaloStr = $item->renovaciones_intervalo ?: $norma['intervalo'];

                $isCumple = !empty($item->cumple) ? (strtoupper(trim($item->cumple)) === 'SI' || strtoupper(trim($item->cumple)) === 'CUMPLE') : (($minNorma > 0) ? ($renovH >= $minNorma) : true);
                $cumpleStr = $isCumple ? 'SI' : 'NO';

                $measurementsList[] = [
                    'id' => $item->id,
                    'num' => $item->point_number ?: ($index + 1),
                    'local_trabajo' => $item->local_trabajo ?: 'Área Operativa',
                    'tipo_ventilacion' => $item->tipo_ventilacion ?: 'Natural',
                    'elemento_ventilacion' => $item->elemento_ventilacion ?: 'Ventana',
                    'temperatura_seca_c' => (float) ($item->temperatura_seca_c ?? 20.0),
                    'vel_aire_ms' => $velMs,
                    'area_ventilacion_m2' => $areaM2,
                    'caudal_m3h' => $caudal,
                    'volumen_m3' => $volM3,
                    'renovaciones_h' => $renovH,
                    'renovaciones_intervalo' => $intervaloStr,
                    'is_compliant' => $isCumple,
                    'cumple' => $cumpleStr,
                    'compliance_text' => $cumpleStr,
                    'observations' => ($item->observations && $item->observations !== 'Sin observaciones') ? $item->observations : '',
                ];
            }
        } else {
            // Mock sample rows matching the official ventilation table
            $measurementsList = [
                [
                    'id' => 1,
                    'num' => 1,
                    'local_trabajo' => 'Oficina Técnica y Administrativa',
                    'tipo_ventilacion' => 'Natural',
                    'elemento_ventilacion' => 'Ventanas exteriores',
                    'temperatura_seca_c' => 21.5,
                    'vel_aire_ms' => 0.42,
                    'area_ventilacion_m2' => 2.40,
                    'caudal_m3h' => 1.01,
                    'volumen_m3' => 180.00,
                    'renovaciones_h' => 20.16,
                    'renovaciones_intervalo' => '4 - 8',
                    'is_compliant' => true,
                    'cumple' => 'SI',
                    'compliance_text' => 'SI',
                    'observations' => 'Ventilación natural adecuada con flujo cruzado',
                ],
                [
                    'id' => 2,
                    'num' => 2,
                    'local_trabajo' => 'Sala de Control y Operaciones',
                    'tipo_ventilacion' => 'Mecánica',
                    'elemento_ventilacion' => 'Extractor helicoidal',
                    'temperatura_seca_c' => 22.0,
                    'vel_aire_ms' => 1.85,
                    'area_ventilacion_m2' => 0.38,
                    'caudal_m3h' => 0.70,
                    'volumen_m3' => 140.00,
                    'renovaciones_h' => 18.08,
                    'renovaciones_intervalo' => '4 - 10',
                    'is_compliant' => true,
                    'cumple' => 'SI',
                    'compliance_text' => 'SI',
                    'observations' => 'Sistema de extracción forzada en funcionamiento óptimo',
                ],
                [
                    'id' => 3,
                    'num' => 3,
                    'local_trabajo' => 'Taller de Mantenimiento Mecánico',
                    'tipo_ventilacion' => 'Mixta',
                    'elemento_ventilacion' => 'Portón y extractores de techo',
                    'temperatura_seca_c' => 19.8,
                    'vel_aire_ms' => 0.65,
                    'area_ventilacion_m2' => 6.50,
                    'caudal_m3h' => 4.23,
                    'volumen_m3' => 850.00,
                    'renovaciones_h' => 17.89,
                    'renovaciones_intervalo' => '3 - 8',
                    'is_compliant' => true,
                    'cumple' => 'SI',
                    'compliance_text' => 'SI',
                    'observations' => 'Condiciones ambientales conformes a la normativa',
                ],
                [
                    'id' => 4,
                    'num' => 4,
                    'local_trabajo' => 'Almacén de Insumos y Repuestos',
                    'tipo_ventilacion' => 'Natural',
                    'elemento_ventilacion' => 'Rejillas de ventilación perimetrales',
                    'temperatura_seca_c' => 18.5,
                    'vel_aire_ms' => 0.28,
                    'area_ventilacion_m2' => 1.80,
                    'caudal_m3h' => 0.50,
                    'volumen_m3' => 320.00,
                    'renovaciones_h' => 5.67,
                    'renovaciones_intervalo' => '6 - 10',
                    'is_compliant' => false,
                    'cumple' => 'NO',
                    'compliance_text' => 'NO',
                    'observations' => 'Se sugiere instalar sistema de inyección/extracción mecánica',
                ],
            ];
        }

        return view('measurements.ventilaciones.report', compact(
            'module',
            'installationName',
            'startDateRaw',
            'endDateRaw',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'equipmentName',
            'equipmentBrand',
            'equipmentModel',
            'equipmentSerial',
            'registeredByHeader',
            'measurementsList',
            'reportSettings',
            'userName',
            'userRole'
        ));
    }

    /**
     * Save/autosave report header settings or overrides for ventilation.
     */
    public function saveReportData(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        if ($request->has('installation_name')) {
            $module->installation_name = $request->input('installation_name');
        }
        if ($request->has('start_date')) {
            $module->start_date = $request->input('start_date') ?: null;
        }
        if ($request->has('end_date')) {
            $module->end_date = $request->input('end_date') ?: null;
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $request->input('monitoring_type');
        }

        // Custom equipment overrides in photo_report_settings if provided
        $settings = $module->photo_report_settings ?: [];
        if ($request->has('equipment_name')) {
            $settings['equipment_name'] = $request->input('equipment_name');
        }
        if ($request->has('equipment_brand')) {
            $settings['equipment_brand'] = $request->input('equipment_brand');
        }
        if ($request->has('equipment_model')) {
            $settings['equipment_model'] = $request->input('equipment_model');
        }
        if ($request->has('equipment_serial')) {
            $settings['equipment_serial'] = $request->input('equipment_serial');
        }
        $module->photo_report_settings = $settings;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Informe de ventilación guardado correctamente.',
        ]);
    }
}
