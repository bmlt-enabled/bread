<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://bmlt.app
 * @since 2.8.0
 *
 * @package    Bread
 * @subpackage Bread/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bread
 * @subpackage Bread/admin
 * @author     bmlt-enabled <help@bmlt.app>
 */
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
    var $outside_meeting_result = array();
    var $maxSetting = 1;
    var $loaded_setting = 1;
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 2.8.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style("jquery-ui", plugin_dir_url(__FILE__) . "css/jquery-ui.min.css", false, "1.2", 'all');
        wp_enqueue_style("spectrum", plugin_dir_url(__FILE__) . "css/spectrum.css", false, "1.2", 'all');
        wp_enqueue_style("admin", plugin_dir_url(__FILE__) . "css/admin.css", false, "1.2", 'all');
        wp_enqueue_style("chosen", plugin_dir_url(__FILE__) . "css/chosen.min.css", false, "1.2", 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 2.8.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('common');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script("bmlt_meeting_list", plugin_dir_url(__FILE__) . "js/bmlt_meeting_list.js", array('jquery'), "1.2", true);
        wp_enqueue_script("tooltipster", plugin_dir_url(__FILE__) . "js/jquery.tooltipster.min.js", array('jquery'), "1.2", true);
        wp_enqueue_script("spectrum", plugin_dir_url(__FILE__) . "js/spectrum.js", array('jquery'), "1.2", true);
        wp_enqueue_script("chosen", plugin_dir_url(__FILE__) . "js/chosen.jquery.min.js", array('jquery'), "1.2", true);
    }

    function ml_default_editor()
    {
        global $my_admin_page;
        $screen = get_current_screen();
        if ($screen->id == $my_admin_page) {
            return "tinymce";
        }
    }

    function force_mce_refresh($ver)
    {
        global $my_admin_page;
        $screen = get_current_screen();
        if ($screen->id == $my_admin_page) {
            return $ver + 99;
        }
    }

    // Register new button in the editor
    function my_register_mce_button($buttons)
    {
        global $my_admin_page;
        $screen = get_current_screen();
        if ($screen->id == $my_admin_page) {
            array_push($buttons, 'front_page_button', 'custom_template_button_1', 'custom_template_button_2');
        }
        return $buttons;
    }

    function my_custom_plugins()
    {
        global $my_admin_page;
        $plugins_array = array();
        $screen = get_current_screen();
        if ($screen->id == $my_admin_page) {
            $plugins = array('table', 'code', 'contextmenu' ); //Add any more plugins you want to load here
            //Build the response - the key is the plugin name, value is the URL to the plugin JS
            foreach ($plugins as $plugin) {
                $plugins_array[ $plugin ] = plugins_url('tinymce/', __FILE__) . $plugin . '/plugin.min.js';
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
        global $my_admin_page;
        $screen = get_current_screen();
        if ($screen->id == $my_admin_page) {
            $initArray['fontsize_formats'] = "5pt 6pt 7pt 8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 22pt 24pt 26pt 28pt 30pt 32pt 34pt 36pt 38pt";
            $initArray['theme_advanced_blockformats'] = 'h2,h3,h4,p';
            $initArray['wordpress_adv_hidden'] = false;
            $initArray['font_formats']='Arial (Default)=arial;';
            $initArray['font_formats'].='Times (Sans-Serif)=times;';
            $initArray['font_formats'].='Courier (Monospace)=courier;';
            $initArray['content_style'] = 'body { font-family: Arial; }';
        }
        return $initArray;
    }
    function is_root_server_missing()
    {
        global $my_admin_page;
        $screen = get_current_screen();
        if ($screen->id == $my_admin_page) {
            $root_server = Bread::getOption('root_server');
            if ($root_server == '') {
                echo '<div id="message" class="error"><p>Missing BMLT Server in settings for bread.</p>';
                $url = admin_url('options-general.php?page=bmlt-meeting-list.php');
                echo "<p><a href='$url'>Settings</a></p>";
                echo '</div>';
            } else if (!Bread::temp_dir()) {
                echo '<div id="message" class="error"><p>' . Bread::temp_dir() . ' temporary directory is not writable.</p>';
                $url = admin_url('options-general.php?page=bmlt-meeting-list.php');
                echo "<p><a href='$url'>Settings</a></p>";
                echo '</div>';
            }
        }
    }

    function pwsix_process_rename_settings()
    {
        Bread::getMLOptions(Bread::getRequestedSetting());
        if (isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes') {
            return;
        }
        if (empty($_POST['pwsix_action']) || 'rename_setting' != $_POST['pwsix_action']) {
            return;
        }
        if (! wp_verify_nonce($_POST['pwsix_rename_nonce'], 'pwsix_rename_nonce')) {
            return;
        }
        if (! $this->current_user_can_modify()) {
            return;
        }

        Bread::renameSetting($this->loaded_setting, sanitize_text_field($_POST['setting_descr']));
    }
    /**
     * Process a settings export that generates a .json file of the shop settings
     */
    function pwsix_process_settings_export()
    {
        Bread::getMLOptions(Bread::getRequestedSetting());
        if (isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes') {
            return;
        }
        if (empty($_REQUEST['pwsix_action']) || 'export_settings' != $_REQUEST['pwsix_action']) {
            return;
        }
        if (! wp_verify_nonce($_POST['pwsix_export_nonce'], 'pwsix_export_nonce')) {
            return;
        }
        if (! current_user_can('manage_bread')) {  // TODO: Is this necessary? Why not let the user make a copy
            return;
        }

        $blogname = str_replace(" - ", " ", get_option('blogname').'-'.Bread::getSettingName($this->loaded_setting));
        $blogname = str_replace(" ", "-", $blogname);
        $date = date("m-d-Y");
        $blogname = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($blogname)), '-');
        $json_name = $blogname.$date.".json"; // Naming the filename will be generated.
        $settings = get_option(Bread::getOptionsName());
        foreach ($settings as $key => $value) {
            $value = maybe_unserialize($value);
            $need_options[$key] = $value;
        }
        $json_file = json_encode($need_options); // Encode data into json data
        ignore_user_abort(true);
        header('Content-Type: application/json; charset=utf-8');
        header("Content-Disposition: attachment; filename=$json_name");
        header("Expires: 0");
        echo json_encode($settings);
        exit;
    }
    function current_user_can_modify()
    {
        if (! current_user_can('manage_bread')) {
            return false;
        }
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles)) {
            return true;
        }
        $authors_safe = Bread::getOption('authors');
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
        if (! current_user_can('manage_bread')) {
            return false;
        }
        return true;
    }
    /**
     * Process a settings import from a json file
     */
    function pwsix_process_settings_import()
    {
        Bread::getMLOptions(Bread::getRequestedSetting());
        if (isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes') {
            return;
        }
        if (empty($_REQUEST['pwsix_action']) || 'import_settings' != $_REQUEST['pwsix_action']) {
            return;
        }
        if (empty($_REQUEST['pwsix_import_nonce']) || !wp_verify_nonce($_REQUEST['pwsix_import_nonce'], 'pwsix_import_nonce')) {
            return;
        }
        if (! current_user_can('manage_bread')) {
            return;
        }
        $file_name = $_FILES['import_file']['name'];
        $tmp = explode('.', $file_name);
        $extension = end($tmp);
        if ($extension != 'json') {
            wp_die(__('Please upload a valid .json file'));
        }
        $import_file = $_FILES['import_file']['tmp_name'];
        if (empty($import_file)) {
            wp_die(__('Please upload a file to import'));
        }
        $file_size = $_FILES['import_file']['size'];
        if ($file_size > 500000) {
            wp_die(__('File size greater than 500k'));
        }
        $encode_options = file_get_contents($import_file);
        while (0 === strpos(bin2hex($encode_options), 'efbbbf')) {
            $encode_options = substr($encode_options, 3);
        }
        $settings = json_decode($encode_options, true);
        $settings['authors'] =
        update_option(Bread::getOptionsName(), $settings);
        setcookie('pwsix_action', "import_settings", time()+10);
        setcookie('current-meeting-list', $this->loaded_setting, time()+10);
        wp_safe_redirect(admin_url('?page=bmlt-meeting-list.php'));
    }

    /**
     * Process a default settings
     */
    function pwsix_process_default_settings()
    {
        Bread::getMLOptions(Bread::getRequestedSetting());
        if (! current_user_can('manage_bread')
            || (isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes' )
        ) {
            return;
        } elseif (isset($_REQUEST['pwsix_action']) && 'three_column_default_settings' == $_REQUEST['pwsix_action']) {
            if (! wp_verify_nonce($_POST['pwsix_submit_three_column'], 'pwsix_submit_three_column')) {
                die('Whoops! There was a problem with the data you posted. Please go back and try again.');
            }
            $import_file = plugin_dir_path(__FILE__) . "includes/three_column_settings.json";
        } elseif (isset($_REQUEST['pwsix_action']) && 'four_column_default_settings' == $_REQUEST['pwsix_action']) {
            if (! wp_verify_nonce($_POST['pwsix_submit_four_column'], 'pwsix_submit_four_column')) {
                die('Whoops! There was a problem with the data you posted. Please go back and try again.');
            }
            $import_file = plugin_dir_path(__FILE__) . "includes/four_column_settings.json";
        } elseif (isset($_REQUEST['pwsix_action']) && 'booklet_default_settings' == $_REQUEST['pwsix_action']) {
            if (! wp_verify_nonce($_POST['pwsix_submit_booklet'], 'pwsix_submit_booklet')) {
                die('Whoops! There was a problem with the data you posted. Please go back and try again.');
            }
            $import_file = plugin_dir_path(__FILE__) . "includes/booklet_settings.json";
        } else {
            return;
        }
        if (empty($import_file)) {
            wp_die(__('Error importing default settings file'));
        }
        $encode_options = file_get_contents($import_file);
        $settings = json_decode($encode_options, true);
        $settings['authors'] = Bread::getOption('authors');
        update_option(Bread::getOptionsName(), $settings);
        setcookie('pwsix_action', "default_settings_success", time()+10);
        setcookie('current-meeting-list', $this->loaded_setting, time()+10);
        wp_safe_redirect(admin_url('?page=bmlt-meeting-list.php'));
    }
    function my_theme_add_editor_styles()
    {
        global $my_admin_page;
        $screen = get_current_screen();
        if (isset($screen) && $screen->id == $my_admin_page) {
            add_editor_style(plugin_dir_url(__FILE__) . "css/editor-style.css");
        }
    }
    /**
     * @desc Adds the Settings link to the plugin activate/deactivate page
     */
    function filter_plugin_actions($links, $file)
    {
        $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link); // before other links
        return $links;
    }
    private function addServiceBody($service_body_name)
    {
        if (false === ( Bread::getOption($service_body_name) == 'Not Used' )) {
            $area_data = explode(',', Bread::getOption($service_body_name));
            $area = $this->arraySafeGet($area_data);
            Bread::setOption($service_body_name, $area == 'NOT USED' ? '' : $area);
            $service_body_id = $this->arraySafeGet($area_data, 1);
            if (Bread::getOption('recurse_service_bodies') == 1) {
                return '&recursive=1&services[]=' . $service_body_id;
            } else {
                return '&services[]='.$service_body_id;
            }
        }
    }
    function arraySafeGet($arr, $i = 0)
    {
            return is_array($arr) ? $arr[$i] ?? '': '';
    }
        /**
         * Saves the admin options to the database.
         */
    function save_admin_options()
    {
        Bread::updateOptions();
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
                && Bread::getOption('user_agent') != 'None'
            ) {
                $args['headers'] = array(
                    'User-Agent' => Bread::getOption('user_agent')
                );
            }
            if (Bread::getOption('sslverify') == '1') {
                $args['sslverify'] = false;
            }
            return wp_remote_get($url, $args);
    }
            /**
             * @desc Adds the options sub-panel
             */
    function admin_menu_link()
    {
        activate_bread();
        global $my_admin_page;
        Bread_Activator::activate();
        $my_admin_page = add_menu_page('Meeting List', 'Meeting List', 'manage_bread', basename(__FILE__), array(&$this, 'admin_options_page'), 'dashicons-admin-page');
    }
    function admin_options_page()
    {
        include_once plugin_dir_path(__FILE__).'partials/bread-admin-display.php';
        (new Bread_AdminDisplay($this))->admin_options_page();
    }
    function pwsix_process_settings_admin()
    {
            Bread::getMLOptions(Bread::getRequestedSetting());
        if (isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes') {
            return;
        }
        if (empty($_POST['pwsix_action']) || 'settings_admin' != $_POST['pwsix_action']) {
            return;
        }
        if (! wp_verify_nonce($_POST['pwsix_settings_admin_nonce'], 'pwsix_settings_admin_nonce')) {
            return;
        }
        if (isset($_POST['delete'])) {
            if (!$this->current_user_can_modify()) {
                return;
            }
            if ($this->loaded_setting == 1) {
                return;
            }
            Bread::deleteSetting($this->loaded_setting);
            Bread::getMLOptions(1);
            $this->loaded_setting = 1;
            Bread::setRequestedSetting(1);
        } elseif (isset($_POST['duplicate'])) {
            if (!$this->current_user_can_create()) {
                return;
            }
            $id = $this->maxSetting + 1;
            Bread::setOptionsName(Bread::generateOptionName($id));
            Bread::setOption('authors', array());
            $this->save_admin_options();
            Bread::renameSetting($id, 'Setting '.$id);
            $this->maxSetting = $id;
            Bread::getMLOptions($id);
        }
    }
}
