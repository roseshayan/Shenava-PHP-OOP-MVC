/**
 * Shenava - Admin Panel Main JavaScript
 * Global functions and utilities
 */

class ShenavaAdmin {
    constructor() {
        this.apiBase = '../backend/public/api/v1';
        this.init();
    }

    init() {
        this.setupAjax();
        this.setupGlobalHandlers();
        this.checkSession();
    }

    setupAjax() {
        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            beforeSend: function (xhr) {
                // You can add authentication tokens here later
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                ShenavaAdmin.showToast('خطا در ارتباط با سرور', 'error');
            }
        });
    }

    setupGlobalHandlers() {
        // Confirm before delete
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            const message = $(this).data('confirm') || 'آیا از حذف این آیتم مطمئن هستید؟';

            if (confirm(message)) {
                window.location.href = $(this).attr('href');
            }
        });

        // Auto-dismiss alerts
        $('.alert').not('.alert-permanent').delay(5000).fadeOut(400);

        // Tooltip initialization
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    checkSession() {
        // Check session every 5 minutes
        setInterval(() => {
            $.get('includes/session-check.php')
                .fail(() => {
                    this.showToast('Session expired. Please login again.', 'warning');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                });
        }, 300000);
    }

    // Show notification toast
    static showToast(message, type = 'info', duration = 5000) {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${this.getToastIcon(type)} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        $('#toastContainer').append(toast);
        const bsToast = new bootstrap.Toast(toast[0], {delay: duration});
        bsToast.show();

        toast.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

    static getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    // Format date
    static formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fa-IR');
    }

    // Format file size
    static formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Loading state for buttons
    static setButtonLoading(button, isLoading) {
        const $btn = $(button);
        if (isLoading) {
            $btn.prop('disabled', true);
            $btn.data('original-text', $btn.html());
            $btn.html(`
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                در حال پردازش...
            `);
        } else {
            $btn.prop('disabled', false);
            $btn.html($btn.data('original-text'));
        }
    }
}

// Initialize when document is ready
$(document).ready(function () {
    window.shenavaAdmin = new ShenavaAdmin();

    // Create toast container if not exists
    if ($('#toastContainer').length === 0) {
        $('body').append(`
            <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>
        `);
    }
});