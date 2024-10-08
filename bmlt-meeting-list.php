<?php
/**
Plugin Name: bread
Plugin URI: http://wordpress.org/extend/plugins/bread/
Description: Maintains and generates a PDF Meeting List from BMLT.
Author: bmlt-enabled
Author URI: https://bmlt.app
Version: 2.7.14
*/
/* Disallow direct access to the plugin file */
use Mpdf\Mpdf;
use function DeepCopy\deep_copy;

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

//require_once plugin_dir_path(__FILE__).'mpdf/vendor/autoload.php';
include 'partials/_helpers.php';
if (!class_exists("Bread")) {
    class Bread
    {
        var $lang = '';
        var $mpdf = null;
        var $tmp_dir;
        var $meeting_count = 0;
        var $formats_used = '';
        var $formats_by_key = array();
        var $formats_spanish = '';
        var $formats_french = '';
        var $formats_all = '';
        var $wheelchair_format = null;
        var $translate = array();
        var $services = '';
        var $requested_setting = 1;
        var $target_timezone = false;
        var $calculated_fields = array(
            'duration_m',
            'duration_h',
            'day',
            'day_abbr',
            'area_name',
        );
        var $legacy_synonyms = array (
            'borough'   => 'location_city_subsection',
            'time'      => 'start_time',
            'state'     => 'location_province',
            'street'    => 'location_street',
            'neighborhood'  => 'location_neighborhood',
            'city'          => 'location_municipality',
            'zip'           => 'location_postal_code_1',
            'location'      => 'location_text',
            'info'          => 'location_info',
            'county'        => 'location_sub_province',
            'group'         => 'meeting_name',
            'email'         => 'email_contact',
            'mins'          => 'duration_m',
            'hrs'           => 'duration_h',
            "area"          => 'area_name',
        );
        var $section_shortcodes;
        var $service_meeting_result = null;
        const SETTINGS = 'bmlt_meeting_list_settings';
        const OPTIONS_NAME = 'bmlt_meeting_list_options';
        var $optionsName = Bread::OPTIONS_NAME;
        var $options = array();
        var $outside_meeting_result = array();
        var $allSettings = array();
        var $maxSetting = 1;
        var $loaded_setting = 1;
        var $authors_safe = array();
        var $connection_error = '';
        var $protocol = '';
        var $unique_areas = array();

        function loadAllSettings()
        {
            $this->allSettings = get_option(Bread::SETTINGS);
            if ($this->allSettings === false) {
                $this->allSettings = array();
                $this->allSettings[1] = "Default Setting";
                $this->maxSetting = 1;
            } else {
                foreach ($this->allSettings as $key => $value) {
                    if ($key > $this->maxSetting) {
                        $this->maxSetting = $key;
                    }
                }
            }
        }
        function startsWith($haystack, $needle)
        {
            $length = strlen($needle);
            return (substr($haystack, 0, $length) === $needle);
        }
        function getCurrentMeetingListHolder()
        {
            $ret = array();
            if (isset($_REQUEST['current-meeting-list'])) {
                $ret['current-meeting-list'] = $_REQUEST['current-meeting-list'];
            } else if (isset($_COOKIE['current-meeting-list'])) {
                $ret['current-meeting-list'] = $_COOKIE['current-meeting-list'];
            }
            return $ret;
        }
        function __construct()
        {
            // Register hooks
            register_activation_hook(__FILE__, array(__CLASS__, 'activation'));

            $this->protocol = (strpos(strtolower(home_url()), "https") !== false ? "https" : "http") . "://";

            $this->loadAllSettings();
            $holder = $this->getCurrentMeetingListHolder();

            $current_settings = isset($holder['current-meeting-list']) ? intval($holder['current-meeting-list']) : 1;
            $this->load_translations();
            if (isset($holder['current-meeting-list']) && !is_admin()) {
                $this->getMLOptions($current_settings);
                add_action('plugins_loaded', array(&$this, 'bmlt_meeting_list' ));
            } else if (is_admin()) {
                $this->requested_setting = $current_settings;
                add_action("admin_init", array(&$this, 'my_sideload_image'));
                add_action("admin_menu", array(&$this, "admin_menu_link"));
                add_filter('tiny_mce_before_init', array(&$this, 'tiny_tweaks'));
                add_filter('mce_external_plugins', array(&$this, 'my_custom_plugins'));
                add_filter('mce_buttons', array(&$this, 'my_register_mce_button'));
                //add_action("admin_notices", array(&$this, "is_root_server_missing"));
                add_action("admin_init", array(&$this, "pwsix_process_settings_export"));
                add_action("admin_init", array(&$this, "pwsix_process_settings_import"));
                add_action("admin_init", array(&$this, "pwsix_process_default_settings"));
                add_action("admin_init", array(&$this, "pwsix_process_settings_admin"));
                add_action("admin_init", array(&$this, "pwsix_process_rename_settings"));
                add_action("admin_init", array(&$this, "my_theme_add_editor_styles"));
                add_action("admin_enqueue_scripts", array(&$this, "enqueue_backend_files"));
                add_action("wp_default_editor", array(&$this, "ml_default_editor"));
                add_filter('tiny_mce_version', array(__CLASS__, 'force_mce_refresh'));
            }

            register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivation'));
        }

        public static function activation()
        {
            Bread::add_cap();
        }

        private static function add_cap()
        {
            $role = $GLOBALS['wp_roles']->role_objects['administrator'];
            if (isset($role) && !$role->has_cap('manage_bread')) {
                $role->add_cap('manage_bread');
            }
        }

        public static function deactivation()
        {
            Bread::remove_cap();
        }

        private static function remove_cap()
        {
            $role = $GLOBALS['wp_roles']->role_objects['administrator'];
            if (isset($role) && $role->has_cap('manage_bread')) {
                $role->remove_cap('manage_bread');
            }
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

        function my_sideload_image()
        {
            global $my_admin_page;
            $screen = get_current_screen();
            if (isset($screen) && $screen->id == $my_admin_page) {
                if (get_option($this->optionsName) === false) {
                    $url = plugin_dir_url(__FILE__) . "includes/nalogo.jpg";
                    media_sideload_image($url, 0);
                }
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
        function get_temp_dir()
        {
            if (!$this->tmp_dir) {
                $dir = get_temp_dir();
                $dir = rtrim($dir, DIRECTORY_SEPARATOR);
                if (!is_dir($dir) || !is_writable($dir)) {
                    return false;
                }
                $this->brute_force_cleanup($dir);
                $attempts = 0;
                $path = '';
                do {
                    $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, 'bread', mt_rand(100000, mt_getrandmax()));
                } while (!mkdir($path) && $attempts++ < 100);
                $this->tmp_dir = $path;
            }
            return $this->tmp_dir;
        }
        function is_root_server_missing()
        {
            global $my_admin_page;
            $screen = get_current_screen();
            if ($screen->id == $my_admin_page) {
                $root_server = $this->options['root_server'];
                if ($root_server == '') {
                    echo '<div id="message" class="error"><p>Missing BMLT Server in settings for bread.</p>';
                    $url = admin_url('options-general.php?page=bmlt-meeting-list.php');
                    echo "<p><a href='$url'>Settings</a></p>";
                    echo '</div>';
                } else if (!$this->get_temp_dir()) {
                    echo '<div id="message" class="error"><p>' . $this->get_temp_dir() . ' temporary directory is not writable.</p>';
                    $url = admin_url('options-general.php?page=bmlt-meeting-list.php');
                    echo "<p><a href='$url'>Settings</a></p>";
                    echo '</div>';
                }
            }
        }
        function Bread()
        {
            $this->__construct();
        }

        /**
        * @desc Adds JS/CSS to the header
        */
        function enqueue_backend_files($hook)
        {
            if ($hook == 'toplevel_page_bmlt-meeting-list') {
                wp_enqueue_script('common');
                wp_enqueue_script('jquery-ui-tabs');
                wp_enqueue_script('jquery-ui-accordion');
                wp_enqueue_script('jquery-ui-dialog');
                wp_enqueue_style("jquery-ui", plugin_dir_url(__FILE__) . "css/jquery-ui.min.css", false, "1.2", 'all');
                wp_enqueue_style("spectrum", plugin_dir_url(__FILE__) . "css/spectrum.css", false, "1.2", 'all');
                wp_enqueue_style("admin", plugin_dir_url(__FILE__) . "css/admin.css", false, "1.2", 'all');
                wp_enqueue_style("chosen", plugin_dir_url(__FILE__) . "css/chosen.min.css", false, "1.2", 'all');
                wp_enqueue_script("bmlt_meeting_list", plugin_dir_url(__FILE__) . "js/bmlt_meeting_list.js", array('jquery'), "1.2", true);
                wp_enqueue_script("tooltipster", plugin_dir_url(__FILE__) . "js/jquery.tooltipster.min.js", array('jquery'), "1.2", true);
                wp_enqueue_script("spectrum", plugin_dir_url(__FILE__) . "js/spectrum.js", array('jquery'), "1.2", true);
                wp_enqueue_script("chosen", plugin_dir_url(__FILE__) . "js/chosen.jquery.min.js", array('jquery'), "1.2", true);
            }
        }

        function my_theme_add_editor_styles()
        {
            global $my_admin_page;
            $screen = get_current_screen();
            if (isset($screen) && $screen->id == $my_admin_page) {
                add_editor_style(plugin_dir_url(__FILE__) . "css/editor-style.css");
            }
        }
        function load_translations()
        {
            $files = scandir(dirname(__FILE__)."/lang");
            foreach ($files as $file) {
                if (strpos($file, "translate_")!==0) {
                    continue;
                }
                include(dirname(__FILE__)."/lang/".$file);
                $key = substr($file, 10, -4);
                $this->translate[$key] = $translate;
            }
        }
        function getday($day, $abbreviate = false, $language = 'en')
        {
            $data = '';
            $key = "WEEKDAYS";
            if ($abbreviate) {
                $key = "WKDYS";
            }
            return mb_convert_encoding($this->translate[$language][$key][$day], 'UTF-8', mb_list_encodings());
        }

        function authenticate_root_server()
        {
            $query_string = http_build_query(array(
                'admin_action' => 'login',
                'c_comdef_admin_login' => $this->options['bmlt_login_id'],
                'c_comdef_admin_password' => $this->options['bmlt_login_password'], '&'));
            return $this->get($this->options['root_server']."/local_server/server_admin/xml.php?" . $query_string);
        }
        function requires_authentication()
        {
            return ($this->options['include_meeting_email'] == 1 || $this->options['include_asm'] == 1);
        }
        function get_root_server_request($url)
        {
            $cookies = null;

            if ($this->requires_authentication()) {
                $auth_response = $this->authenticate_root_server();
                $cookies = wp_remote_retrieve_cookies($auth_response);
            }

            return $this->get($url, $cookies);
        }

        function get_configured_root_server_request($url)
        {
            return $this->get_root_server_request($this->options['root_server']."/".$url);
        }

        function get($url, $cookies = array())
        {
            $args = array(
                'timeout' => '120',
                'cookies' => $cookies,
            );
            if (isset($this->options['user_agent']) &&
            $this->options['user_agent'] != 'None') {
                $args['headers'] = array(
                    'User-Agent' => $this->options['user_agent']
                );
            }
            if ($this->options['sslverify'] == '1') {
                $args['sslverify'] = false;
            }
            return wp_remote_get($url, $args);
        }
        function get_all_meetings()
        {
            $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults&data_field_key=weekday_tinyint,start_time,service_body_bigint,id_bigint,meeting_name,location_text,email_contact&sort_keys=meeting_name,service_body_bigint,weekday_tinyint,start_time");
            $result = json_decode(wp_remote_retrieve_body($results), true);

            $this->unique_areas = $this->get_areas();
            $all_meetings = array();
            foreach ($result as $value) {
                foreach ($this->unique_areas as $unique_area) {
                    $area_data = explode(',', $unique_area);
                    $area_id = $this->arraySafeGet($area_data, 1);
                    if ($area_id === $value['service_body_bigint']) {
                        $area_name = $this->arraySafeGet($area_data);
                    }
                }

                $value['start_time'] = date("g:iA", strtotime($value['start_time']));
                $all_meetings[] = $value['meeting_name'].'||| ['.$this->getday($value['weekday_tinyint'], true, $this->lang).'] ['.$value['start_time'].']||| ['.$area_name.']||| ['.$value['id_bigint'].']';
            }

            return $all_meetings;
        }
        function get_fieldkeys()
        {
            $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetFieldKeys");
            return json_decode(wp_remote_retrieve_body($results), true);
        }
        var $standard_keys = array(
            "id_bigint","worldid_mixed","service_body_bigint",
            "weekday_tinyint","start_time","duration_time","formats",
            "lang_enum","longitude","latitude","meeting_name"."location_text",
            "location_info","location_street","location_city_subsection",
            "location_neighborhood","location_municipality","location_sub_province",
            "location_province","location_postal_code_1","location_nation","comments","zone");
        function get_nonstandard_fieldkeys()
        {
            $all_fks = $this->get_fieldkeys();
            $ret = array();
            foreach ($all_fks as $fk) {
                if (!in_array($fk['key'], $this->standard_keys)) {
                    $ret[] = $fk;
                }
            }
            $ext_fields = apply_filters("Bread_Enrich_Meeting_Data", array(), array());
            foreach ($ext_fields as $key => $value) {
                $ret[] = array("key" => $key, "description" => $key);
            }
            return $ret;
        }
        function get_areas()
        {
            $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetServiceBodies");
            $result = json_decode(wp_remote_retrieve_body($results), true);
            $unique_areas = array();

            foreach ($result as $value) {
                $parent_name = 'Parent ID';
                foreach ($result as $parent) {
                    if ($value['parent_id'] == $parent['id']) {
                        $parent_name = $parent['name'];
                    }
                }
                if ($value['parent_id'] == '') {
                    $value['parent_id'] = '0';
                }
                $unique_areas[] = $value['name'] . ',' . $value['id'] . ',' . $value['parent_id'] . ',' . $parent_name;
            }

            return $unique_areas;
        }

        function get_bmlt_server_lang()
        {
            $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetServerInfo");
            $result = json_decode(wp_remote_retrieve_body($results), true);
            if ($result==null) {
                return 'en';
            }
            $result = $result["0"]["nativeLang"];

            return $result;
        }

        function testRootServer($override_root_server = null)
        {
            if ($override_root_server == null) {
                $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetServerInfo");
            } else {
                $results = $this->get_root_server_request($override_root_server."/client_interface/json/?switcher=GetServerInfo");
            }
            if ($results instanceof WP_Error) {
                $this->connection_error = $results->get_error_message();
                return false;
            }
            $httpcode = wp_remote_retrieve_response_code($results);
            if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
                $this->connection_error = "HTTP Return Code: ".$httpcode;
                return false;
            }

            return json_decode(wp_remote_retrieve_body($results), true);
        }
        // This is used from the AdminUI, not to generate the
        // meeting list.
        function getFormatsForSelect($all = false)
        {
            if ($all) {
                $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetFormats");
                $results = json_decode(wp_remote_retrieve_body($results), true);
                $this->sortBySubkey($results, 'key_string');
                return $results;
            }
            if (!isset($this->options['recurse_service_bodies'])) {
                $this->options['recurse_service_bodies'] = 1;
            }
            $area_data = explode(',', $this->options['service_body_1']);
            $service_body_id = $this->arraySafeGet($area_data, 1);
            if ($this->options['recurse_service_bodies'] == 1) {
                $services = '&recursive=1&services[]=' . $service_body_id;
            } else {
                $services = '&services[]='.$service_body_id;
            }
            if (empty($service_body_id)) {
                $queryUrl = "client_interface/json/?switcher=GetFormats";
            } else {
                $queryUrl = "client_interface/json/?switcher=GetSearchResults$services&get_formats_only";
            }
            $results = $this->get_configured_root_server_request($queryUrl);
            $results = json_decode(wp_remote_retrieve_body($results), true);
            $results = empty($service_body_id) ? $results : $results['formats'];
            $this->sortBySubkey($results, 'key_string');
            return $results;
        }

        function sortBySubkey(&$array, $subkey, $sortType = SORT_ASC)
        {
            if (empty($array)) {
                return;
            }
            foreach ($array as $subarray) {
                $keys[] = $subarray[$subkey];
            }
            array_multisort($keys, $sortType, $array);
        }
        function upgrade_settings()
        {
            if (!isset($this->options['cont_header_shown'])
                && isset($this->options['page_height_fix'])) {
                $fix = floatval($this->options['page_height_fix']);
                // say, the height of 2 lines
                $x = floatval($this->options['content_font_size']) *
                floatval($this->options['content_line_height']) * 2.0 * 0.35; // pt to mm
                if ($fix < $x) {
                    $this->options['cont_header_shown'] = true;
                } else {
                    $this->options['cont_header_shown'] = false;
                }
                unset($this->options['page_height_fix']);
            }
            if ($this->options['weekday_language'] == 'both') {
                $this->options['weekday_language'] = "en_es";
            }
            if ($this->options['weekday_language'] == 'both_po') {
                $this->options['weekday_language'] = "en_po";
            }
            if ($this->options['sub_header_shown'] == '0') {
                $this->options['sub_header_shown'] = 'none';
            }
            if ($this->options['sub_header_shown'] == '1') {
                $this->options['sub_header_shown'] = 'display';
            }
        }
        function bmlt_meeting_list($atts = null, $content = null)
        {
            $import_streams = [];
            ini_set('max_execution_time', 600); // tomato server can take a long time to generate a schedule, override the server setting
            $this->lang = $this->get_bmlt_server_lang();
            // addServiceBody has the side effect that
            // the service body option is overridden, so that it contains
            // only the name of the service body.
            $services = $this->addServiceBody('service_body_1');
            $services .= $this->addServiceBody('service_body_2');
            $services .= $this->addServiceBody('service_body_3');
            $services .= $this->addServiceBody('service_body_4');
            $services .= $this->addServiceBody('service_body_5');
            $area = $this->options['service_body_1'];

            if (isset($_GET['custom_query'])) {
                $services = $_GET['custom_query'];
            } elseif ($this->options['custom_query'] !== '') {
                $services = $this->options['custom_query'];
            }
            $this->services = $services;
            if ($this->options['root_server'] == '') {
                echo '<p><strong>bread Error: BMLT Server missing.<br/><br/>Please go to Settings -> bread and verify BMLT Server</strong></p>';
                exit;
            }
            if ($this->options['service_body_1'] == 'Not Used' && true === ($this->options['custom_query'] == '' )) {
                echo '<p><strong>bread Error: Service Body 1 missing from configuration.<br/><br/>Please go to Settings -> bread and verify Service Body</strong><br/><br/>Contact the bread administrator and report this problem!</p>';
                exit;
            }
            if (headers_sent()) {
                echo '<div id="message" class="error"><p>Headers already sent before Meeting List generation</div>';
                exit;
            }

            $num_columns = 0;
            if (!isset($this->options['suppress_heading'])) {
                $this->options['suppress_heading'] = 0;
            }
            if (!isset($this->options['header_font_size'])) {
                $this->options['header_font_size'] = $this->options['content_font_size'];
            }
            if (!isset($this->options['header_text_color'])) {
                $this->options['header_text_color'] = '#ffffff';
            }
            if (!isset($this->options['header_background_color'])) {
                $this->options['header_background_color'] = '#000000';
            }
            if (!isset($this->options['pageheader_textcolor'])) {
                $this->options['pageheader_textcolor'] = '#000000';
            }
            if (!isset($this->options['pageheader_fontsize']) || floatval($this->options['pageheader_fontsize'])<4) {
                $this->options['pageheader_fontsize'] = '9';
            }
            if (!isset($this->options['pageheader_backgroundcolor'])) {
                $this->options['pageheader_backgroundcolor'] = '#ffffff';
            }
            if (!isset($this->options['margin_left'])) {
                $this->options['margin_left'] = 3;
            }
            if (!isset($this->options['margin_bottom'])) {
                $this->options['margin_bottom'] = 3;
            }
            if (!isset($this->options['margin_top'])) {
                $this->options['margin_top'] = 3;
            }
            if (!isset($this->options['margin_header'])) {
                $this->options['margin_header'] = 3;
            }
            if (!isset($this->options['margin_footer'])) {
                $this->options['margin_footer'] = 5;
            }
            if (!isset($this->options['page_size'])) {
                $this->options['page_size'] = 'legal';
            }
            if (!isset($this->options['page_orientation'])) {
                $this->options['page_orientation'] = 'L';
            }
            if (!isset($this->options['booklet_pages'])) {
                $this->options['booklet_pages'] = false;
            }
            if (!isset($this->options['page_fold'])) {
                $this->options['page_fold'] = 'quad';
            }
            if (!isset($this->options['meeting_sort'])) {
                $this->options['meeting_sort'] = 'day';
            }
            if (!isset($this->options['borough_suffix'])) {
                $this->options['borough_suffix'] = 'Borough';
            }
            if (!isset($this->options['county_suffix'])) {
                $this->options['county_suffix'] = 'County';
            }
            if (!isset($this->options['neighborhood_suffix'])) {
                $this->options['neighborhood_suffix'] = 'Neighborhood';
            }
            if (!isset($this->options['city_suffix'])) {
                $this->options['city_suffix'] = 'City';
            }
            if (!isset($this->options['column_line'])) {
                $this->options['column_line'] = 0;
            }
            if (!isset($this->options['col_color'])) {
                $this->options['col_color'] = '#bfbfbf';
            }
            if (!isset($this->options['custom_section_content'])) {
                $this->options['custom_section_content'] = '';
            }
            if (!isset($this->options['custom_section_line_height'])) {
                $this->options['custom_section_line_height'] = '1';
            }
            if (!isset($this->options['custom_section_font_size'])) {
                $this->options['custom_section_font_size'] = '9';
            }
            if (!isset($this->options['pagenumbering_font_size'])) {
                $this->options['pagenumbering_font_size'] = '9';
            }
            if (!isset($this->options['include_meeting_email'])) {
                $this->options['include_meeting_email'] = 0;
            }
            if (!isset($this->options['include_protection'])) {
                $this->options['include_protection'] = 0;
            }
            if (!isset($this->options['base_font'])) {
                $this->options['base_font'] = 'dejavusanscondensed';
            }
            if (!isset($this->options['colorspace'])) {
                $this->options['colorspace'] = 0;
            }
            if (!isset($this->options['weekday_language'])) {
                $this->options['weekday_language'] = 'en';
            }
            if (!isset($this->options['asm_language'])) {
                $this->options['asm_language'] = '';
            }
            if (!isset($this->options['weekday_start'])) {
                $this->options['weekday_start'] = '1';
            }
            if (!isset($this->options['include_asm'])) {
                $this->options['include_asm'] = '0';
            }
            if (!isset($this->options['asm_format_key'])) {
                $this->options['asm_format_key'] = 'ASM';
            }
            if (!isset($this->options['asm_sort_order'])) {
                $this->options['asm_sort_order'] = 'name';
            }
            if (!isset($this->options['header_uppercase'])) {
                $this->options['header_uppercase'] = '0';
            }
            if (!isset($this->options['header_bold'])) {
                $this->options['header_bold'] = '1';
            }
            if (!isset($this->options['sub_header_shown'])) {
                $this->options['sub_header_shown'] = '0';
            }
            if (!isset($this->options['bmlt_login_id'])) {
                $this->options['bmlt_login_id'] = '';
            }
            if (!isset($this->options['bmlt_login_password'])) {
                $this->options['bmlt_login_password'] = '';
            }
            if (!isset($this->options['protection_password'])) {
                $this->options['protection_password'] = '';
            }
            if (!isset($this->options['cache_time'])) {
                $this->options['cache_time'] = 0;
            }
            if (!isset($this->options['extra_meetings'])) {
                $this->options['extra_meetings'] = [];
            }
            if (!isset($this->options['custom_query'])) {
                $this->options['custom_query'] = '';
            }
            if (!isset($this->options['asm_custom_query'])) {
                $this->options['asm_custom_query'] = '';
            }
            if (!isset($this->options['user_agent'])) {
                $this->options['user_agent'] = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bread';
            }
            if (!isset($this->options['sslverify'])) {
                $this->options['sslverify'] = '0';
            }
            if (!isset($this->options['used_format_1'])) {
                $this->options['used_format_1'] = '';
            }
            if (!isset($this->options['wheelchair_size'])) {
                $this->options['wheelchair_size'] = '20px';
            }
            if (intval($this->options['cache_time']) > 0 && ! isset($_GET['nocache']) &&
                    ! isset($_GET['custom_query'])) {
                if (false !== ( $content = get_transient($this->get_TransientKey()) )) {
                    $content = pack("H*", $content);
                    $name = $this->get_FilePath();
                    header('Content-Type: application/pdf');
                    header('Content-Length: '.strlen($content));
                    header('Content-disposition: inline; filename="'.$name.'"');
                    header('Cache-Control: public, must-revalidate, max-age=0');
                    header('Pragma: public');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                    echo $content;
                    exit;
                }
            }
            if (isset($_GET['time_zone'])) {
                $this->target_timezone = timezone_open($_GET['time_zone']);
            }
            // upgrade
            if (!isset($this->options['bread_version'])) {
                if (!($this->options['meeting_sort'] === 'weekday_area'
                   || $this->options['meeting_sort'] === 'weekday_city'
                   || $this->options['meeting_sort'] === 'weekday_county'
                   || $this->options['meeting_sort'] === 'day')) {
                       $this->options['weekday_language'] = $this->lang;
                }
                if ($this->options['page_fold']=='half') {
                    if ($this->options['page_size']=='A5') {
                        $this->options['page_size'] = 'A4';
                    }
                    $this->options['page_orientation'] = 'L';
                }
                if ($this->options['page_fold']=='tri') {
                    $this->options['page_orientation'] = 'L';
                }
                if (substr($this->options['meeting_sort'], 0, 8) == 'weekday_') {
                        $this->options['sub_header_shown'] = 'display';
                }
                if (isset($this->options['pageheader_text'])) {
                    $this->options['pageheader_content'] = $this->options['pageheader_text'];
                    unset($this->options['pageheader_text']);
                }
                if (substr($this->options['root_server'], -1) == '/') {
                    $this->options['root_server'] = substr($this->options['root_server'], 0, strlen($this->options['root_server'])-1);
                }
                if (substr($this->options['root_server'], 0, 4) !== 'http') {
                    $this->options['root_server'] = 'http://'.$this->options['root_server'];
                }
            }
            $this->upgrade_settings();
            // TODO: The page number is always 5 from botton...this should be adjustable
            if ($this->options['page_fold'] == 'half') {
                if ($this->options['page_size'] == 'letter') {
                    $page_type_settings = ['format' => array(139.7,215.9), 'margin_footer' => $this->options['margin_footer']];
                } elseif ($this->options['page_size'] == 'legal') {
                    $page_type_settings = ['format' => array(177.8,215.9), 'margin_footer' => $this->options['margin_footer']];
                } elseif ($this->options['page_size'] == 'ledger') {
                    $page_type_settings = ['format' => 'letter-P', 'margin_footer' => $this->options['margin_footer']];
                } elseif ($this->options['page_size'] == 'A4') {
                    $page_type_settings = ['format' => 'A5-P', 'margin_footer' => $this->options['margin_footer']];
                } elseif ($this->options['page_size'] == 'A5') {
                    $page_type_settings = ['format' => 'A6-P', 'margin_footer' => $this->options['margin_footer']];
                } elseif ($this->options['page_size'] == '5inch') {
                    $page_type_settings = ['format' => array(197.2,279.4), 'margin_footer' => $this->options['margin_footer']];
                }
            } elseif ($this->options['page_fold'] == 'flyer') {
                if ($this->options['page_size'] == 'letter') {
                    $page_type_settings = ['format' => array(93.13,215.9), 'margin_footer' => $this->options['margin_footer']];
                } elseif ($this->options['page_size'] == 'legal') {
                    $page_type_settings = ['format' => array(118.53,215.9), 'margin_footer' => $this->options['margin_footer']];
                } elseif ($this->options['page_size'] == 'ledger') {
                    $page_type_settings = ['format' => array(143.93,279.4), 'margin_footer' => $this->options['margin_footer']];
                } elseif ($this->options['page_size'] == 'A4') {
                    $page_type_settings = ['format' => array(99.0,210.0), 'margin_footer' => $this->options['margin_footer']];
                }
            } elseif ($this->options['page_fold'] == 'full') {
                $ps = $this->options['page_size'];
                if ($ps=='ledger') {
                    $ps = 'tabloid';
                }
                $page_type_settings = ['format' => $ps."-".$this->options['page_orientation'], 'margin_footer' => $this->options['margin_footer']];
            } else {
                $ps = $this->options['page_size'];
                if ($ps=='ledger') {
                    $ps = 'tabloid';
                }
                $page_type_settings = ['format' => $ps."-".$this->options['page_orientation'], 'margin_footer' => 0];
            }
            $default_font = $this->options['base_font'] == "freesans" ? "dejavusanscondensed" : $this->options['base_font'];
            $mode = 's';
            if ($default_font == 'arial' || $default_font == 'times' || $default_font == 'courier') {
                $mpdf_init_options = [
                    'fontDir' => array(
                        __DIR__ . '/mpdf/vendor/mpdf/mpdf/ttfonts',
                        __DIR__ . '/fonts',
                        ),
                    'tempDir' => $this->get_temp_dir(),
                    'mode' => $mode,
                    'default_font_size' => 7,
                    'fontdata' => [
                        "arial" => [
                            'R' => "Arial.ttf",
                            'B' => "ArialBold.ttf",
                            'I' => "ArialItalic.ttf",
                            'BI' => "ArialBoldItalic.ttf",
                        ],
                        "times" => [
                            'R' => "Times.ttf",
                            'B' => "TimesBold.ttf",
                            'I' => "TimesItalic.ttf",
                            'BI' => "TimesBoldItalic.ttf",
                        ],
                        "courier" => [
                            'R' => "CourierNew.ttf",
                            'B' => "CourierNewBold.ttf",
                            'I' => "CourierNewItalic.ttf",
                            'BI' => "CourierNewBoldItalic.ttf",
                        ]
                    ],
                    'default_font' => $default_font,
                    'margin_left' => $this->options['margin_left'],
                    'margin_right' => $this->options['margin_right'],
                    'margin_top' => $this->options['margin_top'],
                    'margin_bottom' => $this->options['margin_bottom'],
                    'margin_header' => $this->options['margin_header'],
                ];
            } else {
                $mpdf_init_options = [
                    'mode' => $mode,
                    'tempDir' => $this->get_temp_dir(),
                    'default_font_size' => 7,
                    'default_font' => $default_font,
                    'margin_left' => $this->options['margin_left'],
                    'margin_right' => $this->options['margin_right'],
                    'margin_top' => $this->options['margin_top'],
                    'margin_bottom' => $this->options['margin_bottom'],
                    'margin_header' => $this->options['margin_header'],
                ];
            }
            $mpdf_init_options['restrictColorSpace'] = $this->options['colorspace'];
            $mpdf_init_options = array_merge($mpdf_init_options, $page_type_settings);
            $mpdf_init_options = apply_filters("Bread_Mpdf_Init_Options", $mpdf_init_options, $this->options);
            @ob_end_clean();
            // We load mPDF only when we need to and as late as possible.  This prevents
            // conflicts with other plugins that use the same PSRs in different versions
            // by simply clobbering the other definitions.  Since we generate the PDF then
            // die, we shouldn't create any conflicts ourselves.
            require_once plugin_dir_path(__FILE__).'mpdf/vendor/autoload.php';
            $this->mpdf = new mPDF($mpdf_init_options);
            $this->mpdf->setAutoBottomMargin = 'pad';
            $this->mpdf->shrink_tables_to_fit = 1;
            // TODO: Adding a page number really could just be an option or tag.
            if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                $this->mpdf->DefHTMLFooterByName('MyFooter', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">'.$this->options['nonmeeting_footer'].'</div>');
                $this->mpdf->DefHTMLFooterByName('_default', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">'.$this->options['nonmeeting_footer'].'</div>');
                $this->mpdf->DefHTMLFooterByName('Meeting1Footer', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">'.$this->options['meeting1_footer'].'</div>');
                $this->mpdf->DefHTMLFooterByName('Meeting2Footer', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">'.$this->options['meeting2_footer'].'</div>');
            }

            $this->mpdf->simpleTables = false;
            $this->mpdf->useSubstitutions = false;
            $this->mpdf->mirrorMargins = false;
            $this->mpdf->list_indent_first_level = 1; // 1 or 0 - whether to indent the first level of a list
            // LOAD a stylesheet
            $header_stylesheet = file_get_contents(plugin_dir_path(__FILE__).'css/mpdfstyletables.css');
            $this->mpdf->WriteHTML($header_stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
            $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['content_line_height']);
            $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
            if ($this->options['column_line'] == 1 &&
                ($this->options['page_fold'] == 'tri' || $this->options['page_fold'] == 'quad')) {
                $html = '<body style="background-color:#fff;">';
                if ($this->options['page_fold'] == 'tri') {
                    $html .= '<table style="background-color: #fff;width: 100%; border-collapse: collapse;">
					<tbody>
					<tr>
					<td style="background-color: #fff;width: 33.33%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 33.33%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 33.33%; height: 279.4mm;">&nbsp;</td>
					</tr>
					</tbody>
					</table></body>';
                }
                if ($this->options['page_fold'] == 'quad') {
                    $html .= '<table style="background-color: #fff;width: 100%; border-collapse: collapse;">
					<tbody>
					<tr>
					<td style="background-color: #fff;width: 25%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 25%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 25%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 25%; height: 279.4mm;">&nbsp;</td>
					</tr>
					</tbody>
					</table>';
                }
                $mpdf_column=new mPDF([
                    'mode' => $mode,
                    'tempDir' => $this->get_temp_dir(),
                    'format' => $mpdf_init_options['format'],
                    'default_font_size' => 7,
                    'default_font' => $default_font,
                    'margin_left' => $this->options['margin_left'],
                    'margin_right' => $this->options['margin_right'],
                    'margin_top' => $this->options['margin_top'],
                    'margin_bottom' => $this->options['margin_bottom'],
                    'margin_footer' => 0,
                    'orientation' => 'P',
                    'restrictColorSpace' => $this->options['colorspace'],
                ]);

                $mpdf_column->WriteHTML($html);
                $FilePath = $this->get_temp_dir(). DIRECTORY_SEPARATOR . $this->get_FilePath('_column');
                $mpdf_column->Output($FilePath, 'F');
                $h = \fopen($FilePath, 'rb');
                $stream = new \setasign\Fpdi\PdfParser\StreamReader($h, false);
                $import_streams[$FilePath] = $stream;
                $pagecount = $this->mpdf->SetSourceFile($stream);
                $tplId = $this->mpdf->importPage($pagecount);
                $this->mpdf->SetPageTemplate($tplId);
            }

            $this->section_shortcodes = array(
                '<h2>'                          => '<h2 style="font-size:'.$this->options['front_page_font_size'] . 'pt!important;">',
                '<div>[page_break]</div>'       =>  '<pagebreak />',
                '<p>[page_break]</p>'           =>  '<pagebreak />',
                '[page_break]'                  =>  '<pagebreak />',
                '<!--nextpage-->'               =>  '<pagebreak />',
                "[area]"                        =>  strtoupper($this->options['service_body_1']),
                '<div>[new_column]</div>'       =>  '<columnbreak />',
                '<p>[new_column]</p>'           =>  '<columnbreak />',
                '[new_column]'                  =>  '<columnbreak />',
                '[page_break no_page_number]'   => '<pagebreak /><sethtmlpagefooter name="" value="0" />',
                '[start_page_numbers]'          => '<sethtmlpagefooter name="MyFooter" page="ALL" value="1" />',
                "[month_lower]"                 => date("F"),
                "[month_upper]"                 => strtoupper(date("F")),
                "[month]"                       => strtoupper(date("F")),
                "[day]"                         => strtoupper(date("j")),
                "[year]"                        => strtoupper(date("Y")),
                "[service_body]"                => strtoupper($this->options['service_body_1']),
                "[service_body_1]"              => strtoupper($this->options['service_body_1']),
                "[service_body_2]"              => strtoupper($this->options['service_body_2']),
                "[service_body_3]"              => strtoupper($this->options['service_body_3']),
                "[service_body_4]"              => strtoupper($this->options['service_body_4']),
                "[service_body_5]"              => strtoupper($this->options['service_body_5']),

            );
            $this->unique_areas = $this->get_areas();
            // Extensions
            $this->section_shortcodes = apply_filters("Bread_Section_Shortcodes", $this->section_shortcodes, $this->unique_areas, $this->formats_used);

            if (isset($this->options['pageheader_content'])) {
                $data = $this->options['pageheader_content'];
                $this->standard_shortcode_replacement($data, 'pageheader');
                $header_style = "vertical-align: top; text-align: center; font-weight: bold;margin-top:3px;margin-bottom:3px;";
                $header_style .= "color:".$this->options['pageheader_textcolor'].";";
                $header_style .= "background-color:".$this->options['pageheader_backgroundcolor'].";";
                $header_style .= "font-size:".$this->options['pageheader_fontsize']."pt;";
                $header_style .= "line-height:".$this->options['content_line_height'].";";

                $this->mpdf->SetHTMLHeader(
                    '<div style="'.$header_style.'">'.$data.'</div>',
                    'O'
                );
            }
            if (isset($this->options['watermark'])) {
                $this->mpdf->SetWatermarkImage($this->options['watermark'], 0.2, 'F');
                $this->mpdf->showWatermarkImage = true;
            }
            $sort_keys = 'weekday_tinyint,start_time,meeting_name';
            $get_used_formats = '&get_used_formats';
            $select_language = '';
            if ($this->options['weekday_language'] != $this->lang) {
                $select_language = '&lang_enum='.$this->getSingleLanguage($this->options['weekday_language']);
            }
            if ($this->options['used_format_1'] == '') {
                $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys$get_used_formats$select_language");
            } elseif ($this->options['used_format_1'] != '') {
                $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys&get_used_formats&formats[]=".$this->options['used_format_1'].$select_language);
            }

            $result = json_decode(wp_remote_retrieve_body($results), true);
            if (!empty($this->options['extra_meetings'])) {
                $extras = "";
                foreach ((array)$this->options['extra_meetings'] as $value) {
                    $data = array(" [", "]");
                    $value = str_replace($data, "", $value);
                    $extras .= "&meeting_ids[]=".$value;
                }

                $extra_results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults&sort_keys=".$sort_keys."".$extras."".$get_used_formats.$select_language);
                $extra_result = json_decode(wp_remote_retrieve_body($extra_results), true);
                if ($extra_result <> null) {
                    $result_meetings = array_merge($result['meetings'], $extra_result['meetings']);
                    foreach ($result_meetings as $key => $row) {
                        $weekday[$key] = $row['weekday_tinyint'];
                        $start_time[$key] = $row['start_time'];
                    }

                    array_multisort($weekday, SORT_ASC, $start_time, SORT_ASC, $result_meetings);
                    $this->formats_used = array_merge($result['formats'], $extra_result['formats']);
                } else {
                    $this->formats_used = $result['formats'];
                    $result_meetings = $result['meetings'];
                }
            } else {
                $this->formats_used = $result['formats'];
                $result_meetings = $result['meetings'];
            }

            if ($result_meetings == null) {
                echo "<script type='text/javascript'>\n";
                echo "document.body.innerHTML = ''";
                echo "</script>";
                echo '<div style="font-size: 20px;text-align:center;font-weight:normal;color:#F00;margin:0 auto;margin-top: 30px;"><p>No Meetings Found</p><p>Or</p><p>Internet or Server Problem</p><p>'.$this->options['root_server'].'</p><p>Please try again or contact your BMLT Administrator</p></div>';
                exit;
            }
            $this->adjust_timezone($result_meetings, $this->target_timezone);
            $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetFormats$select_language");
            $this->formats_all = json_decode(wp_remote_retrieve_body($results), true);
            if ($this->options['asm_language']=='') {
                $this->options['asm_language'] = $this->options['weekday_language'];
            }
            $this->formats_by_key[$this->options['weekday_language']] = array();
            foreach ($this->formats_all as $thisFormat) {
                $this->formats_by_key[$this->options['weekday_language']][$thisFormat['key_string']] = $thisFormat;
                if ($thisFormat['world_id'] == 'WCHR') {
                    $this->wheelchair_format = $thisFormat;
                }
            }
            if (isset($this->options['asm_format_key']) && strlen($this->options['asm_format_key'])>0) {
                if ($this->options['weekday_language'] != $this->options['asm_language']) {
                    $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetFormats&lang_enum=".$this->getSingleLanguage($this->options['asm_language']));
                    $formats_all = json_decode(wp_remote_retrieve_body($results), true);
                    $this->sortBySubkey($formats_all, 'key_string');
                    $this->formats_by_key[$this->options['asm_language']] = array();
                    foreach ($formats_all as $thisFormat) {
                            $this->formats_by_key[$this->options['asm_language']][$thisFormat['key_string']] = $thisFormat;
                        if ($thisFormat['key_string']==$this->options['asm_format_key']) {
                            $this->options['asm_format_id'] = $thisFormat['id'];
                        }
                    }
                } elseif (substr($this->options['asm_format_key'], 0, 1)!='@') {
                    if (isset($this->formats_by_key[$this->options['weekday_language']][$this->options['asm_format_key']])) {
                        $this->options['asm_format_id'] = $this->formats_by_key[$this->options['weekday_language']][$this->options['asm_format_key']]['id'];
                    }
                }
            }
            if (strpos($this->options['custom_section_content'].$this->options['front_page_content'].$this->options['last_page_content'], '[format_codes_used_basic_es') !== false) {
                if ($this->options['used_format_1'] == '') {
                    $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=time$get_used_formats&lang_enum=es");
                } else {
                    $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=time&get_used_formats&lang_enum=es&formats[]=".$this->options['used_format_1']);
                }
                $result_es = json_decode(wp_remote_retrieve_body($results), true);
                $this->formats_spanish = $result_es['formats'];
                $this->sortBySubkey($this->formats_spanish, 'key_string');
            }
            if (strpos($this->options['custom_section_content'].$this->options['front_page_content'].$this->options['last_page_content'], '[format_codes_used_basic_fr') !== false) {
                if ($this->options['used_format_1'] == '') {
                    $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=time$get_used_formats&lang_enum=fr");
                } else {
                    $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=time&get_used_formats&lang_enum=fr&formats[]=".$this->options['used_format_1']);
                }
                $result_fr = json_decode(wp_remote_retrieve_body($results), true);
                $this->formats_french = $result_fr['formats'];
                $this->sortBySubkey($this->formats_french, 'key_string');
            }

            if ($this->options['include_asm'] === '0') {
                $countmax = count($this->formats_used);
                for ($count = 0; $count < $countmax; $count++) {
                    if ($this->formats_used[$count]['key_string'] == $this->options['asm_format_key']) {
                        unset($this->formats_used[$count]);
                    }
                }
                $this->formats_used = array_values($this->formats_used);
            }
            $this->sortBySubkey($this->formats_used, 'key_string');
            $this->sortBySubkey($this->formats_all, 'key_string');

            $this->meeting_count = count($result_meetings);

            $result_meetings = $this->orderByWeekdayStart($result_meetings);
            if ($this->options['page_fold'] === 'full' || $this->options['page_fold'] === 'half' || $this->options['page_fold'] === 'flyer') {
                $num_columns = 0;
            } elseif ($this->options['page_fold'] === 'tri') {
                $num_columns = 3;
            } elseif ($this->options['page_fold'] === 'quad') {
                $num_columns = 4;
            } elseif ($this->options['page_fold'] === '') {
                $this->options['page_fold'] = 'quad';
                $num_columns = 4;
            }

            $this->mpdf->SetColumns($num_columns, '', $this->options['column_gap']);
            if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                $this->write_front_page();
            }
            $this->mpdf->WriteHTML('td{font-size: '.$this->options['content_font_size']."pt;line-height:".$this->options['content_line_height'].';background-color:#ffffff00;}', 1);
            $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['content_font_size'] . 'pt');
            $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['content_line_height']);
            $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
            $this->upgradeHeaderData();
            if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                $this->WriteHTML('<sethtmlpagefooter name="Meeting1Footer" page="ALL" />');
            }
            $this->writeMeetings($result_meetings, $this->options['meeting_template_content'], $this->options['weekday_language'], $this->options['include_asm']==0 ? -1 : 0, true);

            if ($this->options['page_fold'] !== 'half' && $this->options['page_fold'] !== 'full') {
                $this->write_custom_section();
                $this->write_front_page();
            } else {
                $this->WriteHTML('<sethtmlpagefooter name="MyFooter" page="ALL" />');
                if (trim($this->options['last_page_content']) !== '') {
                    $this->write_last_page();
                }
            }
            $this->mpdf->SetDisplayMode('fullpage', 'two');
            if ($this->options['page_fold'] == 'half') {
                $FilePath = $this->get_temp_dir(). DIRECTORY_SEPARATOR . $this->get_FilePath('_half');
                $this->mpdf->Output($FilePath, 'F');
                $mpdfOptions = [
                        'mode' => $mode,
                        'tempDir' => $this->get_temp_dir(),
                        'default_font_size' => '',
                        'margin_left' => 0,
                        'margin_right' => 0,
                        'margin_top' => 0,
                        'margin_bottom' => 0,
                        'margin_footer' => 0,
                        'orientation' => 'L',
                        'restrictColorSpace' => $this->options['colorspace'],
                    ];
                $ps = $this->options['page_size'];
                if ($ps=='ledger') {
                    $mpdfOptions['format'] = 'tabloid';
                } elseif ($ps == '5inch') {
                    $mpdfOptions['format'] = array(197.2,279.4);
                } else {
                    $mpdfOptions['format'] = $ps.'-L';
                }
                $mpdfOptions['curlUserAgent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:108.0) Gecko/20100101 Firefox/108.0';
                $mpdfOptions = apply_filters("Bread_Mpdf_Init_Options", $mpdfOptions, $this->options);
                $mpdftmp=new mPDF($mpdfOptions);
                $this->mpdf->shrink_tables_to_fit = 1;
                $ow = $mpdftmp->h;
                $oh = $mpdftmp->w;
                $pw = $mpdftmp->w / 2;
                $ph = $mpdftmp->h;
                $h = \fopen($FilePath, 'rb');
                $stream = new \setasign\Fpdi\PdfParser\StreamReader($h, false);
                $import_streams[$FilePath] = $stream;
                $pagecount = $mpdftmp->SetSourceFile($stream);
                $pp = $this->get_booklet_pages($pagecount);
                foreach ($pp as $v) {
                    $mpdftmp->AddPage();
                    if ($v[0]>0 & $v[0]<=$pagecount) {
                        $tplIdx = $mpdftmp->importPage($v[0]);
                        $mpdftmp->UseTemplate($tplIdx, 0, 0, $pw, $ph);
                    }
                    if ($v[1]>0 & $v[1]<=$pagecount) {
                        $tplIdx = $mpdftmp->importPage($v[1]);
                        $mpdftmp->UseTemplate($tplIdx, $pw, 0, $pw, $ph);
                    }
                }
                $this->mpdf = $mpdftmp;
            } else if ($this->options['page_fold'] == 'full' && $this->options['booklet_pages']) {
                $FilePath = $this->get_temp_dir(). DIRECTORY_SEPARATOR . $this->get_FilePath('_full');
                $this->mpdf->Output($FilePath, 'F');
                $mpdfOptions = [
                    'mode' => $mode,
                    'tempDir' => $this->get_temp_dir(),
                    'default_font_size' => '',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                    'margin_footer' => 6,
                    'orientation' => $this->options['page_orientation'],
                    'restrictColorSpace' => $this->options['colorspace'],
                ];
                $mpdfOptions['format'] =  $this->options['page_size']."-".$this->options['page_orientation'];
                /** this is because mPDF has an old UA and SiteGround is complaining
                 * It will be fixed in the next release of mPDF, but we can't wait that long.
                 * But, when a new mPDF comes out, remove this line.
                 */
                $mpdf_config['curlUserAgent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:108.0) Gecko/20100101 Firefox/108.0';
                /* */
                $mpdfOptions = apply_filters("Bread_Mpdf_Init_Options", $mpdfOptions, $this->options);
                $mpdftmp=new mPDF($mpdfOptions);
                $this->mpdf->shrink_tables_to_fit = 1;
                //$mpdftmp->SetImportUse();
                $h = \fopen($FilePath, 'rb');
                $stream = new \setasign\Fpdi\PdfParser\StreamReader($h, false);
                $import_streams[$FilePath] = $stream;
                $np = $mpdftmp->SetSourceFile($stream);
                $pp = 4*ceil($np/4);
                for ($i=1; $i<$np; $i++) {
                    $mpdftmp->AddPage();
                    $tplIdx = $mpdftmp->ImportPage($i);
                    $mpdftmp->UseTemplate($tplIdx);
                }
                for ($i=$np; $i<$pp; $i++) {
                    $mpdftmp->AddPage();
                }
                $mpdftmp->AddPage();
                $tplIdx = $mpdftmp->ImportPage($np);
                $mpdftmp->UseTemplate($tplIdx);
                $this->mpdf = $mpdftmp;
            } else if ($this->options['page_fold'] == 'flyer') {
                $FilePath = $this->get_temp_dir(). DIRECTORY_SEPARATOR . $this->get_FilePath('_flyer');
                $this->mpdf->Output($FilePath, 'F');
                $mpdfOptions = [
                    'mode' => $mode,
                    'tempDir' => $this->get_temp_dir(),
                    'default_font_size' => '',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                    'margin_footer' => 6,
                    'format' => $this->options['page_size'].'-L',
                    'orientation' => 'L',
                    'restrictColorSpace' => $this->options['colorspace'],
                ];
                $mpdftmp=new mPDF($mpdfOptions);
                $this->mpdf->shrink_tables_to_fit = 1;
                //$mpdftmp->SetImportUse();
                $h = \fopen($FilePath, 'rb');
                $stream = new \setasign\Fpdi\PdfParser\StreamReader($h, false);
                $import_streams[$FilePath] = $stream;
                $np = $mpdftmp->SetSourceFile($stream);
                $ow = $mpdftmp->w;
                $oh = $mpdftmp->h;
                $fw = $ow / 3;
                $mpdftmp->AddPage();
                $tplIdx = $mpdftmp->importPage(1);
                $mpdftmp->UseTemplate($tplIdx, 0, 0);
                $mpdftmp->UseTemplate($tplIdx, $fw, 0);
                $mpdftmp->UseTemplate($tplIdx, $fw+$fw, 0);
                $sep = $this->columnSeparators($oh);
                if (!empty($sep)) {
                    $mpdftmp->writeHTML($sep);
                }
                $mpdftmp->AddPage();
                $tplIdx = $mpdftmp->ImportPage(2);
                $mpdftmp->UseTemplate($tplIdx, 0, 0);
                $mpdftmp->UseTemplate($tplIdx, $fw, 0);
                $mpdftmp->UseTemplate($tplIdx, $fw+$fw, 0);
                if (!empty($sep)) {
                    $mpdftmp->writeHTML($sep);
                }
                $this->mpdf = $mpdftmp;
            }
            if ($this->options['include_protection'] == 1) {
                // 'copy','print','modify','annot-forms','fill-forms','extract','assemble','print-highres'
                $this->mpdf->SetProtection(array('copy','print','print-highres'), '', $this->options['protection_password']);
            }
            if (headers_sent()) {
                echo '<div id="message" class="error"><p>Headers already sent before PDF generation</div>';
            } else {
                if (intval($this->options['cache_time']) > 0 && ! isset($_GET['nocache'])
                    && !isset($_GET['custom_query'])) {
                    $content = $this->mpdf->Output('', 'S');
                    $content = bin2hex($content);
                    $transient_key = $this->get_TransientKey();
                    set_transient($transient_key, $content, intval($this->options['cache_time']) * HOUR_IN_SECONDS);
                }
                $FilePath = apply_filters("Bread_Download_Name", $this->get_FilePath(), $this->options['service_body_1'], $this->allSettings[$this->loaded_setting]);
                $this->mpdf->Output($FilePath, 'I');
            }
            foreach ($import_streams as $FilePath => $stream) {
                @unlink($FilePath);
            }
            $this->rrmdir($this->get_temp_dir());
            exit;
        }
        function rrmdir($dir)
        {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object)) {
                            $this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                        } else {
                            @unlink($dir. DIRECTORY_SEPARATOR .$object);
                        }
                    }
                }
                @rmdir($dir);
            }
        }
        function brute_force_cleanup($dir)
        {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (str_starts_with($object, "bread")) {
                            $filename = $dir . DIRECTORY_SEPARATOR .$object;
                            if (time()-filemtime($filename) > 24 * 3600) {
                                $this->rrmdir($filename);
                            }
                        }
                    }
                }
            }
        }
        function orderByWeekdayStart(&$result_meetings)
        {
            $days = array_column($result_meetings, 'weekday_tinyint');
            $today_str = $this->options['weekday_start'];
            return array_merge(
                array_splice($result_meetings, array_search($today_str, $days)),
                array_splice($result_meetings, 0)
            );
        }
        function get_FilePath($pos = '')
        {
            $site = '';
            if (is_multisite()) {
                $site = get_current_blog_id().'_';
            }
            return "meetinglist_".$site.$this->loaded_setting.$pos.'_'.strtolower(date("njYghis")).".pdf";
        }
        function adjust_timezone(&$meetings, $target_timezone)
        {
            if (!$target_timezone) {
                return;
            }
            $target_midnight = new DateTime();
            $target_midnight->setTimezone($target_timezone);
            $target_midnight->setTime(23, 59);
            $target_yesterday = new DateTime();
            $target_yesterday->setTimezone($target_timezone);
            $target_yesterday->setTime(0, 0);
            foreach ($meetings as &$meeting) {
                if (!empty($meeting['time_zone'])) {
                    $meeting_time_zone = timezone_open($meeting['time_zone']);
                    if ($meeting_time_zone) {
                        $date = date_create($meeting['start_time'], $meeting_time_zone);
                        date_timezone_set($date, $target_timezone);
                        $meeting['start_time'] = $date->format('H:i');
                        if ($date >= $target_midnight) {
                            $meeting['weekday_tinyint'] = $meeting['weekday_tinyint']+1;
                            if ($meeting['weekday_tinyint']==8) {
                                $meeting['weekday_tinyint'] = 1;
                            }
                        } elseif ($date < $target_yesterday) {
                            $meeting['weekday_tinyint'] = $meeting['weekday_tinyint']-1;
                            if ($meeting['weekday_tinyint']==0) {
                                $meeting['weekday_tinyint'] = 7;
                            }
                        }
                    }
                }
            }
            usort($meetings, array($this, "sortDayTime"));
        }
        function sortDayTime($a, $b)
        {
            if ($a['weekday_tinyint'] < $b['weekday_tinyint']) {
                return -1;
            }
            if ($a['weekday_tinyint'] > $b['weekday_tinyint']) {
                return 1;
            }
            if ($a['start_time'] < $b['start_time']) {
                return -1;
            }
            if ($a['start_time'] > $b['start_time']) {
                return 1;
            }
            if ($a['duration_time'] < $b['duration_time']) {
                return -1;
            }
            if ($a['duration_time'] > $b['duration_time']) {
                return 1;
            }
            return 0;
        }
        // include_asm = 0  -  let everything through
        //               1  -  only meetings with asm format
        //              -1  -  only meetings without asm format
        function writeMeetings($result_meetings, $template, $lang, $include_asm, $asm_flag)
        {
            $headerMeetings = $this->getHeaderMeetings($result_meetings, $lang, $include_asm, $asm_flag);
            $unique_heading = $this->getUniqueHeadings($headerMeetings);

            $header_style = "color:".$this->options['header_text_color'].";";
            $header_style .= "background-color:".$this->options['header_background_color'].";";
            $header_style .= "font-size:".$this->options['header_font_size']."pt;";
            $header_style .= "line-height:".$this->options['content_line_height'].";";
            $header_style .= "text-align:center;padding-top:2px;padding-bottom:3px;";

            if ($this->options['header_uppercase'] == 1) {
                $header_style .= 'text-transform: uppercase;';
            }
            if ($this->options['header_bold'] == 0) {
                $header_style .= 'font-weight: normal;';
            }
            if ($this->options['header_bold'] == 1) {
                $header_style .= 'font-weight: bold;';
            }
            $cont = '('.$this->translate[$lang]['CONT'].')';


            $template = wpautop(stripslashes($template));
            $template = preg_replace('/[[:^print:]]/', ' ', $template);

            $template = str_replace("&nbsp;", " ", $template);
            $analysedTemplate = $this->analyseTemplate($template);
            $first_meeting = true;
            $newMajorHeading = false;
            /***
             * You might be wondering why I am not using keep-with-table...
             * The problem is, keep with table doesn't work with columns, only pages.
             * We want to check that a header and at least one meeting fits, so we write it
             * to a test PDF, see how big it is, and check if it will fit.
             */
            $test_pages = deep_copy($this->mpdf);
            foreach ($unique_heading as $this_heading_raw) {
                $newMajorHeading = true;
                if ($this->skip_heading($this_heading_raw)) {
                    continue;
                }
                $this_heading = $this->remove_sort_key($this_heading_raw);
                $unique_subheading = array_keys($headerMeetings[$this_heading_raw]);
                asort($unique_subheading, SORT_NATURAL | SORT_FLAG_CASE);
                foreach ($unique_subheading as $this_subheading_raw) {
                    $newSubHeading = true;
                    $this_subheading = $this->remove_sort_key($this_subheading_raw);
                    foreach ($headerMeetings[$this_heading_raw][$this_subheading_raw] as $meeting_value) {
                        $header = '';
                        if ($newSubHeading && !empty($this->options['combine_headings'])) {
                            $header_string =  $this->options['combine_headings'];
                            $header_string =  str_replace('main_grouping', $this_heading, $header_string);
                            $header_string =  str_replace('subgrouping', $this_subheading, $header_string);
                            $header .= "<div style='".$header_style."'>".$header_string."</div>";
                        } elseif (!empty($this->options['subgrouping'])) {
                            if ($newMajorHeading === true) {
                                $xtraMargin = '';
                                if (!$first_meeting) {
                                    $xtraMargin = 'margin-top:2pt;';
                                }
                                $header .= '<div style="'.$header_style.$xtraMargin.'">'.$this_heading."</div>";
                            }
                            if ($newSubHeading && $this->options['sub_header_shown']=='display') {
                                $header .= "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>".$this_subheading."</p>";
                            }
                        } elseif ($newMajorHeading === true) {
                            $header .= "<div style='".$header_style."'>".$this_heading."</div>";
                        }
                        if ($this->options['suppress_heading']==1) {
                            $header = '';
                        }
                        $data = $header . $this->write_single_meeting(
                            $meeting_value,
                            $template,
                            $analysedTemplate,
                            $meeting_value['area_name']
                        );
                        $this->writeBreak($test_pages);
                        $y_startpos = $test_pages->y;
                        @$test_pages->WriteHTML($data);
                        $y_diff = $test_pages->y - $y_startpos;
                        if ($y_diff >= $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height) {
                            $this->writeBreak($this->mpdf);
                            if (!$newMajorHeading && $this->options['cont_header_shown']) {
                                $header = "<div style='".$header_style."'>".$this_heading." " . $cont . "</div>";
                                $data = $header.$data;
                            }
                        }
                        $this->WriteHTML($data);
                        $first_meeting = false;
                        $newSubHeading = false;
                        $newMajorHeading = false;
                    }
                }
            }
        }
        function asm_test($value, $flag = false)
        {
            if (empty($this->options['asm_format_key'])) {
                return false;
            }
            $format_key = $this->options['asm_format_key'];
            if ($format_key == "@Virtual@") {
                if ($flag && $this->isHybrid($value)) {
                    return false;
                }
                return $this->isVirtual($value) || $this->isHybrid($value);
            }
            if ($format_key == "@F2F@") {
                return !$this->isVirtual($value) || $this->isHybrid($value);
            }
            $enFormats = explode(",", $value['formats']);
            return in_array($format_key, $enFormats);
        }
        function isHybrid($value)
        {
            $enFormats = explode(",", $value['formats']);
            return in_array('HY', $enFormats);
        }
        function isVirtual($value)
        {
            $enFormats = explode(",", $value['formats']);
            return in_array('VM', $enFormats);
        }
        // include_asm = 0  -  let everything through
        //               1  -  only meetings with asm format
        //              -1  -  only meetings without asm format
        function getHeaderMeetings(&$result_meetings, $lang, $include_asm, $asm_flag)
        {
            $levels = $this->getHeaderLevels();
            $headerMeetings = array();
            foreach ($result_meetings as &$value) {
                $value = $this->enhance_meeting($value, $lang);
                $asm_test = $this->asm_test($value, $asm_flag);
                if ((( $include_asm < 0 && $asm_test ) ||
                    ( $include_asm > 0 && !$asm_test ))) {
                        continue;
                }

                $main_grouping = $this->getHeaderItem($value, 'main_grouping');
                if (!isset($headerMeetings[$main_grouping])) {
                    $headerMeetings[$main_grouping] = array();
                    if ($levels == 1) {
                        $headerMeetings[$main_grouping][0] = array();
                    }
                }
                if ($levels == 2) {
                    $subgrouping = $this->getHeaderItem($value, 'subgrouping');
                    if (!isset($headerMeetings[$main_grouping][$subgrouping])) {
                        $headerMeetings[$main_grouping][$subgrouping] = array();
                    }
                    $headerMeetings[$main_grouping][$subgrouping][] = $value;
                } else {
                    $headerMeetings[$main_grouping][0][] = $value;
                }
            }
            return $headerMeetings;
        }
        function getUniqueHeadings($headerMeetings)
        {
            $unique_heading = array_keys($headerMeetings);
            asort($unique_heading, SORT_NATURAL | SORT_FLAG_CASE);
            return $unique_heading;
        }
        function remove_sort_key($this_heading)
        {
            if (mb_substr($this_heading, 0, 1)=='[') {
                $end = strpos($this_heading, ']');
                if ($end>0) {
                    return trim(substr($this_heading, $end+1));
                }
            }
            return $this_heading;
        }
        function skip_heading($this_heading)
        {
            return (mb_substr($this_heading, 0, 5)=='[XXX]');
        }
        function writeBreak($mpdf)
        {
            if ($this->options['page_fold'] === 'half' || $this->options['page_fold'] === 'full') {
                $mpdf->WriteHTML("<pagebreak>");
            } else {
                $mpdf->WriteHTML("<columnbreak />");
            }
        }
        function getOptionForDisplay($option, $default = '')
        {
            return empty($this->options[$option])?$default:esc_html($this->options[$option]);
        }
        function columnSeparators($oh)
        {
            if ($this->options['column_line'] == 1) {
                return '<body style="background:none;">
				<table style="background: none;width: 100%; height:'.$oh.'mm border-collapse: collapse;">
					<tbody>
					<tr>
					<td style="width: 33.33%; border-right: 1px solid '.$this->options['col_color']. '; height: '.$oh.'mm;">&nbsp;</td>
					<td style="width: 33.33%; border-right: 1px solid '.$this->options['col_color']. '; height: '.$oh.'mm;">&nbsp;</td>
					<td style="width: 33.33%; height: 100%;">&nbsp;</td>
					</tr>
					</tbody>
					</table>';
            }
        }
        function getHeaderLevels()
        {
            if (!empty($this->options['subgrouping'])) {
                return 2;
            }
            return 1;
        }
        function getHeaderItem($value, $name)
        {
            if (empty($this->options[$name])) {
                    return '';
            }
            $grouping = '';
            if ($this->options[$name]=='service_body_bigint') {
                foreach ($this->unique_areas as $unique_area) {
                    $area_data = explode(',', $unique_area);
                    $area_name = $this->arraySafeGet($area_data);
                    $area_id = $this->arraySafeGet($area_data, 1);
                    if ($area_id === $value['service_body_bigint']) {
                        return $area_name;
                    }
                }
                return 'Area not found';
            } elseif ($this->options[$name]=='day') {
                $off = intval($this->options['weekday_start']);
                $day = intval($value['weekday_tinyint']);
                if ($day < $off) {
                    $day = $day + 7;
                }
                return '['.str_pad($day, 2, '0', STR_PAD_LEFT).']'.$value['day'];
            } elseif (isset($value[$this->options[$name]])) {
                $grouping = $this->parse_field($value[$this->options[$name]]);
            }
            $alt = '';
            if ($grouping==''
                && !empty($this->options[$name.'_alt'])
                && isset($value[$this->options[$name.'_alt']])) {
                $grouping = $this->parse_field($value[$this->options[$name.'_alt']]);
                $alt = '_alt';
            }
            if (strlen(trim($grouping))==0) {
                return 'NO DATA';
            }
            if (!empty($this->options[$name.$alt.'_suffix'])) {
                return $grouping.' '.$this->options[$name.$alt.'_suffix'];
            }
            return $grouping;
        }
        function upgradeHeaderData()
        {
            $this->options['combine_headings'] = '';
            if ($this->options['meeting_sort'] === 'user_defined') {
                if ($this->options['sub_header_shown'] == 'combined') {
                    $this->options['combine_headings'] = 'main_grouping - subgrouping';
                }
                return;
            }
            unset($this->options['subgrouping']);
            if ($this->options['meeting_sort'] === 'state') {
                $this->options['main_grouping'] = 'location_province';
                $this->options['subgrouping'] = 'location_municipality';
                $this->options['combine_headings'] = 'subgrouping, main_grouping';
            } elseif ($this->options['meeting_sort'] === 'city') {
                $this->options['main_grouping'] = 'location_municipality';
            } elseif ($this->options['meeting_sort'] === 'borough') {
                $this->options['main_grouping'] = 'location_city_subsection';
                $this->options['main_grouping_suffix'] = $this->options['borough_suffix'];
            } elseif ($this->options['meeting_sort'] === 'county') {
                $this->options['main_grouping'] = 'location_sub_province';
                $this->options['main_grouping_alt_suffix'] = $this->options['county_suffix'];
            } elseif ($this->options['meeting_sort'] === 'borough_county') {
                $this->options['main_grouping'] = 'location_city_subsection';
                $this->options['main_grouping_suffix'] = $this->options['borough_suffix'];
                $this->options['main_grouping_alt'] = 'location_sub_province';
                $this->options['main_grouping_alt_suffix'] = $this->options['county_suffix'];
            } elseif ($this->options['meeting_sort'] === 'neighborhood_city') {
                $this->options['main_grouping'] = 'location_neighborhood';
                $this->options['main_grouping_suffix'] = $this->options['neighborhood_suffix'];
                $this->options['main_grouping_alt'] = 'location_municipality';
                $this->options['main_grouping_alt_suffix'] = $this->options['city_suffix'];
            } elseif ($this->options['meeting_sort'] === 'group') {
                $this->options['main_grouping'] = 'meeting_name';
            } elseif ($this->options['meeting_sort'] === 'weekday_area') {
                $this->options['main_grouping'] = 'day';
                $this->options['subgrouping'] = 'service_body_bigint';
            } elseif ($this->options['meeting_sort'] === 'weekday_city') {
                $this->options['main_grouping'] = 'day';
                $this->options['subgrouping'] = 'location_municipality';
            } elseif ($this->options['meeting_sort'] === 'weekday_county') {
                $this->options['main_grouping'] = 'day';
                $this->options['subgrouping'] = 'location_sub_province';
            } else {
                $this->options['main_grouping'] = 'day';
            }
        }
        function get_area_name($meeting_value)
        {
            foreach ($this->unique_areas as $unique_area) {
                $area_data = explode(',', $unique_area);
                $area_id = $this->arraySafeGet($area_data, 1);
                if ($area_id === $meeting_value['service_body_bigint']) {
                    return $this->arraySafeGet($area_data);
                }
            }
            return '';
        }
        function analyseTemplate($template)
        {
            $arr = preg_split('/\W+/', $template, 0, PREG_SPLIT_OFFSET_CAPTURE);
            $arr = array_reverse($arr, true);
            $ret = array();
            foreach ($arr as $item) {
                if (strlen($item[0])<3) {
                    continue;
                }
                if ($item[0]=='table') {
                    continue;
                }
                if ($item[0]=='tbody') {
                    continue;
                }
                if ($item[0]=='strong') {
                    continue;
                }
                if ($item[0]=='left') {
                    continue;
                }
                if ($item[0]=='right') {
                    continue;
                }
                if ($item[0]=='top') {
                    continue;
                }
                if ($item[0]=='bottom') {
                    continue;
                }
                if ($item[0]=='center') {
                    continue;
                }
                if ($item[0]=='align') {
                    continue;
                }
                if ($item[0]=='font') {
                    continue;
                }
                if ($item[0]=='size') {
                    continue;
                }
                if ($item[0]=='text') {
                    continue;
                }
                if ($item[0]=='style') {
                    continue;
                }
                if ($item[0]=='family') {
                    continue;
                }
                if ($item[0]=='vertical') {
                    continue;
                }
                if ($item[0]=='color') {
                    continue;
                }
                if ($item[0]=='QRCode') {
                    continue;
                }
                if ($item[1]>0 && $template[$item[1]-1]=='['
                    && $template[$item[1]+strlen($item[0])]==']') {
                        $item[0] = '['.$item[0].']';
                        $item[1] = $item[1] - 1;
                        $item[2] = true;
                } else {
                    $item[2] = false;
                }
                $ret[] = $item;
            }
            return $ret;
        }
        function enhance_meeting(&$meeting_value, $lang)
        {
            $duration = explode(':', $meeting_value['duration_time']);
            $minutes = intval($duration[0])*60 + intval($duration[1]) + intval($duration[2]);
            $meeting_value['duration_m'] = $minutes;
            $meeting_value['duration_h'] = rtrim(rtrim(number_format($minutes/60, 2), 0), '.');
            $space = ' ';
            if ($this->options['remove_space'] == 1) {
                $space = '';
            }
            if ($this->options['time_clock'] == null || $this->options['time_clock'] == '12' || $this->options['time_option'] == '') {
                $time_format = "g:i".$space."A";
            } elseif ($this->options['time_clock'] == '24fr') {
                $time_format = "H\hi";
            } else {
                $time_format = "H:i";
            }
            if ($this->options['time_option'] == 1 || $this->options['time_option'] == '') {
                $meeting_value['start_time'] = date($time_format, strtotime($meeting_value['start_time']));
                if ($meeting_value['start_time'] == '12:00PM' || $meeting_value['start_time'] == '12:00 PM') {
                    $meeting_value['start_time'] = 'NOON';
                }
            } elseif ($this->options['time_option'] == '2') {
                $addtime = '+ ' . $minutes . ' minutes';
                $end_time = date($time_format, strtotime($meeting_value['start_time'] . ' ' . $addtime));
                $meeting_value['start_time'] = date($time_format, strtotime($meeting_value['start_time']));
                if ($lang=='fa') {
                    $meeting_value['start_time'] = $this->toPersianNum($end_time).$space.'-'.$space.$this->toPersianNum($meeting_value['start_time']);
                } else {
                    $meeting_value['start_time'] = $meeting_value['start_time'].$space.'-'.$space.$end_time;
                }
            } elseif ($this->options['time_option'] == '3') {
                $time_array = array("1:00", "2:00", "3:00", "4:00", "5:00", "6:00", "7:00", "8:00", "9:00", "10:00", "11:00", "12:00");
                $temp_start_time = date("g:i", strtotime($meeting_value['start_time']));
                $temp_start_time_2 = date("g:iA", strtotime($meeting_value['start_time']));
                if ($temp_start_time_2 == '12:00PM') {
                    $start_time = 'NOON';
                } elseif (in_array($temp_start_time, $time_array)) {
                    $start_time = date("g", strtotime($meeting_value['start_time']));
                } else {
                    $start_time = date("g:i", strtotime($meeting_value['start_time']));
                }
                $addtime = '+ ' . $minutes . ' minutes';
                $temp_end_time = date("g:iA", strtotime($meeting_value['start_time'] . ' ' . $addtime));
                $temp_end_time_2 = date("g:i", strtotime($meeting_value['start_time'] . ' ' . $addtime));
                if ($temp_end_time == '12:00PM') {
                    $end_time = 'NOON';
                } elseif (in_array($temp_end_time_2, $time_array)) {
                    $end_time = date("g".$space."A", strtotime($temp_end_time));
                } else {
                    $end_time = date("g:i".$space."A", strtotime($temp_end_time));
                }
                $meeting_value['start_time'] = $start_time.$space.'-'.$space.$end_time;
            }

            $meeting_value['day_abbr'] = $this->getday($meeting_value['weekday_tinyint'], true, $lang);
            $meeting_value['day'] = $this->getday($meeting_value['weekday_tinyint'], false, $lang);
            $area_name = $this->get_area_name($meeting_value);
            $meeting_value['area_name'] = $area_name;
            $meeting_value['area_i'] = substr($area_name, 0, 1);

            $meeting_value['wheelchair'] = '';
            if (!is_null($this->wheelchair_format)) {
                $fmts = explode(',', $meeting_value['format_shared_id_list']);
                if (in_array($this->wheelchair_format['id'], $fmts)) {
                    $meeting_value['wheelchair'] = '<img src="'.plugin_dir_url(__FILE__) . 'includes/wheelchair.png" width="'.$this->options['wheelchair_size'].'" height="'.$this->options['wheelchair_size'].'">';
                }
            }
            // Extensions.
            return apply_filters("Bread_Enrich_Meeting_Data", $meeting_value, $this->formats_by_key[$lang]);
        }
        function write_single_meeting($meeting_value, $template, $analysedTemplate, $area_name)
        {
            $data = $template;
            $namedValues = array();
            foreach ($meeting_value as $field => $notUsed) {
                $namedValues[$field] = $this->get_field($meeting_value, $field);
            }
            foreach ($this->legacy_synonyms as $syn => $field) {
                $namedValues[$syn] = $namedValues[$field];
            }
            foreach ($analysedTemplate as $item) {
                $name = $item[0];
                if ($item[2]) {
                    $name = substr($name, 1, strlen($name)-2);
                }
                if (isset($namedValues[$name])) {
                    $data = substr_replace($data, $namedValues[$name], $item[1], strlen($item[0]));
                }
            }
            $qr_pos = strpos($data, "[QRCode");
            if ($qr_pos) {
                $qr_end = strpos($data, ']', $qr_pos);
                $data = substr($data, 0, $qr_pos).
                        '<barcode type="QR" disableborder="1" '.
                        substr($data, $qr_pos+8, $qr_end-$qr_pos-8).
                        '/>'.
                        substr($data, $qr_end+1);
            }
            $search_strings = array();
            $replacements = array();
            $clean_up = array(
                '<em></em>'     => '',
                '<em> </em>'    => '',
                '<strong></strong>' => '',
                '<strong> </strong>' => '',
                '<i></i>' => '',
                '<i> </i>' => '',
                '    '          => ' ',
                '   '           => ' ',
                '  '            => ' ',
                '<p></p>'       => '',
                '()'            => '',
                '<br/>'         => 'line_break',
                '<br />'        => 'line_break',
                'line_break line_break' => '<br />',
                'line_breakline_break'  => '<br />',
                'line_break'    => '<br />',
                '<br />,'       => '<br />',
                ', <br />'      => '<br />',
                ',<br />'       => '<br />',
                '<p>,'          => '<p>',
                ", , ,"         => ",",
                ", *,"          => ",",
                ", ,"           => ",",
                " , "           => " ",
                ", ("           => " (",
                ',</'           => '</',
                ', </'          => '</',
            );
            foreach ($clean_up as $key => $value) {
                $search_strings[] = $key;
                $replacements[] = $value;
            }
            $data = str_replace($search_strings, $replacements, $data);
            return $data;
        }
        function get_booklet_pages($np, $backcover = true)
        {
            $lastpage = $np;
            $np = 4*ceil($np/4);
            $pp = array();
            for ($i=1; $i<=$np/2; $i++) {
                $p1 = $np - $i + 1;
                if ($backcover) {
                    if ($i == 1) {
                        $p1 = $lastpage;
                    } else if ($p1 >= $lastpage) {
                        $p1 = 0;
                    }
                }
                if ($i % 2 == 1) {
                    $pp[] = array( $p1,  $i );
                } else {
                    $pp[] = array( $i, $p1 );
                }
            }
            return $pp;
        }

        function write_front_page()
        {

            $this->mpdf->WriteHTML('td{font-size: '.$this->options['front_page_font_size']."pt;line-height:".$this->options['front_page_line_height'].';}', 1);
            $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['front_page_line_height']);
            $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['front_page_font_size'] . 'pt');
            $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
            $this->options['front_page_content'] = wp_unslash($this->options['front_page_content']);
            $this->standard_shortcode_replacement($this->options['front_page_content'], 'front_page');


            $querystring_custom_items = array();
            preg_match_all('/(\[querystring_custom_\d+\])/', $this->options['front_page_content'], $querystring_custom_items);
            foreach ($querystring_custom_items[0] as $querystring_custom_item) {
                $mod_qs_ci = str_replace("]", "", str_replace("[", "", $querystring_custom_item));
                $this->options['front_page_content'] = str_replace($querystring_custom_item, (isset($_GET[$mod_qs_ci]) ? $_GET[$mod_qs_ci] : "NOT SET"), $this->options['front_page_content']);
            }
            $this->writeHTMLwithServiceMeetings($this->options['front_page_content'], 'front_page');
            $this->mpdf->showWatermarkImage = false;
        }

        function write_last_page()
        {
            $this->mpdf->WriteHTML('td{font-size: '.$this->options['last_page_font_size']."pt;line-height:".$this->options['last_page_line_height'].';}', 1);
            $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['last_page_font_size'] . 'pt');
            $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['last_page_line_height']);
            $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
            $this->standard_shortcode_replacement($this->options['last_page_content'], 'last_page');
            $this->writeHTMLwithServiceMeetings($this->options['last_page_content'], 'last_page');
        }

        function write_custom_section()
        {
            $this->mpdf->SetHTMLHeader();
            if (isset($this->options['pageheader_content']) && trim($this->options['pageheader_content'])) {
                $this->mpdf->SetTopMargin($this->options['margin_header']);
            }
            $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['custom_section_line_height']);
            $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['custom_section_font_size'] . 'pt');
            $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
            $this->standard_shortcode_replacement($this->options['custom_section_content'], 'custom_section');
            $this->mpdf->WriteHTML('td{font-size: '.$this->options['custom_section_font_size']."pt;line-height:".$this->options['custom_section_line_height'].';}', 1);
            $this->writeHTMLwithServiceMeetings($this->options['custom_section_content'], 'custom_section');
        }
        function locale_month_replacement($data, $case, $sym)
        {
            $strpos = strpos($data, "[month_$case"."_");
            if ($strpos !== false) {
                $locLang = substr($data, $strpos+13, 2);
                if (!isset($this->translate[$locLang])) {
                    $locLang = 'en';
                }
                $fmt = new IntlDateFormatter(
                    $this->translate[$locLang]['LOCALE'],
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::FULL
                );
                $fmt->setPattern($sym);
                $month = ucfirst(mb_convert_encoding($fmt->format(time()), 'UTF-8', 'ISO-8859-1'));
                if ($case=='upper') {
                    $month = mb_strtoupper($month, 'UTF-8');
                }
                return substr_replace($data, $month, $strpos, 16);
            }
            return $data;
        }
        function standard_shortcode_replacement(&$data, $page)
        {
            $search_strings = array();
            $replacements = array();
            foreach ($this->section_shortcodes as $key => $value) {
                $search_strings[] = $key;
                $replacements[] = $value;
            }

            $search_strings[] = '[meeting_count]';
            $replacements[] =  $this->meeting_count;
            $data = $this->options[$page.'_content'];
            $data = $this->locale_month_replacement($data, 'lower', "LLLL");
            $data = $this->locale_month_replacement($data, 'upper', "LLLL");
            $data = str_replace($search_strings, $replacements, $data);
            $this->replace_format_shortcodes($data, $page);
            $data = str_replace("[date]", strtoupper(date("F Y")), $data);
            if ($this->target_timezone) {
                $data = str_replace('[timezone]', $this->target_timezone->getName(), $data);
            }
        }
        function writeHTML($str)
        {
            //$str = htmlentities($str);
            @$this->mpdf->WriteHTML(wpautop(stripslashes($str)));
        }
        function writeHTMLwithServiceMeetings($data, $page)
        {
            $strs = array('<p>[service_meetings]</p>','[service_meetings]',
                          '<p>[additional_meetings]</p>','[additional_meetings]');

            foreach ($strs as $str) {
                $pos = strpos($data, $str);
                if (!$pos) {
                    continue;
                }
                if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                    $this->WriteHTML('<sethtmlpagefooter name="Meeting2Footer" page="ALL" />');
                }
                $this->WriteHTML(substr($data, 0, $pos));
                $this->write_service_meetings($this->options[$page.'_font_size'], $this->options[$page.'_line_height']);
                if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                    $this->WriteHTML('<sethtmlpagefooter name="MyFooter" page="ALL" />');
                }
                $this->WriteHTML(substr($data, $pos+strlen($str)));
                return;
            }
            $this->WriteHTML($data);
        }
        function replace_format_shortcodes(&$data, $page_name)
        {

            $this->shortcode_formats('[format_codes_used_basic]', false, $this->formats_used, $page_name, $data);
            $this->shortcode_formats('[format_codes_used_detailed]', true, $this->formats_used, $page_name, $data);
            $this->shortcode_formats('[format_codes_used_basic_es]', false, $this->formats_spanish, $page_name, $data);
            $this->shortcode_formats('[format_codes_used_detailed_es]', true, $this->formats_spanish, $page_name, $data);
            $this->shortcode_formats('[format_codes_used_basic_fr]', false, $this->formats_french, $page_name, $data);
            $this->shortcode_formats('[format_codes_all_basic]', false, $this->formats_all, $page_name, $data);
            $this->shortcode_formats('[format_codes_all_detailed]', true, $this->formats_all, $page_name, $data);
        }
        function shortcode_formats($shortcode, $detailed, $formats, $page, &$str)
        {
            $pos = strpos($str, $shortcode);
            if ($pos==false) {
                return;
            }
            $value = '';
            if ($detailed) {
                $value = $this->write_detailed_formats($formats, $page);
            } else {
                $value = $this->write_formats($formats, $page);
            }
            $str = substr($str, 0, $pos).$value.substr($str, $pos+strlen($shortcode));
        }
        function write_formats($formats, $page)
        {
            if ($formats == null) {
                return '';
            }
            $this->mpdf->WriteHTML('td{font-size: '.$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height'].';}', 1);
            $data = "<table style='width:100%;font-size:".$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height'].";'>";
            for ($count = 0; $count < count($formats); $count++) {
                $data .= '<tr>';
                $data .= "<td style='padding-left:4px;border:1px solid #555;border-right:0;width:12%;vertical-align:top;'>".$formats[$count]['key_string']."</td>";
                $data .= "<td style='border: 1px solid #555;border-left:0;width:38%;vertical-align:top;'>".$formats[$count]['name_string']."</td>";
                $count++;
                if ($count >= count($formats)) {
                    $data .= "<td style='padding-left:4px;border: 1px solid #555;border-right:0;width:12%;vertical-align:top;'></td>";
                    $data .= "<td style='border: 1px solid #555;border-left:0;width:38%;vertical-align:top;'></td>";
                } else {
                    $data .= "<td style='padding-left:4px;border: 1px solid #555;border-right:0;width:12%;vertical-align:top;'>".$formats[$count]['key_string']."</td>";
                    $data .= "<td style='border: 1px solid #555;border-left:0;width:38%;vertical-align:top;'>".$formats[$count]['name_string']."</td>";
                }
                $data .= "</tr>";
            }
            $data .= "</table>";
            return $data;
        }
        function asm_required($data)
        {
            return strpos($data, '[service_meetings]') || strpos($data, '[additional_meetings]');
        }
        function write_detailed_formats($formats, $page)
        {
            if ($formats == null) {
                return '';
            }
            $this->mpdf->WriteHTML('td{font-size: '.$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height'].';}', 1);
            $data = "<table style='width:100%;font-size:".$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height'].";'>";
            for ($count = 0; $count < count($formats); $count++) {
                if (isset($this->options[$page.'_font_size']) && isset($this->options[$page . '_line_height'])) {
                    $data .= "<tr><td style='border-bottom:1px solid #555;width:8%;vertical-align:top;'><span style='font-size:" . $this->options[$page . '_font_size'] . "pt;line-height:" . $this->options[$page . '_line_height'] . ";font-weight:bold;'>" . $formats[$count]['key_string'] . "</span></td>";
                    $data .= "<td style='border-bottom:1px solid #555;width:92%;vertical-align:top;'><span style='font-size:" . $this->options[$page . '_font_size'] . "pt;line-height:" . $this->options[$page . '_line_height'] . ";'>(" . $formats[$count]['name_string'] . ") " . $formats[$count]['description_string'] . "</span></td></tr>";
                }
            }
            $data .= "</table>";
            return $data;
        }
        private function parse_field($text)
        {
            if ($text!='') {
                $exploded = explode("#@-@#", $text);
                $knt = count($exploded);
                if ($knt > 1) {
                    $text = $exploded[$knt-1];
                }
            }
            return $text;
        }
        public function arraySafeGet($arr, $i = 0)
        {
            return is_array($arr) ? $arr[$i] ?? '': '';
        }
        function get_field($obj, $field)
        {
            $value = '';
            if (isset($obj[$field])) {
                $value = $this->parse_field($obj[$field]);
            }
            return $value;
        }
        function write_service_meetings($font_size, $line_height)
        {
            if ($this->service_meeting_result == null) {
                $sort_order = $this->options['asm_sort_order'];
                if ($sort_order=='same') {
                    $sort_order = 'weekday_tinyint,start_time';
                }
                $asm_id = "";
                if (isset($this->options['asm_format_id'])) {
                    $asm_id = '&formats[]='.$this->options['asm_format_id'];
                }
                $services = $this->services;
                if (!empty($this->options['asm_custom_query'])) {
                    $services = $this->options['asm_custom_query'];
                }
                $asm_query = "client_interface/json/?switcher=GetSearchResults$services$asm_id&sort_keys=$sort_order";
                // I'm not sure we need this, but for now we need to emulate the old behavior
                if ($this->options['asm_format_key']==='ASM') {
                    $asm_query .= "&advanced_published=0";
                }
                $results = $this->get_configured_root_server_request($asm_query);
                $this->service_meeting_result = json_decode(wp_remote_retrieve_body($results), true);
                if ($sort_order == 'weekday_tinyint,start_time') {
                    $this->adjust_timezone($this->service_meeting_result, $this->target_timezone);
                    $this->service_meeting_result = $this->orderByWeekdayStart($this->service_meeting_result);
                }
            }
            if ($this->options['asm_sort_order']=='same') {
                if (isset($this->options['asm_template_content']) && trim($this->options['asm_template_content'])) {
                    $template = $this->options['asm_template_content'];
                } else {
                    $template = $this->options['meeting_template_content'];
                }
                $this->writeMeetings($this->service_meeting_result, $template, $this->options['asm_language'], 1, false);
                return;
            }
            $temp = array();
            foreach ($this->service_meeting_result as $value) {
                $value = $this->enhance_meeting($value, $this->options['asm_language']);
                if ($this->asm_test($value, false)) {
                    $temp[] = $value;
                }
            }
            $this->service_meeting_result = $temp;
            if (empty($temp)) {
                return;
            }
            $data = '';
            $template = '';
            if (isset($this->options['asm_template_content']) && trim($this->options['asm_template_content'])) {
                $template = $this->options['asm_template_content'];
            } else {
                $data .= "<table style='line-height:".$line_height."; font-size:".$font_size."pt; width:100%;'>";
            }
            foreach ($this->service_meeting_result as $value) {
                $area_name = $this->get_area_name($value);
                if ($template != '') {
                    $template = str_replace("&nbsp;", " ", $template);
                    $data .= $this->write_single_meeting(
                        $value,
                        $template,
                        $this->analyseTemplate($template),
                        $area_name
                    );
                    continue;
                }
                $display_string = '<strong>'.$value['meeting_name'].'</strong>';
                if (!strstr($value['comments'], 'Open Position')) {
                    $display_string .= '<strong> - ' . $value['start_time'] . '</strong>';
                }

                if (trim($value['location_text'])) {
                    $display_string .= ' - '.trim($value['location_text']);
                }
                if (trim($value['location_street'])) {
                    $display_string .= ' - ' . trim($value['location_street']);
                }
                if (trim($value['location_city_subsection'])) {
                    $display_string .= ' ' . trim($value['location_city_subsection']);
                }
                if (trim($value['location_neighborhood'])) {
                    $display_string .= ' ' . trim($value['location_neighborhood']);
                }
                if (trim($value['location_municipality'])) {
                    $display_string .= ' '.trim($value['location_municipality']);
                }
                if (trim($value['location_province'])) {
                    //$display_string .= ' '.trim ( $value['location_province'] );
                }
                if (trim($value['location_postal_code_1'])) {
                    $display_string .= ' ' . trim($value['location_postal_code_1']);
                }
                if (trim($value['location_info'])) {
                    $display_string .= " (".trim($value['location_info']).")";
                }

                if (isset($value['email_contact']) && $value['email_contact'] != '' && $this->options['include_meeting_email'] == 1) {
                    $str = $this->parse_field($value['email_contact']);
                    $value['email_contact'] = $str;
                    $value['email_contact'] = ' (<i>'.$value['email_contact'].'</i>)';
                } else {
                    $value['email_contact'] = '';
                }
                $display_string .=  $value['email_contact'];
                $data .= "<tr><td style='border-bottom: 1px solid #555;'>".$display_string."</td></tr>";
            }
            if ($template == '') {
                $data .= "</table>";
            }
            $this->writeHTML($data);
        }

        /**
        * @desc Adds the options sub-panel
        */
        function admin_menu_link()
        {
            global $my_admin_page;
            Bread::add_cap();
            $my_admin_page = add_menu_page('Meeting List', 'Meeting List', 'manage_bread', basename(__FILE__), array(&$this, 'admin_options_page'), 'dashicons-admin-page');
        }

        function bmltrootserverurl_meta_box()
        {
            global $connect;
            ?>
            <label for="root_server">BMLT Server: </label>
            <input class="bmlt-input" id="root_server" type="text" size="80" name="root_server" value="<?php echo $this->options['root_server'] ;?>" /> <?php echo $connect; ?>
            <p><a target="_blank" href="https://bmlt.app/what-is-the-bmlt/hit-parade/#bmlt-server">BMLT Server Implementations</a></p>
            <?php
        }

        /**
        * Adds settings/options page
        */
        function admin_options_page()
        {
            $this->getMLOptions($this->requested_setting);
            $this->lang = $this->get_bmlt_server_lang();
            ?>
            <div class="connecting"></div>
            <div class="saving"></div>
            <div style="display:none;">
                <form method="POST" id="three_column_default_settings" name="three_column_default_settings" enctype="multipart/form-data">
                    <?php wp_nonce_field('pwsix_submit_three_column', 'pwsix_submit_three_column'); ?>
                    <input type="hidden" name="pwsix_action" value="three_column_default_settings" />
                    <input type="hidden" name="current-meeting-list" value="<?php echo $this->loaded_setting?>" />
                    <div id="basicModal1">
                        <p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
                        <p>Consider backing up your settings by using the Configuration Tab.</p>
                    </div>
                </form>
                <form method="POST" id="four_column_default_settings" name="four_column_default_settings" enctype="multipart/form-data">
                    <?php wp_nonce_field('pwsix_submit_four_column', 'pwsix_submit_four_column'); ?>
                    <input type="hidden" name="pwsix_action" value="four_column_default_settings" />
                    <input type="hidden" name="current-meeting-list" value="<?php echo $this->loaded_setting?>" />
                    <div id="basicModal2">
                        <p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
                        <p>Consider backing up your settings by using the Configuration Tab.</p>
                    </div>
                </form>
                <form method="POST" id="booklet_default_settings" name="booklet_default_settings" enctype="multipart/form-data">
                    <?php wp_nonce_field('pwsix_submit_booklet', 'pwsix_submit_booklet'); ?>
                    <input type="hidden" name="pwsix_action" value="booklet_default_settings" />
                    <input type="hidden" name="current-meeting-list" value="<?php echo $this->loaded_setting?>" />
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
                $this->options['bread_version'] = sanitize_text_field($_POST['bread_version']);
                $this->options['front_page_content'] = wp_kses_post($_POST['front_page_content']);
                $this->options['last_page_content'] = wp_kses_post($_POST['last_page_content']);
                $this->options['front_page_line_height'] = $_POST['front_page_line_height'];
                $this->options['front_page_font_size'] = floatval($_POST['front_page_font_size']);
                $this->options['last_page_font_size'] = floatval($_POST['last_page_font_size']);
                $this->options['last_page_line_height'] = floatval($_POST['last_page_line_height']);
                $this->options['content_font_size'] = floatval($_POST['content_font_size']);
                $this->options['suppress_heading'] = floatval($_POST['suppress_heading']);
                $this->options['header_font_size'] = floatval($_POST['header_font_size']);
                $this->options['header_text_color'] = validate_hex_color($_POST['header_text_color']);
                $this->options['header_background_color'] = validate_hex_color($_POST['header_background_color']);
                $this->options['header_uppercase'] = intval($_POST['header_uppercase']);
                $this->options['header_bold'] = intval($_POST['header_bold']);
                $this->options['sub_header_shown'] = sanitize_text_field($_POST['sub_header_shown']);
                $this->options['cont_header_shown'] = intval($_POST['cont_header_shown']);
                $this->options['column_gap'] = isset($_POST['column_gap']) ?
                            intval($_POST['column_gap']) : 5;
                $this->options['margin_right'] = intval($_POST['margin_right']);
                $this->options['margin_left'] = intval($_POST['margin_left']);
                $this->options['margin_bottom'] = intval($_POST['margin_bottom']);
                $this->options['margin_top'] = intval($_POST['margin_top']);
                $this->options['margin_header'] = intval($_POST['margin_header']);
                $this->options['margin_footer'] = isset($_POST['margin_footer']) ?
                    intval($_POST['margin_footer']): 5;
                $this->options['pageheader_fontsize'] = floatval($_POST['pageheader_fontsize']);
                $this->options['pageheader_textcolor'] = validate_hex_color($_POST['pageheader_textcolor']);
                $this->options['pageheader_backgroundcolor'] = validate_hex_color($_POST['pageheader_backgroundcolor']);
                $this->options['pageheader_content'] = wp_kses_post($_POST['pageheader_content']);
                $this->options['watermark'] = sanitize_text_field($_POST['watermark']);
                $this->options['page_size'] = sanitize_text_field($_POST['page_size']);
                $this->options['page_orientation'] = validate_page_orientation($_POST['page_orientation']);
                $this->options['page_fold'] = sanitize_text_field($_POST['page_fold']);
                $this->options['booklet_pages'] = isset($_POST['booklet_pages']) ?
                        boolval($_POST['booklet_pages']): false;
                $this->options['meeting_sort'] = sanitize_text_field($_POST['meeting_sort']);
                $this->options['main_grouping'] = sanitize_text_field($_POST['main_grouping']);
                $this->options['subgrouping'] = sanitize_text_field($_POST['subgrouping']);
                $this->options['borough_suffix'] = sanitize_text_field($_POST['borough_suffix']);
                $this->options['county_suffix'] = sanitize_text_field($_POST['county_suffix']);
                $this->options['neighborhood_suffix'] = sanitize_text_field($_POST['neighborhood_suffix']);
                $this->options['city_suffix'] = sanitize_text_field($_POST['city_suffix']);
                $this->options['meeting_template_content'] = wp_kses_post($_POST['meeting_template_content']);
                $this->options['asm_template_content'] = wp_kses_post($_POST['asm_template_content']);
                $this->options['column_line'] = isset($_POST['column_line']) ?
                            boolval($_POST['column_line']) : 0;
                $this->options['col_color'] = isset($_POST['col_color']) ?
                            validate_hex_color($_POST['col_color']) : '#bfbfbf';
                $this->options['custom_section_content'] = wp_kses_post($_POST['custom_section_content']);
                $this->options['custom_section_line_height'] = floatval($_POST['custom_section_line_height']);
                $this->options['custom_section_font_size'] = floatval($_POST['custom_section_font_size']);
                $this->options['pagenumbering_font_size'] = isset($_POST['pagenumbering_font_size']) ?
                                        floatval($_POST['pagenumbering_font_size']) : '9';
                $this->options['used_format_1'] = sanitize_text_field($_POST['used_format_1']);
                $this->options['include_meeting_email'] = isset($_POST['include_meeting_email']) ? boolval($_POST['include_meeting_email']) : false;
                $this->options['recurse_service_bodies'] = isset($_POST['recurse_service_bodies']) ? 1 : 0;
                $this->options['extra_meetings_enabled'] = isset($_POST['extra_meetings_enabled']) ? intval($_POST['extra_meetings_enabled']) : 0;
                $this->options['include_protection'] = boolval($_POST['include_protection']);
                $this->options['weekday_language'] = sanitize_text_field($_POST['weekday_language']);
                $this->options['asm_language'] = sanitize_text_field($_POST['asm_language']);
                $this->options['weekday_start'] = sanitize_text_field($_POST['weekday_start']);
                $this->options['meeting1_footer'] = isset($_POST['meeting1_footer']) ?
                        sanitize_text_field($_POST['meeting1_footer']) : '';
                $this->options['meeting2_footer'] = isset($_POST['meeting2_footer']) ?
                        sanitize_text_field($_POST['meeting2_footer']) :'';
                $this->options['nonmeeting_footer'] = isset($_POST['nonmeeting_footer']) ?
                        sanitize_text_field($_POST['nonmeeting_footer']):'';
                $this->options['include_asm'] = boolval($_POST['include_asm']);
                $this->options['asm_format_key'] = sanitize_text_field($_POST['asm_format_key']);
                $this->options['asm_sort_order'] = sanitize_text_field($_POST['asm_sort_order']);
                $this->options['bmlt_login_id'] = sanitize_text_field($_POST['bmlt_login_id']);
                $this->options['bmlt_login_password'] = sanitize_text_field($_POST['bmlt_login_password']);
                $this->options['base_font'] = sanitize_text_field($_POST['base_font']);
                $this->options['colorspace'] = sanitize_text_field($_POST['colorspace']);
                $this->options['wheelchair_size'] = sanitize_text_field($_POST['wheelchair_size']);
                $this->options['protection_password'] = sanitize_text_field($_POST['protection_password']);
                $this->options['time_clock'] = sanitize_text_field($_POST['time_clock']);
                $this->options['time_option'] = intval($_POST['time_option']);
                $this->options['remove_space'] = boolval($_POST['remove_space']);
                $this->options['content_line_height'] = floatval($_POST['content_line_height']);
                $this->options['root_server'] = validate_url($_POST['root_server']);
                $this->options['service_body_1'] = sanitize_text_field($_POST['service_body_1']);
                $this->options['service_body_2'] = sanitize_text_field($_POST['service_body_2']);
                $this->options['service_body_3'] = sanitize_text_field($_POST['service_body_3']);
                $this->options['service_body_4'] = sanitize_text_field($_POST['service_body_4']);
                $this->options['service_body_5'] = sanitize_text_field($_POST['service_body_5']);
                $this->options['cache_time'] = intval($_POST['cache_time']);
                $this->options['custom_query'] = sanitize_text_field($_POST['custom_query']);
                $this->options['asm_custom_query'] = sanitize_text_field($_POST['asm_custom_query']);
                $this->options['user_agent'] = isset($_POST['user_agent']) ? sanitize_text_field($_POST['user_agent']) : 'None';
                $this->options['sslverify'] = isset($_POST['sslverify']) ? '1' : '0';
                $this->options['extra_meetings'] = array();
                if (isset($_POST['extra_meetings'])) {
                    foreach ($_POST['extra_meetings'] as $extra) {
                        $this->options['extra_meetings'][] = wp_kses_post($extra);
                    }
                }
                $authors = $_POST['authors_select'];
                $this->options['authors'] = array();
                foreach ($authors as $author) {
                    $this->options['authors'][] = intval($author);
                }
                $user = wp_get_current_user();
                if (!in_array($user->ID, $this->options['authors'])) {
                    $this->options['authors'][] = $user->ID;
                }
                set_transient('admin_notice', 'Please put down your weapon. You have 20 seconds to comply.');
                if (!$this->current_user_can_modify()) {
                    echo '<div class="updated"><p style="color: #F00;">You do not have permission to save this configuation!</p>';
                } else {
                    $this->save_admin_options();
                    echo '<div class="updated"><p style="color: #F00;">Your changes were successfully saved!</p>';
                    $num = delete_transient($this->get_TransientKey());
                    if ($num > 0) {
                        echo "<p>$num Cache entries deleted</p>";
                    }
                }
                echo '</div>';
            } elseif (isset($_REQUEST['pwsix_action']) && $_REQUEST['pwsix_action'] == "import_settings") {
                echo '<div class="updated"><p style="color: #F00;">Your file was successfully imported!</p></div>';
                $num = delete_transient($this->get_TransientKey());
            } elseif (isset($_REQUEST['pwsix_action']) && $_REQUEST['pwsix_action'] == "default_settings_success") {
                echo '<div class="updated"><p style="color: #F00;">Your default settings were successfully updated!</p></div>';
                $num = delete_transient($this->get_TransientKey());
            }
            global $wpdb;
            $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid LIKE '%default_nalogo.jpg%'";
            if ($wpdb->get_var($query) == 0) {
                $url = plugin_dir_url(__FILE__) . "includes/default_nalogo.jpg";
                media_sideload_image($url, 0);
            }
            $this->fillUnsetOptions();

            $this->authors_safe = $this->options['authors'];
            ?>
            <?php include 'partials/_help_videos.php'; ?>
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
                    <input type="hidden" name="current-meeting-list" value="<?php echo $this->loaded_setting?>" />
                    <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
                    <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
                    <?php
                    wp_nonce_field('bmltmeetinglistupdate-options');
                    $serverInfo = $this->testRootServer();
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
                        <?php include 'partials/_meeting_list_setup.php'; ?>
                    </div>
                    <div id="tabs-first">
                        <?php include 'partials/_bmlt_server_setup.php'; ?>
                    </div>
                    <div id="layout">
                        <?php include 'partials/_layout_setup.php'; ?>
                    </div>
                    <div id="front-page">
                        <?php include 'partials/_front_page_setup.php'; ?>
                    </div>
                    <div id="meetings">
                        <?php include 'partials/_meetings_setup.php'; ?>
                    </div>
                    <div id="custom-section">
                        <?php include 'partials/_custom_section_setup.php'; ?>
                    </div>
                    <div id="last-page">
                        <?php include 'partials/_last_page_setup.php'; ?>
                    </div>
                    </form>
                    <div id="import-export">
                        <?php include 'partials/_backup_restore_setup.php'; ?>
                    </div>
                </div>
            </div>
            <div id="dialog" title="TinyMCE dialog" style="display: none">
                <textarea>test</textarea>
            </div>
            <?php
        }
        function getSingleLanguage($lang)
        {
            return substr($lang, 0, 2);
        }
        function toPersianNum($number)
        {
            $number = str_replace("1", "۱", $number);
            $number = str_replace("2", "۲", $number);
            $number = str_replace("3", "۳", $number);
            $number = str_replace("4", "۴", $number);
            $number = str_replace("5", "۵", $number);
            $number = str_replace("6", "۶", $number);
            $number = str_replace("7", "۷", $number);
            $number = str_replace("8", "۸", $number);
            $number = str_replace("9", "۹", $number);
            $number = str_replace("0", "۰", $number);
            return $number;
        }
        function fillUnsetOption($option, $default)
        {
            if (!isset($this->options[$option]) || strlen(trim($this->options[$option])) == 0) {
                $this->options[$option] = $default;
            }
        }
        function fillUnsetStringOption($option, $default)
        {
            if (!isset($this->options[$option])) {
                $this->options[$option] = $default;
            }
        }
        function fillUnsetArrayOption($option, $default)
        {
            if (!isset($this->options[$option])) {
                $this->options[$option] = $default;
            } else if (!is_array($this->options[$option])) {
                if (is_string($this->options[$option]) && strlen(trim($this->options[$option])) > 0) {
                    $this->options[$option] = [ trim($this->options[$option]) ];
                } else {
                    $this->options[$option] = $default;
                }
            }
        }
        function fillUnsetOptions()
        {
            $this->fillUnsetOption('front_page_line_height', '1.0');
            $this->fillUnsetOption('front_page_font_size', '10');
            $this->fillUnsetOption('last_page_font_size', '10');
            $this->fillUnsetOption('content_font_size', '9');
            $this->fillUnsetOption('header_font_size', $this->options['content_font_size']);
            $this->fillUnsetOption('pageheader_fontsize', $this->options['header_font_size']);
            if (floatval($this->options['pageheader_fontsize']) < 4) {
                $this->options['pageheader_fontsize'] = 6;
            }
            $this->fillUnsetOption('suppress_heading', 0);
            $this->fillUnsetOption('header_text_color', '#ffffff');
            $this->fillUnsetOption('header_background_color', '#000000');
            $this->fillUnsetOption('pageheader_textcolor', '#000000');
            $this->fillUnsetOption('pageheader_backgroundcolor', '#ffffff');
            $this->fillUnsetOption('header_uppercase', '0');
            $this->fillUnsetOption('header_bold', '1');
            $this->fillUnsetOption('sub_header_shown', 'none');
            $this->fillUnsetOption('margin_top', 3);
            $this->fillUnsetOption('margin_bottom', 3);
            $this->fillUnsetOption('margin_left', 3);
            $this->fillUnsetOption('margin_right', 3);
            $this->fillUnsetOption('column_gap', "5");
            $this->fillUnsetOption('content_line_height', '1.0');
            $this->fillUnsetOption('last_page_line_height', '1.0');
            $this->fillUnsetOption('page_size', 'legal');
            $this->fillUnsetOption('page_orientation', 'L');
            $this->fillUnsetOption('page_fold', 'quad');
            $this->fillUnsetOption('meeting_sort', 'day');
            $this->fillUnsetStringOption('booklet_pages', false);
            $this->fillUnsetStringOption('borough_suffix', 'Borough');
            $this->fillUnsetStringOption('county_suffix', 'County');
            $this->fillUnsetStringOption('neighborhood_suffix', 'Neighborhood');
            $this->fillUnsetStringOption('city_suffix', 'City');
            $this->fillUnsetStringOption('meeting_template_content', '');
            $this->fillUnsetStringOption('asm_template_content', '');
            $this->fillUnsetOption('column_line', 0);
            $this->fillUnsetOption('col_color', '#bfbfbf');
            $this->fillUnsetStringOption('custom_section_content', '');
            $this->fillUnsetOption('custom_section_line_height', '1');
            $this->fillUnsetOption('custom_section_font_size', '9');
            $this->fillUnsetOption('pagenumbering_font_size', '9');
            $this->fillUnsetStringOption('used_format_1', '');
            $this->fillUnsetOption('include_meeting_email', 0);
            $this->fillUnsetOption('base_font', 'dejavusanscondensed');
            $this->fillUnsetOption('colorspace', 0);
            $this->fillUnsetOption('recurse_service_bodies', 1);
            $this->fillUnsetOption('extra_meetings_enabled', 0);
            $this->fillUnsetOption('include_protection', 0);
            $this->fillUnsetOption('weekday_language', 'en');
            $this->fillUnsetStringOption('asm_language', '');  // same as main language
            $this->fillUnsetOption('weekday_start', '1');
            $this->fillUnsetOption('include_asm', '0');
            $this->fillUnsetOption('asm_format_key', '');
            $this->fillUnsetOption('asm_sort_order', 'name');
            $this->fillUnsetStringOption('bmlt_login_id', '');
            $this->fillUnsetStringOption('bmlt_login_password', '');
            $this->fillUnsetStringOption('protection_password', '');
            $this->fillUnsetStringOption('custom_query', '');
            $this->fillUnsetStringOption('asm_custom_query', '');
            $this->fillUnsetStringOption('user_agent', 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bread');
            $this->fillUnsetOption('sslverify', '0');
            $this->fillUnsetOption('cache_time', 0);
            $this->fillUnsetOption('wheelchair_size', "20px");
            $this->fillUnsetArrayOption('extra_meetings', []);
            if (!isset($this->options['extra_meetings'])) {
                if (count($this->options['extra_meetings'])>0) {
                    $this->options['extra_meetings_enabled'] = 1;
                } else {
                    $this->options['extra_meetings_enabled'] = 0;
                }
            }
            $this->fillUnsetArrayOption('authors', []);
            $my_footer = isset($this->translate[$this->options['weekday_language']]) ?
                $this->translate[$this->options['weekday_language']]['PAGE'].' {PAGENO}' : '{PAGENO}';
            $this->fillUnsetStringOption('nonmeeting_footer', $my_footer);
            $this->fillUnsetStringOption('meeting1_footer', $this->options['nonmeeting_footer']);
            $this->fillUnsetStringOption('meeting2_footer', $this->options['nonmeeting_footer']);
        }
        function get_TransientPrefix()
        {
            return '_bread';
        }
        function get_TransientKey()
        {
            return $this->get_TransientPrefix().'__'.$this->loaded_setting;
        }
        function pwsix_process_settings_admin()
        {
            $this->getMLOptions($this->requested_setting);
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
                unset($this->allSettings[$this->loaded_setting]);
                update_option(Bread::SETTINGS, $this->allSettings);
                $this->getMLOptions(1);
                $this->loaded_setting = 1;
                $this->requested_setting = 1;
            } elseif (isset($_POST['duplicate'])) {
                if (!$this->current_user_can_create()) {
                    return;
                }
                $id = $this->maxSetting + 1;
                $this->optionsName = $this->generateOptionName($id);
                $this->authors_safe = array();
                $this->options['authors'] = array();
                $this->save_admin_options();
                $this->allSettings[$id] = 'Setting '.$id;
                update_option(Bread::SETTINGS, $this->allSettings);
                $this->maxSetting = $id;
                $this->getMLOptions($id);
            }
        }
        function pwsix_process_rename_settings()
        {
            $this->getMLOptions($this->requested_setting);
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

            $this->allSettings[$this->loaded_setting] = sanitize_text_field($_POST['setting_descr']);
            update_option(Bread::SETTINGS, $this->allSettings);
        }
        /**
         * Process a settings export that generates a .json file of the shop settings
         */
        function pwsix_process_settings_export()
        {
            $this->getMLOptions($this->requested_setting);
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

            $blogname = str_replace(" - ", " ", get_option('blogname').'-'.$this->allSettings[$this->loaded_setting]);
            $blogname = str_replace(" ", "-", $blogname);
            $date = date("m-d-Y");
            $blogname = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($blogname)), '-');
            $json_name = $blogname.$date.".json"; // Naming the filename will be generated.
            $settings = get_option($this->optionsName);
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
            if (!is_array($this->authors_safe) || empty($this->authors_safe)) {
                return true;
            }
            if (in_array($user->ID, $this->authors_safe)) {
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
            $this->getMLOptions($this->requested_setting);
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
            $settings['authors'] = $this->authors_safe;
            update_option($this->optionsName, $settings);
            setcookie('pwsix_action', "import_settings", time()+10);
            setcookie('current-meeting-list', $this->loaded_setting, time()+10);
            wp_safe_redirect(admin_url('?page=bmlt-meeting-list.php'));
        }

        /**
         * Process a default settings
         */
        function pwsix_process_default_settings()
        {
            $this->getMLOptions($this->requested_setting);
            if (! current_user_can('manage_bread') ||
                (isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes' )) {
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
            $settings['authors'] = $this->authors_safe;
            update_option($this->optionsName, $settings);
            setcookie('pwsix_action', "default_settings_success", time()+10);
            setcookie('current-meeting-list', $this->loaded_setting, time()+10);
            wp_safe_redirect(admin_url('?page=bmlt-meeting-list.php'));
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

        /**
        * Retrieves the plugin options from the database.
        * @return array
        */
        function getMLOptions($current_setting)
        {
            if ($current_setting < 1 and is_admin()) {
                $current_setting = 1;
            }
            if ($current_setting != 1) {
                $this->optionsName = $this->generateOptionName($current_setting);
            } else {
                $this->optionsName = Bread::OPTIONS_NAME;
            }
            //Don't forget to set up the default options
            if (!$theOptions = get_option($this->optionsName)) {
                if ($current_setting != 1) {
                    unset($this->allSettings[$current_setting]);
                    update_option(Bread::SETTINGS, $this->allSettings);
                    die('Undefined setting: '. $current_setting);
                }
                $import_file = plugin_dir_path(__FILE__) . "includes/three_column_settings.json";
                $encode_options = file_get_contents($import_file);
                $theOptions = json_decode($encode_options, true);
                update_option($this->optionsName, $theOptions);
            }
            $this->options = $theOptions;
            $this->fillUnsetOptions();
            $this->upgrade_settings();
            $this->authors_safe = isset($theOptions['authors']) ? $theOptions['authors'] : array();
            $this->loaded_setting = $current_setting;
        }

        private function generateOptionName($current_setting)
        {
            return Bread::OPTIONS_NAME . '_' . $current_setting;
        }
        private function addServiceBody($service_body_name)
        {
            if (false === ( $this->options[$service_body_name] == 'Not Used' )) {
                $area_data = explode(',', $this->options[$service_body_name]);
                $area = $this->arraySafeGet($area_data);
                $this->options[$service_body_name] = ($area == 'NOT USED' ? '' : $area);
                $service_body_id = $this->arraySafeGet($area_data, 1);
                if ($this->options['recurse_service_bodies'] == 1) {
                    return '&recursive=1&services[]=' . $service_body_id;
                } else {
                    return '&services[]='.$service_body_id;
                }
            }
        }
        /**
        * Saves the admin options to the database.
        */
        function save_admin_options()
        {
            update_option($this->optionsName, $this->options);
            return;
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
    } //End Class bread
} // end if
//instantiate the class
if (class_exists("Bread")) {
    $BMLTMeetinglist_instance = new Bread();
}
