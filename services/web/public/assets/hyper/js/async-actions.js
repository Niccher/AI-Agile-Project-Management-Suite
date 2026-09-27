/**
 * Chege Jira - Global Asynchronous Action & SweetAlert2 UX Engine
 * Eliminates full-page reloads and provides fluid, desktop-class visual feedback.
 */

(function (window) {
    'use strict';

    // 1. Toast Mixin: Non-intrusive top-right alerts
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    /**
     * Get the latest CSRF token hash from DOM meta tags
     */
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Update the CSRF token across the DOM (for token regeneration)
     */
    function updateCsrfToken(newToken) {
        if (!newToken) return;
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            meta.setAttribute('content', newToken);
        }
        document.querySelectorAll('input[name="csrf_token"]').forEach((input) => {
            input.value = newToken;
        });
    }

    /**
     * Universal Asynchronous Request Dispatcher
     *
     * @param {string} url Endpoint URL
     * @param {Object|FormData} data Payload (plain object or FormData)
     * @param {Object} options Custom options (method, silent, headers, etc.)
     * @returns {Promise<Object|null>} Decoded JSON response or null
     */
    async function dispatchAsyncAction(url, data = {}, options = {}) {
        const method = (options.method || 'POST').toUpperCase();
        const silent = options.silent === true;
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            ...(options.headers || {})
        };

        const csrfToken = getCsrfToken();
        let body = null;

        if (data instanceof FormData) {
            if (csrfToken && !data.has('csrf_token')) {
                data.append('csrf_token', csrfToken);
            }
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }
            body = data;
        } else if (method !== 'GET' && method !== 'HEAD') {
            headers['Content-Type'] = 'application/json';
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
                data.csrf_token = csrfToken;
            }
            body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, {
                method,
                headers,
                body,
                credentials: 'same-origin'
            });

            // Inspect response headers for regenerated CSRF token
            const resCsrf = response.headers.get('X-CSRF-TOKEN');
            if (resCsrf) {
                updateCsrfToken(resCsrf);
            }

            const contentType = response.headers.get('content-type') || '';
            let json = null;

            if (contentType.includes('application/json')) {
                json = await response.json();
            } else {
                const text = await response.text();
                try {
                    json = JSON.parse(text);
                } catch (e) {
                    if (response.ok) {
                        if (!silent) Toast.fire({ icon: 'success', title: 'Action completed.' });
                        return { success: true, status: 'success', raw: text };
                    }
                    throw new Error(text.substring(0, 150) || `HTTP error ${response.status}`);
                }
            }

            if (json.csrf_hash || json.csrf_token) {
                updateCsrfToken(json.csrf_hash || json.csrf_token);
            }

            const isSuccess = response.ok && (json.success === true || json.status === 'success' || !json.error);

            if (isSuccess) {
                if (!silent) {
                    Toast.fire({
                        icon: 'success',
                        title: json.message || 'Action executed successfully.'
                    });
                }
                return json;
            } else {
                const errMsg = json.message || (json.error ? (json.error.message || json.error) : 'Operation failed.');
                Toast.fire({
                    icon: 'error',
                    title: errMsg
                });
                return null;
            }
        } catch (err) {
            console.error('dispatchAsyncAction Error:', err);
            Toast.fire({
                icon: 'error',
                title: err.message || 'Network error occurred. Please try again.'
            });
            return null;
        }
    }

    /**
     * Modern SweetAlert2 Confirmation Dialog
     *
     * @param {Object} opts Configuration options
     * @param {string} [opts.title] Modal heading
     * @param {string} [opts.text] Description
     * @param {string} [opts.confirmButtonText] Confirm button text
     * @param {string} [opts.confirmButtonColor] Button color hex
     * @param {string} [opts.icon] SweetAlert icon ('warning', 'info', etc.)
     * @param {Function} opts.onConfirm Async callback returning promise
     */
    function confirmAction(opts = {}) {
        return Swal.fire({
            title: opts.title || 'Are you sure?',
            text: opts.text || 'This action cannot be undone.',
            icon: opts.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: opts.confirmButtonColor || '#727cf5',
            cancelButtonColor: '#6c757d',
            confirmButtonText: opts.confirmButtonText || 'Yes, proceed',
            cancelButtonText: opts.cancelButtonText || 'Cancel',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: async () => {
                if (typeof opts.onConfirm === 'function') {
                    try {
                        const result = await opts.onConfirm();
                        if (result === false) {
                            return false;
                        }
                        return result;
                    } catch (error) {
                        Swal.showValidationMessage(`Request failed: ${error.message || error}`);
                    }
                }
            }
        });
    }

    // Expose helpers globally
    window.Toast = Toast;
    window.getCsrfToken = getCsrfToken;
    window.updateCsrfToken = updateCsrfToken;
    window.dispatchAsyncAction = dispatchAsyncAction;
    window.confirmAction = confirmAction;

})(window);
