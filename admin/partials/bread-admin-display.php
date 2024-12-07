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
            <li><label for="service_body_<?php echo $i; ?>">Service Body <?php echo $i; ?>: </label>
                <select class="service_body_select" id="service_body_<?php echo $i; ?>" name="service_body_<?php echo $i; ?>"><?php
                if ($this->connected) {
                    $this->select_service_body_options($i);
                } else { ?>
                        <option selected="selected" value="<?php esc_html($this->bread->getOption("service_body_$i")); ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option><?php
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
                $selected = 'selected = "selected"';
            } ?>
            <option <?php echo $selected; ?> value="<?php echo $area ?>"><?php echo $descr ?></option><?php
        }
    }
    /**
     * Main function for the admin page.
     *
     * @return void
     */
    function admin_options_page()
    {
        $this->bread->getConfigurationForSettingId($this->bread->getRequestedSetting());
        $this->lang = $this->bread->bmlt()->get_bmlt_server_lang();
        ?>
        <div class="connecting"></div>
        <div class="saving"></div>
        <?php
        if (!isset($_POST['bmltmeetinglistsave'])) {
            $_POST['bmltmeetinglistsave'] = false;
        }
        if (!isset($_POST['bmltmeetinglistpreview'])) {
            $_POST['bmltmeetinglistpreview'] = false;
        }
        if ($_POST['bmltmeetinglistsave'] || $_POST['bmltmeetinglistpreview']) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'bmltmeetinglistupdate-options')) {
                die('Whoops! There was a problem with the data you posted. Please go back and try again.');
            }
            $this->bread->setOption('bread_version', sanitize_text_field($_POST['bread_version']));
            $this->bread->setOption('front_page_content', wp_kses_post($_POST['front_page_content']));
            $this->bread->setOption('front_page_line_height', $_POST['front_page_line_height']);
            $this->bread->setOption('front_page_font_size', floatval($_POST['front_page_font_size']));
            $this->bread->setOption('content_font_size', floatval($_POST['content_font_size']));
            $this->bread->setOption('suppress_heading', floatval($_POST['suppress_heading']));
            $this->bread->setOption('header_font_size', floatval($_POST['header_font_size']));
            $this->bread->setOption('header_text_color', sanitize_hex_color($_POST['header_text_color']));
            $this->bread->setOption('header_background_color', sanitize_hex_color($_POST['header_background_color']));
            $this->bread->setOption('header_uppercase', intval($_POST['header_uppercase']));
            $this->bread->setOption('header_bold', intval($_POST['header_bold']));
            $this->bread->setOption('sub_header_shown', sanitize_text_field($_POST['sub_header_shown']));
            $this->bread->setOption('cont_header_shown', intval($_POST['cont_header_shown']));
            $this->bread->setOption(
                'column_gap',
                isset($_POST['column_gap']) ?
                    intval($_POST['column_gap']) : 5
            );
            $this->bread->setOption('margin_right', intval($_POST['margin_right']));
            $this->bread->setOption('margin_left', intval($_POST['margin_left']));
            $this->bread->setOption('margin_bottom', intval($_POST['margin_bottom']));
            $this->bread->setOption('margin_top', intval($_POST['margin_top']));
            $this->bread->setOption('margin_header', intval($_POST['margin_header']));
            $this->bread->setOption(
                'margin_footer',
                isset($_POST['margin_footer']) ?
                    intval($_POST['margin_footer']) : 5
            );
            $this->bread->setOption('pageheader_fontsize', floatval($_POST['pageheader_fontsize']));
            $this->bread->setOption('pageheader_textcolor', sanitize_hex_color($_POST['pageheader_textcolor']));
            $this->bread->setOption('pageheader_backgroundcolor', sanitize_hex_color($_POST['pageheader_backgroundcolor']));
            $this->bread->setOption('pageheader_content', wp_kses_post($_POST['pageheader_content']));
            $this->bread->setOption('watermark', sanitize_text_field($_POST['watermark']));
            $this->bread->setOption('page_size', sanitize_text_field($_POST['page_size']));
            $this->bread->setOption('page_orientation', sanitize_text_field($_POST['page_orientation']));
            $this->bread->setOption('page_fold', sanitize_text_field($_POST['page_fold']));
            $this->bread->setOption(
                'booklet_pages',
                isset($_POST['booklet_pages']) ?
                    boolval($_POST['booklet_pages']) : false
            );
            $this->bread->setOption('meeting_sort', sanitize_text_field($_POST['meeting_sort']));
            $this->bread->setOption('main_grouping', sanitize_text_field($_POST['main_grouping']));
            $this->bread->setOption('subgrouping', sanitize_text_field($_POST['subgrouping']));
            $this->bread->setOption('borough_suffix', sanitize_text_field($_POST['borough_suffix']));
            $this->bread->setOption('county_suffix', sanitize_text_field($_POST['county_suffix']));
            $this->bread->setOption('neighborhood_suffix', sanitize_text_field($_POST['neighborhood_suffix']));
            $this->bread->setOption('city_suffix', sanitize_text_field($_POST['city_suffix']));
            $this->bread->setOption('meeting_template_content', wp_kses_post($_POST['meeting_template_content']));
            $this->bread->setOption('additional_list_template_content', wp_kses_post($_POST['additional_list_template_content']));
            $this->bread->setOption(
                'column_line',
                isset($_POST['column_line']) ?
                    boolval($_POST['column_line']) : 0
            );
            $this->bread->setOption(
                'col_color',
                isset($_POST['col_color']) ?
                    sanitize_hex_color($_POST['col_color']) : '#bfbfbf'
            );
            $this->bread->setOption('custom_section_content', wp_kses_post($_POST['custom_section_content']));
            $this->bread->setOption('custom_section_line_height', floatval($_POST['custom_section_line_height']));
            $this->bread->setOption('custom_section_font_size', floatval($_POST['custom_section_font_size']));
            $this->bread->setOption(
                'pagenumbering_font_size',
                isset($_POST['pagenumbering_font_size']) ?
                    floatval($_POST['pagenumbering_font_size']) : '9'
            );
            $this->bread->setOption('used_format_1', sanitize_text_field($_POST['used_format_1']));
            $this->bread->setOption('recurse_service_bodies', isset($_POST['recurse_service_bodies']) ? 1 : 0);
            $this->bread->setOption('extra_meetings_enabled', isset($_POST['extra_meetings_enabled']) ? intval($_POST['extra_meetings_enabled']) : 0);
            $this->bread->setOption('include_protection', boolval($_POST['include_protection']));
            $this->bread->setOption('weekday_language', sanitize_text_field($_POST['weekday_language']));
            $this->bread->setOption('additional_list_language', sanitize_text_field($_POST['additional_list_language']));
            $this->bread->setOption('weekday_start', sanitize_text_field($_POST['weekday_start']));
            $this->bread->setOption(
                'meeting1_footer',
                isset($_POST['meeting1_footer']) ?
                    sanitize_text_field($_POST['meeting1_footer']) : ''
            );
            $this->bread->setOption(
                'meeting2_footer',
                isset($_POST['meeting2_footer']) ?
                    sanitize_text_field($_POST['meeting2_footer']) : ''
            );
            $this->bread->setOption(
                'nonmeeting_footer',
                isset($_POST['nonmeeting_footer']) ?
                    sanitize_text_field($_POST['nonmeeting_footer']) : ''
            );
            $this->bread->setOption('include_additional_list', boolval($_POST['include_additional_list']));
            $this->bread->setOption('additional_list_format_key', sanitize_text_field($_POST['additional_list_format_key']));
            $this->bread->setOption('additional_list_sort_order', sanitize_text_field($_POST['additional_list_sort_order']));
            $this->bread->setOption('base_font', sanitize_text_field($_POST['base_font']));
            $this->bread->setOption('colorspace', sanitize_text_field($_POST['colorspace']));
            $this->bread->setOption('wheelchair_size', sanitize_text_field($_POST['wheelchair_size']));
            $this->bread->setOption('protection_password', sanitize_text_field($_POST['protection_password']));
            $this->bread->setOption('time_clock', sanitize_text_field($_POST['time_clock']));
            $this->bread->setOption('time_option', intval($_POST['time_option']));
            $this->bread->setOption('remove_space', boolval($_POST['remove_space']));
            $this->bread->setOption('content_line_height', floatval($_POST['content_line_height']));
            $this->bread->setOption('root_server', sanitize_url($_POST['root_server']));
            $this->bread->setOption('service_body_1', sanitize_text_field($_POST['service_body_1']));
            $this->bread->setOption('service_body_2', sanitize_text_field($_POST['service_body_2']));
            $this->bread->setOption('service_body_3', sanitize_text_field($_POST['service_body_3']));
            $this->bread->setOption('service_body_4', sanitize_text_field($_POST['service_body_4']));
            $this->bread->setOption('service_body_5', sanitize_text_field($_POST['service_body_5']));
            $this->bread->setOption('cache_time', intval($_POST['cache_time']));
            $this->bread->setOption('custom_query', sanitize_text_field($_POST['custom_query']));
            $this->bread->setOption('additional_list_custom_query', sanitize_text_field($_POST['additional_list_custom_query']));
            $this->bread->setOption('user_agent', isset($_POST['user_agent']) ? sanitize_text_field($_POST['user_agent']) : 'None');
            $this->bread->setOption('sslverify', isset($_POST['sslverify']) ? '1' : '0');
            $this->bread->setOption('extra_meetings', array());
            if (isset($_POST['extra_meetings'])) {
                foreach ($_POST['extra_meetings'] as $extra) {
                    $this->bread->setOption('extra_meetings', wp_kses_post($extra));
                }
            }
            $authors = isset($_POST['author_chosen']) ? $_POST['author_chosen'] : [];
            $this->bread->setOption('authors', array());
            foreach ($authors as $author) {
                $this->bread->appendOption('authors', intval($author));
            }
            $user = wp_get_current_user();
            if (!is_array($this->bread->getOption('authors'))) {
                $this->bread->setOption('authors', array($this->bread->getOption('authors')));
            }
            if (!in_array($user->ID, $this->bread->getOption('authors'))) {
                $this->bread->setOption('authors', $user->ID);
            }
            if ($_POST['bmltmeetinglistpreview']) {
                session_start();
                $_SESSION['bread_preview_settings'] = $this->bread->getOptions();
                wp_redirect(home_url() . "?preview-meeting-list=1");
                exit();
            }
            set_transient('admin_notice', 'Please put down your weapon. You have 20 seconds to comply.');
            if (!$this->admin->current_user_can_modify()) {
                echo '<div class="updated"><p style="color: #F00;">You do not have permission to save this configuation!</p>';
            } else {
                $this->admin->save_admin_options();
                echo '<div class="updated"><p style="color: #F00;">Your changes were successfully saved!</p>';
                $num = delete_transient($this->bread->get_TransientKey($this->bread->getRequestedSetting()));
                if ($num > 0) {
                    echo "<p>$num Cache entries deleted</p>";
                }
            }
            echo '</div>';
        } elseif (isset($_REQUEST['pwsix_action']) && $_REQUEST['pwsix_action'] == "import_settings") {
            echo '<div class="updated"><p style="color: #F00;">Your file was successfully imported!</p></div>';
            $num = delete_transient($this->bread->get_TransientKey($this->bread->getRequestedSetting()));
        } elseif (isset($_REQUEST['pwsix_action']) && $_REQUEST['pwsix_action'] == "default_settings_success") {
            echo '<div class="updated"><p style="color: #F00;">Your default settings were successfully updated!</p></div>';
            $num = delete_transient($this->bread->get_TransientKey($this->bread->getRequestedSetting()));
        }
        $this->bread->fillUnsetOptions();
        ?>
        <div class="hide wrap" id="meeting-list-tabs-wrapper">
            <div id="tallyBannerContainer">
                <img id="tallyBannerImage" src="<?php echo plugin_dir_url(__FILE__) ?>../css/images/banner.png">
            </div>
            <div id="meeting-list-tabs">
                <ul class="nav">
                    <li><a href="#instructions"><?php _e('Getting Started', 'root-server'); ?></a></li>
                    <li><a href="#editor" id='click-customizer'><?php _e('Customizer', 'root-server'); ?></a></li>
                    <li><a href="#import-export"><?php _e('Backup/ Restore', 'root-server'); ?></a></li>
                </ul>
                <div id="instructions">
        <?php include '_meeting_list_setup.php'; ?>
                </div>
                <div id="editor">
                    <nav class="nav-tab-wrapper">
                        <a href="#tabs-first" class="nav-tab nav-tab-active"><?php _e('BMLT Server', 'root-server'); ?></a>
                        <a href="#layout" class="nav-tab"><?php _e('Page Layout', 'root-server'); ?></a>
                        <a href="#front-page" class="nav-tab"><?php _e('Front Page', 'root-server'); ?></a>
                        <a href="#meetings" class="nav-tab"><?php _e('Meetings', 'root-server'); ?></a>
                        <a href="#custom-section" class="nav-tab"><?php _e('Custom Content', 'root-server'); ?></a>
                    </nav>
                    <form style=" display:inline!important;" method="POST" id="bmlt_meeting_list_options">
                        <input type="hidden" name="current-meeting-list" value="<?php echo $this->bread->getRequestedSetting() ?>" />
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
                            <p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="<?php echo home_url(); ?>/?current-meeting-list=<?php echo $this->bread->getRequestedSetting(); ?>">Generate Meeting List</a></p>
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