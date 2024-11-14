<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="meetingsheaderdiv" class="postbox">
                <?php $title = '
                <p>Customize the Meeting Group Header to your specification.</p>
                <p>The Meeting Group Header will contain the data from Group By.</p>
                ';
                ?>
                <h3 class="hndle">Meeting Group [Column] Header<span title='<?php echo $title; ?>' class="tooltip"></span></h3>
                <div class="inside">
                    <div style="margin-bottom: 10px; padding:0;" id="accordion2">
                        <h3 class="help-accordian">Instructions</h3>
                        <div class="videocontent">
                            <video id="my_video_1" style="width:100%;height:100%;" controls width="100%" height="100%" preload="auto">
                                <source src="https://nameetinglist.org/videos/meeting_group_header.mp4" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        </div>
                    </div>
                    <div>
                        <input name="suppress_heading" value="0" type="hidden">
                        <label for="suppress_heading">Suppress Heading: </label><input type="checkbox" name="suppress_heading" id="suppress_heading" value="1" <?php echo (Bread::getOption('suppress_heading') == '1' ? 'checked' : '') ?>>
                        <table id="header_options_div">
                            <tr>
                                <td style="padding-right: 10px;">Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="header_font_size" name="header_font_size" value="<?php echo Bread::getOption('header_font_size'); ?>" /></td>
                                <td style="padding-right: 10px;">
                                    <div class="theme" id="sp-light">
                                        <label for="header_text_color">Text Color:</label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="header_text_color" name="header_text_color" value="<?php echo Bread::getOption('header_text_color'); ?>" />
                                    </div>
                                </td>
                                <td style="padding-right: 10px;">
                                    <div class="theme" id="sp-light">
                                        <label for="header_background_color">Background Color:</label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="header_background_color" name="header_background_color" value="<?php echo Bread::getOption('header_background_color'); ?>" />
                                    </div>
                                </td>
                                <td style="padding-right: 10px;">
                                    <input name="header_uppercase" value="0" type="hidden">
                                <td><label for="header_uppercase">Uppercase: </label><input type="checkbox" name="header_uppercase" value="1" <?php echo (Bread::getOption('header_uppercase') == '1' ? 'checked' : '') ?>></td>
                                <td style="padding-right: 10px;">
                                    <input name="header_bold" value="0" type="hidden">
                                <td><label for="header_bold">Bold: </label><input type="checkbox" name="header_bold" value="1" <?php echo (Bread::getOption('header_bold') == '1' ? 'checked' : '') ?>></td>
                                <td style="padding-right: 10px;">
                                    <input name="cont_header_shown" value="0" type="hidden">
                                <td><label for="cont_header_shown">Display (Cont) Header: </label><input type="checkbox" name="cont_header_shown" value="1" <?php echo (Bread::getOption('cont_header_shown') == '1' ? 'checked' : '') ?>></td>
                            </tr>
                        </table>
                    </div>
                    <p>
                    <div class="group_by" style="margin-right: 10px; display: inline;">
                        <label for="meeting_sort">Group Meetings By: </label>
                        <select id="meeting_sort" name="meeting_sort">
                            <option <?php echo (Bread::getOption('meeting_sort') == 'day' ? 'selected="selected"' : '') ?> value="day">Weekday</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'city' ? 'selected="selected"' : '') ?> value="city">City</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'group' ? 'selected="selected"' : '') ?> value="group">Group</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'county' ? 'selected="selected"' : '') ?> value="county">County</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'borough' ? 'selected="selected"' : '') ?> value="borough">Borough</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'borough_county' ? 'selected="selected"' : '') ?> value="borough_county">Borough+County</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'neighborhood_city' ? 'selected="selected"' : '') ?> value="neighborhood_city">Neighborhood+City</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'state' ? 'selected="selected"' : '') ?> value="state">State+City</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'weekday_area' ? 'selected="selected"' : '') ?> value="weekday_area">Weekday+Area</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'weekday_city' ? 'selected="selected"' : '') ?> value="weekday_city">Weekday+City</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'weekday_county' ? 'selected="selected"' : '') ?> value="weekday_county">Weekday+County</option>
                            <option <?php echo (Bread::getOption('meeting_sort') == 'user_defined' ? 'selected="selected"' : '') ?> value="user_defined">User Defined</option>
                        </select>
                    </div>
                    <div class="borough_by_suffix">

                        <p>
                            <label for="borough_suffix">Borough Suffix: </label>
                            <input class="borough-by-suffix" id="borough_suffix" type="text" name="borough_suffix" value="<?php echo Bread::getOption('borough_suffix'); ?>" />

                        </p>

                    </div>
                    <div class="county_by_suffix">

                        <p>
                            <label for="county_suffix">County Suffix: </label>
                            <input class="county-by-suffix" id="county_suffix" type="text" name="county_suffix" value="<?php echo Bread::getOption('county_suffix'); ?>" />

                        </p>
                    </div>

                    <div class="neighborhood_by_suffix">

                        <p>
                            <label for="neighborhood_suffix">Neighborhood Suffix: </label>
                            <input class="neighborhood-by-suffix" id="neighborhood_suffix" type="text" name="neighborhood_suffix" value="<?php echo Bread::getOption('neighborhood_suffix'); ?>" />

                        </p>

                    </div>
                    <div class="city_by_suffix">

                        <p>
                            <label for="county_suffix">City Suffix: </label>
                            <input class="city-by-suffix" id="city_suffix" type="text" name="city_suffix" value="<?php echo Bread::getOption('city_suffix'); ?>" />

                        </p>
                    </div>
                    <div class="user_defined_headings">
                        <p>
                            <label for="main_grouping">Main Grouping: </label>
                            <select id="main_grouping" name="main_grouping">
                                <option <?php echo (Bread::getOption('main_grouping') == 'day' ? 'selected="selected"' : '') ?> value="day">Weekday</option>
                                <option <?php echo (Bread::getOption('main_grouping') == 'location_municipality' ? 'selected="selected"' : '') ?> value="location_municipality">City</option>
                                <option <?php echo (Bread::getOption('main_grouping') == 'location_neighborhood' ? 'selected="selected"' : '') ?> value="location_neighborhood">Neighborhood</option>
                                <option <?php echo (Bread::getOption('main_grouping') == 'group' ? 'selected="selected"' : '') ?> value="group">Group</option>
                                <option <?php echo (Bread::getOption('main_grouping') == 'location_sub_province' ? 'selected="selected"' : '') ?> value="location_sub_province">County</option>
                                <option <?php echo (Bread::getOption('main_grouping') == 'location_city_subsection' ? 'selected="selected"' : '') ?> value="location_city_subsection">Borough</option>
                                <option <?php echo (Bread::getOption('main_grouping') == 'location_province' ? 'selected="selected"' : '') ?> value="location_province">State</option>
                                <?php
                                $fks = Bread_Bmlt::get_nonstandard_fieldkeys();
                                foreach ($fks as $fk) {
                                    $selected = '';
                                    if ($fk['key'] == Bread::getOption('main_grouping')) {
                                        $selected = ' selected="selected"';
                                    }
                                    echo '<option value="' . $fk['key'] . '" ' . $selected . '>' . $fk['description'] . '</option>';
                                }
                                ?>
                            </select>
                            <label for="subgrouping">Sub-Grouping: </label>
                            <select id="subgrouping" name="subgrouping">
                                <option <?php echo (empty(Bread::getOption('subgrouping')) ? 'selected="selected"' : '') ?> value="">None</option>
                                <option <?php echo (Bread::getOption('subgrouping') == 'day' ? 'selected="selected"' : '') ?> value="day">Weekday</option>
                                <option <?php echo (Bread::getOption('subgrouping') == 'location_municipality' ? 'selected="selected"' : '') ?> value="location_municipality">City</option>
                                <option <?php echo (Bread::getOption('subgrouping') == 'location_neighborhood' ? 'selected="selected"' : '') ?> value="location_neighborhood">Neighborhood</option>
                                <option <?php echo (Bread::getOption('subgrouping') == 'group' ? 'selected="selected"' : '') ?> value="group">Group</option>
                                <option <?php echo (Bread::getOption('subgrouping') == 'location_sub_province' ? 'selected="selected"' : '') ?> value="location_sub_province">County</option>
                                <option <?php echo (Bread::getOption('subgrouping') == 'location_city_subsection' ? 'selected="selected"' : '') ?> value="location_city_subsection">Borough</option>
                                <option <?php echo (Bread::getOption('subgrouping') == 'location_province' ? 'selected="selected"' : '') ?> value="location_province">State</option>
                                <?php
                                foreach ($fks as $fk) {
                                    $selected = '';
                                    if ($fk['key'] == Bread::getOption('subgrouping')) {
                                        $selected = ' selected="selected"';
                                    }
                                    echo '<option value="' . $fk['key'] . '" ' . $selected . '>' . $fk['description'] . '</option>';
                                }
                                ?>
                            </select>
                        </p>
                    </div>
                    <div class="show_subheader">
                        <p>
                            <label for="sub_header_shown">Display Subgrouping: </label>
                            <select name="sub_header_shown">
                                <option value="none" <?php echo (Bread::getOption('sub_header_shown') == 'none' ? 'selected' : '') ?>>
                                    No header for subgroups
                                </option>
                                <option value="display" <?php echo (Bread::getOption('sub_header_shown') == 'display' ? 'selected' : '') ?>>
                                    Display each subgroup with its own header
                                </option>
                                <option value="combined" <?php echo (Bread::getOption('sub_header_shown') == 'combined' ? 'selected' : '') ?>>
                                    Combine main and subgroup into a single header
                                </option>
                            </select>
                        </p>
                    </div>
                    <div class="weekday_language_div">
                        <label for="weekday_language">Weekday Language: </label>
                        <select name="weekday_language">
                            <?php
                            foreach (Bread::getTranslateTable() as $key => $value) {
                                if (Bread::getOption('weekday_language') == $key || Bread::getOption('weekday_language') == '') {
                                    echo "<option value=\"$key\" selected=\"selected\">" . $value['LANG_NAME'] . "</option>";
                                } else {
                                    echo "<option value=\"$key\">" . $value['LANG_NAME'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="weekday_start_div">
                        <label for="weekday_start">Weekday Start: </label>
                        <select name="weekday_start">
                            <?php
                            for ($d = 1; $d <= 7; $d++) {
                                if (Bread::getOption('weekday_start') == $d || Bread::getOption('weekday_start') == '') {
                                    echo "<option value=\"$d\" selected=\"selected\">" . Bread::getday($d, false, Bread::getOption('weekday_language')) . "</option>";
                                } else {
                                    echo "<option value=\"$d\">" . Bread::getday($d, false, Bread::getOption('weekday_language')) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php if (Bread::getOption('page_fold') == 'half' || Bread::getOption('page_fold') == 'full') {
                        ?>
                        <div class="meeting1_footer_div">
                            <label for="meeting1_footer">Custom Footer: </label>
                            <input name="meeting1_footer" type="text" size="50" value="<?php echo Bread::getOption('meeting1_footer'); ?>">
                        </div>
                    <?php }
                    ?><p>
                </div>
            </div>
            <div id="custommeetingtemplatediv" class="postbox">
                <?php $title = '
                <div style="width:550px; margin-bottom:20px;">
                <p>The <strong>Meeting Template</strong> is a powerful and flexible method for customizing meetings using
                HTML markup and BMLT field names.  The template is set-up once and never needs to be messed
                with again.  Note: When changes are made to the Default Font Size or Line Height, the template
                may need to be adjusted to reflect those changes.</p>
                <p>Sample templates can be found in the editor drop down menu <strong>Meeting Template</strong>.</p>
                <p>BMLT fields can be found in the editor drop down menu <strong>Meeting Template Fields</strong>.</p>
                <p>The <strong>Default Font Size and Line Height</strong> will be used for the meeting template.</p>
                <p>Font Size and Line Height can be overridden using HTML mark-up in the meeting text.</p>
                </div>
                ';
                ?>
                <h3 class="hndle">Meeting Template<span title='<?php echo $title; ?>' class="top-tooltip"></span></h3>
                <div class="inside">
                    <div style="margin-bottom: 10px; padding:0;" id="accordion3">
                        <h3 class="help-accordian">Instructions</h3>
                        <div class="videocontent">
                            <video id="my_video_1" style="width:100%;height:100%;" controls width="100%" height="100%" preload="auto">
                                <source src="https://nameetinglist.org/videos/nameetinglist.mp4" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        </div>
                    </div>
                    <p>
                        Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_font_size" name="content_font_size" value="<?php echo Bread::getOption('content_font_size'); ?>" />&nbsp;&nbsp;
                        Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_line_height" type="text" maxlength="3" size="3" name="content_line_height" value="<?php echo Bread::getOption('content_line_height'); ?>" />&nbsp;&nbsp;
                        Wheelchair Icon Size: <input size="5" maxlength="10" class="bmlt-input-field" style="display:inline;" id="wheelchair_size" type="text" name="wheelchair_size" value="<?php echo Bread::getOption('wheelchair_size'); ?>" />&nbsp;&nbsp;
                    <div><i>Avoid using tables which will greatly slow down the generation time. Use CSS instead to get table-like effects if need be.</i></div>
                    <div style="margin-top:0px; margin-bottom:20px; max-width:100%; width:100%;">
                        <?php
                        $editor_id = "meeting_template_content";
                        $settings    = array(
                            'tabindex'      => false,
                            'editor_height' => 110,
                            'resize'        => true,
                            "media_buttons" => false,
                            "drag_drop_upload" => true,
                            "editor_css"    => "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
                            "teeny"         => false,
                            'quicktags'     => true,
                            'wpautop'       => false,
                            'textarea_name' => $editor_id,
                            'tinymce' => array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'custom_template_button_1,custom_template_button_2')
                        );
                        wp_editor(stripslashes(Bread::getOption('meeting_template_content')), $editor_id, $settings);
                        ?>
                    </div>
                </div>
            </div>
            <div id="starttimeformatdiv" class="postbox">
                <?php $title = '
                <p>Format the <strong>Start Time</strong> (start_time) field in the <strong>Meeting Template</strong>.</p>
                ';
                ?>
                <h3 class="hndle">Start Time Format<span title='<?php echo $title; ?>' class="top-tooltip"></span></h3>
                <div class="inside">
                    <?php
                    $space = Bread::getOption('remove_space') == '1' ? '' : ' ';
                    $end_time_2 = "9" . $space . "PM";
                    $start_time_2 = "8" . $space;
                    if (Bread::getOption('time_clock') == '12') {
                        $start_time = "8:00" . $space . "PM";
                        $end_time = "9:00" . $space . "PM";
                    } elseif (Bread::getOption('time_clock') == '24fr') {
                        $start_time = "20h00";
                        $end_time = "21h00";
                    } else {
                        $start_time = "20:00";
                        $end_time = "21:00";
                    }
                    ?>
                    <table>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="time_clock12" type="radio" name="time_clock" value="12" <?php echo (Bread::getOption('time_clock') == '12' || Bread::getOption('time_clock') == '' ? 'checked' : '') ?>><label for="time_clock">12 Hour</label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option1" type="radio" name="time_option" value="1" <?php echo (Bread::getOption('time_option') == '1' || Bread::getOption('time_option') == '' ? 'checked' : '') ?>><label for="option1"><?php echo $start_time ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <?php if (Bread::getOption('remove_space') == '0' || Bread::getOption('remove_space') == '') { ?>
                                    <div><input class="mlg" id="two" type="radio" name="remove_space" value="0" checked><label for="two">Add White Space</label></div>
                                <?php } else { ?>
                                    <div><input class="mlg" id="two" type="radio" name="remove_space" value="0"><label for="two">Add White Space</label></div>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="time_clock24" type="radio" name="time_clock" value="24" <?php echo (Bread::getOption('time_clock') == '24' ? 'checked' : '') ?>><label for="time_clock">24 Hour</label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option2" type="radio" name="time_option" value="2" <?php echo (Bread::getOption('time_option') == '2' ? 'checked' : '') ?>><label for="option2"><?php echo $start_time ?><?php echo $space ?>-<?php echo $space ?><?php echo $end_time ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <?php if (Bread::getOption('remove_space') == '1') { ?>
                                    <div><input class="mlg" id="four" type="radio" name="remove_space" value="1" checked><label for="four">Remove White Space</label></div>
                                <?php } else { ?>
                                    <div><input class="mlg" id="four" type="radio" name="remove_space" value="1"><label for="four">Remove White Space</label></div>
                                <?php } ?>
                            </td>
                        </tr>
                        </tr>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="time_clock24fr" type="radio" name="time_clock" value="24fr" <?php echo (Bread::getOption('time_clock') == '24fr' ? 'checked' : '') ?>><label for="time_clock">24 Hour French</label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option3" type="radio" name="time_option" value="3" <?php echo (Bread::getOption('time_option') == '3' ? 'checked' : '') ?>><label for="option3"><?php echo $start_time_2 ?><?php echo $space ?>-<?php echo $space ?><?php echo $end_time_2 ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div id="getusedformatsdiv" class="postbox">
                <?php $title = '
                <p>Create a special interest meeting list.</p>
                ';
                ?>
                <h3 class="hndle">Include Only This Meeting Format<span title='<?php echo $title; ?>' class="top-tooltip"></span></h3>
                <div class="inside">
                    <?php if ($this->connected) { ?>
                        <?php $used_formats = Bread_Bmlt::getFormatsForSelect(false); ?>
                    <?php } ?>
                    <label for="used_format_1">Meeting Format: </label>
                    <select id="used_format_1" name="used_format_1">
                        <?php if ($this->connected) { ?>
                            <option value="">Not Used</option>
                            <?php $countmax = count($used_formats); ?>
                            <?php for ($count = 0; $count < $countmax; $count++) { ?>
                                <?php if ($used_formats[$count]['id'] == Bread::getOption('used_format_1')) { ?>
                                    <option selected="selected" value="<?php echo esc_html($used_formats[$count]['id']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo esc_html($used_formats[$count]['id']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                                <?php } ?>
                            <?php } ?>
                        <?php } else { ?>
                            <option selected="selected" value="<?php echo Bread::getOption('used_format_1'); ?>"><?php echo 'Not Connected - Can not get Formats'; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="postbox">
                <h3 class="hndle">Additional List</h3>
                <div class="inside">
                    <p>
                        This section allows the definition of an additional meeting list, containing meetings that should not be included in the main
                        list. This is typically virtual meetings, but it can be any group of meetings identified by a format.
                    </p>
                    <p>
                        <label for="additional_list_format_key">Format of meetings in the additional list: </label>
                        <select id="additional_list_format_key" name="additional_list_format_key">
                            <option value="">Not Used</option>
                            <?php if ($this->connected) { ?>
                                <option value="@Virtual@" <?php echo Bread::getOption('additional_list_format_key') == '@Virtual@' ? 'selected' : '' ?>>Virtual Meetings</option>
                                <option value="@F2F@" <?php echo Bread::getOption('additional_list_format_key') == '@F2F@' ? 'selected' : '' ?>>Face-to-Face Meetings</option>
                                <?php $used_formats = Bread_Bmlt::getFormatsForSelect(true);
                                $countmax = count($used_formats);
                                for ($count = 0; $count < $countmax; $count++) {
                                    if ($used_formats[$count]['key_string'] == Bread::getOption('additional_list_format_key')) { ?>
                                        <option selected="selected" value="<?php echo esc_html($used_formats[$count]['key_string']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                                    <?php   } else { ?>
                                        <option value="<?php echo esc_html($used_formats[$count]['key_string']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                                    <?php   }
                                }
                            } ?>
                        </select>
                    </p>
                    <p>
                        <label for="additional_list_sort_order">Select sort order for the additional list</label>
                        <select id="additional_list_sort_order" name="additional_list_sort_order">
                            <option value="meeting_name" <?php echo Bread::getOption('additional_list_sort_order') == 'meeting_name' ? 'selected' : ''; ?>>By Name</option>
                            <option value="weekday_tinyint,start_time" <?php echo Bread::getOption('additional_list_sort_order') == 'weekday_tinyint,start_time' ? 'selected' : ''; ?>>By Day and Time</option>
                            <option value="same" <?php echo Bread::getOption('additional_list_sort_order') == 'same' ? 'selected' : ''; ?>>Same as main list</option>
                        </select>
                    </p>
                    <p>
                        <label for="additional_list_language">Select language for the additional list</label>
                        <select id="additional_list_language" name="additional_list_language">
                            <?php
                            if (Bread::getOption('additional_list_language') == '') {
                                echo "<option value=\"\" selected=\"selected\">Same as main list</option>";
                            } else {
                                echo "<option value=\"\">Same as main list</option>";
                            }
                            foreach (Bread::getTranslateTable() as $key => $value) {
                                if (Bread::getOption('additional_list_language') == $key) {
                                    echo "<option value=\"$key\" selected=\"selected\">" . $value['LANG_NAME'] . "</option>";
                                } else {
                                    echo "<option value=\"$key\">" . $value['LANG_NAME'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </p>
                    <?php if (Bread::getOption('page_fold') == 'half' || Bread::getOption('page_fold') == 'full') {
                        ?>
                        <div class="meeting2_footer_div">
                            <label for="meeting2_footer">Custom Footer: </label>
                            <input name="meeting2_footer" type="text" size="50" value="<?php echo Bread::getOption('meeting2_footer'); ?>">
                        </div>
                    <?php }
                    ?>
                    <p>
                        <label for="additional_list_custom_query">Custom Query: </label>
                        <input type="text" id="additional_list_custom_query" name="additional_list_custom_query" size="100" value="<?php echo esc_html(Bread::getOption('additional_list_custom_query')) ?>" />
                    </p>
                    <input name="include_additional_list" value="0" type="hidden">
                    <p><input type="checkbox" name="include_additional_list" value="1" <?php echo (Bread::getOption('include_additional_list') == '1' ? 'checked' : '') ?>>Include meetings with this format in the main list</p>
                    If you wish to define different contents for the additional list, use this template.
                    <div style="margin-top:0px; margin-bottom:20px; max-width:100%; width:100%;">
                        <?php
                        $editor_id = "additional_list_template_content";
                        $settings    = array(
                            'tabindex'      => false,
                            'editor_height' => 110,
                            'resize'        => true,
                            "media_buttons" => false,
                            "drag_drop_upload" => true,
                            "editor_css"    => "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
                            "teeny"         => false,
                            'quicktags'     => true,
                            'wpautop'       => false,
                            'textarea_name' => $editor_id,
                            'tinymce' => array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'custom_template_button_1,custom_template_button_2')
                        );
                        wp_editor(stripslashes(Bread::getOption('additional_list_template_content')), $editor_id, $settings);
                        ?>
                    </div>
                    <div class="inside">
                        <div style="margin-bottom: 10px; padding:0;" id="accordion_additional_list">
                            <h3 class="help-accordian">Instructions</h3>
                            <div class="videocontent">
                                <video id="my_video_1" style="width:100%;height:100%;" controls width="100%" height="100%" preload="auto">
                                    <source src="https://nameetinglist.org/videos/show_area_service_meetings.mp4" type="video/mp4">
                                    Your browser does not support HTML5 video.
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($this->admin->current_user_can_modify()) {
        echo '
    <input type="submit" value="Save Changes" id="bmltmeetinglistsave1" name="bmltmeetinglistsave" class="button-primary" />
 ';
    } ?>
    <?php echo '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="' . home_url() . '/?current-meeting-list=' . $this->admin->loaded_setting . '">Generate Meeting List</a></p>'; ?>
    <div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
    <br class="clear">
</div>