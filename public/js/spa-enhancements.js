/**
 * SPA Enhancement Utilities for Cutikuy
 * Provides smooth transitions, loading states, and better UX
 */

class SPAEnhancer {
    constructor() {
        this.init();
    }

    init() {
        this.setupPageTransitions();
        this.setupLoadingOverlay();
        this.setupToastSystem();
        this.setupOfflineIndicator();
        this.setupProgressBar();
        this.setupFormEnhancements();
    }

    /**
     * Page Transitions
     */
    setupPageTransitions() {
        // Smooth page entry
        window.addEventListener('DOMContentLoaded', () => {
            document.body.style.opacity = '0';
            requestAnimationFrame(() => {
                document.body.style.transition = 'opacity 0.3s ease';
                document.body.style.opacity = '1';
            });
        });

        // Smooth page exit
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (link && !link.hasAttribute('target') && !link.hasAttribute('download')) {
                const href = link.getAttribute('href');
                if (href && href.startsWith('/') && !href.includes('#')) {
                    e.preventDefault();
                    document.body.classList.add('page-transitioning');
                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                }
            }
        });
    }

    /**
     * Loading Overlay
     */
    setupLoadingOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);

        window.showLoading = () => overlay.classList.add('active');
        window.hideLoading = () => overlay.classList.remove('active');
    }

    /**
     * Toast Notification System
     */
    setupToastSystem() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);

        window.showToast = (message, type = 'info', duration = 3000) => {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };

            toast.innerHTML = `
                <span style="font-size: 20px;">${icons[type] || icons.info}</span>
                <span>${message}</span>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        };
    }

    /**
     * Offline Indicator
     */
    setupOfflineIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'offline-indicator';
        indicator.innerHTML = '📡 You are offline';
        document.body.appendChild(indicator);

        window.addEventListener('offline', () => {
            indicator.classList.add('show');
            showToast('You are offline. Some features may not work.', 'warning', 5000);
        });

        window.addEventListener('online', () => {
            indicator.classList.remove('show');
            showToast('Back online!', 'success');
        });
    }

    /**
     * Progress Bar
     */
    setupProgressBar() {
        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        document.body.appendChild(progressBar);

        window.showProgress = () => progressBar.classList.add('loading');
        window.hideProgress = () => progressBar.classList.remove('loading');

        // Auto show on fetch
        const originalFetch = window.fetch;
        window.fetch = function (...args) {
            showProgress();
            return originalFetch.apply(this, args)
                .finally(() => {
                    setTimeout(hideProgress, 300);
                });
        };
    }

    /**
     * Form Enhancements
     */
    setupFormEnhancements() {
        // Add loading state to buttons on form submit
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('loading')) {
                submitBtn.classList.add('loading');
                setTimeout(() => submitBtn.classList.remove('loading'), 5000);
            }
        });

        // Auto-save draft (optional)
        this.setupAutoSave();
    }

    /**
     * Auto-save Draft
     */
    setupAutoSave() {
        const forms = document.querySelectorAll('[data-autosave]');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', debounce(() => {
                    this.saveDraft(form);
                }, 2000));
            });
        });
    }

    saveDraft(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        const key = form.dataset.autosave || 'draft';
        localStorage.setItem(key, JSON.stringify(data));
        //console.log('Draft saved:', key);
    }

    loadDraft(formId) {
        const key = formId;
        const draft = localStorage.getItem(key);
        if (draft) {
            return JSON.parse(draft);
        }
        return null;
    }
}

/**
 * Utility Functions
 */

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Smooth scroll to element
function smoothScrollTo(element) {
    element.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

// Copy to clipboard with feedback
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Copied to clipboard!', 'success');
        return true;
    } catch (err) {
        showToast('Failed to copy', 'error');
        return false;
    }
}

// Confirm dialog with better UX
function confirmAction(message, onConfirm, onCancel) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                onConfirm();
            } else if (onCancel) {
                onCancel();
            }
        });
    } else {
        if (confirm(message)) {
            onConfirm();
        } else if (onCancel) {
            onCancel();
        }
    }
}

// Preload route
function preloadRoute(url) {
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    document.head.appendChild(link);
}

// Lazy load images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.spaEnhancer = new SPAEnhancer();
        lazyLoadImages();
    });
} else {
    window.spaEnhancer = new SPAEnhancer();
    lazyLoadImages();
}

// Export for use in other scripts
window.SPAUtils = {
    debounce,
    throttle,
    smoothScrollTo,
    copyToClipboard,
    confirmAction,
    preloadRoute,
    lazyLoadImages
};

//console.log('✨ SPA Enhancements loaded');
