// =====================================
// SIDEBAR TOGGLE FOR MOBILE
// =====================================
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
});

// =====================================
// AUTO DISMISS ALERTS
// =====================================
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// =====================================
// CONFIRM DELETE ACTIONS
// =====================================
function confirmDelete(message) {
    return confirm(message || 'Apakah Anda yakin ingin menghapus data ini?');
}

// =====================================
// FORM VALIDATION ENHANCEMENT
// =====================================
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate="true"]');

    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});

// =====================================
// TEXTAREA AUTO RESIZE
// =====================================
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea[data-autoresize="true"]');

    textareas.forEach(function(textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
});

// =====================================
// TOOLTIP INITIALIZATION
// =====================================
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// =====================================
// LOADING SPINNER
// =====================================
function showLoading() {
    const loadingHtml = `
        <div id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
             background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; 
             justify-content: center;">
            <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', loadingHtml);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// =====================================
// SEARCH FUNCTIONALITY
// =====================================
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);

    if (!input || !table) return;

    input.addEventListener('keyup', function() {
        const filter = input.value.toUpperCase();
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }

            row.style.display = found ? '' : 'none';
        }
    });
}

// =====================================
// NOTIFICATION REFRESH
// =====================================
function refreshNotifications() {
    const badge = document.querySelector('.notification-badge');
    if (!badge) return;

    fetch('api/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error refreshing notifications:', error));
}

// Refresh notifications every 30 seconds
if (document.querySelector('.notification-badge')) {
    setInterval(refreshNotifications, 30000);
}

// =====================================
// PRINT FUNCTIONALITY
// =====================================
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// =====================================
// COPY TO CLIPBOARD
// =====================================
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Berhasil disalin ke clipboard!', 'success');
    }).catch(function(err) {
        showToast('Gagal menyalin: ' + err, 'danger');
    });
}

// =====================================
// TOAST NOTIFICATION
// =====================================
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3" 
             role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.body.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// =====================================
// SMOOTH SCROLL
// =====================================
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

// =====================================
// EXPORT FUNCTIONS
// =====================================
window.confirmDelete = confirmDelete;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.searchTable = searchTable;
window.refreshNotifications = refreshNotifications;
window.printElement = printElement;
window.copyToClipboard = copyToClipboard;
window.showToast = showToast;