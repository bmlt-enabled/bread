<?php
class Bread
{
    private static $instance;
    private $translate = array();
    function __construct()
    {
        Bread::$instance = $this;
        Bread::load_translations();
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
            Bread::$instance->translate[$key] = $translate;
        }
    }
    public static function getTranslateTable()
    {
        return Bread::$instance->translate;
    }
    public static function getday($day, $abbreviate = false, $language = 'en')
    {
        $key = "WEEKDAYS";
        if ($abbreviate) {
            $key = "WKDYS";
        }
        return mb_convert_encoding(Bread::$instance->translate[$language][$key][$day], 'UTF-8', mb_list_encodings());
    }
}
