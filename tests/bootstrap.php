<?php
    require_once __DIR__.'/mock/class-bread.php';
    require_once __DIR__.'/mock/class-bread-bmlt.php';
    require_once __DIR__.'/../public/class-bread-meetingslist-structure.php';
    require_once __DIR__.'/../public/class-bread-meeting-enhancer.php';
    require_once __DIR__.'/../public/class-bread-format-manager.php';
// Mock WordPress functions used for testing.
//phpcs:ignore
function apply_filters($hook_name, $value, $args)
{
    return $value;
}
//phpcs:ignore
function plugin_dir_url($file)
{
    return './';
}
