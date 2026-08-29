/**
 * METRIC — Regions (Regionales) Management Controller (Rendered by Vite)
 * Interactive Modals & SweetAlert2 confirmation
 */

window.openCreateRegionModal = function() {
    const modal = document.getElementById('createRegionModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.openEditRegionModal = function(id, name, code, department, manager, address, status) {
    const modal = document.getElementById('editRegionModal');
    const form = document.getElementById('editRegionForm');
    if (!modal || !form) return;

    form.action = `/regionales/${id}`;
    document.getElementById('editRegionName').value = name;
    document.getElementById('editRegionCode').value = code;
    document.getElementById('editRegionDepartment').value = department;
    document.getElementById('editRegionManager').value = manager === '—' ? '' : manager;
    document.getElementById('editRegionAddress').value = address === '—' ? '' : address;

    const statusSelect = document.getElementById('editRegionStatus');
    if (statusSelect) {
        statusSelect.value = status || 'Operativo';
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
};

window.deleteRegion = function(id, name) {
    if (typeof window.metricConfirm === 'function') {
        window.metricConfirm({
            title: '¿Eliminar Sede Regional?',
            text: `¿Estás seguro de eliminar la sede regional ${name}?`,
            icon: 'warning',
            confirmText: 'Sí, eliminar',
            cancelText: 'Cancelar',
            isDanger: true
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteFormGlobal');
                if (form) {
                    form.action = `/regionales/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar la sede ${name}?`)) {
            const form = document.getElementById('deleteFormGlobal');
            if (form) {
                form.action = `/regionales/${id}`;
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

window.filterRegionsLive = function() {
    const input = document.getElementById('regionsSearchInput');
    if (!input) return;
    const query = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#regionsMasterTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
};
