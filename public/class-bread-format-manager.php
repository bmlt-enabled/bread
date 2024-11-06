<?php
/**
 * Central storage and management of the meeting formats. Also generates the HTML table of formats for inclusion in the PDF.
 * We attempt to do as much lazy loading as possible, to minimize (remote) calls to the root server.
 */
class Bread_FormatsManager
{
    /**
     * The array of formats that are actually used by meetings in the meeting list.  Organized by language, and then by format.
     *
     * @var array
     */
    private $usedFormats = array();
    /**
     * The array of formats that defined by the root server.  Organized by language, and then by format.
     *
     * @var array
     */
    private $allFormats = array();
    /**
     * The array of formats, this time as key value pairs, organized by key_string.
     *
     * @var array
     */
    private $hashedFormats = array();
    /**
     * The default language.
     *
     * @var string
     */
    private string $defaultLang;
    private array|null $wheelchairFormat = array();
    /**
     * The info regarding the formats used is available already during construction because it is returned by the initial root server query.
     *
     * @param array $usedFormats The array of formats.
     * @param string $lang The language of the formats.
     */
    function __construct(array &$usedFormats, string $lang)
    {
        $this->usedFormats[$lang] = $usedFormats;
        $this->hashedFormats[$lang] = $this->hashFormats($usedFormats);
        $this->defaultLang = $lang;
    }
    /**
     * Returns the array of formats that are actually used by meetings in the meeting list.  Key strings and descriptions are from the default language.
     *
     * @var array
     */
    public function getFormatsUsed(): array
    {
        return $this->usedFormats[$this->defaultLang];
    }
    /**
     * Helper functtion to create a key=>value array of formats for convenient lookup
     *
     * @param array $formats the list of formats
     * @return array The key=>value pairs of formats (key==key_string)
     */
    private function hashFormats(array $formats): array
    {
        $ret = array();
        foreach ($formats as $format) {
            $ret[$format['key_string']] = $format;
        }
        return $ret;
    }
    /**
     * Retrieves the full list of formats for a particular language
     *
     * @param string $lang The language.
     * @return void
     */
    private function loadFormats(string $lang): void
    {
        if (isset($this->allFormats[$lang])) {
            return;
        }
        $this->allFormats[$lang] = Bread_Bmlt::get_formats_by_language($lang);
        Bread_Bmlt::sortBySubkey($this->allFormats[$lang], 'key_string');
        $this->hashedFormats[$lang] = $this->hashFormats($this->allFormats[$lang]);
    }
    /**
     * Undocumented function
     *
     * @param string $lang
     * @param string $field
     * @param string $id
     * @return array
     */
    public function getFormatFromField(string $lang, string $field, string $id): array
    {
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
    private function searchField(array $formats, string $id, string $field)
    {
        foreach ($formats as $format) {
            if ($format[$field] == $id) {
                return $format;
            }
        }
        return null;
    }
    public function getFormatByKey(string $lang, string $key)
    {
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
    public function getUsedFormats(string $lang)
    {
        if (isset($this->usedFormats[$lang])) {
            return $this->usedFormats[$lang];
        }
        $this->loadFormats($lang);
        $this->usedFormats[$lang] = array();
        foreach ($this->usedFormats[$this->defaultLang] as $usedFormat) {
            $this->usedFormats[$lang] = $this->getFormatFromField($lang, 'id', $usedFormat['id']);
        }
    }
    public function getHashedFormats(string $lang)
    {
        if (!isset($this->hashedFormats[$lang])) {
            $this->loadFormats($lang);
        }
        return $this->hashedFormats[$lang];
    }
    public function write_detailed_formats(string $lang, bool $isAll, int $lineHeight, int $fontSize)
    {
        $formats = $isAll ? $this->allFormats[$lang] : $this->getUsedFormats($lang);
        if (empty($formats)) {
            return '';
        }
        $data = "<table style='width:100%;font-size:".$fontSize."pt;line-height:".$lineHeight.";'>";
        foreach ($formats as $format) {
                $data .= "<tr><td style='border-bottom:1px solid #555;width:8%;vertical-align:top;'><span style='font-size:" . $fontSize . "pt;line-height:" . $lineHeight . ";font-weight:bold;'>" . $format['key_string'] . "</span></td>";
                $data .= "<td style='border-bottom:1px solid #555;width:92%;vertical-align:top;'><span style='font-size:" . $fontSize . "pt;line-height:" . $lineHeight . ";'>(" . $format['name_string'] . ") " . $format['description_string'] . "</span></td></tr>";
        }
        $data .= "</table>";
        return $data;
    }
    public function write_formats(string $lang, bool $isAll, int $lineHeight, int $fontSize)
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
    public function getWheelchairFormat($lang)
    {
        if (is_array($this->wheelchairFormat) && empty($this->wheelchairFormat)) {
            $this->wheelchairFormat = $this->getFormatFromField($lang, 'world_id', 'WCHR');
        }
        return $this->wheelchairFormat;
    }
}