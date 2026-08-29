/**
 * METRIC — Administrators Management Scripts (Rendered by Vite)
 * Interactive modal controller, WhatsApp credential copier, permissions matrix & search
 */

let currentResetUser = {
    id: null,
    name: '',
    email: '',
    password: ''
};

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
 * Open Edit Administrator Modal
 */
window.openEditAdminModal = function(id, name, email, phone, role, status) {
    const modal = document.getElementById('adminEditModal');
    const form = document.getElementById('editAdminForm');
    if (!modal || !form) return;

    form.action = `/administradores/${id}`;
    document.getElementById('editAdminName').value = name;
    document.getElementById('editAdminEmail').value = email;
    document.getElementById('editAdminPhone').value = phone === '—' ? '' : phone;
    document.getElementById('editAdminRole').value = role;
    document.getElementById('editAdminStatus').value = status || 'online';

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

/**
 * Open Reset Password Modal & WhatsApp Card
 */
window.openResetPasswordModal = function(id, name, email) {
    const modal = document.getElementById('adminResetPasswordModal');
    const form = document.getElementById('resetPasswordForm');
    if (!modal || !form) return;

    form.action = `/administradores/${id}/reset-password`;

    currentResetUser.id = id;
    currentResetUser.name = name;
    currentResetUser.email = email;

    document.getElementById('resetModalName').textContent = name;
    document.getElementById('resetModalEmail').textContent = email;
    document.getElementById('resetModalInitial').textContent = name.charAt(0).toUpperCase();

    // Generar contraseña inicial
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

    currentResetUser.password = pass;
    const input = document.getElementById('resetNewPasswordInput');
    if (input) input.value = pass;

    updateWhatsAppPreview();
};

/**
 * Update WhatsApp text live when typing
 */
document.addEventListener('DOMContentLoaded', () => {
    const passInput = document.getElementById('resetNewPasswordInput');
    if (passInput) {
        passInput.addEventListener('input', (e) => {
            currentResetUser.password = e.target.value;
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
👤 *Usuario:* ${currentResetUser.email}
🔑 *Contraseña:* ${currentResetUser.password}
🌐 *Enlace:* ${loginUrl}
━━━━━━━━━━━━━━━━━━━━━
⚠️ _Recomendamos ingresar y actualizar su contraseña._`;

    preview.textContent = text;
}

/**
 * Copy WhatsApp formatted credentials to clipboard
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
            window.triggerToast('¡Credenciales copiadas al portapapeles! Listas para enviar por WhatsApp', '📋');
        }
    }).catch(() => {
        alert('Copia el siguiente texto manualmente:\n\n' + textToCopy);
    });
};

/**
 * Open Permissions Matrix Modal
 */
window.openPermissionsModal = function(id, name, role, currentPermissions) {
    const modal = document.getElementById('adminPermissionsModal');
    const form = document.getElementById('permissionsForm');
    if (!modal || !form) return;

    form.action = `/administradores/${id}/permissions`;

    document.getElementById('permModalName').textContent = name;
    document.getElementById('permModalRole').textContent = 'Rol: ' + role;
    document.getElementById('permModalInitial').textContent = name.charAt(0).toUpperCase();

    // Normalizar lista de permisos
    const perms = Array.isArray(currentPermissions) ? currentPermissions : [];
    const checkboxes = form.querySelectorAll('input[type="checkbox"][name="permissions[]"]');

    checkboxes.forEach(cb => {
        // Verificar coincidencia exacta o coincidencia general
        const isChecked = perms.includes(cb.value) || perms.includes('Control Total') || perms.includes('Superadministrador');
        cb.checked = isChecked;
        updateCheckboxParentStyle(cb);
    });

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

/**
 * Quick toggle all permissions (Conceder Todo)
 */
window.toggleAllPermissions = function(checked) {
    const form = document.getElementById('permissionsForm');
    if (!form) return;

    const checkboxes = form.querySelectorAll('input[type="checkbox"][name="permissions[]"]');
    checkboxes.forEach(cb => {
        cb.checked = checked;
        updateCheckboxParentStyle(cb);
    });

    if (typeof window.triggerToast === 'function') {
        window.triggerToast(checked ? 'Todos los permisos marcados' : 'Permisos desmarcados', '⚡');
    }
};

/**
 * Quick set Read-Only Permissions
 */
window.setReadOnlyPermissions = function() {
    const form = document.getElementById('permissionsForm');
    if (!form) return;

    const checkboxes = form.querySelectorAll('input[type="checkbox"][name="permissions[]"]');
    checkboxes.forEach(cb => {
        const isViewOnly = cb.value.includes('Ver') || cb.value.includes('Reportes');
        cb.checked = isViewOnly;
        updateCheckboxParentStyle(cb);
    });

    if (typeof window.triggerToast === 'function') {
        window.triggerToast('Permisos ajustados a Solo Lectura', '👁️');
    }
};

function updateCheckboxParentStyle(cb) {
    const parent = cb.closest('.perm-toggle-box');
    if (parent) {
        if (cb.checked) {
            parent.classList.add('checked');
        } else {
            parent.classList.remove('checked');
        }
    }
}

document.addEventListener('change', (e) => {
    if (e.target && e.target.matches('input[type="checkbox"][name="permissions[]"]')) {
        updateCheckboxParentStyle(e.target);
    }
});

/**
 * Open Delete Modal
 */
window.openDeleteAdminModal = function(id, name) {
    const modal = document.getElementById('adminDeleteModal');
    const form = document.getElementById('deleteAdminForm');
    if (!modal || !form) return;

    form.action = `/administradores/${id}`;
    document.getElementById('deleteAdminName').textContent = name;

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

/**
 * Close any modal by ID
 */
window.closeAdminModal = function(modalId) {
    const modal = document.getElementById(modalId || 'adminCreateModal');
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
 */
window.filterAdminsByRole = function(roleName, btn) {
    document.querySelectorAll('.admins-role-filter-group .role-filter-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const rows = document.querySelectorAll('#adminsMasterTable tbody tr');
    rows.forEach(row => {
        if (roleName === 'all') {
            row.style.display = '';
        } else {
            const role = row.getAttribute('data-role') || '';
            const match = role.toLowerCase().includes(roleName.toLowerCase());
            row.style.display = match ? '' : 'none';
        }
    });
};
