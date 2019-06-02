<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="bmltrootserverurl" class="postbox">
                <h3 class="hndle">BMLT Server<span title='<p>Visit <a target="_blank" href="https://bmlt.app/what-is-the-bmlt/hit-parade/#bmlt-server">BMLT Server Implementations</a> to find your BMLT server</p>' class="tooltip"></span></h3>
                <div class="inside">
                    <p>
                    <label for="root_server">BMLT Server URL: </label>
                    <input class="bmlt-input" id="root_server" type="text" name="root_server" value="<?php echo $this->options['root_server']; ?>" />
                    </p>
                    <?php
                    if ( $this_connected ) {
                        echo $ThisVersion;
                    } elseif ( !isset($this->options['root_server']) ) {
                        echo "<span style='color: #f00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>ERROR: Please enter a BMLT Server</span>";
                    } else {
                        echo "<span style='color: #f00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>ERROR: Problem Connecting to BMLT Server</span>";
                    }
                    ?>
                    <?php if ($this_connected) { ?>
                        <?php $unique_areas = $this->get_areas(); ?>
                        <?php asort($unique_areas); ?>
                    <?php } ?>
                    <ul>
                        <li>
                            <label for="service_body_1">Service Body 1: </label>
                            <select class="service_body_select" id="service_body_1" name="service_body_1">
                            <?php if ($this_connected) { ?>
                                <option value="Not Used">Not Used</option>
                                <?php foreach($unique_areas as $unique_area){ ?>
                                    <?php $area_data = explode(',',$unique_area); ?>
                                    <?php $area_name = $area_data[0]; ?>
                                    <?php $area_id = $area_data[1]; ?>
                                    <?php $area_parent = $area_data[2]; ?>
                                    <?php $area_parent_name = $area_data[3]; ?>
                                    <?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
                                    <?php $is_data = explode(',',esc_html($this->options['service_body_1'])); ?>
                                    <?php if ( $is_data[0] != "Not Used" && $area_id == $is_data[1] ) { ?>
                                        <option selected="selected" value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } ?>
                                <?php } ?>
                            <?php } else { ?>
                                <option selected="selected" value="<?php echo esc_html($this->options['service_body_1']); ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
                            <?php } ?>
                            </select>
                        </li> 
                        <li>
                            <label for="service_body_2">Service Body 2: </label>
                            <select class="service_body_select" id="service_body_2" name="service_body_2">
                            <?php if ($this_connected) { ?>
                                <option value="Not Used">Not Used</option>
                                <?php foreach($unique_areas as $unique_area){ ?>
                                    <?php $area_data = explode(',',$unique_area); ?>
                                    <?php $area_name = $area_data[0]; ?>
                                    <?php $area_id = $area_data[1]; ?>
                                    <?php $area_parent = $area_data[2]; ?>
                                    <?php $area_parent_name = $area_data[3]; ?>
                                    <?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
                                    <?php $is_data = explode(',',esc_html($this->options['service_body_2'])); ?>
                                    <?php if ( $is_data[0] != "Not Used" && $area_id == $is_data[1] ) { ?>
                                        <option selected="selected" value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } ?>
                                <?php } ?>
                            <?php } else { ?>
                                <option selected="selected" value="<?php echo esc_html($this->options['service_body_2']); ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
                            <?php } ?>
                            </select>
                        </li> 
                        <li>
                            <label for="service_body_3">Service Body 3: </label>
                            <select class="service_body_select" id="service_body_3" name="service_body_3">
                            <?php if ($this_connected) { ?>
                                <option value="Not Used">Not Used</option>
                                <?php foreach($unique_areas as $unique_area){ ?>
                                    <?php $area_data = explode(',',$unique_area); ?>
                                    <?php $area_name = $area_data[0]; ?>
                                    <?php $area_id = $area_data[1]; ?>
                                    <?php $area_parent = $area_data[2]; ?>
                                    <?php $area_parent_name = $area_data[3]; ?>
                                    <?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
                                    <?php $is_data = explode(',',esc_html($this->options['service_body_3'])); ?>
                                    <?php if ( $is_data[0] != "Not Used" && $area_id == $is_data[1] ) { ?>
                                        <option selected="selected" value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } ?>
                                <?php } ?>
                            <?php } else { ?>
                                <option selected="selected" value="<?php echo esc_html($this->options['service_body_3']); ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
                            <?php } ?>
                            </select>
                        </li> 
                        <li>
                            <label for="service_body_4">Service Body 4: </label>
                            <select class="service_body_select" id="service_body_4" name="service_body_4">
                            <?php if ($this_connected) { ?>
                                <option value="Not Used">Not Used</option>
                                <?php foreach($unique_areas as $unique_area){ ?>
                                    <?php $area_data = explode(',',$unique_area); ?>
                                    <?php $area_name = $area_data[0]; ?>
                                    <?php $area_id = $area_data[1]; ?>
                                    <?php $area_parent = $area_data[2]; ?>
                                    <?php $area_parent_name = $area_data[3]; ?>
                                    <?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
                                    <?php $is_data = explode(',',esc_html($this->options['service_body_4'])); ?>
                                    <?php if ( $is_data[0] != "Not Used" && $area_id == $is_data[1] ) { ?>
                                        <option selected="selected" value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } ?>
                                <?php } ?>
                            <?php } else { ?>
                                <option selected="selected" value="<?php echo esc_html($this->options['service_body_4']); ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
                            <?php } ?>
                            </select>
                        </li> 
                        <li>
                            <label for="service_body_5">Service Body 5: </label>
                            <select class="service_body_select" id="service_body_5" name="service_body_5">
                            <?php if ($this_connected) { ?>
                                <option value="Not Used">Not Used</option>
                                <?php foreach($unique_areas as $unique_area){ ?>
                                    <?php $area_data = explode(',',$unique_area); ?>
                                    <?php $area_name = $area_data[0]; ?>
                                    <?php $area_id = $area_data[1]; ?>
                                    <?php $area_parent = $area_data[2]; ?>
                                    <?php $area_parent_name = $area_data[3]; ?>
                                    <?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
                                    <?php $is_data = explode(',',esc_html($this->options['service_body_5'])); ?>
                                    <?php if ( $is_data[0] != "Not Used" && $area_id == $is_data[1] ) { ?>
                                        <option selected="selected" value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $unique_area ?>"><?php echo $option_description ?></option>
                                    <?php } ?>
                                <?php } ?>
                            <?php } else { ?>
                                <option selected="selected" value="<?php echo esc_html($this->options['service_body_5']); ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
                            <?php } ?>
                            </select>
                        </li> 
                    </ul>
                    <div>
                        <input type="checkbox" name="recurse_service_bodies" value="1" <?php echo ($this->options['recurse_service_bodies'] == 1 ? 'checked' : '') ?> /> Recurse Service Bodies
                    </div>
                </div>
            </div>
            <div id="customquery" class="postbox">
                <h3 class="hndle">Custom Query<span title='<p>This will be executed as part of the meeting search query.  This will override any setting in the Service Body dropdowns.' class="tooltip"></span></h3>
                <div class="inside">
                    <label for="custom_query">Custom Query: </label>
                    <input type="text" id="custom_query" name="custom_query" size="100" value="<?php echo esc_html($this->options['custom_query'])?>" />
                </div>
            </div>
            <div id="extrameetingsdiv" class="postbox">
                <h3 class="hndle">Include Extra Meetings<span title='<p>Include Extra Meetings from Another Service Body.</p><p>All Meetings from your BMLT Server are shown in the list.</p><p>The Meetings you select will be merged into your meeting list.</p><p><em>Note: Be sure to select all meetings for each group.</em>' class="tooltip"></span></h3>
                <div class="inside">
                    <p class="ctrl_key" style="display:none; color: #00AD00;">Hold CTRL Key down to select multiple meetings.</p>
                    <select class="chosen-select" style="width: 100%;" data-placeholder="<?php
						if ($this->options['extra_meetings_enabled'] == 0) {
							echo 'Not Enabled';
						} elseif (!$this_connected) {
							echo 'Not Connected';
						} else {
							echo 'Select Extra Meetings';
						} ?>" id="extra_meetings" name="extra_meetings[]" multiple="multiple">
                    <?php
						if ($this_connected && $this->options['extra_meetings_enabled'] == 1) {
                    		$extra_meetings_array = $this->get_all_meetings();
                        	foreach($extra_meetings_array as $extra_meeting) {
								$extra_meeting_x = explode('|||',$extra_meeting);
								$extra_meeting_id = $extra_meeting_x[3];
								$extra_meeting_display = substr($extra_meeting_x[0], 0, 30) . ';' . $extra_meeting_x[1] . ';' . $extra_meeting_x[2]; ?>
                            <option <?php echo ($this->options['extra_meetings'] != '' && in_array($extra_meeting_id, $this->options['extra_meetings']) ? 'selected="selected"' : '') ?> value="<?php echo $extra_meeting_id ?>"><?php echo esc_html($extra_meeting_display) ?></option>
							<?php
                        	}
                    	} ?>
                    </select>
                    <p>Hint: Type a group name, weekday or area to narrow down your choices.</p>
					<div>
						<input type="checkbox" name="extra_meetings_enabled" value="1" <?php echo ($this->options['extra_meetings_enabled'] == 1 ? 'checked' : '') ?> /> Extra Meetings Enabled
					</div>
                </div>
                
            </div>
            <div id="currentmeetinglistlinkdiv" class="postbox">
                <h3 class="hndle">Current Meeting List Link<span title='<p>Share the "Current Meeting List Link" on your website, email, etc to generate this meeting list.</p>' class="tooltip"></span></h3>
                <div class="inside">
                    <p><a target="_blank" href='<?php echo home_url() ?>/?current-meeting-list=1'><?php echo home_url() ?>/?current-meeting-list=1</a></p>
                </div>
            </div>
            <div id="meetinglistcachediv" class="postbox">
                <h3 class="hndle">Meeting List Cache (<?php echo $this->count_transient_cache(); ?> Cached Entries)<span title='<p>Meeting List data is cached (as database transient) to generate a Meeting List faster.</p><p><i>CACHE is DELETED when you Save Changes.</i></p><p><b>The meeting list will not reflect changes to BMLT until the cache expires or is deleted.</b></p>' class="tooltip"></span></h3>
                <div class="inside">
                    <?php global $_wp_using_ext_object_cache; ?>
                    <?php if ( $_wp_using_ext_object_cache ) { ?>
                        <p>This site is using an external object cache.</p>
                    <?php } ?>
                    <ul>
                        <li>
                            <label for="cache_time">Cache Time: </label>
                            <input class="bmlt-input-field" id="cache_time" onKeyPress="return numbersonly(this, event)" type="number" min="0" max="999" size="3" maxlength="3" name="cache_time" value="<?php echo esc_html($this->options['cache_time']) ;?>" />&nbsp;&nbsp;<i>0 - 999 Hours (0 = disable cache)</i>&nbsp;&nbsp;
                        </li>
                    </ul>
                    <p><i><b>CACHE is DELETED when you Save Changes.</b></i></p>
                </div>
            </div>
        </div>
    <input type="submit" value="Save Changes" id="bmltmeetinglistsave1" name="bmltmeetinglistsave" class="button-primary" />
    <?php echo '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
    <div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
    <br class="clear">
    </div>
</div>
