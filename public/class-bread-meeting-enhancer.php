<?php
if (! defined('ABSPATH')) {
    exit;
}
class Bread_Meeting_Enhancer
{
    private Bread $bread;
    private array $options;
    private array $areas;
    function __construct($bread, $areas)
    {
        $this->bread = $bread;
        $this->options = $bread->getOptions();
        $this->areas = $areas;
    }
    /**
     * Enhance the meeting fields (in place) with calculated values.
     *
     * @param array $meeting_value the raw meeting values, as returned from the BMLT root server.
     * @param string $lang The language used when generating format descriptions, etc.
     * @return void
     */
    public function enhance_meeting(&$meeting_value, $lang, $formatsManager, $formatStartTime = true)
    {
        if ($formatStartTime) {
            $duration = explode(':', $meeting_value['duration_time']);
            $minutes = intval($duration[0]) * 60 + intval($duration[1]) + intval($duration[2]);
            $meeting_value['duration_m'] = $minutes;
            $meeting_value['duration_h'] = rtrim(rtrim(number_format($minutes / 60, 2), 0), '.');
            $space = ' ';
            if ($this->options['remove_space'] == 1) {
                $space = '';
            }
            if ($this->options['time_clock'] == null || $this->options['time_clock'] == '12' || $this->options['time_option'] == '') {
                $time_format = "g:i" . $space . "A";
            } elseif ($this->options['time_clock'] == '24fr') {
                $time_format = "H\hi";
            } else {
                $time_format = "H:i";
            }
            $time_parts = [];
            if ($this->options['time_option'] == 1 || $this->options['time_option'] == '') {
                array_push($time_parts, $this->noon(gmdate($time_format, strtotime($meeting_value['start_time']))));
            } elseif ($this->options['time_option'] == '2') {
                $addtime = '+ ' . $minutes . ' minutes';
                array_push($time_parts, $this->noon(gmdate($time_format, strtotime($meeting_value['start_time']))));
                array_push($time_parts, $this->noon(gmdate($time_format, strtotime($meeting_value['start_time'] . ' ' . $addtime))));
            } elseif ($this->options['time_option'] == '3') {
                $time_array = array("1:00", "2:00", "3:00", "4:00", "5:00", "6:00", "7:00", "8:00", "9:00", "10:00", "11:00", "12:00");
                $temp_start_time = gmdate("g:i", strtotime($meeting_value['start_time']));
                $temp_start_time_2 = gmdate("g:iA", strtotime($meeting_value['start_time']));
                if ($temp_start_time_2 == '12:00PM') {
                    array_push($time_parts, 'NOON');
                } elseif (in_array($temp_start_time, $time_array)) {
                    array_push($time_parts, gmdate("g", strtotime($meeting_value['start_time'])));
                } else {
                    array_push($time_parts, gmdate("g:i", strtotime($meeting_value['start_time'])));
                }
                $addtime = '+ ' . $minutes . ' minutes';
                $temp_end_time = strtotime($meeting_value['start_time'] . ' ' . $addtime);
                $temp_end_time_2 = gmdate("g:i", strtotime($meeting_value['start_time'] . ' ' . $addtime));
                if ($temp_end_time == '12:00PM') {
                    array_push($time_parts, 'NOON');
                } elseif (in_array($temp_end_time_2, $time_array)) {
                    array_push($time_parts, gmdate("g" . $space . "A", $temp_end_time));
                } else {
                    array_push($time_parts, gmdate("g:i" . $space . "A", $temp_end_time));
                }
            }
            if (count($time_parts) == 1) {
                $meeting_value['start_time'] = ($lang == 'fa') ? $this->toPersianNum($time_parts[0]) : $time_parts[0];
            } elseif (count($time_parts) == 2) {
                $meeting_value['start_time'] = ($lang == 'fa')
                    ? $meeting_value['start_time'] = $this->toPersianNum($time_parts[1]) . $space . '-' . $space . $this->toPersianNum($time_parts[0])
                    : $meeting_value['start_time'] = $time_parts[0] . $space . '-' . $space . $time_parts[1];
            }
        }
        $meeting_value['day_abbr'] = $this->bread->getday($meeting_value['weekday_tinyint'], true, $lang);
        $meeting_value['day'] = $this->bread->getday($meeting_value['weekday_tinyint'], false, $lang);
        $area_name = $this->get_area_name($meeting_value);
        $meeting_value['area_name'] = $area_name;
        $meeting_value['area_i'] = substr($area_name, 0, 1);

        $meeting_value['wheelchair'] = '';
        $wheelchair_format = $formatsManager->getWheelchairFormat($this->options['weekday_language']);
        if (!is_null($wheelchair_format)) {
            $fmts = explode(',', $meeting_value['format_shared_id_list']);
            if (in_array($wheelchair_format['id'], $fmts)) {
                $meeting_value['wheelchair'] = '<img src="' . plugin_dir_url(__FILE__) . 'css/wheelchair.png" width="' . $this->options['wheelchair_size'] . '" height="' . $this->options['wheelchair_size'] . '">';
            }
        }
        // Extensions.
        return apply_filters("Bread_Enrich_Meeting_Data", $meeting_value, $formatsManager->getHashedFormats($lang));
    }
    private function noon($time)
    {
        if ($time == '12:00PM' || $time == '12:00 PM') {
            return 'NOON';
        }
        return $time;
    }
    private function get_area_name(array $meeting_value): string
    {
        foreach ($this->areas as $unique_area) {
            $area_data = explode(',', $unique_area);
            $area_id = $this->bread->arraySafeGet($area_data, 1);
            if ($area_id === $meeting_value['service_body_bigint']) {
                return $this->bread->arraySafeGet($area_data);
            }
        }
        return '';
    }

    private function toPersianNum($number)
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
        $number = str_replace("NOON", "ظهر", $number);
        $number = str_replace("AM", "صبح", $number);
        $number = str_replace("am", 'صبح', $number);
        $number = str_replace("PM", "بعدازظهر", $number);
        $number = str_replace("pm", "بعدازظهر", $number);
        return $number;
    }
}
