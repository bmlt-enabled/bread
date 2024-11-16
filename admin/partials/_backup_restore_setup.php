<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="exportdiv" class="postbox">
            <h3 class="hndle">Configuration Manager</h3>
                <div class="inside">
                    <p><?php _e('bread can support multiple meeting lists.  Each meeting list has an integer ID and a text description that help the user to identify '); ?>
                       <?php _e('the configuration (or \'settings\') that will be used to generate the meeting list.  The ID of the configuration is used in the link ');?>
                       <?php _e('that generates the meeting list (eg, ?current-meeting-list=2 generates the meeting list with ID 2.')?></p>
                    <h4>Current Meeting List</h4>
                    <form method="post">
                        <p>Meeting List ID: <?php echo $this->admin->loaded_setting?>
                        <input type="hidden" name="pwsix_action" value="rename_setting" /><input type="hidden" name="current-meeting-list" value="<?php echo $this->admin->loaded_setting?>" /></p>
                        <?php wp_nonce_field('pwsix_rename_nonce', 'pwsix_rename_nonce'); ?>
                        <br>Configuration Name: <input type="text" name="setting_descr" value="<?php echo Bread::getSettingName($this->admin->loaded_setting)?>" />
                        <?php submit_button(__('Save Configuration Name'), 'button-primary', 'submit', false); ?>
                    </form></p>
                    <?php if (count(Bread::getSettingNames())>1) {?>
                    <h4>Configuration Selection</h4>
                    <form method="post">
                        <label for="current_setting">Select Configuration: </label>
                        <select class="setting_select" id="setting_select" name="current-meeting-list">
                        <?php foreach (Bread::getSettingNames() as $aKey => $aDescr) { ?>
                              <option <?php echo (($aKey==$this->admin->loaded_setting)?'selected':"") ?> value="<?php echo $aKey ?>"><?php echo $aKey.': '.$aDescr ?></option>
                        <?php } ?>
                        </select>
                        <?php wp_nonce_field('pwsix_load_nonce', 'pwsix_load_nonce'); ?>
                        <?php submit_button(__('Load Configuration'), 'button-primary', 'submit', false); ?>
                    </form>
                    <?php }?>
                    <hr/>
                    <form method="post">
                        <p><input type="hidden" name="pwsix_action" value="settings_admin" /><input type="hidden" name="current-meeting-list" value="<?php echo $this->admin->loaded_setting?>" /></p></p>
                        <?php wp_nonce_field('pwsix_settings_admin_nonce', 'pwsix_settings_admin_nonce'); ?>
                        <?php submit_button(__('Duplicate Current Configuration'), 'button-primary', 'duplicate', false); ?>
                        <?php if (1 < $this->admin->loaded_setting) {?>
                            <?php submit_button(__('Delete Current Configuration'), 'button-primary', 'delete', false); ?>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="exportdiv" class="postbox">
                <h3 class="hndle">Export Configuration</h3>
                <div class="inside">
                    <p><?php _e('Export or backup meeting list settings.'); ?></p>
                    <p><?php _e('This allows you to easily import meeting list settings into another site.'); ?></p>
                    <p><?php _e('Also useful for backing up before making significant changes to the meeting list settings.'); ?></p>
                    <form method="post">
                        <p><input type="hidden" name="pwsix_action" value="export_settings" /><input type="hidden" name="current-meeting-list" value="<?php echo $this->admin->loaded_setting?>" /></p>
                        <p>
                            <?php wp_nonce_field('pwsix_export_nonce', 'pwsix_export_nonce'); ?>
                            <?php submit_button(__('Export'), 'button-primary', 'submit', false); ?>
                        </p>
                    </form>
                </div>
            </div>
            <div style="margin-bottom: 0px;" id="exportdiv" class="postbox">
                <h3 class="hndle">Import Configuration</h3>
                <div class="inside">
                    <p><?php _e('Import meeting list settings from a previously exported meeting list.'); ?></p>
                    <form id="form_import_file" method="post" enctype="multipart/form-data">
                        <p><input type="file" required name="import_file"/></p>
                        <p>
                        <input type="hidden" name="pwsix_action" value="import_settings" /><input type="hidden" name="current-meeting-list" value="<?php echo $this->admin->loaded_setting?>" />
                           <?php wp_nonce_field('pwsix_import_nonce', 'pwsix_import_nonce'); ?>
                            <?php submit_button(__('Import'), 'button-primary', 'submit_import_file', false, array( 'id' => 'submit_import_file' )); ?>
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
    <p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="<?php echo home_url();?>/?current-meeting-list=<?php echo $this->admin->loaded_setting;?>">Generate Meeting List</a></p>
</div>
