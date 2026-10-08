/**
 * ==========================================================================
 * METRIC V2 — VIBRACIÓN OCUPACIONAL (ISO 2631-1 & ISO 5349-1)
 * Lógica modularizada de cálculo, georreferenciación, modales y exportación
 * ==========================================================================
 */

let singleMapInstance = null;
let allMapInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar cálculos en modal de creación si está presente
    if (document.getElementById('create_tipo')) {
        handleVibTipoChange('create');
    }
});

/**
 * Abre el modal de nuevo punto de vibración
 */
window.openCreateMeasurementModal = function() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('open');
        handleVibTipoChange('create');
        recalculateVibracionModal('create');
    }
};

/**
 * Cierra el modal de nuevo punto
 */
window.closeCreateMeasurementModal = function() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('open');
    }
};

/**
 * Abre el modal de edición de medición con los datos del registro
 */
window.openEditMeasurementModal = function(item) {
    const modal = document.getElementById('editMeasurementModal');
    const form = document.getElementById('editMeasurementForm');
    if (!modal || !form) return;

    // Configurar la URL de acción del formulario
    form.action = `/modulos/${window.MODULE_ID}/vibracion/mediciones/${item.id}`;

    // Llenar campos del modal
    document.getElementById('edit_pt_num_disp').innerText = item.codigo || item.point_number || `VIB-${item.id}`;
    document.getElementById('edit_point_number').value = item.point_number || item.codigo || `VIB-${item.id}`;
    document.getElementById('edit_codigo').value = item.codigo || item.point_number || `VIB-${item.id}`;

    document.getElementById('edit_measurement_date').value = item.raw_date || '';
    document.getElementById('edit_measurement_time').value = item.time || '08:00';
    document.getElementById('edit_area').value = item.area || '';
    document.getElementById('edit_puesto_trabajo').value = item.puesto_trabajo || item.workstation || '';
    document.getElementById('edit_trabajador_evaluado').value = item.trabajador_evaluado || '';
    document.getElementById('edit_maquina_equipo').value = item.maquina_equipo || '';
    document.getElementById('edit_duracion_jornada_h').value = item.duracion_jornada_h || 8.0;
    document.getElementById('edit_duracion_prueba_min').value = item.duracion_prueba_min || 15;

    // Tipo
    const tipoSelect = document.getElementById('edit_tipo');
    tipoSelect.value = item.tipo || 'cuerpo_entero';

    // Tiempo de exposición
    const texpSelect = document.getElementById('edit_tiempo_expos_h');
    const matchedOption = Array.from(texpSelect.options).find(opt => parseFloat(opt.value) === parseFloat(item.tiempo_expos_h));
    if (matchedOption) {
        texpSelect.value = matchedOption.value;
    } else {
        texpSelect.value = "8.0";
    }

    // Configuración sensor
    if (item.tipo === 'cuerpo_entero') {
        document.getElementById('edit_ub_acelerometro').value = item.ub_acelerometro || 'base_asiento';
        document.getElementById('edit_aeqx').value = parseFloat(item.aeqx_ce || item.aeqx || 0).toFixed(4);
        document.getElementById('edit_aeqy').value = parseFloat(item.aeqy_ce || item.aeqy || 0).toFixed(4);
        document.getElementById('edit_aeqz').value = parseFloat(item.aeqz_ce || item.aeqz || 0).toFixed(4);
    } else {
        document.getElementById('edit_mano_afectada').value = item.mano_afectada || 'derecha';
        document.getElementById('edit_aeqx').value = parseFloat(item.aeqx_mb || item.aeqx || 0).toFixed(4);
        document.getElementById('edit_aeqy').value = parseFloat(item.aeqy_mb || item.aeqy || 0).toFixed(4);
        document.getElementById('edit_aeqz').value = parseFloat(item.aeqz_mb || item.aeqz || 0).toFixed(4);
    }

    // Georreferenciación
    document.getElementById('edit_utm_zone').value = item.utm_zone || '19K';
    document.getElementById('edit_utm_easting').value = item.utm_easting || '';
    document.getElementById('edit_utm_northing').value = item.utm_northing || '';
    document.getElementById('edit_latitude').value = item.latitude || '';
    document.getElementById('edit_longitude').value = item.longitude || '';

    // Observaciones y Personal
    document.getElementById('edit_observations').value = item.observations || '';
    if (item.staff_id && document.getElementById('edit_staff_id')) {
        document.getElementById('edit_staff_id').value = item.staff_id;
    }

    // Fotos existentes
    const previewGrid = document.getElementById('edit_photo_preview_grid');
    if (previewGrid) {
        previewGrid.innerHTML = '';
        if (item.images_urls && Array.isArray(item.images_urls)) {
            item.images_urls.forEach(url => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'photo-preview-item';
                itemDiv.innerHTML = `<img src="${url}" alt="Foto"><button type="button" class="btn-remove-photo" onclick="this.parentElement.remove()">✕</button>`;
                previewGrid.appendChild(itemDiv);
            });
        }
    }

    handleVibTipoChange('edit');
    recalculateVibracionModal('edit');

    modal.style.display = 'flex';
    modal.classList.add('open');
};

/**
 * Cierra el modal de edición
 */
window.closeEditMeasurementModal = function() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('open');
    }
};

/**
 * Controla el cambio de tipo de vibración (Cuerpo Entero vs Mano-Brazo)
 */
window.handleVibTipoChange = function(prefix) {
    const tipo = document.getElementById(`${prefix}_tipo`).value;
    const isCE = tipo === 'cuerpo_entero';

    const ceConfig = document.getElementById(`${prefix}_sensor_config_ce`);
    const mbConfig = document.getElementById(`${prefix}_sensor_config_mb`);
    const badge = document.getElementById(`${prefix}_sensor_mode_badge`);

    if (ceConfig) ceConfig.style.display = isCE ? 'flex' : 'none';
    if (mbConfig) mbConfig.style.display = !isCE ? 'flex' : 'none';

    if (badge) {
        badge.className = isCE ? 'badge-mode ce' : 'badge-mode mb';
        badge.innerText = isCE ? 'ISO 2631-1' : 'ISO 5349-1';
    }

    recalculateVibracionModal(prefix);
};

/**
 * Recalcula en vivo las aceleraciones ponderadas, A(8) y estado normativo
 */
window.recalculateVibracionModal = function(prefix) {
    const tipo = document.getElementById(`${prefix}_tipo`).value;
    const isCE = tipo === 'cuerpo_entero';

    const ax = parseFloat(document.getElementById(`${prefix}_aeqx`).value) || 0;
    const ay = parseFloat(document.getElementById(`${prefix}_aeqy`).value) || 0;
    const az = parseFloat(document.getElementById(`${prefix}_aeqz`).value) || 0;
    const texp = parseFloat(document.getElementById(`${prefix}_tiempo_expos_h`).value) || 8.0;

    let atotal = 0;
    let nivelAccion = 0.50;
    let limiteVle = 1.15;

    if (isCE) {
        // ISO 2631-1: Av = sqrt( (1.4*ax)^2 + (1.4*ay)^2 + (1.0*az)^2 )
        const wx = 1.4 * ax;
        const wy = 1.4 * ay;
        const wz = 1.0 * az;
        atotal = Math.sqrt((wx * wx) + (wy * wy) + (wz * wz));
        nivelAccion = 0.50;
        limiteVle = 1.15;
    } else {
        // ISO 5349-1: Ahv = sqrt( ax^2 + ay^2 + az^2 )
        atotal = Math.sqrt((ax * ax) + (ay * ay) + (az * az));
        nivelAccion = 2.50;
        limiteVle = 5.00;
    }

    // A(8) = atotal * sqrt(Texp / 8)
    const factor = Math.sqrt(Math.max(texp, 0.01) / 8.0);
    const a8 = atotal * factor;

    // Actualizar vista previa
    const a8Elem = document.getElementById(`${prefix}_calc_a8`);
    const atotalElem = document.getElementById(`${prefix}_calc_atotal`);
    const vleElem = document.getElementById(`${prefix}_calc_vle`);
    const badgeElem = document.getElementById(`${prefix}_calc_badge`);

    if (a8Elem) a8Elem.innerHTML = `${a8.toFixed(4)} <span style="font-size: 13px; font-weight: 600; color: #64748b;">m/s²</span>`;
    if (atotalElem) atotalElem.innerText = atotal.toFixed(4);
    if (vleElem) vleElem.innerText = `${limiteVle.toFixed(2)} m/s²`;

    if (badgeElem) {
        if (a8 <= nivelAccion) {
            badgeElem.className = 'badge-status-eval pass';
            badgeElem.innerText = 'CUMPLE';
        } else if (a8 <= limiteVle) {
            badgeElem.className = 'badge-status-eval warn';
            badgeElem.innerText = 'NIVEL DE ACCIÓN';
        } else {
            badgeElem.className = 'badge-status-eval fail';
            badgeElem.innerText = 'SUPERA LÍMITE';
        }
    }
};

/**
 * Calibración GNSS / GPS de 4 segundos
 */
window.getCurrentGpsPosition = function(prefix) {
    const accuracyElem = document.getElementById(`${prefix}_gps_accuracy`);
    if (accuracyElem) accuracyElem.value = 'Calibrando (4s)...';

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const acc = pos.coords.accuracy || 3.5;

                document.getElementById(`${prefix}_latitude`).value = lat;
                document.getElementById(`${prefix}_longitude`).value = lng;
                if (accuracyElem) accuracyElem.value = `±${acc.toFixed(1)} m`;

                // Convertir WGS84 a UTM aproximado
                const utm = latLngToUtm(lat, lng);
                document.getElementById(`${prefix}_utm_zone`).value = utm.zone;
                document.getElementById(`${prefix}_utm_easting`).value = utm.easting.toFixed(3);
                document.getElementById(`${prefix}_utm_northing`).value = utm.northing.toFixed(3);
            },
            (err) => {
                if (accuracyElem) accuracyElem.value = '±3.5 m (Por defecto)';
                document.getElementById(`${prefix}_utm_zone`).value = '19K';
                document.getElementById(`${prefix}_utm_easting`).value = '592450.000';
                document.getElementById(`${prefix}_utm_northing`).value = '8175320.000';
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    }
};

function latLngToUtm(lat, lng) {
    // Estimación matemática estándar UTM WGS84
    const zoneNumber = Math.floor((lng + 180) / 6) + 1;
    const zoneLetter = lat >= 0 ? 'N' : 'K';
    const radLat = (lat * Math.PI) / 180.0;
    const radLng = (lng * Math.PI) / 180.0;

    const easting = 500000 + (lng - ((zoneNumber - 1) * 6 - 180 + 3)) * 111320 * Math.cos(radLat);
    const northing = lat >= 0 ? lat * 110574 : 10000000 + lat * 110574;

    return {
        zone: `${zoneNumber}${zoneLetter}`,
        easting: Math.abs(easting),
        northing: Math.abs(northing)
    };
}

/**
 * Previsualización de fotografías cargadas
 */
window.previewVibracionPhotos = function(event, prefix) {
    const grid = document.getElementById(`${prefix}_photo_preview_grid`);
    if (!grid) return;
    grid.innerHTML = '';

    const files = event.target.files;
    if (files) {
        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'photo-preview-item';
                itemDiv.innerHTML = `<img src="${e.target.result}" alt="Preview"><button type="button" class="btn-remove-photo" onclick="this.parentElement.remove()">✕</button>`;
                grid.appendChild(itemDiv);
            };
            reader.readAsDataURL(file);
        });
    }
};

/**
 * Filtro de la tabla maestra
 */
window.filterVibracionTable = function() {
    const searchVal = (document.getElementById('tableSearchInput')?.value || '').toLowerCase().trim();
    const tipoVal = document.getElementById('filterTipoSelect')?.value || '';
    const estadoVal = document.getElementById('filterEstadoSelect')?.value || '';

    const rows = document.querySelectorAll('#vibracionTableBody tr[data-search]');
    let count = 0;

    rows.forEach(row => {
        const rowSearch = row.getAttribute('data-search') || '';
        const rowTipo = row.getAttribute('data-tipo') || '';
        const rowEstado = row.getAttribute('data-estado') || '';

        const matchSearch = !searchVal || rowSearch.includes(searchVal);
        const matchTipo = !tipoVal || rowTipo === tipoVal;
        const matchEstado = !estadoVal || rowEstado === estadoVal;

        if (matchSearch && matchTipo && matchEstado) {
            row.style.display = '';
            count++;
        } else {
            row.style.display = 'none';
        }
    });

    const countDisp = document.getElementById('filteredCountDisp');
    if (countDisp) countDisp.innerText = count;
};

/**
 * Confirmación y eliminación de medición
 */
window.confirmDeleteVibracionMeasurement = function(id, code) {
    if (confirm(`¿Estás seguro de que deseas eliminar la medición ${code}? Esta acción no se puede deshacer.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/modulos/${window.MODULE_ID}/vibracion/mediciones/${id}`;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = window.CSRF_TOKEN;

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';

        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
};

/**
 * Modales auxiliares de mapas, fotos, tablas y exportación
 */
window.openSingleLocationModal = function(item) {
    const modal = document.getElementById('singleLocationModal');
    if (!modal) return;

    document.getElementById('singleLocCode').innerText = item.codigo || `VIB-${item.id}`;
    document.getElementById('singleLocArea').innerText = item.area || '—';
    document.getElementById('singleLocUtm').innerText = `${item.utm_easting || '—'} E, ${item.utm_northing || '—'} N (${item.utm_zone || '19K'})`;

    modal.style.display = 'flex';
    modal.classList.add('open');

    setTimeout(() => {
        const lat = parseFloat(item.latitude) || -16.5;
        const lng = parseFloat(item.longitude) || -68.15;
        if (!singleMapInstance) {
            singleMapInstance = L.map('singleLocationMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(singleMapInstance);
        } else {
            singleMapInstance.setView([lat, lng], 15);
        }
        singleMapInstance.eachLayer(l => { if (l instanceof L.Marker) singleMapInstance.removeLayer(l); });
        L.marker([lat, lng]).addTo(singleMapInstance)
            .bindPopup(`<b>${item.codigo}</b><br>${item.area}<br>A(8): ${item.a8} m/s²`).openPopup();
        singleMapInstance.invalidateSize();
    }, 200);
};

window.closeSingleLocationModal = function() {
    const modal = document.getElementById('singleLocationModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('open');
    }
};

window.openAllLocationsModal = function() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.style.display = 'flex';
    modal.classList.add('open');

    setTimeout(() => {
        if (!allMapInstance) {
            allMapInstance = L.map('allLocationsMap').setView([-16.5, -68.15], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(allMapInstance);
        }
        allMapInstance.eachLayer(l => { if (l instanceof L.Marker) allMapInstance.removeLayer(l); });

        const bounds = [];
        const measurements = window.ALL_MEASUREMENTS_DATA || [];
        measurements.forEach(m => {
            if (m.latitude && m.longitude) {
                const lat = parseFloat(m.latitude);
                const lng = parseFloat(m.longitude);
                bounds.push([lat, lng]);
                L.marker([lat, lng]).addTo(allMapInstance)
                    .bindPopup(`<b>${m.codigo}</b><br>${m.area}<br>A(8): ${m.a8} m/s²<br><b>${m.estado}</b>`);
            }
        });

        if (bounds.length > 0) {
            allMapInstance.fitBounds(bounds, { padding: [30, 30] });
        }
        allMapInstance.invalidateSize();
    }, 200);
};

window.closeAllLocationsModal = function() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('open');
    }
};

window.openPhotoViewerModal = function(images, title) {
    const modal = document.getElementById('photoViewerModal');
    if (!modal) return;

    document.getElementById('photoViewerTitle').innerText = title || 'Evidencia Fotográfica';
    const mainImg = document.getElementById('mainViewerImage');
    const thumbRow = document.getElementById('photoThumbnailsRow');
    thumbRow.innerHTML = '';

    if (images && images.length > 0) {
        mainImg.src = images[0];
        images.forEach(imgUrl => {
            const thumb = document.createElement('img');
            thumb.src = imgUrl;
            thumb.style = 'width: 54px; height: 54px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid #cbd5e1;';
            thumb.onclick = () => { mainImg.src = imgUrl; };
            thumbRow.appendChild(thumb);
        });
    }

    modal.style.display = 'flex';
    modal.classList.add('open');
};

window.closePhotoViewerModal = function() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('open');
    }
};

window.openExportModal = function() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('open');
    }
};

window.closeExportModal = function() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('open');
    }
};

window.openNormativeTablesModal = function() {
    const modal = document.getElementById('normativeTablesModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('open');
    }
};

window.closeNormativeTablesModal = function() {
    const modal = document.getElementById('normativeTablesModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('open');
    }
};

/**
 * Exportación a Excel de alta fidelidad con ExcelJS
 */
window.exportVibracionExcel = async function() {
    if (typeof ExcelJS === 'undefined') {
        alert('Cargando librería ExcelJS. Intente en unos segundos.');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Monitoreo Vibración');

    worksheet.columns = [
        { header: 'CÓDIGO', key: 'codigo', width: 12 },
        { header: 'FECHA', key: 'date', width: 14 },
        { header: 'HORA', key: 'time', width: 10 },
        { header: 'ÁREA', key: 'area', width: 22 },
        { header: 'PUESTO DE TRABAJO', key: 'puesto', width: 22 },
        { header: 'TRABAJADOR', key: 'trabajador', width: 22 },
        { header: 'MÁQUINA / EQUIPO', key: 'maquina', width: 20 },
        { header: 'MODALIDAD', key: 'tipo', width: 18 },
        { header: 'T. EXP (h)', key: 'texp', width: 12 },
        { header: 'AEQ X (m/s²)', key: 'aeqx', width: 14 },
        { header: 'AEQ Y (m/s²)', key: 'aeqy', width: 14 },
        { header: 'AEQ Z (m/s²)', key: 'aeqz', width: 14 },
        { header: 'A(8) (m/s²)', key: 'a8', width: 14 },
        { header: 'NIVEL ACCIÓN', key: 'na', width: 14 },
        { header: 'LÍMITE VLE', key: 'vle', width: 14 },
        { header: 'EVALUACIÓN', key: 'estado', width: 18 },
    ];

    worksheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
    worksheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF97316' } };
    worksheet.getRow(1).alignment = { horizontal: 'center', vertical: 'middle' };

    const measurements = window.ALL_MEASUREMENTS_DATA || [];
    measurements.forEach(m => {
        worksheet.addRow({
            codigo: m.codigo,
            date: m.date,
            time: m.time,
            area: m.area,
            puesto: m.puesto_trabajo,
            trabajador: m.trabajador_evaluado,
            maquina: m.maquina_equipo,
            tipo: m.tipo === 'cuerpo_entero' ? 'Cuerpo Entero' : 'Mano-Brazo',
            texp: m.tiempo_expos_h,
            aeqx: m.aeqx,
            aeqy: m.aeqy,
            aeqz: m.aeqz,
            a8: m.a8,
            na: m.nivel_accion,
            vle: m.limite_vle,
            estado: m.estado,
        });
    });

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Monitoreo_Vibracion_Modulo_${window.MODULE_ID}.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};
