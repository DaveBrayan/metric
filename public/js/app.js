/**
 * METRIC — Global Application Controller (Rendered by Vite)
 * Sidebar collapse state, mobile drawer, local storage persistence & toast notifications
 */

// Initialize state on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    // Check saved sidebar collapsed state
    const isCollapsed = localStorage.getItem('metric_sidebar_collapsed') === 'true';
    if (isCollapsed && window.innerWidth > 1024) {
        document.body.classList.add('sidebar-is-collapsed');
    }

    // Keyboard shortcut (Alt + S) to toggle sidebar
    document.addEventListener('keydown', (e) => {
        if (e.altKey && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            toggleSidebarCollapse();
        }
    });
});

/**
 * Toggle Sidebar Collapse / Expand Mode
 */
window.toggleSidebarCollapse = function() {
    document.body.classList.toggle('sidebar-is-collapsed');
    const isNowCollapsed = document.body.classList.contains('sidebar-is-collapsed');
    localStorage.setItem('metric_sidebar_collapsed', isNowCollapsed);
    triggerToast(isNowCollapsed ? 'Menú lateral contraído' : 'Menú lateral expandido', '📐');
};

/**
 * Toggle Mobile Drawer
 */
window.toggleSidebar = function() {
    const drawer = document.getElementById('sidebarDrawer');
    if (drawer) {
        drawer.classList.toggle('open');
    }
};

/**
 * Generic Live Table Search Filter
 * @param {string} inputId
 * @param {string} tableId
 */
window.searchLiveTableGeneric = function(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    const query = input.value.toLowerCase().trim();
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
};

/**
 * Set Navigation Active Element
 * @param {HTMLElement} element 
 * @param {string} sectionName 
 */
window.setActiveNav = function(element, sectionName) {
    document.querySelectorAll('.nav-item-btn').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
    const label = sectionName || element.getAttribute('data-tooltip') || element.textContent.trim();
    triggerToast('Sección activa: ' + label, '⚡');
};

/**
 * Trigger Global Glassmorphism Toast Notification
 * @param {string} text 
 * @param {string} icon 
 * @param {number} duration 
 */
let toastTimer;
window.triggerToast = function(text, icon = '✓', duration = 2500) {
    const toast = document.getElementById('wowToast');
    const toastText = document.getElementById('toastTextContent');
    const toastIcon = document.getElementById('toastIconSymbol');
    
    if (toast && toastText) {
        toastText.textContent = text;
        if (toastIcon) toastIcon.textContent = icon;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, duration);
    }
};

/**
 * Modern SweetAlert2 Confirmation Dialog
 */
window.metricConfirm = function({ title, text, icon = 'warning', confirmText = 'Sí, continuar', cancelText = 'Cancelar', isDanger = false }) {
    if (typeof Swal === 'undefined') {
        return Promise.resolve({ isConfirmed: confirm(text || title) });
    }

    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: {
            popup: 'metric-swal-popup',
            confirmButton: isDanger ? 'metric-swal-btn-danger' : 'metric-swal-btn-confirm',
            cancelButton: 'metric-swal-btn-cancel'
        },
        buttonsStyling: false,
        background: '#ffffff',
        iconColor: isDanger ? '#ef4444' : '#10b9df',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    });
};

/**
 * Modern SweetAlert2 Alert Message
 */
window.metricAlert = function({ title, text, icon = 'success', timer = 2500 }) {
    if (typeof Swal === 'undefined') {
        alert(text || title);
        return;
    }

    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        timer: timer,
        showConfirmButton: false,
        customClass: {
            popup: 'metric-swal-popup'
        },
        background: '#ffffff',
        iconColor: icon === 'error' ? '#ef4444' : (icon === 'warning' ? '#f59e0b' : '#10b9df')
    });
};
