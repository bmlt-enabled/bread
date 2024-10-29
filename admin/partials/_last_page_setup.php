<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="lastpagecontentdiv" class="postbox">
                <?php $title = '
                <p class="bmlt-heading-h2">Last Page Content<p>
                <p>Any text or graphics can be entered into this section.
                ';
                ?>
                <h3 class="hndle">Last Page Content<span title='<?php echo $title; ?>' class="tooltip"></span></h3>
                    <div class="inside">
                    <p>Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="last_page_font_size" name="last_page_font_size" value="<?php echo Bread::getOption('last_page_font_size');?>" />&nbsp;&nbsp;
                    Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="last_page_line_height" type="text" maxlength="3" size="3" name="last_page_line_height" value="<?php echo Bread::getOption('last_page_line_height');?>" /></p>
                    <div style="margin-top:15px; margin-bottom:20px; max-width:100%; width:100%;">
                        <?php
                        $editor_id = "last_page_content";
                        $settings    = array (
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
                            'tinymce'=> array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'front_page_button')
                        );
                        wp_editor(stripslashes(Bread::getOption('last_page_content')), $editor_id, $settings);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($this->admin->current_user_can_modify()) {
        echo '
    <input type="submit" value="Save Changes" id="bmltmeetinglistsave1" name="bmltmeetinglistsave" class="button-primary" />
 ';
    }?>
    <?php echo '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list='.$this->admin->loaded_setting.'">Generate Meeting List</a></p>'; ?>
    <div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
    <br class="clear">
</div>
