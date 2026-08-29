/**
 * METRIC — Dashboard Page Interactive Scripts
 * Live table searching, chart filters, date modal triggers, and quick action dispatchers
 */

/**
 * Filter projects table in real-time based on search input
 */
function searchLiveTable() {
    const input = document.getElementById('tableSearchInput');
    if (!input) return;
    const query = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#interactiveProjectsTable tbody tr');
    let matchesCount = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(query);
        row.style.display = matches ? '' : 'none';
        if (matches) matchesCount++;
    });
}

/**
 * Switch Chart Time Range Filter
 * @param {string} range 
 */
function handleChartTimeFilter(range = '7D') {
    triggerToast('Rango de gráfico actualizado: ' + range, '📊');
}

/**
 * Open Date Range Picker Modal or Action
 */
function handleDateRangePicker() {
    triggerToast('Selector de rango temporal: 18 May - 24 May 2024', '📅');
}

/**
 * Dispatch Quick Actions
 * @param {string} actionTitle 
 */
function dispatchQuickAction(actionTitle) {
    triggerToast('Iniciando: ' + actionTitle, '⚡');
}

/**
 * Handle Single Project Actions
 * @param {string} projectName 
 */
function handleProjectAction(projectName) {
    triggerToast('Opciones avanzadas para: ' + projectName, '⚙');
}
