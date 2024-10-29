<?php

/**
 * Fired during plugin deactivation
 *
 * @link  https://bmlt.app
 * @since 2.8.0
 *
 * @package    Bread
 * @subpackage Bread/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      2.8.0
 * @package    Bread
 * @subpackage Bread/includes
 * @author     bmlt-enabled <help@bmlt.app>
 */
class Bread_Deactivator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since 2.8.0
     */
    public static function deactivate()
    {
        $role = $GLOBALS['wp_roles']->role_objects['administrator'];
        if (isset($role) && $role->has_cap('manage_bread')) {
            $role->remove_cap('manage_bread');
        }
    }
}
