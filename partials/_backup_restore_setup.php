<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="exportdiv" class="postbox">
                <h3 class="hndle">Export Meeting List Settings</h3>
                <div class="inside">
                    <p><?php _e( 'Export or backup meeting list settings.' ); ?></p>
                    <p><?php _e( 'This allows you to easily import meeting list settings into another site.' ); ?></p>
                    <p><?php _e( 'Also useful for backing up before making significant changes to the meeting list settings.' ); ?></p>
                    <form method="post">
                        <p><input type="hidden" name="pwsix_action" value="export_settings" /></p>
                        <p>
                            <?php wp_nonce_field( 'pwsix_export_nonce', 'pwsix_export_nonce' ); ?>
                            <?php submit_button( __( 'Export' ), 'button-primary', 'submit', false ); ?>
                        </p>
                    </form>
                </div>
            </div>
            <div style="margin-bottom: 0px;" id="exportdiv" class="postbox">
                <h3 class="hndle">Import Meeting List Settings</h3>
                <div class="inside">
                    <p><?php _e( 'Import meeting list settings from a previously exported meeting list.' ); ?></p>
                    <form id="form_import_file" method="post" enctype="multipart/form-data">
                        <p><input type="file" required name="import_file"/></p>
                        <p>
                            <input type="hidden" name="pwsix_action" value="import_settings" />
                            <?php wp_nonce_field( 'pwsix_import_nonce', 'pwsix_import_nonce' ); ?>
                            <?php submit_button( __( 'Import' ), 'button-primary', 'submit_import_file', false, array( 'id' => 'submit_import_file' ) ); ?>
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
</div>