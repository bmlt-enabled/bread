<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="frontpagecontentdiv" class="postbox">
                <?php $title = '
                <p>The Front Page can be customized with text, graphics, tables, shortcodes, ect.</p>
                <p><strong>Add Media</strong> button - upload and add graphics.</p>
                <p><strong>Meeting List Shortcodes</strong> dropdown - insert custom data.</p>
                <p><strong>Default Font Size</strong> can be changed for specific text.</p>
                ';
                ?>
                <h3 class="hndle">Front Page Content<span title='<?php echo $title; ?>' class="tooltip"></span></h3>
                <div class="inside">
                    <p>Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="front_page_font_size" name="front_page_font_size" value="<?php echo Bread::getOptionForDisplay('front_page_font_size', '10'); ?>" />&nbsp;&nbsp;
                        Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="front_page_line_height" type="text" maxlength="3" size="3" name="front_page_line_height" value="<?php echo Bread::getOptionForDisplay('front_page_line_height', '1.0'); ?>" /></p>
                    <div style="margin-top:15px; margin-bottom:20px; max-width:100%; width:100%;">
                        <?php
                        $editor_id = "front_page_content";
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
                        wp_editor(stripslashes(str_replace("http://", Bread::getProtocol(), Bread::getOption('front_page_content'))), $editor_id, $settings);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>