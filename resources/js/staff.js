/**
 * METRIC — Staff (Personal Técnico) Management Controller (Rendered by Vite)
 * Interactive modals, WhatsApp credential generation & SweetAlert2 confirmation
 */

let currentResetStaff = {
    id: null,
    name: '',
    email: '',
    password: ''
};

/**
 * Open Create Staff Modal
 */
window.openCreateStaffModal = function() {
    const modal = document.getElementById('createStaffModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

/**
 * Open Edit Staff Modal
 */
window.openEditStaffModal = function(id, name, email, phone, department, position, status) {
    const modal = document.getElementById('editStaffModal');
    const form = document.getElementById('editStaffForm');
    if (!modal || !form) return;

    form.action = `/personal/${id}`;
    document.getElementById('editStaffName').value = name;
    document.getElementById('editStaffEmail').value = email;
    document.getElementById('editStaffPhone').value = phone === '—' ? '' : phone;
    document.getElementById('editStaffDepartment').value = department;
    document.getElementById('editStaffPosition').value = position;

    const statusSelect = document.getElementById('editStaffStatus');
    if (statusSelect) {
        statusSelect.value = (status === 'online') ? 'online' : 'offline';
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

    form.action = `/personal/${id}/reset-password`;

    currentResetStaff.id = id;
    currentResetStaff.name = name;
    currentResetStaff.email = email;

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

    currentResetStaff.password = pass;
    const input = document.getElementById('resetNewPasswordInput');
    if (input) input.value = pass;

    updateWhatsAppPreview();
};

document.addEventListener('DOMContentLoaded', () => {
    const passInput = document.getElementById('resetNewPasswordInput');
    if (passInput) {
        passInput.addEventListener('input', (e) => {
            currentResetStaff.password = e.target.value;
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
👤 *Usuario:* ${currentResetStaff.email}
🔑 *Contraseña:* ${currentResetStaff.password}
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
 * Delete Staff with SweetAlert2
 */
window.deleteStaff = function(id, name) {
    if (typeof window.metricConfirm === 'function') {
        window.metricConfirm({
            title: '¿Eliminar Colaborador?',
            text: `¿Estás seguro de revocar el acceso y eliminar permanentemente a ${name}?`,
            icon: 'warning',
            confirmText: 'Sí, eliminar',
            cancelText: 'Cancelar',
            isDanger: true
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteFormGlobal');
                if (form) {
                    form.action = `/personal/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar a ${name}?`)) {
            const form = document.getElementById('deleteFormGlobal');
            if (form) {
                form.action = `/personal/${id}`;
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
window.filterStaffLive = function() {
    const input = document.getElementById('staffSearchInput');
    if (!input) return;
    const query = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#staffMasterTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
};
