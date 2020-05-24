<?php

class Wpmf_Fillter_Size {

    function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'wpmf_admin_page_table_script'));
        add_action('wp_enqueue_media', array($this, 'wpmf_load_custom_wp_admin_script'));
        add_action('pre_get_posts', array($this, 'wpmf_fillter_size_weight1'));
        add_action('pre_get_posts', array($this, 'wpmf_fillter_size_weight'), 0, 1);
        add_action('wp_ajax_wpmf_folder_order', array($this, 'wpmf_folder_order'));
        add_action('wp_ajax_wpmf_media_order', array($this, 'wpmf_media_order'));
    }
    
    /* includes styles and some scripts */
    public function wpmf_admin_page_table_script() {
        global $pagenow;
        if ($pagenow == 'upload.php') {
            $this->wpmf_load_custom_wp_admin_script();
        }
    }
    
    /* includes styles and some scripts */
    public function wpmf_load_custom_wp_admin_script() {
        wp_enqueue_script('wpmf-fillter-size');
        wp_localize_script('wpmf-fillter-size', 'wpmflang_fillter', $this->wpmf_localize_script());
        wp_enqueue_style('wpmf-style_fillter_order', plugins_url('/assets/css/style_fillter_order.css', dirname(__FILE__)), array(), WPMF_VERSION);
    }

    public function wpmf_localize_script() {
        return array(
            'mimetype' => __('All media items', 'wpmf'),
            'all_size_label' => __('Minimal size', 'wpmf'),
            'all_weight_label' => __('All weight', 'wpmf'),
            'order_folder_label' => __('Sort folder', 'wpmf'),
            'order_img_label' => __('Sort media', 'wpmf'),
        );
    }
    
    /* Query post by size and weight */
    public function wpmf_fillter_size_weight($query) {
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] != 'attachment')
            return;
        $id_pots = array();
        if ((empty($_REQUEST['query']['wpmf_weight']) || $_REQUEST['query']['wpmf_weight'] == 'all') && (isset($_REQUEST['query']['wpmf_size']) && $_REQUEST['query']['wpmf_size'] != 'all')) {
            $id_pots = $this->wpmf_get_size($_REQUEST['query']['wpmf_size'], '');
        }

        if ((empty($_REQUEST['query']['wpmf_size']) || $_REQUEST['query']['wpmf_size'] == 'all') && (isset($_REQUEST['query']['wpmf_weight']) && $_REQUEST['query']['wpmf_weight'] != 'all')) {
            $id_pots = $this->wpmf_get_size('', $_REQUEST['query']['wpmf_weight']);
        }

        if ((isset($_REQUEST['query']['wpmf_size']) && $_REQUEST['query']['wpmf_size'] != 'all') && (isset($_REQUEST['query']['wpmf_weight']) && $_REQUEST['query']['wpmf_weight'] != 'all')) {
            $id_pots = $this->wpmf_get_size($_REQUEST['query']['wpmf_size'], $_REQUEST['query']['wpmf_weight']);
        }
        
        if(!empty($id_pots)){
            $query->query_vars['post__in'] = $id_pots;
        }
        
        return $query;
    }

    /* Query post by size and weight */
    public function wpmf_fillter_size_weight1($query) {
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] != 'attachment')
            return;
        global $pagenow;
        if ($pagenow == 'upload.php') {
            $id_pots = array();
            if ((isset($_GET['attachment_size']) && $_GET['attachment_size'] != 'all') && (empty($_GET['attachment_weight']) || $_GET['attachment_weight'] == 'all')) {
                $id_pots = $this->wpmf_get_size($_GET['attachment_size'], '');
            }

            if ((isset($_GET['attachment_weight']) && $_GET['attachment_weight'] != 'all' ) && (empty($_GET['attachment_size']) || $_GET['attachment_size'] == 'all')) {
                $id_pots = $this->wpmf_get_size('', $_GET['attachment_weight']);
            }

            if ((isset($_GET['attachment_size']) && $_GET['attachment_size'] != 'all') && (isset($_GET['attachment_weight']) && $_GET['attachment_weight'] != 'all')) {
                $id_pots = $this->wpmf_get_size($_GET['attachment_size'], $_GET['attachment_weight']);
            }
            
            if(!empty($id_pots)){
                $query->query_vars['post__in'] = $id_pots;
            }
        }
    }

    /* Get attachment size */
    public function wpmf_get_size($sizes, $weights) {
        if ($sizes != '') {
            $size = explode('x', $sizes);
            $w_size = (float) $size[0];
            $h_size = (float) $size[1];
        }
        
        if ($weights != '') {
            $weight = explode('-', $weights);
            $min_weight = (float) $weight[0];
            $max_weight = (float) $weight[1];
        }
        $id_pots = array(0);
        $upload_dir = wp_upload_dir();
        global $wpdb;
        $sql = $wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . 'posts' . " WHERE post_type = %s ", array('attachment'));
        $attachments = $wpdb->get_results($sql);
        foreach ($attachments as $attachment) {
            $meta_img = wp_get_attachment_metadata($attachment->ID);
            $meta = get_post_meta($attachment->ID, '_wp_attached_file');
            if (isset($meta[0])) {
                $url_path = $upload_dir['basedir'] . '/' . $meta[0];
                if (file_exists($url_path)) {
                    $weight_att = filesize($url_path);
                } else {
                    $weight_att = 0;
                }
            } else {
                $weight_att = 0;
            }

            if (isset($meta_img['width']) && isset($meta_img['height'])) {
                
            } else {
                $meta_img['width'] = 0;
                $meta_img['height'] = 0;
            }
            
            if ($weights == '') {
                if ((float) $meta_img['width'] >= $w_size || (float) $meta_img['height'] >= $h_size) {
                    if (substr(get_post_mime_type($attachment->ID), 0, 5) == 'image') {
                        $id_pots[] = $attachment->ID;
                    }
                }
            } else if ($sizes == '') {
                if ((float) $weight_att >= $min_weight && (float) $weight_att <= $max_weight) {
                    $id_pots[] = $attachment->ID;
                }
            } else {
                if (((float) $meta_img['width'] >= $w_size || (float) $meta_img['height'] >= $h_size) && ((float) $weight_att >= $min_weight && (float) $weight_att <= $max_weight)) {
                    if (substr(get_post_mime_type($attachment->ID), 0, 5) == 'image') {
                        $id_pots[] = $attachment->ID;
                    }
                }
            }
        }
        
        return $id_pots;
    }
    
    /* setcookie for sort folder */
    public function wpmf_folder_order() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['wpmf_folder_order']) && $_POST['wpmf_folder_order'] != 'all') {
            $sortbys = explode('-', $_POST['wpmf_folder_order']);
            $_SESSION['wpmf_folder_orderby'] = $sortbys[0];
            $_SESSION['wpmf_folder_order'] = $sortbys[1];

            $cookie_name = "wpmf_folder_order";
            $cookie_value = $_POST['wpmf_folder_order'];
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
            wp_send_json($_SESSION['wpmf_folder_order']);
        } else {
            setcookie('wpmf_folder_order', null, -1, '/');
            wp_send_json('all');
        }
    }
    
    /* setcookie for sort attachment */
    public function wpmf_media_order() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['value']) && $_POST['value'] != 'all') {
            $curent_view = $this->wpmf_get_media_view();
            $wpmf_media = $curent_view . "wpmf_media_order";
            $wpmf_mediavalue = $_POST['value'];
            setcookie($wpmf_media, $wpmf_mediavalue, time() + (86400 * 30), "/");
        }
    }
    
    /* Get current view of media library */
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

}

?>