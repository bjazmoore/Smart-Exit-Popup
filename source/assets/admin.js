jQuery(document).ready(function ($) {
    // Media uploader for image
    $('#smart-exit-upload-button').click(function (e) {
        e.preventDefault();
        const frame = wp.media({
            title: 'Select or Upload Popup Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#smart_exit_image_id').val(attachment.id);
            $('#smart-exit-image-preview').html('<img src="' + attachment.url + '" style="max-width:150px;" />');
        });

        frame.open();
    });

   
    $('#smart_exit_power_user_mode').on('change', function () {
        const isChecked = this.checked;
    
        // Remove existing banner if present
        $('.smart-exit-mode-notice').remove();
    
        const message = isChecked
            ? "🛠️ <strong>Enable Power User Mode?</strong> 🚀 Advanced HTML/SVG editing is will be allowed. Save settings to apply this mode."
            : "⚠️ <strong>Disable Power User Mode?</strong> Visual Editor will be restored. Save to confirm.<br/> ⚠️ <strong>WARNING:</strong> Disabling Power User Mode may strip advanced HTML/SVG. ⚠️";
    
        const $notice = $(`
            <div class="notice notice-info smart-exit-mode-notice" style="padding-bottom: 10px;">
                <p>${message}</p>
                <p style="margin-top: 5px;">
                    <button class="button button-primary smart-exit-save-now">💾 Save Now</button>
                    <button class="button smart-exit-dismiss">Cancel</button>
                </p>
            </div>
        `);
    
        // Inject just after <h1>
        $('.wrap h1').after($notice);
    
        // Scroll to it
        $('html, body').animate({
            scrollTop: $notice.offset().top - 40
        }, 400);
    
        // Handle save now
        $notice.find('.smart-exit-save-now').on('click', function () {
            const $submitBtn = $('.wrap form input[type="submit"]');
            if ($submitBtn.length) {
                $submitBtn[0].click();
            } else {
                alert("Unable to find the Save Settings button.");
            }
        });
    
        // Handle cancel
        $notice.find('.smart-exit-dismiss').on('click', function () {
            $notice.fadeOut(300, () => $notice.remove());
        });
    });    
    
   
    // Show/hide custom X offset field
    $('#smart_exit_image_x_position').on('change', function () {
        $('#smart-exit-custom-x-wrapper').toggle($(this).val() === 'custom');
    }).trigger('change');

    // Reset localStorage dismissal flag
    $('#smart-exit-reset-dismissal').click(function () {
        localStorage.removeItem('smartExitPopupDismissed');
        const $msg = $('<div class="notice notice-success is-dismissible"><p>Dismissal flag reset. The popup will reappear next time.</p></div>');
        $('.wrap h1').after($msg);
        setTimeout(() => $msg.fadeOut(400, () => $msg.remove()), 4000);
    });

    // Privacy info toggle
    $('#toggle-privacy-info').on('click', function (e) {
        e.preventDefault();
        const $details = $('#smart-exit-privacy-details');
        $details.slideToggle(200);
        const isVisible = $details.is(':visible');
        $(this).text(isVisible ? '🔒 Hide Privacy & Compliance Info' : '🔒 Show Privacy & Compliance Info');
    });

    // Footer dependency controls
    function toggleFooterDependencies() {
        const enabled = $('#smart_exit_show_footer').is(':checked');
        $('#smart_exit_footer_bg_color').prop('disabled', !enabled);
        $('#smart_exit_enable_dismiss_option').prop('disabled', !enabled);
    }
    $('#smart_exit_show_footer').on('change', toggleFooterDependencies);
    toggleFooterDependencies();

    // Modal height custom toggle
    $('#smart_exit_modal_height_mode').on('change', function () {
        $('#smart-exit-modal-height-custom-wrapper').toggle($(this).val() === 'custom');
    }).trigger('change');

    // Header color type toggle
    $('#smart_exit_header_color_mode').change(function () {
        $('#header-gradient-right-color').toggle($(this).val() === 'gradient');
    }).trigger('change');

    // Header text color toggle
    $('#smart_exit_header_text_color_mode').on('change', function () {
        $('#smart-exit-header-text-color-wrapper').toggle($(this).val() === 'custom');
    }).trigger('change');

    // Footer text color toggle
    $('#smart_exit_footer_text_color_mode').on('change', function () {
        $('#smart-exit-footer-text-color-wrapper').toggle($(this).val() === 'custom');
    }).trigger('change');

    // 🎨 Initialize all color pickers
    $('.wp-color-picker').wpColorPicker();

    function safeTrim(selector) {
        const val = $(selector).val();
        return val ? val.trim() : '';
    }
    
    //form submit validation
    $('form').on('submit', function (e) {
        //console.log("🛡️ Form validation beginning...");
    
        // Remove any existing notices
        $('.smart-exit-error-notice').remove();
    
        let isValid = true;
        let messages = [];

        // console.log("modal background value: ", $('#smart_exit_modal_background').val())
        // console.log("header color left: ", $('#smart_exit_header_color_left').val())
        // console.log("footer background color: ", $('#smart_exit_footer_bg_color').val())
    
        // Modal background
        if (!safeTrim('#smart_exit_modal_background')) {
            isValid = false;
            messages.push('• Modal Background Color is required.');
        }
    
        // Footer background
        if ($('#smart_exit_show_footer').is(':checked') && !safeTrim('#smart_exit_footer_bg_color')) {
            isValid = false;
            messages.push('• Footer Background Color is required.');
        }
    
        // Header background left
        if (!safeTrim('#smart_exit_header_color_left')) {
            isValid = false;
            messages.push('• Header Background Color (left) is required.');
        }
    
        // Header background right (only for gradient)
        if ($('#smart_exit_header_color_mode').val() === 'gradient' && !safeTrim('#smart_exit_header_color_right')) {
            isValid = false;
            messages.push('• Header Background Color (right - gradient) is required.');
        }
    
        // Header text color
        if ($('#smart_exit_header_text_color_mode').val() === 'custom' && !safeTrim('#smart_exit_header_text_color')) {
            isValid = false;
            messages.push('• Custom Header Text Color is required.');
        }
    
        // Footer text color
        if ($('#smart_exit_footer_text_color_mode').val() === 'custom' && !safeTrim('#smart_exit_footer_text_color')) {
            isValid = false;
            messages.push('• Custom Footer Text Color is required.');
        }

        // Require image if image checkbox is checked
        const imageId = $('#smart_exit_image_id').val();
        if ($('#smart_exit_show_image').is(':checked')) { 
            if (imageId === "" || imageId === "0") {
                isValid = false;
                messages.push('• An image must be selected if the "Enable Image in Popup" option is checked.');
            }
        }

        if (!isValid) {
            e.preventDefault();
        
            const $notice = $(`
                <div class="notice notice-error smart-exit-error-notice">
                    <p><strong>Smart Exit Popup - Please fix the following:</strong></p>
                    <ul style="margin-left: 1.5em;">
                        ${messages.map(msg => `<li>${msg}</li>`).join('')}
                    </ul>
                </div>
            `);
        
            const $anchor = $('.wrap h1');
            $anchor.after($notice);
        
            // Scroll to the notice smoothly
            $('html, body').animate({
                scrollTop: $notice.offset().top - 40
            }, 500);
        }
        
    });    
    
});
