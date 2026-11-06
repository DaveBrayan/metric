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

/* ==========================================================================
   METRIC v2 Universal Photo Viewer with Navigation Arrows & Keyboard Support
   ========================================================================== */
let globalViewerImages = [];
let globalViewerIndex = 0;

function formatPhotoUrl(src) {
    if (!src || typeof src !== 'string') return '';
    src = src.trim();
    if (src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:') || src.startsWith('blob:')) {
        return src;
    }
    if (src.startsWith('img_')) {
        return 'https://pachabol.com/metric/upload/' + src;
    }
    if (src.startsWith('/')) {
        return src;
    }
    if (src.startsWith('uploads/')) {
        return '/' + src;
    }
    return '/uploads/measurements/' + src;
}

window.formatPhotoUrl = formatPhotoUrl;

const universalOpenPhotoViewer = function(images, p2 = 'Fotografía del Punto', p3 = 0) {
    if (!images) return;

    let pointTitle = 'Fotografía del Punto';
    let startIndex = 0;

    if (typeof p2 === 'number') {
        startIndex = p2;
        if (typeof p3 === 'string' && p3.trim()) pointTitle = p3;
    } else {
        if (typeof p2 === 'string' && p2.trim()) pointTitle = p2;
        if (typeof p3 === 'number') startIndex = p3;
    }
    
    if (typeof images === 'string') {
        try {
            const parsed = JSON.parse(images);
            globalViewerImages = Array.isArray(parsed) ? parsed : [images];
        } catch (e) {
            globalViewerImages = [images];
        }
    } else if (Array.isArray(images)) {
        globalViewerImages = images.filter(Boolean);
    } else {
        globalViewerImages = [];
    }

    // Aplanar arrays anidados si vinieron [["url"]] y normalizar URLs
    globalViewerImages = globalViewerImages.flat(Infinity).filter(Boolean).map(formatPhotoUrl);

    if (globalViewerImages.length === 0) return;

    globalViewerIndex = Math.max(0, Math.min(startIndex, globalViewerImages.length - 1));

    const modal = document.getElementById('photoViewerModal');
    const titleEl = document.getElementById('photoViewerTitle');
    if (titleEl) titleEl.textContent = pointTitle;

    renderGlobalPhotoViewer();

    if (modal) {
        modal.classList.add('open', 'active');
        modal.style.display = 'flex';
    }
};

window.universalOpenPhotoViewer = universalOpenPhotoViewer;
window.openPhotoViewer = universalOpenPhotoViewer;

try {
    Object.defineProperty(window, 'openPhotoViewer', {
        get: function() { return universalOpenPhotoViewer; },
        set: function() { /* Evitar que scripts secundarios lo sobreescriban */ },
        configurable: true
    });
} catch (e) {
    window.openPhotoViewer = universalOpenPhotoViewer;
}

window.openPhotoViewerModal = function(images, p2 = 0, p3 = 'Fotografía de Medición') {
    if (typeof p2 === 'string' && typeof p3 === 'number') {
        window.openPhotoViewer(images, p2, p3);
    } else if (typeof p2 === 'number') {
        window.openPhotoViewer(images, p3, p2);
    } else {
        window.openPhotoViewer(images, p2 || 'Fotografía de Medición', 0);
    }
};

function universalClosePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) {
        modal.classList.remove('open', 'active', 'show');
        modal.style.removeProperty('display');
        modal.style.display = 'none';
        
        const mainImg = document.getElementById('photoViewerImg') || 
                        document.getElementById('photoViewerMainImage') || 
                        document.getElementById('mainViewerImage') || 
                        document.getElementById('photoViewerImage');
        if (mainImg) mainImg.src = '';
    }
    document.body.style.removeProperty('overflow');
    document.body.style.overflow = '';
}
window.universalClosePhotoViewer = universalClosePhotoViewer;

try {
    Object.defineProperty(window, 'closePhotoViewer', {
        get: function() { return universalClosePhotoViewer; },
        set: function() { /* Evitar que scripts secundarios lo sobreescriban */ },
        configurable: true
    });
    Object.defineProperty(window, 'closePhotoViewerModal', {
        get: function() { return universalClosePhotoViewer; },
        set: function() { /* Evitar que scripts secundarios lo sobreescriban */ },
        configurable: true
    });
} catch (e) {
    window.closePhotoViewer = universalClosePhotoViewer;
    window.closePhotoViewerModal = universalClosePhotoViewer;
}

window.navigatePhotoViewer = function(delta) {
    if (globalViewerImages.length <= 1) return;
    globalViewerIndex = (globalViewerIndex + delta + globalViewerImages.length) % globalViewerImages.length;
    renderGlobalPhotoViewer();
};

window.selectGlobalPhotoViewerIndex = function(index) {
    if (index >= 0 && index < globalViewerImages.length) {
        globalViewerIndex = index;
        renderGlobalPhotoViewer();
    }
};

function renderGlobalPhotoViewer() {
    const imgEl = document.getElementById('photoViewerImg') || 
                  document.getElementById('photoViewerMainImage') || 
                  document.getElementById('mainViewerImage') || 
                  document.getElementById('photoViewerImage');
    const counterEl = document.getElementById('photoViewerCounter');
    const prevBtn = document.getElementById('photoViewerPrevBtn');
    const nextBtn = document.getElementById('photoViewerNextBtn');
    const stripEl = document.getElementById('photoViewerThumbnailsStrip') || document.getElementById('photoThumbnailsRow');

    const total = globalViewerImages.length;
    const currentSrc = globalViewerImages[globalViewerIndex];

    if (imgEl) {
        imgEl.src = currentSrc;
        imgEl.style.display = 'block';
    }

    if (counterEl) {
        if (total > 1) {
            counterEl.textContent = `Foto ${globalViewerIndex + 1} de ${total}`;
            counterEl.style.display = 'inline-block';
        } else {
            counterEl.textContent = '';
            counterEl.style.display = 'none';
        }
    }

    if (prevBtn) prevBtn.style.display = total > 1 ? 'grid' : 'none';
    if (nextBtn) nextBtn.style.display = total > 1 ? 'grid' : 'none';

    if (stripEl) {
        if (total > 1) {
            stripEl.style.display = 'flex';
            stripEl.innerHTML = globalViewerImages.map((src, i) => `
                <div class="viewer-thumb-item ${i === globalViewerIndex ? 'active' : ''}"
                     onclick="selectGlobalPhotoViewerIndex(${i})"
                     title="Ver foto ${i + 1}"
                     style="width: 52px; height: 52px; border-radius: 6px; overflow: hidden; cursor: pointer; border: 2px solid ${i === globalViewerIndex ? '#10b9df' : '#475569'}; flex-shrink: 0; transition: all 0.2s ease;">
                    <img src="${src}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>
            `).join('');
        } else {
            stripEl.innerHTML = '';
            stripEl.style.display = 'none';
        }
    }
}

// Global Keyboard Navigation (Esc, ArrowLeft, ArrowRight)
document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('photoViewerModal');
    if (modal && (modal.classList.contains('open') || modal.classList.contains('active') || modal.style.display === 'flex')) {
        if (e.key === 'Escape') {
            e.preventDefault();
            universalClosePhotoViewer();
        }
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            navigatePhotoViewer(-1);
        }
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            navigatePhotoViewer(1);
        }
    }
});

// Global Event Delegation for clicking backdrop or close buttons (Capture Phase)
document.addEventListener('click', (e) => {
    const modal = document.getElementById('photoViewerModal');
    if (!modal) return;
    const isVisible = modal.classList.contains('open') || 
                      modal.classList.contains('active') || 
                      modal.style.display === 'flex';
    if (!isVisible) return;

    // Click on backdrop directly
    if (e.target === modal) {
        universalClosePhotoViewer();
        return;
    }

    // Click on close button inside photoViewerModal
    const closeBtn = e.target.closest('#photoViewerModal .btn-close-modal, #photoViewerModal [aria-label="Cerrar"], #photoViewerModal [title*="Cerrar"], #photoViewerModal .close-modal-btn');
    if (closeBtn) {
        e.preventDefault();
        e.stopPropagation();
        universalClosePhotoViewer();
    }
}, true);

