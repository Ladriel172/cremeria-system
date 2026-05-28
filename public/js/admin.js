/**
 * Cremería Francis - Admin JS
 * Sidebar, toasts, utilidades globales
 */

/* ============================================================
   SIDEBAR
   ============================================================ */
(function initSidebar() {
    const sidebar   = document.getElementById('sidebar');
    const toggle    = document.getElementById('toggleSidebar');
    const mainContent = document.querySelector('.main-content');

    if (!sidebar || !toggle) return;

    // Restaurar estado guardado
    if (localStorage.getItem('sidebarCollapsed') === '1') {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');
        const collapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
    });

    // Marcar link activo
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        const href = link.getAttribute('href') || '';
        if (href && currentPath.endsWith(href.split('/').pop()) && href !== '#') {
            link.classList.add('active');
        }
    });
})();

/* ============================================================
   TOAST NOTIFICATIONS
   ============================================================ */
window.Toast = (function () {
    const icons = {
        success: 'bi-check-circle-fill',
        error:   'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info:    'bi-info-circle-fill',
    };

    function show(type, title, message, duration = 4000) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const item = document.createElement('div');
        item.className = `toast-item ${type}`;
        item.innerHTML = `
            <i class="bi ${icons[type] || icons.info} toast-icon"></i>
            <div class="toast-content">
                <div class="toast-title">${escapeHtml(title)}</div>
                ${message ? `<div class="toast-msg">${escapeHtml(message)}</div>` : ''}
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="bi bi-x"></i>
            </button>`;

        container.appendChild(item);

        setTimeout(() => {
            item.classList.add('leaving');
            setTimeout(() => item.remove(), 320);
        }, duration);
    }

    return {
        success: (t, m, d) => show('success', t, m, d),
        error:   (t, m, d) => show('error',   t, m, d),
        warning: (t, m, d) => show('warning', t, m, d),
        info:    (t, m, d) => show('info',    t, m, d),
    };
})();

/* ============================================================
   UTILIDADES
   ============================================================ */
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatMoney(amount) {
    return '$' + parseFloat(amount || 0).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}

/* ============================================================
   BÚSQUEDA EN TABLAS (filter en client-side)
   ============================================================ */
function initTableSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const tbody = document.querySelector(`#${tableId} tbody`);
    if (!input || !tbody) return;

    input.addEventListener('input', () => {
        const q = input.value.toLowerCase().trim();
        tbody.querySelectorAll('tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

/* ============================================================
   CONFIRMAR con SweetAlert2 (si está disponible)
   ============================================================ */
function confirmAction(title, text, icon, confirmText) {
    return new Promise(resolve => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title, text, icon,
                showCancelButton: true,
                confirmButtonColor: icon === 'warning' ? '#EF4444' : '#2563EB',
                cancelButtonColor:  '#334155',
                confirmButtonText:  confirmText || 'Confirmar',
                cancelButtonText:   'Cancelar',
                background:  '#1E293B',
                color:       '#F8FAFC',
                borderRadius: '16px',
            }).then(r => resolve(r.isConfirmed));
        } else {
            resolve(window.confirm(text));
        }
    });
}

/* ============================================================
   AUTO-HIDE ALERTS de Bootstrap
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert-auto-hide').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 420);
        }, 4000);
    });
});
