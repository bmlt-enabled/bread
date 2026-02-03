<?php

/**
 * The plugin bootstrap file
 *
 * @link    https://bmlt.app
 * @since   2.8.0
 * @package Bread
 *
 * @wordpress-plugin
 * Plugin Name:       Bread
 * Plugin URI:        https://bmlt.app
 * Description:       Maintains and generates PDF Meeting Lists from BMLT.
 * Version:           2.9.9
 * Author:            bmlt-enabled
 * Author URI:        https://bmlt.app/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bread
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 2.8.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('BREAD_VERSION', '2.9.7');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bread-activator.php
 */
function Bread_activate()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-bread-activator.php';
    Bread_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bread-deactivator.php
 */
function Bread_deactivate()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-bread-deactivator.php';
    Bread_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'Bread_activate');
register_deactivation_hook(__FILE__, 'Bread_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-bread.php';
require plugin_dir_path(__FILE__) . 'includes/class-bread-bmlt.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 2.8.0
 */
function Bread_run()
{

    $plugin = new Bread();
    $plugin->run();
}
Bread_run();
