<div class="content-box content-wpmf-general content-active">
    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_mediafolder" value="0">
            <label alt="<?php _e('Useful for frontend page builders', 'wpmf'); ?>" class="text"><?php _e('Activate WP Media Folder on frontend (public side)', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_mediafolder" name="wpmf_option_mediafolder" value="1" <?php if (isset($option_mediafolder) && $option_mediafolder == 1) echo 'checked' ?>>
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
    </div>
    
    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_countfiles" value="0">
            <label alt="<?php _e('Add the number of media that are placed directly in each folder', 'wpmf'); ?>" class="text"><?php _e('Display the media number in each folder', 'wpmf')?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_countfiles" name="wpmf_option_countfiles" value="1" <?php if (isset($option_countfiles) && $option_countfiles == 1) echo 'checked' ?>>
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
    </div>

    <?php if (current_user_can('manage_options')): ?>
        <div class="btnoption">
            <span class="btn waves-effect waves-light waves-input-wrapper" alt="<?php _e('Import current media and post categories as media folders', 'wpmf'); ?>" id="wmpfImpoBtn"><?php _e('Import WP media categories', 'wpmf') ?></span>
            <span class="spinner" style="float: left;display:none"></span>
            <span class="wpmf_info_update"><?php _e('Settings saved.', 'wpmf') ?></span>
        </div>
    <?php endif; ?>
    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_searchall" value="0">
            <label alt="<?php _e('Search through all media or only in the current folder', 'wpmf'); ?>" class="text"><?php _e('Search through all media folders', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_searchall" name="wpmf_option_searchall" value="1" <?php if (isset($option_searchall) && $option_searchall == 1) echo 'checked' ?>>
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
    </div>
</div>

<!--------------------------------------- Override image and filter ----------------------------------->

<div class="content-box content-wpmf-general">
    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_override" value="0">
            <label alt="<?php _e('Possibility to replace an existing file by another one.', 'wpmf'); ?>" class="text"><?php _e('Override file', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_override" name="wpmf_option_override" value="1" <?php if (isset($option_override) && $option_override == 1) echo 'checked' ?>>
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
    </div>

    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_duplicate" value="0">
            <label alt="<?php _e('Add a button to duplicate a media from the media manager', 'wpmf'); ?>" class="text"><?php _e('Duplicate file', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_duplicate" name="wpmf_option_duplicate" value="1" <?php if (isset($option_duplicate) && $option_duplicate == 1) echo 'checked' ?>>
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
    </div>

    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_hoverimg" value="0">
            <label alt="<?php _e('On mouse hover on an image, a large preview is displayed', 'wpmf'); ?>" class="text"><?php _e('Hover image', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_hoverimg" name="wpmf_option_hoverimg" value="1" <?php if (isset($option_hoverimg) && $option_hoverimg == 1) echo 'checked' ?>>
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
    </div>

    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_useorder" value="0">
            <label alt="<?php _e('Additional filters will be added in the media views.', 'wpmf'); ?>" class="text"><?php _e('Enable the fillter and order feature', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" name="wpmf_useorder" value="1" <?php if (isset($useorder) && $useorder == 1) echo 'checked' ?>>
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
    </div>
</div>

<!--------------------------------------- End Override image and filter ----------------------------------->

<!---------------------------------------  Fillter and order ----------------------------------->

<div class="content-box wpmf-config-gallery content-wpmf-general">
    <div class="box-select">
        <div id="wpmf_fillterdimension" class="div_list">
            <ul class="wpmf_fillterdimension">
                <li class="div_list_child accordion-section control-section control-section-default open">
                    <h3 class="accordion-section-title wpmf-section-title dimension_title" data-title="filldimension" tabindex="0"><?php _e('List default fillter size', 'wpmf') ?></h3>
                    <ul class="content_list_filldimension">
                        <?php
                        if (count($a_dimensions) > 0):
                            foreach ($a_dimensions as $a_dimension):
                                ?>
                                <li class="customize-control customize-control-select item_dimension" style="display: list-item;" data-value="<?php echo $a_dimension; ?>">

                                    <div class="pure-checkbox">
                                        <input id="<?php echo $a_dimension ?>" type="checkbox" name="dimension[]" value="<?php echo $a_dimension ?>" <?php if (in_array($a_dimension, $array_s_de) == true) echo 'checked' ?>>
                                        <label for="<?php echo $a_dimension ?>"><?php echo $a_dimension ?></label>
                                        <i class="zmdi zmdi-delete wpmf-delete" data-label="dimension" data-value="<?php echo $a_dimension; ?>" title="<?php _e('Remove dimension', 'wpmf'); ?>"></i>
                                        <i class="zmdi zmdi-edit wpmf-md-edit" data-label="dimension" data-value="<?php echo $a_dimension; ?>" title="<?php _e('Edit dimension', 'wpmf'); ?>"></i>
                                    </div>
                                </li>
                                <?php
                            endforeach;
                        endif;
                        ?>

                        <li class="customize-control customize-control-select dimension" style="display: list-item;">
                            <div style="width: 100%;float: left;">
                                <span><?php _e('Width', 'wpmf'); ?></span>
                                <input name="wpmf_width_dimension" min="0" class="small-text wpmf_width_dimension" type="number">
                                <span><?php _e('Height', 'wpmf'); ?></span>
                                <input name="wpmf_height_dimension" min="0" class="small-text wpmf_height_dimension" type="number">
                            </div>
                            <span><?php _e('(unit : px)', 'wpmf'); ?></span>
                        </li>

                        <li style="display: list-item;margin:10px 0px 0px 0px">
                            <span name="add_dimension" id="add_dimension" class="button add_dimension"><?php _e('Add new size', 'wpmf'); ?></span>
                            <span name="edit_dimension" data-label="dimension" id="edit_dimension" class="button wpmfedit edit_dimension" style="display: none;"><?php _e('Save', 'wpmf'); ?></span>
                            <span id="can_dimension" class="button wpmf_can" data-label="dimension" style="display: none;"><?php _e('Cancel', 'wpmf'); ?></span>
                        </li>
                    </ul>
                    <p class="description"><?php _e('Image dimension filtering available in filter. Display image with a dimension and above.', 'wpmf'); ?></p>
                </li>
            </ul>
        </div>

        <div id="wpmf_fillterweights" class="div_list">
            <ul class="wpmf_fillterweight">
                <li class="div_list_child accordion-section control-section control-section-default open">
                    <h3 class="accordion-section-title wpmf-section-title sizes_title" data-title="fillweight" tabindex="0"><?php _e('List default fillter weight', 'wpmf') ?></h3>
                    <ul class="content_list_fillweight">
                        <?php
                        if (count($a_weights) > 0):
                            foreach ($a_weights as $a_weight):
                                $labels = explode('-', $a_weight[0]);
                                if ($a_weight[1] == 'kB') {
                                    $label = ($labels[0] / 1024) . ' kB-' . ($labels[1] / 1024) . ' kB';
                                } else {
                                    $label = ($labels[0] / (1024 * 1024)) . ' MB-' . ($labels[1] / (1024 * 1024)) . ' MB';
                                }
                                ?>

                                <li class="customize-control customize-control-select item_weight" style="display: list-item;" data-value="<?php echo $a_weight[0]; ?>" data-unit="<?php echo $a_weight[1]; ?>">

                                    <div class="pure-checkbox">
                                        <input id="<?php echo $a_weight[0] . ',' . $a_weight[1] ?>" type="checkbox" name="weight[]" value="<?php echo $a_weight[0] . ',' . $a_weight[1] ?>" data-unit="<?php echo $a_weight[1]; ?>" <?php if (in_array($a_weight, $array_s_we) == true) echo 'checked' ?>>
                                        <label for="<?php echo $a_weight[0] . ',' . $a_weight[1] ?>"><?php echo $label ?></label>
                                        <i class="zmdi zmdi-delete wpmf-delete" data-label="weight" data-value="<?php echo $a_weight[0]; ?>" data-unit="<?php echo $a_weight[1]; ?>" title="<?php _e('Remove weight', 'wpmf'); ?>"></i>
                                        <i class="zmdi zmdi-edit wpmf-md-edit" data-label="weight" data-value="<?php echo $a_weight[0]; ?>" data-unit="<?php echo $a_weight[1]; ?>" title="<?php _e('Edit weight', 'wpmf'); ?>"></i>
                                    </div>
                                </li>

                                
                                <?php
                            endforeach;
                        endif;
                        ?>

                        <li class="customize-control customize-control-select weight" style="display: list-item;">
                            <div style="width: 100%;float: left;">
                                <span><?php _e('Min', 'wpmf'); ?></span>
                                <input name="wpmf_min_weight" min="0" class="small-text wpmf_min_weight" type="number">
                                <span><?php _e('Max', 'wpmf'); ?></span>
                                <input name="wpmf_max_weight" min="0" class="small-text wpmf_max_weight" type="number">
                            </div>
                            <span style="margin-top: 10px;float: left;"><?php _e('Unit :', 'wpmf'); ?>
                                <select class="wpmfunit" data-label="weight">
                                    <option value="kB"><?php _e('kB', 'wpmf'); ?></option>
                                    <option value="MB"><?php _e('MB', 'wpmf'); ?></option>
                                </select>
                            </span>

                        </li>

                        <li style="display: list-item;margin:10px 0px 0px 0px;float: left;">
                            <span name="add_weight" id="add_weight" class="button add_weight"><?php _e('Add weight', 'wpmf'); ?></span>
                            <span name="edit_weight" data-label="weight" id="edit_weight" class="button wpmfedit edit_weight" style="display: none;"><?php _e('Save', 'wpmf'); ?></span>
                            <span id="can_dimension" class="button wpmf_can" data-label="weight" style="display: none"><?php _e('Cancel', 'wpmf'); ?></span>
                        </li>
                    </ul>
                    <p class="description"><?php _e('Select weight range which you would like to display in media library filter', 'wpmf'); ?></p>
                </li>
            </ul>
        </div>
    </div>
</div>

<!--------------------------------------- End Fillter and order ----------------------------------->

<!--------------------------------------- End advanced params ----------------------------------->
