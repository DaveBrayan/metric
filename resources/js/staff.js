/**
 * METRIC — Staff (Personal Técnico) Management Controller (Rendered by Vite)
 * Interactive modals, auto-generated passwords, WhatsApp modal in light mode & confirmations
 * UI/UX Pro Max Standard: High responsiveness, zero emojis in UI controls
 */

let currentStaffId = null;
let currentCredentials = {
    name: '',
    email: '',
    password: '',
    position: '',
    login_url: ''
};

/**
 * Función universal y robusta para copiar al portapapeles.
 * Funciona en HTTPS, HTTP, IPs locales, navegadores de escritorio y móviles.
 */
window.copyToClipboard = function(text, successMsg = 'Copiado al portapapeles') {
    if (!text) return Promise.resolve(false);
    const textToCopy = String(text).trim();

    return new Promise((resolve) => {
        // Intentar navigator.clipboard si está disponible y en contexto seguro
        if (navigator.clipboard && window.isSecureContext && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(textToCopy)
                .then(() => {
                    showMiniFeedback(successMsg);
                    resolve(true);
                })
                .catch(() => {
                    const fallbackOk = executeFallbackCopy(textToCopy, successMsg);
                    resolve(fallbackOk);
                });
        } else {
            const fallbackOk = executeFallbackCopy(textToCopy, successMsg);
            resolve(fallbackOk);
        }
    });
};

/**
 * Fallback usando textarea con execCommand('copy')
 */
function executeFallbackCopy(text, successMsg) {
    let textArea = null;
    let succeeded = false;

    try {
        textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '-9999px';
        textArea.style.width = '2em';
        textArea.style.height = '2em';
        textArea.style.padding = '0';
        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';
        textArea.style.background = 'transparent';
        textArea.setAttribute('readonly', '');

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        textArea.setSelectionRange(0, text.length);

        succeeded = document.execCommand('copy');
    } catch (e) {
        succeeded = false;
    } finally {
        if (textArea && textArea.parentNode) {
            document.body.removeChild(textArea);
        }
    }

    if (succeeded) {
        showMiniFeedback(successMsg);
        return true;
    } else {
        // Si aún fallara, intentar seleccionar el textarea del modal si está visible
        const box = document.getElementById('credWhatsAppText');
        if (box) {
            box.focus();
            box.select();
            showMiniFeedback('Texto seleccionado. Presiona Ctrl+C para copiar');
        } else {
            showMiniFeedback('Copia el texto manualmente');
        }
        return false;
    }
}

/**
 * Abrir Modal: Alta de Nuevo Colaborador
 */
window.openCreateStaffModal = function() {
    const modal = document.getElementById('createStaffModal');
    const form = document.getElementById('createStaffForm');
    if (!modal) return;

    if (form) {
        form.reset();
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    // Autofoco en el primer campo
    const firstInput = document.getElementById('createStaffFirstName');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 150);
    }
};

/**
 * Enviar formulario de nuevo colaborador vía AJAX para abrir directamente
 * el modal de Credenciales & WhatsApp con la contraseña generada
 */
window.handleCreateStaffSubmit = function(event) {
    event.preventDefault();
    const form = event.target;
    const btnSubmit = document.getElementById('btnSubmitCreateStaff');
    const originalBtnHtml = btnSubmit ? btnSubmit.innerHTML : 'Guardar Personal';

    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span>Generando acceso...</span>';
    }

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
        }

        // 1. Cerrar el modal de creación
        window.closeModal('createStaffModal');

        // 2. Abrir inmediatamente el modal en blanco con las credenciales
        if (data.credentials) {
            window.openCredentialsModal(data.credentials);
        }

        showMiniFeedback('¡Colaborador registrado exitosamente!');

        // 3. Recargar la tabla al cerrar el modal de credenciales
        window.__needReloadOnClose = true;
    })
    .catch(error => {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
        }
        let msg = 'Error al registrar el colaborador.';
        if (error.errors) {
            msg = Object.values(error.errors).flat().join('\n');
        } else if (error.message) {
            msg = error.message;
        }
        alert(msg);
    });
};

/**
 * Abrir Modal: Editar Colaborador
 */
window.openEditStaffModal = function(id, firstName, lastName, email, status) {
    const modal = document.getElementById('editStaffModal');
    const form = document.getElementById('editStaffForm');
    if (!modal || !form) return;

    form.action = `/personal/${id}`;
    
    const fnInput = document.getElementById('editStaffFirstName');
    const lnInput = document.getElementById('editStaffLastName');
    const emailInput = document.getElementById('editStaffEmail');
    const statusSelect = document.getElementById('editStaffStatus');

    if (fnInput) fnInput.value = firstName || '';
    if (lnInput) lnInput.value = lastName || '';
    if (emailInput) emailInput.value = email || '';
    if (statusSelect) {
        statusSelect.value = (status === 'activo' || status === 'online') ? 'activo' : 'inactivo';
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

/**
 * Enviar formulario de edición vía AJAX
 */
window.handleEditStaffSubmit = function(event) {
    event.preventDefault();
    const form = event.target;
    const btnSubmit = document.getElementById('btnSubmitEditStaff');
    const originalBtnHtml = btnSubmit ? btnSubmit.innerHTML : 'Guardar Cambios';

    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span>Guardando...</span>';
    }

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
        }
        window.closeModal('editStaffModal');
        showMiniFeedback('Colaborador actualizado correctamente');
        setTimeout(() => window.location.reload(), 600);
    })
    .catch(error => {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
        }
        let msg = 'Error al actualizar el colaborador.';
        if (error.errors) {
            msg = Object.values(error.errors).flat().join('\n');
        } else if (error.message) {
            msg = error.message;
        }
        alert(msg);
    });
};

/**
 * Preguntar confirmación antes de restablecer la contraseña.
 * Si el usuario confirma, regenera la contraseña y abre el modal en blanco para WhatsApp.
 */
window.confirmResetPassword = function(id, name) {
    const questionTitle = '¿Restablecer contraseña?';
    const questionText = `¿Estás seguro de generar una nueva contraseña para ${name}? Se creará un nuevo acceso y podrás copiarlo para enviarlo por WhatsApp.`;

    if (typeof window.metricConfirm === 'function') {
        window.metricConfirm({
            title: questionTitle,
            text: questionText,
            icon: 'question',
            confirmText: 'Sí, restablecer',
            cancelText: 'Cancelar',
            isDanger: false
        }).then(result => {
            if (result.isConfirmed) {
                executeResetPassword(id, name);
            }
        });
    } else {
        if (confirm(`${questionTitle}\n\n${questionText}`)) {
            executeResetPassword(id, name);
        }
    }
};

/**
 * Ejecuta el restablecimiento en el backend y abre el modal de WhatsApp en blanco
 */
function executeResetPassword(id, name) {
    showMiniFeedback('Generando nueva contraseña...');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value;

    fetch(`/personal/${id}/reset-password`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.credentials) {
            window.openCredentialsModal(data.credentials);
            showMiniFeedback(`¡Nueva contraseña generada para ${name}!`);
            window.__needReloadOnClose = true;
        } else {
            alert('No se pudo restablecer la contraseña.');
        }
    })
    .catch(error => {
        alert('Error al restablecer la contraseña.');
    });
}

/**
 * Abrir Modal: Credenciales de Acceso & WhatsApp (En Modo Claro / Blanco)
 */
window.openCredentialsModal = function(creds) {
    const modal = document.getElementById('credentialsWhatsAppModal');
    if (!modal || !creds) return;

    currentStaffId = creds.id || null;
    currentCredentials = {
        name: creds.name || 'Colaborador',
        email: creds.email || '',
        password: creds.password || 'Metric2026*',
        position: creds.position || 'Técnico de Campo',
        login_url: creds.login_url || (window.location.origin + '/login')
    };

    // Actualizar encabezados y datos en la tarjeta blanca
    const nameEl = document.getElementById('credModalName');
    const initialEl = document.getElementById('credModalInitial');
    const posEl = document.getElementById('credModalPosition');
    const emailEl = document.getElementById('credModalEmail');
    const passEl = document.getElementById('credModalPassword');
    const whatsAppTextEl = document.getElementById('credWhatsAppText');
    const copyBtn = document.getElementById('btnCopyWhatsAppCred');
    const copyBtnText = document.getElementById('btnCopyWhatsAppText');

    if (nameEl) nameEl.textContent = currentCredentials.name;
    if (initialEl) initialEl.textContent = currentCredentials.name.charAt(0).toUpperCase();
    if (posEl) posEl.textContent = currentCredentials.position;
    if (emailEl) emailEl.textContent = currentCredentials.email;
    if (passEl) passEl.textContent = currentCredentials.password;

    // Generar texto limpio formateado para WhatsApp (sin enlace, con indicación de la app móvil)
    const formattedText = 
`*🔐 Credenciales de Acceso — METRIC V2*
━━━━━━━━━━━━━━━━━━━━━
👤 *Usuario:* ${currentCredentials.email}
🔑 *Contraseña:* ${currentCredentials.password}
📱 *Acceso:* Descarga o inicia sesión en la aplicación móvil METRIC.
━━━━━━━━━━━━━━━━━━━━━
⚠️ _Recomendamos ingresar con estas credenciales y no compartirlas._`;

    if (whatsAppTextEl) {
        if (whatsAppTextEl.tagName === 'TEXTAREA' || whatsAppTextEl.tagName === 'INPUT') {
            whatsAppTextEl.value = formattedText;
        } else {
            whatsAppTextEl.textContent = formattedText;
        }
    }

    if (copyBtn) {
        copyBtn.classList.remove('copied');
    }
    if (copyBtnText) {
        copyBtnText.textContent = 'Copiar Texto para WhatsApp';
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

/**
 * Copiar el mensaje formateado completo para WhatsApp al portapapeles.
 * 100% garantizado con selección visual y fallback robusto.
 */
window.copyWhatsAppMessage = function() {
    const textEl = document.getElementById('credWhatsAppText');
    const copyBtn = document.getElementById('btnCopyWhatsAppCred');
    const copyBtnText = document.getElementById('btnCopyWhatsAppText');
    if (!textEl) return;

    let textToCopy = '';
    if (textEl.tagName === 'TEXTAREA' || textEl.tagName === 'INPUT') {
        textToCopy = textEl.value.trim();
        textEl.focus();
        textEl.select();
        textEl.setSelectionRange(0, textToCopy.length);
    } else {
        textToCopy = textEl.textContent.trim();
    }

    if (!textToCopy) {
        showMiniFeedback('No hay texto para copiar');
        return;
    }

    window.copyToClipboard(textToCopy, '¡Texto copiado para WhatsApp!').then(() => {
        if (copyBtn) copyBtn.classList.add('copied');
        if (copyBtnText) copyBtnText.textContent = '¡Copiado para WhatsApp! ✓';

        setTimeout(() => {
            if (copyBtn) copyBtn.classList.remove('copied');
            if (copyBtnText) copyBtnText.textContent = 'Copiar Texto para WhatsApp';
        }, 3000);
    });
};

/**
 * Muestra un toast sutil y accesible
 */
function showMiniFeedback(message) {
    if (typeof window.triggerToast === 'function') {
        window.triggerToast(message);
        return;
    }

    let toast = document.getElementById('metricFloatingToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'metricFloatingToast';
        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.35);
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0;
            transform: translateY(12px);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        `;
        document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    clearTimeout(window.__toastTimeout);
    window.__toastTimeout = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(12px)';
    }, 2500);
}

/**
 * Eliminar Colaborador
 */
window.deleteStaff = function(id, name) {
    const message = `¿Estás seguro de eliminar a ${name}? Esta acción revocará su acceso.`;
    
    if (typeof window.metricConfirm === 'function') {
        window.metricConfirm({
            title: '¿Eliminar Colaborador?',
            text: message,
            icon: 'warning',
            confirmText: 'Sí, eliminar',
            cancelText: 'Cancelar',
            isDanger: true
        }).then(result => {
            if (result.isConfirmed) {
                submitDelete(id);
            }
        });
    } else {
        if (confirm(message)) {
            submitDelete(id);
        }
    }

    function submitDelete(staffId) {
        const form = document.getElementById('deleteFormGlobal');
        if (form) {
            form.action = `/personal/${staffId}`;
            form.submit();
        }
    }
};

/**
 * Cerrar cualquier modal
 */
window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Si cerramos el modal de credenciales después de un reseteo o creación, recargar la lista
    if (modalId === 'credentialsWhatsAppModal' && window.__needReloadOnClose) {
        window.__needReloadOnClose = false;
        window.location.reload();
    }
};

/**
 * Filtrar tabla de personal en tiempo real
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
