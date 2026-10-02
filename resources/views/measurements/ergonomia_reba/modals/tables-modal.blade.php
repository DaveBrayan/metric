<!-- ==========================================================================
     MODAL 8: TABLAS TÉCNICAS NORMATIVAS — MÉTODO REBA (NTP 601 / ISO 11226)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="rebaTablesModal" role="dialog" aria-modal="true"
    aria-labelledby="rebaTablesModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 1040px; width: 95%; max-height: 88vh; display: flex; flex-direction: column;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div
                    style="width: 36px; height: 36px; border-radius: 9px; background: #fffbeb; color: #d97706; display: grid; place-items: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" />
                        <path d="M3 9h18" />
                        <path d="M3 15h18" />
                        <path d="M9 3v18" />
                    </svg>
                </div>
                <div>
                    <h2 id="rebaTablesModalTitle" style="font-size: 17px; margin: 0; font-weight: 800; color: var(--ink, #0f172a);">
                        Matrices Normativas del Método REBA
                    </h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                        Tablas biomecánicas, puntuaciones cruzadas y niveles de acción según NTP 601 / ISO 11226
                    </span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeRebaTablesModal()" aria-label="Cerrar">✕</button>
        </div>

        <!-- Pestañas de Navegación -->
        <div style="display: flex; background: #f8fafc; border-bottom: 1.5px solid #cbd5e1; padding: 10px 20px; gap: 8px; overflow-x: auto;">
            <button type="button" class="reba-tab-btn active" data-tab="tabla_a" onclick="switchRebaTab('tabla_a')">
                <span>1. Tabla A (Tronco/Cuello/Piernas)</span>
            </button>
            <button type="button" class="reba-tab-btn" data-tab="tabla_b" onclick="switchRebaTab('tabla_b')">
                <span>2. Tabla B (Brazos/Antebrazos/Muñecas)</span>
            </button>
            <button type="button" class="reba-tab-btn" data-tab="tabla_c" onclick="switchRebaTab('tabla_c')">
                <span>3. Tabla C (Puntuación A vs B)</span>
            </button>
            <button type="button" class="reba-tab-btn" data-tab="modificadores" onclick="switchRebaTab('modificadores')">
                <span>4. Carga, Agarre & Actividad</span>
            </button>
            <button type="button" class="reba-tab-btn" data-tab="niveles" onclick="switchRebaTab('niveles')">
                <span>5. Niveles de Riesgo & Acción</span>
            </button>
        </div>

        <!-- Cuerpo con Contenido de las Pestañas -->
        <div class="modal-body-custom" style="flex: 1; overflow-y: auto; padding: 20px 24px;">
            
            <!-- TAB 1: TABLA A -->
            <div id="tab_pane_tabla_a" class="reba-tab-pane" style="display: block;">
                <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; font-weight: 800; color: #0f172a;">
                        Tabla A: Puntuación para Tronco, Cuello y Piernas (Grupo A)
                    </span>
                    <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">NTP 601 / Hignett & McAtamney</span>
                </div>
                <div class="reba-table-scroll-wrap">
                    <table class="reba-grid-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="reba-th-primary" style="padding: 8px;">Tronco</th>
                                <th colspan="4" class="reba-th-primary">Cuello 1</th>
                                <th colspan="4" class="reba-th-primary">Cuello 2</th>
                                <th colspan="4" class="reba-th-primary">Cuello 3</th>
                            </tr>
                            <tr>
                                <th class="reba-th-sub" style="width: 28px;">P1</th><th class="reba-th-sub" style="width: 28px;">P2</th><th class="reba-th-sub" style="width: 28px;">P3</th><th class="reba-th-sub" style="width: 28px;">P4</th>
                                <th class="reba-th-sub" style="width: 28px;">P1</th><th class="reba-th-sub" style="width: 28px;">P2</th><th class="reba-th-sub" style="width: 28px;">P3</th><th class="reba-th-sub" style="width: 28px;">P4</th>
                                <th class="reba-th-sub" style="width: 28px;">P1</th><th class="reba-th-sub" style="width: 28px;">P2</th><th class="reba-th-sub" style="width: 28px;">P3</th><th class="reba-th-sub" style="width: 28px;">P4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="reba-th-rowhead">1</td><td>1</td><td>2</td><td>3</td><td>4</td><td>2</td><td>3</td><td>4</td><td>5</td><td>3</td><td>4</td><td>5</td><td>6</td></tr>
                            <tr><td class="reba-th-rowhead">2</td><td>2</td><td>3</td><td>4</td><td>5</td><td>3</td><td>4</td><td>5</td><td>6</td><td>4</td><td>5</td><td>6</td><td>7</td></tr>
                            <tr><td class="reba-th-rowhead">3</td><td>2</td><td>4</td><td>5</td><td>6</td><td>4</td><td>5</td><td>6</td><td>7</td><td>5</td><td>6</td><td>7</td><td>8</td></tr>
                            <tr><td class="reba-th-rowhead">4</td><td>3</td><td>5</td><td>6</td><td>7</td><td>5</td><td>6</td><td>7</td><td>8</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                            <tr><td class="reba-th-rowhead">5</td><td>4</td><td>6</td><td>7</td><td>8</td><td>6</td><td>7</td><td>8</td><td>9</td><td>7</td><td>8</td><td>9</td><td>9</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: TABLA B -->
            <div id="tab_pane_tabla_b" class="reba-tab-pane" style="display: none;">
                <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; font-weight: 800; color: #0f172a;">
                        Tabla B: Puntuación para Brazos, Antebrazos y Muñecas (Grupo B)
                    </span>
                    <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">NTP 601 / Hignett & McAtamney</span>
                </div>
                <div class="reba-table-scroll-wrap">
                    <table class="reba-grid-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="reba-th-primary" style="padding: 8px;">Brazo</th>
                                <th colspan="3" class="reba-th-primary">Antebrazo 1</th>
                                <th colspan="3" class="reba-th-primary">Antebrazo 2</th>
                            </tr>
                            <tr>
                                <th class="reba-th-sub" style="width: 34px;">M1</th><th class="reba-th-sub" style="width: 34px;">M2</th><th class="reba-th-sub" style="width: 34px;">M3</th>
                                <th class="reba-th-sub" style="width: 34px;">M1</th><th class="reba-th-sub" style="width: 34px;">M2</th><th class="reba-th-sub" style="width: 34px;">M3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="reba-th-rowhead">1</td><td>1</td><td>2</td><td>2</td><td>1</td><td>2</td><td>3</td></tr>
                            <tr><td class="reba-th-rowhead">2</td><td>1</td><td>2</td><td>3</td><td>2</td><td>3</td><td>4</td></tr>
                            <tr><td class="reba-th-rowhead">3</td><td>3</td><td>4</td><td>5</td><td>4</td><td>5</td><td>5</td></tr>
                            <tr><td class="reba-th-rowhead">4</td><td>4</td><td>5</td><td>5</td><td>5</td><td>6</td><td>7</td></tr>
                            <tr><td class="reba-th-rowhead">5</td><td>6</td><td>7</td><td>8</td><td>7</td><td>8</td><td>8</td></tr>
                            <tr><td class="reba-th-rowhead">6</td><td>7</td><td>8</td><td>8</td><td>8</td><td>9</td><td>9</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: TABLA C -->
            <div id="tab_pane_tabla_c" class="reba-tab-pane" style="display: none;">
                <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; font-weight: 800; color: #0f172a;">
                        Tabla C: Puntuación Final Combinada (Score A vs Score B)
                    </span>
                    <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">NTP 601 / Matriz C</span>
                </div>
                <div class="reba-table-scroll-wrap">
                    <table class="reba-grid-table">
                        <thead>
                            <tr>
                                <th class="reba-th-primary" style="padding: 6px;">A \ B</th>
                                @for($b = 1; $b <= 12; $b++)
                                    <th class="reba-th-primary">{{ $b }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="reba-th-rowhead">1</td><td>1</td><td>1</td><td>1</td><td>2</td><td>3</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>7</td><td>7</td></tr>
                            <tr><td class="reba-th-rowhead">2</td><td>1</td><td>2</td><td>2</td><td>3</td><td>4</td><td>4</td><td>5</td><td>6</td><td>6</td><td>7</td><td>7</td><td>8</td></tr>
                            <tr><td class="reba-th-rowhead">3</td><td>2</td><td>3</td><td>3</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>7</td><td>8</td><td>8</td><td>8</td></tr>
                            <tr><td class="reba-th-rowhead">4</td><td>3</td><td>4</td><td>4</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>8</td><td>9</td><td>9</td><td>9</td></tr>
                            <tr><td class="reba-th-rowhead">5</td><td>4</td><td>4</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>8</td><td>9</td><td>9</td><td>9</td><td>9</td></tr>
                            <tr><td class="reba-th-rowhead">6</td><td>6</td><td>6</td><td>6</td><td>7</td><td>8</td><td>8</td><td>9</td><td>9</td><td>10</td><td>10</td><td>10</td><td>10</td></tr>
                            <tr><td class="reba-th-rowhead">7</td><td>7</td><td>7</td><td>7</td><td>8</td><td>9</td><td>9</td><td>9</td><td>10</td><td>10</td><td>11</td><td>11</td><td>11</td></tr>
                            <tr><td class="reba-th-rowhead">8</td><td>8</td><td>8</td><td>8</td><td>9</td><td>10</td><td>10</td><td>10</td><td>10</td><td>10</td><td>11</td><td>11</td><td>11</td></tr>
                            <tr><td class="reba-th-rowhead">9</td><td>9</td><td>9</td><td>9</td><td>10</td><td>10</td><td>10</td><td>11</td><td>11</td><td>11</td><td>12</td><td>12</td><td>12</td></tr>
                            <tr><td class="reba-th-rowhead">10</td><td>10</td><td>10</td><td>10</td><td>11</td><td>11</td><td>11</td><td>11</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td></tr>
                            <tr><td class="reba-th-rowhead">11</td><td>11</td><td>11</td><td>11</td><td>11</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td></tr>
                            <tr><td class="reba-th-rowhead">12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td><td>12</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: MODIFICADORES -->
            <div id="tab_pane_modificadores" class="reba-tab-pane" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div style="border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 16px; background: #ffffff;">
                        <h4 style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">
                            Puntuación por Carga / Fuerza
                        </h4>
                        <ul style="font-size: 12.5px; color: #334155; padding-left: 18px; margin: 0; line-height: 1.7;">
                            <li><strong>0 puntos:</strong> Carga menor a 5 kg (fuerza ligera).</li>
                            <li><strong>+1 punto:</strong> Carga entre 5 y 10 kg (fuerza moderada).</li>
                            <li><strong>+2 puntos:</strong> Carga superior a 10 kg (fuerza severa).</li>
                            <li><strong>+1 punto adicional:</strong> Aplicación brusca, rápida o con sacudida.</li>
                        </ul>
                    </div>

                    <div style="border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 16px; background: #ffffff;">
                        <h4 style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">
                            Puntuación por Acoplamiento / Agarre
                        </h4>
                        <ul style="font-size: 12.5px; color: #334155; padding-left: 18px; margin: 0; line-height: 1.7;">
                            <li><strong>0 puntos (Bueno):</strong> Asas de diseño óptimo, sujeción envolvente y confortable.</li>
                            <li><strong>+1 punto (Aceptable):</strong> Agarre manual admisible pero no ergonómico.</li>
                            <li><strong>+2 puntos (Posible):</strong> Sujeción precaria sin asas o forzada.</li>
                            <li><strong>+3 puntos (Inaceptable):</strong> Sin agarre manual, piezas resbaladizas o peligroso.</li>
                        </ul>
                    </div>
                </div>

                <div style="border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 16px; background: #ffffff; margin-top: 14px;">
                    <h4 style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">
                        Puntuación por Actividad Muscular (+1 por cada factor presente)
                    </h4>
                    <ul style="font-size: 12.5px; color: #334155; padding-left: 18px; margin: 0; line-height: 1.7;">
                        <li><strong>+1 punto:</strong> 1 o más partes del cuerpo permanecen estáticas durante más de 1 minuto.</li>
                        <li><strong>+1 punto:</strong> Se repiten acciones posturales cortas más de 4 veces por minuto.</li>
                        <li><strong>+1 punto:</strong> Se producen acciones que inducen cambios posturales rápidos sobre bases inestables.</li>
                    </ul>
                </div>
            </div>

            <!-- TAB 5: NIVELES DE ACCIÓN -->
            <div id="tab_pane_niveles" class="reba-tab-pane" style="display: none;">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                    Niveles de Acción y Prioridad de Intervención REBA
                </h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="border: 1.5px solid #a7f3d0; background: #ecfdf5; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="color: #047857; font-size: 13.5px;">Puntuación 1 — Nivel 0 (Riesgo Inapreciable)</strong>
                            <div style="font-size: 12px; color: #065f46; margin-top: 2px;">No es necesaria ninguna acción ergonómica correctiva.</div>
                        </div>
                        <span class="reba-score-pill risk-inapreciable" style="font-size: 14px; padding: 4px 12px;">1</span>
                    </div>

                    <div style="border: 1.5px solid #d9f99d; background: #f7fee7; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="color: #4d7c0f; font-size: 13.5px;">Puntuación 2 a 3 — Nivel 1 (Riesgo Bajo)</strong>
                            <div style="font-size: 12px; color: #3f6212; margin-top: 2px;">Puede ser necesaria la acción; puesto a mantener bajo observación periódica.</div>
                        </div>
                        <span class="reba-score-pill risk-bajo" style="font-size: 14px; padding: 4px 12px;">2 - 3</span>
                    </div>

                    <div style="border: 1.5px solid #fde68a; background: #fffbeb; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="color: #b45309; font-size: 13.5px;">Puntuación 4 a 7 — Nivel 2 (Riesgo Medio)</strong>
                            <div style="font-size: 12px; color: #92400e; margin-top: 2px;">Es necesaria la acción; requiere rediseño de tareas y rotación de personal.</div>
                        </div>
                        <span class="reba-score-pill risk-medio" style="font-size: 14px; padding: 4px 12px;">4 - 7</span>
                    </div>

                    <div style="border: 1.5px solid #fed7aa; background: #fff7ed; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="color: #c2410c; font-size: 13.5px;">Puntuación 8 a 10 — Nivel 3 (Riesgo Alto)</strong>
                            <div style="font-size: 12px; color: #9a3412; margin-top: 2px;">Es necesaria la acción pronto; intervención ergonómica prioritaria a corto plazo.</div>
                        </div>
                        <span class="reba-score-pill risk-alto" style="font-size: 14px; padding: 4px 12px;">8 - 10</span>
                    </div>

                    <div style="border: 1.5px solid #fecaca; background: #fef2f2; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="color: #b91c1c; font-size: 13.5px;">Puntuación 11 a 15 — Nivel 4 (Riesgo Muy Alto)</strong>
                            <div style="font-size: 12px; color: #991b1b; margin-top: 2px;">Es necesaria la acción de inmediato; suspender o modificar el puesto urgentemente.</div>
                        </div>
                        <span class="reba-score-pill risk-muy-alto" style="font-size: 14px; padding: 4px 12px;">11 - 15</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal-footer-custom" style="justify-content: flex-end;">
            <button type="button" class="btn-secondary-subtle" onclick="closeRebaTablesModal()">Cerrar</button>
        </div>
    </div>
</div>
