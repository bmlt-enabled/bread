<?php

use Mpdf\Mpdf;
use function DeepCopy\deep_copy;

/**
 * Writes the contents of the meeting list as PDF.
 *
 * @link  https://bmlt.app
 * @since 2.8.0
 *
 * @package    Bread
 * @subpackage Bread/public
 * @author     bmlt-enabled <help@bmlt.app>
 */
class Bread_ContentGenerator
{
    /**
     * Converts HTML to PDF.
     *
     * @var Mpdf
     */
    private Mpdf $mpdf;
    /**
     * The configuration of the meeting list.
     *
     * @var array
     */
    private array $options;
    /**
     * The meetings in the meeting list.
     *
     * @var array
     */
    private array $result_meetings;
    /**
     * The standard shortcodes, not including fields in the array representing the meeting.  Used on front and last pages, etc.
     *
     * @var array
     */
    private array $shortcodes;
    private int $meeting_count;
    private $target_timezone;
    /**
     * Convenient API for dealing with formats.
     *
     * @var Bread_FormatsManager
     */
    private Bread_FormatsManager $formatsManager;
    /**
     * Helper class that enriches the meeting with calculated values.
     *
     * @var Bread_Meeting_Enhancer
     */
    private Bread_Meeting_Enhancer $meetingEnhancer;
    /**
     * Usually, the key fieled in the array representing the meeting is used to insert a value into a template.  But we also have these convenience names.
     *
     * @var array
     */
    private array $legacy_synonyms = array(
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
    /**
     * The constuctor sets things up so that we are ready to generate.
     *
     * @param Mpdf $mpdf The object that converts HTML to PDF.
     * @param array $options The configuration of the meeting list.
     * @param array $result_meetings The meetings to be included in the list.
     * @param Bread_FormatsManager $formatsManager
     */
    function __construct(object $mpdf, array $options, array $result_meetings, Bread_FormatsManager $formatsManager)
    {
        $this->mpdf = $mpdf;
        $this->options = $options;
        $this->result_meetings = $this->orderByWeekdayStart($result_meetings);
        $this->formatsManager = $formatsManager;
        if (isset($_GET['time_zone'])) {
            $this->target_timezone = timezone_open($_GET['time_zone']);
        }
        $this->meeting_count = count($result_meetings);
        $this->shortcodes = array(
            '<h2>'                          => '<h2 style="font-size:' . $this->options['front_page_font_size'] . 'pt!important;">',
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
        $this->shortcodes = apply_filters("Bread_Section_Shortcodes", $this->shortcodes, Bread_Bmlt::get_areas(), $formatsManager->getFormatsUsed());
        if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
            $this->mpdf->DefHTMLFooterByName('MyFooter', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">' . $this->options['nonmeeting_footer'] . '</div>');
            $this->mpdf->DefHTMLFooterByName('_default', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">' . $this->options['nonmeeting_footer'] . '</div>');
            $this->mpdf->DefHTMLFooterByName('Meeting1Footer', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">' . $this->options['meeting1_footer'] . '</div>');
            $this->mpdf->DefHTMLFooterByName('Meeting2Footer', '<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">' . $this->options['meeting2_footer'] . '</div>');
        }
        if (!empty($this->options['pageheader_content'])) {
            $data = $this->standard_shortcode_replacement('pageheader_content');
            $header_style = "vertical-align: top; text-align: center; font-weight: bold;margin-top:3px;margin-bottom:3px;";
            $header_style .= "color:" . $this->options['pageheader_textcolor'] . ";";
            $header_style .= "background-color:" . $this->options['pageheader_backgroundcolor'] . ";";
            $header_style .= "font-size:" . $this->options['pageheader_fontsize'] . "pt;";
            $header_style .= "line-height:" . $this->options['content_line_height'] . ";";

            $this->mpdf->SetHTMLHeader(
                '<div style="' . $header_style . '">' . $data . '</div>',
                'O'
            );
        }
        if (!empty($this->options['watermark'])) {
            $this->mpdf->SetWatermarkImage($this->options['watermark'], 0.2, 'F');
            $this->mpdf->showWatermarkImage = true;
        }
    }
    /**
     * Generates the contents of the meeting list.
     *
     * @param int $num_columns The number of columns in the meeting list.
     * @return void
     */
    public function generate(int $num_columns): void
    {
        require_once __DIR__ . '/class-bread-meetingslist-structure.php';
        require_once __DIR__ . '/class-bread-meeting-enhancer.php';
        $this->mpdf->SetColumns($num_columns, '', $this->options['column_gap']);
        if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
            $this->write_front_page();
        }
        $this->mpdf->WriteHTML('td{font-size: ' . $this->options['content_font_size'] . "pt;line-height:" . $this->options['content_line_height'] . ';background-color:#ffffff00;}', 1);
        $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['content_font_size'] . 'pt');
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['content_line_height']);
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
            $this->WriteHTML('<sethtmlpagefooter name="Meeting1Footer" page="ALL" />');
        }
        $lang = $this->options['weekday_language'];
        $this->meetingEnhancer = new Bread_Meeting_Enhancer($this->options, Bread_Bmlt::get_areas());
        foreach ($this->result_meetings as &$value) {
            $value = $this->meetingEnhancer->enhance_meeting($value, $lang, $this->formatsManager);
        }
        $meetingslistStructure = new Bread_Meetingslist_Structure($this->options, $this->result_meetings, $lang, $this->options['include_additional_list'] == 0 ? -1 : 0);
        $this->writeMeetings($this->options['meeting_template_content'], $meetingslistStructure);

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
    /**
     * Writes a HTML string to th PDF.
     *
     * @param string $str The string to be written.
     * @return void
     */
    private function writeHTML(string $str): void
    {
        //$str = htmlentities($str);
        @$this->mpdf->WriteHTML(wpautop(stripslashes($str)));
    }
    /**
     * Replace the shortcodes with the standard values (not meeting dependent).
     *
     * @param string $page The text containing the shortcodes.
     * @return string
     */
    private function standard_shortcode_replacement(string $page): string
    {
        $search_strings = array();
        $replacements = array();
        foreach ($this->shortcodes as $key => $value) {
            $search_strings[] = $key;
            $replacements[] = $value;
        }

        $search_strings[] = '[meeting_count]';
        $replacements[] =  $this->meeting_count;
        $data = $this->options[$page];
        $data = $this->locale_month_replacement($data, 'lower');
        $data = $this->locale_month_replacement($data, 'upper');
        $data = str_replace($search_strings, $replacements, $data);
        $this->replace_format_shortcodes($data, $page);
        $data = str_replace("[date]", strtoupper(date("F Y")), $data);
        if ($this->target_timezone) {
            $data = str_replace('[timezone]', $this->target_timezone->getName(), $data);
        }
        return $data;
    }
    /**
     * Replace [month] shortcodes with the locale-specific name of the month.
     *
     * @param string $data The text containing the shortcode.
     * @param string $case 'upper' or 'lower'
     * @return string The text with the shortcode replaced by the value.
     */
    private function locale_month_replacement(string $data, string $case): string
    {
        $strpos = strpos($data, "[month_$case" . "_");
        if ($strpos !== false) {
            $locLang = substr($data, $strpos + 13, 2);
            if (!isset($this->translate[$locLang])) {
                $locLang = 'en';
            }
            $fmt = new IntlDateFormatter(
                Bread::getTranslateTable()[$locLang]['LOCALE'],
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL
            );
            $fmt->setPattern('LLLL');
            $month = ucfirst(mb_convert_encoding($fmt->format(time()), 'UTF-8', 'ISO-8859-1'));
            if ($case == 'upper') {
                $month = mb_strtoupper($month, 'UTF-8');
            }
            return substr_replace($data, $month, $strpos, 16);
        }
        return $data;
    }
    /**
     * Order the meetings  so that the list starts on the day of the week selected in the configuation.
     *
     * @param array $result_meetings The meetings
     * @return array The meetings.
     */
    private function orderByWeekdayStart(array &$result_meetings): array
    {
        $days = array_column($result_meetings, 'weekday_tinyint');
        $today_str = $this->options['weekday_start'];
        return array_merge(
            array_splice($result_meetings, array_search($today_str, $days)),
            array_splice($result_meetings, 0)
        );
    }
    /**
     * Generate the front page.
     *
     * @return void
     */
    private function write_front_page()
    {
        $this->mpdf->WriteHTML('td{font-size: ' . $this->options['front_page_font_size'] . "pt;line-height:" . $this->options['front_page_line_height'] . ';}', 1);
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['front_page_line_height']);
        $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['front_page_font_size'] . 'pt');
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        $this->options['front_page_content'] = wp_unslash($this->options['front_page_content']);
        $data = $this->standard_shortcode_replacement('front_page_content');

        $querystring_custom_items = array();
        preg_match_all('/(\[querystring_custom_\d+\])/', $this->options['front_page_content'], $querystring_custom_items);
        foreach ($querystring_custom_items[0] as $querystring_custom_item) {
            $mod_qs_ci = str_replace("]", "", str_replace("[", "", $querystring_custom_item));
            $data = str_replace($querystring_custom_item, (isset($_GET[$mod_qs_ci]) ? $_GET[$mod_qs_ci] : "NOT SET"), $data);
        }
        $this->writeHTMLwithAdditionalMeetinglist($data);
        $this->mpdf->showWatermarkImage = false;
    }
    /**
     * Generate the last page.
     *
     * @return void
     */
    private function write_last_page()
    {
        $this->mpdf->WriteHTML('td{font-size: ' . $this->options['last_page_font_size'] . "pt;line-height:" . $this->options['last_page_line_height'] . ';}', 1);
        $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['last_page_font_size'] . 'pt');
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['last_page_line_height']);
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        $data = $this->standard_shortcode_replacement('last_page_content');
        $this->writeHTMLwithAdditionalMeetinglist($data);
    }
    /**
     * Generate the custom section.
     *
     * @return void
     */
    private function write_custom_section()
    {
        $this->mpdf->SetHTMLHeader();
        if (isset($this->options['pageheader_content']) && trim($this->options['pageheader_content'])) {
            $this->mpdf->SetTopMargin($this->options['margin_header']);
        }
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['custom_section_line_height']);
        $this->mpdf->SetDefaultBodyCSS('font-size', $this->options['custom_section_font_size'] . 'pt');
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        $data = $this->standard_shortcode_replacement('custom_section_content');
        $this->mpdf->WriteHTML('td{font-size: ' . $this->options['custom_section_font_size'] . "pt;line-height:" . $this->options['custom_section_line_height'] . ';}', 1);
        $this->writeHTMLwithAdditionalMeetinglist($data);
    }
    /**
     * Generate the meeting list itself, using the specified template and the meetings as structed by the heading manager.
     *
     * @param string $template
     * @param Bread_Meetingslist_Structure $meetingslistStructure
     * @return void
     */
    private function writeMeetings(string $template, Bread_Meetingslist_Structure $meetingslistStructure): void
    {
        $template = wpautop(stripslashes($template));
        $template = preg_replace('/[[:^print:]]/', ' ', $template);

        $template = str_replace("&nbsp;", " ", $template);
        $analysedTemplate = $this->analyseTemplate($template);

        /***
         * You might be wondering why I am not using keep-with-table...
         * The problem is, keep with table doesn't work with columns, only pages.
         * We want to check that a header and at least one meeting fits, so we write it
         * to a test PDF, see how big it is, and check if it will fit.
         */
        $test_pages = deep_copy($this->mpdf);
        while ($subheadings = $meetingslistStructure->iterateMainHeading()) {
            while ($meetings = $meetingslistStructure->iterateSubHeading($subheadings)) {
                while ($meeting_value = $meetingslistStructure->iterateMeetings($meetings)) {
                    $header = $meetingslistStructure->calculateHeading();
                    $data = $this->write_single_meeting(
                        $meeting_value,
                        $template,
                        $analysedTemplate
                    );
                    $this->writeBreak($test_pages);
                    $y_startpos = $test_pages->y;
                    @$test_pages->WriteHTML($header . $data);
                    $y_diff = $test_pages->y - $y_startpos;
                    if ($y_diff >= $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height) {
                        $this->writeBreak($this->mpdf);
                        if (empty($header)) {
                            $header = $meetingslistStructure->calculateContHeader();
                        }
                    }
                    $this->WriteHTML($header . $data);
                }
            }
        }
    }
    /**
     * Write a break between meetings, so that the meeting does not get broken up in the printout.
     *   In booklets, a page break.  In fliers, a column break.
     *
     * @param Mpdf $mpdf
     * @return void
     */
    private function writeBreak(Mpdf $mpdf)
    {
        if ($this->options['page_fold'] === 'half' || $this->options['page_fold'] === 'full') {
            $mpdf->WriteHTML("<pagebreak>");
        } else {
            $mpdf->WriteHTML("<columnbreak />");
        }
    }
    /**
     * Break up the template into chunks, so that it can be efficiently processed/ filled with values from the meeting.
     *
     * @param string $template
     * @return array chunks to be used by the write_single_meeting method.
     */
    private function analyseTemplate(string $template): array
    {
        $arr = preg_split('/\W+/', $template, 0, PREG_SPLIT_OFFSET_CAPTURE);
        $arr = array_reverse($arr, true);
        $ret = array();
        foreach ($arr as $item) {
            if (strlen($item[0]) < 3) {
                continue;
            }
            $htmlTags = array('table', 'tbody', 'strong', 'left', 'right', 'top', 'bottom', 'center', 'align', 'font', 'size', 'text', 'style', 'family', 'vertical', 'color', 'QRCode');
            if (in_array($item[0], $htmlTags)) {
                continue;
            }
            if ($item[1] > 0 && $template[$item[1] - 1] == '['
                && $template[$item[1] + strlen($item[0])] == ']'
            ) {
                $item[0] = '[' . $item[0] . ']';
                $item[1] = $item[1] - 1;
                $item[2] = true;
            } else {
                $item[2] = false;
            }
            $ret[] = $item;
        }
        return $ret;
    }

    /**
     * Write a single meeting to the PDF
     *
     * @param array $meeting_value The meeting data.
     * @param string $template
     * @param array $analysedTemplate
     * @return void
     */
    private function write_single_meeting(array $meeting_value, string $template, array $analysedTemplate)
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
                $name = substr($name, 1, strlen($name) - 2);
            }
            if (isset($namedValues[$name])) {
                $data = substr_replace($data, $namedValues[$name], $item[1], strlen($item[0]));
            }
        }
        $qr_pos = strpos($data, "[QRCode");
        if ($qr_pos) {
            $qr_end = strpos($data, ']', $qr_pos);
            $data = substr($data, 0, $qr_pos) .
                '<barcode type="QR" disableborder="1" ' .
                substr($data, $qr_pos + 8, $qr_end - $qr_pos - 8) .
                '/>' .
                substr($data, $qr_end + 1);
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
    /**
     * Generate the PDF of templates that also contain "additional lists" and format tables.
     *
     * @param string $data The template.
     * @return void
     */
    private function writeHTMLwithAdditionalMeetinglist(string $data)
    {
        $strs = array(
            '<p>[additional_meetinglist]</p>',
            '[additional_meetinglist]',
            '<p>[service_meetings]</p>',
            '[service_meetings]'
        );

        foreach ($strs as $str) {
            $pos = strpos($data, $str);
            if (!$pos) {
                continue;
            }
            if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                $this->WriteHTML('<sethtmlpagefooter name="Meeting2Footer" page="ALL" />');
            }
            $this->WriteHTML(substr($data, 0, $pos));
            $this->write_additional_meetinglist();
            if ($this->options['page_fold'] == 'half' || $this->options['page_fold'] == 'full') {
                $this->WriteHTML('<sethtmlpagefooter name="MyFooter" page="ALL" />');
            }
            $this->WriteHTML(substr($data, $pos + strlen($str)));
            return;
        }
        $this->WriteHTML($data);
    }

    private function replace_format_shortcodes(&$data, $page_name)
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
    private function shortcode_formats($shortcode, $detailed, $lang, $isAll, $page, &$str)
    {
        $pos = strpos($str, $shortcode);
        if ($pos == false) {
            return;
        }
        $value = '';
        if ($detailed) {
            $value = $this->formatsManager->write_detailed_formats($lang, $isAll, $this->options[$page . '_line_height'], $this->options[$page . '_font_size'] . "pt");
        } else {
            $value = $this->formatsManager->write_formats($lang, $isAll, $this->options[$page . '_line_height'], $this->options[$page . '_font_size'] . "pt");
        }
        $str = substr($str, 0, $pos) . $value . substr($str, $pos + strlen($shortcode));
    }
    private function get_field(array $obj, string $field): mixed
    {
        $value = '';
        if (isset($obj[$field])) {
            $value = Bread_Bmlt::parse_field($obj[$field]);
        }
        return $value;
    }
    function write_additional_meetinglist()
    {
        if (isset($this->options['additional_list_template_content']) && trim($this->options['additional_list_template_content'])) {
            $template = $this->options['additional_list_template_content'];
        } else {
            $template = $this->options['meeting_template_content'];
        }
        $additional_list_query = false;
        $additional_meetinglist_result = $this->result_meetings;
        if (empty($this->options['additional_list_format_key']) || $this->options['additional_list_format_key'] == 'additional_list') {
            $additional_list_query = true;
            $sort_order = $this->options['additional_list_sort_order'];
            if ($sort_order == 'same') {
                $sort_order = 'weekday_tinyint,start_time';
            }
            $additional_list_id = "";
            if ($this->options['additional_list_format_key'] === 'additional_list') {
                $additional_list_id = '&formats[]=' . $this->formatsManager->getFormatByKey($this->options['weekday_language'], 'additional_list');
            }
            $services = Bread_Bmlt::generateDefaultQuery();
            if (!empty($this->options['additional_list_custom_query'])) {
                $services = $this->options['additional_list_custom_query'];
            }
            $additional_list_query = "client_interface/json/?switcher=GetSearchResults$services$additional_list_id&sort_keys=$sort_order";
            // additional_list can contain E-Mail and phone numbers that require logins.
            if ($this->options['additional_list_format_key'] === 'additional_list') {
                $additional_list_query .= "&advanced_published=0";
            }
            $results = Bread_Bmlt::get_configured_root_server_request($additional_list_query);
            $additional_meetinglist_result = json_decode(wp_remote_retrieve_body($results), true);
            $this->adjust_timezone($additional_meetinglist_result, $this->target_timezone);
        }
        if ($additional_list_query || $this->options['weekday_language'] != $this->options['additional_list_language']) {
            foreach ($additional_meetinglist_result as &$value) {
                $value = $this->meetingEnhancer->enhance_meeting($value, $this->options['additional_list_language'], $this->formatsManager);
            }
        }
        $meetingslistStructure = new Bread_Meetingslist_Structure($this->options, $additional_meetinglist_result, $this->options['additional_list_language'], 1);
        $this->writeMeetings($template, $meetingslistStructure);
        return;
    }

    private function adjust_timezone(&$meetings, $target_timezone)
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
                        $meeting['weekday_tinyint'] = $meeting['weekday_tinyint'] + 1;
                        if ($meeting['weekday_tinyint'] == 8) {
                            $meeting['weekday_tinyint'] = 1;
                        }
                    } elseif ($date < $target_yesterday) {
                        $meeting['weekday_tinyint'] = $meeting['weekday_tinyint'] - 1;
                        if ($meeting['weekday_tinyint'] == 0) {
                            $meeting['weekday_tinyint'] = 7;
                        }
                    }
                }
            }
        }
        usort($meetings, array($this, "sortDayTime"));
    }
}
