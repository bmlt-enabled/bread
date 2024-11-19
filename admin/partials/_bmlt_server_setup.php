<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}
$all_users = get_users();
$specific_users = array();

foreach ($all_users as $user) {
    if ($user->has_cap('manage_bread')) {
        $specific_users[] = $user;
    }
}
?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="bmltrootserverurl" class="postbox">
                <h3 class="hndle">BMLT Server<span class="my-tooltip" title='<p>Visit <a target="_blank" href="https://doihavethebmlt.org/">BMLT Server Implementations</a> to find your BMLT server</p>'><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <p>
                        <label for="root_server">BMLT Server URL: </label>
                        <input class="bmlt-input" id="root_server" type="text" name="root_server" value="<?php echo Bread::getOption('root_server'); ?>" />
                    </p>
                    <?php
                    if ($this->connected) {
                        echo $this->server_version;
                        echo '<input type="hidden" id="user_agent" value="' . Bread::getOption('user_agent') . '" />';
                        if (Bread::getOption('sslverify') == '1') { ?>
                            <p>
                                <input type="checkbox" id="sslverify" name="sslverify" value="1" checked />
                                <label for="sslverify">Disable SSL verification of server</label>
                        <?php }
                    } elseif (Bread::emptyOption('root_server')) {
                        echo "<span style='color: #f00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>ERROR: Please enter a BMLT Server</span>";
                        echo '<input type="hidden" id="user_agent" value="' . Bread::getOption('user_agent') . '" />';
                        if (Bread::getOption('sslverify') == '1') { ?>
                            <p>
                                <input type="checkbox" id="sslverify" name="sslverify" value="1" checked />
                                <label for="sslverify">Disable SSL verification of server</label></p>
                        <?php }
                    } else {
                        ?><span style='color: #f00;'>
                                <div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>ERROR: Problem Connecting to BMLT Server<br /><?php echo Bread_Bmlt::$connection_error; ?>
                            </span>
                            <p>
                                <label for="user_agent">Try a different user agent or "None" for Wordpress default: "</label>
                                <input class="bmlt-input" id="user_agent" type="text" name="user_agent" value="<?php echo Bread::getOption('user_agent'); ?>" />
                            </p><p>
                                <input type="checkbox" id="sslverify" name="sslverify" value="1" <?php echo Bread::getOption('sslverify') ? 'checked' : ''; ?> />
                                <label for="sslverify">Disable SSL verification of server</label>
                            </p>
                        <?php
                    }
                    ?>
                        <p>
                            <input type="checkbox" id="use_aggregator" name="use_aggregator" value="1" />
                            <label for="use_aggregator">Use Aggregator &#127813;</label>
                            <span title='<p>The aggregator collects meeting data <br/>from all known root servers and pretends to be one large server</p><p>This can be useful to use if you want to display meetings outside <br/>of your server, for instance a statewide listing where the state <br/>covers multiple root servers<br/>Another good use case is if you want to display meetings by users<br/> location</p>' class="tooltip"></span>
                        </p>
                        <ul><?php $this->select_service_bodies();?></ul>
                        <div>
                            <input type="checkbox" name="recurse_service_bodies" value="1" <?php echo (Bread::getOption('recurse_service_bodies') == 1 ? 'checked' : '') ?> /> Recurse Service Bodies
                        </div>
                </div>
            </div>
            <div id="customquery" class="postbox">
                <div style="display:none;">
                    <div id="customquery-tooltip-content"><p>
                        This will be executed as part of the meeting search query.  This will override any setting in the Service Body dropdowns.
                        <br/>You can get help formulating a query using your sites <a href="<?php echo Bread::getOption('root_server');?>/semantic">semantic interface</a>.</p>
                    </div>
                </div>
                <h3 class="hndle">Custom Query<span data-tooltip-content="#customquery-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <label for="custom_query">Custom Query: </label>
                    <input type="text" id="custom_query" name="custom_query" size="100" value="<?php echo esc_html(Bread::getOption('custom_query')) ?>" />
                </div>
            </div>
            <div id="extrameetingsdiv" class="postbox">
            <div style="display:none;">
                    <div id="extrameetings-tooltip-content">
                    <p>Include Extra Meetings from Another Service Body.</p>
                    <p>All Meetings from your BMLT Server are shown in the list.</p>
                    <p>The Meetings you select will be merged into your meeting list.</p>
                    <p><em>Note: Be sure to select all meetings for each group.</em></p>
                    </div>
                </div>
                <h3 class="hndle">Include Extra Meetings<span class="my-tooltip" data-tooltip-content="#extrameetings-tooltip-content"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <p class="ctrl_key" style="display:none; color: #00AD00;">Hold CTRL Key down to select multiple meetings.</p>
                    <select class="chosen-select" style="width: 100%;" data-placeholder="<?php
                    if (Bread::getOption('extra_meetings_enabled') == 0) {
                        echo 'Not Enabled';
                    } elseif (!$this->connected) {
                        echo 'Not Connected';
                    } else {
                        echo 'Select Extra Meetings';
                    } ?>" id="extra_meetings" name="extra_meetings[]" multiple="multiple">
                        <?php
                        if ($this->connected && Bread::getOption('extra_meetings_enabled') == 1) {
                            $extra_meetings_array = Bread_Bmlt::get_all_meetings();
                            foreach ($extra_meetings_array as $extra_meeting) {
                                $extra_meeting_x = explode('|||', $extra_meeting);
                                $extra_meeting_id = trim(Bread::arraySafeGet($extra_meeting_x, 3));
                                $extra_meeting_display = substr(Bread::arraySafeGet($extra_meeting_x), 0, 30) . ';' . Bread::arraySafeGet($extra_meeting_x, 1) . ';' . Bread::arraySafeGet($extra_meeting_x, 2); ?>
                                <option <?php echo (Bread::getOption('extra_meetings') != '' && in_array($extra_meeting_id, Bread::getOption('extra_meetings')) ? 'selected="selected"' : '') ?> value="<?php echo $extra_meeting_id ?>"><?php echo esc_html($extra_meeting_display) ?></option>
                                <?php
                            }
                        } ?>
                    </select>
                    <p>Hint: Type a group name, weekday or area to narrow down your choices.</p>
                    <div>
                        <input type="checkbox" name="extra_meetings_enabled" value="1" <?php echo (!Bread::emptyOption('extra_meetings_enabled') && Bread::getOption('extra_meetings_enabled') == 1 ? 'checked' : '') ?> /> Extra Meetings Enabled
                    </div>
                </div>

            </div>
            <div id="currentmeetinglistlinkdiv" class="postbox">
                <h3 class="hndle">Current Meeting List Link<span title='<p>Share the "Current Meeting List Link" on your website, email, etc to generate this meeting list.</p>' class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <p><a target="_blank" href='<?php echo home_url() ?>/?current-meeting-list=<?php echo $this->admin->loaded_setting ?>'><?php echo home_url() ?>/?current-meeting-list=<?php echo $this->admin->loaded_setting ?></a></p>
                </div>
            </div>
            <div id="currentmeetinglistauthordiv" class="postbox">
                <h3 class="hndle">Meeting List Author(s)</h3>
                <div class="inside">
                    <select id="author_chosen" name="authors_select[]" multiple>
                        <?php foreach ($specific_users as $user) { ?>
                            <option value="<?php echo $user->ID ?>" <?php echo in_array($user->ID, Bread::getOption('authors')) ? 'selected=' : '' ?>><?php echo $user->user_firstname ?> <?php echo $user->user_lastname ?> (<?php echo $user->user_login ?>) </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div id="meetinglistcachediv" class="postbox">
                <h3 class="hndle">Meeting List Cache<span title='<p>Meeting List data is cached (as database transient) to generate a Meeting List faster.</p><p><i>CACHE is DELETED when you Save Changes.</i></p><p><b>The meeting list will not reflect changes to BMLT until the cache expires or is deleted.</b></p>' class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <?php global $_wp_using_ext_object_cache; ?>
                    <?php if ($_wp_using_ext_object_cache) { ?>
                        <p>This site is using an external object cache.</p>
                    <?php } ?>
                    <ul>
                        <li>
                            <label for="cache_time">Cache Time: </label>
                            <input class="bmlt-input-field" id="cache_time" type="number" min="0" max="999" size="3" maxlength="3" name="cache_time" value="<?php echo esc_html(Bread::getOption('cache_time')); ?>" />&nbsp;&nbsp;<i>0 - 999 Hours (0 = disable cache)</i>&nbsp;&nbsp;
                        </li>
                    </ul>
                    <p><i><b>CACHE is DELETED when you Save Changes.</b></i></p>
                </div>
            </div>
        </div>
    </div>
</div>