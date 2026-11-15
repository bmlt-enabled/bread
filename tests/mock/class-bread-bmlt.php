<?php
class Bread_Bmlt
{
    private array $service_bodies = array();
    private string $format_base;
    function __construct($bread)
    {
    }
    public function setFormatBase($format_base)
    {
        $this->format_base = $format_base;
    }
    public function set_service_bodies(array $service_bodies)
    {
        foreach ($service_bodies as $service_body) {
            $this->service_bodies[$service_body['id']] = $service_body;
        }
    }
    public function get_service_body(string $id): ?array
    {
        return $this->service_bodies[$id] ?? null;
    }
    public function get_service_bodies(): array
    {
        return $this->service_bodies;
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
        $json = (new WP_Filesystem_Direct(null))->get_contents('tests/formats/' . $this->format_base . '-' . $lang . ".json");
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
