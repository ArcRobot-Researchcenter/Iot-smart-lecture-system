// Smart Lecture Timetable System - Main JavaScript

// AJAX Helper Function
function ajaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    if (method === 'POST' && !(data instanceof FormData)) {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    }

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    callback(response);
                } catch (e) {
                    callback({ success: false, message: 'Invalid response format' });
                }
            } else {
                callback({ success: false, message: 'Request failed: ' + xhr.statusText });
            }
        }
    };

    xhr.send(data);
}

// Show Toast Notification
function showToast(message, type = 'success') {
    const container = document.querySelector('.toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = 'toast-custom animate-slide-in';
    toast.style.borderLeft = type === 'success' ? '4px solid #28a745' : 
                             type === 'error' ? '4px solid #dc3545' : '4px solid #ffc107';

    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-times-circle' : 'fa-exclamation-triangle'} me-2"></i>
            <span>${message}</span>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

// Confirm Delete
function confirmDelete(message, callback) {
    if (confirm(message || 'Are you sure you want to delete this item?')) {
        callback();
    }
}

// Search Table Function
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        rows[i].style.display = found ? '' : 'none';
    }
}

// Filter Table by Column
function filterTable(tableId, columnIndex, filterValue) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cell = rows[i].getElementsByTagName('td')[columnIndex];
        if (cell) {
            const text = cell.textContent || cell.innerText;
            rows[i].style.display = (filterValue === 'all' || text.toLowerCase().indexOf(filterValue.toLowerCase()) > -1) ? '' : 'none';
        }
    }
}

// Preview Image Upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Print Table
function printTable(tableId, title) {
    const table = document.getElementById(tableId).outerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>${title}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #333; padding: 10px; text-align: left; }
                th { background: #f4e4bc; color: #000; }
                h2 { text-align: center; color: #b8860b; }
                .no-print { display: none; }
            </style>
        </head>
        <body>
            <h2>${title}</h2>
            ${table}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Export to CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/"/g, '\"');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }

    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const link = document.createElement('a');
    link.setAttribute('href', encodeURI(csvContent));
    link.setAttribute('download', filename + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Export to Excel
function exportToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    const html = table.outerHTML;
    const url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename + '.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Toggle Sidebar on Mobile
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}

// Format Time
function formatTime(timeStr) {
    const [hours, minutes] = timeStr.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

// Countdown Timer for Next Lecture
function updateCountdown(targetTime, elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    function update() {
        const now = new Date().getTime();
        const distance = targetTime - now;

        if (distance < 0) {
            element.innerHTML = 'Lecture in progress or completed';
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        element.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
    }

    update();
    setInterval(update, 1000);
}

// AJAX Form Submit
function ajaxFormSubmit(formId, url, callback) {
    const form = document.getElementById(formId);
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);

        ajaxRequest(url, 'POST', formData, function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                if (callback) callback(response);
            } else {
                showToast(response.message || 'An error occurred', 'error');
            }
        });
    });
}

// Initialize DataTable
function initDataTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    // Add search functionality
    const searchInput = document.querySelector(`[data-table="${tableId}"]`);
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchTable(searchInput.id, tableId);
        });
    }
}

// Auto-refresh dashboard stats
function autoRefreshStats() {
    setInterval(function() {
        ajaxRequest('admin/ajax/get_stats.php', 'GET', null, function(response) {
            if (response.success) {
                Object.keys(response.data).forEach(key => {
                    const element = document.getElementById('stat_' + key);
                    if (element) {
                        element.textContent = response.data[key];
                    }
                });
            }
        });
    }, 60000); // Refresh every minute
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-refresh if on dashboard
    if (document.getElementById('dashboard-stats')) {
        autoRefreshStats();
    }
});
