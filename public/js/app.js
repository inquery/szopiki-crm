/**
 * CRM App - Common JS helpers
 */
const App = {
    /**
     * Fetch wrapper with JSON handling and 401 redirect
     */
    async fetch(url, options = {}) {
        const defaults = {
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
        };
        const config = { ...defaults, ...options };
        if (config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }

        const response = await fetch(url, config);

        if (response.status === 401 && !window.location.pathname.startsWith('/login')) {
            window.location.href = '/login';
            return null;
        }

        if (!response.ok) {
            const error = await response.json().catch(() => ({ message: 'Wystapil blad' }));
            throw new Error(error.message || error.error || `HTTP ${response.status}`);
        }

        if (response.status === 204) return null;
        return response.json();
    },

    /**
     * GET request
     */
    get(url) {
        return this.fetch(url);
    },

    /**
     * POST request
     */
    post(url, data) {
        return this.fetch(url, { method: 'POST', body: data });
    },

    /**
     * PUT request
     */
    put(url, data) {
        return this.fetch(url, { method: 'PUT', body: data });
    },

    /**
     * PATCH request
     */
    patch(url, data) {
        return this.fetch(url, { method: 'PATCH', body: data });
    },

    /**
     * DELETE request
     */
    delete(url) {
        return this.fetch(url, { method: 'DELETE' });
    },

    /**
     * Show notification
     */
    notify(message, type = 'info') {
        const container = document.getElementById('notifications-container');
        if (!container) return;

        const colors = {
            success: 'bg-green-600',
            error: 'bg-red-600',
            info: 'bg-blue-600',
        };

        const el = document.createElement('div');
        el.className = `px-4 py-3 rounded-lg shadow-lg text-white text-sm transition-all ${colors[type] || colors.info}`;
        el.textContent = message;
        container.appendChild(el);

        setTimeout(() => {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        }, 5000);
    },

    success(msg) { this.notify(msg, 'success'); },
    error(msg) { this.notify(msg, 'error'); },
    info(msg) { this.notify(msg, 'info'); },

    /**
     * Show confirm dialog
     * Returns a Promise that resolves to true/false
     */
    confirm(title, message) {
        return new Promise((resolve) => {
            const dialog = document.getElementById('confirm-dialog');
            const titleEl = document.getElementById('confirm-title');
            const msgEl = document.getElementById('confirm-message');
            const okBtn = document.getElementById('confirm-ok');
            const cancelBtn = document.getElementById('confirm-cancel');

            titleEl.textContent = title;
            msgEl.textContent = message;
            dialog.classList.remove('hidden');

            const cleanup = () => {
                dialog.classList.add('hidden');
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
            };

            const onOk = () => { cleanup(); resolve(true); };
            const onCancel = () => { cleanup(); resolve(false); };

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
        });
    },

    /**
     * Format date for display
     */
    formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('pl-PL');
    },

    formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('pl-PL') + ' ' + d.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
    },

    /**
     * Build URL with query parameters
     */
    buildUrl(base, params = {}) {
        const url = new URL(base, window.location.origin);
        Object.entries(params).forEach(([k, v]) => {
            if (v !== null && v !== undefined && v !== '') {
                url.searchParams.set(k, v);
            }
        });
        return url.toString();
    },

    /**
     * Show/hide loading spinner in a container
     */
    showLoading(container) {
        container.innerHTML = `
            <div class="flex justify-center items-center p-8">
                <div class="h-8 w-8 animate-spin rounded-full border-2 border-gray-300 border-t-blue-600"></div>
            </div>`;
    },

    /**
     * Get form data as object
     */
    formData(form) {
        const fd = new FormData(form);
        const data = {};
        fd.forEach((value, key) => { data[key] = value; });
        return data;
    },
};
