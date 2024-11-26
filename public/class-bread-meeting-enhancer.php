<?php
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
    public function enhance_meeting(&$meeting_value, $lang, $formatsManager)
    {
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
        if ($this->options['time_option'] == 1 || $this->options['time_option'] == '') {
            $meeting_value['start_time'] = date($time_format, strtotime($meeting_value['start_time']));
            if ($meeting_value['start_time'] == '12:00PM' || $meeting_value['start_time'] == '12:00 PM') {
                $meeting_value['start_time'] = 'NOON';
            }
        } elseif ($this->options['time_option'] == '2') {
            $addtime = '+ ' . $minutes . ' minutes';
            $end_time = date($time_format, strtotime($meeting_value['start_time'] . ' ' . $addtime));
            $meeting_value['start_time'] = date($time_format, strtotime($meeting_value['start_time']));
            if ($lang == 'fa') {
                $meeting_value['start_time'] = $this->toPersianNum($end_time) . $space . '-' . $space . $this->toPersianNum($meeting_value['start_time']);
            } else {
                $meeting_value['start_time'] = $meeting_value['start_time'] . $space . '-' . $space . $end_time;
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
                $end_time = date("g" . $space . "A", strtotime($temp_end_time));
            } else {
                $end_time = date("g:i" . $space . "A", strtotime($temp_end_time));
            }
            $meeting_value['start_time'] = $start_time . $space . '-' . $space . $end_time;
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
                $meeting_value['wheelchair'] = '<img src="' . plugin_dir_url(__FILE__) . 'public/css/wheelchair.png" width="' . $this->options['wheelchair_size'] . '" height="' . $this->options['wheelchair_size'] . '">';
            }
        }
        // Extensions.
        return apply_filters("Bread_Enrich_Meeting_Data", $meeting_value, $formatsManager->getHashedFormats($lang));
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
        return $number;
    }
}
