<!-- ==========================================================================
     MODAL: TABLAS TÉCNICAS NORMATIVAS — MÉTODO ROSA (Rapid Office Strain Assessment)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="rosaTablesModal" role="dialog" aria-modal="true"
    aria-labelledby="rosaTablesModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 1060px; width: 95%; max-height: 88vh; display: flex; flex-direction: column;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div
                    style="width: 36px; height: 36px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" />
                        <path d="M3 9h18" />
                        <path d="M3 15h18" />
                        <path d="M9 3v18" />
                    </svg>
                </div>
                <div>
                    <h2 id="rosaTablesModalTitle" style="font-size: 17px; margin: 0; font-weight: 800; color: var(--ink, #0f172a);">
                        Matrices Normativas del Método ROSA
                    </h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                        Tablas biomecánicas, matrices de decisión A1, A2, A, B, C, D, E y niveles de acción ROSA
                    </span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeRosaTablesModal()" aria-label="Cerrar">✕</button>
        </div>

        <!-- Pestañas de Navegación -->
        <div style="display: flex; background: #f8fafc; border-bottom: 1.5px solid #cbd5e1; padding: 10px 20px; gap: 8px; overflow-x: auto;">
            <button type="button" class="reba-tab-btn active" data-tab="tabla_a" onclick="switchRosaTab('tabla_a')">
                <span>1. Tabla A (Silla: A1, A2 & A)</span>
            </button>
            <button type="button" class="reba-tab-btn" data-tab="tabla_b" onclick="switchRosaTab('tabla_b')">
                <span>2. Tabla B (Pantalla vs Teléfono)</span>
            </button>
            <button type="button" class="reba-tab-btn" data-tab="tabla_c" onclick="switchRosaTab('tabla_c')">
                <span>3. Tabla C (Ratón vs Teclado)</span>
            </button>
            <button type="button" class="reba-tab-btn" data-tab="tabla_d_e" onclick="switchRosaTab('tabla_d_e')">
                <span>4. Tablas D & E (Periféricos & ROSA)</span>
            </button>
            <button type="button" class="reba-tab-btn" data-tab="niveles" onclick="switchRosaTab('niveles')">
                <span>5. Niveles de Riesgo y Acción</span>
            </button>
        </div>

        <!-- Cuerpo con Contenido de las Pestañas -->
        <div class="modal-body-custom" style="flex: 1; overflow-y: auto; padding: 20px 24px;">
            
            <!-- TAB 1: TABLA A (SILLA: A1, A2 Y TABLA A) -->
            <div id="tab_pane_tabla_a" class="reba-tab-pane" style="display: block;">
                <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; font-weight: 800; color: #0f172a;">
                        Tabla A-1: Asiento (Altura x Profundidad) & Tabla A-2: Soporte (Reposabrazos x Respaldo)
                    </span>
                    <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">Sondeo ROSA • Silla</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <!-- Matriz A1 -->
                    <div>
                        <div style="font-size: 12px; font-weight: 800; color: #0284c7; margin-bottom: 6px;">
                            Matriz A1: Puntuación Asiento
                        </div>
                        <table class="reba-normative-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 80px;">Altura Asiento</th>
                                    <th colspan="3" style="text-align: center;">Profundidad Asiento</th>
                                </tr>
                                <tr>
                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>1</strong></td><td>1</td><td>2</td><td>3</td></tr>
                                <tr><td><strong>2</strong></td><td>2</td><td>3</td><td>4</td></tr>
                                <tr><td><strong>3</strong></td><td>3</td><td>4</td><td>5</td></tr>
                                <tr><td><strong>4</strong></td><td>4</td><td>5</td><td>6</td></tr>
                                <tr><td><strong>5</strong></td><td>5</td><td>6</td><td>7</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Matriz A2 -->
                    <div>
                        <div style="font-size: 12px; font-weight: 800; color: #0284c7; margin-bottom: 6px;">
                            Matriz A2: Puntuación Soporte
                        </div>
                        <table class="reba-normative-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 80px;">Reposabrazos</th>
                                    <th colspan="5" style="text-align: center;">Respaldo</th>
                                </tr>
                                <tr>
                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>
                                    <th>4</th>
                                    <th>5</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>1</strong></td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td></tr>
                                <tr><td><strong>2</strong></td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td></tr>
                                <tr><td><strong>3</strong></td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td></tr>
                                <tr><td><strong>4</strong></td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td></tr>
                                <tr><td><strong>5</strong></td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                    Matriz A: Puntuación de la Silla (Asiento x Soporte)
                </div>
                <table class="reba-normative-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 70px;">Asiento</th>
                            <th colspan="9" style="text-align: center;">Soporte (Reposabrazos + Respaldo)</th>
                        </tr>
                        <tr>
                            <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>1</strong></td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                        <tr><td><strong>2</strong></td><td>2</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                        <tr><td><strong>3</strong></td><td>3</td><td>3</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                        <tr><td><strong>4</strong></td><td>4</td><td>4</td><td>4</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                        <tr><td><strong>5</strong></td><td>5</td><td>5</td><td>5</td><td>5</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                        <tr><td><strong>6</strong></td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                        <tr><td><strong>7</strong></td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>8</td><td>9</td></tr>
                    </tbody>
                </table>
                <p style="font-size: 11.5px; color: #64748b; margin-top: 8px;">
                    * <em>Puntuación Silla Final (Score A)</em> = Matriz A + Tiempo de Uso Diario (+1 / 0 / -1).
                </p>
            </div>

            <!-- TAB 2: TABLA B (PANTALLA VS TELÉFONO) -->
            <div id="tab_pane_tabla_b" class="reba-tab-pane" style="display: none;">
                <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; font-weight: 800; color: #0f172a;">
                        Tabla B: Puntuación Pantalla vs Teléfono (Score B)
                    </span>
                    <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">PVD & Comunicación</span>
                </div>
                <table class="reba-normative-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 90px;">Pantalla (PVD)</th>
                            <th colspan="7" style="text-align: center;">Teléfono</th>
                        </tr>
                        <tr>
                            <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>1</strong></td><td>1</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>2</strong></td><td>1</td><td>2</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>3</strong></td><td>2</td><td>2</td><td>3</td><td>3</td><td>4</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>4</strong></td><td>3</td><td>3</td><td>3</td><td>4</td><td>4</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>5</strong></td><td>4</td><td>4</td><td>4</td><td>4</td><td>5</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>6</strong></td><td>5</td><td>5</td><td>5</td><td>5</td><td>5</td><td>6</td><td>6</td></tr>
                        <tr><td><strong>7</strong></td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>7</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- TAB 3: TABLA C (RATÓN VS TECLADO) -->
            <div id="tab_pane_tabla_c" class="reba-tab-pane" style="display: none;">
                <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; font-weight: 800; color: #0f172a;">
                        Tabla C: Puntuación Ratón / Mouse vs Teclado (Score C)
                    </span>
                    <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">Periféricos de Entrada</span>
                </div>
                <table class="reba-normative-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 90px;">Ratón (Mouse)</th>
                            <th colspan="7" style="text-align: center;">Teclado</th>
                        </tr>
                        <tr>
                            <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>1</strong></td><td>1</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>2</strong></td><td>1</td><td>2</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>3</strong></td><td>2</td><td>2</td><td>3</td><td>3</td><td>4</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>4</strong></td><td>3</td><td>3</td><td>3</td><td>4</td><td>4</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>5</strong></td><td>4</td><td>4</td><td>4</td><td>4</td><td>5</td><td>5</td><td>6</td></tr>
                        <tr><td><strong>6</strong></td><td>5</td><td>5</td><td>5</td><td>5</td><td>5</td><td>6</td><td>6</td></tr>
                        <tr><td><strong>7</strong></td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>7</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- TAB 4: TABLAS D & E (PERIFÉRICOS & ROSA INICIAL) -->
            <div id="tab_pane_tabla_d_e" class="reba-tab-pane" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <div style="font-size: 12.5px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                            Tabla D: Periféricos (Score B vs Score C)
                        </div>
                        <table class="reba-normative-table">
                            <thead>
                                <tr>
                                    <th rowspan="2">Score B</th>
                                    <th colspan="8" style="text-align: center;">Score C (Ratón/Teclado)</th>
                                </tr>
                                <tr>
                                    <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>1</strong></td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td></tr>
                                <tr><td><strong>2</strong></td><td>2</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td></tr>
                                <tr><td><strong>3</strong></td><td>3</td><td>3</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td></tr>
                                <tr><td><strong>4</strong></td><td>4</td><td>4</td><td>4</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td></tr>
                                <tr><td><strong>5</strong></td><td>5</td><td>5</td><td>5</td><td>5</td><td>5</td><td>6</td><td>7</td><td>8</td></tr>
                                <tr><td><strong>6</strong></td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>7</td><td>8</td></tr>
                                <tr><td><strong>7</strong></td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>8</td></tr>
                                <tr><td><strong>8</strong></td><td>8</td><td>8</td><td>8</td><td>8</td><td>8</td><td>8</td><td>8</td><td>8</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <div style="font-size: 12.5px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                            Tabla E: Puntuación ROSA Inicial (Score A vs Score D)
                        </div>
                        <table class="reba-normative-table">
                            <thead>
                                <tr>
                                    <th rowspan="2">Silla A</th>
                                    <th colspan="10" style="text-align: center;">Periféricos D</th>
                                </tr>
                                <tr>
                                    <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>1</strong></td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>2</strong></td><td>2</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>3</strong></td><td>3</td><td>3</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>4</strong></td><td>4</td><td>4</td><td>4</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>5</strong></td><td>5</td><td>5</td><td>5</td><td>5</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>6</strong></td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>7</strong></td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>7</td><td>8</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>8</strong></td><td>8</td><td>8</td><td>8</td><td>8</td><td>8</td><td>8</td><td>8</td><td>8</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>9</strong></td><td>9</td><td>9</td><td>9</td><td>9</td><td>9</td><td>9</td><td>9</td><td>9</td><td>9</td><td>10</td></tr>
                                <tr><td><strong>10</strong></td><td>10</td><td>10</td><td>10</td><td>10</td><td>10</td><td>10</td><td>10</td><td>10</td><td>10</td><td>10</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 5: NIVELES DE RIESGO Y ACCIÓN -->
            <div id="tab_pane_niveles" class="reba-tab-pane" style="display: none;">
                <div style="margin-bottom: 14px;">
                    <span style="font-size: 13.5px; font-weight: 800; color: #0f172a;">
                        Clasificación de Riesgo y Prioridad de Acción — Método ROSA
                    </span>
                </div>
                <table class="reba-normative-table" style="font-size: 12.5px;">
                    <thead>
                        <tr>
                            <th style="width: 90px; text-align: center;">Puntuación Final</th>
                            <th style="width: 140px;">Nivel de Riesgo</th>
                            <th style="width: 120px; text-align: center;">Nivel de Acción</th>
                            <th>Descripción de la Acción Requerida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #ecfdf5;">
                            <td style="text-align: center; font-weight: 800; color: #047857; font-size: 15px;">1 - 2</td>
                            <td><strong style="color: #047857;">Inapreciable</strong></td>
                            <td style="text-align: center; font-weight: 700;">Nivel 1</td>
                            <td style="color: #065f46;">Postura y configuración óptimas del puesto. No es necesaria ninguna acción correctiva.</td>
                        </tr>
                        <tr style="background: #f7fee7;">
                            <td style="text-align: center; font-weight: 800; color: #4d7c0f; font-size: 15px;">3 - 4</td>
                            <td><strong style="color: #4d7c0f;">Bajo</strong></td>
                            <td style="text-align: center; font-weight: 700;">Nivel 2</td>
                            <td style="color: #3f6212;">Riesgo bajo. Puesto en condiciones aceptables, pero pueden realizarse mejoras ergonómicas menores.</td>
                        </tr>
                        <tr style="background: #fffbeb;">
                            <td style="text-align: center; font-weight: 800; color: #b45309; font-size: 15px;">5</td>
                            <td><strong style="color: #b45309;">Medio (Alerta)</strong></td>
                            <td style="text-align: center; font-weight: 700;">Nivel 3</td>
                            <td style="color: #92400e;"><strong>Nivel de Alerta Crítico.</strong> El puesto requiere evaluación detallada y acción correctiva pronto.</td>
                        </tr>
                        <tr style="background: #fff7ed;">
                            <td style="text-align: center; font-weight: 800; color: #c2410c; font-size: 15px;">6 - 8</td>
                            <td><strong style="color: #c2410c;">Alto</strong></td>
                            <td style="text-align: center; font-weight: 700;">Nivel 4</td>
                            <td style="color: #9a3412;">Riesgo alto de sobreesfuerzo musculoesquelético. Es necesaria la acción e intervención ergonómica pronto.</td>
                        </tr>
                        <tr style="background: #fef2f2;">
                            <td style="text-align: center; font-weight: 800; color: #b91c1c; font-size: 15px;">9 - 10</td>
                            <td><strong style="color: #b91c1c;">Muy Alto</strong></td>
                            <td style="text-align: center; font-weight: 700;">Nivel 5</td>
                            <td style="color: #991b1b;">Riesgo extremo e inminente de trastornos musculoesqueléticos. Es necesaria la intervención ergonómica de inmediato.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-primary-hero-action" onclick="closeRosaTablesModal()">
                Entendido / Cerrar
            </button>
        </div>
    </div>
</div>
