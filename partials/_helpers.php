<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

function validate_page_orientation($page_orientation) {
    return $page_orientation == "L" || $page_orientation == "P" ? sanitize_text_field($page_orientation) : "";
}

function validate_hex_color($color) {
    return preg_match('/^#[a-f0-9]{6}$/i', $color) ? $color : "";
}

function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : "";
}