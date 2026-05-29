<?php
if (! defined('ABSPATH')) {
    exit;
}
function Bread_bmlt_server_setup_page_render(Bread_AdminDisplay $breadAdmin)
{
    $bread = $breadAdmin->getBreadInstance();

    $all_users = get_users();
    $specific_users = array();

    foreach ($all_users as $user) {
        if ($user->has_cap('manage_bread')) {
            $specific_users[] = $user;
        }
    }
    ?>
<script type="text/javascript">
    window.bread_admin = {};
    window.bread_admin.root_server = '<?php echo esc_js($bread->getOption('root_server')); ?>';
    window.bread_admin.service_bodies_selected = <?php echo json_encode($bread->getOption('service_bodies')); ?>.map(x => x.split(',')[1]);
    window.bread_admin.extra_meetings = <?php echo json_encode($bread->getOption('extra_meetings')); ?>;
    window.bread_admin.used_format = '<?php echo esc_js($bread->getOption('used_format_1')); ?>';
    window.bread_admin.additional_list_format_key = '<?php echo esc_js($bread->getOption('additional_list_format_key')); ?>';
</script>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="bmltrootserverurl" class="postbox">
                <h3 class="hndle"><?php esc_html_e('BMLT Server', 'bread') ?><span class="my-tooltip" title='<p>Visit <a target="_blank" href="https://doihavethebmlt.org/">BMLT Server Implementations</a> to find your BMLT server</p>'><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <p>
                        <label for="root_server"><?php esc_html_e('BMLT Server URL: ', 'bread') ?></label>
                        <input class="bmlt-input" id="root_server" type="text" name="root_server" value="<?php echo esc_url($bread->getOption('root_server')); ?>"
                            onKeypress="root_server_keypress(event)" onChange="test_root_server()" />
                        <span id="connected_message" style="display:none; color:green;">
                            <span style='font-size: 16px;vertical-align: text-top;' class='dashicons dashicons-yes'></span>
                            Version: <span id="server_version"></span>
                        </span>
                        <span id="disconnected_message" style="display:none; color:red;">
                            <span style='font-size: 16px;vertical-align: text-top;' class='dashicons dashicons-no'></span>
                            <?php esc_html_e('ERROR: Problem Connecting to BMLT Server', 'bread') ?>
                        </span>
                    </p>
                    <p>
                        <input type="checkbox" id="use_aggregator" name="use_aggregator" value="1" />
                        <label for="use_aggregator"><?php esc_html_e('Use Aggregator &#127813;', 'bread') ?></label>
                        <span title='<p>The aggregator collects meeting data <br/>from all known root servers and pretends to be one large server</p><p>This can be useful to use if you want to display meetings outside <br/>of your server, for instance a statewide listing where the state <br/>covers multiple root servers<br/>Another good use case is if you want to display meetings by users<br/> location</p>' class="tooltip"></span>
                    </p>
                    <p>
                        <label for="service_bodies"><?php esc_html_e('Service Bodies: ', 'bread') ?></label>
                        <select class="bread-select" style="width: 400px;" data-placeholder="<?php esc_html_e('Choose up to 5 service bodies', 'bread') ?>" id="service_bodies" name="service_bodies[]" multiple="multiple">
                            <?php esc_html_e('Select Service Bodies', 'bread'); ?>">
                        </select>
                    </p>
                    <div>
                        <input type="checkbox" name="recurse_service_bodies" id="recurse_service_bodies" value="1" <?php echo ($bread->getOption('recurse_service_bodies') == 1 ? 'checked' : '') ?> /> <?php esc_html_e('Recurse Service Bodies', 'bread') ?>
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
                                echo esc_html(sprintf(__('You can get help formulating a query using your sites <a href="%s">semantic interface</a>.', 'bread'), esc_url($bread->getOption('root_server'))).'/semantic') ?>
                        </p>
                    </div>
                </div>
                <h3 class="hndle"><?php esc_html_e('Custom Query', 'bread') ?><span data-tooltip-content="#customquery-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <label for="custom_query"><?php esc_html_e('Custom Query: ', 'bread') ?></label>
                    <input type="text" id="custom_query" name="custom_query" size="100" value="<?php echo esc_attr($bread->getOption('custom_query')) ?>" />
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
                    <?php if ($breadAdmin->isConnected() && $bread->getOption('extra_meetings_enabled') == 1) {?>
                        <select class="bread-select" style="width: 400px;" data-placeholder="<?php esc_html_e('Extra Meetings', 'bread') ?>" id="extra_meetings" name="extra_meetings[]" multiple="multiple">
                        </select>
                        <p><?php esc_html_e('Hint: Type a group name, weekday or area to narrow down your choices.', 'bread') ?></p>
                    <?php }?>
                    <div>
                        <input type="checkbox" id="extra_meetings_enabled" name="extra_meetings_enabled" value="1" <?php echo (!$bread->emptyOption('extra_meetings_enabled') && $bread->getOption('extra_meetings_enabled') == 1 ? 'checked' : '') ?> /><?php esc_html_e('Extra Meetings Enabled', 'bread') ?>
                    </div>
                </div>

            </div>
            <div id="currentmeetinglistlinkdiv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Current Meeting List Link', 'bread') ?><span title='<p>Share the "Current Meeting List Link" on your website, email, etc to generate this meeting list.</p>' class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <?php $meeting_list_url = home_url() . '/?current-meeting-list=' . $bread->getRequestedSetting() ?>
                    <p><a target="_blank" href='<?php echo esc_url($meeting_list_url) ?>'><?php echo esc_url($meeting_list_url); ?></a></p>
                </div>
            </div>
            <div id="currentmeetinglistauthordiv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Meeting List Author(s)', 'bread') ?></h3>
                <div class="inside">
                    <select id="bread_author_select" name="authors_select[]" class="bread-select" multiple>
                        <?php foreach ($specific_users as $user) { ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" <?php echo in_array($user->ID, $bread->getOption('authors')) ? 'selected' : '' ?>><?php echo esc_html($user->user_firstname . ' ' . $user->user_lastname . ' (' . $user->user_login . ')'); ?> </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div id="meetinglistloggingdiv" class="postbox">
                <h3 class="hndle"><?php esc_html_e('Optimize/Debug mPDF', 'bread') ?><span title='<p>If there are problens during meeting list generation, you may enable debugging to help locate the error.</p><p><i>Please disable if not actively involved in locating problems.</i></p>' class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <input type="checkbox" name="logging" id="logging" <?php echo $bread->emptyOption('logging') ? '' : 'checked'; ?>>
                    <label for="logging"><?php esc_html_e('Enable Logging', 'bread') ?></label>
                    <br/>
                    <input type="checkbox" name="simpleTables" id="simpleTables" <?php echo $bread->emptyOption('simpleTables') ? '' : 'checked'; ?>>
                    <label for="simpleTables"><?php esc_html_e('Enable SimpleTables', 'bread') ?></label>
                    <br/>
                    <input type="checkbox" name="packTabledata" id="packTabledata" <?php echo $bread->emptyOption('packTabledata') ? '' : 'checked'; ?>>
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
                            <input class="bmlt-input-field" id="cache_time" type="number" min="0" max="999" size="3" maxlength="3" name="cache_time" value="<?php echo esc_html($bread->getOption('cache_time')); ?>" />&nbsp;&nbsp;<i><?php esc_html_e('0 - 999 Hours (0 = disable cache)', 'bread') ?></i>&nbsp;&nbsp;
                        </li>
                    </ul>
                    <p><i><b><?php esc_html_e('CACHE is DELETED when you Save Changes.', 'bread') ?></b></i></p>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
}