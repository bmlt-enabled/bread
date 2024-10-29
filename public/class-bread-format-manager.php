<?php
class Bread_FormatsManager {
    private $usedFormats = array();
    private $allFormats = array();
    private $hashedFormats = array();
    private $defaultLang;
    function __construct(&$usedFormats, $lang) {
        $this->usedFormats[$lang] = $usedFormats;
        $this->hashedFormats[$lang] = $this->hashFormats($usedFormats);
        $this->defaultLang = $lang;
    }
    function getFormatsUsed() {
       return $this->usedFormats[$this->defaultLang];
    }
    function hashFormats($formats) {
        $ret = array();
        foreach ($formats as $format) {
            $ret[$format['key_string']] = $format;
        }
        return $ret;
    }
    function loadFormats($lang) {
        if (isset($this->allFormats[$lang])) {
            return;
        }
        $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetFormats$lang");
        $this->allFormats[$lang] = json_decode(wp_remote_retrieve_body($results), true);
        Bread_Bmlt::sortBySubkey($this->allFormats[$lang], 'key_string');
        $this->hashedFormats[$lang] = $this->hashFormats($this->allFormats[$lang]);
    }
    function getFormatFromField($lang, $field, $id) {
        if (!isset($this->allFormats[$lang])) {
            if (isset($this->usedFormats[$lang])) {
                $found = $this->searchField($this->usedFormats[$lang], $id, $field);
                if ($found != null) {
                    return $found;
                }
            }
            $this->loadFormats($lang);
        }
        return $this->searchField($this->allFormats[$lang], $id, $field);
    }
    function searchField($formats, $id, $field) {
        foreach($formats as $format) {
            if ($format[$field] == $id) return $format;
        }
        return null;
    }
    function getFormatByKey($lang, $key) {
        if (!isset($this->hashedFormats[$lang])) {
            $this->loadFormats($lang);
        }
        if (isset($this->hashedFormats[$lang][$key])) {
            return $this->hashedFormats[$lang][$key];
        }
        if (isset($this->allFormats[$lang])) {
            return null;
        }
        $this->loadFormats($lang);
        if (isset($this->hashedFormats[$lang][$key])) {
            return $this->hashedFormats[$lang][$key];
        }
        return null;
    }
    function getUsedFormats($lang)
    {
        if (isset($this->usedFormats[$lang])) {
            return $this->usedFormats[$lang];
        }
        $this->loadFormats($lang);
        $this->usedFormats[$lang] = array();
        foreach($this->usedFormats[$this->defaultLang] as $usedFormat) {
            $this->usedFormats[$lang] = $this->getFormatFromField($lang, 'id', $usedFormat['id']);
        }
    }
    function getHashedFormats($lang) {
        if (!isset($this->hashedFormats[$lang])) {
            $this->loadFormats($lang);
        }
        return $this->hashedFormats[$lang];
    }
    function write_detailed_formats($lang, $isAll, $lineHeight, $fontSize)
    {
        $formats = $isAll ? $this->allFormats[$lang] : $this->getUsedFormats($lang);
        if (empty($formats)) {
            return '';
        }
        $data = "<table style='width:100%;font-size:".$fontSize."pt;line-height:".$lineHeight.";'>";
        foreach($formats as $format) {
                $data .= "<tr><td style='border-bottom:1px solid #555;width:8%;vertical-align:top;'><span style='font-size:" . $fontSize . "pt;line-height:" . $lineHeight . ";font-weight:bold;'>" . $format['key_string'] . "</span></td>";
                $data .= "<td style='border-bottom:1px solid #555;width:92%;vertical-align:top;'><span style='font-size:" . $fontSize . "pt;line-height:" . $lineHeight . ";'>(" . $format['name_string'] . ") " . $format['description_string'] . "</span></td></tr>";
        }
        $data .= "</table>";
        return $data;
    }
    function write_formats($lang, $isAll, $lineHeight, $fontSize)
    {
        $formats = $isAll ? $this->allFormats[$lang] : $this->getUsedFormats($lang);
        if (empty($formats)) {
            return '';
        }
        $data = "<table style='width:100%;font-size:".$fontSize."pt;line-height:".$lineHeight.";'>";
        for ($count = 0; $count < count($formats); $count++) {
            $data .= '<tr>';
            $data .= "<td style='font-size:".$fontSize."pt;line-height:".$lineHeight.";padding-left:4px;border:1px solid #555;border-right:0;width:12%;vertical-align:top;'>".$formats[$count]['key_string']."</td>";
            $data .= "<td style='font-size:".$fontSize."pt;line-height:".$lineHeight.";border: 1px solid #555;border-left:0;width:38%;vertical-align:top;'>".$formats[$count]['name_string']."</td>";
            $count++;
            if ($count >= count($formats)) {
                $data .= "<td style='font-size:".$fontSize."pt;line-height:".$lineHeight.";padding-left:4px;border: 1px solid #555;border-right:0;width:12%;vertical-align:top;'></td>";
                $data .= "<td style='font-size:".$fontSize."pt;line-height:".$lineHeight.";border: 1px solid #555;border-left:0;width:38%;vertical-align:top;'></td>";
            } else {
                $data .= "<td style='font-size:".$fontSize."pt;line-height:".$lineHeight.";padding-left:4px;border: 1px solid #555;border-right:0;width:12%;vertical-align:top;'>".$formats[$count]['key_string']."</td>";
                $data .= "<td style='font-size:".$fontSize."pt;line-height:".$lineHeight.";border: 1px solid #555;border-left:0;width:38%;vertical-align:top;'>".$formats[$count]['name_string']."</td>";
            }
            $data .= "</tr>";
        }
        $data .= "</table>";
        return $data;
    }
}