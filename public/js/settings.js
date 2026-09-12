/**
 * METRIC — Settings Page Scripts (Rendered by Vite)
 * Dynamic tab switching, API key copying & form notifications
 */

/**
 * Switch Active Settings Tab
 * @param {string} tabId 
 * @param {HTMLElement} btn 
 */
window.switchSettingsTab = function(tabId, btn) {
    document.querySelectorAll('.settings-tabs-bar .tab-nav-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.settings-tab-pane').forEach(p => p.classList.remove('active'));

    if (btn) btn.classList.add('active');
    const pane = document.getElementById(tabId);
    if (pane) pane.classList.add('active');
};

/**
 * Copy API Key to Clipboard
 * @param {string} text 
 */
window.copyApiKey = function(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            if (typeof window.triggerToast === 'function') {
                window.triggerToast('Llave API copiada al portapapeles', '🔑');
            }
        });
    } else {
        if (typeof window.triggerToast === 'function') {
            window.triggerToast('Llave: ' + text.substring(0, 15) + '...', '🔑');
        }
    }
};

/**
 * Regenerate API Key confirmation
 */
window.regenerateApiKey = function() {
    if (confirm('¿Estás seguro de regenerar la llave API? Los sistemas conectados deberán actualizar sus credenciales.')) {
        if (typeof window.triggerToast === 'function') {
            window.triggerToast('Generando nueva llave API...', '⚡');
        }
    }
};
