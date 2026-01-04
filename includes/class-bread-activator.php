<?php
if (! defined('ABSPATH')) {
    exit;
}
/**
 * Fired during plugin activation
 *
 * @link  https://bmlt.app
 * @since 2.8.0
 *
 * @package    Bread
 * @subpackage Bread/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.8.0
 * @package    Bread
 * @subpackage Bread/includes
 * @author     bmlt-enabled <help@bmlt.app>
 */
class Bread_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since 2.8.0
     */
    public static function activate()
    {
        $role = $GLOBALS['wp_roles']->role_objects['administrator'];
        if (isset($role) && !$role->has_cap('manage_bread')) {
            $role->add_cap('manage_bread');
        }
    }
}
