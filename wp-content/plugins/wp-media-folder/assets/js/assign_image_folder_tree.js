(function ($) {
    $(document).ready(function () {
        if (typeof wp != "undefined") {
            if (wp.media && $('body.upload-php table.media').length === 0) {
                if (wp.media.view.AttachmentFilters == undefined || wp.media.view.AttachmentsBrowser == undefined)
                    return;

                var options = {
                    'root': '/',
                    'showroot': wpmflang.assign_tree_label,
                    'onclick': function (elem, type, file) {},
                    'oncheck': function (elem, checked, type, file) {},
                    'usecheckboxes': true, //can be true files dirs or false
                    'expandSpeed': 500,
                    'collapseSpeed': 500,
                    'expandEasing': null,
                    'collapseEasing': null,
                    'canselect': true
                };

                var methods = {
                    init: function (o) {
                        if ($(this).length == 0) {
                            return;
                        }
                        $assignimagetree = $(this);
                        $.extend(options, o);

                        var attachment_id = $('.attachment-details').data('id');
                        if (typeof attachment_id == "undefined")
                            attachment_id = $('#post_ID').val();

                        if (wpmflang.wpmf_role == 'administrator') {
                            if (options.showroot != '') {
                                var tree_init = '';
                                tree_init += '<ul class="jaofiletree">';
                                tree_init += '<li data-id="0" class="directory collapsed selected" data-group="' + wpmflang.wpmf_curent_userid + '">'
                                tree_init += '<div class="pure-checkbox">';
                                tree_init += '<input type="checkbox" id="/" class="wpmf_checkbox_tree" value="wpmf_' + wpmflang.root_media_root + '" data-id="' + wpmflang.root_media_root + '" data-file="/" data-type="dir">';
                                tree_init += '<label class="checked" for="/">';
                                tree_init += '<a for="/" class="title-folder title-root" data-id="0" data-file="' + options.root + '" data-type="dir">' + options.showroot + '</a>';
                                tree_init += '</label>';
                                tree_init += '</div>';
                                tree_init += '</li>';
                                tree_init += '</ul>';
                                $assignimagetree.html(tree_init);
                            }
                            openfolderassign(attachment_id,options.root);
                        } else {
                            if (wpmflang.wpmf_active_media == 1 && wpmflang.term_root_id) {
                                $assignimagetree.html('<ul class="jaofiletree"><li  data-id="' + wpmflang.term_root_id + '"  class="directory collapsed selected"><a class="title-folder title-root" data-id="' + wpmflang.term_root_id + '" data-file="/' + wpmflang.term_root_username + '/" data-type="dir">' + wpmflang.term_root_username + '</a><a class="title-wpmfaddFolder"><i class="material-icons wpmf_icon_newfolder wpmfaddFolder">create_new_folder</i></a></li></ul>');
                                openfolderassign(attachment_id,'/' + wpmflang.term_root_username + '/');
                            } else {
                                if (options.showroot != '') {
                                    $assignimagetree.html('<ul class="jaofiletree"><li  data-id="0"  class="directory collapsed selected"><a class="title-folder title-root" data-id="0" data-file="' + options.root + '" data-type="dir">' + options.showroot + '</a><a class="title-wpmfaddFolder"><i class="material-icons wpmf_icon_newfolder wpmfaddFolder">create_new_folder</i></a></li></ul>');
                                }
                                openfolderassign(attachment_id,options.root);
                            }
                        }
                    },
                    open: function (dir) {
                        var attachment_id = $('.attachment-details').data('id');
                        if (typeof attachment_id == "undefined")
                            attachment_id = $('#post_ID').val();
                        openfolderassign(attachment_id,dir);
                    },
                    close: function (dir) {
                        closedirassign(dir);
                    },
                    getchecked: function () {
                        var list = new Array();
                        var ik = 0;
                        $assignimagetree.find('input:checked + a').each(function () {
                            list[ik] = {
                                type: $(this).attr('data-type'),
                                file: $(this).attr('data-file')
                            }
                            ik++;
                        });
                        return list;
                    },
                    getselected: function () {
                        var list = new Array();
                        var ik = 0;
                        $assignimagetree.find('li.selected > a').each(function () {
                            list[ik] = {
                                type: $(this).attr('data-type'),
                                file: $(this).attr('data-file')
                            }
                            ik++;
                        });
                        return list;
                    }
                };

                openfolderassign = function (attachment_id,dir, callback) {
                    if (typeof $assignimagetree == "undefined")
                        return;
                    var id = $assignimagetree.find('a[data-file="' + dir + '"]').data('id');
                    if ($assignimagetree.find('a[data-file="' + dir + '"]').closest('li').hasClass('expanded') || $assignimagetree.find('a[data-file="' + dir + '"]').closest('li').hasClass('wait')) {
                        if (typeof callback === 'function')
                            callback();
                        return;
                    }
                    var ret;
                    ret = $.ajax({
                        method: 'POST',
                        url: ajaxurl,
                        data: {
                            dir: dir,
                            id: id,
                            attachment_id: attachment_id,
                            action: 'get_assign_tree'
                        },
                        context: $assignimagetree,
                        dataType: 'json',
                        beforeSend: function () {
                            this.find('a[data-file="' + dir + '"]').closest('li').addClass('wait');
                        }
                    }).done(function (res) {

                        var selectedId = $('#wpmfjaoassign .directory.selected').data('id');
                        ret = '<ul class="jaofiletree">';
                        if(res.status){
                            var datas = res.dirs;
                            if((!$('.media-frame').hasClass('mode-select') && $('body').hasClass('upload-php')) || !$('body').hasClass('upload-php')){
                                if(res.root_check){
                                    $('.wpmf_checkbox_tree[data-id="'+ wpmflang.root_media_root +'"]').prop('checked',true);
                                }
                            }

                            for (ij = 0; ij < datas.length; ij++) {
                                if(wpmflang.root_media_root != datas[ij].id){
                                    if (datas[ij].type == 'dir') {
                                        classe = 'directory collapsed';
                                    } else {
                                        classe = 'file ext_' + datas[ij].ext;
                                    }

                                    if (parseInt(datas[ij].id) === parseInt(selectedId)) {
                                        classe += ' selected';
                                    }

                                    ret += '<li class="' + classe + '" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-group="' + datas[ij].term_group + '">';
                                    if (datas[ij].count_child > 0) {
                                        ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '"></div>';
                                    } else {
                                        ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '" style="opacity:0"></div>';
                                    }

                                    ret += '<div class="pure-checkbox">';

                                    if($('.media-frame').hasClass('mode-select') && $('body').hasClass('upload-php')){
                                        ret += '<input type="checkbox" id="' + dir + datas[ij].file + '/" class="wpmf_checkbox_tree" value="wpmf_' + datas[ij].id + '" data-id="' + datas[ij].id + '" data-file="' + dir + datas[ij].file + '" data-type="' + datas[ij].type + '">';
                                    }else{
                                        if(datas[ij].checked){
                                            ret += '<input type="checkbox" checked id="' + dir + datas[ij].file + '/" class="wpmf_checkbox_tree" value="wpmf_' + datas[ij].id + '" data-id="' + datas[ij].id + '" data-file="' + dir + datas[ij].file + '" data-type="' + datas[ij].type + '">';
                                        }else{
                                            ret += '<input type="checkbox" id="' + dir + datas[ij].file + '/" class="wpmf_checkbox_tree" value="wpmf_' + datas[ij].id + '" data-id="' + datas[ij].id + '" data-file="' + dir + datas[ij].file + '" data-type="' + datas[ij].type + '">';
                                        }
                                    }

                                    if(datas[ij].checked){
                                        ret += '<label class="check" for="' + dir + datas[ij].file + '/">';
                                    }else{
                                        if(datas[ij].pchecked){
                                            ret += '<label class="pchecked" for="' + dir + datas[ij].file + '/">';
                                            ret += '<span class="ppp"></span>'
                                        }else{
                                            ret += '<label for="' + dir + datas[ij].file + '/">';
                                        }
                                    }

                                    if (parseInt(datas[ij].id) === parseInt(selectedId)) {
                                        ret += '<i class="zmdi wpmf-zmdi-folder-open"></i>';
                                    } else {
                                        ret += '<i class="zmdi zmdi-folder"></i>';
                                    }
                                    ret += '<a for="wpmf_' + datas[ij].id + '" class="title-folder" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '">' + datas[ij].file + '</a>';
                                    ret += '</label>';
                                    ret += '</div';
                                    ret += '</li>';
                                }
                            }
                        }
                        ret += '</ul>';

                        this.find('a[data-file="' + dir + '"]').closest('li').removeClass('wait').removeClass('collapsed').addClass('expanded');
                        this.find('a[data-file="' + dir + '"]').closest('li').append(ret);
                        this.find('a[data-file="' + dir + '"]').closest('li').children('.jaofiletree').slideDown(options.expandSpeed, options.expandEasing,
                            function () {
                                $assignimagetree.trigger('afteropen');
                                $assignimagetree.trigger('afterupdate');
                                if (typeof callback === 'function')
                                    callback();
                            });

                        seteventsassign();

                    }).done(function () {
                        $assignimagetree.trigger('afteropen');
                        $assignimagetree.trigger('afterupdate');
                    });

                }

                closedirassign = function (dir) {

                    if (typeof $assignimagetree == "undefined")
                        return;
                    $assignimagetree.find('a[data-file="' + dir + '"]').closest('li').children('.jaofiletree').slideUp(options.collapseSpeed, options.collapseEasing, function () {
                        $(this).remove();
                    });

                    $assignimagetree.find('a[data-file="' + dir + '"]').closest('li').removeClass('expanded').addClass('collapsed');
                    seteventsassign();

                    //Trigger custom event
                    $assignimagetree.trigger('afterclose');
                    $assignimagetree.trigger('afterupdate');
                };

                seteventsassign = function () {
                    $assignimagetree = $('#wpmfjaoassign');
                    $assignimagetree.find('li a,li .icon-open-close').unbind('click');
                    //Bind for collapse or expand elements
                    $assignimagetree.find('li.directory a').bind('click', function (e) {
                        e.preventDefault();
                        if(!$(this).hasClass('wpmfaddFolder')){
                            var id = $(this).data('id');
                            if (page !== 'table') {
                                $assignimagetree.find('li').removeClass('selected');
                                $assignimagetree.find('i.zmdi').removeClass('wpmf-zmdi-folder-open').addClass("zmdi-folder");
                                $(this).closest('li').addClass("selected");
                                $(this).closest('li').find(' > .pure-checkbox i.zmdi').removeClass("zmdi-folder").addClass("wpmf-zmdi-folder-open");
                                methods.open($(this).attr('data-file'));
                            }
                        }

                    });

                    $assignimagetree.find('li.directory.collapsed .icon-open-close').bind('click', function (e) {
                        e.preventDefault;
                        methods.open($(this).attr('data-file'));
                    });

                    $assignimagetree.find('li.directory.expanded .icon-open-close').bind('click', function (e) {
                        e.preventDefault;
                        methods.close($(this).attr('data-file'));
                    });

                    $assignimagetree.find('li.directory.expanded .wpmf_checkbox_tree').bind('click', function (e) {
                        e.preventDefault;
                        if($(this).is(':checked')){
                            $(this).closest('.pure-checkbox').find('label').removeClass('pchecked').addClass('checked');
                        }else{
                            $(this).closest('.pure-checkbox').find('label').removeClass('checked');
                        }
                    });


                }

                $.fn.jaofiletreeassign = function (method) {
                    // Method calling logic
                    if (methods[method]) {
                        return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
                    } else if (typeof method === 'object' || !method) {
                        return methods.init.apply(this, arguments);
                    } else {
                        //error
                    }
                };

                function wpmf_set_term() {
                    var wpmf_term_ids_check = [];
                    var wpmf_term_ids_notcheck = [];
                    $('.wpmf_checkbox_tree').each(function (i,v) {
                        if($(v).is(':checked')){
                            wpmf_term_ids_check.push($(v).data('id'));
                        }else{
                            wpmf_term_ids_notcheck.push($(v).data('id'));
                        }
                    });

                    var selectedId = $('.wpmf-categories option:selected').data('id');
                    if(selectedId == 0) selectedId = wpmflang.root_media_root;
                    selectedId = parseInt(selectedId);
                    var attachment_id = $('.attachment-details').data('id');
                    if (typeof attachment_id == "undefined")
                        attachment_id = $('#post_ID').val();
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'wpmf_set_object_term',
                            wpmf_term_ids_check: wpmf_term_ids_check.join(),
                            wpmf_term_ids_notcheck: wpmf_term_ids_notcheck.join(),
                            attachment_id: attachment_id,
                            type: 'one'
                        },
                        success: function(res){
                            wpmfcheckmove = true;
                            if($.inArray( selectedId, res ) != '-1'){
                                $('li.attachment[data-id="'+ attachment_id +'"]').show();
                            }else{
                                $('li.attachment[data-id="'+ attachment_id +'"]').hide();
                            }

                            if(selectedId == 0 && res.length == 0){
                                $('li.attachment[data-id="'+ attachment_id +'"]').show();
                            }

                            $('#snackbar-container .snackbar[data-wpmftype="folder_selection"]').remove();
                            $('.wpmf-folder_selection').snackbar("toggle");
                            wpmf_undo();
                        }
                    });
                }

                function treeshowdialog(){
                    $('.open-popup-tree').on('click',function () {
                        if($('.wpmf-folder_selection').length == 0){
                            $('body').append('<div class="wpmf-folder_selection" data-wpmftype="folder_selection" data-timeout="3000" data-html-allowed="true" data-content="'+ wpmflang.folder_selection +'"></div>');
                        }

                        showDialog({
                            title: wpmflang.label_assign_tree,
                            text: '<span id="wpmfjaoassign"></span>',
                            negative: {
                                title: wpmflang.cancel
                            },
                            positive: {
                                title: wpmflang.label_apply,
                                onClick: function (e) {
                                    wpmf_set_term();
                                }
                            }
                        });
                        //var sdir = '/';
                        $('#wpmfjaoassign').jaofiletreeassign({
                            onclick: function (elem, type, file) {}
                        });
                    });
                }

                function genFormassigntree(id) {
                    var html = '<div class="wpmfjaoassign_row"><div class="wpmfjaoassign_left"></div><div class="wpmfjaoassign_right"><a class="open-popup-tree"><i class="zmdi zmdi-folder"></i>' + wpmflang.assign_tree_label + '</a></div></div>';
                    return html;
                }

                var wpmfAssigntreeform = wp.media.view.AttachmentsBrowser;
                wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
                    createSingle: function() {
                        wpmfAssigntreeform.prototype.createSingle.apply(this, arguments);
                        var sidebar = this.sidebar;
                        var single = this.options.selection.single();

                        var wpmf_form_tree = genFormassigntree(single.id);
                        if (wpmflang.wpmf_pagenow != 'upload.php') {
                            $(sidebar.$el).find('.attachment-details').append(wpmf_form_tree);
                            treeshowdialog();
                        }

                    }
                });

                var wpmfAssigntree = wp.media.view.Modal;
                wp.media.view.Modal = wp.media.view.Modal.extend({
                    open: function () {
                        wpmfAssigntree.prototype.open.apply(this, arguments);
                        if (wpmflang.wpmf_pagenow == 'upload.php') {
                            var attachmentID = $('.attachment-details').data('id');
                            var wpmf_form_tree = genFormassigntree(attachmentID);
                            $('.attachment-info .settings').append(wpmf_form_tree);
                            treeshowdialog();
                        }
                    }
                });
            }
        }
    });
}(jQuery));