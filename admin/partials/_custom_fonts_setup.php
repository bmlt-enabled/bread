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
                $stack = $font['letterform'];
                $array = explode(',', $stack);
                return $array[count($array)-1];
            case 'scripts':
                return implode(', ', $font['scripts']);
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
                $actions[$key] = sprintf('<a href="?page=%s&fontAction=%s&font=%s&nonce=%s&noheader=true">' . esc_attr($action['text']) . '</a>', esc_attr($_REQUEST['page']), esc_attr($action['action']), esc_attr($font['slug']), esc_attr($this->nonce));
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
        if (isset($_GET['script']) && $_GET['script'] != '*') {
            $this->items = array_filter($this->items, function ($font) {
                return in_array($_GET['script'], $font['scripts']);
            });
        }
        if (isset($_GET['letterform']) && $_GET['letterform'] != '*') {
            $this->items = array_filter($this->items, function ($font) {
                return str_starts_with($font['letterform'], $_GET['letterform']);
            });
        }
        foreach ($this->items as $slug => &$info) {
            $info['slug'] = $slug;
        }
        if (isset($_GET['view']) && $_GET['view'] != 'all') {
            $this->items = array_filter($this->items, function ($font) {
                $bool = in_array($font['slug'], $this->active);
                return ($_GET['view'] == 'active') ? $bool : !$bool;
            });
        }
    }
    private function selected($a, $b)
    {
        return ($a == $b) ? 'selected' : '';
    }
    private function getAllScripts(): array
    {
        $ret = [];
        foreach ($this->fonts as $font) {
            foreach ($font['scripts'] as $script) {
                if (!in_array($script, $ret)) {
                    $ret[] = $script;
                }
            }
        }
        return $ret;
    }
    private function getAllLetterforms(): array
    {
        $ret = [];
        foreach ($this->fonts as $font) {
            if (!in_array($font['letterform'], $ret)) {
                $ret[] = $font['letterform'];
            }
        }
        return $ret;
    }
    protected function get_views(): array
    {
        $filters = "";
        if (isset($_GET['script'])) {
            $filters .= "&script=" . $_GET['script'];
        }
        if (isset($_GET['letterform'])) {
            $filters .= "&letterform=" . $_GET['letterform'];
        }
        $ret = [
            'all' => sprintf('<a href="?page=%s&view=all%s">All</a>', esc_attr($_REQUEST['page']), $filters),
            'active' => sprintf('<a href="?page=%s&view=active%s">Active</a>', esc_attr($_REQUEST['page']), $filters),
            'disabled' => sprintf('<a href="?page=%s&view=disabled%s">Disabled</a>', esc_attr($_REQUEST['page']), $filters),
        ];
        if (isset($_GET['view']) && in_array($_GET['view'], array_keys($ret))) {
            $ret[$_GET['view']] = "<strong>" . $ret[$_GET['view']] . "</strong>";
        } else {
            $ret['all'] = "<strong>" . $ret['all'] . "</strong>";
        }
        return $ret;
    }
    protected function extra_tablenav($which)
    {
        $letterform = $_GET['letterform'] ?? '*';
        $script = $_GET['script'] ?? '*'; ?>
        <form method="GET" action="#" id="filter-fonts-form">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']);?>">
        <input type="hidden" name="view" value="<?php echo esc_attr($_REQUEST['view'] ?? '*');?>">
        <label for="filter-fonts-by-script" class="screen-reader-text">Filter by supported scripts</label>
        <select name="script" id="filter-fonts-by-script" class="bread-font-filter">
            <option <?php echo esc_attr($this->selected($script, '*')); ?> value="*">All scripts</option>
        <?php
        foreach ($this->getAllScripts() as $s) {
            echo '<option ' . esc_attr($this->selected($script, $s)) . " value='".esc_attr($s)."'>".esc_attr($s)."</option>";
        }
        ?>
        </select>
        <label for="filter-fonts-by-letterform" class="screen-reader-text">Filter by letterform</label>
        <select name="letterform" id="filter-fonts-by-letterform" class="bread-font-filter">
            <option <?php echo esc_attr($this->selected($letterform, '*')); ?> value="*">All letterforms</option>
        <?php
        foreach ($this->getAllLetterforms() as $s) {
            echo '<option ' . esc_attr($this->selected($letterform, $s)) . " value='".esc_attr($s)."'>".esc_attr($s)."</option>";
        }
        ?>
        </select>
        </form>
        <?php
    }
}
function Bread_custom_fonts_setup_page_render(Bread_AdminDisplay $breadAdminDisplay)
{
    $table = new Bread_Custom_Fonts_Table($breadAdminDisplay->getBreadInstance());
    $table->views();
    $table->prepare_items();
    $table->display();
}