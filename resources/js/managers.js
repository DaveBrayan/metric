/**
 * METRIC — Managers (Responsables) Management Controller (Rendered by Vite)
 * Interactive modals, WhatsApp credential generation & SweetAlert2 confirmation
 */

let currentResetManager = {
    id: null,
    name: '',
    email: '',
    password: ''
};

/**
 * Open Create Manager Modal
 */
window.openCreateManagerModal = function() {
    const modal = document.getElementById('createManagerModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

/**
 * Open Edit Manager Modal
 */
window.openEditManagerModal = function(id, name, email, phone, position, companyId, status) {
    const modal = document.getElementById('editManagerModal');
    const form = document.getElementById('editManagerForm');
    if (!modal || !form) return;

    form.action = `/responsables/${id}`;
    document.getElementById('editManagerName').value = name;
    document.getElementById('editManagerEmail').value = email;
    document.getElementById('editManagerPhone').value = phone === '—' ? '' : phone;
    document.getElementById('editManagerPosition').value = position;
    
    const companySelect = document.getElementById('editManagerCompany');
    if (companySelect && companyId) {
        companySelect.value = companyId;
    }

    const statusSelect = document.getElementById('editManagerStatus');
    if (statusSelect) {
        statusSelect.value = (status === 'Activo') ? 'Activo' : 'Inactivo';
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

/**
 * Open Reset Password Modal & WhatsApp Card
 */
window.openResetPasswordModal = function(id, name, email) {
    const modal = document.getElementById('resetPasswordModal');
    const form = document.getElementById('resetPasswordForm');
    if (!modal || !form) return;

    form.action = `/responsables/${id}/reset-password`;

    currentResetManager.id = id;
    currentResetManager.name = name;
    currentResetManager.email = email;

    document.getElementById('resetModalName').textContent = name;
    document.getElementById('resetModalEmail').textContent = email;
    document.getElementById('resetModalInitial').textContent = name.charAt(0).toUpperCase();

    generateRandomPassword();

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

/**
 * Generate Secure Random Password
 */
window.generateRandomPassword = function() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789#@!';
    let pass = 'Metric' + new Date().getFullYear() + '#';
    for (let i = 0; i < 4; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    currentResetManager.password = pass;
    const input = document.getElementById('resetNewPasswordInput');
    if (input) input.value = pass;

    updateWhatsAppPreview();
};

document.addEventListener('DOMContentLoaded', () => {
    const passInput = document.getElementById('resetNewPasswordInput');
    if (passInput) {
        passInput.addEventListener('input', (e) => {
            currentResetManager.password = e.target.value;
            updateWhatsAppPreview();
        });
    }
});

function updateWhatsAppPreview() {
    const preview = document.getElementById('whatsappCredentialText');
    if (!preview) return;

    const loginUrl = window.location.origin + '/login';
    const text = 
`*🔐 Credenciales de Acceso — METRIC V2*
━━━━━━━━━━━━━━━━━━━━━
👤 *Usuario:* ${currentResetManager.email}
🔑 *Contraseña:* ${currentResetManager.password}
🌐 *Enlace:* ${loginUrl}
━━━━━━━━━━━━━━━━━━━━━
⚠️ _Recomendamos ingresar y actualizar su contraseña._`;

    preview.textContent = text;
}

/**
 * Copy WhatsApp credentials to clipboard
 */
window.copyWhatsAppCredentials = function() {
    const preview = document.getElementById('whatsappCredentialText');
    if (!preview) return;

    const textToCopy = preview.textContent.trim();

    navigator.clipboard.writeText(textToCopy).then(() => {
        const btnText = document.getElementById('copyCredentialBtnText');
        if (btnText) {
            const original = btnText.textContent;
            btnText.textContent = '¡Copiado para WhatsApp! ✓';
            setTimeout(() => {
                btnText.textContent = original;
            }, 2500);
        }

        if (typeof window.triggerToast === 'function') {
            window.triggerToast('¡Credenciales copiadas para enviar por WhatsApp!', '📋');
        }
    }).catch(() => {
        alert('Copia el texto manualmente:\n\n' + textToCopy);
    });
};

/**
 * Delete Manager with SweetAlert2
 */
window.deleteManager = function(id, name) {
    if (typeof window.metricConfirm === 'function') {
        window.metricConfirm({
            title: '¿Eliminar Responsable?',
            text: `¿Estás seguro de eliminar permanentemente a ${name}? Se revocarán todos sus accesos al sistema.`,
            icon: 'warning',
            confirmText: 'Sí, eliminar',
            cancelText: 'Cancelar',
            isDanger: true
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteFormGlobal');
                if (form) {
                    form.action = `/responsables/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar al responsable ${name}?`)) {
            const form = document.getElementById('deleteFormGlobal');
            if (form) {
                form.action = `/responsables/${id}`;
                form.submit();
            }
        }
    }
};

/**
 * Close any modal
 */
window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

/**
 * Filter Table Live
 */
window.filterManagersLive = function() {
    const input = document.getElementById('managersSearchInput');
    if (!input) return;
    const query = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#managersMasterTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
};
