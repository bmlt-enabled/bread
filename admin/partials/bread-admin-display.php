<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://bmlt.app
 * @since 2.8.0
 *
 * @package    Bread
 * @subpackage Bread/admin/partials
 */
class Bread_AdminDisplay
{
    private $lang;
    private Bread_Admin $admin;
    private $connected;
    private $server_version;
    private Bread $bread;
    private array $unique_areas;

    function __construct($admin)
    {
        $this->admin = $admin;
        $this->bread = $admin->get_bread_instance();
        $this->refresh_status();
    }
    private function refresh_status()
    {
        $serverInfo = $this->bread->bmlt()->testRootServer();
        $this->connected = is_array($serverInfo) && array_key_exists("version", $serverInfo[0]) ? $serverInfo[0]["version"] : '';
        if ($this->connected) {
            $this->unique_areas = $this->bread->bmlt()->get_areas();
            asort($this->unique_areas);
            if ($serverInfo[0]["aggregator_mode_enabled"] ?? false) {
                $this->server_version = "<span style='color: #00AD00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-admin-site'></div>Using Tomato Server</span>";
            } elseif ($this->connected) {
                $this->server_version = "<span style='color: #0A8ADD;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-smiley'></div>Your BMLT Server is running " . $this->connected . "</span>";
            }
        }
    }
    private function select_service_bodies()
    {
        for ($i = 1; $i <= 5; $i++) { ?>
            <li><label for="service_body_<?php echo esc_html($i); ?>">Service Body <?php echo esc_html($i); ?>: </label>
                <select class="service_body_select" id="service_body_<?php echo esc_html($i); ?>" name="service_body_<?php echo esc_html($i); ?>"><?php
                if ($this->connected) {
                    $this->select_service_body_options($i);
                } else { ?>
                        <option selected value="<?php esc_html($this->bread->getOption("service_body_$i")); ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option><?php
                } ?>
                </select>
            </li><?php
        }
    }
    private function select_service_body_options(int $i)
    {
        ?>
        <option value="Not Used">Not Used</option>
        <?php
        foreach ($this->unique_areas as $area) {
            $area_data = explode(',', $area);
            $area_name = $this->bread->arraySafeGet($area_data);
            $area_id = $this->bread->arraySafeGet($area_data, 1);
            $area_parent = $this->bread->arraySafeGet($area_data, 2);
            $area_parent_name = $this->bread->arraySafeGet($area_data, 3);
            $descr = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")";
            $selected = '';
            $sb = esc_html($this->bread->getOption("service_body_$i"));
            $area_selected = explode(',', $sb);
            if ($this->bread->arraySafeGet($area_selected) != "Not Used" && $area_id == $this->bread->arraySafeGet($area_selected, 1)) {
                $selected = 'selected';
            } ?>
            <option <?php echo esc_attr($selected); ?> value="<?php echo esc_html($area) ?>"><?php echo esc_html($descr) ?></option><?php
        }
    }
                                                                                                                            /**
                                                                                                                             * Main function for the admin page.
                                                                                                                             *
                                                                                                                             * @return void
                                                                                                                             */
    function admin_options_page()
    {
        $this->lang = $this->bread->bmlt()->get_bmlt_server_lang();
        ?>
        <div class="connecting"></div>
        <div class="saving"></div>
        <?php
        set_transient('admin_notice', 'Please put down your weapon. You have 20 seconds to comply.');
        echo '<div class="updated">';
        if (!$this->admin->current_user_can_modify()) {
            echo '<p style="color: #F00;">You do not have permission to save this configuation!</p>';
        } elseif (isset($_COOKIE['bread_import_file'])) {
            echo '<p style="color: #F00;">File loaded.</p>';
            delete_transient($this->bread->get_TransientKey($this->bread->getRequestedSetting()));
        } elseif (isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave']) {
            $this->admin->save_admin_options();
            echo '<p style="color: #F00;">Your changes were successfully saved!</p>';
            $num = delete_transient($this->bread->get_TransientKey($this->bread->getRequestedSetting()));
            if ($num > 0) {
                echo "<p>" . esc_attr($num) . " Cache entries deleted</p>";
            }
        }
        echo '</div>';

        $this->bread->fillUnsetOptions();
        ?>
        <div class="hide wrap" id="meeting-list-tabs-wrapper">
            <div id="tallyBannerContainer">
                <img id="tallyBannerImage" src="<?php echo esc_url(plugin_dir_url(__FILE__) . "../css/images/banner.png") ?>">
            </div>
            <div id="meeting-list-tabs">
                <ul class="nav">
                    <li><a href="#instructions"><?php esc_html_e('Getting Started', 'bread'); ?></a></li>
                    <li><a href="#editor" id='click-customizer'><?php esc_html_e('Customizer', 'bread'); ?></a></li>
                    <li><a href="#import-export"><?php esc_html_e('Backup/ Restore', 'bread'); ?></a></li>
                </ul>
                <div id="instructions">
        <?php include '_meeting_list_setup.php'; ?>
                </div>
                <div id="editor">
                    <nav class="nav-tab-wrapper">
                        <a href="#tabs-first" class="nav-tab nav-tab-active"><?php esc_html_e('BMLT Server', 'bread'); ?></a>
                        <a href="#layout" class="nav-tab"><?php esc_html_e('Page Layout', 'bread'); ?></a>
                        <a href="#front-page" class="nav-tab"><?php esc_html_e('Front Page', 'bread'); ?></a>
                        <a href="#meetings" class="nav-tab"><?php esc_html_e('Meetings', 'bread'); ?></a>
                        <a href="#custom-section" class="nav-tab"><?php esc_html_e('Custom Content', 'bread'); ?></a>
                    </nav>
                    <form style=" display:inline!important;" method="POST" id="bmlt_meeting_list_options">
                        <input type="hidden" name="current-meeting-list" value="<?php echo esc_attr($this->bread->getRequestedSetting()) ?>" />
        <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
        <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
        <?php wp_nonce_field('bmltmeetinglistupdate-options'); ?>
        <?php $this->refresh_status(); ?>
                        <div id="tabs-first" class="tab-content">
        <?php include '_bmlt_server_setup.php'; ?>
                        </div>
                        <div id="layout" class="tab-content">
        <?php include '_layout_setup.php'; ?>
                        </div>
                        <div id="front-page" class="tab-content">
        <?php include '_front_page_setup.php'; ?>
                        </div>
                        <div id="meetings" class="tab-content">
        <?php include '_meetings_setup.php'; ?>
                        </div>
                        <div id="custom-section" class="tab-content">
        <?php include '_custom_section_setup.php'; ?>
                        </div>
        <?php if ($this->admin->current_user_can_modify()) { ?>
                            <input type="submit" value="Save Changes" id="bmltmeetinglistsave" name="bmltmeetinglistsave" class="button-primary gears-working" />
                            <input type="submit" value="Preview" id="bmltmeetinglistpreview" name="bmltmeetinglistpreview" class="button-primary" formtarget="_blank" />
                            <p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="<?php echo esc_url(home_url() . "/?current-meeting-list=" . $this->bread->getRequestedSetting()); ?>">Generate Meeting List</a></p>
                            <div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generating Meeting List.</i></div>
                            <br class="clear">
        <?php } ?>
                    </form>
                </div>
                <div id="import-export">
        <?php include '_backup_restore_setup.php'; ?>
                </div>
            </div>
        </div>
        <div id="dialog" title="TinyMCE dialog" style="display: none">
            <textarea>test</textarea>
        </div>
        <?php
    }
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->