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
    private string $slug;
    /**
     * Initialize the class and set its properties.
     *
     * @since 2.8.0
     * @param string    $plugin_name       The name of this plugin.
     * @param string    $version    The version of this plugin.
     */
    public function __construct($slug)
    {
        $this->slug = $slug;
    }
    public function getSlug()
    {
        return $this->slug;
    }
    public function createMenu()
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
        // The prefix "BmltEnabled" is correct: it is unique enough to avoid conflicts and the filter is shared
        // with other plugins from this author
		// phpcs:ignore
        $icon = apply_filters("BmltEnabled_IconSVG", 'dashicons-location-alt');
        add_menu_page(
            'Meeting Lists',
            'Meeting Lists',
            $cap,
            $this->slug,
            '',
            $icon,
            null
        );
        // The prefix "BmltEnabled" is correct: it is unique enough to avoid conflicts and the filter is shared
        // with other plugins from this author
		// phpcs:ignore
        do_action('BmltEnabled_Submenu', $this->slug);
    }
}
