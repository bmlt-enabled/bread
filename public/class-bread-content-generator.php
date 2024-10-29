<?php
use function DeepCopy\deep_copy;

class Bread_ContentGenerator
{
    private $mpdf;
    private $options;
    private $result_meetings;
    private $shortcodes;
    private $unique_areas;
    private $meeting_count;
    private $target_timezone;
    private $wheelchair_format;
    private $formatsManager;
    private $legacy_synonyms = array (
        'borough'   => 'location_city_subsection',
        'time'      => 'start_time',
        'state'     => 'location_province',
        'street'    => 'location_street',
        'neighborhood'  => 'location_neighborhood',
        'city'          => 'location_municipality',
        'zip'           => 'location_postal_code_1',
        'location'      => 'location_text',
        'info'          => 'location_info',
        'county'        => 'location_sub_province',
        'group'         => 'meeting_name',
        'email'         => 'email_contact',
        'mins'          => 'duration_m',
        'hrs'           => 'duration_h',
        "area"          => 'area_name',
    );
    function __construct($mpdf, $options, $result_meetings, $formatsManager)
    {
        $this->mpdf = $mpdf;
        $this->options = $options;
        $this->result_meetings = $this->orderByWeekdayStart($result_meetings);
        $this->unique_areas = Bread_Bmlt::get_areas();
        $this->formatsManager = $formatsManager;
        if (isset($_GET['time_zone'])) {
            $this->target_timezone = timezone_open($_GET['time_zone']);
        }
        $this->meeting_count = count($result_meetings);
        $this->wheelchair_format = $formatsManager->getFormatFromField($this->options['weekday_language'], 'world_id', 'WCHR');
        $this->shortcodes = array(
            '<h2>'                          => '<h2 style="font-size:'.$this->options['front_page_font_size'] . 'pt!important;">',
            '<div>[page_break]</div>'       =>  '<pagebreak />',
            '<p>[page_break]</p>'           =>  '<pagebreak />',
            '[page_break]'                  =>  '<pagebreak />',
            '<!--nextpage-->'               =>  '<pagebreak />',
            "[area]"                        =>  strtoupper($this->options['service_body_1']),
            '<div>[new_column]</div>'       =>  '<columnbreak />',
            '<p>[new_column]</p>'           =>  '<columnbreak />',
            '[new_column]'                  =>  '<columnbreak />',
            '[page_break no_page_number]'   => '<pagebreak /><sethtmlpagefooter name="" value="0" />',
            '[start_page_numbers]'          => '<sethtmlpagefooter name="MyFooter" page="ALL" value="1" />',
            "[month_lower]"                 => date("F"),
            "[month_upper]"                 => strtoupper(date("F")),
            "[month]"                       => strtoupper(date("F")),
            "[day]"                         => strtoupper(date("j")),
            "[year]"                        => strtoupper(date("Y")),
            "[service_body]"                => strtoupper($this->options['service_body_1']),
            "[service_body_1]"              => strtoupper($this->options['service_body_1']),
            "[service_body_2]"              => strtoupper($this->options['service_body_2']),
            "[service_body_3]"              => strtoupper($this->options['service_body_3']),
            "[service_body_4]"              => strtoupper($this->options['service_body_4']),
            "[service_body_5]"              => strtoupper($this->options['service_body_5']),

            );
        $this->shortcodes = apply_filters("Bread_Section_Shortcodes", $this->shortcodes, $this->unique_areas, $formatsManager->getFormatsUsed());
        if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
            $this->mpdf->DefHTMLFooterByName('MyFooter', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">'.$this->options['nonmeeting_footer'].'</div>');
            $this->mpdf->DefHTMLFooterByName('_default', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">'.$this->options['nonmeeting_footer'].'</div>');
            $this->mpdf->DefHTMLFooterByName('Meeting1Footer', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">'.$this->options['meeting1_footer'].'</div>');
            $this->mpdf->DefHTMLFooterByName('Meeting2Footer', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">'.$this->options['meeting2_footer'].'</div>');
        }
        if (isset($this->options['pageheader_content'])) {
            $data = $this->options['pageheader_content'];
            $this->standard_shortcode_replacement($data, 'pageheader', $this->shortcodes);
            $header_style = "vertical-align: top; text-align: center; font-weight: bold;margin-top:3px;margin-bottom:3px;";
            $header_style .= "color:".$this->options['pageheader_textcolor'].";";
            $header_style .= "background-color:".$this->options['pageheader_backgroundcolor'].";";
            $header_style .= "font-size:".$this->options['pageheader_fontsize']."pt;";
            $header_style .= "line-height:".$this->options['content_line_height'].";";

            $this->mpdf->SetHTMLHeader(
                '<div style="'.$header_style.'">'.$data.'</div>',
                'O'
            );
        }
        if (isset($this->options['watermark'])) {
            $this->mpdf->SetWatermarkImage($this->options['watermark'], 0.2, 'F');
            $this->mpdf->showWatermarkImage = true;
        }
    }
    public function generate($num_columns)
    {
        $this->mpdf->SetColumns($num_columns, '', $this->options['column_gap']);
        if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
            $this->write_front_page();
        }
        $this->mpdf->WriteHTML('td{font-size: '.$this->options['content_font_size']."pt;line-height:".$this->options['content_line_height'].';background-color:#ffffff00;}', 1);
        $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['content_font_size'] . 'pt');
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['content_line_height']);
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        $this->upgradeHeaderData();
        if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
            $this->WriteHTML('<sethtmlpagefooter name="Meeting1Footer" page="ALL" />');
        }
        $this->writeMeetings($this->result_meetings, $this->options['meeting_template_content'], $this->options['weekday_language'], $this->options['include_asm']==0 ? -1 : 0, true);

        if ($this->options['page_fold'] !== 'half' && $this->options['page_fold'] !== 'full') {
            $this->write_custom_section();
            $this->write_front_page();
        } else {
            $this->mpdf->WriteHTML('<sethtmlpagefooter name="MyFooter" page="ALL" />');
            if (trim($this->options['last_page_content']) !== '') {
                $this->write_last_page();
            }
        }
    }
    function writeHTML($str)
    {
        //$str = htmlentities($str);
        @$this->mpdf->WriteHTML(wpautop(stripslashes($str)));
    }
    function upgradeHeaderData()
    {
        $this->options['combine_headings'] = '';
        if ($this->options['meeting_sort'] === 'user_defined') {
            if ($this->options['sub_header_shown'] == 'combined') {
                $this->options['combine_headings'] = 'main_grouping - subgrouping';
            }
            return;
        }
        unset($this->options['subgrouping']);
        if ($this->options['meeting_sort'] === 'state') {
            $this->options['main_grouping'] = 'location_province';
            $this->options['subgrouping'] = 'location_municipality';
            $this->options['combine_headings'] = 'subgrouping, main_grouping';
        } elseif ($this->options['meeting_sort'] === 'city') {
            $this->options['main_grouping'] = 'location_municipality';
        } elseif ($this->options['meeting_sort'] === 'borough') {
            $this->options['main_grouping'] = 'location_city_subsection';
            $this->options['main_grouping_suffix'] = $this->options['borough_suffix'];
        } elseif ($this->options['meeting_sort'] === 'county') {
            $this->options['main_grouping'] = 'location_sub_province';
            $this->options['main_grouping_alt_suffix'] = $this->options['county_suffix'];
        } elseif ($this->options['meeting_sort'] === 'borough_county') {
            $this->options['main_grouping'] = 'location_city_subsection';
            $this->options['main_grouping_suffix'] = $this->options['borough_suffix'];
            $this->options['main_grouping_alt'] = 'location_sub_province';
            $this->options['main_grouping_alt_suffix'] = $this->options['county_suffix'];
        } elseif ($this->options['meeting_sort'] === 'neighborhood_city') {
            $this->options['main_grouping'] = 'location_neighborhood';
            $this->options['main_grouping_suffix'] = $this->options['neighborhood_suffix'];
            $this->options['main_grouping_alt'] = 'location_municipality';
            $this->options['main_grouping_alt_suffix'] = $this->options['city_suffix'];
        } elseif ($this->options['meeting_sort'] === 'group') {
            $this->options['main_grouping'] = 'meeting_name';
        } elseif ($this->options['meeting_sort'] === 'weekday_area') {
            $this->options['main_grouping'] = 'day';
            $this->options['subgrouping'] = 'service_body_bigint';
        } elseif ($this->options['meeting_sort'] === 'weekday_city') {
            $this->options['main_grouping'] = 'day';
            $this->options['subgrouping'] = 'location_municipality';
        } elseif ($this->options['meeting_sort'] === 'weekday_county') {
            $this->options['main_grouping'] = 'day';
            $this->options['subgrouping'] = 'location_sub_province';
        } else {
            $this->options['main_grouping'] = 'day';
        }
    }
    function standard_shortcode_replacement(&$data, $page)
    {
        $search_strings = array();
        $replacements = array();
        foreach ($this->shortcodes as $key => $value) {
            $search_strings[] = $key;
            $replacements[] = $value;
        }

        $search_strings[] = '[meeting_count]';
        $replacements[] =  $this->meeting_count;
        $data = $this->options[$page.'_content'];
        $data = $this->locale_month_replacement($data, 'lower', "LLLL");
        $data = $this->locale_month_replacement($data, 'upper', "LLLL");
        $data = str_replace($search_strings, $replacements, $data);
        $this->replace_format_shortcodes($data, $page);
        $data = str_replace("[date]", strtoupper(date("F Y")), $data);
        if ($this->target_timezone) {
            $data = str_replace('[timezone]', $this->target_timezone->getName(), $data);
        }
    }
    function locale_month_replacement($data, $case, $sym)
    {
        $strpos = strpos($data, "[month_$case"."_");
        if ($strpos !== false) {
            $locLang = substr($data, $strpos+13, 2);
            if (!isset($this->translate[$locLang])) {
                $locLang = 'en';
            }
            $fmt = new IntlDateFormatter(
                Bread::getTranslateTable()[$locLang]['LOCALE'],
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL
            );
            $fmt->setPattern($sym);
            $month = ucfirst(mb_convert_encoding($fmt->format(time()), 'UTF-8', 'ISO-8859-1'));
            if ($case=='upper') {
                   $month = mb_strtoupper($month, 'UTF-8');
            }
            return substr_replace($data, $month, $strpos, 16);
        }
        return $data;
    }
    function orderByWeekdayStart(&$result_meetings)
    {
        $days = array_column($result_meetings, 'weekday_tinyint');
        $today_str = $this->options['weekday_start'];
        return array_merge(
            array_splice($result_meetings, array_search($today_str, $days)),
            array_splice($result_meetings, 0)
        );
    }
    function write_front_page()
    {

        $this->mpdf->WriteHTML('td{font-size: '.$this->options['front_page_font_size']."pt;line-height:".$this->options['front_page_line_height'].';}', 1);
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['front_page_line_height']);
        $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['front_page_font_size'] . 'pt');
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        $this->options['front_page_content'] = wp_unslash($this->options['front_page_content']);
        $this->standard_shortcode_replacement($this->options['front_page_content'], 'front_page');


        $querystring_custom_items = array();
        preg_match_all('/(\[querystring_custom_\d+\])/', $this->options['front_page_content'], $querystring_custom_items);
        foreach ($querystring_custom_items[0] as $querystring_custom_item) {
            $mod_qs_ci = str_replace("]", "", str_replace("[", "", $querystring_custom_item));
            $this->options['front_page_content'] = str_replace($querystring_custom_item, (isset($_GET[$mod_qs_ci]) ? $_GET[$mod_qs_ci] : "NOT SET"), $this->options['front_page_content']);
        }
        $this->writeHTMLwithServiceMeetings($this->options['front_page_content'], 'front_page');
        $this->mpdf->showWatermarkImage = false;
    }

    function write_last_page()
    {
        $this->mpdf->WriteHTML('td{font-size: '.$this->options['last_page_font_size']."pt;line-height:".$this->options['last_page_line_height'].';}', 1);
        $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['last_page_font_size'] . 'pt');
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['last_page_line_height']);
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        $this->standard_shortcode_replacement($this->options['last_page_content'], 'last_page');
        $this->writeHTMLwithServiceMeetings($this->options['last_page_content'], 'last_page');
    }

    function write_custom_section()
    {
        $this->mpdf->SetHTMLHeader();
        if (isset($this->options['pageheader_content']) && trim($this->options['pageheader_content'])) {
            $this->mpdf->SetTopMargin($this->options['margin_header']);
        }
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['custom_section_line_height']);
        $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['custom_section_font_size'] . 'pt');
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        $this->standard_shortcode_replacement($this->options['custom_section_content'], 'custom_section');
        $this->mpdf->WriteHTML('td{font-size: '.$this->options['custom_section_font_size']."pt;line-height:".$this->options['custom_section_line_height'].';}', 1);
        $this->writeHTMLwithServiceMeetings($this->options['custom_section_content'], 'custom_section');
    }
            // include_asm = 0  -  let everything through
        //               1  -  only meetings with asm format
        //              -1  -  only meetings without asm format
    function writeMeetings($result_meetings, $template, $lang, $include_asm, $asm_flag)
    {
        if (count($result_meetings)==0) {
            return;
        }
        $headerMeetings = $this->getHeaderMeetings($result_meetings, $lang, $include_asm, $asm_flag);
        $unique_heading = $this->getUniqueHeadings($headerMeetings);

        $header_style = "color:".$this->options['header_text_color'].";";
        $header_style .= "background-color:".$this->options['header_background_color'].";";
        $header_style .= "font-size:".$this->options['header_font_size']."pt;";
        $header_style .= "line-height:".$this->options['content_line_height'].";";
        $header_style .= "text-align:center;padding-top:2px;padding-bottom:3px;";

        if ($this->options['header_uppercase'] == 1) {
            $header_style .= 'text-transform: uppercase;';
        }
        if ($this->options['header_bold'] == 0) {
            $header_style .= 'font-weight: normal;';
        }
        if ($this->options['header_bold'] == 1) {
            $header_style .= 'font-weight: bold;';
        }
        $cont = '('.Bread::getTranslateTable()[$lang]['CONT'].')';


        $template = wpautop(stripslashes($template));
        $template = preg_replace('/[[:^print:]]/', ' ', $template);

        $template = str_replace("&nbsp;", " ", $template);
        $analysedTemplate = $this->analyseTemplate($template);
        $first_meeting = true;
        $newMajorHeading = false;
        /***
         * You might be wondering why I am not using keep-with-table...
         * The problem is, keep with table doesn't work with columns, only pages.
         * We want to check that a header and at least one meeting fits, so we write it
         * to a test PDF, see how big it is, and check if it will fit.
         */
        $test_pages = deep_copy($this->mpdf);
        foreach ($unique_heading as $this_heading_raw) {
            $newMajorHeading = true;
            if ($this->skip_heading($this_heading_raw)) {
                continue;
            }
            $this_heading = $this->remove_sort_key($this_heading_raw);
            $unique_subheading = array_keys($headerMeetings[$this_heading_raw]);
            asort($unique_subheading, SORT_NATURAL | SORT_FLAG_CASE);
            foreach ($unique_subheading as $this_subheading_raw) {
                $newSubHeading = true;
                $this_subheading = $this->remove_sort_key($this_subheading_raw);
                foreach ($headerMeetings[$this_heading_raw][$this_subheading_raw] as $meeting_value) {
                    $header = '';
                    if ($newSubHeading && !empty($this->options['combine_headings'])) {
                        $header_string =  $this->options['combine_headings'];
                        $header_string =  str_replace('main_grouping', $this_heading, $header_string);
                        $header_string =  str_replace('subgrouping', $this_subheading, $header_string);
                        $header .= "<div style='".$header_style."'>".$header_string."</div>";
                    } elseif (!empty($this->options['subgrouping'])) {
                        if ($newMajorHeading === true) {
                            $xtraMargin = '';
                            if (!$first_meeting) {
                                $xtraMargin = 'margin-top:2pt;';
                            }
                            $header .= '<div style="'.$header_style.$xtraMargin.'">'.$this_heading."</div>";
                        }
                        if ($newSubHeading && $this->options['sub_header_shown']=='display') {
                            $header .= "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>".$this_subheading."</p>";
                        }
                    } elseif ($newMajorHeading === true) {
                        $header .= "<div style='".$header_style."'>".$this_heading."</div>";
                    }
                    if ($this->options['suppress_heading']==1) {
                        $header = '';
                    }
                    $data = $header . $this->write_single_meeting(
                        $meeting_value,
                        $template,
                        $analysedTemplate,
                        $meeting_value['area_name']
                    );
                    $this->writeBreak($test_pages);
                    $y_startpos = $test_pages->y;
                    @$test_pages->WriteHTML($data);
                    $y_diff = $test_pages->y - $y_startpos;
                    if ($y_diff >= $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height) {
                        $this->writeBreak($this->mpdf);
                        if (!$newMajorHeading && $this->options['cont_header_shown']) {
                            $header = "<div style='".$header_style."'>".$this_heading." " . $cont . "</div>";
                            $data = $header.$data;
                        }
                    }
                    $this->WriteHTML($data);
                    $first_meeting = false;
                    $newSubHeading = false;
                    $newMajorHeading = false;
                }
            }
        }
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
        $enFormats = explode(",", $value['formats']);
        return in_array('HY', $enFormats);
    }
    function isVirtual($value)
    {
        $enFormats = explode(",", $value['formats']);
        return in_array('VM', $enFormats);
    }
        // include_asm = 0  -  let everything through
        //               1  -  only meetings with asm format
        //              -1  -  only meetings without asm format
    function getHeaderMeetings(&$result_meetings, $lang, $include_asm, $asm_flag)
    {
        $levels = $this->getHeaderLevels();
        $headerMeetings = array();
        foreach ($result_meetings as &$value) {
            $value = $this->enhance_meeting($value, $lang);
            $asm_test = $this->asm_test($value, $asm_flag);
            if ((( $include_asm < 0 && $asm_test ) ||
                ( $include_asm > 0 && !$asm_test ))) {
                    continue;
            }
            $main_grouping = $this->getHeaderItem($value, 'main_grouping');
            if (!isset($headerMeetings[$main_grouping])) {
                $headerMeetings[$main_grouping] = array();
                if ($levels == 1) {
                    $headerMeetings[$main_grouping][0] = array();
                }
            }
            if ($levels == 2) {
                $subgrouping = $this->getHeaderItem($value, 'subgrouping');
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
    function writeBreak($mpdf)
    {
        if ($this->options['page_fold'] === 'half' || $this->options['page_fold'] === 'full') {
            $mpdf->WriteHTML("<pagebreak>");
        } else {
            $mpdf->WriteHTML("<columnbreak />");
        }
    }

    function analyseTemplate($template)
    {
        $arr = preg_split('/\W+/', $template, 0, PREG_SPLIT_OFFSET_CAPTURE);
        $arr = array_reverse($arr, true);
        $ret = array();
        foreach ($arr as $item) {
            if (strlen($item[0])<3) {
                continue;
            }
            $htmlTags = array('table', 'tbody', 'strong', 'left', 'right', 'top', 'bottom', 'center', 'align', 'font', 'size', 'text', 'style', 'family', 'vertical', 'color', 'QRCode');
            if (in_array($item[0], $htmlTags)) {
                continue;
            }
            if ($item[1]>0 && $template[$item[1]-1]=='['
                && $template[$item[1]+strlen($item[0])]==']') {
                    $item[0] = '['.$item[0].']';
                    $item[1] = $item[1] - 1;
                    $item[2] = true;
            } else {
                $item[2] = false;
            }
            $ret[] = $item;
        }
        return $ret;
    }
    function enhance_meeting(&$meeting_value, $lang)
    {
        $duration = explode(':', $meeting_value['duration_time']);
        $minutes = intval($duration[0])*60 + intval($duration[1]) + intval($duration[2]);
        $meeting_value['duration_m'] = $minutes;
        $meeting_value['duration_h'] = rtrim(rtrim(number_format($minutes/60, 2), 0), '.');
        $space = ' ';
        if ($this->options['remove_space'] == 1) {
            $space = '';
        }
        if ($this->options['time_clock'] == null || $this->options['time_clock'] == '12' || $this->options['time_option'] == '') {
            $time_format = "g:i".$space."A";
        } elseif ($this->options['time_clock'] == '24fr') {
            $time_format = "H\hi";
        } else {
            $time_format = "H:i";
        }
        if ($this->options['time_option'] == 1 || $this->options['time_option'] == '') {
            $meeting_value['start_time'] = date($time_format, strtotime($meeting_value['start_time']));
            if ($meeting_value['start_time'] == '12:00PM' || $meeting_value['start_time'] == '12:00 PM') {
                $meeting_value['start_time'] = 'NOON';
            }
        } elseif ($this->options['time_option'] == '2') {
            $addtime = '+ ' . $minutes . ' minutes';
            $end_time = date($time_format, strtotime($meeting_value['start_time'] . ' ' . $addtime));
            $meeting_value['start_time'] = date($time_format, strtotime($meeting_value['start_time']));
            if ($lang=='fa') {
                $meeting_value['start_time'] = $this->toPersianNum($end_time).$space.'-'.$space.$this->toPersianNum($meeting_value['start_time']);
            } else {
                $meeting_value['start_time'] = $meeting_value['start_time'].$space.'-'.$space.$end_time;
            }
        } elseif ($this->options['time_option'] == '3') {
            $time_array = array("1:00", "2:00", "3:00", "4:00", "5:00", "6:00", "7:00", "8:00", "9:00", "10:00", "11:00", "12:00");
            $temp_start_time = date("g:i", strtotime($meeting_value['start_time']));
            $temp_start_time_2 = date("g:iA", strtotime($meeting_value['start_time']));
            if ($temp_start_time_2 == '12:00PM') {
                $start_time = 'NOON';
            } elseif (in_array($temp_start_time, $time_array)) {
                $start_time = date("g", strtotime($meeting_value['start_time']));
            } else {
                $start_time = date("g:i", strtotime($meeting_value['start_time']));
            }
            $addtime = '+ ' . $minutes . ' minutes';
            $temp_end_time = date("g:iA", strtotime($meeting_value['start_time'] . ' ' . $addtime));
            $temp_end_time_2 = date("g:i", strtotime($meeting_value['start_time'] . ' ' . $addtime));
            if ($temp_end_time == '12:00PM') {
                $end_time = 'NOON';
            } elseif (in_array($temp_end_time_2, $time_array)) {
                $end_time = date("g".$space."A", strtotime($temp_end_time));
            } else {
                $end_time = date("g:i".$space."A", strtotime($temp_end_time));
            }
            $meeting_value['start_time'] = $start_time.$space.'-'.$space.$end_time;
        }

        $meeting_value['day_abbr'] = Bread::getday($meeting_value['weekday_tinyint'], true, $lang);
        $meeting_value['day'] = Bread::getday($meeting_value['weekday_tinyint'], false, $lang);
        $area_name = $this->get_area_name($meeting_value);
        $meeting_value['area_name'] = $area_name;
        $meeting_value['area_i'] = substr($area_name, 0, 1);

        $meeting_value['wheelchair'] = '';
        if (!is_null($this->wheelchair_format)) {
            $fmts = explode(',', $meeting_value['format_shared_id_list']);
            if (in_array($this->wheelchair_format['id'], $fmts)) {
                $meeting_value['wheelchair'] = '<img src="'.plugin_dir_url(__FILE__) . 'includes/wheelchair.png" width="'.$this->options['wheelchair_size'].'" height="'.$this->options['wheelchair_size'].'">';
            }
        }
        // Extensions.
        return apply_filters("Bread_Enrich_Meeting_Data", $meeting_value, $this->formatsManager->getHashedFormats($lang));
    }
    function write_single_meeting($meeting_value, $template, $analysedTemplate, $area_name)
    {
        $data = $template;
        $namedValues = array();
        foreach ($meeting_value as $field => $notUsed) {
            $namedValues[$field] = $this->get_field($meeting_value, $field);
        }
        foreach ($this->legacy_synonyms as $syn => $field) {
            $namedValues[$syn] = $namedValues[$field];
        }
        foreach ($analysedTemplate as $item) {
            $name = $item[0];
            if ($item[2]) {
                $name = substr($name, 1, strlen($name)-2);
            }
            if (isset($namedValues[$name])) {
                $data = substr_replace($data, $namedValues[$name], $item[1], strlen($item[0]));
            }
        }
        $qr_pos = strpos($data, "[QRCode");
        if ($qr_pos) {
            $qr_end = strpos($data, ']', $qr_pos);
            $data = substr($data, 0, $qr_pos).
                    '<barcode type="QR" disableborder="1" '.
                    substr($data, $qr_pos+8, $qr_end-$qr_pos-8).
                    '/>'.
                    substr($data, $qr_end+1);
        }
        $search_strings = array();
        $replacements = array();
        $clean_up = array(
            '<em></em>'     => '',
            '<em> </em>'    => '',
            '<strong></strong>' => '',
            '<strong> </strong>' => '',
            '<i></i>' => '',
            '<i> </i>' => '',
            '    '          => ' ',
            '   '           => ' ',
            '  '            => ' ',
            '<p></p>'       => '',
            '()'            => '',
            '<br/>'         => 'line_break',
            '<br />'        => 'line_break',
            'line_break line_break' => '<br />',
            'line_breakline_break'  => '<br />',
            'line_break'    => '<br />',
            '<br />,'       => '<br />',
            ', <br />'      => '<br />',
            ',<br />'       => '<br />',
            '<p>,'          => '<p>',
            ", , ,"         => ",",
            ", *,"          => ",",
            ", ,"           => ",",
            " , "           => " ",
            ", ("           => " (",
            ',</'           => '</',
            ', </'          => '</',
        );
        foreach ($clean_up as $key => $value) {
            $search_strings[] = $key;
            $replacements[] = $value;
        }
        $data = str_replace($search_strings, $replacements, $data);
        return $data;
    }
    function writeHTMLwithServiceMeetings($data, $page)
    {
        $strs = array('<p>[service_meetings]</p>','[service_meetings]',
                      '<p>[additional_meetings]</p>','[additional_meetings]');

        foreach ($strs as $str) {
            $pos = strpos($data, $str);
            if (!$pos) {
                continue;
            }
            if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                $this->WriteHTML('<sethtmlpagefooter name="Meeting2Footer" page="ALL" />');
            }
            $this->WriteHTML(substr($data, 0, $pos));
            $this->write_service_meetings($this->options[$page.'_font_size'], $this->options[$page.'_line_height']);
            if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                $this->WriteHTML('<sethtmlpagefooter name="MyFooter" page="ALL" />');
            }
            $this->WriteHTML(substr($data, $pos+strlen($str)));
            return;
        }
        $this->WriteHTML($data);
    }

    function replace_format_shortcodes(&$data, $page_name)
    {
        $lang = $this->options['weekday_language'];
        $this->shortcode_formats('[format_codes_used_basic]', false, $lang, false, $page_name, $data);
        $this->shortcode_formats('[format_codes_used_detailed]', true, $lang, false, $page_name, $data);
        $this->shortcode_formats('[format_codes_used_basic_es]', false, 'es', true, $page_name, $data);
        $this->shortcode_formats('[format_codes_used_detailed_es]', true, 'es', true, $page_name, $data);
        $this->shortcode_formats('[format_codes_used_basic_fr]', false, 'fr', true, $page_name, $data);
        $this->shortcode_formats('[format_codes_all_basic]', false, $lang, true, $page_name, $data);
        $this->shortcode_formats('[format_codes_all_detailed]', true, $lang, true, $page_name, $data);
    }
    function shortcode_formats($shortcode, $detailed, $lang, $isAll, $page, &$str)
    {
        $pos = strpos($str, $shortcode);
        if ($pos==false) {
            return;
        }
        $value = '';
        if ($detailed) {
            $value = $this->formatsManager->write_detailed_formats($lang, $isAll, $this->options[$page.'_line_height'], $this->options[$page.'_font_size']."pt");
        } else {
            $value = $this->formatsManager->write_formats($lang, $isAll, $this->options[$page.'_line_height'], $this->options[$page.'_font_size']."pt");
        }
        $str = substr($str, 0, $pos).$value.substr($str, $pos+strlen($shortcode));
    }
    function asm_required($data)
    {
        return strpos($data, '[service_meetings]') || strpos($data, '[additional_meetings]');
    }
    function getHeaderLevels()
    {
        if (!empty($this->options['subgrouping'])) {
            return 2;
        }
        return 1;
    }
    function getHeaderItem($value, $name)
    {
        if (empty($this->options[$name])) {
                return '';
        }
        $grouping = '';
        if ($this->options[$name]=='service_body_bigint') {
            foreach ($this->unique_areas as $unique_area) {
                $area_data = explode(',', $unique_area);
                $area_name = Bread::arraySafeGet($area_data);
                $area_id = Bread::arraySafeGet($area_data, 1);
                if ($area_id === $value['service_body_bigint']) {
                    return $area_name;
                }
            }
            return 'Area not found';
        } elseif ($this->options[$name]=='day') {
            $off = intval($this->options['weekday_start']);
            $day = intval($value['weekday_tinyint']);
            if ($day < $off) {
                $day = $day + 7;
            }
            return '['.str_pad($day, 2, '0', STR_PAD_LEFT).']'.$value['day'];
        } elseif (isset($value[$this->options[$name]])) {
            $grouping = $this->parse_field($value[$this->options[$name]]);
        }
        $alt = '';
        if ($grouping==''
            && !empty($this->options[$name.'_alt'])
            && isset($value[$this->options[$name.'_alt']])) {
            $grouping = $this->parse_field($value[$this->options[$name.'_alt']]);
            $alt = '_alt';
        }
        if (strlen(trim($grouping))==0) {
            return 'NO DATA';
        }
        if (!empty($this->options[$name.$alt.'_suffix'])) {
            return $grouping.' '.$this->options[$name.$alt.'_suffix'];
        }
        return $grouping;
    }
    function get_area_name($meeting_value)
    {
        foreach ($this->unique_areas as $unique_area) {
            $area_data = explode(',', $unique_area);
            $area_id = Bread::arraySafeGet($area_data, 1);
            if ($area_id === $meeting_value['service_body_bigint']) {
                return Bread::arraySafeGet($area_data);
            }
        }
        return '';
    }
    function get_field($obj, $field)
    {
        $value = '';
        if (isset($obj[$field])) {
                $value = $this->parse_field($obj[$field]);
        }
                    return $value;
    }
    function write_service_meetings($font_size, $line_height)
    {
        if (isset($this->options['asm_template_content']) && trim($this->options['asm_template_content'])) {
            $template = $this->options['asm_template_content'];
        } else {
            $template = $this->options['meeting_template_content'];
        }
        $asm_query = false;
        $service_meeting_result = $this->result_meetings;
        if (empty($this->options['asm_format_key']) || $this->options['asm_format_key'] == 'ASM') {
            $asm_query = true;
            $sort_order = $this->options['asm_sort_order'];
            if ($sort_order=='same') {
                $sort_order = 'weekday_tinyint,start_time';
            }
            $asm_id = "";
            if ($this->options['asm_format_key']==='ASM') {
                $asm_id = '&formats[]='.$this->formatsManager->getFormatByKey('ASM');
            }
            $services = Bread_Bmlt::generateDefaultQuery();
            if (!empty($this->options['asm_custom_query'])) {
                $services = $this->options['asm_custom_query'];
            }
            $asm_query = "client_interface/json/?switcher=GetSearchResults$services$asm_id&sort_keys=$sort_order";
            // ASM can contain E-Mail and phone numbers that require logins.
            if ($this->options['asm_format_key']==='ASM') {
                $asm_query .= "&advanced_published=0";
            }
            $results = Bread_Bmlt::get_configured_root_server_request($asm_query);
            $service_meeting_result = json_decode(wp_remote_retrieve_body($results), true);
            $this->adjust_timezone($service_meeting_result, $this->target_timezone);
            if ($sort_order == 'weekday_tinyint,start_time') {
                $service_meeting_result = $this->orderByWeekdayStart($service_meeting_result);
            }
        }
        if ($asm_query || $this->options['weekday_language'] != $this->options['asm_language']) {
            foreach ($service_meeting_result as &$value) {
                if ($this->asm_test($value, false)) {
                    $value = $this->enhance_meeting($value, $this->options['asm_language']);
                }
            }
        }
        if ($this->options['asm_sort_order']=='same') {
            $this->writeMeetings($service_meeting_result, $template, $this->options['asm_language'], 1, false);
            return;
        }
        $data = '';
        if (isset($this->options['asm_template_content']) && trim($this->options['asm_template_content'])) {
            $template = $this->options['asm_template_content'];
            $template = str_replace("&nbsp;", " ", $template);
            $analysedTemplate = $this->analyseTemplate($template);
            foreach ($service_meeting_result as &$value) {
                $data .= $this->write_single_meeting(
                    $value,
                    $template,
                    $analysedTemplate,
                    $this->get_area_name($value)
                );
            }
        } else {
            $data .= "<table style='line-height:".$line_height."; font-size:".$font_size."pt; width:100%;'>";
            foreach ($service_meeting_result as &$value) {
                $display_string = '<strong>'.$value['meeting_name'].'</strong>';
                if (!strstr($value['comments'], 'Open Position')) {
                    $display_string .= '<strong> - ' . $value['start_time'] . '</strong>';
                }

                if (trim($value['location_text'])) {
                    $display_string .= ' - '.trim($value['location_text']);
                }
                if (trim($value['location_street'])) {
                    $display_string .= ' - ' . trim($value['location_street']);
                }
                if (trim($value['location_city_subsection'])) {
                    $display_string .= ' ' . trim($value['location_city_subsection']);
                }
                if (trim($value['location_neighborhood'])) {
                    $display_string .= ' ' . trim($value['location_neighborhood']);
                }
                if (trim($value['location_municipality'])) {
                    $display_string .= ' '.trim($value['location_municipality']);
                }
                if (trim($value['location_province'])) {
                    //$display_string .= ' '.trim ( $value['location_province'] );
                }
                if (trim($value['location_postal_code_1'])) {
                    $display_string .= ' ' . trim($value['location_postal_code_1']);
                }
                if (trim($value['location_info'])) {
                    $display_string .= " (".trim($value['location_info']).")";
                }

                if (isset($value['email_contact']) && $value['email_contact'] != '' && $this->options['include_meeting_email'] == 1) {
                    $str = $this->parse_field($value['email_contact']);
                    $value['email_contact'] = $str;
                    $value['email_contact'] = ' (<i>'.$value['email_contact'].'</i>)';
                } else {
                    $value['email_contact'] = '';
                }
                $display_string .=  $value['email_contact'];
                $data .= "<tr><td style='border-bottom: 1px solid #555;'>".$display_string."</td></tr>";
            }
            $data .= "</table>";
        }
        $this->writeHTML($data);
    }
    private function parse_field($text)
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
    function toPersianNum($number)
    {
        $number = str_replace("1", "۱", $number);
        $number = str_replace("2", "۲", $number);
        $number = str_replace("3", "۳", $number);
        $number = str_replace("4", "۴", $number);
        $number = str_replace("5", "۵", $number);
        $number = str_replace("6", "۶", $number);
        $number = str_replace("7", "۷", $number);
        $number = str_replace("8", "۸", $number);
        $number = str_replace("9", "۹", $number);
        $number = str_replace("0", "۰", $number);
        return $number;
    }
    function adjust_timezone(&$meetings, $target_timezone)
    {
        if (!$target_timezone) {
            return;
        }
        $target_midnight = new DateTime();
        $target_midnight->setTimezone($target_timezone);
        $target_midnight->setTime(23, 59);
        $target_yesterday = new DateTime();
        $target_yesterday->setTimezone($target_timezone);
        $target_yesterday->setTime(0, 0);
        foreach ($meetings as &$meeting) {
            if (!empty($meeting['time_zone'])) {
                $meeting_time_zone = timezone_open($meeting['time_zone']);
                if ($meeting_time_zone) {
                    $date = date_create($meeting['start_time'], $meeting_time_zone);
                    date_timezone_set($date, $target_timezone);
                    $meeting['start_time'] = $date->format('H:i');
                    if ($date >= $target_midnight) {
                        $meeting['weekday_tinyint'] = $meeting['weekday_tinyint']+1;
                        if ($meeting['weekday_tinyint']==8) {
                            $meeting['weekday_tinyint'] = 1;
                        }
                    } elseif ($date < $target_yesterday) {
                        $meeting['weekday_tinyint'] = $meeting['weekday_tinyint']-1;
                        if ($meeting['weekday_tinyint']==0) {
                            $meeting['weekday_tinyint'] = 7;
                        }
                    }
                }
            }
        }
        usort($meetings, array($this, "sortDayTime"));
    }
}
