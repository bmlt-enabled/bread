<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * Process requests coming from the admin UI: customizing the configuration, setting up a configuration from the wizard, etc.
 *
 * There is also a lot of code here setting up the TimyMCE editors for Bread.
 *
 * @package    Bread
 * @subpackage Bread/admin
 * @author     bmlt-enabled <help@bmlt.app>
 */
include_once plugin_dir_path(__FILE__) . 'partials/_meeting_list_setup.php';
class Bread_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since  2.8.0
     * @access private
     * @var    string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since  2.8.0
     * @access private
     * @var    string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 2.8.0
     * @param string    $plugin_name       The name of this plugin.
     * @param string    $version    The version of this plugin.
     */
    var $bmltEnabled_admin;
    private Bread $bread;
    private string $hook = "";
    public function __construct($plugin_name, $version, $bmltEnabled_admin, $bread)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->bmltEnabled_admin = $bmltEnabled_admin;
        $this->bread = $bread;
    }
    public function get_bread_instance(): Bread
    {
        return $this->bread;
    }
    /**
     * Register the stylesheets for the admin area.
     *
     * @since 2.8.0
     */
    public function enqueue_styles($hook)
    {
        if (!str_ends_with($hook, $this->hook)) {
            return;
        }
        wp_enqueue_style("jquery-ui", plugin_dir_url(__FILE__) . "css/jquery-ui.min.css", false, "1.2", 'all');
        wp_enqueue_style("spectrum", plugin_dir_url(__FILE__) . "css/spectrum.min.css", false, "1.2", 'all');
        wp_enqueue_style("tooltipster", plugin_dir_url(__FILE__) . "css/tooltipster.bundle.min.css", false, "1.2", 'all');
        wp_enqueue_style("tooltipster-noir", plugin_dir_url(__FILE__) . "css/tooltipster-sideTip-noir.min.css", false, "1.2", 'all');
        wp_enqueue_style("admin", plugin_dir_url(__FILE__) . "css/admin.css", false, "1.2", 'all');
        wp_enqueue_style("select2", plugin_dir_url(__FILE__) . "css/select2.min.css", false, "1.2", 'all');
        wp_enqueue_style("smartWizard-dots", plugin_dir_url(__FILE__) . "css/smart_wizard_dots.css", false, "6.0.6", 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 2.8.0
     */
    public function enqueue_scripts($hook)
    {
        if (!str_ends_with($hook, $this->hook)) {
            return;
        }
        wp_enqueue_script('common');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script("bmlt_meeting_list", plugin_dir_url(__FILE__) . "js/bmlt_meeting_list.js", array('jquery'), BREAD_VERSION, true);
        wp_enqueue_script("tooltipster", plugin_dir_url(__FILE__) . "js/tooltipster.bundle.min.js", array('jquery'), "1.2", true);
        wp_enqueue_script("spectrum", plugin_dir_url(__FILE__) . "js/spectrum.min.js", array('jquery'), "1.2", true);
        wp_enqueue_script("select2", plugin_dir_url(__FILE__) . "js/select2.min.js", array('jquery'), "1.2", true);
        wp_enqueue_script("fetch-jsonp", plugin_dir_url(__FILE__) . "js/fetch-jsonp.js", array('jquery'), "1.30", true);
        wp_enqueue_script("smartWizard", plugin_dir_url(__FILE__) . "js/jquery.smartWizard.js", array('jquery'), "6.0.6", true);
        wp_enqueue_script("breadWizard", plugin_dir_url(__FILE__) . "js/bread-wizard.js", array('smartWizard'), BREAD_VERSION, true);
        /**
         * Make some JSON from PHP available in JS.
         */
        $str = (new WP_Filesystem_Direct(null))->get_contents(plugin_dir_path(__FILE__) . 'templates/meeting_data_templates.json');
        wp_add_inline_script('common', "meetingDataTemplates = $str", 'before');
        $strTemplates = $this->get_meeting_list_templates_json(plugin_dir_path(__FILE__) . 'templates');
        $langs = [];
        foreach ($this->bread->getTranslateTable() as $key => $value) {
            $langs[] = ['key' => $key, 'name' => $value['LANG_NAME']];
        }
        $strLangs = wp_json_encode($langs);
        wp_add_inline_script('breadWizard', "breadLayouts = $strTemplates; breadTranslations = $strLangs", 'before');
    }
    /**
     * This allows us to simply add a file to the appropriate directory
     * to make the configuration available for selection in the wizard.
     *
     * @param string $root the directory where we will search for configurations.  The configurations must be in
     * a directory with a numeric name, where the numeric value is the maximum number of meetings for the configurations stored there.
     * The filenames of the configurations must have a specific format, which is used to describe the configurations in the UI.
     * @return string
     */
    function get_meeting_list_templates_json(string $root): string
    {
        $sizes = [];
        foreach (scandir($root) as $dir) {
            if (!(is_numeric($dir))) {
                continue;
            }
            $files = [];
            foreach (scandir($root . '/' . $dir) as $file) {
                if (substr($file, 0, 1) !== '.') {
                    $files[] = $file;
                }
            }
            $sizes[] = array(
                'maxSize' => $dir,
                'configurations' => $files,
            );
        }
        return wp_json_encode($sizes);
    }
    function ml_default_editor($r)
    {
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen != null && str_ends_with($screen->id, $this->bmltEnabled_admin->getSlug())) {
                return "tinymce";
            }
        }
        return $r;
    }

    function force_mce_refresh($ver)
    {
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen != null && str_ends_with($screen->id, $this->bmltEnabled_admin->getSlug())) {
                return $ver + 99;
            }
        }
        return $ver;
    }

    // Register new button in the editor
    function my_register_mce_button($buttons)
    {
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen != null && str_ends_with($screen->id, $this->bmltEnabled_admin->getSlug())) {
                array_push($buttons, 'front_page_button', 'custom_template_button_1', 'custom_template_button_2');
            }
        }
        return $buttons;
    }

    function my_custom_plugins()
    {
        $plugins_array = array();
        if (!function_exists('get_current_screen')) {
            return $plugins_array;
        }
        $screen = get_current_screen();
        if ($screen != null && str_ends_with($screen->id, $this->bmltEnabled_admin->getSlug())) {
            $plugins = array('table', 'code', 'contextmenu'); //Add any more plugins you want to load here
            //Build the response - the key is the plugin name, value is the URL to the plugin JS
            foreach ($plugins as $plugin) {
                $plugins_array[$plugin] = plugins_url('tinymce/', __FILE__) . $plugin . '/plugin.min.js';
            }
            $shortcode_menu = array();
            $shortcode_menu['front_page_button'] = plugins_url('tinymce/', __FILE__) . 'front_page_button/plugin.min.js';
            //let's leave the enhancement mechanism open for now.
            //apply_filters is one option, perhaps we will think of something better.
            //$shortcode_menu = apply_filters("Bread_Adjust_Menu", $shortcode_menu);
            $plugins_array = array_merge($plugins_array, $shortcode_menu);
        }
        return $plugins_array;
    }

    // Enable font size & font family selects in the editor
    function tiny_tweaks($initArray)
    {
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen != null && str_ends_with($screen->id, $this->bmltEnabled_admin->getSlug())) {
                $initArray['fontsize_formats'] = "5pt 6pt 7pt 8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 22pt 24pt 26pt 28pt 30pt 32pt 34pt 36pt 38pt";
                $initArray['theme_advanced_blockformats'] = 'h2,h3,h4,p';
                $initArray['wordpress_adv_hidden'] = false;
                $initArray['font_formats'] = 'Arial (Default)=arial;';
                $initArray['font_formats'] .= 'Times (Sans-Serif)=times;';
                $initArray['font_formats'] .= 'Courier (Monospace)=courier;';
                $initArray['content_style'] = 'body { font-family: Arial; }';
            }
        }
        return $initArray;
    }
    function is_root_server_missing()
    {
        if (!function_exists('get_current_screen')) {
            return;
        }
        $screen = get_current_screen();
        if ($screen != null && str_ends_with($screen->id, $this->bmltEnabled_admin->getSlug())) {
            $root_server = $this->bread->getOption('root_server');
            if ($root_server == '') {
                echo '<div id="message" class="error"><p>Missing BMLT Server in settings for bread.</p>';
                $url = admin_url('options-general.php?page=class-bread-admin.php');
                echo "<p><a href='" . esc_url($url) . "'>Settings</a></p>";
                echo '</div>';
            }
        }
    }

    function pwsix_process_rename_settings()
    {
        if (! wp_verify_nonce($_POST['pwsix_rename_nonce'], 'pwsix_rename_nonce')) {
            return;
        }
        if (! $this->current_user_can_modify()) {
            return;
        }
        $this->bread->getConfigurationForSettingId($this->bread->getRequestedSetting());
        $this->bread->setAndSaveSetting($this->bread->getRequestedSetting(), sanitize_text_field($_POST['setting_descr']));
    }
    /**
     * Process a settings export that generates a .json file of the shop settings
     */
    function pwsix_process_settings_export()
    {
        if (!isset($_POST['pwsix_export_nonce']) || ! wp_verify_nonce($_POST['pwsix_export_nonce'], 'pwsix_export_nonce')) {
            return;
        }
        $this->download_settings_inner();
    }
    function download_settings()
    {
        if ($this->bread->exportingMeetingList()) {
            $this->download_settings_inner();
        }
    }
    function download_mpdf_log()
    {
        if ($this->bread->exportingLogFile()) {
            $this->download_log_file();
        }
    }
    private function download_settings_inner()
    {
        $this->bread->getConfigurationForSettingId($this->bread->getRequestedSetting());
        $blogname = str_replace(" - ", " ", get_option('blogname') . '-' . $this->bread->getSettingName($this->bread->getRequestedSetting()));
        $blogname = str_replace(" ", "-", $blogname);
        $date = gmdate("m-d-Y");
        $blogname = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($blogname)), '-');
        $json_name = $blogname . $date . ".json"; // Naming the filename will be generated.
        $settings = $this->bread->getOptions();
        foreach ($settings as $key => $value) {
            $value = maybe_unserialize($value);
            $need_options[$key] = $value;
        }
        $json_file = wp_json_encode($need_options); // Encode data into json data
        ignore_user_abort(true);
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        header("Content-Disposition: attachment; filename=$json_name");
        header("Expires: 0");
        header('Content-Length: ' . strlen($json_file));
        file_put_contents('php://output', $json_file);
        exit;
    }
    function download_log_file()
    {
        if (!isset($_REQUEST['export-mpdf-log'])) {
            exit;
        }
        foreach (Bread::get_log_files() as $log) {
            if ($log['name'] === $_REQUEST['export-mpdf-log']) {
                $this->exportLogFile($log['path']);
            }
        }
        exit;
    }
    function exportLogFile($file)
    {
        ignore_user_abort(true);
        header('Content-Description: File Transfer');
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_end_flush();
        readfile($file);// phpcs:ignore
        ob_end_flush();
        exit;
    }
    function current_user_can_modify()
    {
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles)) {
            return true;
        }
        if (! current_user_can('manage_bread')) {
            return false;
        }
        $authors_safe = $this->bread->getOption('authors');
        if (!is_array($authors_safe) || empty($authors_safe)) {
            return true;
        }
        if (in_array($user->ID, $authors_safe)) {
            return true;
        }
        return false;
    }
    function current_user_can_create()
    {
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles)) {
            return true;
        }
        if (current_user_can('manage_options')) {
            return true;
        }
        if (current_user_can('manage_bread')) {
            return true;
        }
        return false;
    }
    /**
     * Process a settings import from a json file
     */
    function pwsix_process_settings_import()
    {
        if (empty($_REQUEST['pwsix_import_nonce']) || !wp_verify_nonce($_REQUEST['pwsix_import_nonce'], 'pwsix_import_nonce')) {
            return;
        }
        if (! $this->current_user_can_modify()) {
            return;
        }
        $this->bread->getConfigurationForSettingId($this->bread->getRequestedSetting());
        $file_name = $_FILES['import_file']['name'];
        $tmp = explode('.', $file_name);
        $extension = end($tmp);
        if ($extension != 'json') {
            wp_die(esc_html(__('Please upload a valid .json file', 'bread')));
        }
        $import_file = $_FILES['import_file']['tmp_name'];
        if (empty($import_file)) {
            wp_die(esc_html(__('Please upload a file to import', 'bread')));
        }
        $file_size = $_FILES['import_file']['size'];
        if ($file_size > 500000) {
            wp_die(esc_html(__('File size greater than 500k', 'bread')));
        }
        $encode_options = (new WP_Filesystem_Direct(null))->get_contents($import_file);
        while (0 === strpos(bin2hex($encode_options), 'efbbbf')) {
            $encode_options = substr($encode_options, 3);
        }
        $settings = json_decode($encode_options, true);
        $settings['authors'] = array(wp_get_current_user()->ID);
        $this->bread->setOptions($settings);
        update_option($this->bread->getOptionsName(), $this->bread->getOptions());
        setcookie('current-meeting-list', $this->bread->getRequestedSetting(), time() + 10);
        setcookie('bread_import_file', $import_file, time() + 10);
        wp_safe_redirect(admin_url('?page=bmlt-enabled-bread'));
    }
    function my_theme_add_editor_styles()
    {
        if (!function_exists('get_current_screen')) {
            return;
        }
        $screen = get_current_screen();
        if ($screen != null && str_ends_with($screen->id, $this->bmltEnabled_admin->getSlug())) {
            add_editor_style(plugin_dir_url(__FILE__) . "css/editor-style.css");
        }
    }
    /**
     * Saves the admin options to the database.
     */
    function save_admin_options()
    {
        $this->bread->updateOptions();
    }
    public function getLatestRootVersion()
    {
        $results = $this->get("https://api.github.com/repos/bmlt-enabled/bmlt-root-server/releases/latest");
        $httpcode = wp_remote_retrieve_response_code($results);
        $response_message = wp_remote_retrieve_response_message($results);
        if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty($response_message)) {
            return 'Problem Connecting to Server!';
        };
        $body = wp_remote_retrieve_body($results);
        $result = json_decode($body, true);
        return $result['name'];
    }
    function get($url, $cookies = array())
    {
        $args = array(
            'timeout' => '120',
            'cookies' => $cookies,
        );
        if (isset($this->options['user_agent'])
            && $this->bread->getOption('user_agent') != 'None'
        ) {
            $args['headers'] = array(
                'User-Agent' => $this->bread->getOption('user_agent')
            );
        }
        if ($this->bread->getOption('sslverify') == '1') {
            $args['sslverify'] = false;
        }
        return wp_remote_get($url, $args);
    }
    /**
     * @desc Adds the options sub-panel
     */
    function admin_submenu_link($parent_slug)
    {
        Bread_activate();
        $this->bmltEnabled_admin->createMenu();

        $cap = 'manage_options';
        if (!current_user_can($cap)) {
            $cap = 'manage_bread';
        }
        $this->hook = add_submenu_page(
            $parent_slug,
            'Printable Meeting Lists',
            'Printable Meeting Lists',
            $cap,
            'bmlt-enabled-bread',
            array(&$this, 'admin_options_page'),
            2
        );
    }
    function admin_options_page()
    {
        if (!empty($_POST['pwsix_action']) && (!isset($_POST['bmltmeetinglistsave']) || $_POST['bmltmeetinglistsave'] != 'Save Changes')) {
            switch ($_POST['pwsix_action']) {
                case 'settings_admin':
                    $this->pwsix_process_settings_admin();
                    break;
                case 'rename_setting':
                    $this->pwsix_process_rename_settings();
                    break;
                case 'export_settings':
                    $this->pwsix_process_settings_export();
                    break;
                case 'import_settings':
                    $this->pwsix_process_settings_import();
                    break;
                default:
                    break;
            }
        }
        if (empty($this->bread->getOptions())) {
            $this->bread->getConfigurationForSettingId($this->bread->getRequestedSetting());
        }
        include_once plugin_dir_path(__FILE__) . 'partials/bread-admin-display.php';
        (new Bread_AdminDisplay($this))->admin_options_page();
    }
    function pwsix_process_wizard()
    {
        if (!isset($_POST['pwsix_wizard_nonce']) || !wp_verify_nonce($_POST['pwsix_wizard_nonce'], 'pwsix_wizard_nonce')) {
            return;
        }
        if (!$this->current_user_can_create()) {
            return;
        }
        $layoutInfos = explode(',', sanitize_text_field($_POST['wizard_layout']));
        $encode_options = (new WP_Filesystem_Direct(null))->get_contents(plugin_dir_path(__FILE__) . 'templates/' . $layoutInfos[0]);
        while (0 === strpos(bin2hex($encode_options), 'efbbbf')) {
            $encode_options = substr($encode_options, 3);
        }
        $settings = json_decode($encode_options, true);
        $ncols = substr_count($settings['meeting_template_content'], '<td');
        $id = $this->bread->loadAllSettings([]);
        $id = $this->bread->isInitialSetting() ? 1 : ((is_numeric($_POST['wizard_setting_id'])) ? intval($_POST['wizard_setting_id']) : $this->bread->getMaxSetting() + 1);
        $optionsName = $this->bread->generateOptionName($id);
        $settings['page_size'] = $layoutInfos[1];
        $settings['authors'] = array();
        $settings['root_server'] = sanitize_url($_POST['wizard_root_server']);
        for ($i = 0; $i < count($_POST['wizard_service_bodies']); $i++) {
            $j = $i + 1;
            $settings['service_body_' . $j] = sanitize_text_field($_POST['wizard_service_bodies'][$i]);
        }
        $settings['used_format_1'] = intval($_POST['wizard_format_filter']);
        $settings['weekday_language'] = sanitize_key($_POST['wizard_language']);
        $vm_flag = intval($_POST['wizard_virtual_meetings']);
        if ($vm_flag != '0') {
            $settings['additional_list_format_key'] = '@Virtual@';
            $settings['additional_list_sort_order'] = 'weekday_tinyint,start_time';
        }
        $str = (new WP_Filesystem_Direct(null))->get_contents(plugin_dir_path(__FILE__) . 'templates/meeting_data_templates.json');
        $meeting_templates = json_decode($str, true);
        if ($vm_flag == '1') {
            $settings['custom_section_content'] =
                '<table style="width: 100%;">
            <tbody>
            <tr>
            <td style="padding: 2pt; background-color: #000000; text-align: center;"><span style="color: #ffffff;"><span style="font-size: ' . $settings['header_fontsize'] . 'px;"><b>ONLINE-MEETINGS</b></span></span></td>
            </tr>
            </tbody>
            </table>
            <p>[additional_meetinglist]</p>' . $settings['custom_section_content'];
            $settings['additional_list_template_content'] = join('', $meeting_templates['Online Meeting Two Column - Link in QR-Code']);
        }
        $settings['meeting_sort'] = sanitize_text_field($_POST['wizard_meeting_sort']);
        if ($settings['meeting_sort'] != 'day') {
            $ncols = substr_count($settings['meeting_template_content'], '<td');
            if ($ncols < 2) {
                $settings['meeting_template_content'] = $meeting_templates["One Column Template with Day [Day Time Meeting Data]"];
            } else {
                $settings['meeting_template_content'] = $meeting_templates["Two Column Template with Day [Day/Time] [Meeting Data]"];
            }
        }
        update_option($optionsName, $settings);
        $setting_name = sanitize_title($_POST['wizard-setting-name']);
        $setting_name = $setting_name == '' ? 'Setting ' . $id : $setting_name;
        $this->bread->setAndSaveSetting($id, $setting_name);
        $this->bread->getConfigurationForSettingId($id);
        ignore_user_abort(true);
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        header("Expires: 0");
        $message = ['result' => ['setting' => $id]];
        $data = wp_json_encode($message);
        header('Content-Length: ' . strlen($data));
        file_put_contents('php://output', $data);
        exit();
    }
    function pwsix_process_settings_admin()
    {
        if (! wp_verify_nonce($_POST['pwsix_settings_admin_nonce'], 'pwsix_settings_admin_nonce')) {
            return;
        }
        $this->bread->getConfigurationForSettingId($this->bread->getRequestedSetting());
        if (isset($_POST['delete_settings'])) {
            if (!$this->current_user_can_modify()) {
                return;
            }
            if ($this->bread->getRequestedSetting() == 1) {
                return;
            }
            $this->bread->deleteSetting($this->bread->getRequestedSetting());
            $this->bread->getConfigurationForSettingId(1);
        } elseif (isset($_POST['duplicate'])) {
            if (!$this->current_user_can_create()) {
                return;
            }
            $id = $this->bread->getMaxSetting() + 1;
            $this->bread->setOptionsName($this->bread->generateOptionName($id));
            $this->bread->setOption('authors', array());
            $this->save_admin_options();
            $this->bread->setAndSaveSetting($id, 'Setting ' . $id);
            $this->bread->getConfigurationForSettingId($id);
        }
    }
    function process_customize_form()
    {
        if (isset($_POST['bmltmeetinglistsave']) || isset($_POST['bmltmeetinglistpreview'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'bmltmeetinglistupdate-options')) {
                die('Whoops! There was a problem with the data you posted. Please go back and try again.');
            }
            $this->bread->getConfigurationForSettingId($this->bread->getRequestedSetting());
            $this->bread->setOption('bread_version', sanitize_text_field($_POST['bread_version']));
            $this->bread->setOption('logging', isset($_POST['logging']));
            $this->bread->setOption('simpleTables', isset($_POST['simpleTables']));
            $this->bread->setOption('packTabledata', isset($_POST['packTabledata']));
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
                    $this->bread->appendOption('extra_meetings', wp_kses_post($extra));
                }
            }
            $authors = isset($_POST['authors_select']) ? $_POST['authors_select'] : [];
            $this->bread->setOption('authors', array());
            foreach ($authors as $author) {
                $this->bread->appendOption('authors', intval($author));
            }
            $user = wp_get_current_user();
            if (!in_array($user->ID, $this->bread->getOption('authors'))) {
                $this->bread->appendOption('authors', $user->ID);
            }
            if (isset($_POST['bmltmeetinglistpreview'])) {
                session_start();
                $_SESSION['bread_preview_settings'] = $this->bread->getOptions();
                wp_redirect(home_url() . "?preview-meeting-list=1");
                exit();
            }
        }
    }
}
