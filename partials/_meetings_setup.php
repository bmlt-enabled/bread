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
                            <video id="my_video_1"  style="width:100%;height:100%;" controls width="100%" height="100%" preload="auto">
                                <source src="https://nameetinglist.org/videos/meeting_group_header.mp4" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        </div>
                    </div>
                    <table><tr>
                    <td style="padding-right: 10px;">Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="header_font_size" name="header_font_size" value="<?php echo $this->options['header_font_size']; ?>" /></td>
                    <td style="padding-right: 10px;">
                        <div class="theme" id="sp-light">
                            <label for="header_text_color">Text Color:</label>  <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="header_text_color" name="header_text_color" value="<?php echo $this->options['header_text_color']; ?>" />
                        </div>
                    </td>
                    <td style="padding-right: 10px;">
                        <div class="theme" id="sp-light">
                            <label for="header_background_color">Background Color:</label>  <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="header_background_color" name="header_background_color" value="<?php echo $this->options['header_background_color']; ?>" />
                        </div>
                    </td>
                    <td style="padding-right: 10px;">
                        <input name="header_uppercase" value="0" type="hidden">
                    <td><label for="header_uppercase">Uppercase: </label><input type="checkbox" name="header_uppercase" value="1" <?php echo ($this->options['header_uppercase'] == '1' ? 'checked' : '') ?>></td>
                    <td style="padding-right: 10px;">
                        <input name="header_bold" value="0" type="hidden">
                    <td><label for="header_bold">Bold: </label><input type="checkbox" name="header_bold" value="1" <?php echo ($this->options['header_bold'] == '1' ? 'checked' : '') ?>></td>
                    <td style="padding-right: 10px;">
                        <input name="sub_header_shown" value="0" type="hidden">
                    <td><label for="sub_header_shown">Display Sub Heading: </label><input type="checkbox" name="sub_header_shown" value="1" <?php echo ($this->options['sub_header_shown'] == '1' ? 'checked' : '') ?>></td>
                    </tr></table>
                    <p>
                        <div class="group_by" style="margin-right: 10px; display: inline;">
                            <label for="meeting_sort">Group Meetings By: </label>
                            <select id="meeting_sort" name="meeting_sort">					
                                <option <?php echo ($this->options['meeting_sort'] == 'day' ? 'selected="selected"' : '') ?> value="day">Weekday</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'city' ? 'selected="selected"' : '') ?> value="city">City</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'group' ? 'selected="selected"' : '') ?> value="group">Group</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'county' ? 'selected="selected"' : '') ?> value="county">County</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'borough' ? 'selected="selected"' : '') ?> value="borough">Borough</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'borough_county' ? 'selected="selected"' : '') ?> value="borough_county">Borough+County</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'neighborhood_city' ? 'selected="selected"' : '') ?> value="neighborhood_city">Neighborhood+City</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'state' ? 'selected="selected"' : '') ?> value="state">State+City</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'weekday_area' ? 'selected="selected"' : '') ?> value="weekday_area">Weekday+Area</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'weekday_city' ? 'selected="selected"' : '') ?> value="weekday_city">Weekday+City</option>
                                <option <?php echo ($this->options['meeting_sort'] == 'weekday_county' ? 'selected="selected"' : '') ?> value="weekday_county">Weekday+County</option>
                            </select>
                        </div>
                    <div class="borough_by_suffix">

                        <p>
                            <label for="borough_suffix">Borough Suffix: </label>
                            <input class="borough-by-suffix" id="borough_suffix" type="text" name="borough_suffix" value="<?php echo $this->options['borough_suffix']; ?>" />

                        </p>

                    </div>
                    <div class="county_by_suffix">

                        <p>
                            <label for="county_suffix">County Suffix: </label>
                            <input class="county-by-suffix" id="county_suffix" type="text" name="county_suffix" value="<?php echo $this->options['county_suffix']; ?>" />

                        </p>
                    </div>

                    <div class="neighborhood_by_suffix">

                        <p>
                            <label for="neighborhood_suffix">Neighborhood Suffix: </label>
                            <input class="neighborhood-by-suffix" id="neighborhood_suffix" type="text" name="neighborhood_suffix" value="<?php echo $this->options['neighborhood_suffix']; ?>" />

                        </p>

                    </div>
                    <div class="city_by_suffix">

                        <p>
                            <label for="county_suffix">City Suffix: </label>
                            <input class="city-by-suffix" id="city_suffix" type="text" name="city_suffix" value="<?php echo $this->options['city_suffix']; ?>" />

                        </p>
                    </div>
					<?php
                    $languages = [
								"en" => "English",
								"fr" => "French",
								"po" => "Portuguese",
								"both" => "English/Spanish",
								"both_po" => "English/Spanish/Portuguese",
								"fr_en" => "French/English",
								"se" => "Swedish",
                                "dk" => "Danish",
                                "it" => "Italian"
						];
					?>
					<div class="weekday_language_div">
						<label for="weekday_language">Weekday Language: </label>
						<select name="weekday_language">
						<?php
							foreach ($languages as $key => $value) {
								if ($this->options['weekday_language'] == $key || $this->options['weekday_language'] == '' ) {
									echo "<option value=\"$key\" selected=\"selected\">$value</option>";
								} else {
									echo "<option value=\"$key\">$value</option>";
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
								if ($this->options['weekday_start'] == $d || $this->options['weekday_start'] == '' ) {
									echo "<option value=\"$d\" selected=\"selected\">" . $this->getday($d, false, $this->options['weekday_language']) . "</option>";
								} else {
									echo "<option value=\"$d\">" . $this->getday($d, false, $this->options['weekday_language']) . "</option>";
								}
							}
							?>
						</select>
					</div>
                    <p>
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
                            <video id="my_video_1"  style="width:100%;height:100%;" controls width="100%" height="100%" preload="auto">
                                <source src="https://nameetinglist.org/videos/nameetinglist.mp4" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        </div>
                    </div>
                    <p>
                    Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_font_size" name="content_font_size" value="<?php echo $this->options['content_font_size'] ;?>" />&nbsp;&nbsp;
                    Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_line_height" type="text" maxlength="3" size="3" name="content_line_height" value="<?php echo $this->options['content_line_height'] ;?>" />&nbsp;&nbsp
                    <?php $title = '
                    <p>Page Height Adjust will add or remove space at the bottom of the meeting list.</p>
                    <p>1. Decrease value if a Group Header is missing at the top of the meeting list (-5, -10, etc).</p>
                    <p>2. increase value when using a small meeting font to fit more meetings (+5, +10, etc).</p>
                    ';
                    ?>
                    Page Height Adjust: <input min="-50" max="50" step="1" size="4" maxlength="4" type="number" class="bmlt-input-field" style="display:inline;" name="page_height_fix" value="<?php echo $this->options['page_height_fix'] ;?>" /><span title='<?php echo $title; ?>' class="top-middle-tooltip"></span>
                    </p>
                    <div><i>Decrease Page Height Adjust if <strong>MEETING GROUP HEADER</strong> is missing.</i></div>
                    <div style="margin-top:0px; margin-bottom:20px; max-width:100%; width:100%;">
                        <?php
                        $editor_id = "meeting_template_content";
                        $settings    = array (
                            'tabindex'      => FALSE,
                            'editor_height'	=> 110,
                            'resize'        => TRUE,
                            "media_buttons"	=> FALSE,
                            "drag_drop_upload" => TRUE,
                            "editor_css"	=> "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
                            "teeny"			=> FALSE,
                            'quicktags'		=> TRUE,
                            'wpautop'		=> FALSE,
                            'textarea_name' => $editor_id,
                            'tinymce'=> array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'custom_template_button_1,custom_template_button_2')
                        );
                        wp_editor( stripslashes($this->options['meeting_template_content']), $editor_id, $settings );
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
                    <?php $space = ' '; ?>
                    <?php if ( $this->options['remove_space'] == '1' ) { ?>
                        <?php $space = ''; ?>
                    <?php } ?>
                    <?php if ( $this->options['time_clock'] == '12' ) { ?>
                        <?php $start_time = "8:00".$space."PM"; ?>
                        <?php $start_time_2 = "8".$space; ?>
                        <?php $end_time = "9:00".$space."PM"; ?>
                        <?php $end_time_2 = "9".$space."PM"; ?>
                    <?php } elseif ( $this->options['time_clock'] == '24fr' ) { ?>
                        <?php $start_time = "20h00"; ?>
                        <?php $end_time = "21h00"; ?>
                    <?php } else { ?>
                        <?php $start_time = "20:00"; ?>
                        <?php $end_time = "21:00"; ?>
                    <?php } ?>
                    <table>
                    <tr>
                    <td style="padding-right: 30px;">
                        <div><input class="mlg" id="time_clock12" type="radio" name="time_clock" value="12" <?php echo ($this->options['time_clock'] == '12' || $this->options['time_clock'] == '' ? 'checked' : '') ?>><label for="time_clock">12 Hour</label></div>
                    </td>
                    <td style="padding-right: 30px;">
                        <div><input class="mlg" id="option1" type="radio" name="time_option" value="1" <?php echo ($this->options['time_option'] == '1' || $this->options['time_option'] == '' ? 'checked' : '') ?>><label for="option1"><?php echo $start_time ?></label></div>
                    </td>
                    <td style="padding-right: 30px;">
                    <?php if ( $this->options['remove_space'] == '0' || $this->options['remove_space'] == '' ) { ?>
                        <div><input class="mlg" id="two" type="radio" name="remove_space" value="0" checked><label for="two">Add White Space</label></div>
                    <?php } else { ?>
                        <div><input class="mlg" id="two" type="radio" name="remove_space" value="0"><label for="two">Add White Space</label></div>
                    <?php } ?>
                    </td>
                    </tr>
                    <tr>
                    <td style="padding-right: 30px;">
                        <div><input class="mlg" id="time_clock24" type="radio" name="time_clock" value="24" <?php echo ($this->options['time_clock'] == '24' ? 'checked' : '') ?>><label for="time_clock">24 Hour</label></div>
                    </td>
                    <td style="padding-right: 30px;">
                        <div><input class="mlg" id="option2" type="radio" name="time_option" value="2" <?php echo ($this->options['time_option'] == '2' ? 'checked' : '') ?>><label for="option2"><?php echo $start_time ?><?php echo $space ?>-<?php echo $space ?><?php echo $end_time ?></label></div>
                    </td>
                    <td style="padding-right: 30px;">								
                    <?php if ( $this->options['remove_space'] == '1' ) { ?>
                        <div><input class="mlg" id="four" type="radio" name="remove_space" value="1" checked><label for="four">Remove White Space</label></div>
                    <?php } else { ?>
                        <div><input class="mlg" id="four" type="radio" name="remove_space" value="1"><label for="four">Remove White Space</label></div>
                    <?php } ?>									 
                    </td>
                    </tr>
                    </tr>
                    <tr>
                    <td style="padding-right: 30px;">
                        <div><input class="mlg" id="time_clock24fr" type="radio" name="time_clock" value="24fr" <?php echo ($this->options['time_clock'] == '24fr' ? 'checked' : '') ?>><label for="time_clock">24 Hour French</label></div>
                    </td>
                    <td style="padding-right: 30px;">
                        <div><input class="mlg" id="option3" type="radio" name="time_option" value="3" <?php echo ($this->options['time_option'] == '3' ? 'checked' : '') ?>><label for="option3"><?php echo $start_time_2 ?><?php echo $space ?>-<?php echo $space ?><?php echo $end_time_2 ?></label></div>
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
                    <?php if ($this_connected) { ?>
                        <?php $used_formats = $this->getUsedFormats(); ?>
                    <?php } ?>
                    <label for="used_format_1">Meeting Format: </label>
                    <select id="used_format_1" name="used_format_1">
                    <?php if ($this_connected) { ?>
                        <option value="">Not Used</option>
                        <?php $countmax = count ( $used_formats ); ?>
                        <?php for ( $count = 0; $count < $countmax; $count++ ) { ?>
                            <?php if ( $used_formats[$count]['id'] == $this->options['used_format_1'] ) { ?>
                                <option selected="selected" value="<?php echo esc_html($used_formats[$count]['id']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                            <?php } else { ?>
                                <option value="<?php echo esc_html($used_formats[$count]['id']) ?>"><?php echo esc_html($used_formats[$count]['name_string']) ?></option>
                            <?php } ?>
                        <?php } ?>
                    <?php } else { ?>
                        <option selected="selected" value="<?php echo $this->options['used_format_1']; ?>"><?php echo 'Not Connected - Can not get Formats'; ?></option>
                    <?php } ?>
                    </select>
                </div>
            </div>
            <?php
                $connected = '';
                $logged_in = wp_remote_retrieve_body($this->authenticate_root_server()); ?>
            <?php $connected = "<p><div style='color: #f00;font-size: 16px;vertical-align: middle;' class='dashicons dashicons-unlock'></div><span style='color: #f00;'>Login ID or Password Incorrect</span></p>"; ?>
            <?php if ( $logged_in == 'OK') { ?>
                <?php $connected = "<p><div style='color: #00AD00;font-size: 16px;vertical-align: middle;' class='dashicons dashicons-lock'></div><span style='color: #00AD00;'>Login OK</span></p>"; ?>
            <?php } ?>
            <div class="postbox">
                <h3 class="hndle">Special Features<span title='You must login with an service body administrator account to use these features.' class="top-tooltip"></span></h3>
                <div class="inside">
                    <p>
                        <label for="bmlt_login_id">Login ID: </label>
                        <input class="bmlt-login" id="bmlt_login_id" type="text" name="bmlt_login_id" value="<?php echo esc_html($this->options['bmlt_login_id']) ;?>" />&nbsp;&nbsp;&nbsp;&nbsp;
                        <label for="bmlt_login_password">Password: </label>
                        <input class="bmlt-login" id="bmlt_login_password" type="password" name="bmlt_login_password" value="<?php echo esc_html($this->options['bmlt_login_password']) ;?>" />  <?php echo $connected; ?>
                    </p>
                    <?php if ($logged_in == 'OK') { ?>
                        <div id="includeemaildiv" class="inside">
                            <?php $title = '
                                <p>Enable the <strong>Meeting Email Contact</strong> (email_contact) field in the <strong>Meeting Template</strong>.</p>
                                <p>This feature requires a login ID and password for the service body.</p>
                                <p>This can be Service Body Administrator or Observer.</p>
                                <p>Visit the <a target="_blank" href="https://bmlt.magshare.net/specific-topics/bmlt-roles/">BMLT Roles</a> page for more details.</p>
                                ';
                            ?>
                            <b>Meeting Email Contact<span title='<?php echo $title; ?>' class="top-tooltip"></span></b>
                            <input name="include_meeting_email" value="0" type="hidden"><p><input type="checkbox" name="include_meeting_email" value="1" <?php echo ($this->options['include_meeting_email'] == '1' ? 'checked' : '') ?>>Enable</p>
                        </div>
                     <?php } ?>
                        <div id="includeasmdiv" class="inside">
                            <?php $title = '
                                <p>Show <strong>Area Service Meetings</strong> (ASM) in the meeting list.</p>
                                <p>In BMLT a meeting can have the format code ASM indicating it is a service meeting.</p>
                                <p>Typically Areas show their Area Service Meetings separately on the meeting list</p>
                                <p>and may not want to show the Area Service Meetings again in the list of regular meetings.</p>
                                <p>To list the Area Service Meetings in the list of regular meetings enable this check-box.</p>
                                <p>If you login, you can use unpublished meetings with the ASM format.</p>
                                ';
                            ?>
                            <b>Show Area Service Meetings<span title='<?php echo $title; ?>' class="top-tooltip"></span></b>
                            <input name="include_asm" value="0" type="hidden">
                            <p><input type="checkbox" name="include_asm" value="1" <?php echo ($this->options['include_asm'] == '1' ? 'checked' : '') ?>>Enable</p>
                            <div class="inside">
                                <div style="margin-bottom: 10px; padding:0;" id="accordion_asm">
                                    <h3 class="help-accordian">Instructions</h3>
                                    <div class="videocontent">
                                        <video id="my_video_1"  style="width:100%;height:100%;" controls width="100%" height="100%" preload="auto">
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
    </div>
    <input type="submit" value="Save Changes" id="bmltmeetinglistsave4" name="bmltmeetinglistsave" class="button-primary" />
    <?php echo '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
    <div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
    <br class="clear">
</div>
