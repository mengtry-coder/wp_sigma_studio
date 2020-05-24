<?php

class Wpmf_Background_Folder {

    function __construct() {
        add_filter("attachment_fields_to_edit", array($this, "wpmf_attachment_fields_to_edit"), 10, 2);
        add_filter("attachment_fields_to_save", array($this, "wpmf_attachment_fields_to_save"), 10, 2);
    }
    
    /* add custom field background color for attachment */
    function wpmf_attachment_fields_to_edit($form_fields, $post) {
        global $pagenow;
        if ($pagenow != 'post.php') {
            $current_folder = get_the_terms($post, WPMF_TAXO);
            if(!empty($current_folder) && is_array($current_folder)){
                foreach ($current_folder as $folder){
                    if($folder->taxonomy == 'wpmf-category'){
                        $curentFolder = $folder->term_id;
                    }
                }
            }
            if (!empty($current_folder) && substr($post->post_mime_type, 0, 5) == 'image') {
                $option_bgfolder = get_option('wpmf_field_bgfolder');
                if (!empty($option_bgfolder) && !empty($current_folder) && !empty($option_bgfolder[$curentFolder]) && $option_bgfolder[$curentFolder][0] == $post->ID) {
                    $html = '<input checked type="checkbox" class="wpmf_field_bgfolder" id="attachments-' . $post->ID . '-wpmf_field_bgfolder" name="attachments[' . $post->ID . '][wpmf_field_bgfolder]">';
                } else {
                    $html = '<input type="checkbox" class="wpmf_field_bgfolder" id="attachments-' . $post->ID . '-wpmf_field_bgfolder" name="attachments[' . $post->ID . '][wpmf_field_bgfolder]">';
                }
                $form_fields['wpmf_field_bgfolder'] = array(
                    "label" => __('Folder cover', 'wpmf'),
                    "input" => "html",
                    'html' => $html
                );
            }
        }

        return $form_fields;
    }
    
    /* save option 'wpmf_field_bgfolder' when save attachment */
    function wpmf_attachment_fields_to_save($post, $attachment) {
        $option_bgfolder = get_option('wpmf_field_bgfolder');
        if (empty($option_bgfolder)) {
            $option_bgfolder = array();
        }
        $current_folder_id = $this->wpmf_get_current_folder_id();
        if (isset($attachment['wpmf_field_bgfolder']) && $attachment['wpmf_field_bgfolder'] == 'on') {
            $image_thumb = wp_get_attachment_image_src($post['ID'], 'thumbnail');
            $option_bgfolder[$current_folder_id] = array($post['ID'], $image_thumb[0]);
        } else {
            unset($option_bgfolder[$current_folder_id]);
        }

        update_option('wpmf_field_bgfolder', $option_bgfolder);
        return $post;
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

}

?>