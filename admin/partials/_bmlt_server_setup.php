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
                <h3 class="hndle"><?php esc_html_e('BMLT Server', 'bread') ?><span class="my-tooltip" title='<p>Visit <a target="_blank" href="https://doihavethebmlt.org/">BMLT Server Implementations</a> to find your BMLT server</p>'><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <p>
                        <label for="root_server"><?php esc_html_e('BMLT Server URL: ', 'bread') ?></label>
                        <input class="bmlt-input" id="root_server" type="text" name="root_server" value="<?php echo esc_url($this->bread->getOption('root_server')); ?>" />
                    </p>
                    <?php
                    if ($this->connected) {
                        echo wp_kses($this->server_version, 'post');
                        echo '<input type="hidden" id="user_agent" value="' . esc_html($this->bread->getOption('user_agent')) . '" />';
                        if ($this->bread->getOption('sslverify') == '1') { ?>
                            <p>
                                <input type="checkbox" id="sslverify" name="sslverify" value="1" checked />
                                <label for="sslverify"><?php esc_html_e('Disable SSL verification of server', 'bread') ?></label>
                        <?php }
                    } elseif ($this->bread->emptyOption('root_server')) {
                        echo "<span style='color: #f00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>".esc_html(__('ERROR: Please enter a BMLT Server', 'bread'))."</span>";
                        echo '<input type="hidden" id="user_agent" value="' . esc_html($this->bread->getOption('user_agent')) . '" />';
                        if ($this->bread->getOption('sslverify') == '1') { ?>
                            <p>
                                <input type="checkbox" id="sslverify" name="sslverify" value="1" checked />
                                <label for="sslverify"><?php esc_html_e('Disable SSL verification of server', 'bread') ?></label>
                            </p>
                        <?php }
                    } else {
                        ?><span style='color: #f00;'>
                            <div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div><?php esc_html_e('ERROR: Problem Connecting to BMLT Server', 'bread') ?><br /><?php echo esc_html($this->bread->bmlt()->connection_error); ?>
                        </span>
                        <p>
                            <label for="user_agent"><?php esc_html_e('Try a different user agent or "None" for Wordpress default: ', 'bread') ?></label>
                            <input class="bmlt-input" id="user_agent" type="text" name="user_agent" value="<?php echo esc_attr($this->bread->getOption('user_agent')); ?>" />
                        </p>
                        <p>
                            <input type="checkbox" id="sslverify" name="sslverify" value="1" <?php echo $this->bread->getOption('sslverify') ? 'checked' : ''; ?> />
                            <label for="sslverify"><?php esc_html_e('Disable SSL verification of server', 'bread') ?></label>
                        </p>
                        <?php
                    }
                    ?>
                    <p>
                        <input type="checkbox" id="use_aggregator" name="use_aggregator" value="1" />
                        <label for="use_aggregator"><?php esc_html_e('Use Aggregator &#127813;', 'bread') ?></label>
                        <span title='<p>The aggregator collects meeting data <br/>from all known root servers and pretends to be one large server</p><p>This can be useful to use if you want to display meetings outside <br/>of your server, for instance a statewide listing where the state <br/>covers multiple root servers<br/>Another good use case is if you want to display meetings by users<br/> location</p>' class="tooltip"></span>
                    </p>
                    <ul><?php $this->select_service_bodies(); ?></ul>
                    <div>
                        <input type="checkbox" name="recurse_service_bodies" value="1" <?php echo ($this->bread->getOption('recurse_service_bodies') == 1 ? 'checked' : '') ?> /> <?php esc_html_e('Recurse Service Bodies', 'bread') ?>
                    </div>
                </div>
            </div>
            <div id="customquery" class="postbox">
                <div style="display:none;">
                    <div id="customquery-tooltip-content">
                        <p>
                            <?php esc_html_e('This will be executed as part of the meeting search query. This will override any setting in the Service Body dropdowns.', 'bread') ?>
                            <br /><?php
                                /* translators: the string is a link to the semantic interface of the BMLT server */
                                echo esc_html(sprintf(__('You can get help formulating a query using your sites <a href="%s">semantic interface</a>.', 'bread'), esc_url($this->bread->getOption('root_server'))).'/semantic') ?>
                        </p>
                    </div>
                </div>
                <h3 class="hndle"><?php esc_html_e('Custom Query', 'bread') ?><span data-tooltip-content="#customquery-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <label for="custom_query"><?php esc_html_e('Custom Query: ', 'bread') ?></label>
                    <input type="text" id="custom_query" name="custom_query" size="100" value="<?php echo esc_attr($this->bread->getOption('custom_query')) ?>" />
                </div>
            </div>
            <div id="extrameetingsdiv" class="postbox">
                <div style="display:none;">
                    <div id="extrameetings-tooltip-content">
                        <p><?php esc_html_e('Include Extra Meetings from Another Service Body.', 'bread') ?></p>
                        <p><?php esc_html_e('All Meetings from your BMLT Server are shown in the list.', 'bread') ?></p>
                        <p><?php esc_html_e('The Meetings you select will be merged into your meeting list.', 'bread') ?></p>
                        <p><em><?php esc_html_e('Note: Be sure to select all meetings for each group.', 'bread') ?></em></p>
                    </div>
                </div>
                <h3 class="hndle"><?php esc_html_e('Include Extra Meetings', 'bread') ?><span class="my-tooltip" data-tooltip-content="#extrameetings-tooltip-content"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <?php if ($this->connected && $this->bread->getOption('extra_meetings_enabled') == 1) {?>
                        <select id="extra_meetings" class="chosen-select" id="extra_meetings" name="extra_meetings[]" multiple="multiple">
                        <?php
                            $extra_meetings_array = $this->bread->bmlt()->get_all_meetings();
                            echo "<option value=''>Select Extra Meetings</option>";
                        foreach ($extra_meetings_array as $id => $descr) {
                            $selected = $this->bread->getOption('extra_meetings') != '' && in_array($id, $this->bread->getOption('extra_meetings')) ? 'selected' : '';
                            echo "<option " . esc_attr($selected) . " value='" . esc_attr($id) . "'>" . esc_html($descr) . "</option>";
                        }
                        ?>
                        </select>
                        <p><?php esc_html_e('Hint: Type a group name, weekday or area to narrow down your choices.', 'bread') ?></p>
                    <?php }?>
                    <div>
                        <input type="checkbox" name="extra_meetings_enabled" value="1" <?php echo (!$this->bread->emptyOption('extra_meetings_enabled') && $this->bread->getOption('extra_meetings_enabled') == 1 ? 'checked' : '') ?> /><?php esc_html_e('Extra Meetings Enabled', 'bread') ?>
                    </div>
                </div>

            </div>
            <div id="currentmeetinglistlinkdiv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Current Meeting List Link', 'bread') ?><span title='<p>Share the "Current Meeting List Link" on your website, email, etc to generate this meeting list.</p>' class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <?php $meeting_list_url = home_url() . '/?current-meeting-list=' . $this->bread->getRequestedSetting() ?>
                    <p><a target="_blank" href='<?php echo esc_url($meeting_list_url) ?>'><?php echo esc_url($meeting_list_url); ?></a></p>
                </div>
            </div>
            <div id="currentmeetinglistauthordiv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Meeting List Author(s)', 'bread') ?></h3>
                <div class="inside">
                    <select id="author_chosen" name="authors_select[]" class="chosen-select" multiple>
                        <?php foreach ($specific_users as $user) { ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" <?php echo in_array($user->ID, $this->bread->getOption('authors')) ? 'selected' : '' ?>><?php echo esc_html($user->user_firstname . ' ' . $user->user_lastname . ' (' . $user->user_login . ')'); ?> </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div id="meetinglistloggingdiv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Optimize/Debug mPDF', 'bread') ?><span title='<p>If there are problens during meeting list generation, you may enable debugging to help locate the error.</p><p><i>Please disable if not actively involved in locating problems.</i></p>' class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <input type="checkbox" name="logging" id="logging" <?php echo $this->bread->emptyOption('logging') ? '' : 'checked'; ?>>
                    <label for="logging"><?php esc_html_e('Enable Logging', 'bread') ?></label>
                    <br/>
                    <input type="checkbox" name="simpleTables" id="simpleTables" <?php echo $this->bread->emptyOption('simpleTables') ? '' : 'checked'; ?>>
                    <label for="simpleTables"><?php esc_html_e('Enable SimpleTables', 'bread') ?></label>
                    <br/>
                    <input type="checkbox" name="packTabledata" id="packTabledata" <?php echo $this->bread->emptyOption('packTabledata') ? '' : 'checked'; ?>>
                    <label for="packTabledata"><?php esc_html_e('Pack Table Data', 'bread') ?></label>
                    <?php
                    $logs = Bread::get_log_files();
                    if (!empty($logs)) {?>
                        <br/><h4><?php esc_html_e('Download Log Files', 'bread') ?></h4>
                        <?php
                        foreach ($logs as $log) {
                            ?>
                            <a href="<?php echo esc_url(home_url().'/?export-mpdf-log='.$log['name']);?>"><?php echo esc_html($log['name']);?></a>
                            <?php
                        }
                    }
                    ?>
                    <br/>
                </div>
            </div>
            <div id="meetinglistcachediv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Meeting List Cache', 'bread') ?><span title='<p>Meeting List data is cached (as database transient) to generate a Meeting List faster.</p><p><i>CACHE is DELETED when you Save Changes.</i></p><p><b>The meeting list will not reflect changes to BMLT until the cache expires or is deleted.</b></p>' class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <?php global $_wp_using_ext_object_cache; ?>
                    <?php if ($_wp_using_ext_object_cache) { ?>
                        <p><?php esc_html_e('This site is using an external object cache.', 'bread') ?></p>
                    <?php } ?>
                    <ul>
                        <li>
                            <label for="cache_time"><?php esc_html_e('Cache Time: ', 'bread') ?></label>
                            <input class="bmlt-input-field" id="cache_time" type="number" min="0" max="999" size="3" maxlength="3" name="cache_time" value="<?php echo esc_html($this->bread->getOption('cache_time')); ?>" />&nbsp;&nbsp;<i><?php esc_html_e('0 - 999 Hours (0 = disable cache)', 'bread') ?></i>&nbsp;&nbsp;
                        </li>
                    </ul>
                    <p><i><b><?php esc_html_e('CACHE is DELETED when you Save Changes.', 'bread') ?></b></i></p>
                </div>
            </div>
        </div>
    </div>
</div>