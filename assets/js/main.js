/**
 * Bella Vista Oasis Spa - Core Client-Side Architecture
 * 
 * Provides automated CSRF injection for AJAX requests, toast notifications,
 * custom modal confirmations, real-time booking calculations, slot availability checks,
 * live table filtering, media previews, and session activity tracking.
 */

(function () {
    'use strict';

    // Global Namespace
    window.BVO = window.BVO || {};

    /**
     * 1. Retrieve Current CSRF Token
     * Reads from <meta name="csrf-token"> tag.
     */
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }
    BVO.getCsrfToken = getCsrfToken;

    /**
     * 2. AJAX Fetch Wrapper with Automatic CSRF Injection
     *
     * @param {string} url
     * @param {object} options
     * @returns {Promise<Response>}
     */
    async function secureFetch(url, options = {}) {
        options.headers = options.headers || {};

        // Auto-inject CSRF token for mutating requests
        const method = (options.method || 'GET').toUpperCase();
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
            const token = getCsrfToken();
            if (token && !options.headers['X-CSRF-Token']) {
                options.headers['X-CSRF-Token'] = token;
            }
        }

        // Set default Accept header
        if (!options.headers['Accept']) {
            options.headers['Accept'] = 'application/json';
        }

        try {
            return await fetch(url, options);
        } catch (error) {
            console.error('BVO Network Request Error:', error);
            showToast('Unable to connect to the sanctuary server. Please verify your connection.', 'danger');
            throw error;
        }
    }
    BVO.secureFetch = secureFetch;

    /**
     * 3. Toast Notifications
     *
     * @param {string} message
     * @param {string} type ('success', 'danger', 'warning', 'info', 'gold')
     * @param {number} duration Duration in milliseconds (default 4500)
     */
    function showToast(message, type = 'gold', duration = 4500) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1095';
            document.body.appendChild(container);
        }

        const toastId = 'bvo-toast-' + Date.now();
        const iconMap = {
            success: 'fa-circle-check text-success',
            danger: 'fa-triangle-exclamation text-danger',
            warning: 'fa-bell text-warning',
            info: 'fa-circle-info text-info',
            gold: 'fa-gem text-gold'
        };

        const iconClass = iconMap[type] || iconMap.gold;
        const toastTypeClass = 'toast-' + type;

        const toastEl = document.createElement('div');
        toastEl.id = toastId;
        toastEl.className = `toast luxury-toast ${toastTypeClass} border-0 shadow-lg mb-2`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = `
            <div class="toast-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fa-solid ${iconClass} me-2 fs-6"></i>
                    <strong class="font-playfair text-gold tracking-wide">Bella Vista Oasis</strong>
                </div>
                <small class="text-silver">Just now</small>
                <button type="button" class="btn-close btn-close-gold ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body font-inter text-light">
                ${message}
            </div>
        `;

        container.appendChild(toastEl);

        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: duration
            });
            bsToast.show();

            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        } else {
            // Fallback if bootstrap JS is still loading
            toastEl.classList.add('show');
            setTimeout(() => {
                toastEl.remove();
            }, duration);
        }
    }
    BVO.showToast = showToast;

    /**
     * 4. Confirmation Dialog Modal
     *
     * @param {string} title
     * @param {string} message
     * @param {function} onConfirm
     * @param {string} confirmText
     * @param {string} cancelText
     */
    function confirmAction(title, message, onConfirm, confirmText = 'Confirm', cancelText = 'Cancel') {
        let modalEl = document.getElementById('bvo-confirm-modal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'bvo-confirm-modal';
            modalEl.className = 'modal fade';
            modalEl.tabIndex = -1;
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content luxury-modal">
                        <div class="modal-header luxury-modal-header d-flex justify-content-between align-items-center">
                            <h5 class="modal-title font-playfair text-gold mb-0" id="bvo-confirm-title">Confirm Action</h5>
                            <button type="button" class="btn-close btn-close-gold" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body font-inter py-4" id="bvo-confirm-body">
                            Are you sure you wish to proceed?
                        </div>
                        <div class="modal-footer luxury-modal-footer">
                            <button type="button" class="btn btn-outline-gold btn-sm px-4" data-bs-dismiss="modal" id="bvo-confirm-cancel">Cancel</button>
                            <button type="button" class="btn btn-gold btn-sm px-4 fw-bold" id="bvo-confirm-btn">Confirm</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modalEl);
        }

        document.getElementById('bvo-confirm-title').textContent = title;
        document.getElementById('bvo-confirm-body').innerHTML = message;
        document.getElementById('bvo-confirm-cancel').textContent = cancelText;

        const confirmBtn = document.getElementById('bvo-confirm-btn');
        confirmBtn.textContent = confirmText;

        const bsModal = new bootstrap.Modal(modalEl);

        // Replace click listener
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', async () => {
            bsModal.hide();
            if (typeof onConfirm === 'function') {
                await onConfirm();
            }
        });

        bsModal.show();
    }
    BVO.confirmAction = confirmAction;

    /**
     * 5. Global Loading Overlay Controls
     */
    function showLoading(message) {
        const overlay = document.getElementById('global-loading-overlay');
        if (overlay) {
            if (message) {
                const textEl = overlay.querySelector('.text-silver');
                if (textEl) textEl.textContent = message;
            }
            overlay.classList.remove('d-none');
        }
    }

    function hideLoading() {
        const overlay = document.getElementById('global-loading-overlay');
        if (overlay) {
            overlay.classList.add('d-none');
        }
    }
    BVO.showLoading = showLoading;
    BVO.hideLoading = hideLoading;

    /**
     * 6. Form Validation Helper
     * Validates required inputs, pattern matches, and passwords before submission.
     */
    function initFormValidation(formSelector) {
        const forms = document.querySelectorAll(formSelector || '.needs-luxury-validation');
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    showToast('Please complete all required fields correctly.', 'warning');
                }
                form.classList.add('was-validated');
            }, false);
        });
    }
    BVO.initFormValidation = initFormValidation;

    /**
     * 7. Live Table Search / Filtering
     *
     * @param {string} inputSelector
     * @param {string} tableSelector
     */
    function initTableSearch(inputSelector, tableSelector) {
        const input = document.querySelector(inputSelector);
        const table = document.querySelector(tableSelector);
        if (!input || !table) return;

        input.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    BVO.initTableSearch = initTableSearch;

    /**
     * 8. Media / Image Upload Preview
     *
     * @param {string} fileInputSelector
     * @param {string} previewImgSelector
     */
    function initImagePreview(fileInputSelector, previewImgSelector) {
        const fileInput = document.querySelector(fileInputSelector);
        const previewImg = document.querySelector(previewImgSelector);

        if (!fileInput || !previewImg) return;

        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    showToast('Please choose an image file (PNG, JPG, WEBP).', 'warning');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    BVO.initImagePreview = initImagePreview;

    /**
     * 9. Booking Price Calculator
     * Computes service base price and extra guest surcharges dynamically.
     *
     * @param {number} basePrice
     * @param {string} roomType ('single', 'couples', 'family')
     * @param {number} guestCount
     * @param {number} additionalFee (default 750 for family)
     * @returns {object} { basePrice, extraCharges, totalAmount }
     */
    function calculateBookingPrice(basePrice, roomType, guestCount, additionalFee = 750) {
        guestCount = parseInt(guestCount, 10) || 1;
        basePrice = parseFloat(basePrice) || 0;
        additionalFee = parseFloat(additionalFee) || 0;

        let extraCharges = 0;
        if (roomType === 'family' && guestCount > 4) {
            extraCharges = (guestCount - 4) * additionalFee;
        }

        const totalAmount = basePrice + extraCharges;
        return {
            basePrice: basePrice,
            extraCharges: extraCharges,
            totalAmount: totalAmount,
            formattedTotal: '₱ ' + totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        };
    }
    BVO.calculateBookingPrice = calculateBookingPrice;

    /**
     * 10. AJAX Room & Schedule Availability Checker
     *
     * @param {number} roomId
     * @param {string} date (YYYY-MM-DD)
     * @param {string} startTime (HH:MM)
     * @param {number} durationMinutes
     * @param {string} apiEndpoint
     * @returns {Promise<object>}
     */
    async function checkSlotAvailability(roomId, date, startTime, durationMinutes, apiEndpoint = 'api/check-availability.php') {
        if (!roomId || !date || !startTime || !durationMinutes) {
            return { available: false, message: 'Please provide room, date, and appointment time.' };
        }

        try {
            showLoading('Checking sanctuary suite availability...');
            const params = new URLSearchParams({
                room_id: roomId,
                date: date,
                start_time: startTime,
                duration: durationMinutes
            });

            const response = await secureFetch(`${apiEndpoint}?${params.toString()}`);
            const data = await response.json();
            hideLoading();
            return data;
        } catch (error) {
            hideLoading();
            return { available: false, message: 'Could not verify slot availability.' };
        }
    }
    BVO.checkSlotAvailability = checkSlotAvailability;

    /**
     * 11. Interactive Star Rating Selector
     */
    function initStarRating(containerSelector, hiddenInputSelector) {
        const container = document.querySelector(containerSelector);
        const input = document.querySelector(hiddenInputSelector);
        if (!container || !input) return;

        const stars = container.querySelectorAll('.fa-star');
        stars.forEach(star => {
            star.addEventListener('mouseenter', function () {
                const val = parseInt(this.getAttribute('data-value'), 10);
                highlightStars(val);
            });

            star.addEventListener('click', function () {
                const val = parseInt(this.getAttribute('data-value'), 10);
                input.value = val;
                highlightStars(val);
            });
        });

        container.addEventListener('mouseleave', function () {
            const currentVal = parseInt(input.value, 10) || 5;
            highlightStars(currentVal);
        });

        function highlightStars(count) {
            stars.forEach(s => {
                const starVal = parseInt(s.getAttribute('data-value'), 10);
                if (starVal <= count) {
                    s.classList.remove('fa-regular');
                    s.classList.add('fa-solid');
                } else {
                    s.classList.remove('fa-solid');
                    s.classList.add('fa-regular');
                }
            });
        }
    }
    BVO.initStarRating = initStarRating;

    /**
     * 12. Session Timeout Monitor
     * Warns users after 20 minutes of inactivity and logs out after 24 hours.
     */
    function initSessionMonitor() {
        let lastActivity = Date.now();
        const warningThreshold = 20 * 60 * 1000; // 20 minutes
        let warningShown = false;

        function resetActivity() {
            lastActivity = Date.now();
            warningShown = false;
        }

        ['mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
            window.addEventListener(evt, resetActivity, { passive: true });
        });

        setInterval(() => {
            const elapsed = Date.now() - lastActivity;
            if (elapsed > warningThreshold && !warningShown) {
                warningShown = true;
                showToast('You have been idle for 20 minutes. Please interact with the page to keep your session active.', 'warning', 10000);
            }
        }, 60000); // Check every minute
    }

    /**
     * 13. Auto Initialization on DOMContentLoaded
     */
    document.addEventListener('DOMContentLoaded', () => {
        // Navbar scroll effect
        const navbar = document.querySelector('.luxury-navbar');
        if (navbar) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 30) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        }

        // Initialize form validations
        initFormValidation();

        // Initialize session monitor
        initSessionMonitor();

        // Auto-close alert after 6 seconds
        const alerts = document.querySelectorAll('.luxury-alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } else {
                    alert.remove();
                }
            }, 6000);
        });
    });

})();
