<?php

/**
 * The core plugin class.
 *
 * Defines internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * This class manages the Wordpress options for the setting.  The process is a little more complex than usual, because
 * Bread stores 2 levels of options.  The bottom layer is the configuration of an individual meeting list.  But because
 * Bread can manage the options for multiple meeting lists, the main layer is a list of "settings", which name and point to
 * the lower level.  The WP options mechanism is used for both levels.
 *
 * The admin side edits the options, the public sides uses them, so this is a central class that will be passed around to
 * the classes that do the actual work.
 *
 * @since      2.8.0
 * @package    Bread
 * @subpackage Bread/includes
 * @author     bmlt-enabled <help@bmlt.app>
 */
class Bread
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since  2.8.0
     * @access protected
     * @var    Bread_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since  2.8.0
     * @access protected
     * @var    string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since  2.8.0
     * @access protected
     * @var    string    $version    The current version of the plugin.
     */
    protected $version;
    public const SETTINGS = 'bmlt_meeting_list_settings';
    public const OPTIONS_NAME = 'bmlt_meeting_list_options';
    private $optionsName;
    private $allSettings = array();
    private $maxSetting = 1;
    /**
     * The setting we are editing, generating or otherwise working with.  Generally set with query string "?current-meeting-list".
     *
     * @var integer
     */
    private int $requested_setting = 1;
    private $protocol;
    private string $tmp_dir;
    /**
     * The settings that detemine how the meeting list should be generated.
     *
     * @var array
     */
    private array $options = array();
    /**
     * Translations for things like weekday names.  Two dimensions, first is language code, second is key value for text.
     *
     * @var array
     */
    private array $translate = array();
    private bool $generating_meeting_list = false;
    private bool $exporting_meeting_list = false;
    /**
     * The wizard wants to know if we are generating the first meeting list for this site.
     *
     * @var boolean
     */
    private bool $initial_setting = false;
    /**
     * The helper class for accessing the BMLT root server.
     *
     * @var Bread_Bmlt
     */
    private Bread_Bmlt $bread_bmlt;
    public function bmlt(): Bread_Bmlt
    {
        return $this->bread_bmlt;
    }
    public function temp_dir(): string
    {
        return $this->tmp_dir;
    }
    public function getOption($name): mixed
    {
        if (!isset($this->options[$name])) {
            return '';
        }
        return $this->options[$name];
    }
    public function emptyOption($name): bool
    {
        return empty($this->options[$name]);
    }
    public function getOptionForDisplay($option, $default = '')
    {
        return empty($this->options[$option]) ? $default : esc_html($this->options[$option]);
    }
    public function setOption($name, $value)
    {
        return $this->options[$name] = $value;
    }
    public function appendOption($name, $value)
    {
        return $this->options[$name][] = $value;
    }
    /**
     * mPDF needs to have a temporary directory available.  This is a problem for some hosting providers, so we need some
     * logic to make sure it is available, and also to clean up after ourselves, to save disk space.
     *
     * @return string
     */
    private static function setup_temp_dir(): string
    {
        $dir = get_temp_dir();
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }
        Bread::brute_force_cleanup($dir);
        $attempts = 0;
        $path = '';
        do {
            $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, 'bread', mt_rand(100000, mt_getrandmax()));
        } while (!mkdir($path) && $attempts++ < 100);
        return $path;
    }
    private static function brute_force_cleanup($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (str_starts_with($object, "bread")) {
                        $filename = $dir . DIRECTORY_SEPARATOR . $object;
                        if (time() - filemtime($filename . (is_dir($filename) ? '/.' : '')) > 24 * 3600) {
                            Bread::rrmdir($filename);
                        }
                    }
                }
            }
        }
    }
    public function removeTempDir()
    {
        Bread::rrmdir($this->temp_dir());
    }
    private static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object)) {
                        Bread::rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        @unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            @rmdir($dir);
        }
    }
    private function loadAllSettings($holder): int
    {
        if (isset($holder['bread_preview_settings'])) {
            $this->allSettings = array();
            $this->allSettings[1] = $holder['bread_preview_settings'];
        } else {
            $this->allSettings = get_option(Bread::SETTINGS);
        }
        if ($this->allSettings === false) {
            $this->allSettings = array();
            $this->allSettings[1] = array();
            $this->maxSetting = 1;
        } else {
            foreach ($this->allSettings as $key => $value) {
                if ($key > $this->maxSetting) {
                    $this->maxSetting = $key;
                }
            }
        }
        return isset($holder['current-meeting-list']) ? intval($holder['current-meeting-list']) : 1;
    }
    /**
     * Undocumented function
     *
     * @param [type] $id
     * @param [type] $name
     * @return void
     */
    public function setAndSaveSetting($id, $name): void
    {
        $this->allSettings[$id] = $name;
        update_option(Bread::SETTINGS, $this->allSettings);
    }
    public function getSettingName($id)
    {
        return $this->allSettings[$id];
    }
    public function getSettingNames()
    {
        return $this->allSettings;
    }
    public function deleteSetting($id)
    {
        unset($this->allSettings[$id]);
        update_option(Bread::SETTINGS, $this->allSettings);
    }
    /**
     * Example the superglobals $_REQUEST, $_SESSION and $_COOKIE and try to determine the settings-id, putting the
     * results in a container.
     *
     * @return array
     */
    private function getCurrentMeetingListHolder(): array
    {
        $ret = array();
        if (isset(($_REQUEST['preview-meeting-list'])) && !is_admin()) {
            session_start();
            $ret['bread_preview_settings'] = $_SESSION['bread_preview_settings'];
            $ret['current-meeting-list'] = 1;
            // TODO check that the value is equal to some security option we've set, otherwise reject.
        }
        if (isset($_REQUEST['current-meeting-list'])) {
            $ret['current-meeting-list'] = $_REQUEST['current-meeting-list'];
        } elseif (isset($_REQUEST['export-meeting-list'])) {
            $ret['current-meeting-list'] = $_REQUEST['export-meeting-list'];
            $this->exporting_meeting_list = true;
        } elseif (isset($_COOKIE['current-meeting-list'])) {
            $ret['current-meeting-list'] = $_COOKIE['current-meeting-list'];
        }
        $this->generating_meeting_list = !empty($ret) && !is_admin() && !$this->exporting_meeting_list;
        return $ret;
    }
    /**
     * Undocumented function
     *
     * @return void
     */
    public function generatingMeetingList(): bool
    {
        return $this->generating_meeting_list;
    }
    public function exportingMeetingList(): bool
    {
        return $this->exporting_meeting_list;
    }
    public function generateOptionName($current_setting)
    {
        return Bread::OPTIONS_NAME . '_' . $current_setting;
    }
    /**
     * Retrieves the plugin options from the database.
     *
     * @return array
     */
    public function &getConfigurationForSettingId($current_setting)
    {
        if ($current_setting < 1) {
            $current_setting = is_admin() ? 1 : $this->requested_setting;
        }
        if (is_array($this->allSettings[$current_setting])) {
            $this->options = $this->allSettings[$current_setting];
        } else {
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
                    die('Undefined setting: ' . $current_setting);
                }
                //TODO fix default here, or force user to wizard
                $this->initial_setting = true;
                $import_file = plugin_dir_path(__FILE__) . "includes/three_column_settings.json";
                $encode_options = file_get_contents($import_file);
                $theOptions = json_decode($encode_options, true);
                update_option($this->optionsName, $theOptions);
            }
            $this->options = $theOptions;
            $this->fillUnsetOptions();
            $this->upgrade_settings();
        }

        $this->requested_setting = $current_setting;
        return $this->options;
    }
    public function getOptions()
    {
        return $this->options;
    }
    public function getOptionsName()
    {
        return $this->optionsName;
    }
    public function setOptionsName($name)
    {
        return $this->optionsName = $name;
    }
    public function getRequestedSetting()
    {
        return $this->requested_setting;
    }
    public function setRequestedSetting($id)
    {
        $this->requested_setting = $id;
    }
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 2.8.0
     */
    public function __construct()
    {
        if (defined('BREAD_VERSION')) {
            $this->version = BREAD_VERSION;
        } else {
            $this->version = '2.8.0';
        }
        $this->plugin_name = 'bread';
        $this->tmp_dir = $this->setup_temp_dir();
        $this->protocol = (strpos(strtolower(home_url()), "https") !== false ? "https" : "http") . "://";

        $holder = $this->getCurrentMeetingListHolder();
        $this->requested_setting = $this->loadAllSettings($holder);
        $this->bread_bmlt = new Bread_Bmlt($this);

        $this->load_dependencies();
        $this->set_locale();
        $this->load_translations();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    function load_translations()
    {
        $files = scandir(dirname(__FILE__) . "/lang");
        foreach ($files as $file) {
            if (strpos($file, "translate_") !== 0) {
                continue;
            }
            include dirname(__FILE__) . "/lang/" . $file;
            $key = substr($file, 10, -4);
            $this->translate[$key] = $translate;
        }
    }
    public function getTranslateTable()
    {
        return $this->translate;
    }
    public function getProtocol()
    {
        return $this->protocol;
    }
    /**
     * Did any settings exist before?
     *
     * @return bool true when no setting existed previously, ie, if we should create setting 1.
     */
    public function getInitialSetting()
    {
        return $this->initial_setting;
    }
    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Bread_Loader. Orchestrates the hooks of the plugin.
     * - Bread_i18n. Defines internationalization functionality.
     * - Bread_Admin. Defines all hooks for the admin area.
     * - Bread_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since  2.8.0
     * @access private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-bread-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-bread-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-bread-admin.php';

        /**
         * The class that creates the main menu item in the admin area, shared between bread and crouton.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-bmltenabled-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'public/class-bread-public.php';

        $this->loader = new Bread_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Bread_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since  2.8.0
     * @access private
     */
    private function set_locale()
    {

        $plugin_i18n = new Bread_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since  2.8.0
     * @access private
     */
    private function define_admin_hooks()
    {
        $bmltenabled_admin = new BmltEnabled_Admin();
        $plugin_admin = new Bread_Admin($this->get_plugin_name(), $this->get_version(), $bmltenabled_admin, $this);

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action("admin_menu", $bmltenabled_admin, "admin_menu_link");
        $this->loader->add_action("BmltEnabled_Submenu", $plugin_admin, "admin_submenu_link");
        $this->loader->add_filter("BmltEnabled_Slugs", $plugin_admin, "submenu_slug");
        $this->loader->add_filter('tiny_mce_before_init', $plugin_admin, 'tiny_tweaks');
        $this->loader->add_filter('mce_external_plugins', $plugin_admin, 'my_custom_plugins');
        $this->loader->add_filter('mce_buttons', $plugin_admin, 'my_register_mce_button');
        //add_action("admin_notices", $plugin_admin, "is_root_server_missing");
        $this->loader->add_action("admin_init", $plugin_admin, "my_theme_add_editor_styles");
        $this->loader->add_action("wp_default_editor", $plugin_admin, "ml_default_editor");
        $this->loader->add_filter('tiny_mce_version', $plugin_admin, 'force_mce_refresh');

        // This is a public function.  nameetinglists.org wants to let people see each others settings.
        // It's OK now, since there is no more login stuff.
        $this->loader->add_action('plugins_loaded', $plugin_admin, 'download_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since  2.8.0
     * @access private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Bread_Public($this->get_plugin_name(), $this->get_version(), $this);

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('plugins_loaded', $plugin_public, 'bmlt_meeting_list');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 2.8.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since  2.8.0
     * @return string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since  2.8.0
     * @return Bread_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since  2.8.0
     * @return string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
    /**
     * Return a blank if the key is unset
     *
     * @param array $arr
     * @param string $key
     * @return string
     */
    public static function arraySafeGet(array $arr, string $key = "0"): string
    {
        return $arr[$key] ?? '';
    }
    public function getday($day, $abbreviate = false, $language = 'en')
    {
        $key = "WEEKDAYS";
        if ($abbreviate) {
            $key = "WKDYS";
        }
        return $this->translate[$language][$key][$day];
    }
    private function fillUnsetOption($option, $default)
    {
        if (!isset($this->options[$option]) || strlen(trim($this->options[$option])) == 0) {
            $this->options[$option] = $default;
        }
    }
    private function fillUnsetStringOption($option, $default)
    {
        if (!isset($this->options[$option])) {
            $this->options[$option] = $default;
        }
    }
    private function fillUnsetArrayOption($option, $default)
    {
        if (!isset($this->options[$option])) {
            $this->options[$option] = $default;
        } else if (!is_array($this->options[$option])) {
            if (is_string($this->options[$option]) && strlen(trim($this->options[$option])) > 0) {
                $this->options[$option] = [trim($this->options[$option])];
            } else {
                $this->options[$option] = $default;
            }
        }
    }
    /**
     * Make sure the data from frontend or from DB is usuable.  This might involve upgrading data from earlier versions of Bread.
     *
     * @return void
     */
    public function fillUnsetOptions()
    {
        $this->fillUnsetOptionsInner();
        $this->removeDeprecated();
    }
    private function removeDeprecated()
    {
        unset($this->options['include_meeting_email']);
        unset($this->options['bmlt_login_id']);
        unset($this->options['bmlt_login_password']);
    }
    private function fillUnsetOptionsInner()
    {
        $this->fillUnsetOption('front_page_line_height', '1.0');
        $this->fillUnsetOption('front_page_font_size', '10');
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
        $this->fillUnsetStringOption('additional_list_template_content', '');
        $this->fillUnsetOption('column_line', 0);
        $this->fillUnsetOption('col_color', '#bfbfbf');
        $this->fillUnsetStringOption('custom_section_content', '');
        $this->fillUnsetOption('custom_section_line_height', '1');
        $this->fillUnsetOption('custom_section_font_size', '9');
        $this->fillUnsetOption('pagenumbering_font_size', '9');
        $this->fillUnsetStringOption('used_format_1', '');
        $this->fillUnsetOption('base_font', 'dejavusanscondensed');
        $this->fillUnsetOption('colorspace', 0);
        $this->fillUnsetOption('recurse_service_bodies', 1);
        $this->fillUnsetOption('extra_meetings_enabled', 0);
        $this->fillUnsetOption('include_protection', 0);
        $this->fillUnsetOption('weekday_language', 'en');
        $this->fillUnsetStringOption('additional_list_language', '');  // same as main language
        $this->fillUnsetOption('weekday_start', '1');
        $this->fillUnsetOption('include_additional_list', '0');
        $this->fillUnsetOption('additional_list_format_key', '');
        $this->fillUnsetOption('additional_list_sort_order', 'name');
        $this->fillUnsetStringOption('protection_password', '');
        $this->fillUnsetStringOption('custom_query', '');
        $this->fillUnsetStringOption('additional_list_custom_query', '');
        $this->fillUnsetStringOption('user_agent', 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bread');
        $this->fillUnsetOption('sslverify', '0');
        $this->fillUnsetOption('cache_time', 0);
        $this->fillUnsetOption('wheelchair_size', "20px");
        $this->fillUnsetArrayOption('extra_meetings', []);
        if (!isset($this->options['extra_meetings'])) {
            if (count($this->options['extra_meetings']) > 0) {
                $this->options['extra_meetings_enabled'] = 1;
            } else {
                $this->options['extra_meetings_enabled'] = 0;
            }
        }
        $this->fillUnsetArrayOption('authors', []);
        $my_footer = isset($this->translate[$this->options['weekday_language']]) ?
            $this->translate[$this->options['weekday_language']]['PAGE'] . ' {PAGENO}' : '{PAGENO}';
        $this->fillUnsetStringOption('nonmeeting_footer', $my_footer);
        $this->fillUnsetStringOption('meeting1_footer', $this->options['nonmeeting_footer']);
        $this->fillUnsetStringOption('meeting2_footer', $this->options['nonmeeting_footer']);
    }
    /**
     * Does the work of upgrading from earlier versions.
    */
    private function upgrade_settings(): void
    {
        if (!isset($this->options['bread_version'])) {
            if (!($this->options['meeting_sort'] === 'weekday_area'
                || $this->options['meeting_sort'] === 'weekday_city'
                || $this->options['meeting_sort'] === 'weekday_county'
                || $this->options['meeting_sort'] === 'day')) {
                $this->options['weekday_language'] = $this->bmlt()->get_bmlt_server_lang();
            }
            if ($this->options['page_fold'] == 'half') {
                if ($this->options['page_size'] == 'A5') {
                    $this->options['page_size'] = 'A4';
                }
                $this->options['page_orientation'] = 'L';
            }
            if ($this->options['page_fold'] == 'tri') {
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
                $this->options['root_server'] = substr($this->options['root_server'], 0, strlen($this->options['root_server']) - 1);
            }
            if (substr($this->options['root_server'], 0, 4) !== 'http') {
                $this->options['root_server'] = 'http://' . $this->options['root_server'];
            }
        }
        if (!isset($this->options['cont_header_shown'])
            && isset($this->options['page_height_fix'])
        ) {
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
        $this->renamed_option('asm_format_key', 'additional_list_format_key');
        $this->renamed_option('asm_sort_order', 'additional_list_sort_order');
        $this->renamed_option('asm_language', 'additional_list_language');
        $this->renamed_option('asm_custom_query', 'additional_list_custom_query');
        $this->renamed_option('asm_template_content', 'additional_list_template_content');
        if (!isset($this->options['bread_version']) || $this->options['bread_version'] < '2.8') {
            if (($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') && trim($this->options['last_page_content']) !== '') {
                $this->options['custom_section_content'] = $this->options['last_page_content'];
                $this->options['custom_section_font_size'] = $this->options['last_page_font_size'];
                $this->options['custom_section_line_height'] = $this->options['last_page_line_height'];
                unset($this->options['last_page_content']);
                unset($this->options['last_page_line_height']);
                unset($this->options['last_page_font_size']);
            }
        }
        $this->options['bread_version'] = BREAD_VERSION;
    }
    private function renamed_option(string $old, string $new)
    {
        if (!empty($this->options[$old])) {
            if (empty($this->options[$new])) {
                $this->options[$new] = $this->options[$old];
                unset($this->options[$old]);
            }
        }
    }
    /**
     * Stores the current settings in the Wordpress Options DB.
     *
     * @return void
     */
    public function updateOptions()
    {
        update_option(Bread::getOptionsName(), $this->options);
    }
    public static function get_TransientKey($setting): string
    {
        return '_bread__' . $setting;
    }
    public function getMaxSetting(): int
    {
        return $this->maxSetting;
    }
}
