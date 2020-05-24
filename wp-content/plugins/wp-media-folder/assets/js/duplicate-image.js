(function ($) {
    if (typeof ajaxurl == "undefined") {
        ajaxurl = wpmflang.ajaxurl;
    }
    $(document).ready(function () {
        $(document).on('click', '.wpmf_btn_duplicate', function (event) {
            $('.wpmf_spinner_duplicate').show().css('visibility', 'visible');
            $('.wpmf_message_duplicate').html(null);
            var id = $('.attachment-details').data('id');
            if (typeof id != 'undefined') {
                $.ajax({
                    method: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        action: 'wpmf_duplicate_file',
                        id: id
                    },
                    success: function (res) {
                        $('.wpmf_spinner_duplicate').hide();
                        if (res.status == true) {
                            $('.wpmf_message_duplicate').html('<div class="updated">' + res.message + '</div>');
                        } else {
                            $('.wpmf_message_duplicate').html('<div class="error">' + res.message + '</div>');
                        }

                        if (page != 'table' && wpmflang.wpmf_pagenow != 'upload.php') {
                            setTimeout(function () {
                                wp.Uploader.queue.reset();
                            }, 1000);

                        }
                    }
                });
            }
        });
        
        var myduplicateForm = wp.media.view.AttachmentsBrowser;
        var form_uplicate = '<a class="wpmf_btn_duplicate">'+ wpmflang.duplicate_text +'</a><span class="spinner wpmf_spinner_duplicate"></span><p class="wpmf_message_duplicate"></p>';
        wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
            createSingle: function() {
                myduplicateForm.prototype.createSingle.apply(this, arguments);
                var sidebar = this.sidebar;
                var single = this.options.selection.single();
                if (wpmflang.wpmf_pagenow != 'upload.php') {
                    if (typeof wpmflang.duplicate != 'undefined' && wpmflang.duplicate == 1) {
                        $(sidebar.$el).find('.attachment-info .details').append(form_uplicate);
                    }
                }
            }
        });
        
        var myDuplicate = wp.media.view.Modal;
        wp.media.view.Modal = wp.media.view.Modal.extend({
            open: function () {
                myDuplicate.prototype.open.apply(this, arguments);
                if (wpmflang.wpmf_pagenow == 'upload.php') {
                    if (typeof wpmflang.duplicate != 'undefined' && wpmflang.duplicate == 1) {
                        $('.details').append(form_uplicate);
                    }
                }
            }
        });
    });
}(jQuery));