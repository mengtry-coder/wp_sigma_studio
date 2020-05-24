<?php
/*
  Plugin Name: WP Media folder
  Plugin URI: http://www.joomunited.com
  Description: WP media Folder is a WordPress plugin that enhance the WordPress media manager by adding a folder manager inside.
  Author: Joomunited
  Version: 4.2.5
  Author URI: http://www.joomunited.com
  Text Domain: wpmf
  Domain Path: /languages
  Licence : GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
  Copyright : Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 */
// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');

//Check plugin requirements
if (version_compare(PHP_VERSION, '5.3', '<')) {
    if( !function_exists('wpmf_disable_plugin') ){
        function wpmf_disable_plugin(){
            if ( current_user_can('activate_plugins') && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
                deactivate_plugins( __FILE__ );
                unset( $_GET['activate'] );
            }
        }
    }

    if( !function_exists('wpmf_show_error') ){
        function wpmf_show_error(){
            echo '<div class="error"><p><strong>WP Media Folder</strong> need at least PHP 5.3 version, please update php before installing the plugin.</p></div>';
        }
    }

    //Add actions
    add_action( 'admin_init', 'wpmf_disable_plugin' );
    add_action( 'admin_notices', 'wpmf_show_error' );

    //Do not load anything more
    return;
}

//Include the jutranslation helpers
include_once('jutranslation' . DIRECTORY_SEPARATOR . 'jutranslation.php');
call_user_func('\Joomunited\WPMediaFolder\Jutranslation\Jutranslation::init',__FILE__, 'wpmf', 'WP Media Folder', 'wpmf', 'languages' . DIRECTORY_SEPARATOR . 'wpmf-en_US.mo');

if (!defined('WP_MEDIA_FOLDER_PLUGIN_DIR'))
    define('WP_MEDIA_FOLDER_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (!defined('WPMF_FILE')) {
    define('WPMF_FILE', __FILE__);
}

if (!defined('WPMF_TAXO')) {
    define('WPMF_TAXO', 'wpmf-category');
}

if (!defined('WPMF_ABSPATH')) {
    define('WPMF_ABSPATH', ABSPATH);
}

define('WPMF_GALLERY_PREFIX', 'wpmf_gallery_');
define('_WPMF_GALLERY_PREFIX', '_wpmf_gallery_');
define('WPMF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPMF_DOMAIN', 'wpmf');
define('WPMF_URL', plugin_dir_url(__FILE__));
define('WPMF_VERSION', '4.2.5');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
register_activation_hook(__FILE__, 'wp_media_folder_install');

function wp_media_folder_install() {
    global $wpdb;
    $query = "INSERT INTO " . $wpdb->prefix . "postmeta (post_id, meta_key, meta_value) VALUES ";
    $limit = 100;
    $values = array();
    $place_holders = array();
    $total = $wpdb->get_var("SELECT COUNT(posts.ID) as total FROM " . $wpdb->prefix . "posts as posts
               WHERE   posts.post_type = 'attachment'");

    if($total <= 10000){
        $j = ceil((int) $total / $limit);
        for ($i = 1; $i <= $j; $i++) {

            $ofset = ($i - 1) * $limit;
            $attachments = $wpdb->get_results("SELECT ID FROM " . $wpdb->prefix . "posts as posts
               WHERE   posts.post_type     = 'attachment' LIMIT $limit OFFSET $ofset");

            foreach ($attachments as $attachment) {

                $wpmf_size_filetype = wpmf_get_sizefiletype($attachment->ID);
                $size = $wpmf_size_filetype['size'];
                $ext = $wpmf_size_filetype['ext'];
                if (!get_post_meta($attachment->ID, 'wpmf_size')) {
                    array_push($values, $attachment->ID, 'wpmf_size', $size);
                    $place_holders[] = "('%d', '%s', '%s')";
                }

                if (!get_post_meta($attachment->ID, 'wpmf_filetype')) {
                    array_push($values, $attachment->ID, 'wpmf_filetype', $ext);
                    $place_holders[] = "('%d', '%s', '%s')";
                }
            }

            if (count($place_holders) > 0) {
                $query = "INSERT INTO " . $wpdb->prefix . "postmeta (post_id, meta_key, meta_value) VALUES ";
                $query .= implode(', ', $place_holders);
                $wpdb->query($wpdb->prepare("$query ", $values));
                $place_holders = array();
                $values = array();
            }
        }
    }
}

/* Get size and file type for attachment */

function wpmf_get_sizefiletype($pid) {
    $wpmf_size_filetype = array();
    $meta = get_post_meta($pid, '_wp_attached_file');
    $upload_dir = wp_upload_dir();
    $url_attachment = $upload_dir['basedir'] . '/' . $meta[0];
    if (file_exists($url_attachment)) {
        $size = filesize($url_attachment);
        $filetype = wp_check_filetype($url_attachment);
        $ext = $filetype['ext'];
    } else {
        $size = 0;
        $ext = '';
    }
    $wpmf_size_filetype['size'] = $size;
    $wpmf_size_filetype['ext'] = $ext;

    return $wpmf_size_filetype;
}

$option_mediafolder = get_option('wpmf_option_mediafolder');
if (!empty($option_mediafolder) || is_admin()) {
    require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-media-folder.php' );
    $GLOBALS['wp_media_folder'] = new Wp_Media_Folder;
    require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/wpmf-display-own-media.php' );
    new Wpmf_Display_Own_Media;
    $useorder = get_option('wpmf_useorder');
    if (isset($useorder) && $useorder == 1) {
        require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/wpmf-orderby-media.php' );
        new Wpmf_Add_Columns_Media;
        require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/wpmf-fillter-size.php' );
        new Wpmf_Fillter_Size;
    }

    $option_duplicate = get_option('wpmf_option_duplicate');
    if (isset($option_duplicate) && $option_duplicate == 1) {
        require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/wpmf-duplicate-file.php' );
        new Wpmf_duplicate_file;
    }

    $wpmf_media_rename = get_option('wpmf_media_rename');
    if (isset($wpmf_media_rename) && $wpmf_media_rename == 1) {
        require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-media-rename.php' );
        new Wpmf_Media_Rename;
    }

    require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/wpmf-background-folder.php' );
    new Wpmf_Background_Folder;

    $option_override = get_option('wpmf_option_override');
    if (isset($option_override) && $option_override == 1) {
        require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/wpmf-replace-image.php' );
        new Wpmf_replace_file;
    }

    require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/wpmf_image_watermark.php' );
    new Wpmf_watermark;
}

$usegellery = get_option('wpmf_usegellery');
if (isset($usegellery) && $usegellery == 1) {
    require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/wpmf-display-gallery.php' );
    new Wpmf_Display_Gallery;
}

require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-wp-folder-option.php' );
new Media_Folder_Option;
$wpmf_option_singlefile = get_option('wpmf_option_singlefile');
if (isset($wpmf_option_singlefile) && $wpmf_option_singlefile == 1) {
    require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/wpmf-single-file.php' );
    new Single_File();
}

$wpmf_option_lightboximage = get_option('wpmf_option_lightboximage');
if (isset($wpmf_option_lightboximage) && $wpmf_option_lightboximage == 1) {
    require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-single-lightbox.php' );
    new Wpmf_Single_Lightbox;
}

require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/wpmf-pdf-embed.php' );
new Wpmf_Pdf_Embed();

add_action('wp_enqueue_media', 'wpmf_add_style');
function wpmf_add_style() {
    wp_enqueue_style('wpmf-style-linkbtn', plugins_url('/assets/css/style_linkbtn.css', __FILE__), array(), WPMF_VERSION);
}

/* Register WPMF_TAXO taxonomy */
add_action('init', 'wpmf_register_taxonomy_for_images', 0);
function wpmf_register_taxonomy_for_images() {
    register_taxonomy(WPMF_TAXO, 'attachment', array(
        'hierarchical' => true,
        'show_in_nav_menus' => false,
        'show_ui' => false,
        'public' => false,
        'labels' => array(
            'name' => __('WPMF Categories', 'wpmf'),
            'singular_name' => __('WPMF Category', 'wpmf'),
            'menu_name' => __('WPMF Categories', 'wpmf'),
            'all_items' => __('All WPMF Categories', 'wpmf'),
            'edit_item' => __('Edit WPMF Category', 'wpmf'),
            'view_item' => __('View WPMF Category', 'wpmf'),
            'update_item' => __('Update WPMF Category', 'wpmf'),
            'add_new_item' => __('Add New WPMF Category', 'wpmf'),
            'new_item_name' => __('New WPMF Category Name', 'wpmf'),
            'parent_item' => __('Parent WPMF Category', 'wpmf'),
            'parent_item_colon' => __('Parent WPMF Category:', 'wpmf'),
            'search_items' => __('Search WPMF Categories', 'wpmf'),
        ),
    ));

    $root_id = get_option('wpmf_folder_root_id', false) ;
    if($root_id) {
        $tag = get_term_by('id', $root_id, WPMF_TAXO);
    }else {
        $tag = get_term_by('name', __('WP Media Folder Root', 'wpmf'), WPMF_TAXO);
        if(empty($tag)){
            $inserted = wp_insert_term(__('WP Media Folder Root', 'wpmf'), WPMF_TAXO, array('parent' => 0));
            if (!get_option('wpmf_folder_root_id', false)) {
                add_option('wpmf_folder_root_id', $inserted['term_id'], '', 'yes');
            }
        }else{
            if (!get_option('wpmf_folder_root_id', false)) {
                add_option('wpmf_folder_root_id', $tag->term_id, '', 'yes');
            }
        }
    }

}

//config section        
if (!defined('JU_BASE')) {
    define('JU_BASE', 'https://www.joomunited.com/');
}

$remote_updateinfo = JU_BASE . 'juupdater_files/wp-media-folder.json';
//end config

require 'juupdater/juupdater.php';
$UpdateChecker = Jufactory::buildUpdateChecker(
                $remote_updateinfo, __FILE__
);
