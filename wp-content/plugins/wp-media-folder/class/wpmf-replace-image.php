<?php
require_once( WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-main-function.php' );
class Wpmf_replace_file extends Wpmf_main{

    function __construct() {
        add_action('wp_enqueue_media', array($this, 'wpmf_enqueue_admin_scripts'));
        add_action('wp_ajax_wpmf_replace_file', array($this, 'wpmf_replace_file'));
    }

    /* Ajax replace attachment */
    public function wpmf_replace_file() {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (!empty($_FILES["wpmf_replace_file"])) {
            if (empty($_POST['post_selected'])) {
                _e('Post empty', 'wpmf');
                die();
            }

            $id = $_POST['post_selected'];
            $post = get_post($id);
            $metadata = wp_get_attachment_metadata($id);

            $filepath = get_attached_file($id);
            $infopath = pathinfo($filepath);
            $allowedTypes = array('gif', 'jpg', 'png', 'bmp' ,'pdf','svg','html');
            $allowedImageTypes = array('gif', 'jpg', 'png', 'bmp');
            if (in_array($infopath['extension'], $allowedTypes) == false) {
                wp_send_json(array('status' => false , 'msg' => __('Please replace the media with a media that has the same format (jpg by jpg, pdf by pdf…)','wpmf')));
            }
            $new_filetype = wp_check_filetype($_FILES["wpmf_replace_file"]["name"]);
            if ($new_filetype['ext'] != $infopath["extension"]) {
                wp_send_json(array('status' => false , 'msg' => __('To replace a media and keep the link to this media working, it must be in the same format, ie. jpg > jpg… Thanks!','wpmf')));
            }

            if ($_FILES["wpmf_replace_file"]["error"] > 0) {
                echo "Error: " . $_FILES["wpmf_replace_file"]["error"] . "<br>";
            } else {
                $uploadpath = wp_upload_dir();
                @unlink($filepath);
                if (in_array($infopath['extension'], $allowedTypes)) {
                    if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                        foreach ($metadata['sizes'] as $size => $sizeinfo) {
                            $intermediate_file = str_replace(basename($filepath), $sizeinfo['file'], $filepath);
                            /** This filter is documented in wp-includes/functions.php */
                            $intermediate_file = apply_filters('wp_delete_file', $intermediate_file);
                            @unlink(path_join($uploadpath['basedir'], $intermediate_file));
                        }
                    }
                }

                @move_uploaded_file($_FILES["wpmf_replace_file"]["tmp_name"], $infopath['dirname'] . "/" . $infopath['basename']);
                update_post_meta($id, 'wpmf_size', filesize($infopath['dirname'] . "/" . $infopath['basename']));

                //_wp_attachment_metadata
                if (in_array($infopath['extension'], $allowedImageTypes)) {
                    $actual_sizes_array = getimagesize($filepath);
                    $metadata['width'] = $actual_sizes_array[0];
                    $metadata['height'] = $actual_sizes_array[1];
                    $this->createThumbs($filepath, $infopath['extension'], $metadata,$id);
                }
                if(isset($_FILES["wpmf_replace_file"]['size'])){
                    $size = $_FILES["wpmf_replace_file"]['size'];
                    update_post_meta($id, 'wpmf_size',$size);
                    if($size >= 1024 && $size < 1024*1024){
                        $size = ceil($size/1024) . ' KB';
                    }elseif($size >= 1024*1024){
                        $size = ceil($size/(1024*1024)) . ' MB';
                    }elseif($size < 1024){
                        $size = $size .' B';
                    }
                }else{
                    $size = '0 B';
                }

                if (in_array($infopath['extension'], $allowedImageTypes)) {
                    $metadata = wp_get_attachment_metadata($id);
                    $dimensions = $metadata['width'] .' x '.$metadata['height'];
                    wp_send_json(array('status' => true , 'size' => $size , 'dimensions' => $dimensions));
                }else{
                    wp_send_json(array('status' => true , 'size' => $size));
                }
            }
        } else {
            wp_send_json(array('status' => false , 'msg' => __('File not exist','wpmf')));
        }
    }

    /* includes styles and some scripts */
    function wpmf_enqueue_admin_scripts() {
        if (!current_user_can('upload_files'))
            return;
        wp_enqueue_script('wpmf-jquery-form', plugins_url('assets/js/jquery.form.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION);
        wp_register_script('replace-image', plugins_url('assets/js/replace-image.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION, true);
        wp_enqueue_script('replace-image');
        wp_enqueue_style('replace-style', plugins_url('assets/css/style_replace_image.css', dirname(__FILE__)), array(), WPMF_VERSION);
    }
}
