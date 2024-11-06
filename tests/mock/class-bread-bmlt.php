<?php
Class Bread_Bmlt
{
    private array $areas;
    public static function set_areas(array $areas) {
       Bread_Bmlt::$areas = $areas;
    }
    public static function get_areas() {
        return Bread_Bmlt::$areas;
    }
}