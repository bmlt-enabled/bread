<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="exportdiv" class="postbox">
                <h3 class="hndle"><?php _e('Configuration Manager', 'bread-domain') ?></h3>
                <div class="inside">
                    <p><?php _e("Bread can support multiple meeting lists.  Each meeting list has an integer ID and a text description that help the user to identify
                        the configuration (or \'settings\') that will be used to generate the meeting list.  The ID of the configuration is used in the link
                        that generates the meeting list (eg, ?current-meeting-list=2 generates the meeting list with ID 2).", 'bread-domain') ?></p>
                    <h4><?php _e('Current Meeting List', 'bread-domain') ?></h4>
                    <form method="post">
                        <p><?php _e('Meeting List ID: ', 'bread-domain') ?><?php echo esc_html($this->bread->getRequestedSetting()) ?>
                            <input type="hidden" name="pwsix_action" value="rename_setting" /><input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($this->bread->getRequestedSetting()) ?>" />
                        </p>
                        <?php wp_nonce_field('pwsix_rename_nonce', 'pwsix_rename_nonce'); ?>
                        <br><?php _e('Configuration Name: ', 'bread-domain') ?><input type="text" name="setting_descr" value="<?php echo esc_attr($this->bread->getSettingName($this->bread->getRequestedSetting())) ?>" />
                        <?php submit_button(__('Save Configuration Name', 'bread-domain'), 'button-primary', 'submit_settings_name', false); ?>
                    </form>
                    </p>
                    <?php if (count($this->bread->getSettingNames()) > 1) { ?>
                        <h4><?php _e('Configuration Selection', 'bread-domain') ?></h4>
                        <form method="post">
                            <label for="current_setting"><?php _e('Select Configuration: ', 'bread-domain') ?></label>
                            <select class="setting_select" id="setting_select" name="current-meeting-list">
                                <?php foreach ($this->bread->getSettingNames() as $aKey => $aDescr) { ?>
                                    <option <?php echo (($aKey == $this->bread->getRequestedSetting()) ? 'selected' : "") ?> value="<?php echo esc_attr($aKey) ?>"><?php echo esc_html($aKey . ': ' . $aDescr); ?></option>
                                <?php } ?>
                            </select>
                            <?php wp_nonce_field('pwsix_load_nonce', 'pwsix_load_nonce'); ?>
                            <?php submit_button(__('Load Configuration', 'bread-domain'), 'button-primary', 'submit_change_settings', false); ?>
                        </form>
                    <?php } ?>
                    <hr />
                    <form method="post">
                        <p><input type="hidden" name="pwsix_action" value="settings_admin" /><input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($this->bread->getRequestedSetting()) ?>" /></p>
                        </p>
                        <?php wp_nonce_field('pwsix_settings_admin_nonce', 'pwsix_settings_admin_nonce'); ?>
                        <?php submit_button(esc_html__('Duplicate Current Configuration', 'bread-domain'), 'button-primary', 'duplicate', false); ?>
                        <?php if (1 < $this->bread->getRequestedSetting()) { ?>
                            <?php submit_button(esc_html__('Delete Current Configuration', 'bread-domain'), 'button-primary', 'delete_settings', false); ?>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="exportdiv" class="postbox">
                <h3 class="hndle"><?php _e('Export Configuration', 'bread-domain') ?></h3>
                <div class="inside">
                    <p><?php _e('Export or backup meeting list settings.', 'bread-domain'); ?></p>
                    <p><?php _e('This allows you to easily import meeting list settings into another site.', 'bread-domain'); ?></p>
                    <p><?php _e('Also useful for backing up before making significant changes to the meeting list settings.', 'bread-domain'); ?></p>
                    <form method="post">
                        <p><input type="hidden" name="pwsix_action" value="export_settings" /><input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($this->bread->getRequestedSetting()) ?>" /></p>
                        <p>
                            <?php wp_nonce_field('pwsix_export_nonce', 'pwsix_export_nonce'); ?>
                            <?php submit_button(__('Export', 'bread-domain'), 'button-primary', 'submit', false); ?>
                        </p>
                    </form>
                </div>
            </div>
            <div style="margin-bottom: 0px;" id="exportdiv" class="postbox">
                <h3 class="hndle"><?php _e('Import Configuration', 'bread-domain'); ?></h3>
                <div class="inside">
                    <p><?php _e('Import meeting list settings from a previously exported meeting list.', 'bread-domain'); ?></p>
                    <form id="form_import_file" method="post" enctype="multipart/form-data">
                        <p><input type="file" required name="import_file" /></p>
                        <p>
                            <input type="hidden" name="pwsix_action" value="import_settings" /><input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($this->bread->getRequestedSetting()) ?>" />
                            <?php wp_nonce_field('pwsix_import_nonce', 'pwsix_import_nonce'); ?>
                            <?php submit_button(__('Import', 'bread-domain'), 'button-primary', 'submit_import_file', false, array('id' => 'submit_import_file')); ?>
                        </p>
                        <div id="basicModal">
                            <p style="color:#f00;"><?php _e('Your current meeting list settings will be replaced and lost forever.', 'bread-domain'); ?></p>
                            <p><?php _e('Consider backing up your settings by using the Export function.', 'bread-domain') ?></p>
                        </div>
                        <div id="nofileModal" title="File Missing">
                            <div style="color:#f00;"><?php _e('Please Choose a File.', 'bread-domain'); ?></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br class="clear">
    <p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="<?php echo esc_url(home_url()."/?current-meeting-list=".$this->bread->getRequestedSetting()); ?>"><?php _e('Generate Meeting List', 'bread-domain'); ?></a></p>
</div>