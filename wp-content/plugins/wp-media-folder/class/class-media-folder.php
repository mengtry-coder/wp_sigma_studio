<?php
require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-main-function.php' );
class Wp_Media_Folder extends Wpmf_main{
    public $wpmf_folder_root_id = 0;
    function __construct() {
        $wpmf_folder_root_id = get_option('wpmf_folder_root_id');
        $this->wpmf_folder_root_id = $wpmf_folder_root_id;
        add_action('init', array($this, 'wpmf_session_start'), 1);
        if (is_plugin_active('wp-sweep/wp-sweep.php')) {
            add_action('admin_init', array($this, 'wpmf_update_count_term'));
        }

        if (!get_option('_wpmf_import_notice_flag', false)) {
            add_action('admin_notices', array($this, 'wpmf_whow_notice'), 3);
        }

        if (!get_option('_wpmf_import_size_notice_flag', false)) {
            add_action('admin_notices', array($this, 'wpmf_whow_notice_import_size'), 3);
        }

        if (!get_option('wpmf_use_taxonomy', false)) {
            add_option('wpmf_use_taxonomy', 1, '', 'yes');
        }
        add_action('wp_ajax_wpmf_import', array($this, 'wpmf_import_categories'));
        add_action('restrict_manage_posts', array($this, 'wpmf_add_image_category_filter'));
        add_action('pre_get_posts', array($this, 'wpmf_pre_get_posts1'));
        add_action('admin_enqueue_scripts', array($this, 'wpmf_admin_page_table_script'));
        add_action('wp_enqueue_media', array($this, 'wpmf_media_page_table_script'));
        add_action('admin_head', array($this, 'wpmf_admin_head'));
        add_action('pre_get_posts', array($this, 'wpmf_pre_get_posts'), 0, 1);
        add_action('wp_ajax_change_folder', array($this, 'wpmf_change_folder'));
        add_filter('add_attachment', array($this, 'wpmf_after_upload'), 10, 1);
        add_action('wp_ajax_add_folder', array($this, 'wpmf_add_folder'));
        add_action('wp_ajax_edit_folder', array($this, 'wpmf_edit_folder'));
        add_action('wp_ajax_delete_folder', array($this, 'wpmf_delete_folder'));

        add_action('wp_ajax_move_file', array($this, 'wpmf_move_file'));
        add_action('wp_ajax_move_folder', array($this, 'wpmf_move_folder'));
        add_action('wp_ajax_get_terms', array($this, 'wpmf_get_terms'));
        add_action('wp_ajax_get_assign_tree', array($this, 'wpmf_get_assign_tree'));
        add_action('wp_ajax_wpmf_change_view', array($this, 'wpmf_change_view'));
        add_action('wp_ajax_wpmf_remove_view', array($this, 'wpmf_remove_view'));
        add_action('wp_ajax_wpmf_gallery_get_image', array($this, 'wpmf_gallery_get_image'));
        add_action('wp_ajax_wpmf_undo', array($this, 'wpmf_undo'));
        add_action('wp_ajax_wpmf_undo_deleteFolder', array($this, 'wpmf_undo_deleteFolder'));
        add_action('wp_ajax_wpmf_last_filter', array($this, 'wpmf_last_filter'));
        add_action('wp_ajax_wpmf_delete_cookie_sort', array($this, 'wpmf_delete_cookie_sort'));
        add_action('wp_ajax_wpmf_set_object_term', array($this, 'wpmf_set_object_term'));
        add_action('wp_ajax_wpmf_create_remote_video', array($this, 'wpmf_create_remote_video'));
        add_action('wp_ajax_wpmf_get_user_media_tree', array($this, 'wpmf_get_user_media_tree'));
        add_filter('media_send_to_editor', array($this, 'add_remote_video'), 10, 3);
        add_filter('wpmf_afinal_output', array($this, 'wpmf_svgs_final_output'));
        add_filter('upload_mimes', array($this, 'wpmf_svgs_upload_mimes'));
        add_filter('wp_check_filetype_and_ext', array($this,'wpmf_svgs_disable_real_mime_check'), 10, 4 );
        add_filter( 'wp_prepare_attachment_for_js', array($this,'wpmf_svgs_response_for_svg'), 10, 3 );
        add_action('admin_footer', array($this, 'add_editor_footer'));
        add_action( 'admin_print_styles', array($this, 'wpmf_admin_inline_style' ));
        add_action( 'add_attachment', array($this,'wpmf_update_file_title') );
        add_filter("attachment_fields_to_edit", array($this, "wpmf_attachment_fields_to_edit"), 10, 2);
        add_filter("attachment_fields_to_save", array($this, "wpmf_attachment_fields_to_save"), 10, 2);
		add_action( 'wpml_media_create_duplicate_attachment', array($this, "wpmf_media_set_files_to_folder_wpml"),10,2 );
    }

    public function wpmf_media_set_files_to_folder_wpml($attachment_id, $duplicated_attachment_id){
        $terms = get_the_terms($attachment_id , WPMF_TAXO);
        if(!empty($terms)) {
            foreach ($terms as $term) {
                $this->wpmf_add_attachment_langguages($attachment_id, $term->term_id);
            }
        }
    }

    public function wpmf_svgs_response_for_svg( $response, $attachment, $meta ) {
        if( $response['mime'] == 'image/svg+xml' && empty( $response['sizes'] ) ) {
            $svg_path = get_attached_file( $attachment->ID );
            $dimensions = $this->wpmf_svgs_get_dimensions( $svg_path );

            $response[ 'sizes' ] = array(
                'full' => array(
                    'url' => $response[ 'url' ],
                    'width' => $dimensions->width,
                    'height' => $dimensions->height,
                    'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait'
                )
            );

        }

        return $response;
    }

    public function wpmf_svgs_get_dimensions( $svg ) {

        $svg = simplexml_load_file( $svg );
        $attributes = $svg->attributes();
        $width = (string) $attributes->width;
        $height = (string) $attributes->height;

        return (object) array( 'width' => $width, 'height' => $height );

    }

    public function wpmf_admin_inline_style(){
        global $pagenow , $current_screen;
        if (is_admin() && ($pagenow == 'customize.php' || $current_screen->base == 'toplevel_page_wptm')) {
            $this->wpmf_admin_head();
            $this->add_editor_footer();
        }
    }

    /* update count children of a folder */
    public function wpmf_update_count_term() {
        global $wpdb;
        $terms = get_categories(array('taxonomy' => WPMF_TAXO,'hide_empty' => false));
        if (!empty($terms)) {
            foreach ($terms as $term) {
                // get count file in folder
                $object_in_term = get_objects_in_term($term->term_id, WPMF_TAXO);
                $count_object = count($object_in_term);
                // get count subfolder in folder
                $term_child = get_term_children($term->term_id, WPMF_TAXO);
                $count_term_child = count($term_child);
                $count = $count_object + $count_term_child;
                $wpdb->update($wpdb->term_taxonomy, compact('count'), array('term_taxonomy_id' => $term->term_id));
            }
        }
    }

    public function wpmf_svgs_upload_mimes($mimes = array()) {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        return $mimes;
    }

    function wpmf_svgs_disable_real_mime_check( $data, $category, $categoryname, $mimes ) {
        $wp_filetype = wp_check_filetype( $categoryname, $mimes );

        $ext = $wp_filetype['ext'];
        $type = $wp_filetype['type'];
        $proper_filename = $data['proper_filename'];

        return compact( 'ext', 'type', 'proper_filename' );
    }

    public function wpmf_svgs_thumbs_filter() {

        $final = '';
        $ob_levels = count(ob_get_level());

        for ($i = 0; $i < $ob_levels; $i++) {

            $final .= ob_get_clean();
        }
        echo apply_filters('wpmf_afinal_output', $final);
    }

    public function wpmf_svgs_final_output($content) {
        global $pagenow;
        if($pagenow != 'customize.php'){
            $content = str_replace(
                '<# } else if ( \'image\' === data.type && data.sizes && data.sizes.full ) { #>', '<# } else if ( \'svg+xml\' === data.subtype ) { #>
                        <img class="details-image" src="{{ data.url }}" draggable="false" />
                        <# } else if ( \'image\' === data.type && data.sizes && data.sizes.full ) { #>', $content
            );

            $content = str_replace(
                    '<# } else if ( \'image\' === data.type && data.sizes ) { #>', '<# } else if ( \'svg+xml\' === data.subtype ) { #>
                            <div class="centered">
                                    <img src="{{ data.url }}" class="thumbnail" draggable="false" />
                            </div>
                    <# } else if ( \'image\' === data.type && data.sizes ) { #>', $content
            );
        }

        return $content;
    }

    public function wpmf_gallery_get_image() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        global $wpdb;
        if (!empty($_POST['ids']) && isset($_POST['wpmf_orderby']) && isset($_POST['wpmf_order'])) {
            $ids = $_POST['ids'];
            $wpmf_orderby = $_POST['wpmf_orderby'];
            $wpmf_order = $_POST['wpmf_order'];
            if ($wpmf_orderby == 'title' || $wpmf_orderby == 'date') {
                $wpmf_orderby = 'post_' . $wpmf_orderby;
                // query attachment by orderby and order
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'attachment',
                    'post__in' => $ids,
                    'post_status' => 'any',
                    'orderby' => $wpmf_orderby,
                    'order' => $wpmf_order
                );
                $query = new WP_Query($args);
                $posts = $query->get_posts();
                wp_send_json($posts);
            }
        }
        wp_send_json(false);
    }

    /*  Start new or resume existing session */
    public function wpmf_session_start() {
        ob_start();
        if (!session_id()) {
            @session_start();
        }
        $domain = 'wpmf';
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_plugin_textdomain($domain, false, dirname(plugin_basename(WPMF_FILE)) . '/languages/');
    }
    
    /* load styles */
    public function load_style(){
        wp_enqueue_style('wpmf-jaofiletree', plugins_url('/assets/css/jaofiletree.css', dirname(__FILE__)), array(), WPMF_VERSION);
        wp_enqueue_style('wpmf-material-design-iconic-font.min', plugins_url('/assets/css/material-design-iconic-font.min.css', dirname(__FILE__)), array(), WPMF_VERSION);
        wp_enqueue_style('wpmf-style', plugins_url('/assets/css/style.css', dirname(__FILE__)), array(), WPMF_VERSION);
        wp_enqueue_style('wpmf-snackbar', plugins_url('/assets/css/snackbar.css', dirname(__FILE__)), array(), WPMF_VERSION);
        wp_enqueue_style('wpmf-material', plugins_url('/assets/css/material.css', dirname(__FILE__)), array(), WPMF_VERSION);
        wp_enqueue_style('wpmf-mdl', plugins_url('/assets/css/modal-dialog/mdl-jquery-modal-dialog.css', dirname(__FILE__)), array(), WPMF_VERSION);
        wp_enqueue_style('wpmf-deep_orange', plugins_url('/assets/css/modal-dialog/material.deep_orange-amber.min.css', dirname(__FILE__)), array(), WPMF_VERSION);
        wp_enqueue_style('wpmf-google-icon', '//fonts.googleapis.com/icon?family=Material+Icons');
        wp_enqueue_style('wpmf-style-qtip', plugins_url('/assets/css/jquery.qtip.css', dirname(__FILE__)), array(), WPMF_VERSION);

    }
    
    /* load scripts */
    public function load_script(){
        wp_enqueue_script('jquery');
        wp_enqueue_script('wpmf-snack', plugins_url('/assets/js/snackbar.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION);
        wp_enqueue_script('wpmf-material', plugins_url('/assets/js/modal-dialog/material.min.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION);
        wp_enqueue_script('wpmf-mdl', plugins_url('/assets/js/modal-dialog/mdl-jquery-modal-dialog.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION);
        wp_enqueue_script(array('jquery-ui-draggable', 'jquery-ui-droppable'));
        wp_register_script('wpmf-script', plugins_url('/assets/js/script.js', dirname(__FILE__)), array('jquery', 'plupload'), WPMF_VERSION);
        wp_enqueue_script('wpmf-script');

        wp_register_script('wpmf-filter-display-media', plugins_url('/assets/js/wpmf-display-media.js', dirname(__FILE__)), array('jquery', 'plupload'), WPMF_VERSION);
        wp_register_script('duplicate-image', plugins_url('assets/js/duplicate-image.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION, true);
        wp_register_script('wpmf-fillter-size', plugins_url('/assets/js/fillter-size.js', dirname(__FILE__)), array('jquery', 'plupload'), WPMF_VERSION);
        wp_enqueue_script('wpmf-script-qtip', plugins_url('/assets/js/jquery.qtip.min.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION, true);
        wp_enqueue_script('wpmf-assign-tree', plugins_url('/assets/js/assign_image_folder_tree.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION);

        $params = $this->wpmf_localize_script();
        wp_localize_script('wpmf-script', 'wpmflang', $params);
        wp_enqueue_script('wplink');
        wp_enqueue_style( 'editor-buttons' );
    }

    public function wpmf_admin_page_table_script() {
        global $pagenow, $current_screen;
        if (!current_user_can('upload_files'))
            return;

        if ($pagenow == 'upload.php') {
            $this->load_script();
            $this->load_style();
        }
    }

    /* includes styles and some scripts */
    public function wpmf_media_page_table_script() {
        if (!current_user_can('upload_files'))
            return;
        $this->wpmf_admin_head();
        $this->load_script();
        $this->load_style();
        add_action('shutdown', array($this, 'wpmf_svgs_thumbs_filter'), 0);
    }
    
    /* get localize script  */
    public function wpmf_localize_script() {
        global $pagenow;
        $option_override = get_option('wpmf_option_override');
        $option_duplicate = get_option('wpmf_option_duplicate');

        if($pagenow == 'upload.php'){
            $categorytype = $this->wpmf_get_filetype();
            $zip = array('zip', 'rar', 'ace', 'arj', 'bz2', 'cab', 'gzip', 'iso', 'jar', 'lzh', 'tar', 'uue', 'xz', 'z', '7-zip');
            $pdf = array('pdf');
            // get count file archive
            $count_zip = $this->wpmf_count_ext($zip, 'application');
            // get count file pdf
            $count_pdf = $this->wpmf_count_ext($pdf, 'application/pdf');
        }else{
            $categorytype = '';
            $count_zip = 0;
            $count_pdf = 0;
        }

        $wpmfdisplay_media = array('yes' => 'Yes');
        if (isset($_SESSION['wpmf_display_media'])) {
            $display = $_SESSION['wpmf_display_media'];
        } else {
            $display = '';
        }
        // get some options
        $terms = $this->get_attachment_terms();
        $parents_array = $this->get_parrents_array($terms['attachment_terms']);
        $usegellery = get_option('wpmf_usegellery');
        $get_plugin_enhanced_media = strpos(json_encode(get_option('active_plugins')), 'enhanced-media-library.php');
        $option_media_remove = get_option('wpmf_option_media_remove');
        $option_seach = get_option('wpmf_option_searchall');

        $curent_view = $this->wpmf_get_media_view();
        $cook_order_media = $this->wpmf_get_cookie_media($pagenow, $curent_view);
        $cook_order_f = $this->wpmf_get_cookie_folder();
        $s_dimensions = get_option('wpmf_selected_dimension');
        $size = json_decode($s_dimensions);
        $s_weights = get_option('wpmf_weight_selected');
        $weight = json_decode($s_weights);
        $order_folder = array('name-ASC' => __('Name (Ascending)', 'wpmf'), 'name-DESC' => __('Name (Descending)', 'wpmf'), 'id-ASC' => __('ID (Ascending)', 'wpmf'), 'id-DESC' => __('ID (Descending)', 'wpmf'));
        $order_media = array('date|asc' => __('Date (Ascending)', 'wpmf'),
            'date|desc' => __('Date (Descending)', 'wpmf'),
            'title|asc' => __('Title (Ascending)', 'wpmf'),
            'title|desc' => __('Title (Descending)', 'wpmf'),
            'size|asc' => __('Size (Ascending)', 'wpmf'),
            'size|desc' => __('Size (Descending)', 'wpmf'),
            'filetype|asc' => __('File type (Ascending)', 'wpmf'),
            'filetype|desc' => __('File type (Descending)', 'wpmf'),
        );
        if (isset($_SESSION['wpmf_folder_order']) && isset($_SESSION['wpmf_folder_orderby'])) {
            $order_selected = $_SESSION['wpmf_folder_orderby'] . '-' . $_SESSION['wpmf_folder_order'];
        } else {
            $order_selected = 'all';
        }
        
        if(isset($_GET['attachment_size'])){
            $attachment_size = $_GET['attachment_size'];
        }else{
            $attachment_size = '';
        }
        
        if(isset($_GET['attachment_weight'])){
            $attachment_weight = $_GET['attachment_weight'];
        }else{
            $attachment_weight = '';
        }
        
        $option_countfiles = get_option('wpmf_option_countfiles');
        $option_hoverimg = get_option('wpmf_option_hoverimg');
        $root_media_root = get_term_by('id', $this->wpmf_folder_root_id, WPMF_TAXO);
        return array(
            'create_folder' => __('Create Folder', 'wpmf'),
            'media_folder' => __('Media Library', 'wpmf'),
            'promt' => __('New folder name:', 'wpmf'),
            'new_folder' => __('New folder', 'wpmf'),
            'new_folder_tree' => __('NEW FOLDER', 'wpmf'),
            'alert_add' => __('A folder already exists here with the same name. Please try with another name, thanks :)', 'wpmf'),
            'alert_delete' => __('Are you sure to want to delete this folder', 'wpmf'),
            'alert_delete_all' => __('This folder contains other sub folders and files. Are you sure want to delete it ?', 'wpmf'),
            'alert_delete1' => __('this folder contains sub-folder, delete sub-folders before', 'wpmf'),
            'display_media' => __('Display only my own media', 'wpmf'),
            'create_gallery_folder' => __('Create a gallery from folder', 'wpmf'),
            'home' => __('Home', 'wpmf'),
            'youarehere' => __('You are here', 'wpmf'),
            'back' => __('Back', 'wpmf'),
            'dragdrop' => __('Drag and Drop me hover a folder', 'wpmf'),
            'smallview' => __('Small View', 'wpmf'),
            'pdf' => __('PDF', 'wpmf'),
            'zip' => __('Zip & archives', 'wpmf'),
            'other' => __('Other', 'wpmf'),
            'error_replace' => __('To replace a media and keep the link to this media working, it must be in the same format, ie. jpg > jpg, zip > zip� Thanks!', 'wpmf'),
            'uploaded_to_this' => __('Uploaded to this ', 'wpmf'),
            'mimetype' => __('All media items', 'wpmf'),
            'override' => $option_override,
            'duplicate' => $option_duplicate,
            'wpmf_file' => $categorytype,
            'wpmfcount_zip' => $count_zip,
            'wpmfcount_pdf' => $count_pdf,
            'wpmf_display_media' => json_encode($wpmfdisplay_media),
            'no_media_label' => __('No', 'wpmf'),
            'yes_media_label' => __('Yes', 'wpmf'),
            'wpmf_selected_dmedia' => $display,
            'wpmf_categories' => $terms['attachment_terms'],
            'wpmf_categories_order' => $terms['attachment_terms_order'],
            'parents_array' => $parents_array,
            'wpmf_images_path' => plugins_url('assets/images', dirname(__FILE__)),
            'taxo' => $terms['taxo'],
            'wpmf_role' => $terms['role'],
            'wpmf_active_media' => $terms['wpmf_active_media'],
            'term_root_username' => $terms['term_root_username'],
            'term_root_id' => $terms['term_root_id'],
            'wpmf_pagenow' => $terms['wpmf_pagenow'],
            'usegellery' => $usegellery,
            'enhanced_media_plugin' => $get_plugin_enhanced_media,
            'wpmf_post_type' => $terms['wpmf_post_type'],
            'wpmf_curent_userid' => get_current_user_id(),
            'wpmf_post_mime_type' => $terms['post_mime_types'],
            'wpmf_type' => $terms['post_type'],
            'wpmfview' => @$terms['wpmfview'],
            'site_url' => get_site_url() . '/wp-admin/upload.php?mode=grid',
            'useorder' => $terms['useorder'],
            'wpmf_remove_media' => @$option_media_remove,
            'wpmf_search' => $option_seach,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'wpmf_search' => $option_seach,
            'wpmf_size' => $size,
            'size' => $attachment_size,
            'wpmf_weight' => $weight,
            'weight' => $attachment_weight,
            'order_folder' => $order_folder,
            'order_media' => $order_media,
            'order_f' => $order_selected,
            'wpmf_order_media' => $cook_order_media,
            'wpmf_order_f' => $cook_order_f,
            'option_countfiles' => $option_countfiles,
            'replace' => __('Send new file and replace','wpmf'),
            'duplicate_text' => __('Duplicate','wpmf'),
            'wpmf_undo_remove' => __( 'Folder removed.', 'wpmf' ),
            'wpmf_undo_movefile' => __( 'Moved some files.', 'wpmf' ),
            'wpmf_undo_movefolder' => __( 'Moved a folder.', 'wpmf' ),
            'wpmf_undo_editfolder' => __( 'Folder name updated', 'wpmf' ),
            'wpmf_undo_restored' => __( 'File/Folder restored with success.', 'wpmf' ),
            'wpmf_file_replace' => __( 'File replaced!', 'wpmf' ),
            'wpmf_fileupload' => __( 'File upload on the way...', 'wpmf' ),
            'wpmf_undofilter' => __( 'Filter applied', 'wpmf' ),
            'wpmf_remove_filter' => __( 'Media filters removed', 'wpmf' ),
            'cancel' => __('Cancel','wpmf'),
            'create' => __('Create','wpmf'),
            'save' => __('Save','wpmf'),
            'delete' => __('Delete','wpmf'),
            'information' => __('Information','wpmf'),
            'wpmf_option_hoverimg' => $option_hoverimg,
            'wpmf_reset' => __('Reset','wpmf'),
            'label_filter_order' => __('Filter or order media','wpmf'),
            'label_remove_filter' => __('Remove all filters','wpmf'),
            'wpmf_remove_file' => __('Media removed','wpmf'),
            'wpmf_addfolder' => __('Folder added','wpmf'),
            'wpmf_media_uploaded' => __('New media uploaded','wpmf'),
            'assign_tree_label' => __('Media folders selection','wpmf'),
            'label_assign_tree' => __('Select the folders where the media belong','wpmf'),
            'label_apply' => __('Apply','wpmf'),
            'folder_selection' => __('New folder selection applied to media','wpmf'),
            'all_size_label' => __('Minimal size', 'wpmf'),
            'all_weight_label' => __('All weight', 'wpmf'),
            'order_folder_label' => __('Sort folder', 'wpmf'),
            'order_img_label' => __('Sort media', 'wpmf'),
            'root_media_root' => $root_media_root->term_id,
            'remote_video' => __('Remote video', 'wpmf'),
            'upload' => __('UPLOAD', 'wpmf'),
            'filesize_label' => __('File size:', 'wpmf'),
            'dimensions_label' => __('Dimensions:', 'wpmf'),
            'parent' => $terms['parent']
        );
    }

    public function get_parrents_array($attachment_terms) {
        $wcat = isset($_GET['wcat']) ? $_GET['wcat'] : '0';
        $parents = array();
        $pCat = (int) $wcat;
        while ($pCat != 0) {
            $parents[] = $pCat;
            $pCat = (int) $attachment_terms[$pCat]['parent_id'];
        }

        $parents_array = array_reverse($parents);
        return $parents_array;
    }

    public function get_attachment_terms() {
        global $pagenow, $current_user, $post;
        // get categories
        $attachment_terms = array();
        $terms = get_categories(array('hide_empty' => false, 'taxonomy' => WPMF_TAXO, 'pll_get_terms_not_translated' => 1));
        $terms = $this->generatePageTree($terms);
        $terms = $this->parent_sort($terms);
        $term_rootId = 0;

        $attachment_terms_order = array();
        $wpmf_create_folder = get_option('wpmf_create_folder');
        $wpmf_active_media = get_option('wpmf_active_media');
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        $term_root_username = '';
        $parent = 0;
        if ($role == 'administrator' || $wpmf_active_media == 0) { // role == administrator or disable option 'Display only media by User/User'
            $attachment_terms[] = array('id' => 0, 'label' => __('Select a folder', 'wpmf'), 'slug' => '', 'parent_id' => 0);
            $attachment_terms_order[] = '0';
        } else { // role != administrator or enable option 'Display only media by User/User'
            if ($wpmf_create_folder == 'user') {
                $term_root_username = $current_user->user_login;
                $wpmf_checkbox_tree = get_option('wpmf_checkbox_tree');
                if(!empty($wpmf_checkbox_tree)){
                    $parent = $wpmf_checkbox_tree;
                }
            } elseif ($wpmf_create_folder == 'role') {
                $term_root_username = $role;
            }
            $wpmfterm = $this->wpmf_term_root();
            if (!empty($wpmfterm)) {
                $term_rootId = $wpmfterm['term_rootId'];
                $term__label = $wpmfterm['term_label'];
                $term__parent = $wpmfterm['term_parent'];
                $term_slug = $wpmfterm['term_slug'];
                $attachment_terms[$term_rootId] = array('id' => $term_rootId, 'label' => $term__label, 'slug' => $term_slug, 'parent_id' => $term__parent);
                $attachment_terms_order[] = $term_rootId;
            } else {
                $attachment_terms[] = array('id' => 0, 'label' => __('Select a folder', 'wpmf'), 'slug' => '', 'parent_id' => 0);
                $attachment_terms_order[] = 0;
            }
        }

        if (isset($wpmf_active_media) && $wpmf_active_media == 1 && $role != 'administrator') { // role != administrator or enable option 'Display only media by User/User'
            $wpmfterm = $this->wpmf_term_root();
            if (!empty($wpmfterm)) {
                $term_rootId = $wpmfterm['term_rootId'];
            } else {
                $term_rootId = 0;
            }

            $current_role = $this->wpmf_get_roles(get_current_user_id());
            $ts = get_term_children($term_rootId, WPMF_TAXO);
            foreach ($terms as $term) {
                if ($wpmf_create_folder == 'user') {
                    if ($term->term_group == get_current_user_id()) {
                        if (in_array($term->term_id, $ts)) {
                            $attachment_terms[$term->term_id] = array('id' => $term->term_id, 'label' => $term->name, 'slug' => $term->slug, 'parent_id' => $term->category_parent, 'depth' => $term->depth, 'term_group' => $term->term_group);
                            $attachment_terms_order[] = $term->term_id;
                        }
                    }
                } else {
                    $role = $this->wpmf_get_roles($term->term_group);
                    if ($current_role == $role && $term_slug != $term->slug) {
                        $attachment_terms[$term->term_id] = array('id' => $term->term_id, 'label' => $term->name, 'slug' => $term->slug, 'parent_id' => $term->category_parent, 'depth' => $term->depth, 'term_group' => $term->term_group);
                        $attachment_terms_order[] = $term->term_id;
                    }
                }
            }
        } else { // role == administrator or disable option 'Display only media by User/User'
            $current_role = 'administrator';
            foreach ($terms as $term) {
                $attachment_terms[$term->term_id] = array('id' => $term->term_id, 'label' => $term->name, 'slug' => $term->slug, 'parent_id' => $term->category_parent, 'depth' => $term->depth, 'term_group' => $term->term_group);
                $attachment_terms_order[] = $term->term_id;
            }
        }
        
        $post_mime_types = get_post_mime_types();
        $useorder = get_option('wpmf_useorder');
        if (!$useorder || $useorder == 0 || $useorder == '') {
            unset($_SESSION['wpmfview']);
        }
        
        // get post type
        global $post;
        if (!empty($post) && !empty($post->post_type)) {
            $post_type = $post->post_type;
        } else {
            $post_type = '';
        }

        // get current media view
        if (empty($_SESSION['wpmfview'])) {
            $wpmfview = 'wpmf_default';
        } else {
            $wpmfview = $_SESSION['wpmfview'];
        }

        if (in_array('js_composer/js_composer.php', get_option('active_plugins'))) {
            $wpmf_post_type = 1;
        } else {
            $wpmf_post_type = 0;
        }

        return array('role' => $current_role,
            'wpmf_active_media' => $wpmf_active_media,
            'taxo' => WPMF_TAXO,
            'term_root_username' => $term_root_username,
            'term_root_id' => $term_rootId,
            'attachment_terms' => $attachment_terms,
            'attachment_terms_order' => $attachment_terms_order,
            'wpmf_pagenow' => $pagenow,
            'post_mime_types' => $post_mime_types,
            'useorder' => $useorder,
            'post_type' => $post_type,
            'wpmfview' => $wpmfview,
            'wpmf_post_type' => $wpmf_post_type,
            'parent' => $parent
        );
    }

    public function wpmf_whow_notice_import_size() {
        global $wpdb;
        $total = $wpdb->get_var("SELECT COUNT(posts.ID) as total FROM " . $wpdb->prefix . "posts as posts
               WHERE   posts.post_type = 'attachment'");

        if($total > 10000){
            echo '<script type="text/javascript">' . PHP_EOL
                . 'function wpmfimport_meta_size(button){' . PHP_EOL
                . 'var $this = jQuery(button);' . PHP_EOL
                . 'var wpmf_current_page = $this.data("page");' . PHP_EOL
                . '$this.find(".spinner").show().css({"visibility":"visible"});' . PHP_EOL
                . 'jQuery.ajax({' . PHP_EOL
                . 'type: \'POST\',' . PHP_EOL
                . 'url: ajaxurl,' . PHP_EOL
                . 'data: {' . PHP_EOL
                . 'action: "wpmf_import_size_filetype",' . PHP_EOL
                . 'wpmf_current_page: wpmf_current_page,' . PHP_EOL
                . '},' . PHP_EOL
                . 'success: function (res) {' . PHP_EOL
                . 'if (res.status == false) {' . PHP_EOL
                . '$this.data("page",parseInt(res.page) + 1);' . PHP_EOL
                . '$this.click();' . PHP_EOL
                . '}else{' . PHP_EOL
                . '$this.closest("div#wpmf_error").hide();' . PHP_EOL
                . '}' . PHP_EOL
                . '}' . PHP_EOL
                . '});' . PHP_EOL
                . '}' . PHP_EOL
                . '</script>';
            echo '<div class="error" id="wpmf_error">'
                . '<p>'
                . __('Your website has a large image library (>10000 images). WP Media Folder needs to index all of them to run smoothly. It may take few minutes... keep cool :)', 'wpmf')
                . '<a href="#" class="button button-primary" style="margin: 0 5px;" data-page="0" onclick="wpmfimport_meta_size(this);" id="wmpfImportsize">' . __('Import size and filetype now', 'wpmf') . ' <span class="spinner" style="display:none"></span></a>'
                . '</p>'
                . '</div>';
        }
    }

    public function wpmf_whow_notice() {
        if (current_user_can('manage_options')) {
            echo '<script type="text/javascript">' . PHP_EOL
            . 'function importWpmfTaxonomy(doit,button){' . PHP_EOL
            . 'jQuery(button).find(".spinner").show().css({"visibility":"visible"});' . PHP_EOL
            . 'jQuery.post(ajaxurl, {action: "wpmf_import",doit:doit}, function(response) {' . PHP_EOL
            . 'jQuery(button).closest("div#wpmf_error").hide();' . PHP_EOL
            . 'if(doit===true){' . PHP_EOL
            . 'jQuery("#wpmf_error").after("<div class=\'updated\'> <p><strong>' . __('Categories imported into WP Media Folder. Enjoy!!!', 'wpmf') . '</strong></p></div>");' . PHP_EOL
            . '}' . PHP_EOL
            . '});' . PHP_EOL
            . '}' . PHP_EOL
            . '</script>';
            echo '<div class="error" id="wpmf_error">'
            . '<p>'
            . __('Thanks for using WP Media Folder! Save time by transforming post categories into media folders automatically. More info', 'wpmf')
            . '<a href="#" class="button button-primary" style="margin: 0 5px;" onclick="importWpmfTaxonomy(true,this);" id="wmpfImportBtn">' . __('Import categories now', 'wpmf') . ' <span class="spinner" style="display:none"></span></a> or <a href="#" onclick="importWpmfTaxonomy(false,this);" style="margin: 0 5px;" class="button">' . __('No thanks ', 'wpmf') . ' <span class="spinner" style="display:none"></span></a>'
            . '</p>'
            . '</div>';
        }
    }
    
    /* this function do import wordpress category default */
    function wpmf_import_categories() {
        $option_import_taxo = get_option('_wpmf_import_notice_flag');
        if (isset($option_import_taxo) && $option_import_taxo == 'yes') {
            die();
        }
        if ($_POST['doit'] === 'true') {
            // get all term taxonomy 'category'
            $terms = get_terms('category', array(
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => false,
                'child_of' => 0
            ));

            $termsRel = array('0' => 0);
            // insert wpmf-category term
            foreach ($terms as $term) {
                $inserted = wp_insert_term($term->name, WPMF_TAXO, array('slug' => wp_unique_term_slug($term->slug, $term)));
                if (is_wp_error($inserted)) {
                    wp_send_json($inserted->get_error_message());
                }
                $termsRel[$term->term_id] = $inserted['term_id'];
            }
            // update parent wpmf-category term
            foreach ($terms as $term) {
                wp_update_term($termsRel[$term->term_id], WPMF_TAXO, array('parent' => $termsRel[$term->parent]));
            }

            //update attachments
            $attachments = get_posts(array('posts_per_page' => -1, 'post_type' => 'attachment'));
            foreach ($attachments as $attachment) {
                $terms = wp_get_post_terms($attachment->ID, 'category');
                $termsArray = array();
                foreach ($terms as $term) {
                    $termsArray[] = $termsRel[$term->term_id];
                }
                if ($termsArray != null) {
                    wp_set_post_terms($attachment->ID, $termsArray, WPMF_TAXO);
                }
            }
        }
        if ($_POST['doit'] === 'true') {
            update_option('_wpmf_import_notice_flag', 'yes');
        } else {
            update_option('_wpmf_import_notice_flag', 'no');
        }
        die();
    }

    /* Display or retrieve the HTML dropdown list of categories. */
    public function wpmf_add_image_category_filter() {
        global $pagenow;
        if ($pagenow == 'upload.php') {
            $wpmf_active_media = get_option('wpmf_active_media');
            if(!function_exists('get_userdata')){
                require_once ( ABSPATH . 'wp-includes/pluggable.php' );
            }
            $user_data = get_userdata(get_current_user_id());
            $user_roles = $user_data->roles;
            $role = array_shift($user_roles);
            $root_media_root = get_term_by('id', $this->wpmf_folder_root_id, WPMF_TAXO);
            if ($role != 'administrator' && $wpmf_active_media == 1) {
                $wpmfterm = $this->wpmf_term_root();
                $term_rootId = $wpmfterm['term_rootId'];
                $term_label = $wpmfterm['term_label'];
                $dropdown_options = array('exclude' => $root_media_root->term_id,'show_option_none' => $term_label, 'option_none_value' => $term_rootId, 'hide_empty' => false, 'hierarchical' => true, 'orderby' => 'name', 'taxonomy' => WPMF_TAXO, 'class' => 'wpmf-categories', 'name' => 'wcat', 'selected' => (int) (isset($_GET['wcat']) ? $_GET['wcat'] : 0));
            } else {
                $dropdown_options = array('exclude' => $root_media_root->term_id,'show_option_none' => __('Select a folder', 'wpmf'), 'option_none_value' => 0, 'hide_empty' => false, 'hierarchical' => true, 'orderby' => 'name', 'taxonomy' => WPMF_TAXO, 'class' => 'wpmf-categories', 'name' => 'wcat', 'selected' => (int) (isset($_GET['wcat']) ? $_GET['wcat'] : 0));
            }

            wp_dropdown_categories($dropdown_options);
        }
    }

    /* Query post in media list view  */
    public function wpmf_pre_get_posts1($query) {
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] != 'attachment')
            return;
        global $pagenow;
        $option_seach = get_option('wpmf_option_searchall');
        if ($pagenow == 'upload.php') {
            if(isset($_GET['page']) && $_GET['page'] == 'wp-retina-2x'){
                return $query;
            }

            if ((int) $option_seach == 0 || (empty($_GET['s']) && (int) $option_seach == 1)) {
                if (isset($_GET['wcat']) && (int) $_GET['wcat'] !== 0) {
                    // list view , query post with term_id != 0
                    $query->tax_query->queries[] = array(
                        'taxonomy' => WPMF_TAXO,
                        'field' => 'term_id',
                        'terms' => (int) $_GET['wcat'],
                        'include_children' => false
                    );
                    $query->query_vars['tax_query'] = $query->tax_query->queries;
                } else {
                    // grid view , query post with term_id != 0
                    $wpmf_active_media = get_option('wpmf_active_media');
                    if(!function_exists('get_userdata')){
                        require_once ( ABSPATH . 'wp-includes/pluggable.php' );
                    }
                    $user_data = get_userdata(get_current_user_id());
                    $user_roles = $user_data->roles;
                    $role = array_shift($user_roles);
                    if ($wpmf_active_media == 1 && $role != 'administrator') {
                        $wpmfterm = $this->wpmf_term_root();
                        $term_rootId = $wpmfterm['term_rootId'];
                        $query->tax_query->queries[] = array(
                            'taxonomy' => WPMF_TAXO,
                            'field' => 'term_id',
                            'terms' => (int) $term_rootId,
                            'include_children' => false
                        );
                        $query->query_vars['tax_query'] = $query->tax_query->queries;
                    } else {
                        $terms = get_categories(array('hide_empty' => false, 'taxonomy' => WPMF_TAXO));
                        $cats = array();
                        foreach ($terms as $term) {
                            if (!empty($term->term_id)) {
                                $cats[] = $term->term_id;
                            }
                        }
                        $root_media_root = get_term_by('id', $this->wpmf_folder_root_id, WPMF_TAXO);
                        if(in_array($root_media_root->term_id,$cats)){
                            $key = array_search($root_media_root->term_id,$cats);
                        }
                        unset($cats[$key]);
                        if(count($terms) == 1 && $terms[0]->term_id == $this->wpmf_folder_root_id){

                        }else{
                            if(isset($query->tax_query)){
                                $args = array(
                                    'relation' => 'OR',
                                    array(
                                        'taxonomy' => WPMF_TAXO,
                                        'field' => 'term_id',
                                        'terms' => $cats,
                                        'operator' => 'NOT IN'
                                    ),
                                    array(
                                        'taxonomy' => WPMF_TAXO,
                                        'field' => 'term_id',
                                        'terms' => $root_media_root->term_id,
                                        'operator' => 'IN'
                                    )
                                );
                                $query->set('tax_query', $args);
                            }
                        }
                    }
                }
            }
        }
    }

    function wpmf_admin_head() {
        if (!current_user_can('upload_files'))
            return;
        $option_seach = get_option('wpmf_option_searchall');
        $media_view = $this->wpmf_get_media_view();
        if (isset($_GET['s']) && $_GET['s'] != '' && $option_seach == 1 && $media_view == 'list') {
            echo '<style>.wpmf-attachments-browser{display:none !important;}</style>';
        }
    }
    
    /* Renders 'wpmf-editor' editor in footer */ 
    public function add_editor_footer() {
        if ( ! class_exists( '_WP_Editors', false ) ){
            require_once ABSPATH . "wp-includes/class-wp-editor.php";
            _WP_Editors::wp_link_dialog();
        }
    }
    
    /* Query post in media gird view and ifame  */
    public function wpmf_pre_get_posts($query) {
        $option_seach = get_option('wpmf_option_searchall');
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] != 'attachment')
            return;

        if (isset($_REQUEST['query']['orderby']) && $_REQUEST['query']['orderby'] == 'menu_order ID') {
            
        } else {
            $taxonomies = apply_filters('attachment-category', get_object_taxonomies('attachment', 'objects'));
            if (!$taxonomies)
                return;
            foreach ($taxonomies as $taxonomyname => $taxonomy) :
                if ($taxonomyname == WPMF_TAXO) {
                    if ($option_seach == 0 || (empty($_REQUEST['query']['s']) && $option_seach == 1)) {
                        if (isset($_REQUEST['query']['wpmf_taxonomy']) && $_REQUEST['query']['term_slug']) {
                            $query->set('tax_query', array(
                                array(
                                    'taxonomy' => $taxonomyname,
                                    'field' => 'slug',
                                    'terms' => $_REQUEST['query']['term_slug'],
                                    'include_children' => false
                                )
                                    )
                            );
                        } elseif (isset($_REQUEST[$taxonomyname]) && is_numeric($_REQUEST[$taxonomyname]) && intval($_REQUEST[$taxonomyname]) != 0) {
                            $term = get_term_by('id', $_REQUEST[$taxonomyname], $taxonomyname);
                            if (is_object($term))
                                set_query_var($taxonomyname, $term->slug);
                        }elseif (isset($_REQUEST['query']['wpmf_taxonomy']) && $_REQUEST['query']['term_slug'] == '') {
                            $terms = get_categories(array('taxonomy' => $taxonomyname,'hide_empty' => false, 'hierarchical' => false));
                            $unsetTags = array();
                            foreach ($terms as $term) {
                                $unsetTags[] = $term->slug;
                            }
                            $root_media_root = get_term_by('name', __('WP Media Folder Root', 'wpmf'), WPMF_TAXO);
                            if(in_array($root_media_root->slug,$unsetTags)){
                                $key = array_search($root_media_root->slug,$unsetTags);
                            }
                            unset($unsetTags[$key]);
                            if(count($terms) == 1 && $terms[0]->term_id == $this->wpmf_folder_root_id){

                            }else{
                                $query->set('tax_query', array(
                                        'relation' => 'OR',
                                        array(
                                            'taxonomy' => $taxonomyname,
                                            'field' => 'slug',
                                            'terms' => $unsetTags,
                                            'operator' => 'NOT IN',
                                            'include_children' => false
                                        ),
                                        array(
                                            'taxonomy' => $taxonomyname,
                                            'field' => 'slug',
                                            'terms' => $root_media_root->slug,
                                            'include_children' => false
                                        )
                                    )
                                );
                            }

                        }
                    }
                }

            endforeach;
        }

        global $current_user, $wpdb;
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        $wpmf_create_folder = get_option('wpmf_create_folder');
        $wpmf_active_media = get_option('wpmf_active_media');
        $id_author = get_current_user_id();

        if ($role == 'administrator') {
            // role administrator when checked checkbox 'Display only my own media'
            if (isset($_SESSION['wpmf_display_media']) && $_SESSION['wpmf_display_media'] == 'yes') {
                $query->query_vars['author'] = $id_author;
            }
        } elseif (isset($wpmf_active_media) && $wpmf_active_media == 1) {
            // role != administrator when enable option 'Display only media by User/User'
            if ($wpmf_create_folder == 'user') {
                $query->query_vars['author'] = $id_author;
            } else {
                $current_role = $this->wpmf_get_roles(get_current_user_id());
                $user_query = new WP_User_Query(array('role' => $current_role));
                $user_lists = $user_query->get_results();
                $user_array = array();

                foreach ($user_lists as $user) {
                    $user_array[] = $user->data->ID;
                }

                $query->query_vars['author__in'] = $user_array;
            }
        }

        return $query;
    }

    /* Display folder and file when change a folder */
    public function wpmf_change_folder() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        global $current_user;
        $wpmfjson = array();
        $id = (int) $_POST['id'] | 0;
        $_SESSION['wpmf-current-folder'] = $id;
        update_option('wpmf_current_folder_id'.$current_user->ID,$id);
        if (isset($_COOKIE['wpmf_folder_order']) && empty($_SESSION['wpmf_folder_orderby']) && empty($_SESSION['wpmf_folder_order'])) {
            $sortbys = explode('-', $_COOKIE['wpmf_folder_order']);
            $orderby = $sortbys[0];
            $order = $sortbys[1];
        } else {
            if (isset($_SESSION['wpmf_folder_orderby'])) {
                $orderby = $_SESSION['wpmf_folder_orderby'];
            } else {
                $orderby = 'name';
            }

            if (isset($_SESSION['wpmf_folder_order'])) {
                $order = $_SESSION['wpmf_folder_order'];
            } else {
                $order = 'ASC';
            }
        }

        $terms_child = get_categories(array('taxonomy' => WPMF_TAXO,'orderby' => $orderby, 'order' => $order, 'parent' => $id, 'hide_empty' => false));
        $wpmfjson['terms'] = array();
        $wpmfjson['countfiles'] = array();

        $wpmf_create_folder = get_option('wpmf_create_folder');
        $wpmf_active_media = get_option('wpmf_active_media');
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        if (($role != 'administrator' && isset($wpmf_active_media) && $wpmf_active_media == 1) || ($role == 'administrator' && isset($_SESSION['wpmf_display_media']) && $_SESSION['wpmf_display_media'] == 'yes')) {
            $id1 = array();
            $current_role = $this->wpmf_get_roles(get_current_user_id());
            foreach ($terms_child as $term) {

                if ($wpmf_create_folder == 'user') {
                    if ($term->term_group == get_current_user_id()) {
                        $wpmfjson['terms'][] = $term;
                        $id1[] = $term->term_id;
                    }
                } else {
                    $role = $this->wpmf_get_roles($term->term_group);
                    if ($current_role == $role) {
                        $wpmfjson['terms'][] = $term;
                        $id1[] = $term->term_id;
                    }
                }
                
                $count = $this->wpmf_get_count_files($term->term_id);
                $wpmfjson['countfiles'][$term->term_id] = $count;
            }
            $wpmfjson['id1'] = $id1;
        } else {
            $wpmfjson['terms'] = $terms_child;
            foreach ($terms_child as $term) {
                $count = $this->wpmf_get_count_files($term->term_id);
                $wpmfjson['countfiles'][$term->term_id] = $count;
            }
        }
        $option_bgfolder = get_option('wpmf_field_bgfolder');
        $wpmfjson['option_bgfolder'] = $option_bgfolder;
        wp_send_json($wpmfjson);
    }
    
    public function wpmf_get_count_files($term_id){
        $count = get_objects_in_term($term_id,WPMF_TAXO);
        return count($count);
    }

    /**
     * get current  folder
     */
    public function wpmf_get_current_folder_id()
    {
        global $current_user;
        if(empty($_SESSION['wpmf-current-folder'])){
            $current_folder_id = get_option('wpmf_current_folder_id'.$current_user->ID);
        }else{
            $current_folder_id = $_SESSION['wpmf-current-folder'];
        }
        return $current_folder_id;
    }

    /* set file to current folder after upload files */
    public function wpmf_after_upload($attachment_id) {
        $current_folder_id = $this->wpmf_get_current_folder_id();
        $parent = isset($current_folder_id) ? (int) $current_folder_id : 0;
        $post_upload = get_post($attachment_id);
        // only set object to term when upload files from media library screen
        if (!empty($post_upload) && strpos($post_upload->post_content, 'wpmf-nextgen-image') == false && strpos($post_upload->post_content, '[wpmf-ftp-import]') == false) {
            if ($parent) {
                wp_set_object_terms($attachment_id, $parent, WPMF_TAXO, true);
            }
        }

        if (!empty($attachment_id)) {
            $this->wpmf_add_sizefiletype($attachment_id);
        }
    }

    /* set file to current folder after upload files with multiple langguage */
    public function wpmf_add_attachment_langguages($attachment_id, $parent) {
        //compability with sitepress-multilingual-cms plugin
        if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
            global $sitepress;
            $trid = $sitepress->get_element_trid($attachment_id, 'post_attachment');
            if ($trid) {
                $translations = $sitepress->get_element_translations($trid, 'post_attachment', true, true, true);
                foreach ($translations as $translation) {
                    if ($translation->element_id != $attachment_id) {
                        wp_set_object_terms($translation->element_id, $parent, WPMF_TAXO, true);
                        $this->wpmf_add_sizefiletype($translation->element_id);
                    }
                }
            }
        }
    }
    
    /* get size and file type of a file */
    function wpmf_get_sizefiletype($pid) {
        $wpmf_size_filetype = array();
        $meta = get_post_meta($pid, '_wp_attached_file');
        $upload_dir = wp_upload_dir();
        // get path file
        $path_attachment = $upload_dir['basedir'] . '/' . $meta[0];
        if (file_exists($path_attachment)) {
            // get size
            $size = filesize($path_attachment);
            // get file type
            $categorytype = wp_check_filetype($path_attachment);
            $ext = $categorytype['ext'];
        } else {
            $size = 0;
            $ext = '';
        }
        $wpmf_size_filetype['size'] = $size;
        $wpmf_size_filetype['ext'] = $ext;

        return $wpmf_size_filetype;
    }

    /* add meta size and file type of a file */
    public function wpmf_add_sizefiletype($attachment_id) {
        $wpmf_size_filetype = $this->wpmf_get_sizefiletype($attachment_id);
        $size = $wpmf_size_filetype['size'];
        $ext = $wpmf_size_filetype['ext'];
        if (!get_post_meta($attachment_id, 'wpmf_size')) {
            add_post_meta($attachment_id, 'wpmf_size', $size);
        }

        if (!get_post_meta($attachment_id, 'wpmf_filetype')) {
            add_post_meta($attachment_id, 'wpmf_filetype', $ext);
        }
    }

    /** Add a new folder via ajax * */
    public function wpmf_add_folder() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['name']) && $_POST['name'] != '') {
            $term = esc_attr(trim($_POST['name']));
        } else {
            $term = __('New folder', 'wpmf');
        }
        $termParent = (int) $_POST['parent'] | 0;
        $id_author = get_current_user_id();
        // insert new term
        $inserted = wp_insert_term($term, WPMF_TAXO, array('parent' => $termParent));
        if (is_wp_error($inserted)) {
            wp_send_json($inserted->get_error_message());
        } else {
            // update term_group for new term
            $updateted = wp_update_term($inserted['term_id'], WPMF_TAXO, array('term_group' => $id_author));
            $termInfos = get_term($updateted['term_id'], WPMF_TAXO);
            wp_send_json($termInfos);
        }
    }

    /** Edit folder via ajax * */
    public function wpmf_edit_folder() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        $term = esc_attr($_POST['name']);
        $termoldInfos = get_term((int) $_POST['id'], WPMF_TAXO);
        if (!$term) {
            return;
        }
        //check duplicate name
        $siblings = get_categories(array('taxonomy' => WPMF_TAXO,'fields' => 'names', 'get' => 'all', 'parent' => (int) $_POST['parent_id']));
        if (in_array($term, $siblings)) {
            return wp_send_json(false);
        }
        $termInfos = wp_update_term((int) $_POST['id'], WPMF_TAXO, array('name' => $term));
        if ($termInfos instanceof WP_Error) {
            wp_send_json($termInfos->get_error_messages());
        } else {
            $termInfos = get_term($termInfos['term_id'], WPMF_TAXO);

            // update last action
            if(isset($_POST['type']) && $_POST['type'] != 'undo'){
                $last_action = array(
                    'type' => 'edit_folder',
                    'id_folder' => (int) $_POST['id'],
                    'name' => $termoldInfos->name,
                    'parent' => (int) $_POST['parent_id']
                );
                update_option('wpmf_last_action' , $last_action);
            }else{
                delete_option('wpmf_last_action');
            }
            wp_send_json($termInfos);
        }
    }

    /** Delete folder via ajax * */
    public function wpmf_delete_folder() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        $option_media_remove = get_option('wpmf_option_media_remove');
        $bgfolder = get_option('wpmf_field_bgfolder');
        $wpmf_list_sync_media = get_option('wpmf_list_sync_media');
        $wpmf_ao_lastRun = get_option('wpmf_ao_lastRun');
        if ($option_media_remove == 1) {
            // delete all subfolder and subfile
            $childs = get_term_children((int) $_POST['id'], WPMF_TAXO);
            $childs[] = (int) $_POST['id'];
            foreach ($childs as $child) {
                $childs_media = get_objects_in_term($child, WPMF_TAXO);
                foreach ($childs_media as $media) {
                    wp_delete_attachment($media);
                }
                // remove element $child in option 'wpmf_list_sync_media' , 'wpmf_field_bgfolder' , 'wpmf_ao_lastRun'
                wp_delete_term($child, WPMF_TAXO);
                if (isset($wpmf_list_sync_media[$child]))
                    unset($wpmf_list_sync_media[$child]);
                if (isset($wpmf_ao_lastRun[$child]))
                    unset($wpmf_ao_lastRun[$child]);
            }
            // update option 'wpmf_list_sync_media' , 'wpmf_field_bgfolder' , 'wpmf_ao_lastRun'
            update_option('wpmf_list_sync_media', $wpmf_list_sync_media);
            update_option('wpmf_ao_lastRun', $wpmf_ao_lastRun);
            //update_option('wpmf_field_bgfolder', $bgfolder);
            wp_send_json(array('type' => 'all', 'fids' => $childs));
        }else {
            // delete curent folder
            $childs = get_term_children((int) $_POST['id'], WPMF_TAXO);
            if (is_array($childs) && count($childs) > 0) {
                wp_send_json(array('type' => 'one', 'status' => false));
            } else {
                $current_term = get_term((int) $_POST['id'] , WPMF_TAXO);
                $last_action = array(
                    'type' => 'delete_folder',
                    'id_folder' => $current_term->term_id,
                    'old_parent' => $current_term->parent,
                    'name' => $current_term->name
                );
                $object_in_term = get_objects_in_term($current_term->term_id, WPMF_TAXO);
                $last_action['object_in_term'] = $object_in_term;

                $child = get_term_children((int) $_POST['parent'], WPMF_TAXO);
                // remove element $_POST['id'] in option 'wpmf_list_sync_media' , 'wpmf_field_bgfolder' , 'wpmf_ao_lastRun'
                if (isset($wpmf_list_sync_media[(int) $_POST['id']]))
                    unset($wpmf_list_sync_media[(int) $_POST['id']]);
                if (isset($wpmf_ao_lastRun[(int) $_POST['id']]))
                    unset($wpmf_ao_lastRun[(int) $_POST['id']]);
                update_option('wpmf_list_sync_media', $wpmf_list_sync_media);
                update_option('wpmf_ao_lastRun', $wpmf_ao_lastRun);
                if(wp_delete_term((int) $_POST['id'], WPMF_TAXO) != false){
                    update_option('wpmf_last_action' , $last_action);
                    wp_send_json(array('type' => 'one', 'status' => true, 'count_child' => count($child)));
                }else{
                    wp_send_json(array('type' => 'one', 'status' => false, 'count_child' => count($child)));
                }

            }
        }
    }

    /** Move a file via ajax * */
    public function wpmf_move_file() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        $return = true;
        $ids = explode(',', $_POST['ids']);
        $list_files = array();
        foreach ($ids as $id) {
            // compability with sitepress-multilingual-cms plugin
            if (defined('ICL_SITEPRESS_VERSION')) {
                global $sitepress;
                $trid = $sitepress->get_element_trid($id, 'post_attachment');
                if ($trid) {
                    $translations = $sitepress->get_element_translations($trid, 'post_attachment', true, true, true);
                    foreach ($translations as $translation) {
                        if ($translation->element_id != $id) {
                            wp_remove_object_terms((int) $translation->element_id, (int)$_POST['currentCategory'], WPMF_TAXO);
                            wp_set_object_terms((int) $translation->element_id, (int) $_POST['id_category'], WPMF_TAXO, true);
                        }
                    }
                }
            }

            wp_remove_object_terms((int) $id, (int)$_POST['currentCategory'], WPMF_TAXO);
            if ((int) $_POST['id_category'] === 0 || wp_set_object_terms((int) $id, (int) $_POST['id_category'], WPMF_TAXO, true)) {
                $list_files[] = (int) $id;
            } else {
                $return = false;
            }
        }

        if(isset($_POST['type']) && $_POST['type'] != 'undo'){
            $last_action = array(
                'type' => 'move_file',
                'old_parent' => $_POST['currentCategory'],
                'new_parent' => $_POST['id_category'],
                'list_files' => $list_files,
            );
            update_option('wpmf_last_action' , $last_action);
        }else{
            delete_option('wpmf_last_action');
        }
        $count_file_cat_parent = $this->wpmf_get_count_files($_POST['id_category']);
        $count_file_cat = $this->wpmf_get_count_files($_POST['currentCategory']);
        wp_send_json(array('status' => $return , 'datas_count' => array($count_file_cat_parent,$count_file_cat)));
    }

    /** Move a folder via ajax * */
    public function wpmf_move_folder() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        $_SESSION['wpmf_child'] = array();
        $this->get_folder_child($_POST['id']);
        if (in_array((int) $_POST['id_category'], $_SESSION['wpmf_child'])) {
            unset($_SESSION['wpmf_child']);
            return wp_send_json(array('status' => false, 'wrong' => 'wrong'));
        }

        $term = esc_attr($_POST['name']);
        $siblings = get_categories(array('taxonomy' => WPMF_TAXO,'fields' => 'names', 'get' => 'all', 'parent' => (int) $_POST['id_category']));
        if (in_array($term, $siblings)) {
            return wp_send_json(false);
        }

        $r = wp_update_term((int) $_POST['id'], WPMF_TAXO, array('parent' => (int) $_POST['id_category']));
        if ($r instanceof WP_Error) {
            wp_send_json(false);
        } else {
            $child_id = get_term_children((int) $_POST['id'], WPMF_TAXO);
            $child_id_category = get_term_children((int) $_POST['id_category'], WPMF_TAXO);
            $child_parent_id = get_term_children((int) $_POST['parent_id'], WPMF_TAXO);
            // update last action
            if(isset($_POST['type']) && $_POST['type'] != 'undo'){
                $last_action = array(
                    'type' => 'move_folder',
                    'id_folder' => $_POST['id'],
                    'old_parent' => $_POST['parent_id'],
                    'new_parent' => $_POST['id_category'],
                    'name' => $_POST['name']
                );
                update_option('wpmf_last_action' , $last_action);
            }else{
                delete_option('wpmf_last_action');
            }

            wp_send_json(array('status' => true, 'count_id' => count($child_id), 'id_category' => count($child_id_category), 'parent_id' => count($child_parent_id),));
        }
    }

    public function generatePageTree($datas, $parent = 0, $depth = 0, $limit = 0) {
        if ($limit > 1000)
            return '';
        $tree = array();
        for ($i = 0, $ni = count($datas); $i < $ni; $i++) {
            if (!empty($datas[$i])) {
                if ($datas[$i]->parent == $parent) {
                    $datas[$i]->name = $datas[$i]->name;
                    $datas[$i]->depth = $depth;
                    $tree[] = $datas[$i];
                    $t = $this->generatePageTree($datas, $datas[$i]->term_id, $depth + 1, $limit++);
                    $tree = array_merge($tree, $t);
                }
            }
        }
        return $tree;
    }

    /**
     * sort parents before children
     * http://stackoverflow.com/questions/6377147/sort-an-array-placing-children-beneath-parents
     *
     * @param array   $objects input objects with attributes 'id' and 'parent'
     * @param array   $result  (optional, reference) internal
     * @param integer $parent  (optional) internal
     * @param integer $depth   (optional) internal
     * @return array           output
     */
    public function parent_sort(array $objects, array &$result = array(), $parent = 0, $depth = 0) {
        foreach ($objects as $key => $object) {
            if ($object->parent == $parent) {
                $object->depth = $depth;
                array_push($result, $object);
                unset($objects[$key]);
                $this->parent_sort($objects, $result, $object->term_id, $depth + 1);
            }
        }
        return $result;
    }

    /* get term to display folder tree */
    public function wpmf_get_terms() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        global $current_user;
        $dir = '/';
        if (!empty($_POST['dir'])) {
            $dir = $_POST['dir'];
            if ($dir[0] == '/') {
                $dir = '.' . $dir . '/';
            }
        }
        $dir = str_replace('..', '', $dir);
        $root = dirname(__FILE__) . '/../';
        $dirs = $fi = array();
        $id = 0;
        if (!empty($_POST['id'])) {
            $id = (int) $_POST['id'];
        }
        
        // get orderby and order
        if (isset($_COOKIE['wpmf_folder_order']) && empty($_SESSION['wpmf_folder_orderby']) && empty($_SESSION['wpmf_folder_order'])) {
            $sortbys = explode('-', $_COOKIE['wpmf_folder_order']);
            $orderby = $sortbys[0];
            $order = $sortbys[1];
        } else {
            if (isset($_SESSION['wpmf_folder_orderby'])) {
                $orderby = $_SESSION['wpmf_folder_orderby'];
            } else {
                $orderby = 'name';
            }

            if (isset($_SESSION['wpmf_folder_order'])) {
                $order = $_SESSION['wpmf_folder_order'];
            } else {
                $order = 'ASC';
            }
        }
        
        // Retrieve the terms in a given taxonomy or list of taxonomies.
        $categorys = get_categories(array('taxonomy' => WPMF_TAXO,'orderby' => $orderby, 'order' => $order, 'parent' => $id, 'hide_empty' => false));
        $wpmf_active_media = get_option('wpmf_active_media');
        $wpmf_create_folder = get_option('wpmf_create_folder');
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        $current_role = $this->wpmf_get_roles(get_current_user_id());
        $option_countfiles = get_option('wpmf_option_countfiles');
        $count_file = 0;
        foreach ($categorys as $category) {
            if (($role != 'administrator' && isset($wpmf_active_media) && $wpmf_active_media == 1) || ($role == 'administrator' && isset($_SESSION['wpmf_display_media']) && $_SESSION['wpmf_display_media'] == 'yes')) {
                if ($wpmf_create_folder == 'user') {
                    if ($category->term_group == get_current_user_id()) {
                        $child = get_term_children((int) $category->term_id, WPMF_TAXO);
                        $countchild = count($child);
                        if(isset($option_countfiles) && $option_countfiles == 1){
                            $count_file = $this->wpmf_get_count_files($category->term_id);
                        }
                        $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'count_file' => $count_file);
                    }
                } else {
                    $role = $this->wpmf_get_roles($category->term_group);
                    if ($current_role == $role) {
                        if(isset($option_countfiles) && $option_countfiles == 1) {
                            $count_file = $this->wpmf_get_count_files($category->term_id);
                        }
                        $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'count_file' => $count_file);
                    }
                }
            } else {
                $child = get_term_children((int) $category->term_id, WPMF_TAXO);
                $countchild = count($child);
                if(isset($option_countfiles) && $option_countfiles == 1) {
                    $count_file = $this->wpmf_get_count_files($category->term_id);
                }
                $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'count_file' => $count_file);
            }
        }

        if (count($dirs) < 0) {
            wp_send_json('not empty');
        } else {
            wp_send_json($dirs);
        }
    }

    /* get current user role */
    public function wpmf_get_roles($userId) {
        if(!function_exists('get_userdata')){
            require_once ( ABSPATH . 'wp-includes/pluggable.php' );
        }
        $userdata = get_userdata($userId);
        if(!empty($userdata->roles)){
            $role = array_shift($userdata->roles);
        }else{
            $role = '';
        }
        return $role;
    }
    
    /* get info root folder */
    public function wpmf_term_root() {
        global $current_user;
        $wpmf_checkbox_tree = get_option('wpmf_checkbox_tree');
        if(!empty($wpmf_checkbox_tree)){
            $current_parrent = get_term($wpmf_checkbox_tree,WPMF_TAXO);
            if(!empty($current_parrent)){
                $parent = $wpmf_checkbox_tree;
            }else{
                $parent = 0;
            }
        }else{
            $parent = 0;
        }
        $term_roots = get_categories(array('taxonomy' => WPMF_TAXO ,'parent' => $parent, 'hide_empty' => false));
        $wpmfterm = array();

        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        if (count($term_roots) > 0) {
            $wpmf_create_folder = get_option('wpmf_create_folder');
            if ($wpmf_create_folder == 'user') {
                foreach ($term_roots as $term) {
                    if ($term->name == $current_user->user_login && $term->term_group == get_current_user_id()) {
                        $wpmfterm['term_rootId'] = $term->term_id;
                        $wpmfterm['term_label'] = $term->name;
                        $wpmfterm['term_parent'] = $term->parent;
                        $wpmfterm['term_slug'] = $term->slug;
                    }
                }
            } else {
                foreach ($term_roots as $term) {
                    if ($term->name == $role && strpos($term->slug, '-wpmf-role') != false) {
                        $wpmfterm['term_rootId'] = $term->term_id;
                        $wpmfterm['term_label'] = $term->name;
                        $wpmfterm['term_parent'] = $term->parent;
                        $wpmfterm['term_slug'] = $term->slug;
                    }
                }
            }
        }
        return $wpmfterm;
    }

    public function wpmf_remove_view() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_SESSION['wpmfview'])) {
            unset($_SESSION['wpmfview']);
        }
    }

    public function wpmf_change_view() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        $_SESSION['wpmfview'] = 'small';
    }
    
    /* get child term */
    public function get_folder_child($id_parent) {
        $folder_childs = get_categories(array('taxonomy' => WPMF_TAXO,'parent' => (int) $id_parent, 'hide_empty' => false));
        if (count($folder_childs) > 0) {
            foreach ($folder_childs as $child) {
                $_SESSION['wpmf_child'][] = $child->term_id;
                $this->get_folder_child($child->term_id);
            }
        }
    }
    
    /* get current view media library */
    public function wpmf_get_media_view() {
        global $wpdb;
        $views = get_user_meta(get_current_user_id(), $wpdb->prefix . 'media_library_mode');
        if (!empty($views)) {
            $curent_view = $views[0];
        } else {
            $curent_view = 'grid';
        }
        return $curent_view;
    }
    
    /* get count file by type */
    public function wpmf_count_ext($exts, $app) {
        global $wpdb;
        $count = 0;
        if ($app == 'application/pdf') {
            $sql = $wpdb->prepare("SELECT COUNT(ID) FROM " . $wpdb->prefix . 'posts' . " WHERE post_type = %s AND post_mime_type= %s ", array('attachment', 'application/pdf'));
            $count = $wpdb->get_var($sql);
        } else {
            $sql = $wpdb->prepare("SELECT COUNT(ID) FROM " . $wpdb->prefix . 'posts' . " WHERE post_type = %s AND post_mime_type IN ('application/zip','application/rar','application/ace','application/arj','application/bz2','application/cab','application/gzip','application/iso','application/jar','application/lzh','application/tar','application/uue','application/xz','application/z','application/7-zip') ", array('attachment'));
            $count = $wpdb->get_var($sql);
        }

        return $count;
    }
    
    /* get current filter file type */
    public function wpmf_get_filetype() {
        if (isset($_GET['attachment-filter'])) {
            if ($_GET['attachment-filter'] == 'wpmf-zip' || $_GET['attachment-filter'] == 'wpmf-pdf' || $_GET['attachment-filter'] == 'wpmf-other') {
                $categorytype = $_GET['attachment-filter'];
            } else {
                $categorytype = '';
            }
        } else {
            $categorytype = '';
        }

        return $categorytype;
    }

    public function wpmf_get_cookie_media($pagenow, $curent_view) {
        if ($pagenow == 'upload.php') {
            if (isset($_COOKIE[$curent_view . "wpmf_media_order"])) {
                $cook_order_media = $_COOKIE[$curent_view . "wpmf_media_order"];
            } else {
                $cook_order_media = '';
            }
        } else {
            if (isset($_COOKIE["gridwpmf_media_order"])) {
                $cook_order_media = $_COOKIE["gridwpmf_media_order"];
            } else {
                $cook_order_media = '';
            }
        }

        return $cook_order_media;
    }

    public function wpmf_get_cookie_folder() {
        if (isset($_COOKIE['wpmf_folder_order'])) {
            $cook_order_f = $_COOKIE['wpmf_folder_order'];
        } else {
            $cook_order_f = 'all';
        }

        return $cook_order_f;
    }

    public function wpmf_undo(){
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        $last_action = get_option('wpmf_last_action');
        delete_option('wpmf_last_action');
        wp_send_json($last_action);
    }

    public function wpmf_undo_deleteFolder(){
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['name']) && $_POST['name']) {
            $term = esc_attr($_POST['name']);
        } else {
            $term = __('New folder', 'wpmf');
        }
        $termParent = (int) $_POST['parent'] | 0;
        $id_author = get_current_user_id();
        // insert new term
        $inserted = wp_insert_term($term, WPMF_TAXO, array('parent' => $termParent));
        if (is_wp_error($inserted)) {
            wp_send_json($inserted->get_error_message());
        } else {
            // update term_group for new term
            $updateted = wp_update_term($inserted['term_id'], WPMF_TAXO, array('term_group' => $id_author));
            $last_action = get_option('wpmf_last_action');
            if(!empty($last_action)){
                if($last_action['type'] == 'delete_folder'){
                    if(!empty($last_action['object_in_term']) && is_array($last_action['object_in_term'])){
                        foreach ($last_action['object_in_term'] as $value){
                            wp_set_object_terms((int) $value, (int) $inserted['term_id'], WPMF_TAXO, true);
                        }
                    }
                }

            }
            // update post
            global $wpdb;
            $sql = $wpdb->prepare("SELECT ID,post_content,post_type FROM " . $wpdb->prefix . "posts WHERE post_content LIKE %s ", array('%wpmf_folder_id="'.$last_action['id_folder'].'"%'));
            $posts = $wpdb->get_results($sql);
            foreach ($posts as $post) {
                $new_content = str_replace('wpmf_folder_id="'.$last_action['id_folder'].'"','wpmf_folder_id="'.$inserted['term_id'].'"',$post->post_content);
                wp_update_post(array('ID' => $post->ID, 'post_content' => $new_content));
            }
            // update folder background
            $bg = get_option('wpmf_field_bgfolder');
            if(isset($bg[$last_action['id_folder']])){
                $bg[$inserted['term_id']] = $bg[$last_action['id_folder']];
                unset($bg[$last_action['id_folder']]);
                update_option('wpmf_field_bgfolder',$bg);
            }
            // delete last action
            delete_option('wpmf_last_action');
            $termInfos = get_term($updateted['term_id'], WPMF_TAXO);
            wp_send_json($termInfos);
        }
    }

    public function wpmf_last_filter(){
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if(isset($_POST['wpmf_select_id']) && isset($_POST['wpmf_select_value'])){
            $last_filter = get_option('wpmf_last_action');
            if(!empty($last_filter)){
                if(!empty($last_filter['type']) && $last_filter['type'] == 'filter'){
                    if($last_filter['wpmf_select_id'] == $_POST['wpmf_select_id'] && $last_filter['wpmf_select_value'] == $_POST['wpmf_select_value']){
                        wp_send_json(false);
                    }
                }
            }

            $last_action = array(
                'type' => 'filter',
                'wpmf_select_id' => $_POST['wpmf_select_id'],
                'wpmf_select_value' => $_POST['wpmf_select_value'],
            );
            update_option('wpmf_last_action' , $last_action);
            wp_send_json(true);
        }
        wp_send_json(false);
    }

    public function wpmf_delete_cookie_sort(){
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        setcookie('wpmf_folder_order', null, -1, '/');
        if(isset($_SESSION['wpmf_folder_order'])){
            unset($_SESSION['wpmf_folder_order']);
        }
        setcookie('gridwpmf_media_order', null, -1, '/');
        wp_send_json(true);
    }

    /**
     * get folder tree
     */
    public function wpmf_get_user_media_tree(){
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        global $current_user;
        $dir = '/';
        if (!empty($_POST['dir'])) {
            $dir = $_POST['dir'];
            if ($dir[0] == '/') {
                $dir = '.' . $dir . '/';
            }
        }
        $dir = str_replace('..', '', $dir);
        $dirs = $fi = array();
        $id = 0;
        if (!empty($_POST['id'])) {
            $id = (int) $_POST['id'];
        }

        // Retrieve the terms in a given taxonomy or list of taxonomies.
        $categories = get_categories(array('taxonomy' => WPMF_TAXO,'orderby' => 'name', 'order' => 'ASC', 'parent' => $id, 'hide_empty' => false));
        $wpmf_active_media = get_option('wpmf_active_media');
        $wpmf_create_folder = get_option('wpmf_create_folder');
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        $current_role = $this->wpmf_get_roles(get_current_user_id());
        $user_media_folder_root = get_option('wpmf_checkbox_tree');
        $current_parrent = get_term((int)$user_media_folder_root,WPMF_TAXO);
        if(empty($current_parrent)){
            $user_media_folder_root = 0;
        }

        if(empty($user_media_folder_root)) $user_media_folder_root = 0;
        foreach ($categories as $category) {
            $checked = true;
            if($category->term_id == $user_media_folder_root){
                $checked = true;
                $pchecked = false;
            }else{
                $checked = false;
                $pchecked = $this->user_media_check_checked($category->term_id,$user_media_folder_root);
            }
            if (($role != 'administrator' && isset($wpmf_active_media) && $wpmf_active_media == 1) || ($role == 'administrator' && isset($_SESSION['wpmf_display_media']) && $_SESSION['wpmf_display_media'] == 'yes')) {
                if ($wpmf_create_folder == 'user') {
                    if ($category->term_group == get_current_user_id()) {
                        $child = get_term_children((int) $category->term_id, WPMF_TAXO);
                        $countchild = count($child);
                        $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'checked' => $checked , 'pchecked' => $pchecked);
                    }
                } else {
                    $role = $this->wpmf_get_roles($category->term_group);
                    if ($current_role == $role) {
                        $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'checked' => $checked , 'pchecked' => $pchecked);
                    }
                }
            } else {
                $child = get_term_children((int) $category->term_id, WPMF_TAXO);
                $countchild = count($child);
                $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'checked' => $checked , 'pchecked' => $pchecked);
            }
        }

        if (count($dirs) < 0) {
            wp_send_json(array('status' => false));
        } else {
            wp_send_json(array('dirs' => $dirs , 'user_media_folder_root' => $user_media_folder_root , 'status' => true));
        }
    }

    /*
    get assign folder tree
    */
    public function wpmf_get_assign_tree() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        global $current_user;
        $dir = '/';
        if (!empty($_POST['dir'])) {
            $dir = $_POST['dir'];
            if ($dir[0] == '/') {
                $dir = '.' . $dir . '/';
            }
        }
        $dir = str_replace('..', '', $dir);
        $dirs = $fi = array();
        $id = 0;
        if (!empty($_POST['id'])) {
            $id = (int) $_POST['id'];
        }

        // Retrieve the terms in a given taxonomy or list of taxonomies.
        $categories = get_categories(array('taxonomy' => WPMF_TAXO,'orderby' => 'name', 'order' => 'ASC', 'parent' => $id, 'hide_empty' => false));
        $wpmf_active_media = get_option('wpmf_active_media');
        $wpmf_create_folder = get_option('wpmf_create_folder');
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        $current_role = $this->wpmf_get_roles(get_current_user_id());
        $term_of_file = wp_get_object_terms((int) $_POST['attachment_id'], WPMF_TAXO, array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'ids') );
        // check image in root
        $root_check = true;
        $root_media_root = get_term_by('name', __('WP Media Folder Root', 'wpmf'), WPMF_TAXO);
        if(empty($term_of_file) || (!empty($term_of_file) && in_array($root_media_root->term_id , $term_of_file))){
            $root_check = true;
        }else{
            $root_check = false;
        }

        foreach ($categories as $category) {
            $checked = true;
            if(in_array($category->term_id,$term_of_file)){
                $checked = true;
                $pchecked = false;
            }else{
                $checked = false;
                $pchecked = $this->check_checked($category->term_id,$term_of_file);
            }
            if (($role != 'administrator' && isset($wpmf_active_media) && $wpmf_active_media == 1) || ($role == 'administrator' && isset($_SESSION['wpmf_display_media']) && $_SESSION['wpmf_display_media'] == 'yes')) {
                if ($wpmf_create_folder == 'user') {
                    if ($category->term_group == get_current_user_id()) {
                        $child = get_term_children((int) $category->term_id, WPMF_TAXO);
                        $countchild = count($child);
                        $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'checked' => $checked , 'pchecked' => $pchecked);
                    }
                } else {
                    $role = $this->wpmf_get_roles($category->term_group);
                    if ($current_role == $role) {
                        $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'checked' => $checked , 'pchecked' => $pchecked);
                    }
                }
            } else {
                $child = get_term_children((int) $category->term_id, WPMF_TAXO);
                $countchild = count($child);
                $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $category->name, 'id' => $category->term_id, 'parent_id' => $category->parent, 'count_child' => $countchild, 'term_group' => $category->term_group , 'checked' => $checked , 'pchecked' => $pchecked);
            }
        }

        if (count($dirs) < 0) {
            wp_send_json(array('status' => false));
        } else {
            wp_send_json(array('dirs' => $dirs , 'root_check' => $root_check , 'status' => true));
        }
    }

    public function check_checked($term_id,$term_of_file){
        $childs = get_term_children((int) $term_id, WPMF_TAXO);
        $pchecked = false;
        foreach ($childs as $child){
            if(in_array($term_of_file,(array)$child)){
                $pchecked = true;
                break;
            }else{
                $pchecked = false;
                $this->check_checked($child,$term_of_file);
            }
        }
        return $pchecked;
    }

    public function user_media_check_checked($term_id,$termID){
        $childs = get_term_children((int) $term_id, WPMF_TAXO);
        $pchecked = false;
        foreach ($childs as $child){
            if($child == $termID){
                $pchecked = true;
                break;
            }else{
                $pchecked = false;
                $this->check_checked($child,$termID);
            }
        }
        return $pchecked;
    }

    /*
     * Set file to multiple folders
     */
    public function wpmf_set_object_term(){
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if(isset($_POST['attachment_id']) && $_POST['attachment_id'] != ''){
            $attachment_ids = explode(',',$_POST['attachment_id']);
            foreach ($attachment_ids as $attachment_id){
                // set file to list folder checked
                if(isset($_POST['wpmf_term_ids_check'])){
                    $wpmf_term_ids_check = explode(',',$_POST['wpmf_term_ids_check']);
                    foreach ($wpmf_term_ids_check as $term_id){
                        wp_set_object_terms((int) $attachment_id,(int) $term_id , WPMF_TAXO , true);
                    }
                }

                // unset file to list folder checked
                if(isset($_POST['wpmf_term_ids_notcheck'])){
                    $wpmf_term_ids_notcheck = explode(',',$_POST['wpmf_term_ids_notcheck']);
                    foreach ($wpmf_term_ids_notcheck as $term_id){
                        wp_remove_object_terms((int) $attachment_id, (int)$term_id, WPMF_TAXO);
                    }
                }
            }
        }else{
            wp_send_json(false);
        }

        if(isset($_POST['type']) && $_POST['type'] == 'one'){
            $term_of_file = wp_get_object_terms((int) $_POST['attachment_id'], WPMF_TAXO, array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'ids') );
            wp_send_json($term_of_file);
        }else{
            wp_send_json(true);
        }
    }

    public function wpmf_update_file_title( $pid ) {
        $options_format_title = get_option('wpmf_options_format_title');
        $post = get_post($pid);
        if(!empty($post)){
            $title = $post->post_title;

            /* create array character from settings */
            $character = array();
            if (isset($options_format_title['tilde']) && $options_format_title['tilde']) {
                $character[] = '~';
            }
            if (isset($options_format_title['underscore']) && $options_format_title['underscore']) {
                $character[] = '_';
            }
            if (isset($options_format_title['period']) && $options_format_title['period']) {
                $character[] = '.';
            }
            if (isset($options_format_title['plus']) && $options_format_title['plus']) {
                $character[] = '+';
            }
            if (isset($options_format_title['hyphen']) && $options_format_title['hyphen']) {
                $character[] = '-';
            }

            if (isset($options_format_title['hash']) && $options_format_title['hash']) {
                $character[] = '#';
            }

            if (isset($options_format_title['ampersand']) && $options_format_title['ampersand']) {
                $character[] = '@';
            }

            if (isset($options_format_title['number']) && $options_format_title['number']) {
                for ($i = 0;$i<=9;$i++){
                    $character[] = $i;
                }
            }

            if (isset($options_format_title['square_brackets']) && $options_format_title['square_brackets']) {
                $character[] = '[]';
            }

            if (isset($options_format_title['round_brackets']) && $options_format_title['round_brackets']) {
                $character[] = '()';
            }

            if (isset($options_format_title['curly_brackets']) && $options_format_title['curly_brackets']) {
                $character[] = '{}';
            }

            /* Replace character to space */
            if (!empty($character)) {
                $title = str_replace($character, ' ', $title);
            }

            $title = preg_replace("/\s+/", " ", $title);
            $capita = $options_format_title['capita'];

            /* Capitalize Title. */
            switch ($capita) {
                case 'cap_all':
                    $title = ucwords($title);
                    break;
                case 'all_upper':
                    $title = strtoupper($title);
                    break;
                case 'cap_first':
                    $title = ucfirst(strtolower($title));
                    break;
                case 'all_lower':
                    $title = strtolower($title);
                    break;
                case 'dont_alter':
                    break;
            }

            // update _wp_attachment_image_alt
            if (isset($options_format_title['alt']) && $options_format_title['alt']) {
                update_post_meta($pid, '_wp_attachment_image_alt', $title);
            }

            // update post
            $uploaded_post = array();
            $uploaded_post['ID'] = $pid;
            $uploaded_post['post_title'] = $title;

            if (isset($options_format_title['description']) && $options_format_title['description']) {
                $uploaded_post['post_content'] = $title;
            }

            if (isset($options_format_title['caption']) && $options_format_title['caption']) {
                $uploaded_post['post_excerpt'] = $title;
            }

            wp_update_post($uploaded_post);
        }
    }

    /**
     * ajax create remote video
     */
    public function wpmf_create_remote_video(){
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if(isset($_POST['wpmf_remote_link'])){
            $url = $_POST['wpmf_remote_link'];
            // check if link is a valid youtube link
            $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
            if(!preg_match($regex_pattern, $url, $match)){
                wp_send_json(array('status' => false , 'msg' => __('Sorry, not a youtube URL','wpmf')));
            }

            // get thumbnail of video
            $parts = parse_url($url);
            parse_str($parts['query'], $query);
            $content = @file_get_contents("http://img.youtube.com/vi/".$query['v']."/sddefault.jpg");
            if(empty($content)){
                wp_send_json(array('status' => false , 'msg' => __('Sorry, this video not found','wpmf')));
            }
            $upload_dir = wp_upload_dir();
            $infos = @file_get_contents("http://youtube.com/get_video_info?video_id=".$query['v']);
            parse_str($infos, $info);
            if(isset($info['status']) && $info['status'] == 'fail'){
                wp_send_json(array('status' => false , 'msg' => $info['reason']));
            }

            $info_thumbnail = pathinfo($info['thumbnail_url']); // get info thumbnail
            $title = sanitize_title($info['title']); // get title video
            // create wpmf_remote_video folder
            if(!file_exists($upload_dir['basedir'] .'/wpmf_remote_video')){
                if (!mkdir($upload_dir['basedir'] .'/wpmf_remote_video')) {
                    wp_send_json(array('status' => false , 'msg' => __('Failed to create folders...','wpmf')));
                }
            }

            // upload  thumbnail to wpmf_remote_video folder
            $upload_folder = $upload_dir['basedir'] .'/wpmf_remote_video';
            $upload = file_put_contents($upload_folder.'/'.$title.'.'.$info_thumbnail['extension'],$content);

            // upload images
            if ($upload) {
                $attachment = array(
                    'guid' => $upload_dir['baseurl'] . '/' . $title . '.' .$info_thumbnail['extension'],
                    'post_mime_type' => ($info_thumbnail['extension'] == 'jpg') ? 'image/jpeg' : 'image/' . $info_thumbnail['extension'],
                    'post_title' => $title,
                    'post_content' => 'wpmf_remote_video'
                );

                $image_path = $upload_folder.'/'.$title.'.'.$info_thumbnail['extension'];
                $attach_id = wp_insert_attachment($attachment, $image_path);

                $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
                wp_update_attachment_metadata($attach_id, $attach_data);
                update_post_meta($attach_id, 'wpmf_remote_video_link', $url);
                // create image in folder
                $current_folder_id = $this->wpmf_get_current_folder_id();
                if(empty($current_folder_id)) $current_folder_id = 0;
                wp_set_object_terms((int) $attach_id, (int) $current_folder_id, WPMF_TAXO, false);
                wp_send_json(array('status' => true , 'msg' => __('Upload success!','wpmf')));
            }
        }
        wp_send_json(array('status' => false , 'msg' => __('Upload errors...','wpmf')));
    }

    public function get_youtube_title($video_id){
        $html = 'https://www.googleapis.com/youtube/v3/videos?id='.$video_id.'&key=alskdfhwueoriwaksjdfnzxcvxzfserwesfasdfs&part=snippet';
        $response = file_get_contents($html);
        $decoded = json_decode($response, true);
        foreach ($decoded['items'] as $items) {
            $title= $items['snippet']['title'];
            return $title;
        }
    }

    public function wpmf_attachment_fields_to_edit($form_fields, $post) {
        $form_fields['wpmf_gallery_custom_image_link'] = array(
            "label" => __('Link to', 'wpmf'),
            "input" => "html",
            'html' => '<input type="text" class="text" id="attachments-' . $post->ID . '-wpmf_gallery_custom_image_link" name="attachments[' . $post->ID . '][wpmf_gallery_custom_image_link]" value="' . get_post_meta($post->ID, _WPMF_GALLERY_PREFIX . "custom_image_link", true) . '"> <button role="presentation" type="button" id="link-btn" class="link-btn"><i class="zmdi zmdi-link wpmf-zmdi-link"></i></button>'
        );

        $remote_video = get_post_meta($post->ID,'wpmf_remote_video_link');
        if(!empty($remote_video)){
            $form_fields['wpmf_remote_video_link'] = array(
                "label" => __('Remote video', 'wpmf'),
                "input" => "html",
                'html' => '<input type="text" class="text" id="attachments-' . $post->ID . '-wpmf_remote_video_link" name="attachments[' . $post->ID . '][wpmf_remote_video_link]" value="' . get_post_meta($post->ID, 'wpmf_remote_video_link', true) . '">'
            );
        }
        return $form_fields;
    }

    /**
     * Add video to editor
     */
    public function add_remote_video($html, $id, $attachment){
        $remote_video = get_post_meta($id,'wpmf_remote_video_link');
        if(!empty($remote_video)){
            $html = $remote_video;
        }
        return $html;
    }

    public function wpmf_attachment_fields_to_save($post, $attachment) {
        if (isset($attachment['wpmf_remote_video_link'])) {
            $url = $attachment['wpmf_remote_video_link'];
            // get thumbnail of video
            $parts = parse_url($url);
            parse_str($parts['query'], $query);
            $content = @file_get_contents("http://img.youtube.com/vi/".$query['v']."/sddefault.jpg");
            $upload_dir = wp_upload_dir();

            // check if link is a valid youtube link
            $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
            if(!preg_match($regex_pattern, $url, $match)){
                return $post;
            }

            // get thumbnail of video
            $parts = parse_url($url);
            parse_str($parts['query'], $query);
            $check_img = @file_get_contents("http://img.youtube.com/vi/".$query['v']."/sddefault.jpg");
            if(empty($check_img)){
                return $post;
            }

            $filepath = get_attached_file($post['ID']);
            $infos = pathinfo($filepath);
            $metadata = wp_get_attachment_metadata($post['ID']);

            // upload  thumbnail to wpmf_remote_video folder
            @unlink($filepath);
            $upload_folder = $upload_dir['basedir'] .'/wpmf_remote_video';
            $upload = file_put_contents($upload_folder.'/'.$infos['basename'],$content);
            if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                foreach ($metadata['sizes'] as $size => $sizeinfo) {
                    $intermediate_file = str_replace(basename($filepath), $sizeinfo['file'], $filepath);
                    /** This filter is documented in wp-includes/functions.php */
                    $intermediate_file = apply_filters('wp_delete_file', $intermediate_file);
                    @unlink(path_join($upload_dir['basedir'], $intermediate_file));
                }
            }

            $this->createThumbs($filepath, $infos['extension'], $metadata,$post['ID']);
            update_post_meta($post['ID'], 'wpmf_remote_video_link', esc_url_raw($attachment['wpmf_remote_video_link']));
        }
        return $post;
    }
}
?>