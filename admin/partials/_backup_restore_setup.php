<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}
function Bread_backup_restore_setup_page_render(Bread_AdminDisplay $breadAdmin)
{
    $bread = $breadAdmin->getBreadInstance();
    ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="exportdiv" class="postbox">
                <h3 class="hndle">Configuration Manager</h3>
                <div class="inside">
                    <p>Bread can support multiple meeting lists.  Each meeting list has an integer ID and a text description that help the user to identify
                        the configuration (or \'settings\') that will be used to generate the meeting list.  The ID of the configuration is used in the link
                        that generates the meeting list (eg, ?current-meeting-list=2 generates the meeting list with ID 2).</p>
                    <h4>Current Meeting List</h4>
                    <form method="post">
                        <p>Meeting List ID: <?php echo esc_html($bread->getRequestedSetting()) ?>
                            <input type="hidden" name="pwsix_action" value="rename_setting" /><input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($bread->getRequestedSetting()) ?>" />
                        </p>
                        <?php wp_nonce_field('pwsix_rename_nonce', 'pwsix_rename_nonce'); ?>
                        <br>Configuration Name: <input type="text" name="setting_descr" value="<?php echo esc_attr($bread->getSettingName($bread->getRequestedSetting())) ?>" />
                        <?php submit_button(esc_html__('Save Configuration Name', 'bread'), 'button-primary', 'submit_settings_name', false); ?>
                    </form>
                    </p>
                    <?php if (count($bread->getSettingNames()) > 1) { ?>
                        <h4>Configuration Selection</h4>
                        <form method="post">
                            <label for="current_setting">Select Configuration: </label>
                            <select class="setting_select" id="setting_select" name="current-meeting-list">
                                <?php foreach ($bread->getSettingNames() as $aKey => $aDescr) { ?>
                                    <option <?php echo (($aKey == $bread->getRequestedSetting()) ? 'selected' : "") ?> value="<?php echo esc_attr($aKey) ?>"><?php echo esc_html($aKey . ': ' . $aDescr); ?></option>
                                <?php } ?>
                            </select>
                            <?php wp_nonce_field('pwsix_load_nonce', 'pwsix_load_nonce'); ?>
                            <?php submit_button(esc_html__('Load Configuration', 'bread'), 'button-primary', 'submit_change_settings', false); ?>
                        </form>
                    <?php } ?>
                    <hr />
                    <form method="post">
                        <p><input type="hidden" name="pwsix_action" value="settings_admin" /><input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($bread->getRequestedSetting()) ?>" /></p>
                        </p>
                        <?php wp_nonce_field('pwsix_settings_admin_nonce', 'pwsix_settings_admin_nonce'); ?>
                        <?php submit_button(esc_html__('Duplicate Current Configuration', 'bread'), 'button-primary', 'duplicate', false); ?>
                        <?php if (1 < $bread->getRequestedSetting()) { ?>
                            <?php submit_button(esc_html__('Delete Current Configuration', 'bread'), 'button-primary', 'delete_settings', false); ?>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="exportdiv" class="postbox">
                <h3 class="hndle">Export Configuration</h3>
                <div class="inside">
                    <p><?php esc_html_e('Export or backup meeting list settings.', 'bread'); ?></p>
                    <p><?php esc_html_e('This allows you to easily import meeting list settings into another site.', 'bread'); ?></p>
                    <p><?php esc_html_e('Also useful for backing up before making significant changes to the meeting list settings.', 'bread'); ?></p>
                    <form method="post">
                        <p><input type="hidden" name="pwsix_action" value="export_settings" /><input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($bread->getRequestedSetting()) ?>" /></p>
                        <p>
                            <?php wp_nonce_field('pwsix_export_nonce', 'pwsix_export_nonce'); ?>
                            <?php submit_button(esc_html__('Export', 'bread'), 'button-primary', 'submit', false); ?>
                        </p>
                    </form>
                </div>
            </div>
            <div style="margin-bottom: 0px;" id="exportdiv" class="postbox">
                <h3 class="hndle">Import Configuration</h3>
                <div class="inside">
                    <p><?php esc_html_e('Import meeting list settings from a previously exported meeting list.', 'bread'); ?></p>
                    <form id="form_import_file" method="post" enctype="multipart/form-data">
                        <p><input type="file" required name="import_file" /></p>
                        <p>
                            <input type="hidden" name="pwsix_action" value="import_settings" /><input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($bread->getRequestedSetting()) ?>" />
                            <?php wp_nonce_field('pwsix_import_nonce', 'pwsix_import_nonce'); ?>
                            <?php submit_button(esc_html__('Import', 'bread'), 'button-primary', 'submit_import_file', false, array('id' => 'submit_import_file')); ?>
                        </p>
                        <div id="basicModal">
                            <p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
                            <p>Consider backing up your settings by using the Export function.</p>
                        </div>
                        <div id="nofileModal" title="File Missing">
                            <div style="color:#f00;">Please Choose a File.</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br class="clear">
    <p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="<?php echo esc_url(home_url()."/?current-meeting-list=".$bread->getRequestedSetting()); ?>">Generate Meeting List</a></p>
</div>
<?php }