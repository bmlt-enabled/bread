<?php

/**
 * Creates the main item in the admin menu, where both bread and crouton admins can live.
 *
 * @package    Bread
 * @subpackage Bread/admin
 * @author     bmlt-enabled <help@bmlt.app>
 */
class BmltEnabled_Admin
{
    private bool $menu_created = false;
    /**
     * Initialize the class and set its properties.
     *
     * @since 2.8.0
     * @param string    $plugin_name       The name of this plugin.
     * @param string    $version    The version of this plugin.
     */
    public function __construct()
    {
    }
    public function createdMenu()
    {
        $this->menu_created = true;
    }
    function admin_menu_link()
    {
        if ($this->menu_created) {
            return;
        }
        $cap = 'manage_options';
        if (!current_user_can($cap)) {
            $cap = 'manage_bread';
        }
        $slugs = apply_filters('BmltEnabled_Slugs', []);
        $icon = apply_filters("BmltEnabled_IconSVG", 'dashicons-location-alt');
        $slug = $slugs[0];
        add_menu_page(
            'Meeting Lists',
            'Meeting Lists',
            $cap,
            $slug,
            '',
            $icon,
            null
        );
        do_action('BmltEnabled_Submenu', $slug);
    }
}
