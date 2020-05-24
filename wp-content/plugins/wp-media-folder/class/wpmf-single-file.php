<?php

class Single_File {

    function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'wpmf_single_file_style_admin'));
        $singlefile = get_option('wpmf_option_singlefile');
        if (isset($singlefile) && $singlefile == 1) {
            add_filter('media_send_to_editor', array($this, 'add_image_files'), 10, 3);
        }
        add_action('wp_enqueue_scripts', array($this, 'wpmf_load_custom_wp_admin_script'));
        add_filter('mce_external_plugins', array($this, 'wpmf_register'));
    }
    
    /* includes script */
    function wpmf_register($plugin_array) {
        $url = WPMF_PLUGIN_URL . '/assets/js/single-file.js';
        $plugin_array["wpmf_mce"] = $url;
        return $plugin_array;
    }
    
    /* includes styles */
    function wpmf_single_file_style_admin() {
        add_editor_style(plugins_url('/assets/css/wpmf_single_file.css', dirname(__FILE__)));
    }
    
    /* includes styles */
    function wpmf_load_custom_wp_admin_script() {
        wp_enqueue_style('wpmf-single-file', plugins_url('/assets/css/wpmf_single_file.css', dirname(__FILE__)), array(), WPMF_VERSION);
    }
    
    /* Custom html file after insert to editor */
    function add_image_files($html, $id, $attachment) {
        $post = get_post($id);
        $mimetype = explode("/", $post->post_mime_type);
        $target = get_post_meta($id, '_gallery_link_target', true);
        $meta = get_post_meta($id, '_wp_attached_file');
        $upload_dir = wp_upload_dir();
        $url_attachment = $upload_dir['basedir'] . '/' . $meta[0];
        if (file_exists($url_attachment)) {
            $size = filesize($url_attachment);
            if ($size < 1024 * 1024) {
                $size = round($size / 1024, 1) . ' kB';
            } else if ($size > 1024 * 1024) {
                $size = round($size / (1024 * 1024), 1) . ' MB';
            }
        } else {
            $size = 0;
        }

        if ($mimetype[0] == 'application' && $mimetype[1] != 'pdf') {
            $type = wp_check_filetype($post->guid);
            $ext = $type['ext'];
            $html = '<span class="wpmf_mce-wrap" data-file="' . $id . '" style="overflow: hidden;">';
            $html .= '<a class="wpmf-defile wpmf_mce-single-child" href="' . $post->guid . '" data-id="' . $id . '" target="'.$target.'">';
            $html .= '<span class="wpmf_mce-single-child" style="font-weight: bold;">' . $post->post_title . '</span><br>';
            $html .= '<span class="wpmf_mce-single-child" style="font-weight: normal;font-size: 0.8em;"><b class="wpmf_mce-single-child">Size : </b>' . $size . '<b class="wpmf_mce-single-child"> Format : </b>' . strtoupper($ext) . '</span>';
            $html .= '</a>';
            $html .= '</span>';
        }
        return $html;
    }

}
