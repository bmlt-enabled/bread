<?php
class Bread_Heading_Manager
{
    private string $header_style;
    private string $cont;
    private array $options;
    private int $main_index = 0;
    private int $sub_index = 0;
    private int $meeting_index = 0;
    private array $headerMeetings;
    private array $unique_heading;
    private string $main_heading_raw;
    private string $sub_heading_raw;
    private bool $newMainHeading;

    function upgradeHeaderData($meeting_sort)
    {
        $this->options['combine_headings'] = '';
        if ($meeting_sort === 'user_defined') {
            if ($this->options['sub_header_shown'] == 'combined') {
                $this->options['combine_headings'] = 'main_grouping - subgrouping';
            }
            return;
        }
        unset($this->options['subgrouping']);
        if ($meeting_sort === 'state') {
            $this->options['main_grouping'] = 'location_province';
            $this->options['subgrouping'] = 'location_municipality';
            $this->options['combine_headings'] = 'subgrouping, main_grouping';
        } elseif ($meeting_sort === 'city') {
            $this->options['main_grouping'] = 'location_municipality';
        } elseif ($meeting_sort === 'borough') {
            $this->options['main_grouping'] = 'location_city_subsection';
            $this->options['main_grouping_suffix'] = $this->options['borough_suffix'];
        } elseif ($meeting_sort === 'county') {
            $this->options['main_grouping'] = 'location_sub_province';
            $this->options['main_grouping_alt_suffix'] = $this->options['county_suffix'];
        } elseif ($meeting_sort === 'borough_county') {
            $this->options['main_grouping'] = 'location_city_subsection';
            $this->options['main_grouping_suffix'] = $this->options['borough_suffix'];
            $this->options['main_grouping_alt'] = 'location_sub_province';
            $this->options['main_grouping_alt_suffix'] = $this->options['county_suffix'];
        } elseif ($meeting_sort === 'neighborhood_city') {
            $this->options['main_grouping'] = 'location_neighborhood';
            $this->options['main_grouping_suffix'] = $this->options['neighborhood_suffix'];
            $this->options['main_grouping_alt'] = 'location_municipality';
            $this->options['main_grouping_alt_suffix'] = $this->options['city_suffix'];
        } elseif ($meeting_sort === 'group') {
            $this->options['main_grouping'] = 'meeting_name';
        } elseif ($meeting_sort === 'weekday_area') {
            $this->options['main_grouping'] = 'day';
            $this->options['subgrouping'] = 'service_body_bigint';
        } elseif ($meeting_sort === 'weekday_city') {
            $this->options['main_grouping'] = 'day';
            $this->options['subgrouping'] = 'location_municipality';
        } elseif ($meeting_sort === 'weekday_county') {
            $this->options['main_grouping'] = 'day';
            $this->options['subgrouping'] = 'location_sub_province';
        } else {
            $this->options['main_grouping'] = 'day';
        }
    }
    function __construct(array $options, array $result_meetings, string $lang, int $include_asm)
    {
        $this->options = $options;
        $meeting_sort = $options['meeting_sort'];
        if ($include_asm > 0) {
            $this->options['suppess_heading'] = 1;
            switch ($options['asm_sort_order']) {
                case 'meeting_name':
                    $meeting_sort = 'meeting_name';
                    break;
                case 'weekday_tinyint,start_time':
                    $meeting_sort = 'day';
                    break;
                default:
                    break;
            }
        }
        $this->upgradeHeaderData($meeting_sort);

        $header_style = "color:".$options['header_text_color'].";";
        $header_style .= "background-color:".$options['header_background_color'].";";
        $header_style .= "font-size:".$options['header_font_size']."pt;";
        $header_style .= "line-height:".$options['content_line_height'].";";
        $header_style .= "text-align:center;padding-top:2px;padding-bottom:3px;";

        if ($options['header_uppercase'] == 1) {
            $header_style .= 'text-transform: uppercase;';
        }
        if ($options['header_bold'] == 0) {
            $header_style .= 'font-weight: normal;';
        }
        if ($options['header_bold'] == 1) {
            $header_style .= 'font-weight: bold;';
        }
        $this->header_style = $header_style;
        $this->cont = '('.Bread::getTranslateTable()[$lang]['CONT'].')';

        $this->headerMeetings = $this->getHeaderMeetings($result_meetings, $include_asm);
        $this->unique_heading = $this->getUniqueHeadings($this->headerMeetings);
    }
    function iterateMainHeading()
    {
        if ($this->main_index >= count($this->unique_heading)) {
            return null;
        }
        $this->main_heading_raw = $this->unique_heading[$this->main_index++];
        if ($this->skip_heading($this->main_heading_raw)) {
            return $this->iterateMainHeading();
        }
        $unique_subheading = array_keys($this->headerMeetings[$this->main_heading_raw]);
        asort($unique_subheading, SORT_NATURAL | SORT_FLAG_CASE);
        $this->sub_index = 0;
        return $unique_subheading;
    }
    function iterateSubHeading($unique_subheading, $still_new = false)
    {
        if ($this->sub_index >= count($unique_subheading)) {
            return null;
        }
        $this->newMainHeading = ($this->sub_index==0) || $still_new;
        $this->sub_heading_raw = $unique_subheading[$this->sub_index++];
        if ($this->skip_heading($this->sub_heading_raw)) {
            return $this->iterateSubHeading($unique_subheading, $this->newMainHeading);
        }
        $this->meeting_index = 0;
        return $this->headerMeetings[$this->main_heading_raw][$this->sub_heading_raw];
    }
    function iterateMeetings($meetings)
    {
        if ($this->meeting_index >= count($meetings)) {
            return null;
        }
        $this->newMainHeading = $this->newMainHeading && $this->meeting_index == 0;
        return $meetings[$this->meeting_index++];
    }
            // include_asm = 0  -  let everything through
        //               1  -  only meetings with asm format
        //              -1  -  only meetings without asm format
    function getHeaderMeetings(&$result_meetings, $include_asm)
    {
        $levels = $this->getHeaderLevels();
        $headerMeetings = array();
        foreach ($result_meetings as &$value) {
            $asm_test = $this->asm_test($value, $include_asm==1);
            if ((( $include_asm < 0 && $asm_test ) ||
                ( $include_asm > 0 && !$asm_test ))) {
                    continue;
            }
            $main_grouping = $this->getHeaderItem($value, $this->setupDefaultHeading('main_'), $include_asm==1);
            if (!isset($headerMeetings[$main_grouping])) {
                $headerMeetings[$main_grouping] = array();
                if ($levels == 1) {
                    $headerMeetings[$main_grouping][0] = array();
                }
            }
            if ($levels == 2) {
                $subgrouping = $this->getHeaderItem($value, $this->setupDefaultHeading('sub'), $include_asm==1);
                if (!isset($headerMeetings[$main_grouping][$subgrouping])) {
                    $headerMeetings[$main_grouping][$subgrouping] = array();
                }
                $headerMeetings[$main_grouping][$subgrouping][] = $value;
            } else {
                $headerMeetings[$main_grouping][0][] = $value;
            }
        }
        return $headerMeetings;
    }
    function getUniqueHeadings($headerMeetings)
    {
        $unique_heading = array_keys($headerMeetings);
        asort($unique_heading, SORT_NATURAL | SORT_FLAG_CASE);
        return $unique_heading;
    }
    function remove_sort_key($this_heading)
    {
        if (mb_substr($this_heading, 0, 1)=='[') {
            $end = strpos($this_heading, ']');
            if ($end>0) {
                return trim(substr($this_heading, $end+1));
            }
        }
        return $this_heading;
    }
    function skip_heading($this_heading)
    {
        return (mb_substr($this_heading, 0, 5)=='[XXX]');
    }
    function getHeaderLevels()
    {
        if (!empty($options['subgrouping'])) {
            return 2;
        }
        return 1;
    }
    function setupDefaultHeading($level)
    {
            return array(
                'name' =>  $level.'grouping',
                'name_alt' => $level.'grouping_alt',
                'name_suffix' => $level.'grouping_alt',
                'name_alt_suffix' => $level.'grouping_alt',
            );
    }
    function getHeaderItem($value, $names)
    {
        if (!$this->options[$names['name']]) {
                return '';
        }
        $grouping = '';
        $name = $this->options[$names['name']];
        if ($name=='service_body_bigint') {
            foreach (Bread_Bmlt::get_areas() as $unique_area) {
                $area_data = explode(',', $unique_area);
                $area_name = Bread::arraySafeGet($area_data);
                $area_id = Bread::arraySafeGet($area_data, 1);
                if ($area_id === $value['service_body_bigint']) {
                    return $area_name;
                }
            }
            return 'Area not found';
        } elseif ($name=='day') {
            $off = intval($this->options['weekday_start']);
            $day = intval($value['weekday_tinyint']);
            if ($day < $off) {
                $day = $day + 7;
            }
            return '['.str_pad($day, 2, '0', STR_PAD_LEFT).']'.$value['day'];
        } elseif (isset($value[$name])) {
            $grouping = Bread_Bmlt::parse_field($value[$name]);
        }
        $suffix = $this->options[$names['name_suffix']] ?? '';
        if ($grouping==''
            && !empty($name_alt)
            && isset($value[$name_alt])) {
            $grouping = Bread_Bmlt::parse_field($value[$name_alt]);
            $suffix = $this->options[$names['name_alt_suffix']] ?? '';
        }
        if (strlen(trim($grouping))==0) {
            return 'NO DATA';
        }
        if (!empty($suffix)) {
            return $grouping.' '.$suffix;
        }
        return $grouping;
    }
    function asm_test($value, $flag = false)
    {
        if (empty($this->options['asm_format_key'])) {
            return false;
        }
        $format_key = $this->options['asm_format_key'];
        if ($format_key == "@Virtual@") {
            if ($flag && $this->isHybrid($value)) {
                return false;
            }
            return $this->isVirtual($value) || $this->isHybrid($value);
        }
        if ($format_key == "@F2F@") {
            return !$this->isVirtual($value) || $this->isHybrid($value);
        }
        $enFormats = explode(",", $value['formats']);
        return in_array($format_key, $enFormats);
    }
    function isHybrid($value)
    {
        if (empty($value['formats'])) {
            return false;
        }
        $enFormats = explode(",", $value['formats']);
        return in_array('HY', $enFormats);
    }
    function isVirtual($value)
    {
        if (empty($value['formats'])) {
            return false;
        }
        $enFormats = explode(",", $value['formats']);
        return in_array('VM', $enFormats);
    }
    function calculateHeading()
    {
        $header = '';
        if ($this->options['suppress_heading']==1) {
            return $header;
        }
        $this_heading = $this->remove_sort_key($this->main_heading_raw);
        $this_subheading = $this->remove_sort_key($this->sub_heading_raw);
        if (($this->meeting_index==1) && !empty($options['combine_headings'])) {
            $header_string =  $this->options['combine_headings'];
            $header_string =  str_replace('main_grouping', $this_heading, $header_string);
            $header_string =  str_replace('subgrouping', $this_subheading, $header_string);
            $header .= "<div style='".$this->header_style."'>".$header_string."</div>";
        } elseif (!empty($options['subgrouping'])) {
            if ($this->main_index==1) {
                $xtraMargin = '';
                if (!$this->main_index>1 or $this->meeting_index>1) {
                    $xtraMargin = 'margin-top:2pt;';
                }
                $header .= '<div style="'.$this->header_style.$xtraMargin.'">'.$this_heading."</div>";
            }
            if (($this->meeting_index==1) && $this->options['sub_header_shown']=='display') {
                $header .= "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>".$this_subheading."</p>";
            }
        } elseif ($this->newMainHeading) {
            $header .= "<div style='".$this->header_style."'>".$this_heading."</div>";
        }
        return $header;
    }
    function calculateContHeader()
    {
        $header = '';
        if ($this->options['suppress_heading']==1) {
            return $header;
        }
        if (!$this->newMainHeading && $this->options['cont_header_shown']) {
            $header = "<div style='".$this->header_style."'>".$this->remove_sort_key($this->main_heading_raw)." " . $this->cont . "</div>";
        }
        return $header;
    }
}
