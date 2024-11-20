<?php
class Bread_Bmlt
{
    private array $areas = array();
    private string $format_base;
    function __construct($bread)
    {
    }
    public function setFormatBase($format_base)
    {
        $this->format_base = $format_base;
    }
    public function set_areas(array $areas)
    {
        $this->areas = $areas;
    }
    public function get_areas()
    {
        return $this->areas;
    }
    public static function parse_field($text)
    {
        if ($text != '') {
            $exploded = explode("#@-@#", $text);
            $knt = count($exploded);
            if ($knt > 1) {
                $text = $exploded[$knt - 1];
            }
        }
        return $text;
    }
    public function get_formats_by_language(string $lang)
    {
        $json = file_get_contents('tests/formats/' . $this->format_base . '-' . $lang . ".json");
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
