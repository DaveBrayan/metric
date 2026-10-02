<!-- ==========================================================================
     MODAL 2: DETALLE / EDICIÓN DE EVALUACIÓN REBA (MODO CONSULTA CON BOTÓN EDITAR)
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
                <h2 id="editMeasModalTitle">Detalle de Evaluación REBA</h2>
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

                    <!-- COLUMNA 1: Datos del Puesto de Trabajo & Tarea -->
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

                        <!-- 1. Área y Sector en estudio -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_area_sector">1. Área y Sector en estudio <span
                                    class="req">*</span></label>
                            <input type="text" name="area_sector" id="edit_area_sector" class="custom-form-input"
                                required disabled placeholder="Ej: Planta Procesadora, Almacén Central, Administración">
                        </div>

                        <!-- 2. Factor de riesgo disergonómico -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_factor_riesgo">
                                <span>2. Factor de riesgo disergonómico <span class="req">*</span></span>
                            </label>
                            <select name="factor_riesgo" id="edit_factor_riesgo" class="custom-form-select" required disabled>
                                <option value="Levantamiento y descenso manual de carga">Levantamiento y descenso manual de carga</option>
                                <option value="Empuje y arrastre manual">Empuje y arrastre manual</option>
                                <option value="Transporte manual">Transporte manual</option>
                                <option value="Bipedestación">Bipedestación</option>
                                <option value="Movimientos repetitivos">Movimientos repetitivos</option>
                                <option value="Posturas forzadas">Posturas forzadas</option>
                                <option value="Vibraciones">Vibraciones</option>
                                <option value="Confort térmico">Confort térmico</option>
                                <option value="Estrés de contacto">Estrés de contacto</option>
                            </select>
                        </div>

                        <!-- 3. N° de trabajadores & 5. Edad -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_num_trabajadores">3. N° de trabajadores</label>
                                <input type="number" name="num_trabajadores" id="edit_num_trabajadores"
                                    class="custom-form-input" min="1" disabled value="1" oninput="updateWorkerNamesInputs('edit')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_edad">5. Edad</label>
                                <input type="text" name="edad" id="edit_edad" class="custom-form-input" disabled placeholder="Ej: 38">
                            </div>
                        </div>

                        <!-- 4. Puesto de trabajo -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_puesto_trabajo">4. Puesto de Trabajo <span
                                    class="req">*</span></label>
                            <input type="text" name="puesto_trabajo" id="edit_puesto_trabajo"
                                class="custom-form-input" required disabled placeholder="Ej: Operador de Envasado, Mecánico de Mantenimiento...">
                        </div>

                        <!-- 6. Procedimiento escrito & 7. Capacitación -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_procedimiento_escrito">6. Procedimiento escrito</label>
                                <select name="procedimiento_escrito" id="edit_procedimiento_escrito" class="custom-form-select" disabled>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_capacitacion">7. Capacitación</label>
                                <select name="capacitacion" id="edit_capacitacion" class="custom-form-select" disabled>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        <!-- 8. Nombres de los trabajadores -->
                        <div class="form-field-group">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                                <label class="form-field-label" style="margin-bottom: 0;">8. Nombre(s) del trabajador/es</label>
                                <span id="edit_workers_count_badge" style="font-size: 10.5px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 1px 6px; border-radius: 4px;">1 trabajador</span>
                            </div>
                            <div id="edit_nombres_trabajadores_container" style="display: flex; flex-direction: column; gap: 6px; margin-top: 4px;">
                                <div class="worker-name-row" style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 11px; font-weight: 800; color: #fff; background: #0284c7; padding: 4px 7px; border-radius: 6px; flex-shrink: 0;">T1</span>
                                    <input type="text" name="nombres_trabajadores[]" class="custom-form-input worker-name-input" disabled placeholder="Nombre y apellido del trabajador 1">
                                </div>
                            </div>
                        </div>

                        <!-- 9. Fuerza de agarre -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_fuerza_agarre">9. Fuerza de agarre</label>
                            <input type="text" name="fuerza_agarre" id="edit_fuerza_agarre" class="custom-form-input" disabled placeholder="Ej: 45, Moderada, Intensa">
                        </div>

                        <!-- 10. Carga Peso (Kg) & 11. Distancia (m) -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_carga_peso_kg">10. Carga / Peso (kg)</label>
                                <input type="number" step="0.1" name="carga_peso_kg" id="edit_carga_peso_kg" class="custom-form-input" disabled placeholder="Ej: 20">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_distancia_m">11. Distancia (m)</label>
                                <input type="number" step="0.1" name="distancia_m" id="edit_distancia_m" class="custom-form-input" disabled placeholder="Ej: 5.0">
                            </div>
                        </div>

                        <!-- 12. Ayuda Mecánica & 13. Descripción Carga -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_ayuda_mecanica">12. Ayuda Mecánica</label>
                                <input type="text" name="ayuda_mecanica" id="edit_ayuda_mecanica" class="custom-form-input" disabled placeholder="Ej: Transpalet, Carretilla, Ninguna">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_descripcion_carga">13. Descripción Carga</label>
                                <input type="text" name="descripcion_carga" id="edit_descripcion_carga" class="custom-form-input" disabled placeholder="Ej: Cajas corrugadas, Bolsas">
                            </div>
                        </div>

                        <!-- 14. Manifestación temprana & 15. Ubicación del síntoma -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_manifestacion_temprana">14. Manifestación temprana</label>
                                <select name="manifestacion_temprana" id="edit_manifestacion_temprana" class="custom-form-select" disabled>
                                    <option value="No">No</option>
                                    <option value="Si">Sí</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_ubicacion_sintoma">15. Ubicación del síntoma</label>
                                <select name="ubicacion_sintoma" id="edit_ubicacion_sintoma" class="custom-form-select" disabled>
                                    <option value="Ninguna">Ninguna</option>
                                    <option value="Cuello">Cuello</option>
                                    <option value="Hombros">Hombros</option>
                                    <option value="Brazos">Brazos</option>
                                    <option value="Codos">Codos</option>
                                    <option value="Antebrazos">Antebrazos</option>
                                    <option value="Muñecas">Muñecas</option>
                                    <option value="Manos">Manos</option>
                                    <option value="Dedos">Dedos</option>
                                    <option value="Espalda alta">Espalda alta</option>
                                    <option value="Espalda baja">Espalda baja</option>
                                    <option value="Cadera">Cadera</option>
                                    <option value="Muslos">Muslos</option>
                                    <option value="Rodillas">Rodillas</option>
                                    <option value="Piernas">Piernas</option>
                                    <option value="Tobillos">Tobillos</option>
                                    <option value="Pies">Pies</option>
                                </select>
                            </div>
                        </div>

                        <!-- 16. Tiempo de exposición (h) -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_tiempo_exposicion">16. Tiempo de exposición (h)</label>
                            <select name="tiempo_exposicion_horas" id="edit_tiempo_exposicion" class="custom-form-select" disabled>
                                <option value="2">2 horas</option>
                                <option value="4">4 horas</option>
                                <option value="6">6 horas</option>
                                <option value="8">8 horas</option>
                            </select>
                        </div>

                        <!-- 17. Tareas Analizadas -->
                        <div class="form-field-group">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                                <label class="form-field-label" style="margin-bottom: 0;">17. Tareas Analizadas</label>
                                <button type="button" class="btn-add-tarea-action" id="edit_btn_add_tarea" onclick="addTareaInput('edit')" style="font-size: 11px; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 2px 8px; cursor: pointer; display: none; align-items: center; gap: 4px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Tarea</span>
                                </button>
                            </div>
                            <div id="edit_tareas_container" style="display: flex; flex-direction: column; gap: 6px; margin-top: 4px;">
                                <div class="tarea-row" style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 11px; font-weight: 800; color: #475569; background: #f1f5f9; padding: 4px 7px; border-radius: 6px; flex-shrink: 0;">1</span>
                                    <input type="text" name="tareas[]" class="custom-form-input tarea-input" disabled placeholder="Tarea 1: Ej. Levantamiento manual de cajas...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: Archivo Fotográfico (Slide) & Parámetros REBA -->
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
                                    <span style="font-size: 11.5px; color: #94a3b8;">No se registraron imágenes para esta evaluación</span>
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

                        <!-- PARÁMETROS BIOMECÁNICOS REBA -->
                        <div class="ergo-params-scroll-container">
                            <!-- GRUPO A: TRONCO, CUELLO, PIERNAS & CARGA -->
                            <div class="reba-section-box" style="margin-bottom: 12px;">
                                <div class="reba-section-header">
                                    <span class="reba-section-badge">A</span>
                                    <h3>Grupo A (Tronco, Cuello, Piernas & Carga)</h3>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">1. Tronco</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="tronco_base" value="1" disabled onchange="updateEditLiveScore()"> Erguido / 0°</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="tronco_base" value="2" disabled onchange="updateEditLiveScore()"> 0°-20° flexión ó 0°-20° extensión</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="tronco_base" value="3" disabled onchange="updateEditLiveScore()"> 20°-60° flexión ó &gt; 20° extensión</div>
                                        <span class="reba-option-score-tag">+3</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="tronco_base" value="4" disabled onchange="updateEditLiveScore()"> &gt; 60° flexión severa</div>
                                        <span class="reba-option-score-tag">+4</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="tronco_mod" value="1" id="edit_tronco_mod" disabled onchange="updateEditLiveScore()"> Torsión o inclinación lateral</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">2. Cuello</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="cuello_base" value="1" disabled onchange="updateEditLiveScore()"> 0°-20° flexión</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="cuello_base" value="2" disabled onchange="updateEditLiveScore()"> &gt; 20° flexión o extensión</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="cuello_mod" value="1" id="edit_cuello_mod" disabled onchange="updateEditLiveScore()"> Torsión o inclinación lateral</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">3. Piernas</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="piernas_base" value="1" disabled onchange="updateEditLiveScore()"> Soporte bilateral, andando o sentado</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="piernas_base" value="2" disabled onchange="updateEditLiveScore()"> Soporte unilateral o postura inestable</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff; margin-top: 4px;">
                                        <div><input type="checkbox" name="piernas_flexion_30_60" value="1" id="edit_piernas_flexion_30_60" disabled onchange="updateEditLiveScore()"> Flexión de rodillas entre 30° y 60°</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff; margin-top: 4px;">
                                        <div><input type="checkbox" name="piernas_flexion_mas_60" value="2" id="edit_piernas_flexion_mas_60" disabled onchange="updateEditLiveScore()"> Flexión de rodillas mayor a 60° (salvo postura sedente)</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">4. Carga / Fuerza</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="carga_fuerza" value="0" disabled onchange="updateEditLiveScore()"> Menor a 5 kg</div>
                                        <span class="reba-option-score-tag">+0</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="carga_fuerza" value="1" disabled onchange="updateEditLiveScore()"> 5 a 10 kg</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="carga_fuerza" value="2" disabled onchange="updateEditLiveScore()"> Mayor a 10 kg</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                </div>
                            </div>

                            <!-- GRUPO B: BRAZOS, ANTEBRAZOS, MUÑECAS & AGARRE -->
                            <div class="reba-section-box" style="margin-bottom: 12px;">
                                <div class="reba-section-header">
                                    <span class="reba-section-badge">B</span>
                                    <h3>Grupo B (Brazos, Antebrazos, Muñecas & Agarre)</h3>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">1. Brazo / Hombro</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="brazo_base" value="1" disabled onchange="updateEditLiveScore()"> 0°-20° flexión/extensión</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="brazo_base" value="2" disabled onchange="updateEditLiveScore()"> &gt; 20° extensión ó 20°-45° flexión</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="brazo_base" value="3" disabled onchange="updateEditLiveScore()"> 45°-90° flexión</div>
                                        <span class="reba-option-score-tag">+3</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="brazo_base" value="4" disabled onchange="updateEditLiveScore()"> &gt; 90° flexión severa</div>
                                        <span class="reba-option-score-tag">+4</span>
                                    </label>
                                    <div style="display: grid; grid-template-columns: 1fr; gap: 4px; margin-top: 4px;">
                                        <label class="reba-option-card" style="background: #f0f9ff;">
                                            <div><input type="checkbox" name="brazo_abduccion" value="1" id="edit_brazo_abduccion" disabled onchange="updateEditLiveScore()"> Abducción o rotación del brazo</div>
                                            <span class="reba-option-score-tag">+1</span>
                                        </label>
                                        <label class="reba-option-card" style="background: #f0f9ff;">
                                            <div><input type="checkbox" name="brazo_hombro_elevado" value="1" id="edit_brazo_hombro_elevado" disabled onchange="updateEditLiveScore()"> Hombro elevado / postura sostenida</div>
                                            <span class="reba-option-score-tag">+1</span>
                                        </label>
                                        <label class="reba-option-card" style="background: #f0f9ff;">
                                            <div><input type="checkbox" name="brazo_apoyo_gravedad" value="1" id="edit_brazo_apoyo_gravedad" disabled onchange="updateEditLiveScore()"> Apoyo o postura a favor de gravedad</div>
                                            <span class="reba-option-score-tag">-1</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">2. Antebrazo</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="antebrazo_base" value="1" disabled onchange="updateEditLiveScore()"> 60°-100° flexión</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="antebrazo_base" value="2" disabled onchange="updateEditLiveScore()"> &lt; 60° ó &gt; 100° flexión</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">3. Muñeca</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="muneca_base" value="1" disabled onchange="updateEditLiveScore()"> 0°-15° flexión/extensión</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="muneca_base" value="2" disabled onchange="updateEditLiveScore()"> &gt; 15° flexión o extensión</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card" style="background: #f0f9ff;">
                                        <div><input type="checkbox" name="muneca_mod" value="1" id="edit_muneca_mod" disabled onchange="updateEditLiveScore()"> Torsión o desviación radial/cubital</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                </div>

                                <div class="reba-input-group">
                                    <label class="reba-input-label">4. Acoplamiento / Agarre</label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="agarre" value="0" disabled onchange="updateEditLiveScore()"> Bueno (asas ergonómicas)</div>
                                        <span class="reba-option-score-tag">+0</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="agarre" value="1" disabled onchange="updateEditLiveScore()"> Aceptable (sujeción regular)</div>
                                        <span class="reba-option-score-tag">+1</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="agarre" value="2" disabled onchange="updateEditLiveScore()"> Posible pero no aceptable</div>
                                        <span class="reba-option-score-tag">+2</span>
                                    </label>
                                    <label class="reba-option-card">
                                        <div><input type="radio" name="agarre" value="3" disabled onchange="updateEditLiveScore()"> Incómodo / sin agarre manual</div>
                                        <span class="reba-option-score-tag">+3</span>
                                    </label>
                                </div>
                            </div>

                            <!-- PUNTUACIÓN POR ACTIVIDAD -->
                            <div class="reba-section-box">
                                <div class="reba-section-header">
                                    <span class="reba-section-badge">C</span>
                                    <h3>Puntuación por Actividad</h3>
                                </div>

                                <label class="reba-option-card" style="background: #f8fafc;">
                                    <div><input type="checkbox" name="actividad_estatica" value="1" id="edit_actividad_estatica" disabled onchange="updateEditLiveScore()"> Partes del cuerpo estáticas (> 1 min)</div>
                                    <span class="reba-option-score-tag">+1</span>
                                </label>
                                <label class="reba-option-card" style="background: #f8fafc;">
                                    <div><input type="checkbox" name="actividad_repetitiva" value="1" id="edit_actividad_repetitiva" disabled onchange="updateEditLiveScore()"> Movimientos repetitivos (> 4 veces/min)</div>
                                    <span class="reba-option-score-tag">+1</span>
                                </label>
                                <label class="reba-option-card" style="background: #f8fafc;">
                                    <div><input type="checkbox" name="actividad_inestable" value="1" id="edit_actividad_inestable" disabled onchange="updateEditLiveScore()"> Posturas inestables o cambios bruscos</div>
                                    <span class="reba-option-score-tag">+1</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 3: Ubicación Geográfica, Mini Mapa & Resultados REBA -->
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

                        <!-- PANEL LIVE SCORE REBA -->
                        <div class="reba-live-score-box" style="margin-top: 8px;">
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; font-weight: 800; margin-bottom: 10px;">
                                Cálculo Recalculado (REBA)
                            </div>

                            <div class="reba-live-score-grid">
                                <div class="reba-live-score-item">
                                    <div class="label">Score A</div>
                                    <div class="val" id="edit_live_score_a">1</div>
                                </div>
                                <div class="reba-live-score-item">
                                    <div class="label">Score B</div>
                                    <div class="val" id="edit_live_score_b">1</div>
                                </div>
                                <div class="reba-live-score-item">
                                    <div class="label">Tabla C</div>
                                    <div class="val" id="edit_live_score_c">1</div>
                                </div>
                                <div class="reba-live-score-item">
                                    <div class="label">Actividad</div>
                                    <div class="val" id="edit_live_score_act">+0</div>
                                </div>
                            </div>

                            <div id="edit_live_final_box" class="reba-live-final-display risk-inapreciable" style="margin-top: 10px;">
                                <div class="title">Puntuación Final REBA</div>
                                <div class="score" id="edit_live_score_final">1</div>
                                <div class="action" id="edit_live_risk_level">Riesgo Inapreciable</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;" id="edit_live_action_level">
                                    Nivel 0: Postura óptima, no requiere acción
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones: Textarea directamente debajo del mapa y score -->
                        <div class="form-field-group" style="margin-top: 8px;">
                            <label class="form-field-label" for="edit_observaciones">Observaciones y Recomendaciones</label>
                            <textarea name="observaciones" id="edit_observaciones" class="custom-form-textarea" rows="3"
                                disabled
                                placeholder="Observaciones biomecánicas, fuentes de sobrecarga musculoesquelética, mejoras posturales..."></textarea>
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
