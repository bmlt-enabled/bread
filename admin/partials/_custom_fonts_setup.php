<?php
if (! defined('ABSPATH')) {
    exit;
}
if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class Bread_Custom_Fonts_Table extends WP_List_Table
{
    private Bread $bread;
    private array $fonts;
    private array $active;
    private string $nonce;
    function getTypeFromStack(string $stack): string
    {
        $array = explode(',', $stack);
        return $array[count($array)-1];
    }
    function __construct(Bread $bread)
    {
        $this->bread = $bread;
        $this->fonts = $bread->getAvailableFonts();
        $this->active = $bread->getActiveFonts();
        $this->nonce = wp_create_nonce("bread_font_action");
        return parent::__construct();
    }
    function get_columns()
    {
        return [
            'name' => 'Font Family',
            'type' => 'Type',
            'scripts' => 'Character Sets',
            'specimen' => 'More Information',
        ];
    }
    function column_default($font, $column)
    {
        switch ($column) {
            case 'type':
                $stack = $font['stack'];
                $array = explode(',', $stack);
                return $array[count($array)-1];
            case 'scripts':
                return implode(',', $font['scripts']);
            default:
                if (!isset($font[$column])) {
                    return '';
                }
                return $font[$column];
        }
    }
    function column_name($font)
    {
        $actions = [];
        if (isset($font['actions'])) {
            foreach ($font['actions'] as $key => $action) {
                $actions[$key] = sprintf('<a href="?page=%s&fontAction=%s&font=%s&nonce=%s&noheader=true">' . $action['text'] . '</a>', $_REQUEST['page'], $action['action'], $font['slug'], $this->nonce);
            }
        }
        $name =  $font['name'];
        if (in_array($font['slug'], $this->active)) {
            $name = "<strong>" . $name  . "</strong>";
        }
        return sprintf('%1$s %2$s', $name, $this->row_actions($actions));
    }
    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->fonts;
        foreach ($this->items as $slug => &$info) {
            $info['slug'] = $slug;
        }
    }
}

function Bread_custom_fonts_setup_page_render(Bread_AdminDisplay $breadAdminDisplay)
{
    $table = new Bread_Custom_Fonts_Table($breadAdminDisplay->getBreadInstance());
    $table->prepare_items();
    $table->display();
}
