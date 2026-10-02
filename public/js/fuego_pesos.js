/**
 * =============================================================================
 * CARGA DE FUEGO POR PESO (NB 58005 / NTP 453) — JAVASCRIPT ENGINE (METRIC v2)
 * =============================================================================
 */

// 1. CATÁLOGOS NORMATIVOS DE ACTIVIDADES Y MATERIALES COMBUSTIBLES CON VALORES Ki (Pci)
const FIRE_WEIGHT_ACTIVITIES_CATALOG = [
    'Aceites comestibles – fabricación',
    'Almacenes - en general',
    'Barnices - fabricación',
    'Barnizados - taller',
    'Bebidas - sin alcohol',
    'Bebidas alcohólicas fabricación',
    'Bebidas carbonatadas - fabricación',
    'Betún - preparación',
    'Carpintería',
    'Café - torrefacto',
    'Cartón - fabricación de cajas y elementos',
    'Caucho - fabricación de objetos',
    'Celuloide - fabricación',
    'Cera - fabricación de artículos',
    'Cerámica - taller',
    'Cerveza - fabricación',
    'Chocolate - fabricación',
    'Colas - fabricación',
    'Confección - talleres',
    'Conservas - fabricación',
    'Corcho - tratamiento',
    'Cuerdas',
    'Fabricación Cosméticos',
    'Cuero - tratamiento y objetos',
    'Destilerías - mat. inflamables',
    'Disolventes - destilación',
    'Ebanistería (sin alm. madera)',
    'Electricista - taller',
    'Electricidad - fabricación aparatos',
    'Electricidad - reparación aparatos',
    'Electrónica – fabricación aparatos',
    'Electrónica - reparación aparatos',
    'Motores eléctricos - fabricación',
    'Orfebrería - fabricación',
    'Panificación - elaboración y hornos de pan',
    'Pasamanería - taller',
    'Embarcaciones - fabricación',
    'Escobas - fabricación',
    'Esterillas - fabricación',
    'Fertilizantes químicos - fabricación',
    'Fibras artificiales',
    'Producción manipulación',
    'Forjas y herrerías',
    'Frigoríficos - cámaras',
    'Fundición de metales',
    'Galvanoplástica',
    'Géneros de punto - fabricación',
    'Grasas comestibles - fabricación',
    'Imprenta',
    'Industrias químicas',
    'Juguetes - fabricación',
    'Laboratorios eléctricos',
    'Laboratorios físicos y metalúrgicos',
    'Laboratorios fotográficos',
    'Laboratorios químicos',
    'Licores - fabricación',
    'Madera – fabricación contrachapados',
    'Mampostería - fabricación',
    'Mantequilla - fabricación',
    'Máquinas - fabricación',
    'Marcos - fabricación',
    'Materiales usados - tratamiento',
    'Mecanización de metales',
    'Medias - fabricación',
    'Medicamentos - laboratorios',
    'Metales - fabricación de artículos',
    'Muebles - fabricación (madera)',
    'Muebles - fabricación (metal)',
    'Molinos harineros',
    'Resinas sintéticas - fabricación',
    'Sacos - fabricación',
    'Seda artificial - fabricación',
    'Taller mecánico',
    'Papel - fabricación',
    'Pastas alimenticias - fabricación',
    'Pinturas - talleres',
    'Pinturas y barnices - fabricación',
    'Pinceles y cepillos - fabricación',
    'Pirotecnia - fabricación',
    'Plancha - taller',
    'Placas de resina sintética -fabricación',
    'Productos alimenticios - fabricación',
    'Reparaciones - taller',
    'Tapicería',
    'Teatro',
    'Tejidos - fábricas',
    'Telefónica - central',
    'Tintas de imprenta - fabricación',
    'Tintorerías',
    'Transformadores - construcción',
    'Vidrio - fabricación de artículos',
    'Vulcanización',
    'Zapatos - fabricación'
];

const FIRE_MATERIALS_CATALOG = [
    { name: 'Aceite de algodón', ki_mcal: 9.40, ki_mj: 39.33, ci: 1.6 },
    { name: 'Aceite de lino', ki_mcal: 9.40, ki_mj: 39.33, ci: 1.6 },
    { name: 'Aceite mineral', ki_mcal: 10.00, ki_mj: 41.84, ci: 1.6 },
    { name: 'Aceite de oliva', ki_mcal: 9.30, ki_mj: 38.91, ci: 1.6 },
    { name: 'Aceite de parafina', ki_mcal: 10.30, ki_mj: 43.10, ci: 1.6 },
    { name: 'Acetona', ki_mcal: 7.40, ki_mj: 30.96, ci: 1.6 },
    { name: 'Acetileno', ki_mcal: 11.90, ki_mj: 49.79, ci: 1.6 },
    { name: 'Ácido acético', ki_mcal: 3.50, ki_mj: 14.64, ci: 1.3 },
    { name: 'Aguarrás', ki_mcal: 10.20, ki_mj: 42.68, ci: 1.6 },
    { name: 'Alcohol etílico', ki_mcal: 7.10, ki_mj: 29.71, ci: 1.6 },
    { name: 'Alcohol metílico', ki_mcal: 5.30, ki_mj: 22.18, ci: 1.6 },
    { name: 'Algodón', ki_mcal: 4.00, ki_mj: 16.74, ci: 1.3 },
    { name: 'Almidón', ki_mcal: 4.20, ki_mj: 17.57, ci: 1.3 },
    { name: 'Antracita', ki_mcal: 7.80, ki_mj: 32.64, ci: 1.0 },
    { name: 'Azúcar', ki_mcal: 4.00, ki_mj: 16.74, ci: 1.3 },
    { name: 'Azufre', ki_mcal: 2.20, ki_mj: 9.20, ci: 1.0 },
    { name: 'Bencina', ki_mcal: 10.50, ki_mj: 43.93, ci: 1.6 },
    { name: 'Butano', ki_mcal: 11.80, ki_mj: 49.37, ci: 1.6 },
    { name: 'Café', ki_mcal: 3.80, ki_mj: 15.90, ci: 1.3 },
    { name: 'Carbón vegetal', ki_mcal: 7.50, ki_mj: 31.38, ci: 1.3 },
    { name: 'Cartón', ki_mcal: 4.00, ki_mj: 16.74, ci: 1.3 },
    { name: 'Cartón asfáltico', ki_mcal: 8.50, ki_mj: 35.56, ci: 1.6 },
    { name: 'Caucho', ki_mcal: 9.50, ki_mj: 39.75, ci: 1.6 },
    { name: 'Celuloide', ki_mcal: 4.50, ki_mj: 18.83, ci: 1.6 },
    { name: 'Cera', ki_mcal: 10.20, ki_mj: 42.68, ci: 1.6 },
    { name: 'Chocolate', ki_mcal: 5.80, ki_mj: 24.27, ci: 1.3 },
    { name: 'Cloruro de polivinilo (PVC)', ki_mcal: 4.80, ki_mj: 20.08, ci: 1.6 },
    { name: 'Cuero', ki_mcal: 4.60, ki_mj: 19.25, ci: 1.3 },
    { name: 'Gasóleo (Diesel)', ki_mcal: 10.50, ki_mj: 43.93, ci: 1.6 },
    { name: 'Glicerina', ki_mcal: 4.30, ki_mj: 17.99, ci: 1.3 },
    { name: 'Grasas', ki_mcal: 9.20, ki_mj: 38.49, ci: 1.6 },
    { name: 'Harina de trigo', ki_mcal: 4.00, ki_mj: 16.74, ci: 1.3 },
    { name: 'Lana', ki_mcal: 5.00, ki_mj: 20.92, ci: 1.3 },
    { name: 'Lino', ki_mcal: 4.00, ki_mj: 16.74, ci: 1.3 },
    { name: 'Madera', ki_mcal: 4.40, ki_mj: 18.41, ci: 1.3 },
    { name: 'Magnesio', ki_mcal: 5.90, ki_mj: 24.69, ci: 1.6 },
    { name: 'Mantequilla', ki_mcal: 9.20, ki_mj: 38.49, ci: 1.6 },
    { name: 'Papel', ki_mcal: 4.00, ki_mj: 16.74, ci: 1.3 },
    { name: 'Parafina', ki_mcal: 10.30, ki_mj: 43.10, ci: 1.6 },
    { name: 'Petróleo crudo', ki_mcal: 10.20, ki_mj: 42.68, ci: 1.6 },
    { name: 'Polietileno (PE)', ki_mcal: 11.10, ki_mj: 46.44, ci: 1.6 },
    { name: 'Poliéster', ki_mcal: 6.20, ki_mj: 25.94, ci: 1.6 },
    { name: 'Poliestireno (Plastoform)', ki_mcal: 9.90, ki_mj: 41.42, ci: 1.6 },
    { name: 'Poliuretano (Espuma)', ki_mcal: 6.00, ki_mj: 25.10, ci: 1.6 },
    { name: 'Propano', ki_mcal: 12.00, ki_mj: 50.21, ci: 1.6 },
    { name: 'Resina sintética', ki_mcal: 8.50, ki_mj: 35.56, ci: 1.6 },
    { name: 'Seda', ki_mcal: 4.80, ki_mj: 20.08, ci: 1.3 },
    { name: 'Tabaco', ki_mcal: 4.10, ki_mj: 17.15, ci: 1.3 },
    { name: 'Turba', ki_mcal: 4.50, ki_mj: 18.83, ci: 1.3 },
    { name: 'Viscosa / Rayón', ki_mcal: 4.20, ki_mj: 17.57, ci: 1.3 }
];

const FIRE_EQUIPMENT_TYPES = [
    'EXTINTOR',
    'PULSADOR',
    'ALARMA',
    'HIDRANTE',
    'BOCA DE INCENDIO'
];

let allLocationsMapInstance = null;
let singleMapInstance = null;

// =============================================================================
// 2. INICIALIZACIÓN Y CONFIGURACIÓN GLOBAL
// =============================================================================
document.addEventListener('DOMContentLoaded', function () {
    initPagination();
    renderAllInteractivePhotoCards();
    updatePhotoSelectionCount();

    // Iniciar con al menos 1 material y 1 equipo en create modal
    if (document.getElementById('create_materials_container')) {
        addMaterialWeightRow('create');
        addFireEquipmentRow('create', 'EXTINTOR', 2, 'PQS 6kg Tipo ABC');
    }
});

// =============================================================================
// 3. ENCABEZADO TÉCNICO AUTOSAVE
// =============================================================================
function autoSaveHeaderField() {
    const data = {
        _token: window.CSRF_TOKEN,
        installation_name: document.getElementById('inline_installation_name')?.value || '',
        start_date: document.getElementById('inline_start_date')?.value || '',
        end_date: document.getElementById('inline_end_date')?.value || '',
        monitoring_type: document.getElementById('inline_monitoring_type')?.value || ''
    };

    fetch(window.METRIC_FIRE_WEIGHT_CONFIG?.updateHeaderUrl || `/modulos/${window.MODULE_ID}/carga-fuego-peso/header`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        const badge = document.getElementById('headerAutoSaveBadge');
        if (badge) {
            badge.classList.add('visible');
            setTimeout(() => badge.classList.remove('visible'), 2000);
        }
    })
    .catch(err => console.error('Error al guardar encabezado técnico:', err));
}
window.autoSaveHeaderField = autoSaveHeaderField;

// =============================================================================
// 4. BÚSQUEDA REACTIVA Y PAGINACIÓN EN TABLA MAESTRA
// =============================================================================
let currentPage = 1;
const pageSize = 10;

function searchFireWeightLive() {
    const q = (document.getElementById('fireSearchInput')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#fireTableBody tr.fire-data-row');
    let visibleCount = 0;

    rows.forEach(r => {
        const searchData = r.getAttribute('data-search') || '';
        if (!q || searchData.includes(q)) {
            r.style.display = '';
            visibleCount++;
        } else {
            r.style.display = 'none';
        }
    });

    const noResults = document.getElementById('noResultsSearchRow');
    if (noResults) {
        noResults.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }

    paginateVisibleRows();
}
window.searchFireWeightLive = searchFireWeightLive;

function initPagination() {
    paginateVisibleRows();
}

function paginateVisibleRows() {
    const q = (document.getElementById('fireSearchInput')?.value || '').toLowerCase().trim();
    const allRows = Array.from(document.querySelectorAll('#fireTableBody tr.fire-data-row'));
    const matchedRows = allRows.filter(r => {
        if (!q) return true;
        const searchData = r.getAttribute('data-search') || '';
        return searchData.includes(q);
    });

    const total = matchedRows.length;
    const totalPages = Math.ceil(total / pageSize) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    matchedRows.forEach((r, idx) => {
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        r.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    // Actualizar badges
    const startIdx = total === 0 ? 0 : (currentPage - 1) * pageSize + 1;
    const endIdx = Math.min(currentPage * pageSize, total);

    const elStart = document.getElementById('firePageStart');
    const elEnd = document.getElementById('firePageEnd');
    const elTotal = document.getElementById('firePageTotal');
    if (elStart) elStart.textContent = startIdx;
    if (elEnd) elEnd.textContent = endIdx;
    if (elTotal) elTotal.textContent = total;

    renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
    const container = document.getElementById('firePaginationControls');
    if (!container) return;
    container.innerHTML = '';

    if (totalPages <= 1) return;

    // Botón Prev
    const btnPrev = document.createElement('button');
    btnPrev.className = 'modules-pag-btn';
    btnPrev.innerHTML = '‹';
    btnPrev.disabled = currentPage === 1;
    btnPrev.onclick = () => { if (currentPage > 1) { currentPage--; paginateVisibleRows(); } };
    container.appendChild(btnPrev);

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = `modules-pag-btn ${i === currentPage ? 'active' : ''}`;
        btn.textContent = i;
        btn.onclick = () => { currentPage = i; paginateVisibleRows(); };
        container.appendChild(btn);
    }

    // Botón Next
    const btnNext = document.createElement('button');
    btnNext.className = 'modules-pag-btn';
    btnNext.innerHTML = '›';
    btnNext.disabled = currentPage === totalPages;
    btnNext.onclick = () => { if (currentPage < totalPages) { currentPage++; paginateVisibleRows(); } };
    container.appendChild(btnNext);
}

// =============================================================================
// 5. GESTIÓN DINÁMICA DE MATERIALES POR PESO EN MODALES
// =============================================================================
function addMaterialWeightRow(prefix = 'create', data = {}) {
    const container = document.getElementById(`${prefix}_materials_container`);
    if (!container) return;

    const rowId = 'mat_row_' + Math.random().toString(36).substring(2, 9);
    const card = document.createElement('div');
    card.className = 'fire-material-pill-card';
    card.id = rowId;
    card.style.cssText = 'background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px; margin-bottom: 8px; position: relative;';

    const selectedAct = data.actividad || 'Almacenes - en general';
    const selectedMat = data.material || 'Madera';
    const pesoKg = data.peso_kg || 100;
    const kiMcal = data.ki_mcal_kg || 4.40;
    const kiMj = data.ki_mj_kg || 18.41;
    const ciVal = data.ci || 1.3;
    const cantidad = data.cantidad || 1;
    const desc = data.descripcion || '';

    // Generar opciones de actividades
    const actOptions = FIRE_WEIGHT_ACTIVITIES_CATALOG.map(a => 
        `<option value="${a}" ${a === selectedAct ? 'selected' : ''}>${a}</option>`
    ).join('');

    // Generar opciones de materiales
    const matOptions = FIRE_MATERIALS_CATALOG.map(m => 
        `<option value="${m.name}" data-ki-mcal="${m.ki_mcal}" data-ki-mj="${m.ki_mj}" data-ci="${m.ci}" ${m.name === selectedMat ? 'selected' : ''}>${m.name} (${m.ki_mcal} Mcal/kg)</option>`
    ).join('');

    card.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span class="material-type-badge">Material Combustible</span>
                <span id="${rowId}_qi_display" style="font-family: monospace; font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 1px 6px; border-radius: 4px;">0.0 MJ</span>
            </div>
            <button type="button" class="btn-delete-mat-row" onclick="document.getElementById('${rowId}').remove(); recalcSectorArea('${prefix}');"
                style="background: transparent; border: 0; color: #ef4444; font-size: 14px; cursor: pointer; padding: 0 4px;" title="Quitar material">✕</button>
        </div>

        <div style="display: flex; flex-direction: column; gap: 6px;">
            <div>
                <label style="font-size: 10.5px; font-weight: 700; color: #475569;">Actividad / Uso Normativo:</label>
                <select name="materials_actividad[]" class="custom-form-select" style="height: 32px; font-size: 11.5px;">
                    ${actOptions}
                </select>
            </div>

            <div>
                <label style="font-size: 10.5px; font-weight: 700; color: #475569;">Material Combustible (Ki / Pci):</label>
                <select name="materials_material[]" class="custom-form-select" style="height: 32px; font-size: 11.5px;" onchange="handleMaterialSelectChange('${rowId}', '${prefix}')">
                    ${matOptions}
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                <div>
                    <label style="font-size: 10px; font-weight: 700; color: #475569;">Peso (Pi en kg):</label>
                    <input type="number" step="0.1" min="0.1" name="materials_peso_kg[]" value="${pesoKg}" class="custom-form-input tech-val-mono" style="height: 32px; font-size: 11.5px; padding: 0 6px;" oninput="updateRowQi('${rowId}', '${prefix}')">
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 700; color: #475569;">Ki (Mcal/kg):</label>
                    <input type="number" step="0.01" name="materials_ki_mcal[]" value="${kiMcal}" class="custom-form-input tech-val-mono" style="height: 32px; font-size: 11.5px; padding: 0 6px;" oninput="updateRowQi('${rowId}', '${prefix}')">
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 700; color: #475569;">Coef. Ci:</label>
                    <select name="materials_ci[]" class="custom-form-select" style="height: 32px; font-size: 11.5px; padding: 0 6px;" onchange="updateRowQi('${rowId}', '${prefix}')">
                        <option value="1.0" ${ciVal == 1.0 ? 'selected' : ''}>1.0 (Baja)</option>
                        <option value="1.3" ${ciVal == 1.3 ? 'selected' : ''}>1.3 (Media)</option>
                        <option value="1.6" ${ciVal == 1.6 ? 'selected' : ''}>1.6 (Alta)</option>
                    </select>
                </div>
            </div>

            <div>
                <input type="text" name="materials_descripcion[]" value="${desc}" placeholder="Descripción (ej. Estibas, cajas, tambores...)" class="custom-form-input" style="height: 30px; font-size: 11px;">
            </div>
        </div>
    `;

    container.appendChild(card);
    updateRowQi(rowId, prefix);
}
window.addMaterialWeightRow = addMaterialWeightRow;

function handleMaterialSelectChange(rowId, prefix) {
    const card = document.getElementById(rowId);
    if (!card) return;
    const select = card.querySelector('select[name="materials_material[]"]');
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption) {
        const kiMcal = selectedOption.getAttribute('data-ki-mcal') || 4.00;
        const ci = selectedOption.getAttribute('data-ci') || 1.3;
        const kiInput = card.querySelector('input[name="materials_ki_mcal[]"]');
        const ciSelect = card.querySelector('select[name="materials_ci[]"]');
        if (kiInput) kiInput.value = kiMcal;
        if (ciSelect) ciSelect.value = ci;
    }
    updateRowQi(rowId, prefix);
}
window.handleMaterialSelectChange = handleMaterialSelectChange;

function updateRowQi(rowId, prefix) {
    const card = document.getElementById(rowId);
    if (!card) return;
    const peso = parseFloat(card.querySelector('input[name="materials_peso_kg[]"]')?.value || 0);
    const kiMcal = parseFloat(card.querySelector('input[name="materials_ki_mcal[]"]')?.value || 0);
    const ci = parseFloat(card.querySelector('select[name="materials_ci[]"]')?.value || 1.3);

    // Qi = Pi * Ki (Mcal) * Ci * 4.184 (para MJ)
    const qiMcal = peso * kiMcal * ci;
    const qiMj = qiMcal * 4.184;

    const display = document.getElementById(`${rowId}_qi_display`);
    if (display) {
        display.textContent = `${qiMj.toFixed(1)} MJ (${qiMcal.toFixed(1)} Mcal)`;
    }

    recalcSectorArea(prefix);
}
window.updateRowQi = updateRowQi;

// Recalcular área del sector y Qs ponderada
function recalcSectorArea(prefix = 'create') {
    const largo = parseFloat(document.getElementById(`${prefix}_yi_largo`)?.value || 0);
    const ancho = parseFloat(document.getElementById(`${prefix}_xi_ancho`)?.value || 0);
    const area = largo * ancho;

    const badge = document.getElementById(`${prefix}_calc_area_badge`);
    if (badge) {
        badge.textContent = `${area.toFixed(2)} m²`;
    }
}
window.recalcSectorArea = recalcSectorArea;

// =============================================================================
// 6. GESTIÓN DINÁMICA DE EQUIPOS CONTRA INCENDIO EN MODALES
// =============================================================================
function addFireEquipmentRow(prefix = 'create', tipo = 'EXTINTOR', cantidad = 1, observacion = '') {
    const container = document.getElementById(`${prefix}_fire_equipments_container`);
    if (!container) return;

    const rowId = 'eq_row_' + Math.random().toString(36).substring(2, 9);
    const row = document.createElement('div');
    row.id = rowId;
    row.style.cssText = 'display: grid; grid-template-columns: 120px 55px 1fr 32px 24px; gap: 6px; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px;';

    const options = FIRE_EQUIPMENT_TYPES.map(t => `<option value="${t}" ${t === tipo ? 'selected' : ''}>${t}</option>`).join('');

    row.innerHTML = `
        <select name="fire_equipments_tipo[]" class="custom-form-select" style="height: 32px; font-size: 11.5px; padding: 0 6px;">
            ${options}
        </select>
        <input type="number" min="1" max="99" name="fire_equipments_cantidad[]" value="${cantidad}" class="custom-form-input tech-val-mono" style="height: 32px; font-size: 12px; text-align: center;" placeholder="Cant">
        <input type="text" name="fire_equipments_obs[]" value="${observacion}" placeholder="Obs (ej. PQS 6kg, manómetro OK)" class="custom-form-input" style="height: 32px; font-size: 11.5px;">
        <button type="button" class="btn-eq-photo-action" title="Ver fotografía de la tarjeta de inspección" onclick="viewEquipmentPhoto('${prefix}', '${tipo}')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
        </button>
        <button type="button" class="btn-delete-eq-row" onclick="document.getElementById('${rowId}').remove()" style="background: transparent; border: 0; color: #ef4444; font-size: 15px; cursor: pointer; display: grid; place-items: center;" title="Eliminar equipo">✕</button>
    `;

    container.appendChild(row);
}
window.addFireEquipmentRow = addFireEquipmentRow;

function viewEquipmentPhoto(prefix = 'edit', type = 'Extintor') {
    const allPhotos = window.activeMeasurementAllPhotos || modalPhotos[prefix] || [];
    // Si hay más de 1 foto, la segunda foto corresponde a la tarjeta del extintor tomada en la app.
    // Si solo hay 1 foto general, mostramos esa foto.
    const targetPhoto = allPhotos.length > 1 ? allPhotos[1] : (allPhotos[0] || null);
    if (targetPhoto) {
        openPhotoViewer(targetPhoto, `Tarjeta de Inspección / Extintor — ${type}`);
    } else {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Sin fotografía de tarjeta',
                text: 'No se encontró fotografía adjunta de la tarjeta para este equipo en el registro.',
                confirmButtonText: 'Entendido',
                customClass: {
                    confirmButton: 'metric-swal-btn-primary'
                }
            });
        } else {
            alert('No se encontró fotografía adjunta de la tarjeta para este equipo.');
        }
    }
}
window.viewEquipmentPhoto = viewEquipmentPhoto;

function handlePhotoUploadPreview(input, containerId) {
    const container = document.getElementById(containerId);
    if (!container || !input.files) return;
    container.innerHTML = '';

    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'width: 50px; height: 50px; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1;';
            wrap.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            container.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}
window.handlePhotoUploadPreview = handlePhotoUploadPreview;

// =============================================================================
// 7. APERTURA Y CIERRE DE MODALES
// =============================================================================
function openCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) modal.classList.add('open');
}
window.openCreateMeasurementModal = openCreateMeasurementModal;

function closeCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) modal.classList.remove('open');
}
window.closeCreateMeasurementModal = closeCreateMeasurementModal;

let modalPhotos = {
    create: [],
    edit: []
};
let modalPhotoIndex = {
    create: 0,
    edit: 0
};
let modalMiniMaps = {};
let modalMiniMapMarkers = {};
let isEditUnlocked = false;
let activeEditingMeasurementId = null;

function openViewMeasurementModalById(id) {
    const measurements = window.ALL_MEASUREMENTS_DATA || [];
    const item = measurements.find(m => String(m.id) === String(id));
    if (item) {
        openViewMeasurementModal(item);
    } else {
        console.warn('Sector no encontrado con ID:', id);
    }
}
window.openViewMeasurementModalById = openViewMeasurementModalById;

function openViewMeasurementModal(data) {
    const modal = document.getElementById('editMeasurementModal');
    const form = document.getElementById('editMeasurementForm');
    if (!modal || !form) return;

    activeEditingMeasurementId = data.id || null;
    form.action = `/modulos/${window.MODULE_ID}/carga-fuego-peso/mediciones/${data.id || ''}`;

    // Poblar campos básicos
    document.getElementById('edit_point_number').value = data.num || '';
    document.getElementById('edit_pt_num_disp').textContent = String(data.num || '01').padStart(2, '0');
    document.getElementById('edit_measurement_date').value = data.raw_date || '';
    document.getElementById('edit_measurement_time').value = data.time || '';
    document.getElementById('edit_macroarea').value = data.macroarea || '';
    document.getElementById('edit_sector_name').value = data.sector_name || '';

    // Evaluador
    if (data.staff_id && document.getElementById('edit_staff_id')) {
        document.getElementById('edit_staff_id').value = data.staff_id;
    }
    if (document.getElementById('edit_modal_registered_by')) {
        document.getElementById('edit_modal_registered_by').textContent = data.registered_by || window.REGISTERED_BY_HEADER || '';
    }

    if (data.dimensions) {
        document.getElementById('edit_yi_largo').value = data.dimensions.yi_largo || '';
        document.getElementById('edit_xi_ancho').value = data.dimensions.xi_ancho || '';
        recalcSectorArea('edit');
    }

    // Indicadores Qs y Riesgo
    const qsMj = data.qs_mj_m2 || 0;
    const qsMcal = data.qs_mcal_m2 || 0;
    const risk = data.risk_level || 'Bajo';
    if (document.getElementById('edit_qs_mj_disp')) {
        document.getElementById('edit_qs_mj_disp').textContent = `${Number(qsMj).toFixed(1)} MJ/m²`;
    }
    if (document.getElementById('edit_qs_mcal_disp')) {
        document.getElementById('edit_qs_mcal_disp').textContent = `(${Number(qsMcal).toFixed(1)} Mcal/m²)`;
    }
    if (document.getElementById('edit_risk_summary_badge')) {
        const badge = document.getElementById('edit_risk_summary_badge');
        badge.textContent = `Riesgo ${risk}`;
        badge.className = `fire-risk-badge ${risk === 'Alto' ? 'rose' : (risk === 'Medio' ? 'amber' : 'emerald')}`;
    }

    // Galería Fotográfica (Slider) - Solo foto general del sector, excluyendo tarjeta de extintores
    window.activeMeasurementAllPhotos = (data.images && Array.isArray(data.images) && data.images.length > 0)
        ? [...data.images]
        : ((data.image_paths && Array.isArray(data.image_paths) && data.image_paths.length > 0) ? [...data.image_paths] : (data.image_path ? [data.image_path] : []));

    modalPhotos.edit = [];
    modalPhotoIndex.edit = 0;
    if (data.image_path) {
        modalPhotos.edit = [data.image_path];
    } else if (data.images && Array.isArray(data.images) && data.images.length > 0) {
        modalPhotos.edit = [data.images[0]];
    } else if (data.image_paths && Array.isArray(data.image_paths) && data.image_paths.length > 0) {
        modalPhotos.edit = [data.image_paths[0]];
    }
    renderPhotoSlider('edit');
    syncEditRemainingImages();

    // Coordenadas UTM y GPS MiniMap
    let utmZone = data.utm_zone || '19S';
    let utmEasting = parseFloat(data.utm_easting) || 0;
    let utmNorthing = parseFloat(data.utm_northing) || 0;
    let lat = parseFloat(data.latitude);
    let lng = parseFloat(data.longitude);

    if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) {
        if (utmEasting && utmNorthing) {
            const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
            lat = pos.lat;
            lng = pos.lng;
        }
    } else {
        if (!utmEasting || !utmNorthing) {
            const u = latLngToUtm(lat, lng);
            utmEasting = u.easting;
            utmNorthing = u.northing;
            utmZone = u.zone;
        } else {
            const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
            lat = pos.lat;
            lng = pos.lng;
        }
    }

    const eInput = document.getElementById('edit_utm_easting');
    const nInput = document.getElementById('edit_utm_northing');
    const zInput = document.getElementById('edit_utm_zone');
    const disp = document.getElementById('edit_utm_display');
    const locInput = document.getElementById('edit_location');
    const latInput = document.getElementById('edit_latitude');
    const lngInput = document.getElementById('edit_longitude');

    const formattedUtm = `E: ${utmEasting.toFixed(2)}, N: ${utmNorthing.toFixed(2)}, Z: ${utmZone}`;
    if (eInput) eInput.value = utmEasting.toFixed(2);
    if (nInput) nInput.value = utmNorthing.toFixed(2);
    if (zInput) zInput.value = utmZone;
    if (disp) disp.textContent = formattedUtm;
    if (locInput) locInput.value = formattedUtm;
    if (latInput) latInput.value = isNaN(lat) ? '' : lat.toFixed(7);
    if (lngInput) lngInput.value = isNaN(lng) ? '' : lng.toFixed(7);

    // Observaciones
    document.getElementById('edit_observations').value = data.observations || '';

    // Poblar materiales
    const matContainer = document.getElementById('edit_materials_container');
    if (matContainer) {
        matContainer.innerHTML = '';
        const mats = (data.materials && Array.isArray(data.materials)) ? data.materials : [];
        mats.forEach(mat => addMaterialWeightRow('edit', mat));
    }

    // Poblar equipos
    const eqContainer = document.getElementById('edit_fire_equipments_container');
    if (eqContainer) {
        eqContainer.innerHTML = '';
        const eqs = (data.fire_equipments && Array.isArray(data.fire_equipments)) ? data.fire_equipments : [];
        eqs.forEach(eq => addFireEquipmentRow('edit', eq.tipo, eq.cantidad, eq.observacion));
    }

    // Iniciar SIEMPRE en Modo Consulta (Solo Lectura)
    applyEditModeState(false);

    modal.classList.add('open');
    setTimeout(() => {
        initModalMiniMap('edit', isNaN(lat) ? -16.5034120 : lat, isNaN(lng) ? -68.1324560 : lng);
    }, 150);
}
window.openViewMeasurementModal = openViewMeasurementModal;
window.openEditMeasurementModal = openViewMeasurementModal;

function closeEditMeasurementModal() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.classList.remove('open');
        applyEditModeState(false);
    }
}
window.closeEditMeasurementModal = closeEditMeasurementModal;

function toggleModalEditMode() {
    applyEditModeState(!isEditUnlocked);
}
window.toggleModalEditMode = toggleModalEditMode;

function applyEditModeState(unlocked) {
    isEditUnlocked = unlocked;
    const form = document.getElementById('editMeasurementForm');
    const badge = document.getElementById('modalModeStatusBadge');
    const btn = document.getElementById('btnToggleEditMode');
    const btnText = document.getElementById('btnToggleEditModeText');
    const btnGps = document.getElementById('edit_btn_gps');
    const btnAddPhoto = document.getElementById('edit_btn_add_photos');
    const btnDelPhoto = document.getElementById('edit_btn_delete_photo');
    const btnAddMat = document.getElementById('edit_btn_add_material');
    const btnAddEq = document.getElementById('edit_btn_add_equipment');
    const btnSubmit = document.getElementById('edit_modal_submit_btn');

    if (!form) return;

    if (unlocked) {
        form.classList.remove('modal-view-mode');
        if (badge) {
            badge.className = 'modal-badge-edit';
            badge.textContent = 'Editando';
        }
        if (btn) btn.classList.add('active-editing');
        if (btnText) btnText.textContent = 'Solo Lectura';
        if (btnGps) btnGps.style.display = 'inline-flex';
        if (btnAddPhoto) btnAddPhoto.style.display = 'inline-flex';
        if (btnDelPhoto && (modalPhotos.edit || []).length > 0) btnDelPhoto.style.display = 'inline-flex';
        if (btnAddMat) btnAddMat.style.display = 'inline-flex';
        if (btnAddEq) btnAddEq.style.display = 'inline-flex';
        if (btnSubmit) btnSubmit.style.display = 'inline-flex';

        form.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.id !== 'edit_point_number') el.disabled = false;
        });

        // Mostrar botones de eliminar fila en materiales y equipos
        form.querySelectorAll('.btn-delete-mat-row, .btn-delete-eq-row').forEach(b => b.style.display = 'inline-block');
    } else {
        form.classList.add('modal-view-mode');
        if (badge) {
            badge.className = 'modal-badge-view';
            badge.textContent = 'Solo Lectura';
        }
        if (btn) btn.classList.remove('active-editing');
        if (btnText) btnText.textContent = 'Editar';
        if (btnGps) btnGps.style.display = 'none';
        if (btnAddPhoto) btnAddPhoto.style.display = 'none';
        if (btnDelPhoto) btnDelPhoto.style.display = 'none';
        if (btnAddMat) btnAddMat.style.display = 'none';
        if (btnAddEq) btnAddEq.style.display = 'none';
        if (btnSubmit) btnSubmit.style.display = 'none';

        form.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = true;
        });

        // Ocultar botones de eliminar fila
        form.querySelectorAll('.btn-delete-mat-row, .btn-delete-eq-row').forEach(b => b.style.display = 'none');
    }
}
window.applyEditModeState = applyEditModeState;

// =============================================================================
// VISOR Y CARRUSEL FOTOGRÁFICO SLIDE (IDÉNTICO A ILUMINACIÓN)
// =============================================================================
function renderPhotoSlider(mode) {
    const list = modalPhotos[mode] || [];
    const idx = modalPhotoIndex[mode] || 0;
    const viewport = document.getElementById(`${mode}_slider_viewport`);
    const mainImg = document.getElementById(`${mode}_slider_img`);
    const placeholder = document.getElementById(`${mode}_slider_placeholder`);
    const counter = document.getElementById(`${mode}_slider_counter`);
    const btnPrev = document.getElementById(`${mode}_slider_btn_prev`);
    const btnNext = document.getElementById(`${mode}_slider_btn_next`);
    const thumbsStrip = document.getElementById(`${mode}_slider_thumbs`);
    const countIndicator = document.getElementById(`${mode}_photo_count_indicator`);
    const btnDelPhoto = document.getElementById(`${mode}_btn_delete_photo`);

    if (countIndicator) {
        countIndicator.textContent = `${list.length} foto${list.length === 1 ? '' : 's'}`;
    }

    if (list.length === 0) {
        if (mainImg) mainImg.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
        if (counter) counter.style.display = 'none';
        if (btnPrev) btnPrev.style.display = 'none';
        if (btnNext) btnNext.style.display = 'none';
        if (thumbsStrip) thumbsStrip.style.display = 'none';
        if (btnDelPhoto) btnDelPhoto.style.display = 'none';
        return;
    }

    if (placeholder) placeholder.style.display = 'none';
    if (mainImg) {
        mainImg.src = list[idx];
        mainImg.style.display = 'block';
    }
    if (counter) {
        counter.textContent = `${idx + 1} / ${list.length}`;
        counter.style.display = 'block';
    }
    if (btnPrev) btnPrev.style.display = list.length > 1 ? 'grid' : 'none';
    if (btnNext) btnNext.style.display = list.length > 1 ? 'grid' : 'none';
    if (btnDelPhoto && isEditUnlocked) btnDelPhoto.style.display = 'inline-flex';

    if (thumbsStrip) {
        if (list.length > 1) {
            thumbsStrip.style.display = 'flex';
            thumbsStrip.innerHTML = '';
            list.forEach((url, i) => {
                const item = document.createElement('div');
                item.className = `slider-thumb-item ${i === idx ? 'active' : ''}`;
                item.onclick = () => selectSliderPhoto(mode, i);
                item.innerHTML = `<img src="${url}" alt="thumb">`;
                thumbsStrip.appendChild(item);
            });
        } else {
            thumbsStrip.style.display = 'none';
        }
    }
}
window.renderPhotoSlider = renderPhotoSlider;

function slidePhotoNav(mode, direction) {
    const list = modalPhotos[mode] || [];
    if (list.length <= 1) return;
    let idx = modalPhotoIndex[mode] + direction;
    if (idx < 0) idx = list.length - 1;
    if (idx >= list.length) idx = 0;
    modalPhotoIndex[mode] = idx;
    renderPhotoSlider(mode);
}
window.slidePhotoNav = slidePhotoNav;

function selectSliderPhoto(mode, index) {
    modalPhotoIndex[mode] = index;
    renderPhotoSlider(mode);
}
window.selectSliderPhoto = selectSliderPhoto;

function expandCurrentModalPhoto(mode) {
    const list = modalPhotos[mode] || [];
    const idx = modalPhotoIndex[mode] || 0;
    if (list[idx]) {
        openPhotoViewer(list[idx], `Fotografía del Sector (${idx + 1}/${list.length})`);
    }
}
window.expandCurrentModalPhoto = expandCurrentModalPhoto;

function handleMultipleImagesSelected(input, mode) {
    if (!input.files || input.files.length === 0) return;
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            modalPhotos[mode].push(e.target.result);
            modalPhotoIndex[mode] = modalPhotos[mode].length - 1;
            renderPhotoSlider(mode);
            syncEditRemainingImages();
        };
        reader.readAsDataURL(file);
    });
}
window.handleMultipleImagesSelected = handleMultipleImagesSelected;

function deleteActivePhoto(mode) {
    if (mode === 'edit' && !isEditUnlocked) return;
    const list = modalPhotos[mode] || [];
    const idx = modalPhotoIndex[mode] || 0;
    if (list.length === 0) return;

    list.splice(idx, 1);
    if (modalPhotoIndex[mode] >= list.length) {
        modalPhotoIndex[mode] = Math.max(0, list.length - 1);
    }
    renderPhotoSlider(mode);
    if (mode === 'edit') syncEditRemainingImages();
}
window.deleteActivePhoto = deleteActivePhoto;

function syncEditRemainingImages() {
    const hidden = document.getElementById('edit_existing_images_json');
    if (hidden) {
        hidden.value = JSON.stringify(modalPhotos.edit || []);
    }
}

// =============================================================================
// MINIMAPA LEAFLET PARA COORDENADAS UTM / GPS
// =============================================================================
function initModalMiniMap(prefix, lat, lng) {
    const mapContainer = document.getElementById(`${prefix}_modal_map`) || document.getElementById(`${prefix}ModalMiniMapLeaflet`);
    if (!mapContainer || typeof L === 'undefined') return;
    const mapDivId = mapContainer.id;

    if (modalMiniMaps[prefix]) {
        modalMiniMaps[prefix].remove();
        delete modalMiniMaps[prefix];
    }

    const defaultLat = isNaN(lat) || lat === 0 ? -16.5034120 : lat;
    const defaultLng = isNaN(lng) || lng === 0 ? -68.1324560 : lng;

    const map = L.map(mapDivId, {
        attributionControl: false,
        zoomControl: true
    }).setView([defaultLat, defaultLng], 17);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const iconHtml = `<div style="background: #0284c7; width: 22px; height: 22px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 0 10px rgba(0,0,0,0.5);"></div>`;
    const customIcon = L.divIcon({
        className: 'custom-map-pin',
        html: iconHtml,
        iconSize: [22, 22],
        iconAnchor: [11, 11]
    });

    const marker = L.marker([defaultLat, defaultLng], {
        icon: customIcon,
        draggable: true
    }).addTo(map);

    marker.on('dragend', function(e) {
        if (prefix === 'edit' && !isEditUnlocked) {
            marker.setLatLng([defaultLat, defaultLng]);
            return;
        }
        const pos = e.target.getLatLng();
        syncMapToInputs(prefix, pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        if (prefix === 'edit' && !isEditUnlocked) return;
        marker.setLatLng(e.latlng);
        syncMapToInputs(prefix, e.latlng.lat, e.latlng.lng);
    });

    modalMiniMaps[prefix] = map;
    modalMiniMapMarkers[prefix] = marker;

    setTimeout(() => {
        map.invalidateSize();
    }, 250);
}
window.initModalMiniMap = initModalMiniMap;

function syncMapToInputs(prefix, lat, lng) {
    const u = latLngToUtm(lat, lng);
    const eInput = document.getElementById(`${prefix}_utm_easting`);
    const nInput = document.getElementById(`${prefix}_utm_northing`);
    const zInput = document.getElementById(`${prefix}_utm_zone`);
    const disp = document.getElementById(`${prefix}_utm_display`);
    const locInput = document.getElementById(`${prefix}_location`);
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);

    const formattedUtm = `E: ${u.easting.toFixed(2)}, N: ${u.northing.toFixed(2)}, Z: ${u.zone}`;
    if (eInput) eInput.value = u.easting.toFixed(2);
    if (nInput) nInput.value = u.northing.toFixed(2);
    if (zInput) zInput.value = u.zone;
    if (disp) disp.textContent = formattedUtm;
    if (locInput) locInput.value = formattedUtm;
    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);
}

function syncUtmToMap(prefix) {
    const eInput = document.getElementById(`${prefix}_utm_easting`);
    const nInput = document.getElementById(`${prefix}_utm_northing`);
    const zInput = document.getElementById(`${prefix}_utm_zone`);
    const disp = document.getElementById(`${prefix}_utm_display`);
    const locInput = document.getElementById(`${prefix}_location`);
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);

    const easting = parseFloat(eInput?.value) || 0;
    const northing = parseFloat(nInput?.value) || 0;
    const zone = zInput?.value || '19S';

    if (easting && northing) {
        const pos = utmToLatLng(easting, northing, zone);
        const formattedUtm = `E: ${easting.toFixed(2)}, N: ${northing.toFixed(2)}, Z: ${zone}`;
        if (disp) disp.textContent = formattedUtm;
        if (locInput) locInput.value = formattedUtm;
        if (latInput) latInput.value = pos.lat.toFixed(7);
        if (lngInput) lngInput.value = pos.lng.toFixed(7);

        if (modalMiniMaps[prefix] && modalMiniMapMarkers[prefix]) {
            modalMiniMaps[prefix].setView([pos.lat, pos.lng], 17);
            modalMiniMapMarkers[prefix].setLatLng([pos.lat, pos.lng]);
        }
    }
}
window.syncUtmToMap = syncUtmToMap;

function captureGpsCoordinates(prefix) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    if (!navigator.geolocation) {
        alert('La geolocalización no está soportada por su navegador.');
        return;
    }

    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        syncMapToInputs(prefix, lat, lng);
        if (modalMiniMaps[prefix] && modalMiniMapMarkers[prefix]) {
            modalMiniMaps[prefix].setView([lat, lng], 17);
            modalMiniMapMarkers[prefix].setLatLng([lat, lng]);
        }
    }, err => {
        alert('No se pudo obtener la ubicación GPS: ' + err.message);
    }, { enableHighAccuracy: true });
}
window.captureGpsCoordinates = captureGpsCoordinates;

function utmToLatLng(easting, northing, zone) {
    const zoneNum = parseInt(zone, 10) || 19;
    const isSouth = (zone.toUpperCase().includes('S') || (!zone.toUpperCase().includes('N') && northing > 5000000));
    const k0 = 0.9996;
    const a = 6378137.0;
    const e = 0.081819191;
    const e1sq = 0.006739497;

    const x = easting - 500000.0;
    const y = isSouth ? northing - 10000000.0 : northing;
    const m = y / k0;
    const mu = m / (a * (1 - 0.25 * Math.pow(e, 2) - 0.046875 * Math.pow(e, 4) - 0.01953125 * Math.pow(e, 6)));

    const e1 = (1 - Math.sqrt(1 - Math.pow(e, 2))) / (1 + Math.sqrt(1 - Math.pow(e, 2)));
    const j1 = 1.5 * e1 - 0.84375 * Math.pow(e1, 3);
    const j2 = 1.3125 * Math.pow(e1, 2) - 1.71875 * Math.pow(e1, 4);
    const j3 = 1.572916667 * Math.pow(e1, 3);

    const fp = mu + j1 * Math.sin(2 * mu) + j2 * Math.sin(4 * mu) + j3 * Math.sin(6 * mu);
    const c1 = e1sq * Math.pow(Math.cos(fp), 2);
    const t1 = Math.pow(Math.tan(fp), 2);
    const r1 = a * (1 - Math.pow(e, 2)) / Math.pow(1 - Math.pow(e, 2) * Math.pow(Math.sin(fp), 2), 1.5);
    const n1 = a / Math.sqrt(1 - Math.pow(e, 2) * Math.pow(Math.sin(fp), 2));
    const d = x / (n1 * k0);

    const lat = fp - (n1 * Math.tan(fp) / r1) * (Math.pow(d, 2) / 2 - (5 + 3 * t1 + 10 * c1 - 4 * Math.pow(c1, 2) - 9 * e1sq) * Math.pow(d, 4) / 24);
    const lng = (d - (1 + 2 * t1 + c1) * Math.pow(d, 3) / 6 + (5 - 2 * c1 + 28 * t1 - 3 * Math.pow(c1, 2) + 8 * e1sq + 24 * Math.pow(t1, 2)) * Math.pow(d, 5) / 120) / Math.cos(fp);

    const latDeg = (lat * 180) / Math.PI;
    const lngDeg = ((zoneNum - 1) * 6 - 180 + 3) + (lng * 180) / Math.PI;

    return { lat: latDeg, lng: lngDeg };
}

function latLngToUtm(lat, lng) {
    const zoneNum = Math.floor((lng + 180) / 6) + 1;
    const isSouth = lat < 0;
    const zone = `${zoneNum}${isSouth ? 'S' : 'N'}`;

    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const k0 = 0.9996;
    const e = Math.sqrt(2 * f - Math.pow(f, 2));
    const ePrimeSq = Math.pow(e, 2) / (1 - Math.pow(e, 2));

    const latRad = (lat * Math.PI) / 180.0;
    const lngRad = (lng * Math.PI) / 180.0;
    const lngOrigin = ((zoneNum - 1) * 6 - 180 + 3) * (Math.PI / 180.0);
    const dLng = lngRad - lngOrigin;

    const n = a / Math.sqrt(1 - Math.pow(e, 2) * Math.pow(Math.sin(latRad), 2));
    const t = Math.pow(Math.tan(latRad), 2);
    const c = ePrimeSq * Math.pow(Math.cos(latRad), 2);
    const m = a * ((1 - Math.pow(e, 2)/4 - 3*Math.pow(e, 4)/64 - 5*Math.pow(e, 6)/256) * latRad
            - (3*Math.pow(e, 2)/8 + 3*Math.pow(e, 4)/32 + 45*Math.pow(e, 6)/1024) * Math.sin(2*latRad)
            + (15*Math.pow(e, 4)/256 + 45*Math.pow(e, 6)/1024) * Math.sin(4*latRad)
            - (35*Math.pow(e, 6)/3072) * Math.sin(6*latRad));

    let easting = k0 * n * (dLng * Math.cos(latRad) + Math.pow(dLng, 3) * Math.pow(Math.cos(latRad), 3) * (1 - t + c) / 6.0) + 500000.0;
    let northing = k0 * (m + n * Math.tan(latRad) * (Math.pow(dLng, 2) * Math.pow(Math.cos(latRad), 2) / 2.0 + Math.pow(dLng, 4) * Math.pow(Math.cos(latRad), 4) * (5 - t + 9 * c + 4 * Math.pow(c, 2)) / 24.0));

    if (isSouth) northing += 10000000.0;

    return { easting, northing, zone };
}

function confirmDeleteMeasurement(id, num) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar sector de incendio?',
            text: `Se eliminará el Sector #${num} y todo su inventario de carga de fuego por peso.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'metric-swal-popup',
                confirmButton: 'metric-swal-btn-danger',
                cancelButton: 'metric-swal-btn-cancel'
            }
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteMeasurementForm');
                if (form) {
                    form.action = `/modulos/${window.MODULE_ID}/carga-fuego-peso/mediciones/${id}`;
                    form.submit();
                }
            }
        });
    }
}
window.confirmDeleteMeasurement = confirmDeleteMeasurement;

// Visor de fotos
function openPhotoViewer(src, title = 'Fotografía del Sector') {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const titleEl = document.getElementById('photoViewerTitle');
    if (modal && img) {
        img.src = src;
        if (titleEl) titleEl.textContent = title;
        modal.classList.add('open');
    }
}
window.openPhotoViewer = openPhotoViewer;

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) modal.classList.remove('open');
}
window.closePhotoViewer = closePhotoViewer;

function expandCurrentMapPhoto() {
    const img = document.getElementById('mapModalPhotoImg');
    if (img && img.src) openPhotoViewer(img.src, 'Fotografía del Sector');
}
window.expandCurrentMapPhoto = expandCurrentMapPhoto;

// Modal Exportar
function openExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) modal.classList.add('open');
}
window.openExportModal = openExportModal;

function closeExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) modal.classList.remove('open');
}
window.closeExportModal = closeExportModal;

// =============================================================================
// 8. GENERACIÓN Y DESCARGA DE PLANILLA TÉCNICA EXCEL (.XLSX)
// =============================================================================
async function downloadFireWeightExcelPlanilla() {
    if (typeof ExcelJS === 'undefined') {
        alert('Cargando librería ExcelJS, por favor intenta en unos segundos.');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'METRIC v2 — Pachabol';
    workbook.created = new Date();

    const sheet = workbook.addWorksheet('Carga de Fuego por Peso', {
        pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true, fitToWidth: 1 }
    });

    const header = window.TECHNICAL_HEADER_DATA || {};
    const measurements = window.ALL_MEASUREMENTS_DATA || [];

    // Título Principal
    sheet.mergeCells('A1:J1');
    const titleCell = sheet.getCell('A1');
    titleCell.value = 'ESTUDIO TÉCNICO DE CARGA DE FUEGO PONDERADA POR PESO DE MATERIALES (NB 58005 / NTP 453)';
    titleCell.font = { name: 'Arial', size: 13, bold: true, color: { argb: 'FFFFFFFF' } };
    titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0284C7' } };
    titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
    sheet.getRow(1).height = 28;

    // Encabezado Técnico
    sheet.getCell('A3').value = 'INSTALACIÓN:';
    sheet.getCell('B3').value = header.installationName || 'Planta Industrial';
    sheet.getCell('G3').value = 'FECHA EVALUACIÓN:';
    sheet.getCell('H3').value = `${header.startDateFormatted} - ${header.endDateFormatted}`;

    sheet.getCell('A4').value = 'EQUIPO UTILIZADO:';
    sheet.getCell('B4').value = `${header.equipmentName} (${header.equipmentBrand} ${header.equipmentModel})`;
    sheet.getCell('G4').value = 'SERIE EQUIPO:';
    sheet.getCell('H4').value = header.equipmentSerial;

    ['A3', 'G3', 'A4', 'G4'].forEach(c => {
        sheet.getCell(c).font = { bold: true, color: { argb: 'FF1E293B' } };
    });

    // Encabezados de Tabla
    const headers = [
        'N°', 'Macroárea', 'Sector de Incendio', 'Dimensiones (L x A)', 'Área (m²)',
        'Materiales Combustibles (Pi kg)', 'Ki Ponderado (MJ/kg)', 'Equipos Contra Incendio', 'Qs Ponderada (MJ/m²)', 'Nivel de Riesgo'
    ];

    sheet.getRow(6).values = headers;
    sheet.getRow(6).font = { bold: true, color: { argb: 'FFFFFFFF' } };
    sheet.getRow(6).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } };
    sheet.getRow(6).alignment = { horizontal: 'center', vertical: 'middle' };
    sheet.getRow(6).height = 24;

    // Filas de Datos
    let rowIdx = 7;
    measurements.forEach(m => {
        const row = sheet.getRow(rowIdx);
        const matNames = (m.materials || []).map(mat => `${mat.material} (${mat.peso_kg}kg)`).join(', ');
        const eqText = (m.fire_equipments || []).map(e => `${e.cantidad} ${e.tipo}`).join(', ');

        row.values = [
            m.num,
            m.macroarea,
            m.sector_name,
            `${m.dimensions?.yi_largo || 0}m × ${m.dimensions?.xi_ancho || 0}m`,
            m.dimensions?.area_m2 || 0,
            matNames,
            m.materials?.[0]?.ki_mj_kg || 0,
            eqText,
            m.qs_mj_m2 || 0,
            m.risk_level || 'Bajo'
        ];

        row.alignment = { vertical: 'middle' };
        row.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
        row.getCell(5).alignment = { horizontal: 'right', vertical: 'middle' };
        row.getCell(9).alignment = { horizontal: 'right', vertical: 'middle' };
        row.getCell(10).alignment = { horizontal: 'center', vertical: 'middle' };

        // Color de celda de riesgo
        const riskCell = row.getCell(10);
        if (m.risk_level === 'Alto') {
            riskCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFE4E6' } };
            riskCell.font = { bold: true, color: { argb: 'FFBE123C' } };
        } else if (m.risk_level === 'Medio') {
            riskCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFBEB' } };
            riskCell.font = { bold: true, color: { argb: 'FFB45309' } };
        } else {
            riskCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECFDF5' } };
            riskCell.font = { bold: true, color: { argb: 'FF047857' } };
        }

        rowIdx++;
    });

    // Auto-ajustar ancho de columnas
    sheet.columns.forEach((col, idx) => {
        col.width = [6, 24, 28, 18, 12, 35, 14, 26, 20, 16][idx] || 15;
    });

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Estudio_Carga_Fuego_Peso_${Date.now()}.xlsx`;
    a.click();
    window.URL.revokeObjectURL(url);
}
window.downloadFireWeightExcelPlanilla = downloadFireWeightExcelPlanilla;

// =============================================================================
// 9. LEAFLET MAPS INTEGRATION
// =============================================================================
function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');

    setTimeout(() => {
        if (!allLocationsMapInstance) {
            allLocationsMapInstance = L.map('allLocationsMapLeaflet').setView([-16.5000, -68.1500], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap METRIC'
            }).addTo(allLocationsMapInstance);
        }

        const measurements = window.ALL_MEASUREMENTS_DATA || [];
        const validCoords = [];

        measurements.forEach((m, idx) => {
            if (m.latitude && m.longitude) {
                validCoords.push([m.latitude, m.longitude]);
                const markerColor = m.risk_level === 'Alto' ? '#e11d48' : (m.risk_level === 'Medio' ? '#d97706' : '#059669');

                const circleMarker = L.circleMarker([m.latitude, m.longitude], {
                    radius: 9,
                    fillColor: markerColor,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(allLocationsMapInstance);

                circleMarker.bindPopup(`
                    <div style="font-family: 'Outfit', sans-serif; font-size: 12px; padding: 4px;">
                        <strong style="color: #0f172a; font-size: 13px;">#${m.num} — ${m.sector_name}</strong><br>
                        <span style="color: #64748b;">${m.macroarea}</span><br>
                        <span style="font-weight: 700; color: ${markerColor};">Riesgo: ${m.risk_level} (${m.qs_mj_m2} MJ/m²)</span><br>
                        <span style="font-size: 11px; color: #0284c7;">Evaluado por: ${m.registered_by}</span>
                    </div>
                `);
            }
        });

        if (validCoords.length > 0) {
            allLocationsMapInstance.fitBounds(L.latLngBounds(validCoords), { padding: [40, 40] });
        }
    }, 250);
}
window.openAllLocationsModal = openAllLocationsModal;

function closeAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) modal.classList.remove('open');
}
window.closeAllLocationsModal = closeAllLocationsModal;

function focusPointOnAllLocationsMap(idx) {
    const measurements = window.ALL_MEASUREMENTS_DATA || [];
    const m = measurements[idx];
    if (m && m.latitude && m.longitude && allLocationsMapInstance) {
        allLocationsMapInstance.setView([m.latitude, m.longitude], 17, { animate: true });
    }
}
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;

// =============================================================================
// 10. TABLAS NORMATIVAS MODAL
// =============================================================================
function openFireTablesModal() {
    const modal = document.getElementById('fireTablesModal');
    if (modal) modal.classList.add('open');
}
window.openFireTablesModal = openFireTablesModal;

function closeFireTablesModal() {
    const modal = document.getElementById('fireTablesModal');
    if (modal) modal.classList.remove('open');
}
window.closeFireTablesModal = closeFireTablesModal;

function switchFireTableTab(tabId) {
    document.querySelectorAll('.fire-tables-nav-tabs .fire-tab-nav').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.fire-table-tab-pane').forEach(pane => pane.style.display = 'none');

    const activeBtn = Array.from(document.querySelectorAll('.fire-tables-nav-tabs .fire-tab-nav')).find(b => b.getAttribute('onclick')?.includes(tabId));
    if (activeBtn) activeBtn.classList.add('active');

    const pane = document.getElementById(tabId);
    if (pane) pane.style.display = 'block';
}
window.switchFireTableTab = switchFireTableTab;

function filterNormativeCatalogTable(input) {
    const q = (input.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#normativeCatalogTable tbody tr');
    rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        r.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}
window.filterNormativeCatalogTable = filterNormativeCatalogTable;

// =============================================================================
// 11. REPORTE FOTOGRÁFICO MOSAICO (TAMAÑO CARTA PARA PDF)
// =============================================================================
let currentGridDistribution = '2x3';
let selectedPointIdsForReport = [];

function openPhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (!modal) return;

    if (selectedPointIdsForReport.length === 0) {
        const data = window.ALL_MEASUREMENTS_DATA || [];
        selectedPointIdsForReport = data.map(m => m.id);
    }

    renderAllInteractivePhotoCards();
    updatePhotoSelectionCount();
    modal.classList.add('open');
}
window.openPhotoReportModal = openPhotoReportModal;

function closePhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) modal.classList.remove('open');
}
window.closePhotoReportModal = closePhotoReportModal;

function changeGridDistribution(gridType) {
    currentGridDistribution = gridType;
    document.querySelectorAll('#gridDistSelector .btn-grid-dist').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-grid') === gridType);
    });
    renderPhotoSheetsPreview();
}
window.changeGridDistribution = changeGridDistribution;

function togglePhotoSelection(pointId) {
    const idx = selectedPointIdsForReport.indexOf(pointId);
    if (idx > -1) {
        selectedPointIdsForReport.splice(idx, 1);
    } else {
        selectedPointIdsForReport.push(pointId);
    }
    updatePhotoSelectionCount();
    renderPhotoSheetsPreview();
}
window.togglePhotoSelection = togglePhotoSelection;

function selectAllPoints(select = true) {
    const data = window.ALL_MEASUREMENTS_DATA || [];
    if (select) {
        selectedPointIdsForReport = data.map(m => m.id);
    } else {
        selectedPointIdsForReport = [];
    }
    document.querySelectorAll('.photo-select-checkbox').forEach(cb => {
        cb.checked = select;
    });
    updatePhotoSelectionCount();
    renderPhotoSheetsPreview();
}
window.selectAllPoints = selectAllPoints;

function updatePhotoSelectionCount() {
    const total = (window.ALL_MEASUREMENTS_DATA || []).length;
    const count = selectedPointIdsForReport.length;
    const textEl = document.getElementById('photoSelectionCountText');
    if (textEl) {
        textEl.textContent = `${count} de ${total} seleccionadas`;
    }
}

function switchPhotoReportTab(tab) {
    const btnInteractive = document.getElementById('tabBtnInteractive');
    const btnSheets = document.getElementById('tabBtnSheets');
    const viewInteractive = document.getElementById('photoInteractiveView');
    const viewSheets = document.getElementById('photoSheetsView');

    if (tab === 'interactive') {
        btnInteractive?.classList.add('active');
        btnSheets?.classList.remove('active');
        if (viewInteractive) viewInteractive.style.display = 'block';
        if (viewSheets) viewSheets.style.display = 'none';
    } else {
        btnInteractive?.classList.remove('active');
        btnSheets?.classList.add('active');
        if (viewInteractive) viewInteractive.style.display = 'none';
        if (viewSheets) viewSheets.style.display = 'block';
        renderPhotoSheetsPreview();
    }
}
window.switchPhotoReportTab = switchPhotoReportTab;

function renderAllInteractivePhotoCards() {
    const container = document.getElementById('photoInteractiveGridContainer');
    if (!container) return;
    container.innerHTML = '';

    const measurements = window.ALL_MEASUREMENTS_DATA || [];

    measurements.forEach(m => {
        const isChecked = selectedPointIdsForReport.includes(m.id);
        const card = document.createElement('div');
        card.className = 'photo-card-item';
        card.innerHTML = `
            <div class="photo-card-img-wrap">
                ${m.image_path ? `<img src="${m.image_path}" alt="Foto Sector">` : `
                    <div style="color: #64748b; text-align: center; font-size: 11px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        <div style="margin-top: 4px;">Sin Imagen</div>
                    </div>
                `}
                <label style="position: absolute; top: 8px; right: 8px; background: rgba(15, 23, 42, 0.75); border-radius: 6px; padding: 3px 6px; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                    <input type="checkbox" class="photo-select-checkbox" ${isChecked ? 'checked' : ''} onchange="togglePhotoSelection(${m.id})">
                    <span style="color: #fff; font-size: 10px; font-weight: 700;">PDF</span>
                </label>
            </div>
            <div class="photo-card-info">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-weight: 800; font-size: 12px; color: var(--ink);">#${m.num} — ${m.sector_name}</span>
                    <span style="font-size: 10.5px; font-weight: 800; color: ${m.risk_level === 'Alto' ? '#e11d48' : (m.risk_level === 'Medio' ? '#d97706' : '#059669')};">${m.qs_mj_m2} MJ/m²</span>
                </div>
                <div style="font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    ${m.macroarea} • S = ${m.dimensions?.area_m2 || 0} m²
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function renderPhotoSheetsPreview() {
    const container = document.getElementById('photoSheetsContainer');
    const printArea = document.getElementById('photoReportPrintArea');
    if (!container) return;

    const measurements = (window.ALL_MEASUREMENTS_DATA || []).filter(m => selectedPointIdsForReport.includes(m.id));
    const header = window.TECHNICAL_HEADER_DATA || {};

    let perSheet = 6;
    if (currentGridDistribution === '2x4') perSheet = 8;
    else if (currentGridDistribution === '3x3') perSheet = 9;
    else if (currentGridDistribution === '3x4') perSheet = 12;

    const totalSheets = Math.ceil(measurements.length / perSheet) || 1;
    const pagesIndicator = document.getElementById('photoReportPagesIndicator');
    if (pagesIndicator) pagesIndicator.textContent = `Hojas calculadas: ${totalSheets}`;

    container.innerHTML = '';
    if (printArea) printArea.innerHTML = '';

    for (let s = 0; s < totalSheets; s++) {
        const sheetPoints = measurements.slice(s * perSheet, (s + 1) * perSheet);
        const sheetEl = document.createElement('div');
        sheetEl.className = 'photo-report-sheet';
        sheetEl.style.cssText = 'background: #ffffff; width: 100%; max-width: 900px; margin: 0 auto 24px auto; padding: 24px; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); box-sizing: border-box;';

        sheetEl.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 14px;">
                <div>
                    <h2 style="font-size: 15px; margin: 0; font-weight: 800; color: #0f172a;">REPORTE FOTOGRÁFICO DE CARGA DE FUEGO POR PESO (NB 58005)</h2>
                    <span style="font-size: 11px; color: #64748b;">${header.installationName || 'Instalación Industrial'} • Hoja ${s + 1} de ${totalSheets}</span>
                </div>
                <div style="font-size: 11px; font-weight: 700; color: #0284c7;">
                    METRIC v2
                </div>
            </div>
            <div style="display: grid; grid-template-columns: ${currentGridDistribution.startsWith('2') ? '1fr 1fr' : '1fr 1fr 1fr'}; gap: 10px;">
                ${sheetPoints.map(p => `
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; font-size: 10.5px;">
                        <div style="height: 120px; background: #0f172a; display: grid; place-items: center;">
                            ${p.image_path ? `<img src="${p.image_path}" style="max-width: 100%; max-height: 100%; object-fit: cover;">` : `<span style="color: #64748b; font-size: 10px;">Sin fotografía</span>`}
                        </div>
                        <div style="padding: 6px; background: #f8fafc;">
                            <div style="font-weight: 800; color: #0f172a;">#${p.num} — ${p.sector_name}</div>
                            <div style="color: #64748b;">${p.macroarea} (S=${p.dimensions?.area_m2 || 0}m²)</div>
                            <div style="font-weight: 700; color: #0284c7;">Qs = ${p.qs_mj_m2} MJ/m² (${p.risk_level})</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        container.appendChild(sheetEl);
        if (printArea) {
            printArea.appendChild(sheetEl.cloneNode(true));
        }
    }
}

function printPhotoReport() {
    renderPhotoSheetsPreview();
    window.print();
}
window.printPhotoReport = printPhotoReport;

function savePhotoReportSettingsToServer(showToast = false) {
    if (showToast && typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Configuración guardada',
            text: 'Se guardó la selección y distribución del reporte fotográfico.',
            timer: 1600,
            showConfirmButton: false
        });
    }
}
window.savePhotoReportSettingsToServer = savePhotoReportSettingsToServer;
