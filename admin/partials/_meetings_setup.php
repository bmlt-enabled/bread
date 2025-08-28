<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="meetingsheaderdiv" class="postbox">
                <div style="display:none;">
                    <div id="columnheader-tooltip-content">
                        <?php _e('Customize how meetings are grouped and the headline that each group has.', 'bread-domain') ?>
                    </div>
                </div>
                <h3 class="hndle"><?php _e('Meeting Group [Column] Header', 'bread-domain') ?><span data-tooltip-content="#columnheader-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <div>
                        <input name="suppress_heading" value="0" type="hidden">
                        <label for="suppress_heading"><?php _e('Suppress Heading: ', 'bread-domain') ?></label><input type="checkbox" name="suppress_heading" id="suppress_heading" value="1" <?php echo ($this->bread->getOption('suppress_heading') == '1' ? 'checked' : '') ?>>
                        <table id="header_options_div">
                            <tr>
                                <td style="padding-right: 10px;"><?php _e('Font Size: ', 'bread-domain') ?><input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="header_font_size" name="header_font_size" value="<?php echo esc_attr($this->bread->getOption('header_font_size')); ?>" /></td>
                                <td style="padding-right: 10px;">
                                    <div class="theme" id="sp-light">
                                        <label for="header_text_color"><?php _e('Text Color:', 'bread-domain') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' id="header_text_color" class="bmlt_color" name="header_text_color" value="<?php echo esc_attr($this->bread->getOption('header_text_color')); ?>" />
                                    </div>
                                </td>
                                <td style="padding-right: 10px;">
                                    <div class="theme" id="sp-light">
                                        <label for="header_background_color"><?php _e('Background Color:', 'bread-domain') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' id="header_background_color" class="bmlt_color" name="header_background_color" value="<?php echo esc_attr($this->bread->getOption('header_background_color')); ?>" />
                                    </div>
                                </td>
                                <td style="padding-right: 10px;">
                                    <input name="header_uppercase" value="0" type="hidden">
                                <td><label for="header_uppercase"><?php _e('Uppercase: ', 'bread-domain') ?></label><input type="checkbox" name="header_uppercase" value="1" <?php echo ($this->bread->getOption('header_uppercase') == '1' ? 'checked' : '') ?>></td>
                                <td style="padding-right: 10px;">
                                    <input name="header_bold" value="0" type="hidden">
                                <td><label for="header_bold"><?php _e('Bold: ', 'bread-domain') ?></label><input type="checkbox" name="header_bold" value="1" <?php echo ($this->bread->getOption('header_bold') == '1' ? 'checked' : '') ?>></td>
                                <td style="padding-right: 10px;">
                                    <input name="cont_header_shown" value="0" type="hidden">
                                <td><label for="cont_header_shown"><?php _e('Display (Cont) Header: ', 'bread-domain') ?></label><input type="checkbox" name="cont_header_shown" value="1" <?php echo ($this->bread->getOption('cont_header_shown') == '1' ? 'checked' : '') ?>></td>
                            </tr>
                        </table>
                    </div>
                    <p>
                    <div class="group_by" style="margin-right: 10px; display: inline;">
                        <label for="meeting_sort"><?php _e('Group Meetings By: ', 'bread-domain') ?></label>
                        <select id="meeting_sort" name="meeting_sort">
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'day' ? 'selected' : '') ?> value="day"><?php _e('Weekday', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'city' ? 'selected' : '') ?> value="city"><?php _e('City', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'group' ? 'selected' : '') ?> value="group"><?php _e('Group', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'county' ? 'selected' : '') ?> value="county"><?php _e('County', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'borough' ? 'selected' : '') ?> value="borough"><?php _e('Borough', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'borough_county' ? 'selected' : '') ?> value="borough_county"><?php _e('Borough+County', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'neighborhood_city' ? 'selected' : '') ?> value="neighborhood_city"><?php _e('Neighborhood+City', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'state' ? 'selected' : '') ?> value="state"><?php _e('State+City', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'weekday_area' ? 'selected' : '') ?> value="weekday_area"><?php _e('Weekday+Area', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'weekday_city' ? 'selected' : '') ?> value="weekday_city"><?php _e('Weekday+City', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'weekday_county' ? 'selected' : '') ?> value="weekday_county"><?php _e('Weekday+County ', 'bread-domain') ?></option>
                            <option <?php echo ($this->bread->getOption('meeting_sort') == 'user_defined' ? 'selected' : '') ?> value="user_defined"><?php _e('User Defined', 'bread-domain') ?></option>
                        </select>
                    </div>
                    <div class="borough_by_suffix">

                        <p>
                            <label for="borough_suffix"><?php _e('Borough Suffix: ', 'bread-domain') ?></label>
                            <input class="borough-by-suffix" id="borough_suffix" type="text" name="borough_suffix" value="<?php echo esc_attr($this->bread->getOption('borough_suffix')); ?>" />

                        </p>

                    </div>
                    <div class="county_by_suffix">

                        <p>
                            <label for="county_suffix"><?php _e('County Suffix: ', 'bread-domain') ?></label>
                            <input class="county-by-suffix" id="county_suffix" type="text" name="county_suffix" value="<?php echo esc_attr($this->bread->getOption('county_suffix')); ?>" />

                        </p>
                    </div>

                    <div class="neighborhood_by_suffix">

                        <p>
                            <label for="neighborhood_suffix"><?php _e('Neighborhood Suffix: ', 'bread-domain') ?></label>
                            <input class="neighborhood-by-suffix" id="neighborhood_suffix" type="text" name="neighborhood_suffix" value="<?php echo esc_attr($this->bread->getOption('neighborhood_suffix')); ?>" />

                        </p>

                    </div>
                    <div class="city_by_suffix">

                        <p>
                            <label for="county_suffix"><?php _e('City Suffix: ', 'bread-domain') ?></label>
                            <input class="city-by-suffix" id="city_suffix" type="text" name="city_suffix" value="<?php echo esc_attr($this->bread->getOption('city_suffix')); ?>" />

                        </p>
                    </div>
                    <div class="user_defined_headings">
                        <p>
                            <label for="main_grouping"><?php _e('Main Grouping: ', 'bread-domain') ?></label>
                            <select id="main_grouping" name="main_grouping">
                                <option <?php echo ($this->bread->getOption('main_grouping') == 'day' ? 'selected' : '') ?> value="day"><?php _e('Weekday', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('main_grouping') == 'location_municipality' ? 'selected' : '') ?> value="location_municipality"><?php _e('City', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('main_grouping') == 'location_neighborhood' ? 'selected' : '') ?> value="location_neighborhood"><?php _e('Neighborhood', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('main_grouping') == 'group' ? 'selected' : '') ?> value="group"><?php _e('Group', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('main_grouping') == 'location_sub_province' ? 'selected' : '') ?> value="location_sub_province"><?php _e('County', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('main_grouping') == 'location_city_subsection' ? 'selected' : '') ?> value="location_city_subsection"><?php _e('Borough', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('main_grouping') == 'location_province' ? 'selected' : '') ?> value="location_province"><?php _e('State', 'bread-domain') ?></option>
                                <?php
                                $fks = $this->bread->bmlt()->get_nonstandard_fieldkeys();
                                foreach ($fks as $fk) {
                                    $selected = '';
                                    if ($fk['key'] == $this->bread->getOption('main_grouping')) {
                                        $selected = 'selected';
                                    }
                                    echo '<option value="' . esc_attr($fk['key']) . '" ' . esc_attr($selected) . '>' . esc_html($fk['description']) . '</option>';
                                }
                                ?>
                            </select>
                            <label for="subgrouping"><?php _e('Sub-Grouping: ', 'bread-domain') ?></label>
                            <select id="subgrouping" name="subgrouping">
                                <option <?php echo (empty($this->bread->getOption('subgrouping')) ? 'selected' : '') ?> value=""><?php _e('None', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('subgrouping') == 'day' ? 'selected' : '') ?> value="day"><?php _e('Weekday', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('subgrouping') == 'location_municipality' ? 'selected' : '') ?> value="location_municipality"><?php _e('City', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('subgrouping') == 'location_neighborhood' ? 'selected' : '') ?> value="location_neighborhood"><?php _e('Neighborhood', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('subgrouping') == 'group' ? 'selected' : '') ?> value="group"><?php _e('Group', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('subgrouping') == 'location_sub_province' ? 'selected' : '') ?> value="location_sub_province"><?php _e('County', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('subgrouping') == 'location_city_subsection' ? 'selected' : '') ?> value="location_city_subsection"><?php _e('Borough', 'bread-domain') ?></option>
                                <option <?php echo ($this->bread->getOption('subgrouping') == 'location_province' ? 'selected' : '') ?> value="location_province"><?php _e('State', 'bread-domain') ?></option>
                                <?php
                                foreach ($fks as $fk) {
                                    $selected = '';
                                    if ($fk['key'] == $this->bread->getOption('subgrouping')) {
                                        $selected = 'selected';
                                    }
                                    echo '<option value="' . esc_attr($fk['key']) . '" ' . esc_attr($selected) . '>' . esc_html($fk['description']) . '</option>';
                                }
                                ?>
                            </select>
                        </p>
                    </div>
                    <div class="show_subheader">
                        <p>
                            <label for="sub_header_shown"><?php _e('Display Subgrouping: ', 'bread-domain') ?></label>
                            <select name="sub_header_shown">
                                <option value="none" <?php echo ($this->bread->getOption('sub_header_shown') == 'none' ? 'selected' : '') ?>>
                                    <?php _e('No header for subgroups', 'bread-domain') ?>
                                </option>
                                <option value="display" <?php echo ($this->bread->getOption('sub_header_shown') == 'display' ? 'selected' : '') ?>>
                                    <?php _e('Display each subgroup with its own header', 'bread-domain') ?>
                                </option>
                                <option value="combined" <?php echo ($this->bread->getOption('sub_header_shown') == 'combined' ? 'selected' : '') ?>>
                                    <?php _e('Combine main and subgroup into a single header', 'bread-domain') ?>
                                </option>
                            </select>
                        </p>
                    </div>
                    <div class="weekday_language_div">
                        <label for="weekday_language"><?php _e('Weekday Language: ', 'bread-domain') ?></label>
                        <select name="weekday_language">
                            <?php
                            foreach ($this->bread->getTranslateTable() as $key => $value) {
                                if ($this->bread->getOption('weekday_language') == $key || $this->bread->getOption('weekday_language') == '') {
                                    echo "<option value=\"" . esc_attr($key) . "\" selected>" . esc_html($value['LANG_NAME']) . "</option>";
                                } else {
                                    echo "<option value=\"" . esc_attr($key) . "\">" . esc_html($value['LANG_NAME']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="weekday_start_div">
                        <label for="weekday_start"><?php _e('Weekday Start: ', 'bread-domain') ?></label>
                        <select name="weekday_start">
                            <?php
                            for ($d = 1; $d <= 7; $d++) {
                                if ($this->bread->getOption('weekday_start') == $d || $this->bread->getOption('weekday_start') == '') {
                                    echo "<option value=\"" . esc_attr($d) . "\" selected>" . esc_html($this->bread->getday($d, false, $this->bread->getOption('weekday_language'))) . "</option>";
                                } else {
                                    echo "<option value=\"" . esc_attr($d) . "\">" . esc_html($this->bread->getday($d, false, $this->bread->getOption('weekday_language'))) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="meeting1_footer_div booklet">
                        <label for="meeting1_footer"><?php _e('Custom Footer: ', 'bread-domain') ?></label>
                        <input name="meeting1_footer" type="text" size="50" value="<?php echo esc_attr($this->bread->getOption('meeting1_footer')); ?>">
                    </div>
                </div>
            </div>
            <div id="custommeetingtemplatediv" class="postbox">
                <div style="display:none;">
                    <div id="meetingtemplate-tooltip-content">
                        <div style="width:550px; margin-bottom:20px;">
                            <p>The <strong>Meeting Template</strong> is a powerful and flexible method for customizing meetings using
                                HTML markup and BMLT field names. The template is set-up once and never needs to be messed
                                with again. Note: When changes are made to the Default Font Size or Line Height, the template
                                may need to be adjusted to reflect those changes.</p>
                            <p>Sample templates can be found in the editor drop down menu <strong>Meeting Template</strong>.</p>
                            <p>BMLT fields can be found in the editor drop down menu <strong>Meeting Template Fields</strong>.</p>
                            <p>The <strong>Default Font Size and Line Height</strong> will be used for the meeting template.</p>
                            <p>Font Size and Line Height can be overridden using HTML mark-up in the meeting text.</p>
                        </div>
                    </div>
                </div>
                <h3 class="hndle"><?php _e('Meeting Template', 'bread-domain') ?><span data-tooltip-content="#meetingtemplate-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <p>
                        <?php _e('Default Font Size: ', 'bread-domain') ?><input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_font_size" name="content_font_size" value="<?php echo esc_attr($this->bread->getOption('content_font_size')); ?>" />&nbsp;&nbsp;
                        <?php _e('Line Height: ', 'bread-domain') ?><input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_line_height" type="text" maxlength="3" size="3" name="content_line_height" value="<?php echo esc_attr($this->bread->getOption('content_line_height')); ?>" />&nbsp;&nbsp;
                        <?php _e('Wheelchair Icon Size: ', 'bread-domain') ?><input size="5" maxlength="10" class="bmlt-input-field" style="display:inline;" id="wheelchair_size" type="text" name="wheelchair_size" value="<?php echo esc_attr($this->bread->getOption('wheelchair_size')); ?>" />&nbsp;&nbsp;
                    <div><i><?php _e('Avoid using tables which will greatly slow down the generation time. Use CSS instead to get table-like effects if need be.', 'bread-domain') ?></i></div>
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
                        wp_editor(stripslashes($this->bread->getOption('meeting_template_content')), $editor_id, $settings);
                        ?>
                    </div>
                </div>
            </div>
            <div id="starttimeformatdiv" class="postbox">
                <h3 class="hndle"><?php _e('Start Time Format', 'bread-domain') ?><span title="Format the <strong>Start Time</strong> (start_time) field in the <strong>Meeting Template</strong>." class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <table>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg recalcTimeLabel" id="time_clock12" type="radio" name="time_clock" value="12" <?php echo ($this->bread->getOption('time_clock') == '12' || $this->bread->getOption('time_clock') == '' ? 'checked' : '') ?>><label for="time_clock"><?php _e('12 Hour', 'bread-domain') ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option1" type="radio" name="time_option" value="1" <?php echo ($this->bread->getOption('time_option') == '1' || $this->bread->getOption('time_option') == '' ? 'checked' : '') ?>><label for="option1"></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <?php $checked = $this->bread->getOption('remove_space') == '0' || $this->bread->getOption('remove_space') == '' ? 'checked' : ''; ?>
                                <div><input class="mlg recalcTimeLabel" id="two" type="radio" name="remove_space" value="0" <?php echo esc_attr($checked); ?>><label for="two"><?php _e('Add White Space', 'bread-domain') ?></label></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg recalcTimeLabel" id="time_clock24" type="radio" name="time_clock" value="24" <?php echo ($this->bread->getOption('time_clock') == '24' ? 'checked' : '') ?>><label for="time_clock"><?php _e('24 Hour', 'bread-domain') ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option2" type="radio" name="time_option" value="2" <?php echo ($this->bread->getOption('time_option') == '2' ? 'checked' : '') ?>><label for="option2"></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg recalcTimeLabel" id="four" type="radio" name="remove_space" value="1" <?php echo ($this->bread->getOption('remove_space') == '1') ? 'checked' : ''; ?>><label for="four"><?php _e('Remove White Space', 'bread-domain') ?></label></div>
                            </td>
                        </tr>
                        </tr>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg recalcTimeLabel" id="time_clock24fr" type="radio" name="time_clock" value="24fr" <?php echo ($this->bread->getOption('time_clock') == '24fr' ? 'checked' : '') ?>><label for="time_clock"><?php _e('24 Hour French', 'bread-domain') ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option3" type="radio" name="time_option" value="3" <?php echo ($this->bread->getOption('time_option') == '3' ? 'checked' : '') ?>><label for="option3"></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div id="getusedformatsdiv" class="postbox">
                <h3 class="hndle"><?php _e('Include Only This Meeting Format', 'bread-domain') ?><span title='Create a special interest meeting list.' class="my-tooltip"></span><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <label for="used_format_1"><?php _e('Meeting Format: ', 'bread-domain') ?></label>
                    <select id="used_format_1" name="used_format_1">
                        <?php
                        if ($this->connected) {
                            echo '<option value="">Not Used</option>';
                            $used_formats = $this->bread->bmlt()->getFormatsForSelect(false);
                            foreach ($used_formats as $format) {
                                $selected = '';
                                if ($format['id'] == $this->bread->getOption('used_format_1')) {
                                    $selected = 'selected';
                                }
                                $id = $format['id'];
                                $str = esc_html($format['name_string']);
                                echo "<option " . esc_attr($selected) . " value='" . esc_attr($id) . "'>" . esc_html($str) . "</option>";
                            }
                        } else { ?>
                            <option selected value="Not Connected"></option><?php
                        } ?>
                    </select>
                </div>
            </div>
            <div class="postbox">
                <h3 class="hndle"><?php _e('Additional List', 'bread-domain') ?></h3>
                <div class="inside">
                    <p>
                        <?php _e("This section allows the definition of an additional meeting list, containing meetings that should not be included in the main
                        list. This is typically virtual meetings, but it can be any group of meetings identified by a format.", 'bread-domain') ?>
                    </p>
                    <p>
                        <label for="additional_list_format_key"><?php _e('Format of meetings in the additional list: ', 'bread-domain') ?></label>
                        <select id="additional_list_format_key" name="additional_list_format_key">
                            <option value=""><?php _e('Not Used', 'bread-domain') ?></option>
                            <?php if ($this->connected) { ?>
                                <option value="@Virtual@" <?php echo $this->bread->getOption('additional_list_format_key') == '@Virtual@' ? 'selected' : '' ?>><?php _e('Virtual Meetings', 'bread-domain') ?></option>
                                <option value="@F2F@" <?php echo $this->bread->getOption('additional_list_format_key') == '@F2F@' ? 'selected' : '' ?>><?php _e('Face-to-Face Meetings', 'bread-domain') ?></option>
                                <?php $used_formats = $this->bread->bmlt()->getFormatsForSelect(true);
                                $countmax = count($used_formats);
                                for ($count = 0; $count < $countmax; $count++) {
                                    if ($used_formats[$count]['key_string'] == $this->bread->getOption('additional_list_format_key')) { ?>
                                        <option selected value="<?php echo esc_attr($used_formats[$count]['key_string']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                                    <?php   } else { ?>
                                        <option value="<?php echo esc_attr($used_formats[$count]['key_string']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                                    <?php   }
                                }
                            } ?>
                        </select>
                    </p>
                    <p>
                        <label for="additional_list_sort_order"><?php _e('Select sort order for the additional list', 'bread-domain') ?></label>
                        <select id="additional_list_sort_order" name="additional_list_sort_order">
                            <option value="meeting_name" <?php echo $this->bread->getOption('additional_list_sort_order') == 'meeting_name' ? 'selected' : ''; ?>><?php _e('By Name', 'bread-domain') ?></option>
                            <option value="weekday_tinyint,start_time" <?php echo $this->bread->getOption('additional_list_sort_order') == 'weekday_tinyint,start_time' ? 'selected' : ''; ?>><?php _e('By Day and Time', 'bread-domain') ?></option>
                            <option value="same" <?php echo $this->bread->getOption('additional_list_sort_order') == 'same' ? 'selected' : ''; ?>><?php _e('Same as main list', 'bread-domain') ?></option>
                        </select>
                    </p>
                    <p>
                        <label for="additional_list_language"><?php _e('Select language for the additional list', 'bread-domain') ?></label>
                        <select id="additional_list_language" name="additional_list_language">
                            <?php
                            if ($this->bread->getOption('additional_list_language') == '') {
                                echo "<option value=\"\" selected=\"selected\">".__('Same as main list', 'bread-domain')."</option>";
                            } else {
                                echo "<option value=\"\">".__('Same as main list', 'bread-domain')."</option>";
                            }
                            foreach ($this->bread->getTranslateTable() as $key => $value) {
                                if ($this->bread->getOption('additional_list_language') == $key) {
                                    echo "<option value=\"" . esc_attr($key) . "\" selected>" . esc_html($value['LANG_NAME']) . "</option>";
                                } else {
                                    echo "<option value=\"" . esc_attr($key) . "\">" . esc_html($value['LANG_NAME']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </p>
                    <?php if ($this->bread->getOption('page_fold') == 'half' || $this->bread->getOption('page_fold') == 'full') {
                        ?>
                        <div class="meeting2_footer_div booklet">
                            <label for="meeting2_footer">Custom Footer: </label>
                            <input name="meeting2_footer" type="text" size="50" value="<?php echo esc_attr($this->bread->getOption('meeting2_footer')); ?>">
                        </div>
                    <?php }
                    ?>
                    <p>
                        <label for="additional_list_custom_query"><?php _e('Custom Query: ', 'bread-domain') ?></label>
                        <input type="text" id="additional_list_custom_query" name="additional_list_custom_query" size="100" value="<?php echo esc_attr($this->bread->getOption('additional_list_custom_query')) ?>" />
                    </p>
                    <input name="include_additional_list" value="0" type="hidden">
                    <p><input type="checkbox" name="include_additional_list" value="1" <?php echo ($this->bread->getOption('include_additional_list') == '1' ? 'checked' : '') ?>><?php _e('Include meetings with this format in the main list', 'bread-domain') ?></p>
                    <?php _e('If you wish to define different contents for the additional list, use this template.', 'bread-domain') ?>
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
                        wp_editor(stripslashes($this->bread->getOption('additional_list_template_content')), $editor_id, $settings);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>