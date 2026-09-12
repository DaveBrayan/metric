/**
 * MONITOREO DE OPACIDAD (VEHICULAR / HUMOS) — INTERACTION LOGIC
 * Pachabol Systems Metric v2
 */

// Global state
let opacityMeasurements = window.ALL_MEASUREMENTS_DATA || [];
let allLocationsMap = null;
let allLocationsMarkers = [];

// =========================================================================
// 1. MODALES DE CREACIÓN Y EDICIÓN
// =========================================================================
window.openCreateMeasurementModal = function () {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        recalcOpacidad('create');
    }
};

window.closeCreateMeasurementModal = function () {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.openEditMeasurementModal = function (item) {
    const modal = document.getElementById('editMeasurementModal');
    if (!modal) return;

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        form.action = `/modulos/${window.MODULE_ID}/opacidad/mediciones/${item.id}`;
    }

    document.getElementById('edit_tipo_vehiculo').value = item.tipo_vehiculo || '';
    document.getElementById('edit_marca').value = item.marca || '';
    document.getElementById('edit_modelo').value = item.modelo || '';
    document.getElementById('edit_placa').value = item.placa || '';

    document.getElementById('edit_temp_c').value = item.temp_c !== null ? item.temp_c : '';
    document.getElementById('edit_opa_1').value = item.opa_1 !== null ? item.opa_1 : '';
    document.getElementById('edit_opa_2').value = item.opa_2 !== null ? item.opa_2 : '';
    document.getElementById('edit_opa_3').value = item.opa_3 !== null ? item.opa_3 : '';
    document.getElementById('edit_rpm_1').value = item.rpm_1 !== null ? item.rpm_1 : '';
    document.getElementById('edit_rpm_2').value = item.rpm_2 !== null ? item.rpm_2 : '';
    document.getElementById('edit_rpm_3').value = item.rpm_3 !== null ? item.rpm_3 : '';

    document.getElementById('edit_limite_normativa').value = item.limite_normativa || '50.0';
    document.getElementById('edit_measurement_date').value = item.date_raw || '';
    document.getElementById('edit_measurement_time').value = item.time_raw || '';

    document.getElementById('edit_latitude').value = item.latitude || '';
    document.getElementById('edit_longitude').value = item.longitude || '';
    document.getElementById('edit_utm_zone').value = item.utm_zone || '19K';
    document.getElementById('edit_utm_easting').value = item.utm_easting || '';
    document.getElementById('edit_utm_northing').value = item.utm_northing || '';
    document.getElementById('edit_location_description').value = item.location_description || '';
    document.getElementById('edit_observations').value = item.observations || '';

    if (item.staff_id) {
        document.getElementById('edit_staff_id').value = item.staff_id;
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    recalcOpacidad('edit');
};

window.closeEditMeasurementModal = function () {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

// =========================================================================
// 2. CÁLCULO EN VIVO DE PROMEDIOS Y CUMPLIMIENTO LMP
// =========================================================================
window.recalcOpacidad = function (prefix) {
    const o1 = parseFloat(document.getElementById(`${prefix}_opa_1`)?.value) || null;
    const o2 = parseFloat(document.getElementById(`${prefix}_opa_2`)?.value) || null;
    const o3 = parseFloat(document.getElementById(`${prefix}_opa_3`)?.value) || null;

    const r1 = parseFloat(document.getElementById(`${prefix}_rpm_1`)?.value) || null;
    const r2 = parseFloat(document.getElementById(`${prefix}_rpm_2`)?.value) || null;
    const r3 = parseFloat(document.getElementById(`${prefix}_rpm_3`)?.value) || null;

    const limite = parseFloat(document.getElementById(`${prefix}_limite_normativa`)?.value) || 50.0;

    // Promedio Opacidad
    const oVals = [o1, o2, o3].filter(v => v !== null);
    let avgOpa = null;
    if (oVals.length > 0) {
        avgOpa = oVals.reduce((a, b) => a + b, 0) / oVals.length;
    }

    // Promedio RPM
    const rVals = [r1, r2, r3].filter(v => v !== null);
    let avgRpm = null;
    if (rVals.length > 0) {
        avgRpm = Math.round(rVals.reduce((a, b) => a + b, 0) / rVals.length);
    }

    const dispOpa = document.getElementById(`${prefix}_opa_promedio_display`);
    const dispRpm = document.getElementById(`${prefix}_rpm_promedio_display`);
    const badgeCumple = document.getElementById(`${prefix}_cumple_badge`);

    if (dispOpa) {
        dispOpa.textContent = avgOpa !== null ? `${avgOpa.toFixed(2)} %` : '— %';
    }
    if (dispRpm) {
        dispRpm.textContent = avgRpm !== null ? `${avgRpm} RPM` : '— RPM';
    }

    if (badgeCumple) {
        if (avgOpa === null) {
            badgeCumple.innerHTML = `<span class="badge-compliance-ok" style="background:#f1f5f9;color:#64748b;border-color:#cbd5e1;"><span>PENDIENTE</span></span>`;
        } else if (avgOpa <= limite) {
            badgeCumple.innerHTML = `<span class="badge-compliance-ok"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><polyline points="20 6 9 17 4 12"/></svg><span>CUMPLE LMP (≤ ${limite}%)</span></span>`;
        } else {
            badgeCumple.innerHTML = `<span class="badge-compliance-danger"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><span>SUPERA LÍMITE (> ${limite}%)</span></span>`;
        }
    }
};

// =========================================================================
// 3. CAPTURA DE COORDENADAS GPS & CÁLCULO UTM
// =========================================================================
window.captureCoordinatesGPS = function (prefix) {
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización satelital.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            document.getElementById(`${prefix}_latitude`).value = lat.toFixed(7);
            document.getElementById(`${prefix}_longitude`).value = lng.toFixed(7);

            const utm = latLngToUTM(lat, lng);
            document.getElementById(`${prefix}_utm_zone`).value = utm.zone;
            document.getElementById(`${prefix}_utm_easting`).value = utm.easting.toFixed(1);
            document.getElementById(`${prefix}_utm_northing`).value = utm.northing.toFixed(1);

            const descInput = document.getElementById(`${prefix}_location_description`);
            if (descInput && !descInput.value) {
                descInput.value = `Z: ${utm.zone}, E: ${utm.easting.toFixed(1)}, N: ${utm.northing.toFixed(1)}`;
            }
        },
        function (err) {
            alert('No se pudo obtener la posición satelital: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

function latLngToUTM(lat, lng) {
    const zone = Math.floor((lng + 180) / 6) + 1;
    const utmZone = `${zone}K`;

    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const k0 = 0.9996;
    const e = Math.sqrt(2 * f - f * f);

    const latRad = lat * (Math.PI / 180);
    const lngRad = lng * (Math.PI / 180);
    const lngOrigin = ((zone - 1) * 6 - 180 + 3) * (Math.PI / 180);

    const n = a / Math.sqrt(1 - Math.pow(e * Math.sin(latRad), 2));
    const t = Math.pow(Math.tan(latRad), 2);
    const c = (Math.pow(e, 2) / (1 - Math.pow(e, 2))) * Math.pow(Math.cos(latRad), 2);
    const al = Math.cos(latRad) * (lngRad - lngOrigin);

    const m = a * (
        (1 - Math.pow(e, 2) / 4 - 3 * Math.pow(e, 4) / 64 - 5 * Math.pow(e, 6) / 256) * latRad
        - (3 * Math.pow(e, 2) / 8 + 3 * Math.pow(e, 4) / 32 + 45 * Math.pow(e, 6) / 1024) * Math.sin(2 * latRad)
        + (15 * Math.pow(e, 4) / 256 + 45 * Math.pow(e, 6) / 1024) * Math.sin(4 * latRad)
        - (35 * Math.pow(e, 6) / 3072) * Math.sin(6 * latRad)
    );

    const easting = k0 * n * (al + (1 - t + c) * Math.pow(al, 3) / 6 + (5 - 18 * t + Math.pow(t, 2) + 72 * c - 58 * Math.pow(e, 2)) * Math.pow(al, 5) / 120) + 500000.0;
    let northing = k0 * (m + n * Math.tan(latRad) * (Math.pow(al, 2) / 2 + (5 - t + 9 * c + 4 * Math.pow(c, 2)) * Math.pow(al, 4) / 24 + (61 - 58 * t + Math.pow(t, 2) + 600 * c - 330 * Math.pow(e, 2)) * Math.pow(al, 6) / 720));
    if (lat < 0) northing += 10000000.0;

    return { zone: utmZone, easting, northing };
}

// =========================================================================
// 4. AUTO-GUARDADO INLINE DE ENCABEZADO TÉCNICO
// =========================================================================
window.autoSaveHeaderField = function () {
    const badge = document.getElementById('headerAutoSaveBadge');
    if (badge) {
        badge.classList.add('visible', 'saving');
        badge.querySelector('span').textContent = 'Guardando...';
    }

    const payload = {
        installation_name: document.getElementById('inline_installation_name')?.value || '',
        start_date: document.getElementById('inline_start_date')?.value || '',
        end_date: document.getElementById('inline_end_date')?.value || '',
        monitoring_type: document.getElementById('inline_monitoring_type')?.value || '',
        _token: window.CSRF_TOKEN,
    };

    fetch(`/modulos/${window.MODULE_ID}/opacidad/header`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(res => res.json())
    .then(data => {
        if (badge) {
            badge.classList.remove('saving');
            badge.querySelector('span').textContent = 'Guardado';
            setTimeout(() => { badge.classList.remove('visible'); }, 2500);
        }
    })
    .catch(err => {
        if (badge) {
            badge.classList.remove('saving');
            badge.querySelector('span').textContent = 'Error';
        }
    });
};

// =========================================================================
// 5. FILTROS Y BÚSQUEDA REACTIVA EN TABLA
// =========================================================================
window.filterOpacityTable = function (type, btn) {
    document.querySelectorAll('.filter-pill-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const rows = document.querySelectorAll('.opacity-row-item');
    rows.forEach(r => {
        const isCompliant = r.getAttribute('data-compliant') === 'true';
        if (type === 'all') {
            r.style.display = '';
        } else if (type === 'compliant') {
            r.style.display = isCompliant ? '' : 'none';
        } else if (type === 'danger') {
            r.style.display = !isCompliant ? '' : 'none';
        }
    });
};

window.searchOpacityTable = function () {
    const q = document.getElementById('opacitySearchInput')?.value.toLowerCase().trim() || '';
    const rows = document.querySelectorAll('.opacity-row-item');

    rows.forEach(r => {
        const text = (r.getAttribute('data-search') || '').toLowerCase();
        r.style.display = text.includes(q) ? '' : 'none';
    });
};

// =========================================================================
// 6. MODALES AUXILIARES (UBICACIONES, EXPORT, TABLAS, FOTOS)
// =========================================================================
window.openAllLocationsModal = function () {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        initAllLocationsLeafletMap();
    }, 200);
};

window.closeAllLocationsModal = function () {
    const modal = document.getElementById('allLocationsModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

function initAllLocationsLeafletMap() {
    if (allLocationsMap) {
        allLocationsMap.invalidateSize();
        return;
    }

    const mapContainer = document.getElementById('allLocationsMapLeaflet');
    if (!mapContainer) return;

    allLocationsMap = L.map('allLocationsMapLeaflet').setView([-16.5, -68.15], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(allLocationsMap);

    const validPoints = opacityMeasurements.filter(m => m.latitude && m.longitude);
    const latLngBounds = [];

    validPoints.forEach((m, idx) => {
        const color = m.is_compliant ? '#059669' : '#dc2626';
        const marker = L.circleMarker([m.latitude, m.longitude], {
            radius: 8,
            fillColor: color,
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(allLocationsMap);

        marker.bindPopup(`
            <div style="font-family:'Outfit',sans-serif; font-size:12.5px; line-height:1.4;">
                <strong style="color:#4f46e5;">#${m.point_number} — ${m.placa || 'Sin Placa'}</strong><br>
                <span>Vehículo: <strong>${m.tipo_vehiculo} ${m.marca} ${m.modelo}</strong></span><br>
                <span>Opacidad: <strong>${m.opa_promedio !== null ? m.opa_promedio + '%' : '—'}</strong> (LMP: ${m.limite_normativa}%)</span><br>
                <span>Estado: <strong style="color:${color};">${m.is_compliant ? 'CUMPLE' : 'SUPERA LÍMITE'}</strong></span><br>
                <span>Técnico: ${m.staff_name}</span>
            </div>
        `);

        allLocationsMarkers.push(marker);
        latLngBounds.push([m.latitude, m.longitude]);
    });

    if (latLngBounds.length > 0) {
        allLocationsMap.fitBounds(latLngBounds, { padding: [30, 30] });
    }
}

window.focusPointOnAllLocationsMap = function (idx) {
    if (allLocationsMarkers[idx]) {
        const m = allLocationsMarkers[idx];
        allLocationsMap.setView(m.getLatLng(), 16);
        m.openPopup();
    }
};

window.openExportModal = function () {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closeExportModal = function () {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.openOpacityTablesModal = function () {
    const modal = document.getElementById('opacityTablesModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closeOpacityTablesModal = function () {
    const modal = document.getElementById('opacityTablesModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.openPhotoViewerModal = function (photoUrl, title) {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const t = document.getElementById('photoViewerTitle');
    if (modal && img) {
        img.src = photoUrl;
        if (t) t.textContent = title || 'Evidencia Fotográfica';
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closePhotoViewerModal = function () {
    const modal = document.getElementById('photoViewerModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.openPhotoReportModal = function () {
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closePhotoReportModal = function () {
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};
