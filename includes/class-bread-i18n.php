<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link  https://bmlt.app
 * @since 2.8.0
 *
 * @package    Bread
 * @subpackage Bread/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      2.8.0
 * @package    Bread
 * @subpackage Bread/includes
 * @author     bmlt-enabled <help@bmlt.app>
 */
class Bread_i18n
{


    /**
     * Load the plugin text domain for translation.
     *
     * @since 2.8.0
     */
    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'bread-domain',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
