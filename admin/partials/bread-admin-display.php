<?php
if (! defined('ABSPATH')) {
    exit;
}
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
include_once '_bmlt_server_setup.php';
include_once '_layout_setup.php';
include_once '_front_page_setup.php';
include_once '_meetings_setup.php';
include_once '_custom_section_setup.php';
include_once '_backup_restore_setup.php';
include_once '_custom_fonts_setup.php';
class Bread_AdminDisplay
{

    private Bread_Admin $admin;
    private $connected;
    private $server_version;
    private Bread $bread;
    private array $unique_areas;

    function __construct($admin)
    {
        $this->admin = $admin;
        $this->bread = $admin->get_bread_instance();
    }
    public function getBreadInstance()
    {
        return $this->bread;
    }
    public function getAdmin()
    {
        return $this->admin;
    }
    public function getServerVersion()
    {
        return $this->server_version;
    }
    function admin_options_page($filename = '')
    {
        ?>
        <div class="connecting"></div>
        <div class="saving"></div>
        <?php
        if (!empty($filename)) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p style="color: #000;">'.esc_html(__('File loaded: ', 'bread')).esc_html($filename).'</p>';
            echo '</div>';
            delete_transient($this->bread->get_TransientKey($this->bread->getRequestedSetting()));
        }

        if (isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave']) {
            if (!$this->admin->current_user_can_modify()) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p style="color: #F00;">'.esc_html(__('You do not have permission to save this configuation!', 'bread')).'</p>';
                echo '</div>';
            } else {
                $this->admin->save_admin_options();
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p style="color: #000;">'.esc_html(__('Your changes were successfully saved!', 'bread')).'</p>';
                $num = delete_transient($this->bread->get_TransientKey($this->bread->getRequestedSetting()));
                if ($num > 0) {
                    /* translators: string is number of cache entries deleted */
                    echo "<p>" . esc_html(sprintf(__('%s Cache entries deleted', 'bread'), esc_attr($num), 'bread'))."</p>";
                }
                echo '</div>';
            }
        }

        $this->bread->fillUnsetOptions();
        $dir = str_starts_with(get_locale(), 'fa') ? 'rtl' : 'ltr';
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/bread/bmlt-meeting-list.php');
        ?>
        <div class="hide wrap bmlt-<?php echo esc_attr($dir) ?>" id="meeting-list-tabs-wrapper" dir="<?php echo esc_attr($dir); ?>">
            <div id="tallyBannerContainer">
                <img id="tallyBannerImage" src="<?php echo esc_url(plugin_dir_url(__FILE__) . "../css/images/banner.png") ?>">
            </div>
            <div id="meeting-list-tabs">
                <ul class="nav">
                    <li><a href="#instructions"><?php esc_html_e('Getting Started', 'bread'); ?></a></li>
                    <li><a href="#editor" id='click-customizer'><?php esc_html_e('Customizer', 'bread'); ?></a></li>
                    <li><a href="#custom-fonts"><?php esc_html_e('Custom Fonts', 'bread'); ?></a></li>
                    <li><a href="#import-export"><?php esc_html_e('Backup/ Restore', 'bread'); ?></a></li>
                </ul>
                <div id="instructions">
                <?php Bread_meeting_list_setup_page_render($this); ?>
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
                        <div id="tabs-first" class="tab-content">
        <?php Bread_bmlt_server_setup_page_render($this); ?>
                        </div>
                        <div id="layout" class="tab-content">
        <?php Bread_layout_setup_page_render($this); ?>
                        </div>
                        <div id="front-page" class="tab-content">
        <?php Bread_front_page_setup_page_render($this); ?>
                        </div>
                        <div id="meetings" class="tab-content">
        <?php Bread_meetings_setup_page_render($this); ?>
                        </div>
                        <div id="custom-section" class="tab-content">
        <?php Bread_custom_section_setup_page_render($this) ?>
                        </div>
        <?php if ($this->admin->current_user_can_modify()) { ?>
                            <input type="submit" value="<?php esc_html_e('Save Changes', 'bread') ?>" id="bmltmeetinglistsave" name="bmltmeetinglistsave" class="button-primary gears-working" />
                            <input type="submit" value="<?php esc_html_e('Preview', 'bread') ?>" id="bmltmeetinglistpreview" name="bmltmeetinglistpreview" class="button-primary" formtarget="_blank" />
                            <p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="<?php echo esc_url(home_url() . "/?current-meeting-list=" . $this->bread->getRequestedSetting()); ?>"><?php esc_html_e('Generate Meeting List', 'bread')?></a></p>
                            <div style="display:inline;"><i>&nbsp;&nbsp;<?php esc_html_e('Save Changes before Generating Meeting List.', 'bread') ?></i></div>
                            <br class="clear">
        <?php } ?>
                    </form>
                </div>
                <div id="custom-fonts">
        <?php Bread_custom_fonts_setup_page_render($this); ?>
                </div>
                <div id="import-export">
        <?php Bread_backup_restore_setup_page_render($this); ?>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
