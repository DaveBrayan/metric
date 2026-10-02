/**
 * CARGA DE FUEGO POR ACTIVIDAD — CLIENT-SIDE JAVASCRIPT CONTROLLER
 * Metric v2 Pachabol — NB 58005 / NTP 453
 */

// =============================================================================
// 1. CATÁLOGO NORMATIVO DE ACTIVIDADES (NB 58005)
// =============================================================================
const ALL_FIRE_NORMATIVE_ACTIVITIES = [
    "Abonos químicos", "Aceites comestibles, expedición", "Aceites comestibles", "Aceites: mineral, vegetal y animal",
    "Acero", "Acetileno, llenado de botellas", "Ácido carbónico", "Ácidos inorgánicos", "Acumuladores", "Acumuladores, expedición",
    "Agua oxigenada", "Agujas de acero", "Alambre metálico aislado", "Alambre metálico no aislado", "Albergues", "Albergues juveniles",
    "Alfarería", "Algodón en rama, guata", "Algodón, almacén de", "Alimentación, embalaje", "Alimentación, expedición",
    "Alimentación, materias primas", "Alimentación, platos precocinados", "Almacenes de talleres, etc.", "Almidón", "Alquitrán",
    "Alquitrán, productos de", "Altos hornos", "Aluminio, producción de", "Aluminio, trabajo de", "Antigüedades, venta de",
    "Aparatos de radio", "Aparatos de radio, venta", "Aparatos de televisión", "Aparatos domésticos", "Aparatos eléctricos",
    "Aparatos eléctricos, reparación", "Aparatos electrónicos", "Aparatos electrónicos, reparación", "Aparatos fotográficos",
    "Aparatos mecánicos", "Aparatos pequeños, construcción de", "Aparatos sanitarios, taller", "Aparatos, talleres de reparación",
    "Aparatos, expedición de", "Aparatos, prueba de", "Aparcamientos, edificios de", "Apartamentos", "Apósitos, fabricación de artículos",
    "Archivos", "Arena", "Armarios frigoríficos", "Armas", "Artículos de metal", "Artículos de yeso",
    "Artículos metal fundidos por inyección", "Artículos metálicos, soldadura ligera", "Artículos metálicos, amolado",
    "Artículos metálicos, barnizado", "Artículos metálicos, cerrajería", "Artículos metálicos, chatarras", "Artículos metálicos, dorado",
    "Artículos metálicos, estampado", "Artículos metálicos, forjado", "Artículos metálicos, fresado", "Artículos metálicos, fundición",
    "Artículos metálicos, grabación", "Artículos metálicos, soldadura", "Artículos pirotécnicos", "Aserraderos",
    "Asfalto (bidones, bloques)", "Asfalto, manipulación de", "Automóviles, almacén de accesorios", "Automóviles, garajes y aparcamientos",
    "Automóviles, guarnición", "Automóviles, montaje", "Automóviles, pintura", "Automóviles, reparación", "Automóviles, venta de accesorios",
    "Aviones", "Aviones, hangares", "Azúcar", "Azúcar, productos de", "Azufre", "Balanzas", "Bancos, oficinas y sucursales de",
    "Barcos de madera", "Barcos de plástico", "Barcos metálicos", "Barnices", "Barnices a la cera", "Barnices, expedición",
    "Barnizado", "Barnizado de muebles", "Barnizado de papel", "Bebidas alcohólicas", "Bebidas sin alcohol",
    "Bebidas sin alcohol, expedición de", "Bibliotecas", "Bicicletas", "Bodegas (vinos)", "Bramante", "Bramante, almacén de",
    "Buhardillas habitables", "Cables", "Cacao, productos de", "Café crudo, sin refinar", "Café, extracto", "Café, tostadero",
    "Cajas de madera", "Cajas fuertes", "Calderas, edificios de", "Calefacciones", "Calefacciones centrales", "Calzado",
    "Calzado, accesorios de", "Calzados, expedición", "Calzados, venta", "Cantinas", "Caramelos", "Caramelos, embalaje",
    "Carbón de coke", "Carnicerías, venta", "Carretería, artículos de", "Carrocerías de automóvil", "Cartón", "Cartón embreado",
    "Cartón ondulado", "Cartón piedra", "Cartonaje", "Cartonaje, expedición de", "Caucho", "Caucho, artículos de",
    "Caucho, venta de artículos de", "Celuloide", "Cemento", "Central de calefacción a distancia", "Centrales hidráulicas",
    "Centrales hidroeléctricas", "Centrales térmicas", "Cepillos y brochas", "Cera", "Cera, artículos de", "Cera, venta de artículos de",
    "Cerámica, artículos de", "Cerillas", "Cerrajerías", "Cervecerías", "Cestería", "Cestería, venta de artículos de",
    "Chapa, artículos de", "Chapa, embalaje de artículos", "Chatarrería", "Chocolate", "Chocolate, embalaje",
    "Chocolate, fabricación, sala de moldes", "Cines", "Cochecitos de niño", "Colchones no sintéticos.",
    "Colores y barnices, manufacturas de", "Colores y barnices, mezclas", "Colores y barnices, venta",
    "Colores con diluyentes combustibles", "Confiterías", "Congelados", "Conservas", "Corcho", "Corcho, artículos de",
    "Cordelerías, enusa", "Cordelerías, venta", "Correas", "Cortinas en rollo", "Cosméticos", "Crin, cerda de",
    "Cristalerías", "Cuero", "Cuero sintético, recorte de artículos de", "Cuero sintético", "Cuero sintético, artículos de",
    "Cuero, artículos de", "Cuero, recortes de artículos de", "Cuero, venta de artículos de", "Deportes, venta de artículos de",
    "Depósitos de hidrocarburos", "Depósitos de mercancías incombustibles - en cajas de madera",
    "Depósitos de mercancías incombustibles - en cajas de plástico", "Depósitos de mercancías incombustibles - en estanterías de madera",
    "Depósitos de mercancías incombustibles - en estanterías metálicas", "Depósitos de mercancías incombustibles - en casilleros de madera",
    "Depósitos de mercancías incombustibles - en paletas de madera", "Diluyentes", "Discos", "Droguerías", "Edificios frigoríficos",
    "Electricidad, almacén de materiales de", "Electricidad, taller de", "Embalaje de material impreso",
    "Embalaje de mercancías combustibles", "Embalaje de mercancías incombustibles", "Embalaje de productos alimenticios",
    "Embalaje de textiles", "Emisoras de radio", "Encuadernación", "Escobas", "Escorias", "Escuelas y colegios",
    "Esculturas de piedra", "Especias", "Espumas sintéticas", "Espumas sintéticas, artículos de",
    "Estampación de productos sintéticos, cuero, etc.", "Estampado de materias sintéticas", "Estampado de metales",
    "Estilográficas", "Estudio de televisión", "Estufas de gas", "Expedición de artículos sintéticos",
    "Expedición de artículos de cristal", "Expedición de artículos de hojalata", "Expedición de artículos impresos",
    "Expedición de bebidas", "Expedición de cartonaje", "Expedición de ceras y barnices", "Expedición de muebles",
    "Expedición de pequeños artículos de madera", "Expedición de productos alimenticios", "Expedición de textiles",
    "Exposición de automóviles", "Exposición de cuadros", "Exposición de máquinas", "Exposición de muebles",
    "Farmacias (almacenes incluidos)", "Féretros de madera", "Fibras de coco", "Fieltro", "Fieltro, artículos de",
    "Flores artificiales", "Flores, venta de", "Fontanería", "Forraje", "Fósforo", "Fotocopias, talleres",
    "Fotografía, laboratorios", "Fotografía, películas", "Fotografía, talleres", "Fotografía, tienda", "Fraguas",
    "Fundición de metales", "Funiculares", "Galvanoplastia", "Gasolineras", "Grandes almacenes", "Granos", "Grasas",
    "Grasas comestibles", "Grasas comestibles, expedición", "Guantes", "Guardarropa, armarios de madera",
    "Guardarropa, armarios metálicos", "Harina en sacos", "Harina, fábrica o comercio sin almacén", "Heladería",
    "Heno, balas de", "Herramientas", "Hidrógeno", "Hilados, cardados", "Hilados, encanillado-bobinado",
    "Hilados, hilatura", "Hilados, productos de hilo", "Hilados, productos de lana", "Hilados, torcido", "Hipermercados",
    "Hogares para ancianos", "Hogares para niños", "Hojalaterías", "Hormigón, artículos de", "Hornos", "Hospitales",
    "Hoteles, habitaciones", "Hoteles, vestíbulos, restaurantes, salas", "Hule", "Hule, artículos de", "Iglesias",
    "Imprentas, almacén", "Imprentas, embalaje", "Imprentas, expedición", "Imprentas, salas de máquinas",
    "Imprentas, taller tipográfico", "Incineración de basuras", "Instaladores electricistas", "Instaladores, talleres",
    "Instrumentos de música", "Instrumentos de óptica", "Internados, pensionados", "Jabón", "Jardines de infancia",
    "Joyas, fabricación", "Joyas, venta", "Juguetes", "Laboratorios bacteriológicos", "Laboratorios de física",
    "Laboratorios fotográficos", "Laboratorios metalúrgicos", "Laboratorios odontológicos", "Laboratorios químicos",
    "Láminas de hojalata", "Lámparas de incandescencia", "Lana de madera", "Lapiceros", "Lavadoras", "Lavanderías",
    "Leche condensada", "Leche en polvo", "Legumbres frescas, venta", "Legumbres secas", "Leña", "Levadura", "Librerías",
    "Licores", "Licores, venta", "Limpieza química", "Linóleo", "Locales de desechos (diversas mercancías)", "Lúpulo",
    "Madera en troncos", "Madera, artículos de, barnizado", "Madera, artículos de, carpintería", "Madera, artículos de, ebanistería",
    "Madera, artículos de, expedición", "Madera, artículos de, impregnación", "Madera, artículos de, marquetería",
    "Madera, artículos de, pulimentado", "Madera, artículos de, secado", "Madera, artículos de, serrado", "Madera, artículos de, tallado",
    "Madera, artículos de, torneado", "Madera, artículos de, troquelado", "Madera, mezclada o variada", "Madera, restos de",
    "Madera, vigas y tablas", "Madera, virutas", "Malta", "Mantequilla", "Máquinas", "Máquinas de coser", "Máquinas de oficina",
    "Marcos", "Mármol, artículos de", "Mataderos", "Material de oficina", "Materiales de construcción, almacén",
    "Materiales usados, tratamiento", "Materiales sintéticos", "Materias sintéticas inyectadas", "Materias sintéticas, artículos de",
    "Materias sintéticas, estampado", "Materias sintéticas, soldadura de piezas", "Materias sintéticas, expedición",
    "Mecánica de precisión, taller", "Médica, consulta", "Medicamentos, embalaje", "Medicamentos, venta", "Melaza",
    "Mercería, venta", "Mermelada", "Metales preciosos", "Metales, manufacturas en general", "Metálicas, grandes construcciones",
    "Minerales", "Mostaza", "Motocicletas", "Motores eléctricos", "Muebles de acero", "Muebles de madera",
    "Muebles de madera, barnizado", "Muebles, carpintería", "Muebles, tapizado sin espuma sintética", "Muebles, venta",
    "Muelles de carga con mercancías", "Municiones", "Museos", "Música, tienda de", "Negro de humo, en sacos",
    "Neumáticos", "Neumáticos de automóviles", "Nitrocelulosa", "Oficinas comerciales", "Oficinas postales", "Oficinas técnicas",
    "Orfebrería", "Oxígeno", "Paja prensada", "Paja, artículos de", "Paja, embalajes de", "Paletas de madera", "Palillos",
    "Panaderías industriales", "Panaderías, almacenes", "Panaderías, laboratorios y hornos", "Paneles de corcho",
    "Paneles de madera aglomerada", "Panel de madera aglomerada contrachapada", "Papel", "Papel, apresto", "Papel, desechos prensados",
    "Papel, tratamiento de la madera y materias celulósicas", "Papel, tratamiento-fabricación", "Papel, viejo o granel",
    "Papelería", "Papelería, venta", "Paraguas", "Paraguas, venta", "Parquets", "Pastas alimenticias",
    "Pastas alimenticias, expedición", "Pegamentos combustibles", "Pegamentos incombustibles", "Peletería, productos de",
    "Peletería, venta", "Películas, copias", "Películas, talleres de", "Perfumería, artículos de", "Perfumería, venta de artículos de",
    "Persianas, fabricación de", "Piedras artificiales", "Piedras de afilar", "Piedras preciosas, tallado",
    "Piedras refractarias, artículos de", "Pieles, almacén", "Pilas secas", "Pinceles", "Placas de fibras blandas",
    "Placas de resina sintética", "Planeadores", "Porcelana", "Proceso de datos, sala de ordenador", "Productos de amianto",
    "Productos de carnicería", "Productos de lavado (lejía)", "Producto de lavado (lejía materia prima)",
    "Productos de reparación de calzado", "Productos farmacéuticos", "Productos lácteos",
    "Productos laminados salvo chapa y alambre", "Productos químicos combustibles", "Puertas de madera", "Puertas plásticas",
    "Quesos", "Quioscos de periódicos", "Radio, estudio de", "Radiología, gabinete de", "Refinerías de petróleo", "Refrigeradores",
    "Rejilla, asientos y respaldos", "Relojes", "Relojes, reparación de", "Relojes, venta", "Resinas naturales",
    "Resinas sintéticas", "Resinas sintéticas, placas de", "Restaurantes", "Revestimientos de suelos combustibles",
    "Revestimientos de suelos combustibles. Venta", "Rodamientos o cojinetes de bolas", "Sacos de papel", "Sacos de yute",
    "Sacos de plástico", "Salas de juego", "Salinas, productos de", "Servicios de mesa", "Silos", "Skies (Esquíes)",
    "Sombrererías", "Sosa", "Sótanos, bodegas de casas residenciales", "Tabaco en bruto", "Tabacos, artículos de",
    "Tabacos, venta de artículos", "Talco", "Tallado de piedra", "Talleres de enchapado", "Talleres de guarnicionería",
    "Talleres de pintura", "Talleres de reparación", "Talleres eléctricos", "Talleres mecánicos", "Tapicerías",
    "Tapicerías, artículos de", "Tapices", "Tapices, tintura", "Tapices, venta", "Teatros", "Teatros, bastidores",
    "Tejares, cocción", "Tejares, hornos de secado y estanterías de madera", "Tejares, hornos de secado y estanterías metálicas",
    "Tejares, prensado", "Tejares, preparación de arcilla", "Tejares, secadero, estanterías de madera",
    "Tejares, secadero, estanterías metálicas", "Tejidos de rafia", "Tejidos en general, almacén", "Tejidos sintéticos",
    "Tejidos cáñamo, yute, lino", "Tejidos, depósito de balas de algodón", "Tejidos, seda artificial", "Teléfonos",
    "Teléfonos, centrales de", "Televisión, estudios de", "Textiles", "Textiles, apresto", "Textiles, artículos de",
    "Textiles, bajos de prendas", "Textiles, blanqueado", "Textiles, bordado", "Textiles, calandrado", "Textiles, confección",
    "Textiles, corte", "Textiles, de lino", "Textiles, de yute", "Textiles, embalaje", "Textiles, encajes", "Textiles, estampado",
    "Textiles, expedición", "Textiles, forros", "Textiles, lencería", "Textiles, mantas", "Textiles, prendas de vestir",
    "Textiles, preparación", "Textiles, ropa de cama", "Textiles, tejidos (fabricación)", "Textiles, teñido",
    "Textiles, tricotado", "Textiles, venta", "Tintas", "Tintas de imprenta", "Tintorerías", "Tocadiscos", "Toldos o lonas",
    "Toneles de madera", "Toneles de plástico", "Torneado de piezas de cobre/bronce", "Tractores", "Trajes", "Trajes, venta",
    "Transformadores", "Transformadores, bobinado", "Transformadores, estación de", "Tubos fluorescentes", "Turba, productos de",
    "Vagones, fabricación de", "Vehículos", "Velas de cera", "Venta por correspondencia, empresas de", "Ventanas de madera",
    "Ventanas de plástico", "Vidrio", "Vidrio, plano, fábrica de", "Vidrio, artículos de", "Vidrio, expedición",
    "Vidrio, talleres de soplado", "Vidrio, tintura de", "Vidrio, tratamiento de", "Vidrio, venta de artículos de",
    "Vinagre, producción de", "Vulcanización", "Yeso", "Zulaque de vidrieros", "Zumos de fruta"
];

const FIRE_ACTIVITIES_CATALOG = ALL_FIRE_NORMATIVE_ACTIVITIES.map(name => ({
    name: name,
    qsi_mj: 400,
    qsi_mcal: 96,
    ci: 1.0,
    type: "Producción"
}));

const FIRE_EQUIPMENT_TYPES = [
    "EXTINTOR",
    "PULSADOR",
    "ALARMA",
    "HIDRANTE",
    "BOCA DE INCENDIO"
];

// =============================================================================
// 2. ESTADO GLOBAL Y PAGINACIÓN
// =============================================================================
let currentPage = 1;
const pageSize = 10;
let filteredMeasurements = [];
let allLocationsMapInstance = null;
let singlePointMapInstance = null;
let currentViewingPhotoUrl = null;

document.addEventListener('DOMContentLoaded', function () {
    filteredMeasurements = [...(window.ALL_MEASUREMENTS_DATA || [])];
    renderFireTableRows();
    renderFirePagination();

    // Inicializar fila por defecto en modal de creación si está vacío
    if (document.getElementById('create_activities_container')) {
        addActivityRow('create');
        addFireEquipmentRow('create', 'EXTINTOR', 2, 'PQS 6kg Tipo ABC');
    }
});

// =============================================================================
// 3. RENDERIZADO DE TABLA & PAGINACIÓN CLIENT-SIDE
// =============================================================================
function renderFireTableRows() {
    const tableBody = document.getElementById('fireTableBody');
    const emptyRow = document.getElementById('emptyTableRow');
    const noResultsRow = document.getElementById('noResultsSearchRow');

    const total = filteredMeasurements.length;
    const startIdx = (currentPage - 1) * pageSize;
    const endIdx = Math.min(startIdx + pageSize, total);

    const rows = document.querySelectorAll('#fireTableBody tr.fire-data-row');
    rows.forEach(r => r.remove());

    if (total === 0) {
        if (emptyRow) emptyRow.style.display = 'none';
        if (noResultsRow) noResultsRow.style.display = '';
        return;
    }

    if (noResultsRow) noResultsRow.style.display = 'none';
    if (emptyRow) emptyRow.style.display = 'none';

    const pageItems = filteredMeasurements.slice(startIdx, endIdx);

    pageItems.forEach((m) => {
        const tr = document.createElement('tr');
        tr.className = 'fire-data-row';
        tr.setAttribute('data-risk', (m.risk_level || 'bajo').toLowerCase());

        // Actividades Registradas (Solo Conteo)
        const actCount = (m.activities || []).length;
        const actLabel = actCount === 1 ? 'actividad registrada' : 'actividades registradas';
        const actHtml = `
            <span class="fire-activity-count-badge" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 20px; font-size: 12px; font-weight: 700; color: #be123c;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                <span>${actCount} ${actLabel}</span>
            </span>`;

        // Equipos contra incendio
        let eqHtml = '';
        (m.fire_equipments || []).forEach(eq => {
            const eqType = (eq.tipo || 'extintor').toLowerCase();
            let iconSvg = '<path d="M15 6v14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V6"/><path d="M7 6h8"/><path d="M11 2v4"/><path d="M14 4h4"/>';
            if (eqType.includes('pulsador')) {
                iconSvg = '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="12" cy="12" r="3"/>';
            } else if (eqType.includes('alarma')) {
                iconSvg = '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>';
            } else if (eqType.includes('hidrante') || eqType.includes('boca')) {
                iconSvg = '<path d="M12 2v20"/><path d="M17 5H7"/><path d="M5 10h14"/><path d="M9 14h6"/>';
            }

            eqHtml += `
                <span class="fire-eq-badge ${eqType}" title="${eq.observacion || ''}">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">${iconSvg}</svg>
                    <span>${eq.cantidad || 1} ${eq.tipo}</span>
                </span>`;
        });

        // Riesgo badge
        const riskLevel = m.risk_level || 'Bajo';
        const riskTheme = m.risk_theme || (riskLevel === 'Bajo' ? 'emerald' : (riskLevel === 'Medio' ? 'amber' : 'rose'));
        const qsMj = parseFloat(m.qs_mj_m2 || 0).toFixed(1);
        const qsMcal = parseFloat(m.qs_mcal_m2 || 0).toFixed(1);

        tr.innerHTML = `
            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                ${m.num || '01'}
            </td>
            <td>
                <div style="display: flex; flex-direction: column; gap: 3px;">
                    <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">${m.date || ''}</span>
                    <span class="table-time-pill" title="Hora de evaluación">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span>${m.time || '—'}</span>
                    </span>
                </div>
            </td>
            <td>
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <span style="font-weight: 800; color: var(--ink); font-size: 13px;">${m.sector_name || 'Sector'}</span>
                    <span style="font-size: 11.5px; color: #64748b; font-weight: 500;" title="${m.macroarea || ''}">${m.macroarea || ''}</span>
                </div>
            </td>
            <td>
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <span style="font-family: monospace; font-size: 12px; font-weight: 700; color: #0f172a;">
                        ${(m.dimensions?.yi_largo || 0).toFixed(2)}m × ${(m.dimensions?.xi_ancho || 0).toFixed(2)}m
                    </span>
                    <span style="font-size: 11.5px; font-weight: 800; color: #0284c7;">
                        S = ${(m.dimensions?.area_m2 || 0).toFixed(2)} m²
                    </span>
                </div>
            </td>
            <td>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    ${actHtml || '<span style="color:#94a3b8;font-size:11px;">Sin actividades</span>'}
                </div>
            </td>
            <td>
                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                    ${eqHtml || '<span style="color:#94a3b8;font-size:11px;">Sin equipos</span>'}
                </div>
            </td>
            <td>
                <div style="display: flex; flex-direction: column; gap: 3px;">
                    <span class="fire-risk-badge ${riskTheme}" title="Riesgo: ${riskLevel}">
                        <span>Riesgo ${riskLevel}</span>
                    </span>
                    <span class="fire-qs-text">${qsMj} MJ/m²</span>
                    <span style="font-size: 10px; color: #64748b; font-family: monospace;">(${qsMcal} Mcal/m²)</span>
                </div>
            </td>
            <td style="text-align: center;">
                ${m.image_path ? `
                    <div class="table-thumb-preview" onclick="openPhotoViewer('${m.image_path}', 'Sector ${m.num}: ${escapeStr(m.sector_name)}')" title="Ver fotografía">
                        <img src="${m.image_path}" alt="Foto">
                    </div>
                ` : `
                    <div class="table-thumb-preview" style="cursor: default; opacity: 0.5;" title="Sin fotografía">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                            <circle cx="9" cy="9" r="2" />
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                        </svg>
                    </div>
                `}
            </td>
            <td>
                <span class="badge-registered-staff" title="${m.registered_by || ''}">
                    ${m.registered_by || 'Técnico'}
                </span>
            </td>
            <td style="text-align: right;">
                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                    <!-- Botón Informe Técnico -->
                    <a href="/modulos/${window.MODULE_ID}/carga-fuego-actividad/informe?measurement_id=${m.id}" class="btn-admin-icon-action theme-warning" title="Ver Informe Técnico y Cálculo de Extintores" aria-label="Informe">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10 9 9 9 8 9" />
                        </svg>
                    </a>
                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="openViewMeasurementModalById('${m.id}')" title="Ver detalle del sector">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="confirmDeleteMeasurement('${m.id}', '${m.num}')" title="Eliminar Sector">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                        </svg>
                    </button>
                </div>
            </td>
        `;

        tableBody.appendChild(tr);
    });
}

function renderFirePagination() {
    const total = filteredMeasurements.length;
    const totalPages = Math.ceil(total / pageSize) || 1;
    const start = total > 0 ? (currentPage - 1) * pageSize + 1 : 0;
    const end = Math.min(currentPage * pageSize, total);

    const startEl = document.getElementById('firePageStart');
    const endEl = document.getElementById('firePageEnd');
    const totalEl = document.getElementById('firePageTotal');
    const controlsEl = document.getElementById('firePaginationControls');

    if (startEl) startEl.textContent = start;
    if (endEl) endEl.textContent = end;
    if (totalEl) totalEl.textContent = total;

    if (!controlsEl) return;
    controlsEl.innerHTML = '';

    // Botón Anterior
    const prevBtn = document.createElement('button');
    prevBtn.className = 'modules-pag-btn';
    prevBtn.innerHTML = '‹';
    prevBtn.disabled = currentPage <= 1;
    prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderFireTableRows(); renderFirePagination(); } };
    controlsEl.appendChild(prevBtn);

    // Botones de Páginas
    for (let p = 1; p <= totalPages; p++) {
        const btn = document.createElement('button');
        btn.className = `modules-pag-btn ${p === currentPage ? 'active' : ''}`;
        btn.textContent = p;
        btn.onclick = () => { currentPage = p; renderFireTableRows(); renderFirePagination(); };
        controlsEl.appendChild(btn);
    }

    // Botón Siguiente
    const nextBtn = document.createElement('button');
    nextBtn.className = 'modules-pag-btn';
    nextBtn.innerHTML = '›';
    nextBtn.disabled = currentPage >= totalPages;
    nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderFireTableRows(); renderFirePagination(); } };
    controlsEl.appendChild(nextBtn);
}

function searchFireActivityLive() {
    const input = document.getElementById('fireSearchInput');
    const query = (input?.value || '').toLowerCase().trim();

    const allData = window.ALL_MEASUREMENTS_DATA || [];
    if (!query) {
        filteredMeasurements = [...allData];
    } else {
        filteredMeasurements = allData.filter(m => {
            const searchStr = `${m.num} ${m.macroarea} ${m.sector_name} ${m.location} ${m.registered_by} ${m.risk_level} ` +
                (m.activities || []).map(a => a.actividad).join(' ') + ' ' +
                (m.fire_equipments || []).map(e => e.tipo).join(' ');
            return searchStr.toLowerCase().includes(query);
        });
    }

    currentPage = 1;
    renderFireTableRows();
    renderFirePagination();
}

// =============================================================================
// 4. CONTROL DE FORMULARIOS & FILAS DINÁMICAS (ACTIVIDADES & EQUIPOS)
// =============================================================================
let isEditUnlocked = false;
const modalPhotos = { create: [], edit: [] };
const modalPhotoIndex = { create: 0, edit: 0 };
let modalMiniMapInstance = null;
let modalMiniMapMarker = null;

function recalculateSectorArea(mode) {
    const yi = parseFloat(document.getElementById(`${mode}_yi_largo`)?.value || 0);
    const xi = parseFloat(document.getElementById(`${mode}_xi_ancho`)?.value || 0);
    const area = yi * xi;

    const badge = document.getElementById(`${mode}_calc_area_badge`);
    if (badge) {
        badge.textContent = `${area.toFixed(2)} m²`;
    }
}
window.recalcSectorArea = recalculateSectorArea;

function addActivityRow(mode, actData = null) {
    const container = document.getElementById(`${mode}_activities_container`);
    if (!container) return;

    const rowId = 'act_row_' + Math.random().toString(36).substr(2, 9);
    const row = document.createElement('div');
    row.id = rowId;
    row.className = 'fire-act-item-row';
    row.style.cssText = 'background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px; position: relative; margin-bottom: 8px;';

    const actName = actData ? (actData.actividad || actData.actividad_especifica || actData.name || '') : '';
    const rawType = (actData?.tipo || 'Producción').trim();
    const isStorage = rawType.toLowerCase().startsWith('alma');
    const isDisabled = (mode === 'edit' && !isEditUnlocked);

    row.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
            <span style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase;">Actividad Normada</span>
            <button type="button" class="btn-delete-act-row" onclick="document.getElementById('${rowId}').remove()" style="${isDisabled ? 'display:none;' : ''} border: 0; background: transparent; color: #ef4444; font-size: 13px; font-weight: 800; cursor: pointer;" title="Eliminar actividad">✕</button>
        </div>
        
        <!-- Desplegable Personalizado con Buscador Integrado (350+ Actividades NB 58005) -->
        <div class="custom-search-select-wrapper" id="wrapper_${rowId}" style="margin-bottom: 8px;">
            <input type="hidden" name="activity_names[]" id="hidden_act_${rowId}" value="${escapeStr(actName)}" required ${isDisabled ? 'disabled' : ''}>
            
            <button type="button" class="custom-search-select-trigger ${isDisabled ? 'disabled' : ''}" 
                id="trigger_${rowId}" onclick="toggleActivityDropdown('${rowId}')" ${isDisabled ? 'disabled' : ''}>
                <div style="display: flex; align-items: center; gap: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" style="flex-shrink: 0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span id="label_${rowId}" style="font-weight: 600; font-size: 12px; color: ${actName ? '#0f172a' : '#64748b'};">
                        ${escapeStr(actName || 'Seleccionar actividad normativa...')}
                    </span>
                </div>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            
            <div class="custom-search-select-dropdown" id="dropdown_${rowId}" style="display: none;">
                <div class="search-box-header">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" class="search-filter-input" id="search_input_${rowId}" 
                        placeholder="Buscar entre las 350+ actividades..." 
                        oninput="filterActivityOptions(this, '${rowId}')" onclick="event.stopPropagation()">
                    <button type="button" class="btn-clear-search" onclick="clearActivitySearch('${rowId}', event)" title="Limpiar">✕</button>
                </div>
                <div class="options-list-scrollable" id="options_${rowId}">
                    <!-- Rellenado dinámico con las 350+ actividades -->
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 6px; margin-bottom: 6px;">
            <div>
                <label style="font-size: 10px; font-weight: 700; color: #64748b;">Tipo</label>
                <select name="activity_types[]" id="type_${rowId}" class="custom-form-select" style="height: 32px; font-size: 11.5px;" ${isDisabled ? 'disabled' : ''}>
                    <option value="Producción" ${!isStorage ? 'selected' : ''}>Producción</option>
                    <option value="Almacén" ${isStorage ? 'selected' : ''}>Almacén</option>
                </select>
            </div>
            <div>
                <label style="font-size: 10px; font-weight: 700; color: #64748b;">Largo (m)</label>
                <input type="number" step="0.01" min="0.1" name="activity_largos[]" value="${actData?.largo ?? ''}" class="custom-form-input" style="height: 32px; font-size: 11.5px;" placeholder="0.00" ${isDisabled ? 'disabled' : ''}>
            </div>
            <div>
                <label style="font-size: 10px; font-weight: 700; color: #64748b;">Ancho (m)</label>
                <input type="number" step="0.01" min="0.1" name="activity_anchos[]" value="${actData?.ancho ?? ''}" class="custom-form-input" style="height: 32px; font-size: 11.5px;" placeholder="0.00" ${isDisabled ? 'disabled' : ''}>
            </div>
            <div>
                <label style="font-size: 10px; font-weight: 700; color: #64748b;">Alto (m)</label>
                <input type="number" step="0.01" min="0.1" name="activity_altos[]" value="${actData?.alto ?? ''}" class="custom-form-input" style="height: 32px; font-size: 11.5px;" placeholder="0.00" ${isDisabled ? 'disabled' : ''}>
            </div>
        </div>
        <div>
            <input type="text" name="activity_descs[]" value="${escapeStr(actData?.descripcion || actData?.actividad_especifica || '')}" class="custom-form-input" style="height: 30px; font-size: 11.5px;" placeholder="Detalles de embalaje, estantes o proceso..." ${isDisabled ? 'disabled' : ''}>
        </div>
    `;

    container.appendChild(row);
}
window.addActivityRow = addActivityRow;

function toggleActivityDropdown(rowId) {
    const dropdown = document.getElementById(`dropdown_${rowId}`);
    if (!dropdown) return;

    // Cerrar otros dropdowns abiertos
    document.querySelectorAll('.custom-search-select-dropdown').forEach(d => {
        if (d.id !== `dropdown_${rowId}`) d.style.display = 'none';
    });

    const isOpening = dropdown.style.display === 'none' || dropdown.style.display === '';
    dropdown.style.display = isOpening ? 'block' : 'none';

    if (isOpening) {
        populateActivityOptions(rowId, '');
        const searchInput = document.getElementById(`search_input_${rowId}`);
        if (searchInput) {
            searchInput.value = '';
            setTimeout(() => searchInput.focus(), 50);
        }
    }
}
window.toggleActivityDropdown = toggleActivityDropdown;

function populateActivityOptions(rowId, filterTerm = '') {
    const optionsContainer = document.getElementById(`options_${rowId}`);
    const hiddenInput = document.getElementById(`hidden_act_${rowId}`);
    if (!optionsContainer) return;

    const currentVal = (hiddenInput ? hiddenInput.value : '').trim().toLowerCase();
    const term = filterTerm.trim().toLowerCase();

    const listToFilter = (typeof ALL_FIRE_NORMATIVE_ACTIVITIES !== 'undefined' && Array.isArray(ALL_FIRE_NORMATIVE_ACTIVITIES))
        ? ALL_FIRE_NORMATIVE_ACTIVITIES
        : (FIRE_ACTIVITIES_CATALOG.map(x => x.name));

    const filtered = listToFilter.filter(name => {
        if (!term) return true;
        return name.toLowerCase().includes(term);
    });

    if (filtered.length === 0) {
        optionsContainer.innerHTML = `<div class="activity-no-results">No se encontraron actividades para "${escapeStr(filterTerm)}"</div>`;
        return;
    }

    let html = '';
    filtered.forEach(name => {
        const isSelected = name.toLowerCase() === currentVal;
        html += `
            <div class="activity-option-item ${isSelected ? 'selected' : ''}" 
                onclick="selectActivityOption('${rowId}', '${escapeStr(name)}')">
                <span>${escapeStr(name)}</span>
                ${isSelected ? '<span style="color:#0284c7; font-weight:800;">✓</span>' : ''}
            </div>
        `;
    });

    optionsContainer.innerHTML = html;
}

function filterActivityOptions(inputEl, rowId) {
    populateActivityOptions(rowId, inputEl.value);
}
window.filterActivityOptions = filterActivityOptions;

function clearActivitySearch(rowId, e) {
    if (e) e.stopPropagation();
    const searchInput = document.getElementById(`search_input_${rowId}`);
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
        populateActivityOptions(rowId, '');
    }
}
window.clearActivitySearch = clearActivitySearch;

function selectActivityOption(rowId, name) {
    const hiddenInput = document.getElementById(`hidden_act_${rowId}`);
    const labelSpan = document.getElementById(`label_${rowId}`);
    const dropdown = document.getElementById(`dropdown_${rowId}`);
    const typeSelect = document.getElementById(`type_${rowId}`);

    if (hiddenInput) hiddenInput.value = name;
    if (labelSpan) {
        labelSpan.textContent = name;
        labelSpan.style.color = '#0f172a';
    }
    if (dropdown) dropdown.style.display = 'none';

    // Auto-detección de tipo Almacén vs Producción
    if (typeSelect && name) {
        const val = name.toLowerCase();
        if (val.includes('almacen') || val.includes('deposito') || val.includes('estante') || 
            val.includes('sacos') || val.includes('expedicion') || val.includes('guata') || 
            val.includes('archivo') || val.includes('bodega')) {
            typeSelect.value = 'Almacén';
        }
    }
}
window.selectActivityOption = selectActivityOption;

// Cerrar dropdowns al hacer clic fuera
document.addEventListener('click', function (e) {
    if (!e.target.closest('.custom-search-select-wrapper')) {
        document.querySelectorAll('.custom-search-select-dropdown').forEach(d => {
            d.style.display = 'none';
        });
    }
});

function addFireEquipmentRow(mode, type = 'EXTINTOR', qty = 1, obs = '') {
    const container = document.getElementById(`${mode}_fire_equipments_container`);
    if (!container) return;

    const rowId = 'eq_row_' + Math.random().toString(36).substr(2, 9);
    const row = document.createElement('div');
    row.id = rowId;
    row.className = 'fire-eq-item-row';
    row.style.cssText = 'background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 6px 10px; display: flex; align-items: center; gap: 8px; margin-bottom: 6px;';

    let options = '';
    FIRE_EQUIPMENT_TYPES.forEach(t => {
        options += `<option value="${t}" ${t.toUpperCase() === (type || '').toUpperCase() ? 'selected' : ''}>${t}</option>`;
    });

    const isDisabled = (mode === 'edit' && !isEditUnlocked);

    row.innerHTML = `
        <select name="equipment_types[]" class="custom-form-select" style="width: 130px; height: 32px; font-size: 11px;" ${isDisabled ? 'disabled' : ''}>
            ${options}
        </select>
        <input type="number" name="equipment_quantities[]" value="${qty}" min="1" class="custom-form-input" style="width: 55px; height: 32px; font-size: 11px; text-align: center;" placeholder="Cant" ${isDisabled ? 'disabled' : ''}>
        <input type="text" name="equipment_observations[]" value="${obs || ''}" class="custom-form-input" style="flex: 1; height: 32px; font-size: 11px;" placeholder="Tipo / Observación..." ${isDisabled ? 'disabled' : ''}>
        <button type="button" class="btn-eq-photo-action" title="Ver fotografía de la tarjeta de inspección" onclick="viewEquipmentPhoto('${mode}', '${type}')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
        </button>
        <button type="button" class="btn-delete-eq-row" onclick="document.getElementById('${rowId}').remove()" style="${isDisabled ? 'display:none;' : ''} border: 0; background: transparent; color: #ef4444; font-size: 13px; font-weight: 800; cursor: pointer;" title="Eliminar equipo">✕</button>
    `;

    container.appendChild(row);
}
window.addFireEquipmentRow = addFireEquipmentRow;

function viewEquipmentPhoto(mode = 'edit', type = 'Extintor') {
    const allPhotos = window.activeMeasurementAllPhotos || modalPhotos[mode] || [];
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

// =============================================================================
// 5. APERTURA Y CIERRE DE MODALES & MODO CONSULTA / EDICIÓN
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

function openViewMeasurementModalById(id) {
    const all = window.ALL_MEASUREMENTS_DATA || [];
    const item = all.find(x => String(x.id) === String(id)) || all.find(x => String(x.num) === String(id));
    if (item) {
        openViewMeasurementModal(item);
    }
}
window.openViewMeasurementModalById = openViewMeasurementModalById;

function openViewMeasurementModal(data) {
    if (typeof data === 'string' || typeof data === 'number') {
        return openViewMeasurementModalById(data);
    }
    const modal = document.getElementById('editMeasurementModal');
    if (!modal || !data) return;

    // Actualizar action URL del formulario
    const form = document.getElementById('editMeasurementForm');
    if (form && data.id) {
        form.action = `/modulos/${window.MODULE_ID}/carga-fuego-actividad/mediciones/${data.id}`;
    }

    // Poblar campos principales
    document.getElementById('edit_point_number').value = data.num || '';
    document.getElementById('edit_pt_num_disp').textContent = data.num || '';
    document.getElementById('edit_measurement_date').value = data.raw_date || data.measurement_date || '';
    document.getElementById('edit_measurement_time').value = (data.time && data.time !== '—') ? data.time : (data.measurement_time || '');
    document.getElementById('edit_macroarea').value = data.macroarea || '';
    document.getElementById('edit_sector_name').value = data.sector_name || '';

    // Evaluador
    const staffSelect = document.getElementById('edit_staff_id');
    const staffBadge = document.getElementById('edit_modal_registered_by');
    if (staffSelect && data.staff_id) {
        staffSelect.value = data.staff_id;
    }
    if (staffBadge) {
        staffBadge.textContent = data.registered_by || window.REGISTERED_BY_HEADER || 'Técnico Evaluador';
    }

    // Dimensiones
    if (data.dimensions) {
        document.getElementById('edit_yi_largo').value = data.dimensions.yi_largo || '';
        document.getElementById('edit_xi_ancho').value = data.dimensions.xi_ancho || '';
        recalculateSectorArea('edit');
    }

    // Resumen de Carga de Fuego Qs y Nivel de Riesgo
    const qsMj = parseFloat(data.qs_mj_m2 || 0).toFixed(1);
    const qsMcal = parseFloat(data.qs_mcal_m2 || 0).toFixed(1);
    const riskLevel = data.risk_level || 'Bajo';
    const riskColor = data.risk_theme || data.risk_color || (riskLevel === 'Bajo' ? 'emerald' : (riskLevel === 'Medio' ? 'amber' : 'rose'));

    const qsMjEl = document.getElementById('edit_qs_mj_disp');
    const qsMcalEl = document.getElementById('edit_qs_mcal_disp');
    const riskBadgeEl = document.getElementById('edit_risk_summary_badge');

    if (qsMjEl) qsMjEl.textContent = `${qsMj} MJ/m²`;
    if (qsMcalEl) qsMcalEl.textContent = `(${qsMcal} Mcal/m²)`;
    if (riskBadgeEl) {
        riskBadgeEl.className = `fire-risk-badge ${riskColor}`;
        riskBadgeEl.textContent = `Riesgo ${riskLevel}`;
    }

    // Archivo Fotográfico (Slide Continuo) - Solo foto general del sector, excluyendo tarjeta de extintores
    window.activeMeasurementAllPhotos = (data.images && Array.isArray(data.images) && data.images.length > 0)
        ? [...data.images]
        : ((data.image_paths && Array.isArray(data.image_paths) && data.image_paths.length > 0) ? [...data.image_paths] : (data.image_path ? [data.image_path] : []));

    modalPhotos.edit = [];
    if (data.image_path) {
        modalPhotos.edit = [data.image_path];
    } else if (data.images && Array.isArray(data.images) && data.images.length > 0) {
        modalPhotos.edit = [data.images[0]];
    } else if (data.image_paths && Array.isArray(data.image_paths) && data.image_paths.length > 0) {
        modalPhotos.edit = [data.image_paths[0]];
    }
    modalPhotoIndex.edit = 0;
    syncEditRemainingImages();
    renderPhotoSlider('edit');

    // Coordenadas UTM y GPS
    let utmEasting = (data.utm_easting !== null && data.utm_easting !== '') ? parseFloat(data.utm_easting) : 218468.016;
    let utmNorthing = (data.utm_northing !== null && data.utm_northing !== '') ? parseFloat(data.utm_northing) : 7627234.367;
    let utmZone = data.utm_zone || '20K';
    let lat = (data.latitude !== null && data.latitude !== '') ? parseFloat(data.latitude) : NaN;
    let lng = (data.longitude !== null && data.longitude !== '') ? parseFloat(data.longitude) : NaN;

    let parsedUtm = false;
    if (data.location && typeof data.location === 'string') {
        const match = data.location.match(/E:\s*([0-9.]+),\s*N:\s*([0-9.]+),\s*Z:\s*([0-9A-Za-z]+)/i);
        if (match) {
            utmEasting = parseFloat(match[1]);
            utmNorthing = parseFloat(match[2]);
            utmZone = match[3].toUpperCase();
            parsedUtm = true;
            const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
            lat = pos.lat;
            lng = pos.lng;
        }
    }

    if (!parsedUtm) {
        if (!isNaN(lat) && !isNaN(lng)) {
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

    // Poblar actividades
    const actContainer = document.getElementById('edit_activities_container');
    if (actContainer) {
        actContainer.innerHTML = '';
        const acts = (data.activities && Array.isArray(data.activities)) ? data.activities : [];
        acts.forEach(act => addActivityRow('edit', act));
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
    const btnAddAct = document.getElementById('edit_btn_add_activity');
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
        if (btnAddAct) btnAddAct.style.display = 'inline-flex';
        if (btnAddEq) btnAddEq.style.display = 'inline-flex';
        if (btnSubmit) btnSubmit.style.display = 'inline-flex';

        form.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.id !== 'edit_point_number') el.disabled = false;
        });

        form.querySelectorAll('.custom-search-select-trigger').forEach(b => {
            b.disabled = false;
            b.classList.remove('disabled');
        });

        // Mostrar botones de eliminar fila en actividades y equipos
        form.querySelectorAll('.btn-delete-act-row, .btn-delete-eq-row').forEach(b => b.style.display = 'inline-block');
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
        if (btnAddAct) btnAddAct.style.display = 'none';
        if (btnAddEq) btnAddEq.style.display = 'none';
        if (btnSubmit) btnSubmit.style.display = 'none';

        form.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = true;
        });

        form.querySelectorAll('.custom-search-select-trigger').forEach(b => {
            b.disabled = true;
            b.classList.add('disabled');
        });

        // Ocultar botones de eliminar fila
        form.querySelectorAll('.btn-delete-act-row, .btn-delete-eq-row').forEach(b => b.style.display = 'none');
    }
}
window.applyEditModeState = applyEditModeState;

// =============================================================================
// 6. VISOR Y CARRUSEL FOTOGRÁFICO SLIDE (IDÉNTICO A ILUMINACIÓN)
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
    syncEditRemainingImages();
}
window.deleteActivePhoto = deleteActivePhoto;

function syncEditRemainingImages() {
    const hidden = document.getElementById('edit_remaining_images');
    if (hidden) {
        hidden.value = JSON.stringify(modalPhotos.edit || []);
    }
}

// =============================================================================
// 7. MINI MAPA LEAFLET & PROYECCIÓN UTM (IDÉNTICO A ILUMINACIÓN)
// =============================================================================
function utmToLatLng(easting, northing, zoneStr) {
    let zoneNum = 20;
    let zoneLetter = 'K';
    if (typeof zoneStr === 'string') {
        const m = zoneStr.match(/(\d+)\s*([A-Za-z]?)/);
        if (m) {
            zoneNum = parseInt(m[1], 10) || 20;
            zoneLetter = (m[2] || 'K').toUpperCase();
        }
    } else if (typeof zoneStr === 'number') {
        zoneNum = zoneStr;
    }

    const a = 6378137;
    const f = 1 / 298.257223563;
    const b = a * (1 - f);
    const e = Math.sqrt((a * a - b * b) / (a * a));
    const ePrime = Math.sqrt((a * a - b * b) / (b * b));
    const k0 = 0.9996;

    const isSouth = zoneLetter ? (zoneLetter < 'N') : true;
    const x = easting - 500000;
    const y = isSouth ? northing - 10000000 : northing;

    const m = y / k0;
    const e2 = e * e;
    const e4 = e2 * e2;
    const e6 = e4 * e2;
    const e1 = (1 - Math.sqrt(1 - e2)) / (1 + Math.sqrt(1 - e2));

    const mu = m / (a * (1 - e2 / 4 - 3 * e4 / 64 - 5 * e6 / 256));

    const phi1 = mu + (3 * e1 / 2 - 27 * Math.pow(e1, 3) / 32) * Math.sin(2 * mu)
        + (21 * e1 * e1 / 16 - 55 * Math.pow(e1, 4) / 32) * Math.sin(4 * mu)
        + (151 * Math.pow(e1, 3) / 96) * Math.sin(6 * mu)
        + (1097 * Math.pow(e1, 4) / 512) * Math.sin(8 * mu);

    const sinPhi1 = Math.sin(phi1);
    const cosPhi1 = Math.cos(phi1);
    const tanPhi1 = Math.tan(phi1);

    const n1 = a / Math.sqrt(1 - e2 * sinPhi1 * sinPhi1);
    const t1 = tanPhi1 * tanPhi1;
    const c1 = ePrime * ePrime * cosPhi1 * cosPhi1;
    const r1 = a * (1 - e2) / Math.pow(1 - e2 * sinPhi1 * sinPhi1, 1.5);
    const d = x / (n1 * k0);

    const d2 = d * d;
    const d3 = d2 * d;
    const d4 = d2 * d2;
    const d5 = d4 * d;
    const d6 = d3 * d3;

    const lat = phi1 - (n1 * tanPhi1 / r1) * (
        d2 / 2
        - (5 + 3 * t1 + 10 * c1 - 4 * c1 * c1 - 9 * ePrime * ePrime) * d4 / 24
        + (61 + 90 * t1 + 298 * c1 + 45 * t1 * t1 - 252 * ePrime * ePrime - 3 * c1 * c1) * d6 / 720
    );

    const lon0 = (zoneNum - 1) * 6 - 180 + 3;
    const lng = (lon0 * Math.PI / 180) + (
        d
        - (1 + 2 * t1 + c1) * d3 / 6
        + (5 - 2 * c1 + 28 * t1 - 3 * c1 * c1 + 8 * ePrime * ePrime + 24 * t1 * t1) * d5 / 120
    ) / cosPhi1;

    return {
        lat: lat * 180 / Math.PI,
        lng: lng * 180 / Math.PI
    };
}
window.utmToLatLng = utmToLatLng;

function latLngToUtm(lat, lng) {
    const a = 6378137;
    const f = 1 / 298.257223563;
    const e2 = 2 * f - f * f;
    const ePrime2 = e2 / (1 - e2);
    const k0 = 0.9996;

    const latRad = lat * Math.PI / 180;
    const lngRad = lng * Math.PI / 180;

    let zoneNumber = Math.floor((lng + 180) / 6) + 1;
    if (zoneNumber > 60) zoneNumber = 60;
    if (zoneNumber < 1) zoneNumber = 1;

    const longOrigin = (zoneNumber - 1) * 6 - 180 + 3;
    const longOriginRad = longOrigin * Math.PI / 180;

    const n = a / Math.sqrt(1 - e2 * Math.sin(latRad) * Math.sin(latRad));
    const t = Math.tan(latRad) * Math.tan(latRad);
    const c = ePrime2 * Math.cos(latRad) * Math.cos(latRad);
    const aa = Math.cos(latRad) * (lngRad - longOriginRad);

    const m = a * ((1 - e2 / 4 - 3 * e2 * e2 / 64 - 5 * e2 * e2 * e2 / 256) * latRad
        - (3 * e2 / 8 + 3 * e2 * e2 / 32 + 45 * e2 * e2 * e2 / 1024) * Math.sin(2 * latRad)
        + (15 * e2 * e2 / 256 + 45 * e2 * e2 * e2 / 1024) * Math.sin(4 * latRad)
        - (35 * e2 * e2 * e2 / 3072) * Math.sin(6 * latRad));

    const easting = k0 * n * (aa + (1 - t + c) * Math.pow(aa, 3) / 6
        + (5 - 18 * t + t * t + 72 * c - 58 * ePrime2) * Math.pow(aa, 5) / 120) + 500000;

    let northing = k0 * (m + n * Math.tan(latRad) * (aa * aa / 2
        + (5 - t + 9 * c + 4 * c * c) * Math.pow(aa, 4) / 24
        + (61 - 58 * t + t * t + 600 * c - 330 * ePrime2) * Math.pow(aa, 6) / 720));

    if (lat < 0) northing += 10000000;

    const letters = 'CDEFGHJKLMNPQRSTUVWXX';
    let zoneLetter = 'K';
    if (lat >= -80 && lat <= 84) {
        zoneLetter = letters.charAt(Math.floor((lat + 80) / 8));
    }

    return {
        easting: Math.round(easting * 1000) / 1000,
        northing: Math.round(northing * 1000) / 1000,
        zone: `${zoneNumber}${zoneLetter}`
    };
}
window.latLngToUtm = latLngToUtm;

function initModalMiniMap(mode, lat, lng) {
    const container = document.getElementById(`${mode}_modal_map`);
    if (!container || typeof L === 'undefined') return;

    if (modalMiniMapInstance) {
        modalMiniMapInstance.remove();
        modalMiniMapInstance = null;
    }

    modalMiniMapInstance = L.map(container, {
        center: [lat, lng],
        zoom: 16,
        zoomControl: true,
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(modalMiniMapInstance);

    const icon = L.divIcon({
        className: 'custom-leaflet-marker',
        html: `<div style="background:#0284c7;color:#fff;width:24px;height:24px;border-radius:50%;display:grid;place-items:center;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4);"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z"/></svg></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    modalMiniMapMarker = L.marker([lat, lng], { icon: icon, draggable: (mode === 'create' || isEditUnlocked) }).addTo(modalMiniMapInstance);

    modalMiniMapMarker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updateUtmFieldsFromLatLng(mode, pos.lat, pos.lng);
    });

    modalMiniMapInstance.on('click', function(e) {
        if (mode === 'edit' && !isEditUnlocked) return;
        modalMiniMapMarker.setLatLng(e.latlng);
        updateUtmFieldsFromLatLng(mode, e.latlng.lat, e.latlng.lng);
    });

    setTimeout(() => {
        modalMiniMapInstance.invalidateSize();
    }, 200);
}
window.initModalMiniMap = initModalMiniMap;

function updateUtmFieldsFromLatLng(mode, lat, lng) {
    const utm = latLngToUtm(lat, lng);
    const eInput = document.getElementById(`${mode}_utm_easting`);
    const nInput = document.getElementById(`${mode}_utm_northing`);
    const zInput = document.getElementById(`${mode}_utm_zone`);
    const disp = document.getElementById(`${mode}_utm_display`);
    const locInput = document.getElementById(`${mode}_location`);
    const latInput = document.getElementById(`${mode}_latitude`);
    const lngInput = document.getElementById(`${mode}_longitude`);

    const formattedUtm = `E: ${utm.easting.toFixed(2)}, N: ${utm.northing.toFixed(2)}, Z: ${utm.zone}`;
    if (eInput) eInput.value = utm.easting.toFixed(2);
    if (nInput) nInput.value = utm.northing.toFixed(2);
    if (zInput) zInput.value = utm.zone;
    if (disp) disp.textContent = formattedUtm;
    if (locInput) locInput.value = formattedUtm;
    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);
}

function syncUtmToMap(mode) {
    const easting = parseFloat(document.getElementById(`${mode}_utm_easting`)?.value || 0);
    const northing = parseFloat(document.getElementById(`${mode}_utm_northing`)?.value || 0);
    const zone = document.getElementById(`${mode}_utm_zone`)?.value || '20K';
    const disp = document.getElementById(`${mode}_utm_display`);
    const locInput = document.getElementById(`${mode}_location`);

    const formattedUtm = `E: ${easting.toFixed(2)}, N: ${northing.toFixed(2)}, Z: ${zone}`;
    if (disp) disp.textContent = formattedUtm;
    if (locInput) locInput.value = formattedUtm;

    if (easting > 0 && northing > 0) {
        const pos = utmToLatLng(easting, northing, zone);
        if (modalMiniMapInstance && modalMiniMapMarker) {
            modalMiniMapMarker.setLatLng([pos.lat, pos.lng]);
            modalMiniMapInstance.setView([pos.lat, pos.lng], 16);
        }
        const latInput = document.getElementById(`${mode}_latitude`);
        const lngInput = document.getElementById(`${mode}_longitude`);
        if (latInput) latInput.value = pos.lat.toFixed(7);
        if (lngInput) lngInput.value = pos.lng.toFixed(7);
    }
}
window.syncUtmToMap = syncUtmToMap;

function getCurrentGpsPosition(latId, lngId, mode) {
    if (!navigator.geolocation) {
        alert('Geolocalización no soportada por el navegador.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateUtmFieldsFromLatLng(mode, lat, lng);
            if (modalMiniMapInstance && modalMiniMapMarker) {
                modalMiniMapMarker.setLatLng([lat, lng]);
                modalMiniMapInstance.setView([lat, lng], 17);
            }
        },
        (err) => {
            alert('No se pudo obtener la ubicación GPS: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}
window.getCurrentGpsPosition = getCurrentGpsPosition;

// =============================================================================
// VISOR DE FOTOGRAFÍAS AMPLIFICADAS (MODAL)
// =============================================================================
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
window.openPhotoViewerModal = openPhotoViewer;

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) modal.classList.remove('open');
}
window.closePhotoViewer = closePhotoViewer;

async function downloadFireExcelPlanilla() {
    if (typeof ExcelJS === 'undefined') {
        alert('Cargando librería ExcelJS, por favor intenta en unos segundos.');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'METRIC v2 — Pachabol';
    workbook.created = new Date();

    const sheet = workbook.addWorksheet('Carga de Fuego - NB 58005', {
        pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true, fitToWidth: 1 }
    });

    const header = window.TECHNICAL_HEADER_DATA || {};
    const measurements = window.ALL_MEASUREMENTS_DATA || [];

    // Título Principal
    sheet.mergeCells('A1:J1');
    const titleCell = sheet.getCell('A1');
    titleCell.value = 'ESTUDIO TÉCNICO DE CARGA DE FUEGO PONDERADA POR ACTIVIDAD (NB 58005 / NTP 453)';
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
        'Actividades Principales', 'qsi (MJ/m²)', 'Equipos Contra Incendio', 'Qs Ponderada (MJ/m²)', 'Nivel de Riesgo'
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
        const actNames = (m.activities || []).map(a => a.actividad).join(', ');
        const eqText = (m.fire_equipments || []).map(e => `${e.cantidad} ${e.tipo}`).join(', ');

        row.values = [
            m.num,
            m.macroarea,
            m.sector_name,
            `${m.dimensions?.yi_largo || 0}m × ${m.dimensions?.xi_ancho || 0}m`,
            m.dimensions?.area_m2 || 0,
            actNames,
            m.activities?.[0]?.qsi || 0,
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
    a.download = `Estudio_Carga_Fuego_Actividad_${Date.now()}.xlsx`;
    a.click();
    window.URL.revokeObjectURL(url);
}
window.downloadFireExcelPlanilla = downloadFireExcelPlanilla;

// =============================================================================
// 8. LEAFLET MAPS INTEGRATION
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
        allLocationsMapInstance.setView([m.latitude, m.longitude], 16, { animate: true });
    }
}
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;

// =============================================================================
// 9. TABLAS NORMATIVAS MODAL
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
    document.querySelectorAll('.fire-tab-nav').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.fire-table-tab-pane').forEach(pane => pane.style.display = 'none');

    const activeBtn = Array.from(document.querySelectorAll('.fire-tab-nav')).find(b => b.getAttribute('onclick')?.includes(tabId));
    if (activeBtn) activeBtn.classList.add('active');

    const activePane = document.getElementById(tabId);
    if (activePane) activePane.style.display = 'block';
}
window.switchFireTableTab = switchFireTableTab;

function filterNormativeCatalogTable(input) {
    const query = (input?.value || '').toLowerCase();
    const rows = document.querySelectorAll('#normativeCatalogTable tbody tr');
    rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        r.style.display = text.includes(query) ? '' : 'none';
    });
}
window.filterNormativeCatalogTable = filterNormativeCatalogTable;

// =============================================================================
// 10. REPORTE FOTOGRÁFICO MOSAICO CARTA
// =============================================================================
let currentPhotoGrid = '2x3';
let selectedPointsForReport = [];

function openPhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (!modal) return;

    selectedPointsForReport = (window.ALL_MEASUREMENTS_DATA || []).map(m => m.id);
    renderPhotoInteractiveGrid();
    modal.classList.add('open');
}
window.openPhotoReportModal = openPhotoReportModal;

function closePhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) modal.classList.remove('open');
}
window.closePhotoReportModal = closePhotoReportModal;

function renderPhotoInteractiveGrid() {
    const container = document.getElementById('photoInteractiveGridContainer');
    if (!container) return;

    const measurements = window.ALL_MEASUREMENTS_DATA || [];
    container.innerHTML = '';

    measurements.forEach(m => {
        const card = document.createElement('div');
        card.className = 'photo-card-item';
        card.innerHTML = `
            <div class="photo-card-img-wrap">
                ${m.image_path ? `<img src="${m.image_path}">` : '<span style="color:#64748b;font-size:12px;">Sin fotografía</span>'}
            </div>
            <div class="photo-card-info">
                <strong style="font-size:12.5px;color:#0f172a;">#${m.num} — ${m.sector_name}</strong>
                <div style="font-size:11px;color:#64748b;margin-top:2px;">${m.macroarea}</div>
                <div style="font-size:11px;font-weight:700;color:#e11d48;margin-top:2px;">Qs: ${m.qs_mj_m2} MJ/m² (${m.risk_level})</div>
            </div>
        `;
        container.appendChild(card);
    });

    const pill = document.getElementById('photoSelectionCountText');
    if (pill) pill.textContent = `${measurements.length} de ${measurements.length} seleccionadas`;
}

function changeGridDistribution(grid) {
    currentPhotoGrid = grid;
    document.querySelectorAll('.btn-grid-dist').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-grid') === grid);
    });
}
window.changeGridDistribution = changeGridDistribution;

function switchPhotoReportTab(tab) {
    const isInteractive = tab === 'interactive';
    document.getElementById('tabBtnInteractive')?.classList.toggle('active', isInteractive);
    document.getElementById('tabBtnSheets')?.classList.toggle('active', !isInteractive);
    document.getElementById('photoInteractiveView').style.display = isInteractive ? '' : 'none';
    document.getElementById('photoSheetsView').style.display = isInteractive ? 'none' : '';
}
window.switchPhotoReportTab = switchPhotoReportTab;

function printPhotoReport() {
    window.print();
}
window.printPhotoReport = printPhotoReport;

function savePhotoReportSettingsToServer(showToast = false) {
    if (showToast && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Configuración Guardada!',
            text: 'Se guardó la distribución fotográfica para el reporte PDF.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }
}
window.savePhotoReportSettingsToServer = savePhotoReportSettingsToServer;

// =============================================================================
// 11. AUTO-SAVE INLINE HEADER FIELDS
// =============================================================================
function autoSaveHeaderField() {
    const badge = document.getElementById('headerAutoSaveBadge');
    if (badge) {
        badge.classList.add('visible');
        setTimeout(() => badge.classList.remove('visible'), 2500);
    }
}
window.autoSaveHeaderField = autoSaveHeaderField;

function escapeStr(str) {
    return (str || '').replace(/'/g, "\\'");
}
