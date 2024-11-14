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
        $slug = $slugs[0];
        add_menu_page(
            'Meeting List',
            'Meeting List',
            $cap,
            $slug,
            '',
            //'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCiB3aWR0aD0iNTAuMDAwMDAwcHQiIGhlaWdodD0iNTAuMDAwMDAwcHQiIHZpZXdCb3g9IjAgMCA1MC4wMDAwMDAgNTAuMDAwMDAwIg0KIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiPg0KDQo8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLjAwMDAwMCw1MC4wMDAwMDApIHNjYWxlKDAuMTAwMDAwLC0wLjEwMDAwMCkiDQpmaWxsPSIjMDAwMDAwIiBzdHJva2U9Im5vbmUiPg0KPHBhdGggZD0iTTI4MCAzNjEgYzAgLTc0IC0zIC05MSAtMTUgLTkxIC0xMiAwIC0xNSAxNSAtMTUgNzEgMCA2NyAtMSA3MCAtMjINCjY3IC0yMSAtMyAtMjMgLTEwIC0yOCAtNzIgbC01IC02OSAtMzQgNzEgYy0zNCA3MCAtMzUgNzEgLTU0IDU0IC01NSAtNDggLTcyDQotMTU1IC0zNiAtMjI5IDE4IC0zNiAyNSAtNDMgNTEgLTQzIGwyOSAwIC0yMSAzMCBjLTMzIDQ3IC00MyA5MyAtMjkgMTQyIGwxMQ0KNDMgNjkgLTEzNSA2OCAtMTM1IDAgODMgYzEgNjIgNCA4MyAxNCA4MCA3IC0zIDEzIC0yNiAxNSAtNTYgMyAtNDQgNiAtNTIgMjMNCi01MiAxNiAwIDE5IDcgMTkgNTUgbDAgNTUgNDAgMCBjNDkgMCA1MiAtMTcgMTUgLTczIGwtMjYgLTM3IDMxIDAgYzI3IDAgMzQgNg0KNTAgNDMgMjcgNTggMjUgMTI0IC0zIDE3OCAtMjMgNDMgLTgwIDkxIC0xMjQgMTA0IC0yMyA2IC0yMyA1IC0yMyAtODR6IG04OQ0KLTE2IGMxNyAtMjAgMzEgLTQ1IDMxIC01NSAwIC0xNyAtNyAtMjAgLTQwIC0yMCBsLTQwIDAgMCA1NSBjMCAzMCA0IDU1IDkgNTUNCjUgMCAyMyAtMTYgNDAgLTM1eiIvPg0KPHBhdGggZD0iTTY5IDQyMyBjLTEzIC0xNiAtMTIgLTE3IDQgLTQgOSA3IDE3IDE1IDE3IDE3IDAgOCAtOCAzIC0yMSAtMTN6Ii8+DQo8cGF0aCBkPSJNNDEwIDQzNiBjMCAtMiA4IC0xMCAxOCAtMTcgMTUgLTEzIDE2IC0xMiAzIDQgLTEzIDE2IC0yMSAyMSAtMjEgMTN6Ii8+DQo8cGF0aCBkPSJNNDE5IDczIGMtMTMgLTE2IC0xMiAtMTcgNCAtNCAxNiAxMyAyMSAyMSAxMyAyMSAtMiAwIC0xMCAtOCAtMTcNCi0xN3oiLz4NCjxwYXRoIGQ9Ik00NTUgNTAgYy00IC02IC0zIC0xNiAzIC0yMiA2IC02IDEyIC02IDE3IDIgNCA2IDMgMTYgLTMgMjIgLTYgNg0KLTEyIDYgLTE3IC0yeiIvPg0KPC9nPg0KPC9zdmc+',
            'dashicons-location-alt',
            null
        );
        do_action('BmltEnabled_Submenu', $slug);
    }
}
