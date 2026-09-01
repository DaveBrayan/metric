/**
 * METRIC — Companies (Empresas) Management Controller (Rendered by Vite)
 * Interactive Modals & SweetAlert2 confirmation
 */

window.openCreateCompanyModal = function() {
    const modal = document.getElementById('createCompanyModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.openEditCompanyModal = function(id, name, code, nit, contactPerson, email, phone, status) {
    const modal = document.getElementById('editCompanyModal');
    const form = document.getElementById('editCompanyForm');
    if (!modal || !form) return;

    form.action = `/empresas/${id}`;
    document.getElementById('editCompanyName').value = name;
    document.getElementById('editCompanyCode').value = code;
    document.getElementById('editCompanyNit').value = (nit === '—' || nit === null || nit === undefined) ? '' : nit;
    document.getElementById('editCompanyContact').value = contactPerson === '—' ? '' : contactPerson;
    document.getElementById('editCompanyEmail').value = email === '—' ? '' : email;
    document.getElementById('editCompanyPhone').value = phone === '—' ? '' : phone;

    const statusSelect = document.getElementById('editCompanyStatus');
    if (statusSelect) {
        statusSelect.value = (status === 'Activo') ? 'Activo' : 'Inactivo';
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

window.deleteCompany = function(id, name) {
    if (typeof window.metricConfirm === 'function') {
        window.metricConfirm({
            title: '¿Eliminar Empresa Cliente?',
            text: `¿Estás seguro de eliminar a la empresa ${name}? Se desvincularán sus proyectos asociados.`,
            icon: 'warning',
            confirmText: 'Sí, eliminar',
            cancelText: 'Cancelar',
            isDanger: true
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteFormGlobal');
                if (form) {
                    form.action = `/empresas/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar la empresa ${name}?`)) {
            const form = document.getElementById('deleteFormGlobal');
            if (form) {
                form.action = `/empresas/${id}`;
                form.submit();
            }
        }
    }
};

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.filterCompaniesLive = function() {
    const input = document.getElementById('companiesSearchInput');
    if (!input) return;
    const query = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#companiesMasterTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
};

