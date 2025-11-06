/**
 * Custom Login URL - Admin JavaScript
 * 
 * @package Custom_Login_URL
 * @version 1.0.0
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        CLU_Admin.init();
    });
    
    var CLU_Admin = {
        
        init: function() {
            this.slugInput = $('#clu_login_slug');
            this.slugPreview = $('#clu-slug-preview');
            this.loginUrlBox = $('.clu-login-url');
            this.settingsForm = $('.clu-settings-form');
            
            if (this.slugInput.length) {
                this.bindEvents();
                this.validateSlug();
            }
        },
        
        bindEvents: function() {
            var self = this;
            
            this.slugInput.on('input', function() {
                self.updatePreview();
                self.validateSlug();
            });
            
            this.slugInput.on('blur', function() {
                self.sanitizeSlug();
            });
            
            this.settingsForm.on('submit', function(e) {
                if (!self.validateSlug()) {
                    e.preventDefault();
                    self.showError('Please enter a valid login slug (lowercase letters, numbers, and hyphens only).');
                    return false;
                }
            });
            
            $('.clu-login-link').on('click', function(e) {
                e.preventDefault();
                self.copyToClipboard($(this).attr('href'));
            });
        },
        
        updatePreview: function() {
            var slug = this.slugInput.val().toLowerCase().replace(/[^a-z0-9\-]/g, '');
            
            if (slug) {
                this.slugPreview.text(slug);
                this.highlightPreview();
            } else {
                this.slugPreview.text('login');
            }
        },
        
        highlightPreview: function() {
            this.loginUrlBox.addClass('highlight');
            var self = this;
            setTimeout(function() {
                self.loginUrlBox.removeClass('highlight');
            }, 600);
        },
        
        validateSlug: function() {
            var slug = this.slugInput.val();
            var pattern = /^[a-z0-9\-]+$/;
            var reserved = ['wp-admin', 'wp-content', 'wp-includes', 'admin', 'login', 'wp-login', 'wp-login.php'];
            
            if (!slug || slug.trim() === '') {
                this.slugInput.addClass('invalid');
                return false;
            }
            
            if (!pattern.test(slug)) {
                this.slugInput.addClass('invalid');
                return false;
            }
            
            if (reserved.indexOf(slug) !== -1) {
                this.slugInput.addClass('invalid');
                this.showError('This slug is reserved. Please choose a different one.');
                return false;
            }
            
            this.slugInput.removeClass('invalid');
            return true;
        },
        
        sanitizeSlug: function() {
            var slug = this.slugInput.val();
            var sanitized = slug.toLowerCase()
                               .replace(/[^a-z0-9\-]/g, '')
                               .replace(/\-+/g, '-')
                               .replace(/^\-|\-$/g, '');
            
            if (sanitized !== slug) {
                this.slugInput.val(sanitized);
                this.updatePreview();
            }
        },
        
        copyToClipboard: function(text) {
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            
            try {
                document.execCommand('copy');
                this.showSuccess('Login URL copied to clipboard!');
            } catch (err) {
                this.showError('Failed to copy URL. Please copy it manually.');
            }
            
            $temp.remove();
        },
        
        showError: function(message) {
            this.showNotice(message, 'error');
        },
        
        showSuccess: function(message) {
            this.showNotice(message, 'success');
        },
        
        showNotice: function(message, type) {
            $('.clu-temp-notice').remove();
            
            var $notice = $('<div>', {
                'class': 'notice notice-' + type + ' is-dismissible clu-temp-notice',
                'html': '<p>' + message + '</p>'
            });
            
            $('.clu-settings-wrap h1').after($notice);
            
            $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>');
            
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            });
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
            
            $('html, body').animate({
                scrollTop: $notice.offset().top - 50
            }, 300);
        }
    };
    
})(jQuery);
