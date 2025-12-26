/**
 * Custom Login URL - Admin JavaScript (Vanilla JS)
 * 
 * @package Custom_Login_URL
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    CLU_Admin.init();
});

var CLU_Admin = {

    slugInput: null,
    slugPreview: null,
    loginUrlBox: null,
    settingsForm: null,

    init: function() {
        this.slugInput = document.getElementById('clu_login_slug');
        this.slugPreview = document.getElementById('clu-slug-preview');
        this.loginUrlBox = document.querySelector('.clu-login-url');
        this.settingsForm = document.querySelector('.clu-settings-form');

        if (this.slugInput) {
            this.bindEvents();
            this.validateSlug();
        }
    },

    bindEvents: function() {
        var self = this;

        this.slugInput.addEventListener('input', function() {
            self.updatePreview();
            self.validateSlug();
        });

        this.slugInput.addEventListener('blur', function() {
            self.sanitizeSlug();
        });

        if (this.settingsForm) {
            this.settingsForm.addEventListener('submit', function(e) {
                if (!self.validateSlug()) {
                    e.preventDefault();
                    self.showError('Please enter a valid login slug (lowercase letters, numbers, and hyphens only).');
                    return false;
                }
            });
        }

        var loginLinks = document.querySelectorAll('.clu-login-link');
        loginLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                self.copyToClipboard(link.href);
            });
        });
    },

    updatePreview: function() {
        var slug = this.slugInput.value.toLowerCase().replace(/[^a-z0-9\-]/g, '');

        this.slugPreview.textContent = slug || 'login';
        this.highlightPreview();
    },

    highlightPreview: function() {
        this.loginUrlBox.classList.add('highlight');
        var self = this;
        setTimeout(function() {
            self.loginUrlBox.classList.remove('highlight');
        }, 600);
    },

    validateSlug: function() {
        var slug = this.slugInput.value;
        var pattern = /^[a-z0-9\-]+$/;
        var reserved = ['wp-admin', 'wp-content', 'wp-includes', 'admin', 'login', 'wp-login', 'wp-login.php'];

        if (!slug || slug.trim() === '') {
            this.slugInput.classList.add('invalid');
            return false;
        }

        if (!pattern.test(slug)) {
            this.slugInput.classList.add('invalid');
            return false;
        }

        if (reserved.includes(slug)) {
            this.slugInput.classList.add('invalid');
            this.showError('This slug is reserved. Please choose a different one.');
            return false;
        }

        this.slugInput.classList.remove('invalid');
        return true;
    },

    sanitizeSlug: function() {
        var slug = this.slugInput.value;
        var sanitized = slug.toLowerCase()
                            .replace(/[^a-z0-9\-]/g, '')
                            .replace(/\-+/g, '-')
                            .replace(/^\-|\-$/g, '');

        if (sanitized !== slug) {
            this.slugInput.value = sanitized;
            this.updatePreview();
        }
    },

    copyToClipboard: function(text) {
        var tempInput = document.createElement('input');
        document.body.appendChild(tempInput);
        tempInput.value = text;
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // for mobile devices

        try {
            document.execCommand('copy');
            this.showSuccess('Login URL copied to clipboard!');
        } catch (err) {
            this.showError('Failed to copy URL. Please copy it manually.');
        }

        document.body.removeChild(tempInput);
    },

    showError: function(message) {
        this.showNotice(message, 'error');
    },

    showSuccess: function(message) {
        this.showNotice(message, 'success');
    },

    showNotice: function(message, type) {
        var existing = document.querySelectorAll('.clu-temp-notice');
        existing.forEach(function(el) { el.remove(); });

        var notice = document.createElement('div');
        notice.className = 'notice notice-' + type + ' is-dismissible clu-temp-notice';
        notice.innerHTML = '<p>' + message + '</p>';

        var dismiss = document.createElement('button');
        dismiss.type = 'button';
        dismiss.className = 'notice-dismiss';
        dismiss.innerHTML = '<span class="screen-reader-text">Dismiss</span>';
        dismiss.addEventListener('click', function() {
            notice.remove();
        });

        notice.appendChild(dismiss);

        var container = document.querySelector('.clu-settings-wrap h1');
        container.parentNode.insertBefore(notice, container.nextSibling);

        setTimeout(function() {
            if (notice.parentNode) {
                notice.remove();
            }
        }, 5000);

        window.scrollTo({
            top: notice.offsetTop - 50,
            behavior: 'smooth'
        });
    }

};
