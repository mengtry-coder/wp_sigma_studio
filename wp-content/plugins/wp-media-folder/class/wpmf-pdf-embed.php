<?php

class Wpmf_Pdf_Embed {

    function __construct() {
        add_action('wp_enqueue_media', array($this, 'wpmf_loadscript'));
        add_filter('media_send_to_editor', array($this, 'add_image_files'), 10, 3);
        add_action('wp_enqueue_scripts', array($this, 'wpmf_load_style_script'));
        add_filter("attachment_fields_to_edit", array($this, "wpmf_attachment_fields_to_edit"), 10, 2);
        add_filter("attachment_fields_to_save", array($this, "wpmf_attachment_fields_to_save"), 10, 2);
    }

    function wpmf_load_style_script() {
        global $post;
        if (!empty($post)) {
            if (strpos($post->post_content, 'wpmf-pdfemb-viewer') !== false && strpos($post->post_content, 'data-wpmf_pdf_embed="embed"') !== false) {
                wp_enqueue_script('wpmf_embed_pdf_js', plugins_url('assets/js/pdf-embed/all-pdfemb-basic.min.js', dirname(__FILE__)), array('jquery'));
                wp_localize_script('wpmf_embed_pdf_js', 'wpmf_pdfemb_trans', $this->get_translation_array());
                wp_enqueue_script('wpmf_compat_js', plugins_url('assets/js/pdf-embed/compatibility.js', dirname(__FILE__)), array('jquery'));
                wp_enqueue_script('wpmf_pdf_js', plugins_url('assets/js/pdf-embed/pdf.js', dirname(__FILE__)), array('wpmf_compat_js'));
                wp_enqueue_style('pdfemb_embed_pdf_css', plugins_url('assets/css/pdfemb-embed-pdf.css', dirname(__FILE__)));
            }
        }
    }

    function get_translation_array() {
        $array = array(
            'worker_src' => plugins_url('assets/js/pdf-embed/pdf.worker.min.js', dirname(__FILE__)),
            'cmap_url' => plugins_url('assets/js/pdf-embed/cmaps/', dirname(__FILE__)),
            'objectL10n' =>
            array(
                'loading' => 'Loading...',
                'page' => 'Page',
                'zoom' => 'Zoom',
                'prev' => 'Previous page',
                'next' => 'Next page',
                'zoomin' => 'Zoom In',
                'zoomout' => 'Zoom Out',
                'secure' => 'Secure',
                'download' => 'Download PDF',
                'fullscreen' => 'Full Screen',
                'domainerror' => 'Error: URL to the PDF file must be on exactly the same domain as the current web page.',
                'clickhereinfo' => 'Click here for more info',
                'widthheightinvalid' => 'PDF page width or height are invalid',
                'viewinfullscreen' => 'View in Full Screen',
                'poweredby' => 1));
        return $array;
    }

    /* Custom html file after insert to editor */

    function add_image_files($html, $id, $attachment) {
        $post = get_post($id);
        $mimetype = explode("/", $post->post_mime_type);
        $pdf_embed = get_post_meta($id, 'wpmf_pdf_embed', true);
        $target = get_post_meta($id, '_gallery_link_target', true);
        if ($mimetype[1] == 'pdf') {
            if (isset($pdf_embed) && $pdf_embed == 'embed') {
                $doc = new DOMDocument();
                libxml_use_internal_errors(true);
                @$doc->loadHtml($html);
                $tags = $doc->getElementsByTagName('a');
                if($tags->length > 0){
                    if(!empty($tags)){
                        $class = $tags->item(0)->getAttribute('class');
                        if (!empty($class)) {
                            $newclass = $class . ' wpmf-pdfemb-viewer';
                        } else {
                            $newclass = 'wpmf-pdfemb-viewer';
                        }
                        $tags->item(0)->setAttribute('data-wpmf_pdf_embed',$pdf_embed);
                        $tags->item(0)->setAttribute('target',$target);
                        $tags->item(0)->setAttribute('class', $newclass);
                        $html = $doc->saveHtml();
                    }
                }
            } else {
                $singlefile = get_option('wpmf_option_singlefile');
                if (isset($singlefile) && $singlefile == 1) {
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

                    $type = wp_check_filetype($post->guid);
                    $ext = $type['ext'];
                    $html = '<span class="wpmf_mce-wrap" data-file="' . $id . '" style="overflow: hidden;">';
                    $html .= '<a class="wpmf-defile wpmf_mce-single-child" href="' . $post->guid . '" data-id="' . $id . '" target="'.$target.'">';
                    $html .= '<span class="wpmf_mce-single-child" style="font-weight: bold;">' . $post->post_title . '</span><br>';
                    $html .= '<span class="wpmf_mce-single-child" style="font-weight: normal;font-size: 0.8em;"><b class="wpmf_mce-single-child">Size : </b>' . $size . '<b class="wpmf_mce-single-child"> Format : </b>' . strtoupper($ext) . '</span>';
                    $html .= '</a>';
                    $html .= '</span>';
                }else{
                    $doc = new DOMDocument();
                    libxml_use_internal_errors(true);
                    @$doc->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                    $tags = $doc->getElementsByTagName('a');
                    if($tags->length > 0){
                        if(!empty($tags)){
                            $tags->item(0)->setAttribute('target',$target);
                            $html = $doc->saveHtml();
                        }
                    }
                    //$html = preg_replace('/(<a\b[^><]*)>/i', '$1 target="'.$target.'">', $html);
                }
            }
        }
        return $html;
    }
    
    public function wpmf_admin_footer_pdf_embed(){
        ?>
        <script type="text/javascript">
        jQuery(function ($) {

            if (wp && wp.media && wp.media.events) {
                wp.media.events.on( 'editor:image-edit', function (data) {
                    data.metadata.wpmf_pdf_embed = data.editor.dom.getAttrib( data.image, 'data-wpmf_pdf_embed' );
                } );
            }
        });
        </script>
        <?php
    }
    
    public function wpmf_loadscript() {
        add_action('admin_footer', array($this, 'wpmf_admin_footer_pdf_embed'),11);
        add_action('wp_footer', array($this, 'wpmf_admin_footer_pdf_embed'),11);
    }

    public function wpmf_attachment_fields_to_edit($form_fields, $post) {
        $infosfile = wp_check_filetype($post->guid);
        if(!empty($infosfile['ext']) && $infosfile['ext'] == 'pdf'){
            $value = get_post_meta($post->ID, 'wpmf_pdf_embed', true);
            if(empty($value)) $value = 'large';
            $embed = array(
                'link' => __('Off', 'wpmf'),
                'embed' => __('On', 'wpmf'),
            );
            $option = '';
            foreach ($embed as $k => $v) {
                if($value == $k){
                    $option .= '<option selected value="' . $k . '">' . $v . '</option>';
                }else{
                    $option .= '<option value="' . $k . '">' . $v . '</option>';
                }

            }
            $form_fields['wpmf_pdf_embed'] = array(
                'label' => __('PDF Embed', 'wpmf'),
                'input' => 'html',
                'html' => '
                            <select name="attachments[' . $post->ID . '][wpmf_pdf_embed]" id="attachments[' . $post->ID . '][wpmf_pdf_embed]">
                                    '.$option.'
                            </select>'
            );
        }
        
        return $form_fields;
    }
    
    function wpmf_attachment_fields_to_save($post, $attachment) {
        if (isset($attachment['wpmf_pdf_embed'])) {
            update_post_meta($post['ID'], 'wpmf_pdf_embed', $attachment['wpmf_pdf_embed']);
        }
        return $post;
    }
    

}
