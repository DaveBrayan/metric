/**
 * METRIC — Administrators Management Scripts (Rendered by Vite)
 * Modal controller, live search filtering & role filtering
 */

/**
 * Open Create Administrator Modal
 */
window.openCreateAdminModal = function() {
    const modal = document.getElementById('adminCreateModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

/**
 * Close Administrator Modal
 */
window.closeAdminModal = function() {
    const modal = document.getElementById('adminCreateModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

/**
 * Filter Admins Table Live by Search Query
 */
window.filterAdminsLive = function() {
    const input = document.getElementById('adminsSearchInput');
    if (!input) return;
    const query = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#adminsMasterTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
};

/**
 * Filter Admins by Role Pill
 * @param {string} roleName 
 * @param {HTMLElement} btn 
 */
window.filterAdminsByRole = function(roleName, btn) {
    document.querySelectorAll('.admins-role-filter-group .role-filter-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const rows = document.querySelectorAll('#adminsMasterTable tbody tr');
    rows.forEach(row => {
        if (roleName === 'all') {
            row.style.display = '';
        } else {
            const roleCell = row.querySelector('.admin-role-badge');
            if (roleCell) {
                const match = roleCell.textContent.toLowerCase().includes(roleName.toLowerCase());
                row.style.display = match ? '' : 'none';
            }
        }
    });

    if (typeof window.triggerToast === 'function') {
        window.triggerToast('Filtro aplicado: ' + (roleName === 'all' ? 'Todos' : roleName), '👥');
    }
};

/**
 * Quick Action on Admin Row
 * @param {string} action 
 * @param {string} adminName 
 */
window.handleAdminRowAction = function(action, adminName) {
    if (typeof window.triggerToast === 'function') {
        window.triggerToast(action + ' para: ' + adminName, '⚙');
    }
};
