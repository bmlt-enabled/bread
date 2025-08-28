<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="custom-content-div" class="postbox">
                <div style="display:none;">
                    <div id="customsection-tooltip-content"><?php _e("
                        <p>The Custom Content can be customized with text, graphics, tables, shortcodes, etc.</p>
                        <p><strong>Default Font Size</strong> can be changed for specific text in the editor.</p>
                        <p><strong>Add Media</strong> button - upload and add graphics.</p>
                        <p><strong>Meeting List Shortcodes</strong> dropdown - insert variable data.</p>
                        <p><i>The Custom Content will print immediately after the meetings in the meeting list.</i></p>", 'bread-domain')?>
                    </div>
                </div>
                <h3 class="hndle"><?php _e('Custom Section Content', 'bread-domain') ?><span data-tooltip-content="#customsection-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <p><?php _e('Default Font Size: ', 'bread-domain') ?><input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="custom_section_font_size" name="custom_section_font_size" value="<?php echo esc_html($this->bread->getOptionForDisplay('custom_section_font_size', '9')); ?>" />&nbsp;&nbsp;
                        <?php _e('Line Height: ', 'bread-domain') ?><input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="custom_section_line_height" type="text" maxlength="3" size="3" name="custom_section_line_height" value="<?php echo esc_attr($this->bread->getOptionForDisplay('custom_section_line_height', '1.0')); ?>" /></p>
                    <div style="margin-top:15px; margin-bottom:20px; max-width:100%; width:100%;">
                        <?php
                        $editor_id = "custom_section_content";
                        $settings    = array(
                            'tabindex'      => false,
                            'editor_height' => 500,
                            'resize'        => true,
                            "media_buttons" => true,
                            "drag_drop_upload" => true,
                            "editor_css"    => "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
                            "teeny"         => false,
                            'quicktags'     => true,
                            'wpautop'       => false,
                            'textarea_name' => $editor_id,
                            'tinymce' => array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'front_page_button')
                        );
                        wp_editor(stripslashes(str_replace("http://", $this->bread->getProtocol(), $this->bread->getOption('custom_section_content'))), $editor_id, $settings);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>