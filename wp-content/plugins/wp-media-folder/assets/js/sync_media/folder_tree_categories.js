(function ($) {
    $(document).ready(function () {
        var options_categories = {
            'root': '/',
            'showroot': 'root',
            'onclick': function (elem, type, file) {
            },
            'oncheck': function (elem, checked, type, file) {
            },
            'usecheckboxes': false, //can be true files dirs or false
            'expandSpeed': 500,
            'collapseSpeed': 500,
            'expandEasing': null,
            'collapseEasing': null,
            'canselect': true
        };

        var methods_categories = {
            init_categories: function (o) {
                if ($(this).length == 0) {
                    return;
                }
                $thiscategories = $(this);
                $.extend(options_categories, o);
                if (options_categories.showroot != '') {
                    $thiscategories.html('<ul class="jaofiletree"><li class="drive directory collapsed selected"><a href="#" data-file="' + options_categories.root + '" data-type="dir">' + options_categories.showroot + '</a></li></ul>');
                }
                openfolder_categories(options_categories.root);
            },
            open_categories: function (dir) {
                openfolder_categories(dir);
            },
            close_categories: function (dir) {
                closedir_categories(dir);
            },
        };

        openfolder_categories = function (dir , callback) {
            if (typeof $thiscategories == "undefined")
                return;

            var id = $thiscategories.find('a[data-file="' + dir + '"]').data('id');
            if ($thiscategories.find('a[data-file="' + dir + '"]').parent().hasClass('expanded') || $thiscategories.find('a[data-file="' + dir + '"]').parent().hasClass('wait')) {
                if (typeof callback === 'function')
                    callback();
                return;
            }

            if(typeof id == 'undefined') id = 0;
            var ret;
            ret = $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {dir: dir, id: id, action: 'get_terms', 'wpmf_display_media': 'all'},
                context: $thiscategories,
                dataType: 'json',
                beforeSend: function () {
                    $('#wpmf_foldertree_categories').find('a[data-file="' + dir + '"]').parent().addClass('wait');
                }
            }).done(function (datas) {
                ret = '<ul class="jaofiletree" style="display: none">';
                for (ij = 0; ij < datas.length; ij++) {
                    if(wpmflangoption.root_media_root != datas[ij].id) {
                        if (datas[ij].type == 'dir') {
                            classe = 'directory collapsed';
                        } else {
                            classe = 'file ext_' + datas[ij].ext;
                        }

                        if (datas[ij].id === id.toString()) {
                            classe += ' selected';
                        }

                        ret += '<li class="' + classe + '" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-group="' + datas[ij].term_group + '">';
                        if (datas[ij].count_child > 0) {
                            ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '"></div>';
                        } else {
                            ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '" style="opacity:0"></div>';
                        }

                        ret += '<a href="#" class="title-folder" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '">' + datas[ij].file + '</a>';
                        ret += '</li>';
                    }
                }
                ret += '</ul>';
                $('#wpmf_foldertree_categories').find('a[data-file="' + dir + '"]').parent().removeClass('wait').removeClass('collapsed').addClass('expanded');
                $('#wpmf_foldertree_categories').find('a[data-file="' + dir + '"]').after(ret);
                $('#wpmf_foldertree_categories').find('a[data-file="' + dir + '"]').next().slideDown(options_categories.expandSpeed, options_categories.expandEasing,
                    function () {
                        $thiscategories.trigger('afteropen');
                        $thiscategories.trigger('afterupdate');
                        if (typeof callback === 'function')
                            callback();
                    });

                $('.dir_name_categories').val(dir.replace("\\", "/"));
                $('.dir_name_categories').data('id_category' , id);
                setevents_categories();

            }).done(function () {
                $thiscategories.trigger('afteropen');
                $thiscategories.trigger('afterupdate');
            });

        }

        closedir_categories = function (dir) {
            $thiscategories.find('a[data-file="' + dir + '"]').next().slideUp(options_categories.collapseSpeed, options_categories.collapseEasing, function () {
                $(this).remove();
            });
            $thiscategories.find('a[data-file="' + dir + '"]').parent().removeClass('expanded').addClass('collapsed');
            $('.dir_name_categories').val('');
            $('.dir_name_categories').data('id_category' , 0);
            setevents_categories();

            //Trigger custom event
            $thiscategories.trigger('afterclose');
            $thiscategories.trigger('afterupdate');

        }

        setevents_categories = function () {
            $thiscategories = $('#wpmf_foldertree_categories');
            $thiscategories.find('li a').unbind('click');
            //Bind userdefined function on click an element
            $thiscategories.find('li a').bind('click', function () {
                options_categories.onclick(this, $(this).attr('data-type'), $(this).attr('data-file'));
                if (options_categories.canselect) {
                    $thiscategories.find('li').removeClass('selected');
                    $(this).parent().addClass('selected');
                }
                return false;
            });

            //Bind for collapse or expand elements
            $thiscategories.find('li.directory.collapsed a').bind('click', function () {
                methods_categories.open_categories($(this).attr('data-file'));
                return false;
            });
            $thiscategories.find('li.directory.expanded a').bind('click', function () {
                methods_categories.close_categories($(this).attr('data-file'));
                return false;
            });
        }

        $.fn.jaofiletreecategories = function (method) {
            // Method calling logic
            if (methods_categories[method]) {
                return methods_categories[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
            } else if (typeof method === 'object' || !method) {
                return methods_categories.init_categories.apply(this, arguments);
            } else {
                //error
            }
        };
    });
})(jQuery);