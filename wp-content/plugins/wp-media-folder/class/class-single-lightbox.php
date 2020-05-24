<?php

class Wpmf_Single_Lightbox {

    function __construct() {
        add_action('wp_enqueue_media', array($this, 'wpmf_loadscript'));
        add_action('wp_enqueue_scripts', array($this, 'wpmf_enqueue_script'));
        add_filter("attachment_fields_to_edit", array($this, "wpmf_attachment_fields_to_edit"), 10, 2);
        add_filter("attachment_fields_to_save", array($this, "wpmf_attachment_fields_to_save"), 10, 2);
        add_filter('image_send_to_editor', array($this,'wpmf_imagelightbox_send_to_editor'), 10, 8);
        add_action('print_media_templates', array($this, 'wpmf_print_media_templates'));
        add_action('wp_ajax_wpmf_get_thumb_image', array($this, 'wpmf_get_thumb_image'));
    }
    
    public function wpmf_admin_footer_imagelightbox(){
        ?>
        <script type="text/javascript">
        jQuery(function ($) {

            if (wp && wp.media && wp.media.events) {
                wp.media.events.on( 'editor:image-edit', function (data) {
                    data.metadata.wpmf_image_lightbox = data.editor.dom.getAttrib( data.image, 'data-wpmf_image_lightbox' );
                    data.metadata.wpmf_size_lightbox = data.editor.dom.getAttrib( data.image, 'data-wpmf_size_lightbox' );
                    data.metadata.wpmflightbox = data.editor.dom.getAttrib( data.image, 'data-wpmflightbox' );
                } );
                wp.media.events.on( 'editor:image-update', function (data) {
                    if(data.metadata.link == 'file' && data.metadata.wpmf_size_lightbox != 'none'){
                        data.editor.dom.setAttrib( data.image, 'data-wpmflightbox', 1 );
                    }else{
                        data.editor.dom.setAttrib( data.image, 'data-wpmflightbox', 0 );
                    }
                    $.ajax({
                        url : ajaxurl,
                        method : 'POST',
                        dataType : 'json',
                        data : {
                            action: "wpmf_get_thumb_image",
                            attachment_id : data.metadata.attachment_id,
                            size : data.metadata.wpmf_size_lightbox
                        },
                        success : function(res){
                            if(res.status){
                                data.editor.dom.setAttrib( data.image, 'data-wpmf_image_lightbox', res.url_thumb );
                                data.editor.dom.setAttrib( data.image, 'data-wpmf_size_lightbox', data.metadata.wpmf_size_lightbox );
                            }
                        }
                    });
                } );
            }
        });
        </script>
        <?php
    }


    public function wpmf_loadscript() {
        wp_enqueue_script( 'wpmf-singleimage-lightbox', plugins_url( '/assets/js/single_image_lightbox/image_lightbox.js',dirname(__FILE__)), array ( 'jquery' ), WPMF_VERSION );
        add_action('admin_footer', array($this, 'wpmf_admin_footer_imagelightbox'),11);
        add_action('wp_footer', array($this, 'wpmf_admin_footer_imagelightbox'),11);
    }
    
    public function wpmf_enqueue_script() {
        wp_enqueue_style('wpmf-material-design-iconic-font.min', plugins_url('/assets/css/material-design-iconic-font.min.css', dirname(__FILE__)), array(), WPMF_VERSION);
        wp_enqueue_script( 'wpmf-gallery-popup', plugins_url( '/assets/js/display-gallery/jquery.magnific-popup.min.js',dirname(__FILE__)), array ( 'jquery' ), '0.9.9', true );
        wp_enqueue_script( 'wpmf-singleimage-lightbox', plugins_url( '/assets/js/single_image_lightbox/single_image_lightbox.js',dirname(__FILE__)), array ( 'jquery' ), WPMF_VERSION );
        wp_enqueue_style( 'wpmf-singleimage-popup-style', plugins_url('/assets/css/display-gallery/magnific-popup.css', dirname(__FILE__)), array( ), '0.9.9' );
    }
    
    public function wpmf_get_thumb_image(){
        if(isset($_POST['attachment_id']) && isset($_POST['size'])){
            $image_src = wp_get_attachment_image_src($_POST['attachment_id'],$_POST['size']);
            $url_image = $image_src[0];
            wp_send_json(array('status' => true , 'url_thumb' => $url_image));
        }
        wp_send_json(array('status' => false));
    }
            
    function wpmf_print_media_templates() {
        $display_types = array(
            'default' => __('Default', 'wpmf'),
            'masonry' => __('Masonry', 'wpmf'),
            'portfolio' => __('Portfolio', 'wpmf'),
            'slider' => __('Slider', 'wpmf'),
        );
        
        ?>

        <script type="text/html" id="tmpl-image-wpmf">
            <label class="setting wpmf_size_lightbox">
                <span><?php _e('Lightbox size', 'wpmf'); ?></span>
                <select class="wpmf_size_lightbox" name="wpmf_size_lightbox" data-setting="wpmf_size_lightbox">
                    <option value="none"><?php _e('None','wpmf') ?></option>
                    <?php
                    $sizes = apply_filters('image_size_names_choose', array(
                        'none' => __('None', 'wpmf'),
                        'thumbnail' => __('Thumbnail', 'wpmf'),
                        'medium' => __('Medium', 'wpmf'),
                        'large' => __('Large', 'wpmf'),
                        'full' => __('Full Size', 'wpmf'),
                            ));
                    ?>

        <?php foreach ($sizes as $k => $v) : ?>
                <option value="<?php echo $k ?>"><?php echo $v ?></option>
        <?php endforeach; ?>

                </select>
            </label>
        </script>
        <?php
    }
    
    
    function wpmf_imagelightbox_send_to_editor($html, $id, $caption, $title, $align, $url, $size, $alt = ''){
        $url_attachment = wp_get_attachment_url($id);
        $size = get_post_meta($id, 'wpmf_image_lightbox', true);
        if(empty($size)) $size = 'large';
        
        $image_src = wp_get_attachment_image_src($id,$size);
        $url_image = $image_src[0];
        $attr = 'data-wpmf_image_lightbox';
        $attr_size = 'data-wpmf_size_lightbox';
        
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        @$dom->loadHtml($html);
        $tags = $dom->getElementsByTagName('img');
        foreach($tags as $tag){
            $tag->setAttribute('data-wpmf_image_lightbox',$url_image);
            $tag->setAttribute('data-wpmf_size_lightbox',$size);
            if ($url_attachment != $url || $size=='none') {
                $tag->setAttribute('data-wpmflightbox',0);
            }else{
                $tag->setAttribute('data-wpmflightbox',1);
            }
        }
        $html = $dom->saveHTML();
        return $html;
    }

    public function wpmf_attachment_fields_to_edit($form_fields, $post) {
        $value = get_post_meta($post->ID, 'wpmf_image_lightbox', true);
        if(empty($value)) $value = 'large';
        $sizes = apply_filters('image_size_names_choose', array(
            'thumbnail' => __('Thumbnail', 'wpmf'),
            'medium' => __('Medium', 'wpmf'),
            'large' => __('Large', 'wpmf'),
            'full' => __('Full Size', 'wpmf'),
        ));
        $option = '';
        $option .= '<option value="none">' . __('None','wpmf') . '</option>';
        foreach ($sizes as $k => $v) {
            if($value == $k){
                $option .= '<option selected value="' . $k . '">' . $v . '</option>';
            }else{
                $option .= '<option value="' . $k . '">' . $v . '</option>';
            }
            
        }
        $form_fields['wpmf_image_lightbox'] = array(
            'label' => __('Lightbox size', 'wpmf'),
            'input' => 'html',
            'html' => '
                        <select name="attachments[' . $post->ID . '][wpmf_image_lightbox]" id="attachments[' . $post->ID . '][wpmf_image_lightbox]">
                                '.$option.'
                        </select>'
        );
        
        return $form_fields;
    }
    
    function wpmf_attachment_fields_to_save($post, $attachment) {
        if (isset($attachment['wpmf_image_lightbox'])) {
            update_post_meta($post['ID'], 'wpmf_image_lightbox', $attachment['wpmf_image_lightbox']);
        }
        return $post;
    }
}

?>