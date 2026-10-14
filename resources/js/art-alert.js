/**
 * ArtAlert - Custom Modern Claymorphic Alert, Modal & Toast System for ARTDEVATA
 * Built as a superior native replacement for SweetAlert2 with no external dependencies.
 */

class ArtAlertSystem {
    constructor() {
        this.modalEl = null;
        this.toastContainerEl = null;
        this.currentResolve = null;
        this.timerId = null;
        this.initialized = false;

        if (typeof window !== 'undefined') {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
            } else {
                this.init();
            }
        }
    }

    init() {
        if (this.initialized) return;
        this.createToastContainer();
        this.createModal();
        this.setupEventListeners();
        this.initialized = true;
    }

    getIcons() {
        return {
            success: {
                wrapper: 'bg-emerald-50 text-emerald-600 border border-emerald-200/80 shadow-emerald-500/10',
                progress: 'bg-emerald-500',
                svg: `<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                      </svg>`,
                smallSvg: `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                           </svg>`
            },
            error: {
                wrapper: 'bg-rose-50 text-rose-600 border border-rose-200/80 shadow-rose-500/10',
                progress: 'bg-rose-500',
                svg: `<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                      </svg>`,
                smallSvg: `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                           </svg>`
            },
            danger: {
                wrapper: 'bg-rose-50 text-rose-600 border border-rose-200/80 shadow-rose-500/10',
                progress: 'bg-rose-500',
                svg: `<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                      </svg>`,
                smallSvg: `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                           </svg>`
            },
            warning: {
                wrapper: 'bg-amber-50 text-amber-600 border border-amber-200/80 shadow-amber-500/10',
                progress: 'bg-amber-500',
                svg: `<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                      </svg>`,
                smallSvg: `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                           </svg>`
            },
            question: {
                wrapper: 'bg-purple-50 text-[#6C63FF] border border-purple-200/80 shadow-purple-500/10',
                progress: 'bg-[#6C63FF]',
                svg: `<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>`,
                smallSvg: `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                           </svg>`
            },
            info: {
                wrapper: 'bg-indigo-50 text-indigo-600 border border-indigo-200/80 shadow-indigo-500/10',
                progress: 'bg-indigo-500',
                svg: `<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>`,
                smallSvg: `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                           </svg>`
            }
        };
    }

    createToastContainer() {
        if (document.getElementById('art-toast-container')) return;
        const container = document.createElement('div');
        container.id = 'art-toast-container';
        container.className = 'fixed top-5 right-5 z-[10000] flex flex-col gap-3 pointer-events-none max-w-sm w-full px-4 sm:px-0';
        document.body.appendChild(container);
        this.toastContainerEl = container;
    }

    createModal() {
        if (document.getElementById('art-alert-modal')) return;
        const modal = document.createElement('div');
        modal.id = 'art-alert-modal';
        modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200 opacity-0 pointer-events-none';
        modal.innerHTML = `
            <div class="art-alert-card p-6 sm:p-7 max-w-md w-full bg-white rounded-3xl border border-[#E8E6F0] shadow-2xl transition-all duration-200 transform scale-95 opacity-0 text-center relative overflow-hidden">
                <!-- Icon -->
                <div class="art-alert-icon-box w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm transition-transform duration-200">
                    <div class="art-alert-icon-slot"></div>
                </div>

                <!-- Title -->
                <h3 class="art-alert-title font-extrabold text-[#29283D] text-base font-display"></h3>

                <!-- Body / Message -->
                <div class="art-alert-body text-xs text-[#77768C] mt-2.5 leading-relaxed break-words"></div>

                <!-- Optional Input / Textarea -->
                <div class="art-alert-input-wrapper hidden mt-4 text-left">
                    <textarea class="art-alert-input w-full p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0] text-xs font-semibold text-[#29283D] focus:border-[#6C63FF] focus:bg-white focus:outline-none transition-all resize-none" rows="3"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="art-alert-actions flex items-center justify-center gap-3 mt-6">
                    <button type="button" class="art-alert-btn-cancel hidden clay-btn-secondary px-5 py-2.5 rounded-xl font-bold text-xs text-[#77768C] hover:bg-slate-100 transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="button" class="art-alert-btn-confirm clay-btn-primary px-6 py-2.5 rounded-xl font-bold text-xs text-white transition-all shadow-md cursor-pointer">
                        Lanjutkan
                    </button>
                </div>

                <!-- Auto-close progress bar (if timer set) -->
                <div class="art-alert-timer-bar absolute bottom-0 left-0 h-1 bg-[#6C63FF] transition-all hidden"></div>
            </div>
        `;
        document.body.appendChild(modal);
        this.modalEl = modal;

        // Button clicks
        const confirmBtn = modal.querySelector('.art-alert-btn-confirm');
        const cancelBtn = modal.querySelector('.art-alert-btn-cancel');
        const inputEl = modal.querySelector('.art-alert-input');

        confirmBtn.addEventListener('click', () => {
            const val = inputEl ? inputEl.value : '';
            this.close({ isConfirmed: true, isDismissed: false, value: val });
        });

        cancelBtn.addEventListener('click', () => {
            this.close({ isConfirmed: false, isDismissed: true });
        });

        // Click outside backdrop to dismiss
        modal.addEventListener('click', (e) => {
            if (e.target === modal && !modal.dataset.preventOutsideClick) {
                this.close({ isConfirmed: false, isDismissed: true });
            }
        });
    }

    setupEventListeners() {
        // Escape key to dismiss
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modalEl && !this.modalEl.classList.contains('pointer-events-none')) {
                if (!this.modalEl.dataset.preventOutsideClick) {
                    this.close({ isConfirmed: false, isDismissed: true });
                }
            }
        });
    }

    /**
     * Show Modal or Toast using unified options (SweetAlert2 drop-in interface).
     */
    fire(options = {}) {
        this.init();

        // Support fire('Title', 'Message', 'success')
        if (typeof options === 'string') {
            options = {
                title: arguments[0] || '',
                text: arguments[1] || '',
                icon: arguments[2] || 'info'
            };
        }

        // If toast mode requested
        if (options.toast) {
            return this.toast(
                options.text || options.html || options.title || '',
                options.icon || 'success',
                options.text ? options.title : ''
            );
        }

        return new Promise((resolve) => {
            this.currentResolve = resolve;

            const icons = this.getIcons();
            const iconType = options.icon || (options.isDanger ? 'danger' : 'info');
            const iconData = icons[iconType] || icons.info;

            const iconBox = this.modalEl.querySelector('.art-alert-icon-box');
            const iconSlot = this.modalEl.querySelector('.art-alert-icon-slot');
            const titleEl = this.modalEl.querySelector('.art-alert-title');
            const bodyEl = this.modalEl.querySelector('.art-alert-body');
            const inputWrapper = this.modalEl.querySelector('.art-alert-input-wrapper');
            const inputEl = this.modalEl.querySelector('.art-alert-input');
            const confirmBtn = this.modalEl.querySelector('.art-alert-btn-confirm');
            const cancelBtn = this.modalEl.querySelector('.art-alert-btn-cancel');
            const cardEl = this.modalEl.querySelector('.art-alert-card');
            const timerBar = this.modalEl.querySelector('.art-alert-timer-bar');

            // Set Icon
            iconBox.className = `art-alert-icon-box w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm transition-transform duration-200 ${iconData.wrapper}`;
            iconSlot.innerHTML = iconData.svg;

            // Set Title & Content
            titleEl.textContent = options.title || '';
            titleEl.style.display = options.title ? 'block' : 'none';

            if (options.html) {
                bodyEl.innerHTML = options.html;
            } else if (options.text) {
                bodyEl.textContent = options.text;
            } else {
                bodyEl.innerHTML = '';
            }

            // Input handling
            if (options.input === 'textarea' || options.input === 'text') {
                inputWrapper.classList.remove('hidden');
                inputEl.value = options.inputValue || '';
                inputEl.placeholder = options.inputPlaceholder || 'Masukkan catatan...';
                setTimeout(() => inputEl.focus(), 150);
            } else {
                inputWrapper.classList.add('hidden');
            }

            // Confirm Button
            const isDanger = options.isDanger === true || 
                             options.confirmButtonColor === '#e11d48' || 
                             options.confirmButtonColor === '#FF5A5F' ||
                             options.icon === 'danger';

            confirmBtn.textContent = options.confirmButtonText || 'Lanjutkan';
            confirmBtn.className = isDanger
                ? 'art-alert-btn-confirm clay-btn bg-rose-500 hover:bg-rose-600 text-white border-rose-600 px-6 py-2.5 rounded-xl font-bold text-xs shadow-md shadow-rose-500/25 transition-all cursor-pointer'
                : 'art-alert-btn-confirm clay-btn-primary px-6 py-2.5 rounded-xl font-bold text-xs text-white transition-all shadow-md shadow-[#6C63FF]/25 cursor-pointer';

            if (options.confirmButtonColor && !isDanger) {
                confirmBtn.style.backgroundColor = options.confirmButtonColor;
                confirmBtn.style.borderColor = options.confirmButtonColor;
            } else {
                confirmBtn.style.backgroundColor = '';
                confirmBtn.style.borderColor = '';
            }

            // Cancel Button
            if (options.showCancelButton) {
                cancelBtn.classList.remove('hidden');
                cancelBtn.textContent = options.cancelButtonText || 'Batal';
            } else {
                cancelBtn.classList.add('hidden');
            }

            // Reverse buttons layout
            const actionsEl = this.modalEl.querySelector('.art-alert-actions');
            if (options.reverseButtons) {
                actionsEl.classList.add('flex-row-reverse');
            } else {
                actionsEl.classList.remove('flex-row-reverse');
            }

            this.modalEl.dataset.preventOutsideClick = options.allowOutsideClick === false ? 'true' : '';

            // Timer handling
            if (this.timerId) clearTimeout(this.timerId);
            if (options.timer && options.timer > 0) {
                timerBar.classList.remove('hidden');
                timerBar.style.width = '100%';
                timerBar.style.transition = `width ${options.timer}ms linear`;
                setTimeout(() => { timerBar.style.width = '0%'; }, 20);

                this.timerId = setTimeout(() => {
                    this.close({ isConfirmed: true, isDismissed: false });
                }, options.timer);
            } else {
                timerBar.classList.add('hidden');
            }

            // Show Modal with Animation
            this.modalEl.classList.remove('pointer-events-none');
            requestAnimationFrame(() => {
                this.modalEl.classList.remove('opacity-0');
                this.modalEl.classList.add('opacity-100');
                cardEl.classList.remove('scale-95', 'opacity-0');
                cardEl.classList.add('scale-100', 'opacity-100');
            });
        });
    }

    /**
     * Close the currently open modal.
     */
    close(result = { isConfirmed: false, isDismissed: true }) {
        if (!this.modalEl) return;
        if (this.timerId) {
            clearTimeout(this.timerId);
            this.timerId = null;
        }

        const cardEl = this.modalEl.querySelector('.art-alert-card');
        this.modalEl.classList.remove('opacity-100');
        this.modalEl.classList.add('opacity-0');
        if (cardEl) {
            cardEl.classList.remove('scale-100', 'opacity-100');
            cardEl.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            this.modalEl.classList.add('pointer-events-none');
            if (this.currentResolve) {
                this.currentResolve(result);
                this.currentResolve = null;
            }
        }, 200);
    }

    /**
     * Show modern floating toast notification in top-right.
     */
    toast(message, type = 'success', title = '') {
        this.init();

        const icons = this.getIcons();
        const iconData = icons[type] || icons.success;
        const defaultTitle = type === 'success' ? 'Berhasil' : (type === 'error' ? 'Peringatan' : 'Informasi');
        const displayTitle = title || defaultTitle;

        const toast = document.createElement('div');
        toast.className = 'art-toast-item pointer-events-auto art-toast-card p-4 bg-white/95 backdrop-blur-md rounded-2xl border border-[#E8E6F0] shadow-xl flex items-start gap-3.5 transition-all duration-300 transform translate-y-[-10px] opacity-0 overflow-hidden relative';
        toast.innerHTML = `
            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs ${iconData.wrapper}">
                ${iconData.smallSvg}
            </div>
            <div class="flex-1 min-w-0 pr-2">
                <h4 class="font-extrabold text-xs text-[#29283D] leading-tight">${displayTitle}</h4>
                <div class="text-[11px] text-[#77768C] mt-0.5 leading-relaxed break-words">${message}</div>
            </div>
            <button type="button" class="art-toast-close text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer shrink-0">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="art-toast-progress absolute bottom-0 left-0 h-0.5 ${iconData.progress} w-full transition-all linear"></div>
        `;

        if (this.toastContainerEl) {
            this.toastContainerEl.appendChild(toast);
        }

        // Animate entrance
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-10px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        const progressBar = toast.querySelector('.art-toast-progress');
        const duration = 3800;
        if (progressBar) {
            progressBar.style.transition = `width ${duration}ms linear`;
            setTimeout(() => { progressBar.style.width = '0%'; }, 20);
        }

        const dismiss = () => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-[-10px]', 'opacity-0');
            setTimeout(() => {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        };

        const autoDismissId = setTimeout(dismiss, duration);

        const closeBtn = toast.querySelector('.art-toast-close');
        closeBtn.addEventListener('click', () => {
            clearTimeout(autoDismissId);
            dismiss();
        });

        return Promise.resolve({ isConfirmed: true, isDismissed: false });
    }

    /**
     * Quick Convenience Helpers
     */
    success(message, title = 'Berhasil!') {
        return this.fire({ icon: 'success', title, text: message });
    }

    error(message, title = 'Terjadi Kesalahan') {
        return this.fire({ icon: 'error', title, text: message });
    }

    warning(message, title = 'Perhatian') {
        return this.fire({ icon: 'warning', title, text: message });
    }

    info(message, title = 'Informasi') {
        return this.fire({ icon: 'info', title, text: message });
    }

    confirm(message, options = {}) {
        return this.fire({
            icon: options.isDanger ? 'danger' : 'warning',
            title: options.title || 'Konfirmasi Tindakan',
            text: message,
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText || 'Ya, Lanjutkan',
            cancelButtonText: options.cancelButtonText || 'Batal',
            ...options
        });
    }
}

// Instantiate singleton
const ArtAlert = new ArtAlertSystem();

export default ArtAlert;
