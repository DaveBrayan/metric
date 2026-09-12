/**
 * METRIC_V2 — Executive Multi-Enterprise Dashboard Controller (Rendered by Vite)
 * Real-time Company Switcher, Physical Environmental Readings Recalculation & Matrix Filter
 */

// Enterprise Profiles with dynamic physical telemetry readings
const COMPANY_TELEMETRY_DATA = {
    all: {
        badge: 'ESTADO AMBIENTAL ÓPTIMO: 98.6% CONFORMIDAD LMP (LEY 1333)',
        title: 'Centro de Mando & Telemetría Ambiental Multi-Empresa',
        desc: 'Supervisión centralizada de 24 empresas clientes y 142 estaciones de muestreo.',
        dosimetria: '78.4',
        ruido_ambiental: '58.2',
        agua: '7.35',
        opacidad: '8.5%',
        particulas: '42.0',
    },
    msc: {
        badge: 'MINERA SAN CRISTÓBAL: 99.4% CONFORME — DISTRITO POTOSÍ',
        title: 'Monitoreo de Relaves Mineros, Drenaje & Polvo 24h',
        desc: 'Telemetría de 8 proyectos en mina, control de efluentes ácidos y estaciones Hi-Vol perimetrales.',
        dosimetria: '82.4',
        ruido_ambiental: '61.5',
        agua: '7.80',
        opacidad: '6.2%',
        particulas: '54.0',
    },
    cbn: {
        badge: 'CERVECERÍA BOLIVIANA NACIONAL: 100% CUMPLIMIENTO — SANTA CRUZ',
        title: 'Control de Efluentes Líquidos & Ruido Ocupacional',
        desc: 'Supervisión continua en planta cervecera, control biológico de efluentes y dosimetría de envasado.',
        dosimetria: '76.0',
        ruido_ambiental: '54.0',
        agua: '7.10',
        opacidad: '4.8%',
        particulas: '28.5',
    },
    soboce: {
        badge: 'SOBOCE CEMENTO: 98.1% CONFORME — PLANTA VIACHA / LA PAZ',
        title: 'Control de Emisiones en Hornos Clinker & Partículas Totales',
        desc: 'Monitoreo de opacidad óptica de chimeneas industriales y material particulado suspendido PM10.',
        dosimetria: '81.2',
        ruido_ambiental: '64.0',
        agua: '7.45',
        opacidad: '11.4%',
        particulas: '62.0',
    },
    ypfb: {
        badge: 'YPFB TRANSPORTE: 97.5% ESTABLE — RED DE GASODUCTOS',
        title: 'Telemetría de Gasoductos & Estaciones de Bombeo',
        desc: 'Supervisión acústica y ambiental en derechos de vía, estaciones de compresión y cruces de río.',
        dosimetria: '74.5',
        ruido_ambiental: '52.0',
        agua: '6.95',
        opacidad: '5.0%',
        particulas: '34.0',
    },
    pil: {
        badge: 'PIL ANDINA S.A.: 96.8% EN REGLA — COCHABAMBA',
        title: 'Control de Calidad de Efluentes & Cadena de Frío Industrial',
        desc: 'Supervisión en plantas de procesamiento lácteo y plantas de tratamiento de aguas residuales PTAR.',
        dosimetria: '72.0',
        ruido_ambiental: '48.5',
        agua: '6.85',
        opacidad: '7.2%',
        particulas: '22.0',
    },
};

/**
 * Handle Company Selector Event
 * @param {string} companyKey 
 */
window.handleCompanyFilterChange = function(companyKey) {
    const data = COMPANY_TELEMETRY_DATA[companyKey] || COMPANY_TELEMETRY_DATA.all;

    // 1. Update Hero texts
    const badgeEl = document.getElementById('heroHealthBadge');
    const titleEl = document.getElementById('heroMainTitle');
    const descEl = document.getElementById('heroSubDesc');

    if (badgeEl) badgeEl.textContent = data.badge;
    if (titleEl) titleEl.innerHTML = `<span>${data.title}</span>`;
    if (descEl) descEl.textContent = data.desc;

    // 2. Update Physical Module readings
    updateModuleReading('dosimetria', data.dosimetria);
    updateModuleReading('ruido_ambiental', data.ruido_ambiental);
    updateModuleReading('agua', data.agua);
    updateModuleReading('opacidad', data.opacidad);
    updateModuleReading('particulas', data.particulas);

    // 3. Filter Table Rows
    filterTableByCompany(companyKey);

    // 4. Trigger Chart Pulse
    triggerChartPulse();

    // 5. Toast Feedback
    if (typeof window.triggerToast === 'function') {
        const select = document.getElementById('dashboardCompanyFilterSelect');
        const text = select ? select.options[select.selectedIndex].text : 'Empresa';
        window.triggerToast('Contexto activo: ' + text, '🏢');
    }
};

/**
 * Update single module value
 */
function updateModuleReading(key, val) {
    const el = document.getElementById(`modVal-${key}`);
    if (el) {
        el.style.opacity = '0.3';
        el.style.transform = 'scale(0.9)';
        setTimeout(() => {
            el.textContent = val;
            el.style.opacity = '1';
            el.style.transform = 'scale(1)';
            el.style.transition = 'all 0.25s ease';
        }, 150);
    }
}

/**
 * Filter Table by Company Key
 */
function filterTableByCompany(companyKey) {
    const rows = document.querySelectorAll('#dashboardMasterMatrixTable tbody tr');
    let visible = 0;

    rows.forEach(row => {
        const key = row.getAttribute('data-company-key');
        if (companyKey === 'all' || key === companyKey) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    const heading = document.getElementById('projectsTableHeading');
    if (heading) {
        heading.textContent = `Proyectos Industriales en Monitoreo (${visible})`;
    }
}

/**
 * Trigger Chart Pulse
 */
function triggerChartPulse() {
    const svg = document.getElementById('dashboardTelemetrySvg');
    if (svg) {
        svg.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
        svg.style.opacity = '0.3';
        svg.style.transform = 'scaleY(0.96)';
        setTimeout(() => {
            svg.style.opacity = '1';
            svg.style.transform = 'scaleY(1)';
        }, 220);
    }
}

/**
 * Chart Time Filter Switcher
 */
window.handleChartTimeFilter = function(range = '7D', btn = null) {
    if (btn) {
        document.querySelectorAll('.chart-time-pills-group .time-pill').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
    }
    triggerChartPulse();
    if (typeof window.triggerToast === 'function') {
        window.triggerToast('Curva de telemetría: Rango ' + range, '📊');
    }
};

/**
 * Date Picker Modal
 */
window.handleDateRangePicker = function() {
    if (typeof window.triggerToast === 'function') {
        window.triggerToast('Período de monitoreo: Mayo 2024 (Ciclo Activo)', '📅');
    }
};

/**
 * Quick Action Handler
 */
window.dispatchQuickAction = function(title) {
    if (typeof window.triggerToast === 'function') {
        window.triggerToast('Procesando: ' + title, '⚡');
    }
};
