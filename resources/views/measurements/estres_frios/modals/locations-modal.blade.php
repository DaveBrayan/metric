    <!-- ==========================================================================
         MODAL: MAPA GENERAL DE TODAS LAS UBICACIONES Y PERSONAL REGISTRADOR (FRÍO)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="allLocationsModal" role="dialog" aria-modal="true"
        aria-labelledby="allLocationsModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1150px; width: 95%;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="allLocationsModalTitle" style="font-size: 17px; margin: 0;">Ubicaciones de Puntos de Estrés por Frío</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Puntos de monitoreo WCI geolocalizados, fotografías y personal técnico registrador</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span
                        style="font-size: 12px; font-weight: 800; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px;">
                        {{ $totalMeasurements }} Puntos Registrados
                    </span>
                    <button type="button" class="btn-close-modal" onclick="closeAllLocationsModal()"
                        aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="modal-body-custom" style="padding: 18px 22px;">
                <div class="all-loc-modal-grid">
                    <!-- Mapa Leaflet Interactivo -->
                    <div>
                        <div id="allLocationsMapLeaflet"></div>
                        <div
                            style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #64748b;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                                    <span>Riesgo Bajo (Conforme)</span>
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #ca8a04; display: inline-block;"></span>
                                    <span>Riesgo Moderado</span>
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #dc2626; display: inline-block;"></span>
                                    <span>Riesgo Alto / Crítico</span>
                                </span>
                            </div>
                            <span style="font-size: 11.5px; color: #94a3b8;">Haz clic en un marcador para ver la fotografía y datos del punto</span>
                        </div>
                    </div>

                    <!-- Panel Lateral con Lista de Puntos, Fotografía y Personal Registrador -->
                    <div class="all-loc-sidebar">
                        <div
                            style="padding: 4px 6px; font-size: 12.5px; font-weight: 800; color: var(--ink); display: flex; align-items: center; justify-content: space-between;">
                            <span>Puntos Registrados</span>
                            <span style="font-size: 11px; color: #0284c7;">Clic para enfocar</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse($measurements as $idx => $m)
                                @php
                                    $isCompliant = $m['is_compliant'] ?? true;
                                    $rawNum = (string)($m['num'] ?? $m['point_number'] ?? ($idx + 1));
                                    $cleanNum = intval(preg_replace('/[^0-9]/', '', $rawNum)) ?: ($idx + 1);
                                    $firstPhoto = !empty($m['photos']) && is_array($m['photos']) ? $m['photos'][0] : ($m['image_path'] ?? null);
                                    $riesgo = $m['nivel_riesgo'] ?? 'Bajo';
                                @endphp
                                <div class="all-loc-point-item" onclick="focusPointOnAllLocationsMap({{ $idx }})">
                                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                                        <!-- Miniatura de Imagen -->
                                        @if($firstPhoto)
                                            <div class="table-thumb-preview" style="width: 48px; height: 48px; border-radius: 8px; flex-shrink: 0;"
                                                onclick="event.stopPropagation(); openPhotoViewer('{{ $firstPhoto }}', 'Punto {{ $cleanNum }}: {{ addslashes($m['puesto_trabajo']) }}')"
                                                title="Ver fotografía ampliada">
                                                <img src="{{ $firstPhoto }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @else
                                            <div class="table-thumb-preview" style="width: 48px; height: 48px; border-radius: 8px; flex-shrink: 0; cursor: default; opacity: 0.5;"
                                                title="Sin fotografía registrada">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8">
                                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                                    <circle cx="9" cy="9" r="2" />
                                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                                </svg>
                                            </div>
                                        @endif

                                        <!-- Información del Punto -->
                                        <div style="flex: 1; min-width: 0;">
                                            <div class="all-loc-point-top" style="margin-bottom: 2px;">
                                                <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                                                    <span
                                                        style="font-size: 11.5px; font-weight: 800; background: {{ $isCompliant ? '#ecfdf5' : '#fef2f2' }}; color: {{ $isCompliant ? '#065f46' : '#991b1b' }}; border: 1px solid {{ $isCompliant ? '#a7f3d0' : '#fecaca' }}; padding: 1px 6px; border-radius: 4px; flex-shrink: 0;">
                                                        #{{ $cleanNum }}
                                                    </span>
                                                    <strong
                                                        style="font-size: 12.5px; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                        title="{{ $m['puesto_trabajo'] }}">
                                                        {{ $m['puesto_trabajo'] ?: 'Puesto no especificado' }}
                                                    </strong>
                                                </div>
                                                <span
                                                    style="font-size: 11px; font-weight: 700; color: {{ ($m['sensacion_termica_c'] !== null && $m['sensacion_termica_c'] <= 0) ? '#0284c7' : '#059669' }}; flex-shrink: 0;">
                                                    {{ $m['sensacion_termica_c'] !== null ? number_format($m['sensacion_termica_c'], 1) . ' °C' : '—' }}
                                                </span>
                                            </div>

                                            <div
                                                style="font-size: 11.5px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px;"
                                                title="{{ $m['area'] }} • WCI: {{ $m['indice_viento_wci'] }} W/m²">
                                                {{ $m['area'] }} • WCI: {{ $m['indice_viento_wci'] !== null ? number_format($m['indice_viento_wci'], 0) : '—' }} W/m²
                                            </div>

                                            <div
                                                style="display: flex; align-items: center; gap: 5px; font-size: 11px; color: #0284c7; background: #f0f9ff; padding: 2px 7px; border-radius: 6px; border: 1px solid #e0f2fe; width: fit-content; max-width: 100%;">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                                    <circle cx="12" cy="7" r="4" />
                                                </svg>
                                                <span
                                                    style="font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    Registrado por: <strong>{{ $m['staff_name'] ?: ($registeredByHeader ?: 'Técnico') }}</strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; color: #94a3b8; padding: 24px; font-size: 13px;">
                                    No hay puntos de monitoreo registrados aún.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-primary-hero-action" onclick="closeAllLocationsModal()"
                    style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
            </div>
        </div>
    </div>
