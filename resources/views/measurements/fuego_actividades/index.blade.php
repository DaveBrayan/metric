@extends('layouts.app')

@section('title', 'Carga de Fuego por Actividad — Metric v2 Pachabol')

@push('styles')
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @metricStyle('fuego_actividades')
@endpush

@section('content')
    {{-- 1. Encabezado y Navegación --}}
    @include('measurements.fuego_actividades.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual (Datos de Monitoreo & Equipo de Medición) --}}
    @include('measurements.fuego_actividades.partials.technical-cards')

    {{-- 3. Tabla Maestra de Sectores de Carga de Fuego, Filtros y Paginación Reactiva --}}
    @include('measurements.fuego_actividades.partials.table')

    {{-- ========================================================================= --}}
    {{-- MODALES DEL SISTEMA                                                       --}}
    {{-- ========================================================================= --}}
    @include('measurements.fuego_actividades.modals.create-modal')
    @include('measurements.fuego_actividades.modals.edit-modal')
    @include('measurements.fuego_actividades.modals.map-modal')
    @include('measurements.fuego_actividades.modals.photo-viewer-modal')
    @include('measurements.fuego_actividades.modals.export-modal')
    @include('measurements.fuego_actividades.modals.locations-modal')
    @include('measurements.fuego_actividades.modals.photo-report-modal')
    @include('measurements.fuego_actividades.modals.tables-modal')

    <!-- Formulario oculto para eliminar sector / punto de medición -->
    <form id="deleteMeasurementForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Datalist Autocomplete / Buscador de Actividades Normativas (350+ Actividades NB 58005) -->
    <datalist id="fireActivitiesGlobalDatalist">
        <option value="Abonos químicos"></option>
        <option value="Aceites comestibles, expedición"></option>
        <option value="Aceites comestibles"></option>
        <option value="Aceites: mineral, vegetal y animal"></option>
        <option value="Acero"></option>
        <option value="Acetileno, llenado de botellas"></option>
        <option value="Ácido carbónico"></option>
        <option value="Ácidos inorgánicos"></option>
        <option value="Acumuladores"></option>
        <option value="Acumuladores, expedición"></option>
        <option value="Agua oxigenada"></option>
        <option value="Agujas de acero"></option>
        <option value="Alambre metálico aislado"></option>
        <option value="Alambre metálico no aislado"></option>
        <option value="Albergues"></option>
        <option value="Albergues juveniles"></option>
        <option value="Alfarería"></option>
        <option value="Algodón en rama, guata"></option>
        <option value="Algodón, almacén de"></option>
        <option value="Alimentación, embalaje"></option>
        <option value="Alimentación, expedición"></option>
        <option value="Alimentación, materias primas"></option>
        <option value="Alimentación, platos precocinados"></option>
        <option value="Almacenes de talleres, etc."></option>
        <option value="Almidón"></option>
        <option value="Alquitrán"></option>
        <option value="Alquitrán, productos de"></option>
        <option value="Altos hornos"></option>
        <option value="Aluminio, producción de"></option>
        <option value="Aluminio, trabajo de"></option>
        <option value="Antigüedades, venta de"></option>
        <option value="Aparatos de radio"></option>
        <option value="Aparatos de radio, venta"></option>
        <option value="Aparatos de televisión"></option>
        <option value="Aparatos domésticos"></option>
        <option value="Aparatos eléctricos"></option>
        <option value="Aparatos eléctricos, reparación"></option>
        <option value="Aparatos electrónicos"></option>
        <option value="Aparatos electrónicos, reparación"></option>
        <option value="Aparatos fotográficos"></option>
        <option value="Aparatos mecánicos"></option>
        <option value="Aparatos pequeños, construcción de"></option>
        <option value="Aparatos sanitarios, taller"></option>
        <option value="Aparatos, talleres de reparación"></option>
        <option value="Aparatos, expedición de"></option>
        <option value="Aparatos, prueba de"></option>
        <option value="Aparcamientos, edificios de"></option>
        <option value="Apartamentos"></option>
        <option value="Apósitos, fabricación de artículos"></option>
        <option value="Archivos"></option>
        <option value="Arena"></option>
        <option value="Armarios frigoríficos"></option>
        <option value="Armas"></option>
        <option value="Artículos de metal"></option>
        <option value="Artículos de yeso"></option>
        <option value="Artículos metal fundidos por inyección"></option>
        <option value="Artículos metálicos, soldadura ligera"></option>
        <option value="Artículos metálicos, amolado"></option>
        <option value="Artículos metálicos, barnizado"></option>
        <option value="Artículos metálicos, cerrajería"></option>
        <option value="Artículos metálicos, chatarras"></option>
        <option value="Artículos metálicos, dorado"></option>
        <option value="Artículos metálicos, estampado"></option>
        <option value="Artículos metálicos, forjado"></option>
        <option value="Artículos metálicos, fresado"></option>
        <option value="Artículos metálicos, fundición"></option>
        <option value="Artículos metálicos, grabación"></option>
        <option value="Artículos metálicos, soldadura"></option>
        <option value="Artículos pirotécnicos"></option>
        <option value="Aserraderos"></option>
        <option value="Asfalto (bidones, bloques)"></option>
        <option value="Asfalto, manipulación de"></option>
        <option value="Automóviles, almacén de accesorios"></option>
        <option value="Automóviles, garajes y aparcamientos"></option>
        <option value="Automóviles, guarnición"></option>
        <option value="Automóviles, montaje"></option>
        <option value="Automóviles, pintura"></option>
        <option value="Automóviles, reparación"></option>
        <option value="Automóviles, venta de accesorios"></option>
        <option value="Aviones"></option>
        <option value="Aviones, hangares"></option>
        <option value="Azúcar"></option>
        <option value="Azúcar, productos de"></option>
        <option value="Azufre"></option>
        <option value="Balanzas"></option>
        <option value="Bancos, oficinas y sucursales de"></option>
        <option value="Barcos de madera"></option>
        <option value="Barcos de plástico"></option>
        <option value="Barcos metálicos"></option>
        <option value="Barnices"></option>
        <option value="Barnices a la cera"></option>
        <option value="Barnices, expedición"></option>
        <option value="Barnizado"></option>
        <option value="Barnizado de muebles"></option>
        <option value="Barnizado de papel"></option>
        <option value="Bebidas alcohólicas"></option>
        <option value="Bebidas sin alcohol"></option>
        <option value="Bebidas sin alcohol, expedición de"></option>
        <option value="Bibliotecas"></option>
        <option value="Bicicletas"></option>
        <option value="Bodegas (vinos)"></option>
        <option value="Bramante"></option>
        <option value="Bramante, almacén de"></option>
        <option value="Buhardillas habitables"></option>
        <option value="Cables"></option>
        <option value="Cacao, productos de"></option>
        <option value="Café crudo, sin refinar"></option>
        <option value="Café, extracto"></option>
        <option value="Café, tostadero"></option>
        <option value="Cajas de madera"></option>
        <option value="Cajas fuertes"></option>
        <option value="Calderas, edificios de"></option>
        <option value="Calefacciones"></option>
        <option value="Calefacciones centrales"></option>
        <option value="Calzado"></option>
        <option value="Calzado, accesorios de"></option>
        <option value="Calzados, expedición"></option>
        <option value="Calzados, venta"></option>
        <option value="Cantinas"></option>
        <option value="Caramelos"></option>
        <option value="Caramelos, embalaje"></option>
        <option value="Carbón de coke"></option>
        <option value="Carnicerías, venta"></option>
        <option value="Carretería, artículos de"></option>
        <option value="Carrocerías de automóvil"></option>
        <option value="Cartón"></option>
        <option value="Cartón embreado"></option>
        <option value="Cartón ondulado"></option>
        <option value="Cartón piedra"></option>
        <option value="Cartonaje"></option>
        <option value="Cartonaje, expedición de"></option>
        <option value="Caucho"></option>
        <option value="Caucho, artículos de"></option>
        <option value="Caucho, venta de artículos de"></option>
        <option value="Celuloide"></option>
        <option value="Cemento"></option>
        <option value="Central de calefacción a distancia"></option>
        <option value="Centrales hidráulicas"></option>
        <option value="Centrales hidroeléctricas"></option>
        <option value="Centrales térmicas"></option>
        <option value="Cepillos y brochas"></option>
        <option value="Cera"></option>
        <option value="Cera, artículos de"></option>
        <option value="Cera, venta de artículos de"></option>
        <option value="Cerámica, artículos de"></option>
        <option value="Cerillas"></option>
        <option value="Cerrajerías"></option>
        <option value="Cervecerías"></option>
        <option value="Cestería"></option>
        <option value="Cestería, venta de artículos de"></option>
        <option value="Chapa, artículos de"></option>
        <option value="Chapa, embalaje de artículos"></option>
        <option value="Chatarrería"></option>
        <option value="Chocolate"></option>
        <option value="Chocolate, embalaje"></option>
        <option value="Chocolate, fabricación, sala de moldes"></option>
        <option value="Cines"></option>
        <option value="Cochecitos de niño"></option>
        <option value="Colchones no sintéticos."></option>
        <option value="Colores y barnices, manufacturas de"></option>
        <option value="Colores y barnices, mezclas"></option>
        <option value="Colores y barnices, venta"></option>
        <option value="Colores con diluyentes combustibles"></option>
        <option value="Confiterías"></option>
        <option value="Congelados"></option>
        <option value="Conservas"></option>
        <option value="Corcho"></option>
        <option value="Corcho, artículos de"></option>
        <option value="Cordelerías, enusa"></option>
        <option value="Cordelerías, venta"></option>
        <option value="Correas"></option>
        <option value="Cortinas en rollo"></option>
        <option value="Cosméticos"></option>
        <option value="Crin, cerda de"></option>
        <option value="Cristalerías"></option>
        <option value="Cuero"></option>
        <option value="Cuero sintético, recorte de artículos de"></option>
        <option value="Cuero sintético"></option>
        <option value="Cuero sintético, artículos de"></option>
        <option value="Cuero, artículos de"></option>
        <option value="Cuero, recortes de artículos de"></option>
        <option value="Cuero, venta de artículos de"></option>
        <option value="Deportes, venta de artículos de"></option>
        <option value="Depósitos de hidrocarburos"></option>
        <option value="Depósitos de mercancías incombustibles"></option>
        <option value="Diluyentes"></option>
        <option value="Discos"></option>
        <option value="Droguerías"></option>
        <option value="Edificios frigoríficos"></option>
        <option value="Electricidad, almacén de materiales de"></option>
        <option value="Electricidad, taller de"></option>
        <option value="Embalaje de material impreso"></option>
        <option value="Embalaje de mercancías combustibles"></option>
        <option value="Embalaje de mercancías incombustibles"></option>
        <option value="Embalaje de productos alimenticios"></option>
        <option value="Embalaje de textiles"></option>
        <option value="Emisoras de radio"></option>
        <option value="Encuadernación"></option>
        <option value="Escobas"></option>
        <option value="Escorias"></option>
        <option value="Escuelas y colegios"></option>
        <option value="Esculturas de piedra"></option>
        <option value="Especias"></option>
        <option value="Espumas sintéticas"></option>
        <option value="Espumas sintéticas, artículos de"></option>
        <option value="Estampación de productos sintéticos, cuero, etc."></option>
        <option value="Estampado de materias sintéticas"></option>
        <option value="Estampado de metales"></option>
        <option value="Estilográficas"></option>
        <option value="Estudio de televisión"></option>
        <option value="Estufas de gas"></option>
        <option value="Expedición de artículos sintéticos"></option>
        <option value="Expedición de artículos de cristal"></option>
        <option value="Expedición de artículos de hojalata"></option>
        <option value="Expedición de artículos impresos"></option>
        <option value="Expedición de bebidas"></option>
        <option value="Expedición de cartonaje"></option>
        <option value="Expedición de ceras y barnices"></option>
        <option value="Expedición de muebles"></option>
        <option value="Expedición de pequeños artículos de madera"></option>
        <option value="Expedición de productos alimenticios"></option>
        <option value="Expedición de textiles"></option>
        <option value="Exposición de automóviles"></option>
        <option value="Exposición de cuadros"></option>
        <option value="Exposición de máquinas"></option>
        <option value="Exposición de muebles"></option>
        <option value="Farmacias (almacenes incluidos)"></option>
        <option value="Féretros de madera"></option>
        <option value="Fibras de coco"></option>
        <option value="Fieltro"></option>
        <option value="Fieltro, artículos de"></option>
        <option value="Flores artificiales"></option>
        <option value="Flores, venta de"></option>
        <option value="Fontanería"></option>
        <option value="Forraje"></option>
        <option value="Fósforo"></option>
        <option value="Fotocopias, talleres"></option>
        <option value="Fotografía, laboratorios"></option>
        <option value="Fotografía, películas"></option>
        <option value="Fotografía, talleres"></option>
        <option value="Fotografía, tienda"></option>
        <option value="Fraguas"></option>
        <option value="Fundición de metales"></option>
        <option value="Funiculares"></option>
        <option value="Galvanoplastia"></option>
        <option value="Gasolineras"></option>
        <option value="Grandes almacenes"></option>
        <option value="Granos"></option>
        <option value="Grasas"></option>
        <option value="Grasas comestibles"></option>
        <option value="Grasas comestibles, expedición"></option>
        <option value="Guantes"></option>
        <option value="Guardarropa, armarios de madera"></option>
        <option value="Guardarropa, armarios metálicos"></option>
        <option value="Harina en sacos"></option>
        <option value="Harina, fábrica o comercio sin almacén"></option>
        <option value="Heladería"></option>
        <option value="Heno, balas de"></option>
        <option value="Herramientas"></option>
        <option value="Hidrógeno"></option>
        <option value="Hilados, cardados"></option>
        <option value="Hilados, encanillado-bobinado"></option>
        <option value="Hilados, hilatura"></option>
        <option value="Hilados, productos de hilo"></option>
        <option value="Hilados, productos de lana"></option>
        <option value="Hilados, torcido"></option>
        <option value="Hipermercados"></option>
        <option value="Hogares para ancianos"></option>
        <option value="Hogares para niños"></option>
        <option value="Hojalaterías"></option>
        <option value="Hormigón, artículos de"></option>
        <option value="Hornos"></option>
        <option value="Hospitales"></option>
        <option value="Hoteles, habitaciones"></option>
        <option value="Hoteles, vestíbulos, restaurantes, salas"></option>
        <option value="Hule"></option>
        <option value="Hule, artículos de"></option>
        <option value="Iglesias"></option>
        <option value="Imprentas, almacén"></option>
        <option value="Imprentas, embalaje"></option>
        <option value="Imprentas, expedición"></option>
        <option value="Imprentas, salas de máquinas"></option>
        <option value="Imprentas, taller tipográfico"></option>
        <option value="Incineración de basuras"></option>
        <option value="Instaladores electricistas"></option>
        <option value="Instaladores, talleres"></option>
        <option value="Instrumentos de música"></option>
        <option value="Instrumentos de óptica"></option>
        <option value="Internados, pensionados"></option>
        <option value="Jabón"></option>
        <option value="Jardines de infancia"></option>
        <option value="Joyas, fabricación"></option>
        <option value="Joyas, venta"></option>
        <option value="Juguetes"></option>
        <option value="Laboratorios bacteriológicos"></option>
        <option value="Laboratorios de física"></option>
        <option value="Laboratorios fotográficos"></option>
        <option value="Laboratorios metalúrgicos"></option>
        <option value="Laboratorios odontológicos"></option>
        <option value="Laboratorios químicos"></option>
        <option value="Láminas de hojalata"></option>
        <option value="Lámparas de incandescencia"></option>
        <option value="Lana de madera"></option>
        <option value="Lapiceros"></option>
        <option value="Lavadoras"></option>
        <option value="Lavanderías"></option>
        <option value="Leche condensada"></option>
        <option value="Leche en polvo"></option>
        <option value="Legumbres frescas, venta"></option>
        <option value="Legumbres secas"></option>
        <option value="Leña"></option>
        <option value="Levadura"></option>
        <option value="Librerías"></option>
        <option value="Licores"></option>
        <option value="Licores, venta"></option>
        <option value="Limpieza química"></option>
        <option value="Linóleo"></option>
        <option value="Locales de desechos (diversas mercancías)"></option>
        <option value="Lúpulo"></option>
        <option value="Madera en troncos"></option>
        <option value="Madera, artículos de, barnizado"></option>
        <option value="Madera, artículos de, carpintería"></option>
        <option value="Madera, artículos de, ebanistería"></option>
        <option value="Madera, artículos de, expedición"></option>
        <option value="Madera, artículos de, impregnación"></option>
        <option value="Madera, artículos de, marquetería"></option>
        <option value="Madera, artículos de, pulimentado"></option>
        <option value="Madera, artículos de, secado"></option>
        <option value="Madera, artículos de, serrado"></option>
        <option value="Madera, artículos de, tallado"></option>
        <option value="Madera, artículos de, torneado"></option>
        <option value="Madera, artículos de, troquelado"></option>
        <option value="Madera, mezclada o variada"></option>
        <option value="Madera, restos de"></option>
        <option value="Madera, vigas y tablas"></option>
        <option value="Madera, virutas"></option>
        <option value="Malta"></option>
        <option value="Mantequilla"></option>
        <option value="Máquinas"></option>
        <option value="Máquinas de coser"></option>
        <option value="Máquinas de oficina"></option>
        <option value="Marcos"></option>
        <option value="Mármol, artículos de"></option>
        <option value="Mataderos"></option>
        <option value="Material de oficina"></option>
        <option value="Materiales de construcción, almacén"></option>
        <option value="Materiales usados, tratamiento"></option>
        <option value="Materiales sintéticos"></option>
        <option value="Materias sintéticas inyectadas"></option>
        <option value="Materias sintéticas, artículos de"></option>
        <option value="Materias sintéticas, estampado"></option>
        <option value="Materias sintéticas, soldadura de piezas"></option>
        <option value="Materias sintéticas, expedición"></option>
        <option value="Mecánica de precisión, taller"></option>
        <option value="Médica, consulta"></option>
        <option value="Medicamentos, embalaje"></option>
        <option value="Medicamentos, venta"></option>
        <option value="Melaza"></option>
        <option value="Mercería, venta"></option>
        <option value="Mermelada"></option>
        <option value="Metales preciosos"></option>
        <option value="Metales, manufacturas en general"></option>
        <option value="Metálicas, grandes construcciones"></option>
        <option value="Minerales"></option>
        <option value="Mostaza"></option>
        <option value="Motocicletas"></option>
        <option value="Motores eléctricos"></option>
        <option value="Muebles de acero"></option>
        <option value="Muebles de madera"></option>
        <option value="Muebles de madera, barnizado"></option>
        <option value="Muebles, carpintería"></option>
        <option value="Muebles, tapizado sin espuma sintética"></option>
        <option value="Muebles, venta"></option>
        <option value="Muelles de carga con mercancías"></option>
        <option value="Municiones"></option>
        <option value="Museos"></option>
        <option value="Música, tienda de"></option>
        <option value="Negro de humo, en sacos"></option>
        <option value="Neumáticos"></option>
        <option value="Neumáticos de automóviles"></option>
        <option value="Nitrocelulosa"></option>
        <option value="Oficinas comerciales"></option>
        <option value="Oficinas postales"></option>
        <option value="Oficinas técnicas"></option>
        <option value="Orfebrería"></option>
        <option value="Oxígeno"></option>
        <option value="Paja prensada"></option>
        <option value="Paja, artículos de"></option>
        <option value="Paja, embalajes de"></option>
        <option value="Paletas de madera"></option>
        <option value="Palillos"></option>
        <option value="Panaderías industriales"></option>
        <option value="Panaderías, almacenes"></option>
        <option value="Panaderías, laboratorios y hornos"></option>
        <option value="Paneles de corcho"></option>
        <option value="Paneles de madera aglomerada"></option>
        <option value="Panel de madera aglomerada contrachapada"></option>
        <option value="Papel"></option>
        <option value="Papel, apresto"></option>
        <option value="Papel, desechos prensados"></option>
        <option value="Papel, tratamiento de la madera y materias celulósicas"></option>
        <option value="Papel, tratamiento-fabricación"></option>
        <option value="Papel, viejo o granel"></option>
        <option value="Papelería"></option>
        <option value="Papelería, venta"></option>
        <option value="Paraguas"></option>
        <option value="Paraguas, venta"></option>
        <option value="Parquets"></option>
        <option value="Pastas alimenticias"></option>
        <option value="Pastas alimenticias, expedición"></option>
        <option value="Pegamentos combustibles"></option>
        <option value="Pegamentos incombustibles"></option>
        <option value="Peletería, productos de"></option>
        <option value="Peletería, venta"></option>
        <option value="Películas, copias"></option>
        <option value="Películas, talleres de"></option>
        <option value="Perfumería, artículos de"></option>
        <option value="Perfumería, venta de artículos de"></option>
        <option value="Persianas, fabricación de"></option>
        <option value="Piedras artificiales"></option>
        <option value="Piedras de afilar"></option>
        <option value="Piedras preciosas, tallado"></option>
        <option value="Piedras refractarias, artículos de"></option>
        <option value="Pieles, almacén"></option>
        <option value="Pilas secas"></option>
        <option value="Pinceles"></option>
        <option value="Placas de fibras blandas"></option>
        <option value="Placas de resina sintética"></option>
        <option value="Planeadores"></option>
        <option value="Porcelana"></option>
        <option value="Proceso de datos, sala de ordenador"></option>
        <option value="Productos de amianto"></option>
        <option value="Productos de carnicería"></option>
        <option value="Productos de lavado (lejía)"></option>
        <option value="Producto de lavado (lejía materia prima)"></option>
        <option value="Productos de reparación de calzado"></option>
        <option value="Productos farmacéuticos"></option>
        <option value="Productos lácteos"></option>
        <option value="Productos laminados salvo chapa y alambre"></option>
        <option value="Productos químicos combustibles"></option>
        <option value="Puertas de madera"></option>
        <option value="Puertas plásticas"></option>
        <option value="Quesos"></option>
        <option value="Quioscos de periódicos"></option>
        <option value="Radio, estudio de"></option>
        <option value="Radiología, gabinete de"></option>
        <option value="Refinerías de petróleo"></option>
        <option value="Refrigeradores"></option>
        <option value="Rejilla, asientos y respaldos"></option>
        <option value="Relojes"></option>
        <option value="Relojes, reparación de"></option>
        <option value="Relojes, venta"></option>
        <option value="Resinas naturales"></option>
        <option value="Resinas sintéticas"></option>
        <option value="Resinas sintéticas, placas de"></option>
        <option value="Restaurantes"></option>
        <option value="Revestimientos de suelos combustibles"></option>
        <option value="Revestimientos de suelos combustibles. Venta"></option>
        <option value="Rodamientos o cojinetes de bolas"></option>
        <option value="Sacos de papel"></option>
        <option value="Sacos de yute"></option>
        <option value="Sacos de plástico"></option>
        <option value="Salas de juego"></option>
        <option value="Salinas, productos de"></option>
        <option value="Servicios de mesa"></option>
        <option value="Silos"></option>
        <option value="Skies (Esquíes)"></option>
        <option value="Sombrererías"></option>
        <option value="Sosa"></option>
        <option value="Sótanos, bodegas de casas residenciales"></option>
        <option value="Tabaco en bruto"></option>
        <option value="Tabacos, artículos de"></option>
        <option value="Tabacos, venta de artículos"></option>
        <option value="Talco"></option>
        <option value="Tallado de piedra"></option>
        <option value="Talleres de enchapado"></option>
        <option value="Talleres de guarnicionería"></option>
        <option value="Talleres de pintura"></option>
        <option value="Talleres de reparación"></option>
        <option value="Talleres eléctricos"></option>
        <option value="Talleres mecánicos"></option>
        <option value="Tapicerías"></option>
        <option value="Tapicerías, artículos de"></option>
        <option value="Tapices"></option>
        <option value="Tapices, tintura"></option>
        <option value="Tapices, venta"></option>
        <option value="Teatros"></option>
        <option value="Teatros, bastidores"></option>
        <option value="Tejares, cocción"></option>
        <option value="Tejares, hornos de secado y estanterías de madera"></option>
        <option value="Tejares, hornos de secado y estanterías metálicas"></option>
        <option value="Tejares, prensado"></option>
        <option value="Tejares, preparación de arcilla"></option>
        <option value="Tejares, secadero, estanterías de madera"></option>
        <option value="Tejares, secadero, estanterías metálicas"></option>
        <option value="Tejidos de rafia"></option>
        <option value="Tejidos en general, almacén"></option>
        <option value="Tejidos sintéticos"></option>
        <option value="Tejidos cáñamo, yute, lino"></option>
        <option value="Tejidos, depósito de balas de algodón"></option>
        <option value="Tejidos, seda artificial"></option>
        <option value="Teléfonos"></option>
        <option value="Teléfonos, centrales de"></option>
        <option value="Televisión, estudios de"></option>
        <option value="Textiles"></option>
        <option value="Textiles, apresto"></option>
        <option value="Textiles, artículos de"></option>
        <option value="Textiles, bajos de prendas"></option>
        <option value="Textiles, blanqueado"></option>
        <option value="Textiles, bordado"></option>
        <option value="Textiles, calandrado"></option>
        <option value="Textiles, confección"></option>
        <option value="Textiles, corte"></option>
        <option value="Textiles, de lino"></option>
        <option value="Textiles, de yute"></option>
        <option value="Textiles, embalaje"></option>
        <option value="Textiles, encajes"></option>
        <option value="Textiles, estampado"></option>
        <option value="Textiles, expedición"></option>
        <option value="Textiles, forros"></option>
        <option value="Textiles, lencería"></option>
        <option value="Textiles, mantas"></option>
        <option value="Textiles, prendas de vestir"></option>
        <option value="Textiles, preparación"></option>
        <option value="Textiles, ropa de cama"></option>
        <option value="Textiles, tejidos (fabricación)"></option>
        <option value="Textiles, teñido"></option>
        <option value="Textiles, tricotado"></option>
        <option value="Textiles, venta"></option>
        <option value="Tintas"></option>
        <option value="Tintas de imprenta"></option>
        <option value="Tintorerías"></option>
        <option value="Tocadiscos"></option>
        <option value="Toldos o lonas"></option>
        <option value="Toneles de madera"></option>
        <option value="Toneles de plástico"></option>
        <option value="Torneado de piezas de cobre/bronce"></option>
        <option value="Tractores"></option>
        <option value="Trajes"></option>
        <option value="Trajes, venta"></option>
        <option value="Transformadores"></option>
        <option value="Transformadores, bobinado"></option>
        <option value="Transformadores, estación de"></option>
        <option value="Tubos fluorescentes"></option>
        <option value="Turba, productos de"></option>
        <option value="Vagones, fabricación de"></option>
        <option value="Vehículos"></option>
        <option value="Velas de cera"></option>
        <option value="Venta por correspondencia, empresas de"></option>
        <option value="Ventanas de madera"></option>
        <option value="Ventanas de plástico"></option>
        <option value="Vidrio"></option>
        <option value="Vidrio, plano, fábrica de"></option>
        <option value="Vidrio, artículos de"></option>
        <option value="Vidrio, expedición"></option>
        <option value="Vidrio, talleres de soplado"></option>
        <option value="Vidrio, tintura de"></option>
        <option value="Vidrio, tratamiento de"></option>
        <option value="Vidrio, venta de artículos de"></option>
        <option value="Vinagre, producción de"></option>
        <option value="Vulcanización"></option>
        <option value="Yeso"></option>
        <option value="Zulaque de vidrieros"></option>
        <option value="Zumos de fruta"></option>
    </datalist>
@endsection

@push('scripts')
    <!-- Leaflet JS for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- ExcelJS for High-Fidelity Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

    <!-- Configuración inicial de datos del servidor para el cliente -->
    <script>
        window.METRIC_FIRE_ACTIVITY_CONFIG = {
            moduleId: {{ $module->id ?? 1 }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.fire_activity.header.update', $module->id ?? 1) }}",
            registeredByHeader: @json($registeredByHeader ?? 'Técnico de Campo'),
            measurements: @json($measurements ?? []),
            photoReportSettings: @json($photoReportSettings ?? []),
            technicalHeader: {
                installationName: @json($installationName ?? 'Planta Industrial'),
                startDateFormatted: @json($startDateFormatted ?? date('d/m/Y')),
                endDateFormatted: @json($endDateFormatted ?? date('d/m/Y')),
                monitoringType: @json($monitoringType ?? 'Carga de Fuego por Actividad'),
                equipmentName: @json($equipmentName ?? 'Distanciómetro Láser'),
                equipmentBrand: @json($equipmentBrand ?? 'Bosch'),
                equipmentModel: @json($equipmentModel ?? 'GLM 50 C'),
                equipmentSerial: @json($equipmentSerial ?? 'BSH-7749201')
            }
        };
        // Compatibilidad retroactiva directa para variables globales
        window.MODULE_ID = window.METRIC_FIRE_ACTIVITY_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_FIRE_ACTIVITY_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_FIRE_ACTIVITY_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_FIRE_ACTIVITY_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_FIRE_ACTIVITY_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_FIRE_ACTIVITY_CONFIG.registeredByHeader;
    </script>

    {{-- Lógica modularizada de Carga de Fuego por Actividad --}}
    @metricScript('fuego_actividades')

    {{-- SweetAlert2 Notificaciones de Sesión con diseño oficial METRIC --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: @json(session('success')),
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        buttonsStyling: false,
                        timer: 3500,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'metric-swal-popup',
                            confirmButton: 'metric-swal-btn-confirm'
                        }
                    });
                }
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Atención',
                        text: @json(session('error')),
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'metric-swal-popup',
                            confirmButton: 'metric-swal-btn-danger'
                        }
                    });
                }
            });
        </script>
    @endif

    @if(isset($errors) && $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Errores de Validación',
                        html: `{!! implode('<br>', $errors->all()) !!}`,
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'metric-swal-popup',
                            confirmButton: 'metric-swal-btn-danger'
                        }
                    });
                }
            });
        </script>
    @endif
@endpush
