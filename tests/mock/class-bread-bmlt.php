<?php
Class Bread_Bmlt
{
    private static array $areas = array();
    private static string $format_base;
    public static function setFormatBase($format_base)
    {
        Bread_Bmlt::$format_base = $format_base;
    }
    public static function set_areas(array $areas) {
       Bread_Bmlt::$areas = $areas;
    }
    public static function get_areas() {
        return Bread_Bmlt::$areas;
    }
    public static function parse_field($text)
    {
        if ($text!='') {
            $exploded = explode("#@-@#", $text);
            $knt = count($exploded);
            if ($knt > 1) {
                $text = $exploded[$knt-1];
            }
        }
        return $text;
    }
    public static function get_formats_by_language(string $lang)
    {
        $json = file_get_contents('tests/formats/'.Bread_Bmlt::$format_base.'-'.$lang.".json");
        return json_decode($json, true);
    }
    public static function sortBySubkey(array &$array, string $subkey, int $sortType = SORT_ASC): void
    {
        if (empty($array)) {
            return;
        }
        foreach ($array as $subarray) {
            $keys[] = $subarray[$subkey];
        }
        array_multisort($keys, $sortType, $array);
    }
}