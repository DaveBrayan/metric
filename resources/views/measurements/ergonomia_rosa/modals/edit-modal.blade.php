<!-- ==========================================================================
     MODAL 2: DETALLE / EDICIÓN DE EVALUACIÓN ROSA (MODO CONSULTA CON BOTÓN EDITAR)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true"
    aria-labelledby="editMeasModalTitle">
    <div class="modal-dialog-illumination modal-dialog-lg">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <h2 id="editMeasModalTitle">Detalle de Evaluación ROSA</h2>
                <span id="modalModeStatusBadge" class="modal-badge-view">Solo Lectura</span>
                <!-- Personal Registrado -->
                <div style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9999px; padding: 3px 10px; font-size: 11.5px; font-weight: 700; color: #334155;"
                    title="Personal Registrador">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span id="edit_modal_registered_by">{{ $registeredByHeader }}</span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <!-- Botón para activar/desactivar la edición -->
                <button type="button" class="btn-modal-edit-action" id="btnToggleEditMode"
                    onclick="toggleModalEditMode()">
                    <span id="btnToggleEditModeIcon" style="display: inline-flex; align-items: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                            <path d="m15 5 4 4" />
                        </svg>
                    </span>
                    <span id="btnToggleEditModeText">Editar</span>
                </button>
                <button type="button" class="btn-close-modal" onclick="closeEditMeasurementModal()"
                    aria-label="Cerrar">✕</button>
            </div>
        </div>

        <form id="editMeasurementForm" action="" method="POST" enctype="multipart/form-data" class="modal-view-mode">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_measurement_id" name="id">

            <div class="modal-body-custom">
                <div class="modal-three-cols-grid">

                    <!-- COLUMNA 1: Datos del Puesto de Trabajo & Estudio -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>1. Datos del Puesto</span>
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                    id="edit_pt_num_disp">01</strong></span>
                        </div>

                        <input type="hidden" name="point_number" id="edit_point_number">

                        <!-- Fecha y Hora -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_measurement_date">Fecha de Evaluación <span
                                        class="req">*</span></label>
                                <input type="date" name="measurement_date" id="edit_measurement_date"
                                    class="custom-form-input" required disabled>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_measurement_time">Hora</label>
                                <input type="time" name="measurement_time" id="edit_measurement_time"
                                    class="custom-form-input" step="1" disabled>
                            </div>
                        </div>

                        <!-- Técnico de Campo / Evaluador a Cargo -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_staff_id">
                                <span>Ergónomo / Evaluador a Cargo <span class="req">*</span></span>
                            </label>
                            <select name="staff_id" id="edit_staff_id" class="custom-form-select" disabled
                                onchange="if(document.getElementById('edit_modal_registered_by')) document.getElementById('edit_modal_registered_by').textContent = this.options[this.selectedIndex].text.split('—')[0].trim();">
                                @if(isset($staffList) && count($staffList) > 0)
                                    @foreach($staffList as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} @if(!empty($staff->position)) — {{ $staff->position }} @endif</option>
                                    @endforeach
                                @else
                                    <option value="">{{ $registeredByHeader }}</option>
                                @endif
                            </select>
                        </div>

                        <!-- Área y Puesto de Trabajo -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_area_sector">Área / Sector <span
                                        class="req">*</span></label>
                                <input type="text" name="area_sector" id="edit_area_sector" class="custom-form-input"
                                    required disabled placeholder="Ej: Finanzas / Administración">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_puesto_trabajo">Puesto de Trabajo <span
                                        class="req">*</span></label>
                                <input type="text" name="puesto_trabajo" id="edit_puesto_trabajo"
                                    class="custom-form-input" required disabled placeholder="Ej: Analista de Sistemas">
                            </div>
                        </div>

                        <!-- Factor de Riesgo Disergonómico -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_factor_riesgo">
                                <span>Factor de Riesgo Disergonómico <span class="req">*</span></span>
                            </label>
                            <select name="factor_riesgo" id="edit_factor_riesgo" class="custom-form-select" required disabled>
                                <option value="Trabajo prolongado frente a PVD / Pantallas">Trabajo prolongado frente a PVD / Pantallas</option>
                                <option value="Uso intensivo de pantalla / VDT">Uso intensivo de pantalla / VDT</option>
                                <option value="Posturas frente a PVD">Posturas frente a PVD</option>
                                <option value="Sedestación prolongada en oficina">Sedestación prolongada en oficina</option>
                                <option value="Uso intensivo de ratón y teclado">Uso intensivo de ratón y teclado</option>
                                <option value="Posturas forzadas de cuello y espalda">Posturas forzadas de cuello y espalda</option>
                                <option value="Posturas forzadas">Posturas forzadas</option>
                                <option value="Atención telefónica simultánea a digitación">Atención telefónica simultánea a digitación</option>
                                <option value="Iluminación / reflejos en pantallas">Iluminación / reflejos en pantallas</option>
                                <option value="Movimientos repetitivos">Movimientos repetitivos</option>
                            </select>
                        </div>

                        <!-- N° Trabajadores & Exposición -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_num_trabajadores">N° Trabajadores</label>
                                <input type="number" name="num_trabajadores" id="edit_num_trabajadores"
                                    class="custom-form-input" min="1" disabled value="1">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_tiempo_exposicion">Exposición (h/día)</label>
                                <select name="tiempo_exposicion_horas" id="edit_tiempo_exposicion" class="custom-form-select" disabled>
                                    <option value="2">2 horas</option>
                                    <option value="4">4 horas</option>
                                    <option value="6">6 horas</option>
                                    <option value="8" selected>8 horas</option>
                                </select>
                            </div>
                        </div>

                        <!-- Procedimiento & Capacitación -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_procedimiento_escrito">Procedimiento Escrito</label>
                                <select name="procedimiento_escrito" id="edit_procedimiento_escrito" class="custom-form-select" disabled>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_capacitacion">Capacitación Previa</label>
                                <select name="capacitacion" id="edit_capacitacion" class="custom-form-select" disabled>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sintomatología / Molestia -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_ubicacion_sintoma">Sintomatología / Molestia</label>
                            <select name="ubicacion_sintoma" id="edit_ubicacion_sintoma" class="custom-form-select" disabled>
                                <option value="Ninguna">Ninguna</option>
                                <option value="Cuello">Cuello / Cervical</option>
                                <option value="Hombros">Hombros / Trapecio</option>
                                <option value="Zona dorsal">Espalda dorsal</option>
                                <option value="Zona lumbar">Espalda lumbar</option>
                                <option value="Muñeca / Mano derecha">Muñeca / Mano derecha</option>
                                <option value="Muñeca / Mano izquierda">Muñeca / Mano izquierda</option>
                                <option value="Fatiga visual">Fatiga visual / Ojos</option>
                            </select>
                        </div>
                    </div>

                    <!-- COLUMNA 2: Archivo Fotográfico (Slide) & Parámetros ROSA -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>2. Archivo Fotográfico & Parámetros</span>
                            <span style="font-size: 11px; color: #64748b;" id="edit_photo_count_indicator">0 fotos</span>
                        </div>

                        <!-- CARRUSEL DE FOTOS TIPO SLIDE CONTINUO -->
                        <div class="modal-photo-slider-wrapper">
                            <div class="modal-slider-viewport" id="edit_slider_viewport">
                                <span id="edit_slider_counter" class="slider-counter-badge" style="display: none;">1 / 1</span>

                                <button type="button" class="slider-nav-btn prev" id="edit_slider_btn_prev"
                                    onclick="slidePhotoNav('edit', -1)" style="display: none;"
                                    aria-label="Anterior">❮</button>
                                <button type="button" class="slider-nav-btn next" id="edit_slider_btn_next"
                                    onclick="slidePhotoNav('edit', 1)" style="display: none;"
                                    aria-label="Siguiente">❯</button>

                                <img id="edit_slider_img" class="modal-slider-main-img" src="" alt="Foto evaluación"
                                    style="display: none;">

                                <div id="edit_slider_placeholder" class="modal-slider-placeholder">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        style="margin-bottom: 10px;">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                    <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin Fotografía</strong>
                                    <span style="font-size: 11.5px; color: #94a3b8;">No se registraron imágenes para este puesto</span>
                                </div>
                            </div>

                            <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                            <input type="file" name="images[]" id="edit_images_input" multiple accept="image/*"
                                style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                            <input type="hidden" name="remaining_images" id="edit_remaining_images">

                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                                <button type="button" class="btn-add-photos-trigger" id="edit_btn_add_photos"
                                    onclick="document.getElementById('edit_images_input').click()"
                                    style="display: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <span>+ Subir / Agregar Nuevas Fotos</span>
                                </button>
                                <button type="button" class="btn-delete-active-photo" id="edit_btn_delete_photo"
                                    onclick="deleteActivePhoto('edit')" style="display: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                    </svg>
                                    <span>Eliminar Fotografía Actual</span>
                                </button>
                            </div>
                        </div>

                        <!-- PARÁMETROS BIOMECÁNICOS ROSA -->
                        <div class="ergo-params-scroll-container">
                            <!-- TABLA A: SILLA -->
                            <div class="reba-section-box" style="margin-bottom: 12px;">
                                <div class="reba-section-header">
                                    <span class="reba-section-badge">A</span>
                                    <h3>Tabla A — Silla de Oficina</h3>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">1. Altura del Asiento</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="altura_asiento_base" value="1" disabled onchange="updateEditLiveScore()"> Rodillas a 90°, pies apoyados</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="altura_asiento_base" value="2" disabled onchange="updateEditLiveScore()"> Muy bajo (&lt; 90°) o muy alto (pies al aire)</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="altura_asiento_mod" value="1" id="edit_asiento_espacio" disabled onchange="updateEditLiveScore()"> Espacio piernas insuficiente / no regulable</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">2. Profundidad del Asiento</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="profundidad_base" value="1" disabled onchange="updateEditLiveScore()"> Espacio 8 cm entre borde y hueco poplíteo</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="profundidad_base" value="2" disabled onchange="updateEditLiveScore()"> Muy largo (&lt; 8 cm) o muy corto</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="profundidad_mod" value="1" id="edit_profundidad_no_regulable" disabled onchange="updateEditLiveScore()"> Profundidad no regulable</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">3. Reposabrazos</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="reposabrazos_base" value="1" disabled onchange="updateEditLiveScore()"> Codos a 90°, hombros relajados</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="reposabrazos_base" value="2" disabled onchange="updateEditLiveScore()"> Muy altos / hombros encogidos o muy bajos</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="reposabrazos_mod" value="1" id="edit_reposabrazos_duros" disabled onchange="updateEditLiveScore()"> Demasiado separados, duros o no regulables</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">4. Respaldo</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="respaldo_base" value="1" disabled onchange="updateEditLiveScore()"> Soporte lumbar adecuado (95°-110°)</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="respaldo_base" value="2" disabled onchange="updateEditLiveScore()"> Sin apoyo lumbar o reclinación &gt; 110°</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="respaldo_mod" value="1" id="edit_respaldo_no_regulable" disabled onchange="updateEditLiveScore()"> Sin respaldo o no regulable</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">5. Tiempo de Uso Diario Silla</label>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                                        <label class="reba-option-card">
                                            <div><input type="radio" name="silla_tiempo_uso" value="-1" disabled onchange="updateEditLiveScore()"> &lt; 1 h/día</div>
                                            <span class="reba-option-score-tag">-1</span>
                                        </label>
                                        <label class="reba-option-card">
                                            <div><input type="radio" name="silla_tiempo_uso" value="0" disabled onchange="updateEditLiveScore()"> 1 - 4 h/día</div>
                                            <span class="reba-option-score-tag">+0</span>
                                        </label>
                                        <label class="reba-option-card">
                                            <div><input type="radio" name="silla_tiempo_uso" value="1" disabled onchange="updateEditLiveScore()"> &gt; 4 h/día</div>
                                            <span class="reba-option-score-tag">+1</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- TABLA B: PANTALLA Y TELÉFONO -->
                            <div class="reba-section-box" style="margin-bottom: 12px;">
                                <div class="reba-section-header">
                                    <span class="reba-section-badge">B</span>
                                    <h3>Tabla B — Pantalla & Teléfono</h3>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">1. Pantalla (PVD)</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="pantalla_base" value="1" disabled onchange="updateEditLiveScore()"> A la altura de los ojos (40-75 cm)</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="pantalla_base" value="2" disabled onchange="updateEditLiveScore()"> Muy baja / muy alta (&gt; 30° flexión/extensión)</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="pantalla_base" value="3" disabled onchange="updateEditLiveScore()"> Ángulo lateral &gt; 45°</div>
                                        <span class="reba-option-score-tag">+3</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="pantalla_mod" value="1" id="edit_pantalla_torsion" disabled onchange="updateEditLiveScore()"> Torsión de cuello lateral o reflejos</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="pantalla_tiempo" value="1" id="edit_pantalla_tiempo" disabled onchange="updateEditLiveScore()"> Uso de pantalla &gt; 4 h/día</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">2. Teléfono</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="telefono_base" value="1" disabled onchange="updateEditLiveScore()"> Manos libres o uso con cuello recto</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="telefono_base" value="2" disabled onchange="updateEditLiveScore()"> Alcance forzado (&gt; 30 cm)</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="telefono_mod" value="2" id="edit_telefono_cuello" disabled onchange="updateEditLiveScore()"> Sujeción entre cuello y hombro</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="telefono_tiempo" value="1" id="edit_telefono_tiempo" disabled onchange="updateEditLiveScore()"> Uso telefónico &gt; 1 h continuo ó &gt; 4 h/día</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>
                            </div>

                            <!-- TABLA C: RATÓN Y TECLADO -->
                            <div class="reba-section-box">
                                <div class="reba-section-header">
                                    <span class="reba-section-badge">C</span>
                                    <h3>Tabla C — Ratón & Teclado</h3>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">1. Ratón / Mouse</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="raton_base" value="1" disabled onchange="updateEditLiveScore()"> Alineado con hombro, muñeca recta</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="raton_base" value="2" disabled onchange="updateEditLiveScore()"> Separado del cuerpo o alcance forzado</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="raton_mod" value="1" id="edit_raton_distinto_plano" disabled onchange="updateEditLiveScore()"> Distinto plano o apoyo rígido</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="raton_tiempo" value="1" id="edit_raton_tiempo" disabled onchange="updateEditLiveScore()"> Uso de ratón &gt; 4 h/día</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">2. Teclado</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="teclado_base" value="1" disabled onchange="updateEditLiveScore()"> Muñecas rectas, hombros relajados</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="teclado_base" value="2" disabled onchange="updateEditLiveScore()"> Muñecas en extensión &gt; 15° o muy alto</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="teclado_mod" value="1" id="edit_teclado_desviacion" disabled onchange="updateEditLiveScore()"> Desviación lateral de muñecas al escribir</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="teclado_tiempo" value="1" id="edit_teclado_tiempo" disabled onchange="updateEditLiveScore()"> Digitación continua &gt; 4 h/día</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">3. Actividad Muscular</label>
                                    <label class="reba-option-card" style="background: #f8fafc;">
                                        <div><input type="checkbox" name="actividad_estatica" value="1" id="edit_actividad_estatica" disabled onchange="updateEditLiveScore()"> Posturas estáticas mantenidas (> 30 min)</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f8fafc;">
                                        <div><input type="checkbox" name="actividad_repetitiva" value="1" id="edit_actividad_repetitiva" disabled onchange="updateEditLiveScore()"> Movimientos repetitivos continuos</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 3: Ubicación Geográfica, Mini Mapa & Resultados ROSA -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>3. Ubicación & Resultados</span>
                            <button type="button" id="edit_btn_gps"
                                onclick="getCurrentGpsPosition('edit_latitude', 'edit_longitude', 'edit')"
                                style="display: none; background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; align-items: center; gap: 3px;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.3">
                                    <circle cx="12" cy="12" r="10" />
                                    <circle cx="12" cy="12" r="3" />
                                    <line x1="12" y1="2" x2="12" y2="5" />
                                    <line x1="12" y1="19" x2="12" y2="22" />
                                    <line x1="2" y1="12" x2="5" y2="12" />
                                    <line x1="19" y1="12" x2="22" y2="12" />
                                </svg>
                                Mi GPS
                            </button>
                        </div>

                        <!-- Coordenadas UTM -->
                        <div class="utm-coords-grid">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_utm_easting">Este (E)</label>
                                <input type="number" step="any" name="utm_easting" id="edit_utm_easting" class="custom-form-input"
                                    placeholder="591320.10" disabled oninput="syncUtmToMap('edit')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                <input type="number" step="any" name="utm_northing" id="edit_utm_northing" class="custom-form-input"
                                    placeholder="8175310.40" disabled oninput="syncUtmToMap('edit')">
                            </div>
                            <div class="form-field-group" style="max-width: 85px;">
                                <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                <input type="text" name="utm_zone" id="edit_utm_zone" class="custom-form-input" placeholder="19K"
                                    value="19K" disabled oninput="syncUtmToMap('edit')">
                            </div>
                        </div>

                        <div class="utm-preview-pill">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                <path d="M2 12h20" />
                            </svg>
                            <span id="edit_utm_display">E: —, N: —, Z: 19K</span>
                        </div>

                        <input type="hidden" name="location" id="edit_location">
                        <input type="hidden" name="latitude" id="edit_latitude">
                        <input type="hidden" name="longitude" id="edit_longitude">

                        <!-- Mini Mapa interactivo: Dónde está ubicado el puesto -->
                        <div>
                            <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa Satelital</label>
                            <div id="edit_modal_map" class="modal-minimap-container"></div>
                        </div>

                        <!-- PANEL LIVE SCORE ROSA -->
                        <div class="reba-live-score-box" style="margin-top: 8px;">
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; font-weight: 800; margin-bottom: 10px;">
                                Cálculo en Tiempo Real (ROSA)
                            </div>

                            <div class="reba-live-score-grid" style="grid-template-columns: repeat(4, 1fr);">
                                <div class="reba-live-score-item">
                                    <div class="label">Silla A</div>
                                    <div class="val" id="edit_live_score_a">1</div>
                                </div>
                                <div class="reba-live-score-item">
                                    <div class="label">Monitor B</div>
                                    <div class="val" id="edit_live_score_b">1</div>
                                </div>
                                <div class="reba-live-score-item">
                                    <div class="label">Ratón C</div>
                                    <div class="val" id="edit_live_score_c">1</div>
                                </div>
                                <div class="reba-live-score-item">
                                    <div class="label">Perif. D</div>
                                    <div class="val" id="edit_live_score_d">1</div>
                                </div>
                            </div>

                            <div id="edit_live_final_box" class="reba-live-final-display risk-inapreciable" style="margin-top: 10px;">
                                <div class="title">Puntuación Final ROSA</div>
                                <div class="score" id="edit_live_score_final">1</div>
                                <div class="action" id="edit_live_risk_level">Riesgo Inapreciable</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;" id="edit_live_action_level">
                                    Nivel 1: Postura óptima, no se requiere acción
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones: Textarea directamente debajo del mapa y score -->
                        <div class="form-field-group" style="margin-top: 8px;">
                            <label class="form-field-label" for="edit_observaciones">Observaciones y Recomendaciones</label>
                            <textarea name="observaciones" id="edit_observaciones" class="custom-form-textarea" rows="3"
                                disabled
                                placeholder="Observaciones técnicas, fuentes de fatiga postural, ergonomía del mobiliario..."></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeEditMeasurementModal()">Cerrar</button>
                <button type="submit" class="btn-primary-hero-action" id="edit_modal_submit_btn"
                    style="display: none;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
