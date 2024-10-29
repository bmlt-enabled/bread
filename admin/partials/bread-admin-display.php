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
    private $admin;
    function __construct($admin)
    {
        $this->admin = $admin;
    }
        /**
         * Adds settings/options page
         */
    function admin_options_page()
    {
        Bread::getMLOptions(Bread::getRequestedSetting());
        $this->lang = Bread_bmlt::get_bmlt_server_lang();
        ?>
            <div class="connecting"></div>
            <div class="saving"></div>
            <div style="display:none;">
                <form method="POST" id="three_column_default_settings" name="three_column_default_settings" enctype="multipart/form-data">
                <?php wp_nonce_field('pwsix_submit_three_column', 'pwsix_submit_three_column'); ?>
                    <input type="hidden" name="pwsix_action" value="three_column_default_settings" />
                    <input type="hidden" name="current-meeting-list" value="<?php echo $this->admin->loaded_setting?>" />
                    <div id="basicModal1">
                        <p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
                        <p>Consider backing up your settings by using the Configuration Tab.</p>
                    </div>
                </form>
                <form method="POST" id="four_column_default_settings" name="four_column_default_settings" enctype="multipart/form-data">
                <?php wp_nonce_field('pwsix_submit_four_column', 'pwsix_submit_four_column'); ?>
                    <input type="hidden" name="pwsix_action" value="four_column_default_settings" />
                    <input type="hidden" name="current-meeting-list" value="<?php echo $this->admin->loaded_setting?>" />
                    <div id="basicModal2">
                        <p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
                        <p>Consider backing up your settings by using the Configuration Tab.</p>
                    </div>
                </form>
                <form method="POST" id="booklet_default_settings" name="booklet_default_settings" enctype="multipart/form-data">
                <?php wp_nonce_field('pwsix_submit_booklet', 'pwsix_submit_booklet'); ?>
                    <input type="hidden" name="pwsix_action" value="booklet_default_settings" />
                    <input type="hidden" name="current-meeting-list" value="<?php echo $this->admin->loaded_setting?>" />
                    <div id="basicModal3">
                        <p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
                        <p>Consider backing up your settings by using the Configuration Tab.</p>
                    </div>
                </form>
            </div>
            <?php
            if (!isset($_POST['bmltmeetinglistsave'])) {
                $_POST['bmltmeetinglistsave'] = false;
            }
            if ($_POST['bmltmeetinglistsave']) {
                if (!wp_verify_nonce($_POST['_wpnonce'], 'bmltmeetinglistupdate-options')) {
                    die('Whoops! There was a problem with the data you posted. Please go back and try again.');
                }
                Bread::setOption('bread_version', sanitize_text_field($_POST['bread_version']));
                Bread::setOption('front_page_content', wp_kses_post($_POST['front_page_content']));
                Bread::setOption('last_page_content', wp_kses_post($_POST['last_page_content']));
                Bread::setOption('front_page_line_height', $_POST['front_page_line_height']);
                Bread::setOption('front_page_font_size', floatval($_POST['front_page_font_size']));
                Bread::setOption('last_page_font_size', floatval($_POST['last_page_font_size']));
                Bread::setOption('last_page_line_height', floatval($_POST['last_page_line_height']));
                Bread::setOption('content_font_size', floatval($_POST['content_font_size']));
                Bread::setOption('suppress_heading', floatval($_POST['suppress_heading']));
                Bread::setOption('header_font_size', floatval($_POST['header_font_size']));
                Bread::setOption('header_text_color', sanitize_hex_color($_POST['header_text_color']));
                Bread::setOption('header_background_color', sanitize_hex_color($_POST['header_background_color']));
                Bread::setOption('header_uppercase', intval($_POST['header_uppercase']));
                Bread::setOption('header_bold', intval($_POST['header_bold']));
                Bread::setOption('sub_header_shown', sanitize_text_field($_POST['sub_header_shown']));
                Bread::setOption('cont_header_shown', intval($_POST['cont_header_shown']));
                Bread::setOption(
                    'column_gap',
                    isset($_POST['column_gap']) ?
                    intval($_POST['column_gap']) : 5
                );
                Bread::setOption('margin_right', intval($_POST['margin_right']));
                Bread::setOption('margin_left', intval($_POST['margin_left']));
                Bread::setOption('margin_bottom', intval($_POST['margin_bottom']));
                Bread::setOption('margin_top', intval($_POST['margin_top']));
                Bread::setOption('margin_header', intval($_POST['margin_header']));
                Bread::setOption(
                    'margin_footer',
                    isset($_POST['margin_footer']) ?
                    intval($_POST['margin_footer']): 5
                );
                Bread::setOption('pageheader_fontsize', floatval($_POST['pageheader_fontsize']));
                Bread::setOption('pageheader_textcolor', sanitize_hex_color($_POST['pageheader_textcolor']));
                Bread::setOption('pageheader_backgroundcolor', sanitize_hex_color($_POST['pageheader_backgroundcolor']));
                Bread::setOption('pageheader_content', wp_kses_post($_POST['pageheader_content']));
                Bread::setOption('watermark', sanitize_text_field($_POST['watermark']));
                Bread::setOption('page_size', sanitize_text_field($_POST['page_size']));
                Bread::setOption('page_orientation', sanitize_text_field($_POST['page_orientation']));
                Bread::setOption('page_fold', sanitize_text_field($_POST['page_fold']));
                Bread::setOption(
                    'booklet_pages',
                    isset($_POST['booklet_pages']) ?
                    boolval($_POST['booklet_pages']): false
                );
                Bread::setOption('meeting_sort', sanitize_text_field($_POST['meeting_sort']));
                Bread::setOption('main_grouping', sanitize_text_field($_POST['main_grouping']));
                Bread::setOption('subgrouping', sanitize_text_field($_POST['subgrouping']));
                Bread::setOption('borough_suffix', sanitize_text_field($_POST['borough_suffix']));
                Bread::setOption('county_suffix', sanitize_text_field($_POST['county_suffix']));
                Bread::setOption('neighborhood_suffix', sanitize_text_field($_POST['neighborhood_suffix']));
                Bread::setOption('city_suffix', sanitize_text_field($_POST['city_suffix']));
                Bread::setOption('meeting_template_content', wp_kses_post($_POST['meeting_template_content']));
                Bread::setOption('asm_template_content', wp_kses_post($_POST['asm_template_content']));
                Bread::setOption(
                    'column_line',
                    isset($_POST['column_line']) ?
                    boolval($_POST['column_line']) : 0
                );
                Bread::setOption(
                    'col_color',
                    isset($_POST['col_color']) ?
                    sanitize_hex_color($_POST['col_color']) : '#bfbfbf'
                );
                Bread::setOption('custom_section_content', wp_kses_post($_POST['custom_section_content']));
                Bread::setOption('custom_section_line_height', floatval($_POST['custom_section_line_height']));
                Bread::setOption('custom_section_font_size', floatval($_POST['custom_section_font_size']));
                Bread::setOption(
                    'pagenumbering_font_size',
                    isset($_POST['pagenumbering_font_size']) ?
                    floatval($_POST['pagenumbering_font_size']) : '9'
                );
                Bread::setOption('used_format_1', sanitize_text_field($_POST['used_format_1']));
                Bread::setOption('include_meeting_email', isset($_POST['include_meeting_email']) ? boolval($_POST['include_meeting_email']) : false);
                Bread::setOption('recurse_service_bodies', isset($_POST['recurse_service_bodies']) ? 1 : 0);
                Bread::setOption('extra_meetings_enabled', isset($_POST['extra_meetings_enabled']) ? intval($_POST['extra_meetings_enabled']) : 0);
                Bread::setOption('include_protection', boolval($_POST['include_protection']));
                Bread::setOption('weekday_language', sanitize_text_field($_POST['weekday_language']));
                Bread::setOption('asm_language', sanitize_text_field($_POST['asm_language']));
                Bread::setOption('weekday_start', sanitize_text_field($_POST['weekday_start']));
                Bread::setOption(
                    'meeting1_footer',
                    isset($_POST['meeting1_footer']) ?
                    sanitize_text_field($_POST['meeting1_footer']) : ''
                );
                Bread::setOption(
                    'meeting2_footer',
                    isset($_POST['meeting2_footer']) ?
                    sanitize_text_field($_POST['meeting2_footer']) :''
                );
                Bread::setOption(
                    'nonmeeting_footer',
                    isset($_POST['nonmeeting_footer']) ?
                    sanitize_text_field($_POST['nonmeeting_footer']):''
                );
                Bread::setOption('include_asm', boolval($_POST['include_asm']));
                Bread::setOption('asm_format_key', sanitize_text_field($_POST['asm_format_key']));
                Bread::setOption('asm_sort_order', sanitize_text_field($_POST['asm_sort_order']));
                Bread::setOption('bmlt_login_id', sanitize_text_field($_POST['bmlt_login_id']));
                Bread::setOption('bmlt_login_password', sanitize_text_field($_POST['bmlt_login_password']));
                Bread::setOption('base_font', sanitize_text_field($_POST['base_font']));
                Bread::setOption('colorspace', sanitize_text_field($_POST['colorspace']));
                Bread::setOption('wheelchair_size', sanitize_text_field($_POST['wheelchair_size']));
                Bread::setOption('protection_password', sanitize_text_field($_POST['protection_password']));
                Bread::setOption('time_clock', sanitize_text_field($_POST['time_clock']));
                Bread::setOption('time_option', intval($_POST['time_option']));
                Bread::setOption('remove_space', boolval($_POST['remove_space']));
                Bread::setOption('content_line_height', floatval($_POST['content_line_height']));
                Bread::setOption('root_server', sanitize_url($_POST['root_server']));
                Bread::setOption('service_body_1', sanitize_text_field($_POST['service_body_1']));
                Bread::setOption('service_body_2', sanitize_text_field($_POST['service_body_2']));
                Bread::setOption('service_body_3', sanitize_text_field($_POST['service_body_3']));
                Bread::setOption('service_body_4', sanitize_text_field($_POST['service_body_4']));
                Bread::setOption('service_body_5', sanitize_text_field($_POST['service_body_5']));
                Bread::setOption('cache_time', intval($_POST['cache_time']));
                Bread::setOption('custom_query', sanitize_text_field($_POST['custom_query']));
                Bread::setOption('asm_custom_query', sanitize_text_field($_POST['asm_custom_query']));
                Bread::setOption('user_agent', isset($_POST['user_agent']) ? sanitize_text_field($_POST['user_agent']) : 'None');
                Bread::setOption('sslverify', isset($_POST['sslverify']) ? '1' : '0');
                Bread::setOption('extra_meetings', array());
                if (isset($_POST['extra_meetings'])) {
                    foreach ($_POST['extra_meetings'] as $extra) {
                        Bread::setOption('extra_meetings', wp_kses_post($extra));
                    }
                }
                $authors = $_POST['authors_select'];
                Bread::setOption('authors', array());
                foreach ($authors as $author) {
                    Bread::setOption('authors', intval($author));
                }
                $user = wp_get_current_user();
                if (!in_array($user->ID, Bread::getOption('authors'))) {
                    Bread::setOption('authors', $user->ID);
                }
                set_transient('admin_notice', 'Please put down your weapon. You have 20 seconds to comply.');
                if (!$this->admin->current_user_can_modify()) {
                    echo '<div class="updated"><p style="color: #F00;">You do not have permission to save this configuation!</p>';
                } else {
                    $this->admin->save_admin_options();
                    echo '<div class="updated"><p style="color: #F00;">Your changes were successfully saved!</p>';
                    $num = delete_transient(Bread::get_TransientKey($this->admin->loaded_setting));
                    if ($num > 0) {
                        echo "<p>$num Cache entries deleted</p>";
                    }
                }
                echo '</div>';
            } elseif (isset($_REQUEST['pwsix_action']) && $_REQUEST['pwsix_action'] == "import_settings") {
                echo '<div class="updated"><p style="color: #F00;">Your file was successfully imported!</p></div>';
                $num = delete_transient(Bread::get_TransientKey($this->admin->loaded_setting));
            } elseif (isset($_REQUEST['pwsix_action']) && $_REQUEST['pwsix_action'] == "default_settings_success") {
                echo '<div class="updated"><p style="color: #F00;">Your default settings were successfully updated!</p></div>';
                $num = delete_transient(Bread::get_TransientKey($this->admin->loaded_setting));
            }
            global $wpdb;
            $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid LIKE '%default_nalogo.jpg%'";
            if ($wpdb->get_var($query) == 0) {
                $url = plugin_dir_url(__FILE__) . "includes/default_nalogo.jpg";
                media_sideload_image($url, 0);
            }
            Bread::fillUnsetOptions();
            ?>
            <?php include '_help_videos.php'; ?>
            <div class="hide wrap" id="meeting-list-tabs-wrapper">
                <div id="tallyBannerContainer">
                    <img id="tallyBannerImage" src="<?php echo plugin_dir_url(__FILE__)?>css/images/banner.png">
                </div>
                <div id="meeting-list-tabs">
                    <ul class="nav">
                        <li><a href="#setup"><?php _e('Meeting List Setup', 'root-server'); ?></a></li>
                        <li><a href="#tabs-first"><?php _e('BMLT Server', 'root-server'); ?></a></li>
                        <li><a href="#layout"><?php _e('Page Layout', 'root-server'); ?></a></li>
                        <li><a href="#front-page"><?php _e('Front Page', 'root-server'); ?></a></li>
                        <li><a href="#meetings"><?php _e('Meetings', 'root-server'); ?></a></li>
                        <li><a href="#custom-section"><?php _e('Custom Content', 'root-server'); ?></a></li>
                        <li><a href="#last-page"><?php _e('Last Page', 'root-server'); ?></a></li>
                        <li><a href="#import-export"><?php _e('Configuration', 'root-server'); ?></a></li>
                    </ul>
                    <form style=" display:inline!important;" method="POST" id="bmlt_meeting_list_options">
                    <input type="hidden" name="current-meeting-list" value="<?php echo $this->admin->loaded_setting?>" />
                <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
                <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
                <?php
                wp_nonce_field('bmltmeetinglistupdate-options');
                $serverInfo = Bread_Bmlt::testRootServer();
                $this_connected = is_array($serverInfo) && array_key_exists("version", $serverInfo[0]) ? $serverInfo[0]["version"] : '';
                $bmlt_version = $this_connected;
                if ($serverInfo[0]["aggregator_mode_enabled"] ?? false) {
                    $ThisVersion = "<span style='color: #00AD00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-admin-site'></div>Using Tomato Server</span>";
                } else {
                    $this_version = intval(str_replace(".", "", $this_connected));
                    $connect = "<p><div style='color: #f00;font-size: 16px;vertical-align: middle;' class='dashicons dashicons-no'></div><span style='color: #f00;'>Connection to BMLT Server Failed.  Check spelling or try again.  If you are certain spelling is correct, BMLT Server could be down.</span></p>";
                    if ($this_connected) {
                        $ThisVersion = "<span style='color: #0A8ADD;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-smiley'></div>Your BMLT Server is running ".$bmlt_version."</span>";
                    }
                }
                ?>
                    <div id="setup">
                    <?php include '_meeting_list_setup.php'; ?>
                    </div>
                    <div id="tabs-first">
                    <?php include '_bmlt_server_setup.php'; ?>
                    </div>
                    <div id="layout">
                    <?php include '_layout_setup.php'; ?>
                    </div>
                    <div id="front-page">
                    <?php include '_front_page_setup.php'; ?>
                    </div>
                    <div id="meetings">
                    <?php include '_meetings_setup.php'; ?>
                    </div>
                    <div id="custom-section">
                    <?php include '_custom_section_setup.php'; ?>
                    </div>
                    <div id="last-page">
                    <?php include '_last_page_setup.php'; ?>
                    </div>
                    </form>
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