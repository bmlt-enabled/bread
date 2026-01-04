<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Controls how the meetingslist is structured into headings and possibly subheadings
 */
class Bread_Meetingslist_Structure
{
    /**
     * The CSS associated with the header
     *
     * @var string
     */
    private string $header_style;
    /**
     * The text to be added to the header if we have to go to a new page/column while continuing with the
     * old heading.  And at the top of the new page/column we want something line "Heading (continued)".
     *
     * @var string
     */
    private string $cont;
    /**
     * The meetingslist configuration.
     *
     * @var array
     */
    private array $options;
    /**
     * Index of the current heading.
     *
     * @var integer
     */
    private int $main_index = 0;
    /**
     * Index of the current sub-heading within the current heading.
     *
     * @var integer
     */
    private int $sub_index = 0;
    /**
     * Index of the current meeting within the current sub-heading.
     *
     * @var integer
     */
    private int $meeting_index = 0;
    /**
     * The result of structuring the meetings under their headings.  Array of order 3, first order: main heading; second order subheadings, third order: meetings.
     * The headings are not sorted.
     * @var array
     */
    private array $headerMeetings;
    /**
     * A sorted list of the main headings.
     *
     * @var array
     */
    private array $unique_heading;
    /**
     * The current main heading.  May contain [numbers] in the beginning, in case you don't want to sort alphebetically.  These number can be added to a "filter"
     * extension to enhance_meeting.  This was added for New Zealand, who wants their cities sorted north-south.
     *
     * @var string
     */
    private string $main_heading_raw;
    /**
     * The current sub heading.  May contain [numbers] in the beginning, in case you don't want to sort alphebetically.  These number can be added to a "filter"
     * extension to enhance_meeting.  This was added for New Zealand, who wants their cities sorted north-south.
     *
     * @var string
     */
    private string $sub_heading_raw;
    /**
     * Flag to indicate if we are printing the first meeting of a new main heading.
     *
     * @var boolean
     */
    private bool $newMainHeading;
    /**
     * Add some options that will help us structure the meeting list
     *
     * @param string $meeting_sort the type of ordering we want, e.g., by day, by city, by name.
     * @return void
     */
    private bool $suppress_heading;
    private Bread $bread;
    /**
     * Calculates some options that will be used to structure the meeting list and generate headers.
     *
     * @param string $meeting_sort
     * @return void
     */
    private function upgradeHeaderData(string $meeting_sort)
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
        if (!empty($this->options['subgrouping']) && $this->options['sub_header_shown'] == 'combined') {
            $this->options['combine_headings'] = 'main_grouping - subgrouping';
        }
    }
    /**
     * Setup for structuring the meeting list
     *
     * @param Bread $bread The configuration of the meeting list.
     * @param array $result_meetings The meetings in the meeting list.
     * @param string $lang The language of the meeting list
     * @param integer $include_additional_list Whether or not to include meetings that match the requirements of the additional list. Where
     * 0  -  let everything through
     * 1  -  only meetings with additional_list format
     * -1  -  only meetings without additional_list format
     */
    function __construct(Bread $bread, array $result_meetings, string $lang, int $include_additional_list)
    {
        $this->bread = $bread;
        $this->options = $bread->getOptions();
        $this->suppress_heading = $this->options['suppress_heading'] == 1;

        $meeting_sort = $this->options['meeting_sort'];
        if ($include_additional_list > 0) {
            switch ($this->options['additional_list_sort_order']) {
                case 'name':
                    $meeting_sort = 'group';
                    $this->suppress_heading = true;
                    break;
                case 'weekday_tinyint,start_time':
                    $meeting_sort = 'day';
                    $this->suppress_heading = true;
                    break;
                default:
                    break;
            }
        }
        $this->upgradeHeaderData($meeting_sort);

        $header_style = "color:" . $this->options['header_text_color'] . ";";
        $header_style .= "background-color:" . $this->options['header_background_color'] . ";";
        $header_style .= "font-size:" . $this->options['header_font_size'] . "pt;";
        $header_style .= "line-height:" . $this->options['content_line_height'] . ";";
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
        $this->header_style = $header_style;
        $this->cont = '(' . $bread->getTranslateTable()[$lang]['CONT'] . ')';

        $this->headerMeetings = $this->getHeaderMeetings($result_meetings, $include_additional_list);
        $this->unique_heading = $this->getUniqueHeadings($this->headerMeetings);
    }
    /**
     * Iterates over the main headings in the meeting list
     *
     * @return array the list of sub-headings under this heading.  If there are no sub-headings, an array with a single element is returned. At the end of the list, false is returned.
     */
    public function iterateMainHeading(): array|bool
    {
        if ($this->main_index >= count($this->unique_heading)) {
            return false;
        }
        $this->main_heading_raw = $this->unique_heading[$this->main_index++];
        if ($this->skip_heading($this->main_heading_raw)) {
            return $this->iterateMainHeading();
        }
        $unique_subheading = array_keys($this->headerMeetings[$this->main_heading_raw]);
        asort($unique_subheading, SORT_NATURAL | SORT_FLAG_CASE);
        $this->sub_index = 0;
        return array_values($unique_subheading);
    }
    /**
     * Iterates over the sub-headings in the current main heading.
     *
     * @param array $unique_subheading The list over which we are iterating.
     * @param boolean $still_new a flag to indicate we are looking for the first subheading (we may skip some sub headings)
     * @return array an array of the meetings in this subheading.  At the end of the list, false is returned.
     */
    public function iterateSubHeading(array $unique_subheading, bool $still_new = false): array|bool
    {
        if ($this->sub_index >= count($unique_subheading)) {
            return false;
        }
        $this->newMainHeading = ($this->sub_index == 0) || $still_new;
        $this->sub_heading_raw = $unique_subheading[$this->sub_index++];
        if ($this->skip_heading($this->sub_heading_raw)) {
            return $this->iterateSubHeading($unique_subheading, $this->newMainHeading);
        }
        $this->meeting_index = 0;
        return $this->headerMeetings[$this->main_heading_raw][$this->sub_heading_raw];
    }
    /**
     * Iterates over the meetings in the current sub-heading.
     *
     * @param array $meetings
     * @return array The next meeting.  At the end of the list, false is returned
     */
    public function iterateMeetings(array $meetings): array|bool
    {
        if ($this->meeting_index >= count($meetings)) {
            return false;
        }
        $this->newMainHeading = $this->newMainHeading && $this->meeting_index == 0;
        return $meetings[$this->meeting_index++];
    }
    /**
     * Does the work of structuring the meeting list into heading, subheadings and meetings.
     *
     * @param array $result_meetings The meetings returned from the BMLT root server query.
     * @param integer $include_additional_list Whether or not to include meetings that match the requirements of the additional list. Where
     * 0  -  let everything through
     * 1  -  only meetings with additional_list format
     * -1  -  only meetings without additional_list format
     * @return array rray, with header text as key and array of subheadings as values.  Each subheading is itself an array with the heading as key, and the meetings as values.
     */
    private function getHeaderMeetings(array &$result_meetings, int $include_additional_list): array
    {
        $levels = $this->getHeaderLevels();
        $headerMeetings = array();
        foreach ($result_meetings as &$value) {
            $additional_list_test = $this->additional_list_test($value, $include_additional_list == 1);
            if ((($include_additional_list < 0 && $additional_list_test) ||
                ($include_additional_list > 0 && !$additional_list_test))) {
                continue;
            }
            $main_grouping = $this->getHeaderItem($value, $this->setupDefaultHeading('main_'));
            if (!isset($headerMeetings[$main_grouping])) {
                $headerMeetings[$main_grouping] = array();
                if ($levels == 1) {
                    $headerMeetings[$main_grouping][0] = array();
                }
            }
            if ($levels == 2) {
                $subgrouping = $this->getHeaderItem($value, $this->setupDefaultHeading('sub'));
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
    /**
     * Sort the headings alphabetically.
     *
     * @param array $headerMeetings Array, with header text as key and array of subheadings as values.  Each subheading is itself an array with the heading as key, and the meetings as values.
     * @return array The sorted list.
     */
    public function getUniqueHeadings(array $headerMeetings): array
    {
        $unique_heading = array_keys($headerMeetings);
        asort($unique_heading, SORT_NATURAL | SORT_FLAG_CASE);
        return array_values($unique_heading);
    }
    /**
     * Main headings may contain [numbers] in the beginning, in case you don't want to sort alphebetically.  These number can be added to a "filter"
     * extension to enhance_meeting.  This was added for New Zealand, who wants their cities sorted north-south.  This gets the heading that we want to print.
     *
     * @param string $this_heading The raw heading
     * @return string The heading with the [number] removed.
     */
    private function remove_sort_key(string $this_heading): string
    {
        if (mb_substr($this_heading, 0, 1) == '[') {
            $end = strpos($this_heading, ']');
            if ($end > 0) {
                return trim(substr($this_heading, $end + 1));
            }
        }
        return $this_heading;
    }
    /**
     * If you want a heading to be skipped, the extension can add the sort key "[XXX]" to it.
     *
     * @param string $this_heading The raw heading
     * @return bool true if the heading should be skipped.
     */
    private function skip_heading(string $this_heading): bool
    {
        return (mb_substr($this_heading, 0, 5) == '[XXX]');
    }
    private function getHeaderLevels(): int
    {
        if (!empty($this->options['subgrouping'])) {
            return 2;
        }
        return 1;
    }
    private function setupDefaultHeading(string $level): array
    {
        return array(
            'name' =>  $level . 'grouping',
            'name_alt' => $level . 'grouping_alt',
            'name_suffix' => $level . 'grouping_suffix',
            'name_alt_suffix' => $level . 'grouping_alt_suffix',
        );
    }
    private function getHeaderItem(array $value, array $names): string
    {
        if (!$this->options[$names['name']]) {
            return '';
        }
        $grouping = '';
        $name = $this->options[$names['name']];
        $name_alt = (!empty($names['name_alt'])) && !empty($this->options[$names['name_alt']]) ? $this->options[$names['name_alt']] : '';
        if ($name == 'service_body_bigint') {
            foreach ($this->bread->bmlt()->get_areas() as $unique_area) {
                $area_data = explode(',', $unique_area);
                $area_name = Bread::arraySafeGet($area_data);
                $area_id = Bread::arraySafeGet($area_data, 1);
                if ($area_id === $value['service_body_bigint']) {
                    return $area_name;
                }
            }
            return 'Area not found';
        } elseif ($name == 'day') {
            $off = intval($this->options['weekday_start']);
            $day = intval($value['weekday_tinyint']);
            if ($day < $off) {
                $day = $day + 7;
            }
            return '[' . str_pad($day, 2, '0', STR_PAD_LEFT) . ']' . $value['day'];
        } elseif (isset($value[$name])) {
            $grouping = $this->bread->bmlt()->parse_field($value[$name]);
        }
        $suffix = $this->options[$names['name_suffix']] ?? '';
        if ($grouping == ''
            && !empty($name_alt)
            && isset($value[$name_alt])
        ) {
            $grouping = $this->bread->bmlt()->parse_field($value[$name_alt]);
            $suffix = $this->options[$names['name_alt_suffix']] ?? '';
        }
        if (strlen(trim($grouping)) == 0) {
            return 'NO DATA';
        }
        if (!empty($suffix)) {
            return $grouping . ' ' . $suffix;
        }
        return $grouping;
    }
    /**
     * Does the meeting belong in the additional list.
     *
     * @param array $value the meeting.
     * @param boolean $flag true if we are generating the additonal list.
     * The logic says meetings belong in main list iff they don't belong in additional list,
     * but when the additional list is virtual, we want hybrid meetings in the main list, too.
     * So, wierdly, when generating the main list, we want to say hybrid meetings are NOT in the
     * additional list.
     * @return boolean true if the meeting belongs in the addional list.
     */
    private function additional_list_test(array $value, $flag = false): bool
    {
        if (empty($this->options['additional_list_format_key'])) {
            return false;
        }
        $format_key = $this->options['additional_list_format_key'];
        if ($format_key == "@Virtual@") {
            //TODO: Is this correct?  For now, I'm just refactoring, so leaving it in.
            if (!$flag && $this->isHybrid($value)) {
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
    private function isHybrid(array $value): bool
    {
        if ($value['venue_type'] == 3) {
            return true;
        }
        if (empty($value['formats'])) {
            return false;
        }
        $enFormats = explode(",", $value['formats']);
        return in_array('HY', $enFormats);
    }
    private function isVirtual(array $value): bool
    {
        if ($value['venue_type'] == 2) {
            return true;
        }
        if (empty($value['formats'])) {
            return false;
        }
        $enFormats = explode(",", $value['formats']);
        return in_array('VM', $enFormats);
    }
    /**
     * Gets the heading for the current meeting, based on where we've iterated to in the Heading array
     *
     * @return string the current heading, if needed, as HTML/CSS.  Or an empty string, if not.
     */
    public function calculateHeading(): string
    {
        $header = '';
        if ($this->suppress_heading) {
            return $header;
        }
        $this_heading = $this->remove_sort_key($this->main_heading_raw);
        $this_subheading = $this->remove_sort_key($this->sub_heading_raw);
        if (($this->meeting_index == 1) && !empty($this->options['combine_headings'])) {
            $header_string =  $this->options['combine_headings'];
            $header_string =  str_replace('main_grouping', $this_heading, $header_string);
            $header_string =  str_replace('subgrouping', $this_subheading, $header_string);
            $header .= "<div style='" . $this->header_style . "'>" . $header_string . "</div>";
        } elseif (!empty($this->options['subgrouping'])) {
            if ($this->newMainHeading) {
                $xtraMargin = '';
                if (!$this->main_index > 1 or $this->meeting_index > 1) {
                    $xtraMargin = 'margin-top:2pt;';
                }
                $header .= '<div style="' . $this->header_style . $xtraMargin . '">' . $this_heading . "</div>";
            }
            if (($this->meeting_index == 1) && $this->options['sub_header_shown'] == 'display') {
                $header .= "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>" . $this_subheading . "</p>";
            }
        } elseif ($this->newMainHeading) {
            $header .= '<div style="' . $this->header_style . '">' . $this_heading . "</div>";
        }
        return $header;
    }
    /**
     * When moving between pages/columns, get an appropriate "Continued" HTML.
     *
     * @return string the header HTML.
     */
    public function calculateContHeader(): string
    {
        $header = '';
        $cont = '';
        if ($this->suppress_heading) {
            return $header;
        }
        if (!$this->options['cont_header_shown']) {
            return $header;
        }
        if (!empty($this->options['combine_headings'])) {
            if (!$this->newMainHeading && $this->meeting_index == 1) {
                $cont = $this->cont;
            }
            $header_string =  $this->options['combine_headings'];
            $header_string =  str_replace('main_grouping', $this->remove_sort_key($this->main_heading_raw), $header_string);
            $header_string =  str_replace('subgrouping', $this->remove_sort_key($this->sub_heading_raw), $header_string);
            $header .= "<div style='" . $this->header_style . "'>" . $header_string . $cont . "</div>";
            return $header;
        } else if (!$this->newMainHeading) {
            $cont = $this->cont;
        }
        $header = "<div style='" . $this->header_style . "'>" . $this->remove_sort_key($this->main_heading_raw) . " " . $cont . "</div>";

        return $header;
    }
}
