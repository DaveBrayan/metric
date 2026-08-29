/**
 * METRIC — Global Application Script
 * Manages Sidebar collapse, mobile drawer, local storage persistence & toast notifications
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
function toggleSidebarCollapse() {
    document.body.classList.toggle('sidebar-is-collapsed');
    const isNowCollapsed = document.body.classList.contains('sidebar-is-collapsed');
    localStorage.setItem('metric_sidebar_collapsed', isNowCollapsed);
    triggerToast(isNowCollapsed ? 'Menú lateral contraído' : 'Menú lateral expandido', '📐');
}

/**
 * Toggle Mobile Drawer
 */
function toggleSidebar() {
    const drawer = document.getElementById('sidebarDrawer');
    if (drawer) {
        drawer.classList.toggle('open');
    }
}

/**
 * Set Navigation Active Element
 * @param {HTMLElement} element 
 * @param {string} sectionName 
 */
function setActiveNav(element, sectionName) {
    document.querySelectorAll('.nav-item-btn').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
    const label = sectionName || element.getAttribute('data-tooltip') || element.textContent.trim();
    triggerToast('Sección activa: ' + label, '⚡');
}

/**
 * Trigger Global Glassmorphism Toast Notification
 * @param {string} text 
 * @param {string} icon 
 * @param {number} duration 
 */
let toastTimer;
function triggerToast(text, icon = '✓', duration = 2500) {
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
}
