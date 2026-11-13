<?php
class Bread
{
    private array $options;
    private Bread_Bmlt $bmlt1;
    function __construct($options)
    {
        if (! class_exists('WP_Filesystem_Direct')) {
            require_once '../../../wp-admin/includes/class-wp-filesystem-base.php';
            require_once '../../../wp-admin/includes/class-wp-filesystem-direct.php';
            require_once '../../../wp-includes/class-wp-error.php';
        }
        $this->load_translations();
        if ($this->versionLessThan('2.11', $options)) {
            if (empty($options['service_bodies'])) {
                for ($i = 1; $i <= 5; $i++) {
                    if (isset($options['service_body_' . $i])) {
                        $area_data = explode(',', $options['service_body_' . $i]);
                        $options['service_bodies'][] = $area_data[1];
                    }
                    $options['service_bodies'][$i] = $options['service_body_' . $i];
                }
            } else {
                for ($i = 1; $i <= 5; $i++) {
                    $parts = explode(',', $options['service_bodies'][$i - 1] ?? '');
                    if (count($parts) > 1) {
                        $options['service_bodies'][$i - 1] = $parts[1];
                    }
                }
            }
        }
        $settings['bread_version'] = "2.11.0";
        $this->options = $options;
        $this->bmlt1 = new Bread_Bmlt($this);
    }
    function bmlt()
    {
        return $this->bmlt1;
    }
    function getOptions()
    {
        return $this->options;
    }
    function load_translations()
    {
        $files = scandir("includes/lang");
        foreach ($files as $file) {
            if (strpos($file, "translate_") !== 0) {
                continue;
            }
            include 'includes/lang/' . $file;
            $key = substr($file, 10, -4);
            $this->translate[$key] = $bread_translate;
        }
    }
    public function getTranslateTable()
    {
        return $this->translate;
    }
    public function getday($day, $abbreviate = false, $language = 'en')
    {
        $key = "WEEKDAYS";
        if ($abbreviate) {
            $key = "WKDYS";
        }
        return $this->translate[$language][$key][$day];
    }
    private function versionLessThan($version, $settings): bool
    {
        if (empty($settings['bread_version'])) {
            return true;
        }
        $current = explode('.', $settings['bread_version']);
        $test = explode('.', $version);
        for ($i=0; $i<count($current); $i++) {
            if ($i >= count($test)) {
                return false;
            }
            if (intval($current[$i]) > intval($test[$i])) {
                return false;
            }
            if (intval($current[$i]) < intval($test[$i])) {
                return true;
            }
        }
        return false;
    }
}
