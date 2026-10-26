<div class="ruido-table-card">
    <div class="ruido-toolbar" style="display: flex; justify-content: flex-end;">
        <div class="search-box-pill">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
            <input type="text" id="ruidoSearchInput" class="search-input-pill" placeholder="Buscar..." onkeyup="searchRuidoOcupacionalLive()">
        </div>
    </div>

    <div class="table-responsive-box">
        <table class="modern-table" id="ruidoMasterTable">
            <thead><tr>
                <th style="width:50px;text-align:center;">NRO</th>
                <th style="width:140px;">ÁREA</th>
                <th style="width:140px;">PUNTO</th>
                <th style="width:120px;">TIPO</th>
                <th style="width:70px;text-align:center;">TPE(H)</th>
                <th style="width:70px;text-align:center;">POND</th>
                <th style="width:70px;text-align:center;">RESP</th>
                <th style="width:100px;text-align:center;">LEQ(dB)</th>
                <th style="width:80px;text-align:center;">IMG</th>
                <th style="width:110px;">Por</th>
                <th style="width:160px;text-align:center;">Acciones</th>
            </tr></thead>
            <tbody id="ruidoTableBody">
                @forelse($measurements as $m)
                    <tr class="ruido-data-row" data-search="{{ strtolower($m['num'].' '.$m['area'].' '.$m['punto_medicion'].' '.$m['tipo_ruido']) }}">
                        <td style="font-weight:800;color:#94a3b8;text-align:center;">{{ $m['num'] }}</td>
                        <td style="font-size:12.5px;font-weight:600;color:#0284c7;">{{ $m['area'] }}</td>
                        <td style="font-size:12.5px;font-weight:600;color:#334155;">{{ $m['punto_medicion'] }}</td>
                        <td style="font-size:12px;color:#64748b;"><span style="padding:4px 8px;background:#f0f9ff;border-left:3px solid #0284c7;border-radius:4px;">{{ $m['tipo_ruido'] }}</span></td>
                        <td style="font-size:12.5px;font-weight:700;text-align:center;">{{ number_format($m['tiempo_expos_h'],2) }}</td>
                        <td style="font-size:12px;text-align:center;color:#64748b;font-family:monospace;">{{ $m['ponderacion']??'A' }}</td>
                        <td style="font-size:12px;text-align:center;color:#64748b;font-family:monospace;">{{ $m['respuesta']??'-' }}</td>
                        <td style="font-size:13px;font-weight:800;text-align:center;color:{{ $m['leq_t_db']<=85?'#059669':'#dc2626' }};">{{ number_format($m['leq_t_db'],1) }}</td>
                        <td style="text-align: center; vertical-align: middle;">
                            @php
                                $imgSrc = !empty($m['image_path']) ? $m['image_path'] : (!empty($m['images']) && count($m['images']) > 0 ? $m['images'][0] : null);
                                $imgCount = !empty($m['images_count']) ? $m['images_count'] : (!empty($m['images']) ? count($m['images']) : 0);
                            @endphp
                            @if(!empty($imgSrc))
                                <div class="table-thumb-preview"
                                    style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; max-width: 44px; max-height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; overflow: hidden; background: #f8fafc; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; position: relative; margin: 0 auto;"
                                    onclick="openPhotoViewer('{{ $imgSrc }}', 'Punto {{ $m['num'] }}: {{ addslashes($m['punto_medicion'] ?: $m['area']) }}')"
                                    title="Ver fotografía ampliada">
                                    <img src="{{ $imgSrc }}" alt="Foto" style="width: 100% !important; height: 100% !important; max-width: 100% !important; max-height: 100% !important; object-fit: cover !important; display: block !important;">
                                    @if($imgCount > 1)
                                        <span class="thumb-count-badge" style="position: absolute !important; bottom: 2px !important; right: 2px !important; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px; line-height: 1.1;">{{ $imgCount }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="table-thumb-preview" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; max-width: 44px; max-height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; overflow: hidden; background: #f8fafc; display: inline-flex; align-items: center; justify-content: center; cursor: default; opacity: 0.5; margin: 0 auto;"
                                    title="Sin fotografía registrada">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td style="font-size:11px;font-weight:600;color:#334155;">{{ $m['registered_by'] }}</td>
                        <td style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                            <button type="button" class="btn-admin-icon-action theme-amber" onclick="openEditMeasurementById({{ $m['id'] }})" title="Editar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg></button>
                            <button type="button" class="btn-admin-icon-action theme-danger" onclick="confirmDeleteMeasurement('{{ $m['id'] }}','{{ $m['num'] }}')" title="Eliminar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" style="text-align:center;color:#64748b;padding:36px;">No hay puntos registrados.</td></tr>
                @endforelse
                <tr id="noResultsRow" style="display:none;"><td colspan="11" style="text-align:center;color:#64748b;padding:36px;">No se encontraron mediciones.</td></tr>
            </tbody>
        </table>
    </div>
</div>
