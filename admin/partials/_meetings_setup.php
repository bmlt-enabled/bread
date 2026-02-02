<?php
if (! defined('ABSPATH')) {
    exit;
}
function Bread_meetings_setup_page_render(Bread_AdminDisplay $breadAdmin)
{
    $bread = $breadAdmin->getBreadInstance();
    ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="meetingsheaderdiv" class="postbox">
                <div style="display:none;">
                    <div id="columnheader-tooltip-content">
                        <?php esc_html_e('Customize how meetings are grouped and the headline that each group has.', 'bread') ?>
                    </div>
                </div>
                <h3 class="hndle"><?php esc_html_e('Meeting Group [Column] Header', 'bread') ?><span data-tooltip-content="#columnheader-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <div>
                        <input name="suppress_heading" value="0" type="hidden">
                        <label for="suppress_heading"><?php esc_html_e('Suppress Heading: ', 'bread') ?></label><input type="checkbox" name="suppress_heading" id="suppress_heading" value="1" <?php echo ($bread->getOption('suppress_heading') == '1' ? 'checked' : '') ?>>
                        <table id="header_options_div">
                            <tr>
                                <td style="padding-right: 10px;"><?php esc_html_e('Font Size: ', 'bread') ?><input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="header_font_size" name="header_font_size" value="<?php echo esc_attr($bread->getOption('header_font_size')); ?>" /></td>
                                <td style="padding-right: 10px;">
                                    <div class="theme" id="sp-light">
                                        <label for="header_text_color"><?php esc_html_e('Text Color:', 'bread') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' id="header_text_color" class="bmlt_color" name="header_text_color" value="<?php echo esc_attr($bread->getOption('header_text_color')); ?>" />
                                    </div>
                                </td>
                                <td style="padding-right: 10px;">
                                    <div class="theme" id="sp-light">
                                        <label for="header_background_color"><?php esc_html_e('Background Color:', 'bread') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' id="header_background_color" class="bmlt_color" name="header_background_color" value="<?php echo esc_attr($bread->getOption('header_background_color')); ?>" />
                                    </div>
                                </td>
                                <td style="padding-right: 10px;">
                                    <input name="header_uppercase" value="0" type="hidden">
                                <td><label for="header_uppercase"><?php esc_html_e('Uppercase: ', 'bread') ?></label><input type="checkbox" name="header_uppercase" value="1" <?php echo ($bread->getOption('header_uppercase') == '1' ? 'checked' : '') ?>></td>
                                <td style="padding-right: 10px;">
                                    <input name="header_bold" value="0" type="hidden">
                                <td><label for="header_bold"><?php esc_html_e('Bold: ', 'bread') ?></label><input type="checkbox" name="header_bold" value="1" <?php echo ($bread->getOption('header_bold') == '1' ? 'checked' : '') ?>></td>
                                <td style="padding-right: 10px;">
                                    <input name="cont_header_shown" value="0" type="hidden">
                                <td><label for="cont_header_shown"><?php esc_html_e('Display (Cont) Header: ', 'bread') ?></label><input type="checkbox" name="cont_header_shown" value="1" <?php echo ($bread->getOption('cont_header_shown') == '1' ? 'checked' : '') ?>></td>
                            </tr>
                        </table>
                    </div>
                    <p>
                    <div class="group_by" style="margin-right: 10px; display: inline;">
                        <label for="meeting_sort"><?php esc_html_e('Group Meetings By: ', 'bread') ?></label>
                        <select id="meeting_sort" name="meeting_sort">
                            <option <?php echo ($bread->getOption('meeting_sort') == 'day' ? 'selected' : '') ?> value="day"><?php esc_html_e('Weekday', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'city' ? 'selected' : '') ?> value="city"><?php esc_html_e('City', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'group' ? 'selected' : '') ?> value="group"><?php esc_html_e('Group', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'county' ? 'selected' : '') ?> value="county"><?php esc_html_e('County', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'borough' ? 'selected' : '') ?> value="borough"><?php esc_html_e('Borough', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'borough_county' ? 'selected' : '') ?> value="borough_county"><?php esc_html_e('Borough+County', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'neighborhood_city' ? 'selected' : '') ?> value="neighborhood_city"><?php esc_html_e('Neighborhood+City', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'state' ? 'selected' : '') ?> value="state"><?php esc_html_e('State+City', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'weekday_area' ? 'selected' : '') ?> value="weekday_area"><?php esc_html_e('Weekday+Area', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'weekday_city' ? 'selected' : '') ?> value="weekday_city"><?php esc_html_e('Weekday+City', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'weekday_county' ? 'selected' : '') ?> value="weekday_county"><?php esc_html_e('Weekday+County ', 'bread') ?></option>
                            <option <?php echo ($bread->getOption('meeting_sort') == 'user_defined' ? 'selected' : '') ?> value="user_defined"><?php esc_html_e('User Defined', 'bread') ?></option>
                        </select>
                    </div>
                    <div class="borough_by_suffix">

                        <p>
                            <label for="borough_suffix"><?php esc_html_e('Borough Suffix: ', 'bread') ?></label>
                            <input class="borough-by-suffix" id="borough_suffix" type="text" name="borough_suffix" value="<?php echo esc_attr($bread->getOption('borough_suffix')); ?>" />

                        </p>

                    </div>
                    <div class="county_by_suffix">

                        <p>
                            <label for="county_suffix"><?php esc_html_e('County Suffix: ', 'bread') ?></label>
                            <input class="county-by-suffix" id="county_suffix" type="text" name="county_suffix" value="<?php echo esc_attr($bread->getOption('county_suffix')); ?>" />

                        </p>
                    </div>

                    <div class="neighborhood_by_suffix">

                        <p>
                            <label for="neighborhood_suffix"><?php esc_html_e('Neighborhood Suffix: ', 'bread') ?></label>
                            <input class="neighborhood-by-suffix" id="neighborhood_suffix" type="text" name="neighborhood_suffix" value="<?php echo esc_attr($bread->getOption('neighborhood_suffix')); ?>" />

                        </p>

                    </div>
                    <div class="city_by_suffix">

                        <p>
                            <label for="county_suffix"><?php esc_html_e('City Suffix: ', 'bread') ?></label>
                            <input class="city-by-suffix" id="city_suffix" type="text" name="city_suffix" value="<?php echo esc_attr($bread->getOption('city_suffix')); ?>" />

                        </p>
                    </div>
                    <div class="user_defined_headings">
                        <p>
                            <label for="main_grouping"><?php esc_html_e('Main Grouping: ', 'bread') ?></label>
                            <select id="main_grouping" name="main_grouping">
                                <option <?php echo ($bread->getOption('main_grouping') == 'day' ? 'selected' : '') ?> value="day"><?php esc_html_e('Weekday', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('main_grouping') == 'location_municipality' ? 'selected' : '') ?> value="location_municipality"><?php esc_html_e('City', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('main_grouping') == 'location_neighborhood' ? 'selected' : '') ?> value="location_neighborhood"><?php esc_html_e('Neighborhood', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('main_grouping') == 'group' ? 'selected' : '') ?> value="group"><?php esc_html_e('Group', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('main_grouping') == 'location_sub_province' ? 'selected' : '') ?> value="location_sub_province"><?php esc_html_e('County', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('main_grouping') == 'location_city_subsection' ? 'selected' : '') ?> value="location_city_subsection"><?php esc_html_e('Borough', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('main_grouping') == 'location_province' ? 'selected' : '') ?> value="location_province"><?php esc_html_e('State', 'bread') ?></option>
                                <?php
                                $fks = $bread->bmlt()->get_nonstandard_fieldkeys();
                                foreach ($fks as $fk) {
                                    $selected = '';
                                    if ($fk['key'] == $bread->getOption('main_grouping')) {
                                        $selected = 'selected';
                                    }
                                    echo '<option value="' . esc_attr($fk['key']) . '" ' . esc_attr($selected) . '>' . esc_html($fk['description']) . '</option>';
                                }
                                ?>
                            </select>
                            <label for="subgrouping"><?php esc_html_e('Sub-Grouping: ', 'bread') ?></label>
                            <select id="subgrouping" name="subgrouping">
                                <option <?php echo (empty($bread->getOption('subgrouping')) ? 'selected' : '') ?> value=""><?php esc_html_e('None', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('subgrouping') == 'day' ? 'selected' : '') ?> value="day"><?php esc_html_e('Weekday', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('subgrouping') == 'location_municipality' ? 'selected' : '') ?> value="location_municipality"><?php esc_html_e('City', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('subgrouping') == 'location_neighborhood' ? 'selected' : '') ?> value="location_neighborhood"><?php esc_html_e('Neighborhood', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('subgrouping') == 'group' ? 'selected' : '') ?> value="group"><?php esc_html_e('Group', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('subgrouping') == 'location_sub_province' ? 'selected' : '') ?> value="location_sub_province"><?php esc_html_e('County', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('subgrouping') == 'location_city_subsection' ? 'selected' : '') ?> value="location_city_subsection"><?php esc_html_e('Borough', 'bread') ?></option>
                                <option <?php echo ($bread->getOption('subgrouping') == 'location_province' ? 'selected' : '') ?> value="location_province"><?php esc_html_e('State', 'bread') ?></option>
                                <?php
                                foreach ($fks as $fk) {
                                    $selected = '';
                                    if ($fk['key'] == $bread->getOption('subgrouping')) {
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
                            <label for="sub_header_shown"><?php esc_html_e('Display Subgrouping: ', 'bread') ?></label>
                            <select name="sub_header_shown">
                                <option value="none" <?php echo ($bread->getOption('sub_header_shown') == 'none' ? 'selected' : '') ?>>
                                    <?php esc_html_e('No header for subgroups', 'bread') ?>
                                </option>
                                <option value="display" <?php echo ($bread->getOption('sub_header_shown') == 'display' ? 'selected' : '') ?>>
                                    <?php esc_html_e('Display each subgroup with its own header', 'bread') ?>
                                </option>
                                <option value="combined" <?php echo ($bread->getOption('sub_header_shown') == 'combined' ? 'selected' : '') ?>>
                                    <?php esc_html_e('Combine main and subgroup into a single header', 'bread') ?>
                                </option>
                            </select>
                        </p>
                    </div>
                    <div class="weekday_language_div">
                        <label for="weekday_language"><?php esc_html_e('Weekday Language: ', 'bread') ?></label>
                        <select name="weekday_language">
                            <?php
                            foreach ($bread->getTranslateTable() as $key => $value) {
                                if ($bread->getOption('weekday_language') == $key || $bread->getOption('weekday_language') == '') {
                                    echo "<option value=\"" . esc_attr($key) . "\" selected>" . esc_html($value['LANG_NAME']) . "</option>";
                                } else {
                                    echo "<option value=\"" . esc_attr($key) . "\">" . esc_html($value['LANG_NAME']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="weekday_start_div">
                        <label for="weekday_start"><?php esc_html_e('Weekday Start: ', 'bread') ?></label>
                        <select name="weekday_start">
                            <?php
                            for ($d = 1; $d <= 7; $d++) {
                                if ($bread->getOption('weekday_start') == $d || $bread->getOption('weekday_start') == '') {
                                    echo "<option value=\"" . esc_attr($d) . "\" selected>" . esc_html($bread->getday($d, false, $bread->getOption('weekday_language'))) . "</option>";
                                } else {
                                    echo "<option value=\"" . esc_attr($d) . "\">" . esc_html($bread->getday($d, false, $bread->getOption('weekday_language'))) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="meeting1_footer_div booklet">
                        <label for="meeting1_footer"><?php esc_html_e('Custom Footer: ', 'bread') ?></label>
                        <input name="meeting1_footer" type="text" size="50" value="<?php echo esc_attr($bread->getOption('meeting1_footer')); ?>">
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
                <h3 class="hndle"><?php esc_html_e('Meeting Template', 'bread') ?><span data-tooltip-content="#meetingtemplate-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <p>
                        <?php esc_html_e('Default Font Size: ', 'bread') ?><input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_font_size" name="content_font_size" value="<?php echo esc_attr($bread->getOption('content_font_size')); ?>" />&nbsp;&nbsp;
                        <?php esc_html_e('Line Height: ', 'bread') ?><input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_line_height" type="text" maxlength="3" size="3" name="content_line_height" value="<?php echo esc_attr($bread->getOption('content_line_height')); ?>" />&nbsp;&nbsp;
                        <?php esc_html_e('Wheelchair Icon Size: ', 'bread') ?><input size="5" maxlength="10" class="bmlt-input-field" style="display:inline;" id="wheelchair_size" type="text" name="wheelchair_size" value="<?php echo esc_attr($bread->getOption('wheelchair_size')); ?>" />&nbsp;&nbsp;
                    <div><i><?php esc_html_e('Avoid using tables which will greatly slow down the generation time. Use CSS instead to get table-like effects if need be.', 'bread') ?></i></div>
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
                        wp_editor(stripslashes($bread->getOption('meeting_template_content')), $editor_id, $settings);
                        ?>
                    </div>
                </div>
            </div>
            <div id="starttimeformatdiv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Start Time Format', 'bread') ?><span title="Format the <strong>Start Time</strong> (start_time) field in the <strong>Meeting Template</strong>." class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <table>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg recalcTimeLabel" id="time_clock12" type="radio" name="time_clock" value="12" <?php echo ($bread->getOption('time_clock') == '12' || $bread->getOption('time_clock') == '' ? 'checked' : '') ?>><label for="time_clock"><?php esc_html_e('12 Hour', 'bread') ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option1" type="radio" name="time_option" value="1" <?php echo ($bread->getOption('time_option') == '1' || $bread->getOption('time_option') == '' ? 'checked' : '') ?>><label for="option1"></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <?php $checked = $bread->getOption('remove_space') == '0' || $bread->getOption('remove_space') == '' ? 'checked' : ''; ?>
                                <div><input class="mlg recalcTimeLabel" id="two" type="radio" name="remove_space" value="0" <?php echo esc_attr($checked); ?>><label for="two"><?php esc_html_e('Add White Space', 'bread') ?></label></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg recalcTimeLabel" id="time_clock24" type="radio" name="time_clock" value="24" <?php echo ($bread->getOption('time_clock') == '24' ? 'checked' : '') ?>><label for="time_clock"><?php esc_html_e('24 Hour', 'bread') ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option2" type="radio" name="time_option" value="2" <?php echo ($bread->getOption('time_option') == '2' ? 'checked' : '') ?>><label for="option2"></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg recalcTimeLabel" id="four" type="radio" name="remove_space" value="1" <?php echo ($bread->getOption('remove_space') == '1') ? 'checked' : ''; ?>><label for="four"><?php esc_html_e('Remove White Space', 'bread') ?></label></div>
                            </td>
                        </tr>
                        </tr>
                        <tr>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg recalcTimeLabel" id="time_clock24fr" type="radio" name="time_clock" value="24fr" <?php echo ($bread->getOption('time_clock') == '24fr' ? 'checked' : '') ?>><label for="time_clock"><?php esc_html_e('24 Hour French', 'bread') ?></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                                <div><input class="mlg" id="option3" type="radio" name="time_option" value="3" <?php echo ($bread->getOption('time_option') == '3' ? 'checked' : '') ?>><label for="option3"></label></div>
                            </td>
                            <td style="padding-right: 30px;">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div id="getusedformatsdiv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Include Only This Meeting Format', 'bread') ?><span title='Create a special interest meeting list.' class="my-tooltip"></span><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <label for="used_format_1"><?php esc_html_e('Meeting Format: ', 'bread') ?></label>
                    <select id="used_format_1" name="used_format_1">
                        <?php
                        if ($breadAdmin->isConnected()) {
                            echo '<option value="">Not Used</option>';
                            $used_formats = $bread->bmlt()->getFormatsForSelect(false);
                            foreach ($used_formats as $format) {
                                $selected = '';
                                if ($format['id'] == $bread->getOption('used_format_1')) {
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
                <h3 class="hndle"><?php esc_html_e('Additional List', 'bread') ?></h3>
                <div class="inside">
                    <p>
                        <?php esc_html_e("This section allows the definition of an additional meeting list, containing meetings that should not be included in the main
                        list. This is typically virtual meetings, but it can be any group of meetings identified by a format.", 'bread') ?>
                    </p>
                    <p>
                        <label for="additional_list_format_key"><?php esc_html_e('Format of meetings in the additional list: ', 'bread') ?></label>
                        <select id="additional_list_format_key" name="additional_list_format_key">
                            <option value=""><?php esc_html_e('Not Used', 'bread') ?></option>
                            <?php if ($breadAdmin->isConnected()) { ?>
                                <option value="@Virtual@" <?php echo $bread->getOption('additional_list_format_key') == '@Virtual@' ? 'selected' : '' ?>><?php esc_html_e('Virtual Meetings', 'bread') ?></option>
                                <option value="@F2F@" <?php echo $bread->getOption('additional_list_format_key') == '@F2F@' ? 'selected' : '' ?>><?php esc_html_e('Face-to-Face Meetings', 'bread') ?></option>
                                <?php $used_formats = $bread->bmlt()->getFormatsForSelect(true);
                                $countmax = count($used_formats);
                                for ($count = 0; $count < $countmax; $count++) {
                                    if ($used_formats[$count]['key_string'] == $bread->getOption('additional_list_format_key')) { ?>
                                        <option selected value="<?php echo esc_attr($used_formats[$count]['key_string']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                                    <?php   } else { ?>
                                        <option value="<?php echo esc_attr($used_formats[$count]['key_string']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                                    <?php   }
                                }
                            } ?>
                        </select>
                    </p>
                    <p>
                        <label for="additional_list_sort_order"><?php esc_html_e('Select sort order for the additional list', 'bread') ?></label>
                        <select id="additional_list_sort_order" name="additional_list_sort_order">
                            <option value="meeting_name" <?php echo $bread->getOption('additional_list_sort_order') == 'meeting_name' ? 'selected' : ''; ?>><?php esc_html_e('By Name', 'bread') ?></option>
                            <option value="weekday_tinyint,start_time" <?php echo $bread->getOption('additional_list_sort_order') == 'weekday_tinyint,start_time' ? 'selected' : ''; ?>><?php esc_html_e('By Day and Time', 'bread') ?></option>
                            <option value="same" <?php echo $bread->getOption('additional_list_sort_order') == 'same' ? 'selected' : ''; ?>><?php esc_html_e('Same as main list', 'bread') ?></option>
                        </select>
                    </p>
                    <p>
                        <label for="additional_list_language"><?php esc_html_e('Select language for the additional list', 'bread') ?></label>
                        <select id="additional_list_language" name="additional_list_language">
                            <?php
                            if ($bread->getOption('additional_list_language') == '') {
                                echo "<option value=\"\" selected=\"selected\">".esc_html(__('Same as main list', 'bread'))."</option>";
                            } else {
                                echo "<option value=\"\">".esc_html(__('Same as main list', 'bread'))."</option>";
                            }
                            foreach ($bread->getTranslateTable() as $key => $value) {
                                if ($bread->getOption('additional_list_language') == $key) {
                                    echo "<option value=\"" . esc_attr($key) . "\" selected>" . esc_html($value['LANG_NAME']) . "</option>";
                                } else {
                                    echo "<option value=\"" . esc_attr($key) . "\">" . esc_html($value['LANG_NAME']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </p>
                    <?php if ($bread->getOption('page_fold') == 'half' || $bread->getOption('page_fold') == 'full') {
                        ?>
                        <div class="meeting2_footer_div booklet">
                            <label for="meeting2_footer">Custom Footer: </label>
                            <input name="meeting2_footer" type="text" size="50" value="<?php echo esc_attr($bread->getOption('meeting2_footer')); ?>">
                        </div>
                    <?php }
                    ?>
                    <p>
                        <label for="additional_list_custom_query"><?php esc_html_e('Custom Query: ', 'bread') ?></label>
                        <input type="text" id="additional_list_custom_query" name="additional_list_custom_query" size="100" value="<?php echo esc_attr($bread->getOption('additional_list_custom_query')) ?>" />
                    </p>
                    <input name="include_additional_list" value="0" type="hidden">
                    <p><input type="checkbox" name="include_additional_list" value="1" <?php echo ($bread->getOption('include_additional_list') == '1' ? 'checked' : '') ?>><?php esc_html_e('Include meetings with this format in the main list', 'bread') ?></p>
                    <?php esc_html_e('If you wish to define different contents for the additional list, use this template.', 'bread') ?>
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
                        wp_editor(stripslashes($bread->getOption('additional_list_template_content')), $editor_id, $settings);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php }