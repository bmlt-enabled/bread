<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="custom-content-div" class="postbox">
                <?PHP $title = '
                <p>The Custom Content can be customized with text, graphics, tables, shortcodes, ect.</p>
                <p><strong>Default Font Size</strong> can be changed for specific text in the editor.</p>
                <p><strong>Add Media</strong> button - upload and add graphics.</p>
                <p><strong>Meeting List Shortcodes</strong> dropdown - insert variable data.</p>
                <p><i>The Custom Content will print immediately after the meetings in the meeting list.</i></p>
                ';
                ?>
                <h3 class="hndle">Custom Content<span title='<?PHP echo $title; ?>' class="bottom-tooltip"></span></h3>
                <div class="inside">
                    <p>Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="custom_section_font_size" name="custom_section_font_size" value="<?php echo $this->options['custom_section_font_size'] ;?>" />&nbsp;&nbsp;
                    Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="custom_section_line_height" type="text" maxlength="3" size="3" name="custom_section_line_height" value="<?php echo $this->options['custom_section_line_height'] ;?>" /></p>
                    <div style="margin-top:15px; margin-bottom:20px; max-width:100%; width:100%;">
                        <?php
                        $editor_id = "custom_section_content";
                        $settings    = array (
                            'tabindex'      => FALSE,
                            'editor_height'	=> 500,
                            'resize'        => TRUE,
                            "media_buttons"	=> TRUE,
                            "drag_drop_upload" => TRUE,
                            "editor_css"	=> "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
                            "teeny"			=> FALSE,
                            'quicktags'		=> TRUE,
                            'wpautop'		=> FALSE,
                            'textarea_name' => $editor_id,
                            'tinymce'=> array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'front_page_button')
                        );
                        wp_editor( stripslashes($this->options['custom_section_content']), $editor_id, $settings );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="submit" value="Save Changes" id="bmltmeetinglistsave5" name="bmltmeetinglistsave" class="button-primary" />
    <?php echo '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
    <div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
</div>
