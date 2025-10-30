/**
 * Modern Settings UI JavaScript
 * PAX Support Pro
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initColorPickers();
        initRangeSliders();
        initLivePreview();
        initResetButton();
        initToggles();
        initFormValidation();
    }

    /**
     * Initialize color pickers with live preview
     */
    function initColorPickers() {
        const colorInputs = document.querySelectorAll('.pax-color-input');
        
        colorInputs.forEach(input => {
            const preview = input.previousElementSibling;
            
            // Set initial color
            if (preview && preview.classList.contains('pax-color-preview')) {
                preview.style.background = input.value;
            }
            
            // Update on input
            input.addEventListener('input', function() {
                if (preview) {
                    preview.style.background = this.value;
                }
                updateLivePreview();
            });
            
            // Click preview to open color picker
            if (preview) {
                preview.addEventListener('click', function() {
                    input.click();
                });
            }
        });
    }

    /**
     * Initialize range sliders with value display
     */
    function initRangeSliders() {
        const rangeSliders = document.querySelectorAll('.pax-range-slider');
        
        rangeSliders.forEach(slider => {
            const valueDisplay = slider.parentElement.querySelector('.pax-range-value');
            const unit = slider.dataset.unit || '';
            
            // Set initial value
            if (valueDisplay) {
                valueDisplay.textContent = slider.value + unit;
            }
            
            // Update gradient
            updateSliderGradient(slider);
            
            // Update on input
            slider.addEventListener('input', function() {
                if (valueDisplay) {
                    valueDisplay.textContent = this.value + unit;
                }
                updateSliderGradient(this);
                updateLivePreview();
            });
        });
    }

    /**
     * Update slider gradient based on value
     */
    function updateSliderGradient(slider) {
        const min = slider.min || 0;
        const max = slider.max || 100;
        const value = slider.value;
        const percentage = ((value - min) / (max - min)) * 100;
        
        const color = getComputedStyle(document.documentElement)
            .getPropertyValue('--pax-primary').trim() || '#e53935';
        
        slider.style.background = `linear-gradient(to right, ${color} 0%, ${color} ${percentage}%, #ddd ${percentage}%, #ddd 100%)`;
    }

    /**
     * Initialize live preview updates
     */
    function initLivePreview() {
        // Update preview on any form change
        const form = document.querySelector('.pax-modern-settings form');
        if (!form) return;
        
        form.addEventListener('change', updateLivePreview);
        form.addEventListener('input', debounce(updateLivePreview, 300));
        
        // Initial preview update
        updateLivePreview();
    }

    /**
     * Update live preview panel
     */
    function updateLivePreview() {
        const preview = document.querySelector('.pax-preview-chat');
        if (!preview) return;
        
        // Get form values
        const colorAccent = document.querySelector('[name="color_accent"]')?.value || '#e53935';
        const colorBg = document.querySelector('[name="color_bg"]')?.value || '#0d0f12';
        const colorPanel = document.querySelector('[name="color_panel"]')?.value || '#121418';
        const colorBorder = document.querySelector('[name="color_border"]')?.value || '#2a2d33';
        const colorText = document.querySelector('[name="color_text"]')?.value || '#e8eaf0';
        
        // Apply to preview with smooth transition
        preview.style.setProperty('--preview-accent', colorAccent);
        preview.style.setProperty('--preview-bg', colorBg);
        preview.style.setProperty('--preview-panel', colorPanel);
        preview.style.setProperty('--preview-border', colorBorder);
        preview.style.setProperty('--preview-text', colorText);
        
        // Animate preview update
        preview.style.transform = 'scale(0.98)';
        setTimeout(() => {
            preview.style.transform = 'scale(1)';
        }, 100);
    }

    /**
     * Initialize reset to default button
     */
    function initResetButton() {
        const resetBtn = document.getElementById('pax-reset-defaults');
        const modal = document.getElementById('pax-reset-modal');
        const confirmBtn = document.getElementById('pax-confirm-reset');
        const cancelBtn = document.getElementById('pax-cancel-reset');
        
        if (!resetBtn || !modal) return;
        
        // Show modal
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            modal.classList.add('active');
        });
        
        // Cancel
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                modal.classList.remove('active');
            });
        }
        
        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
        
        // Confirm reset
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                resetToDefaults();
                modal.classList.remove('active');
            });
        }
    }

    /**
     * Reset all settings to defaults
     */
    function resetToDefaults() {
        const defaults = {
            enabled: true,
            enable_chat: true,
            enable_ticket: true,
            enable_console: true,
            enable_speed: true,
            enable_offline_guard: true,
            ai_assistant_enabled: true,
            openai_enabled: false,
            openai_key: '',
            openai_model: 'gpt-4o-mini',
            openai_temperature: 0.35,
            launcher_position: 'bottom-left',
            launcher_auto_open: true,
            toggle_on_click: true,
            brand_name: 'PAX SUPPORT',
            color_accent: '#e53935',
            color_bg: '#0d0f12',
            color_panel: '#121418',
            color_border: '#2a2d33',
            color_text: '#e8eaf0',
            color_sub: '#9aa0a8',
            callback_enabled: true,
            ticket_cooldown_days: 3,
            auto_update_enabled: true,
            update_check_frequency: 'daily',
            backup_local_enabled: true,
            backup_google_drive: false,
            backup_dropbox: false
        };
        
        // Apply defaults to form
        Object.keys(defaults).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (!input) return;
            
            if (input.type === 'checkbox') {
                input.checked = defaults[key];
            } else if (input.type === 'number' || input.type === 'range') {
                input.value = defaults[key];
                // Trigger input event for range sliders
                input.dispatchEvent(new Event('input'));
            } else {
                input.value = defaults[key];
            }
        });
        
        // Update preview
        updateLivePreview();
        
        // Show success message
        showSuccessMessage('Settings reset to defaults. Click "Save Changes" to apply.');
    }

    /**
     * Initialize toggle switches
     */
    function initToggles() {
        const toggles = document.querySelectorAll('.pax-toggle input');
        
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                // Add animation
                const slider = this.nextElementSibling;
                if (slider) {
                    slider.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        slider.style.transform = 'scale(1)';
                    }, 100);
                }
            });
        });
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        const form = document.querySelector('.pax-modern-settings form');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--pax-danger)';
                    setTimeout(() => {
                        field.style.borderColor = '';
                    }, 2000);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showErrorMessage('Please fill in all required fields.');
                return false;
            }
        });
    }

    /**
     * Show success message
     */
    function showSuccessMessage(message) {
        const existing = document.querySelector('.pax-success-message');
        if (existing) {
            existing.remove();
        }
        
        const messageEl = document.createElement('div');
        messageEl.className = 'pax-success-message';
        messageEl.innerHTML = `
            <span class="dashicons dashicons-yes-alt"></span>
            <span>${message}</span>
        `;
        
        document.body.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.style.opacity = '0';
            messageEl.style.transform = 'translateX(400px)';
            setTimeout(() => {
                messageEl.remove();
            }, 300);
        }, 4000);
    }

    /**
     * Show error message
     */
    function showErrorMessage(message) {
        const existing = document.querySelector('.pax-error-message');
        if (existing) {
            existing.remove();
        }
        
        const messageEl = document.createElement('div');
        messageEl.className = 'pax-success-message';
        messageEl.style.background = 'var(--pax-danger)';
        messageEl.innerHTML = `
            <span class="dashicons dashicons-warning"></span>
            <span>${message}</span>
        `;
        
        document.body.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.style.opacity = '0';
            messageEl.style.transform = 'translateX(400px)';
            setTimeout(() => {
                messageEl.remove();
            }, 300);
        }, 4000);
    }

    /**
     * Debounce function
     */
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

    /**
     * Initialize icon selection
     */
    function initIconSelection() {
        const iconOptions = document.querySelectorAll('.pax-icon-option');
        
        iconOptions.forEach(option => {
            option.addEventListener('click', function() {
                const group = this.dataset.group;
                const value = this.dataset.value;
                
                // Remove active from siblings
                document.querySelectorAll(`.pax-icon-option[data-group="${group}"]`).forEach(opt => {
                    opt.classList.remove('active');
                });
                
                // Add active to this
                this.classList.add('active');
                
                // Update hidden input
                const input = document.querySelector(`[name="${group}"]`);
                if (input) {
                    input.value = value;
                }
                
                updateLivePreview();
            });
        });
    }

    // Initialize icon selection if present
    if (document.querySelector('.pax-icon-option')) {
        initIconSelection();
    }

    // Export for external use
    window.paxSettings = {
        updatePreview: updateLivePreview,
        showSuccess: showSuccessMessage,
        showError: showErrorMessage
    };
})();
