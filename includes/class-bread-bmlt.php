<?php
class Bread_Bmlt
{
    public $connection_error;
    private $bmlt_server_lang = '';
    private array $unique_areas;
    private Bread $bread;

    function __construct($bread)
    {
        $this->bread = $bread;
    }

    public function get_configured_root_server_request($url, $raw = false)
    {
        $results = $this->bread->bmlt()->get($this->bread->getOption('root_server') . "/" . $url);
        if ($raw) {
            return $results;
        }
        return json_decode(wp_remote_retrieve_body($results), true);
    }
    public function get_formats_by_language(string $lang)
    {
        return $this->bread->bmlt()->get_configured_root_server_request("client_interface/json/?switcher=GetFormats&lang_enum=$lang");
    }
    /**
     * Undocumented function
     *
     * @param string $url The BMLT call.
     * @return WP_Error | array The result of the call.
     */
    private function get(string $url): WP_Error | array
    {
        $args = array(
            'timeout' => '120',
        );
        if ($this->bread->getOption('user_agent') != 'None') {
            $args['headers'] = array(
                'User-Agent' => $this->bread->getOption('user_agent')
            );
        }
        if ($this->bread->getOption('sslverify') == '1') {
            $args['sslverify'] = false;
        }
        return wp_remote_get($url, $args);
    }
    /**
     * Gets all the meetins in the root server as an array with id=>string.  Used to select extra meetings.
     *
     * @return array
     */
    public function get_all_meetings(): array
    {
        $lang = $this->bread->bmlt()->get_bmlt_server_lang();
        $result = $this->bread->bmlt()->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults&data_field_key=weekday_tinyint,start_time,service_body_bigint,id_bigint,meeting_name,location_text,email_contact&sort_keys=meeting_name,service_body_bigint,weekday_tinyint,start_time");

        $unique_areas = $this->bread->bmlt()->get_areas();
        $all_meetings = array();
        foreach ($result as $value) {
            foreach ($unique_areas as $unique_area) {
                $area_data = explode(',', $unique_area);
                $area_id = $this->bread->arraySafeGet($area_data, 1);
                if ($area_id === $value['service_body_bigint']) {
                    $area_name = $this->bread->arraySafeGet($area_data);
                }
            }

            $value['start_time'] = gmdate("g:iA", strtotime($value['start_time']));
            $all_meetings[$value['id_bigint']] = wp_strip_all_tags($value['meeting_name'] . ' - ' . $this->bread->getday($value['weekday_tinyint'], true, $lang) . ' ' . $value['start_time'] . ' in ' . $area_name . ' at ' . $value['location_text']);
        }

        return $all_meetings;
    }
    public function get_fieldkeys()
    {
        $ret = $this->bread->bmlt()->get_configured_root_server_request("client_interface/json/?switcher=GetFieldKeys");
        return is_null($ret) ? array() : $ret;
    }
    private $standard_keys = array(
        "id_bigint",
        "worldid_mixed",
        "service_body_bigint",
        "weekday_tinyint",
        "start_time",
        "duration_time",
        "formats",
        "lang_enum",
        "longitude",
        "latitude",
        "meeting_name" . "location_text",
        "location_info",
        "location_street",
        "location_city_subsection",
        "location_neighborhood",
        "location_municipality",
        "location_sub_province",
        "location_province",
        "location_postal_code_1",
        "location_nation",
        "comments",
        "zone"
    );
    public function get_nonstandard_fieldkeys()
    {
        $all_fks = $this->bread->bmlt()->get_fieldkeys();
        $ret = array();
        foreach ($all_fks as $fk) {
            if (!in_array($fk['key'], $this->standard_keys)) {
                $ret[] = $fk;
            }
        }
        $ext_fields = apply_filters("Bread_Enrich_Meeting_Data", array(), array());
        foreach ($ext_fields as $key => $value) {
            $ret[] = array("key" => $key, "description" => $key);
        }
        return $ret;
    }
    /**
     * Generates a list of service bodies to be used in the admin UI's drop downs.
     *
     * @return array the service bodies.
     */
    public function get_areas()
    {
        if (!empty($this->unique_areas)) {
            return $this->unique_areas;
        }
        $result = $this->bread->bmlt()->get_configured_root_server_request("client_interface/json/?switcher=GetServiceBodies");
        $this->unique_areas = array();

        foreach ($result as $value) {
            $parent_name = 'Parent ID';
            foreach ($result as $parent) {
                if ($value['parent_id'] == $parent['id']) {
                    $parent_name = $parent['name'];
                }
            }
            if ($value['parent_id'] == '') {
                $value['parent_id'] = '0';
            }
            $this->unique_areas[] = $value['name'] . ',' . $value['id'] . ',' . $value['parent_id'] . ',' . $parent_name;
        }

        return $this->unique_areas;
    }
    /**
     * Gets the default language of the root server.
     *
     * @return string 2 character string ISO standard for the language.
     */
    public function get_bmlt_server_lang(): string
    {
        if ($this->bmlt_server_lang == '') {
            $result = $this->bread->bmlt()->testRootServer();
            if (!($result && is_array($result) && is_array($result[0]))) {
                return 'en';
            }
            $this->bmlt_server_lang = ($result == null) ? 'en' : $result["0"]["nativeLang"];
        }
        return $this->bmlt_server_lang;
    }
    /**
     * Check if this is a valid BMLT server.
     *
     * @param  $override_root_server
     * @return array the results of GetServerInfo
     */
    public function testRootServer(string $override_root_server = null): array|bool
    {
        if ($override_root_server == null) {
            $results = $this->bread->bmlt()->get_configured_root_server_request("client_interface/json/?switcher=GetServerInfo", true);
        } else {
            $results = $this->bread->bmlt()->get($override_root_server . "/client_interface/json/?switcher=GetServerInfo");
        }
        if ($results instanceof WP_Error) {
            $this->connection_error = $results->get_error_message();
            return false;
        }
        $httpcode = wp_remote_retrieve_response_code($results);
        if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
            $this->connection_error = "HTTP Return Code: " . $httpcode;
            return false;
        }

        return json_decode(wp_remote_retrieve_body($results), true);
    }
    /**
     * This is used from the AdminUI, not to generate the meeting list.
     *
     * @param boolean $all should we get all the formats defined in the root server, or only those used in the service body.  This respects the option recurse_service_bodies but only the first service body.
     * @return array the formats
     */
    public function getFormatsForSelect(bool $all = false): array
    {
        if ($all) {
            $results = $this->bread->bmlt()->get_configured_root_server_request("client_interface/json/?switcher=GetFormats");
            $this->bread->bmlt()->sortBySubkey($results, 'key_string');
            return $results;
        }
        $area_data = explode(',', $this->bread->getOption('service_body_1'));
        $service_body_id = $this->bread->arraySafeGet($area_data, 1);
        if ($this->bread->getOption('recurse_service_bodies') == 1) {
            $services = '&recursive=1&services[]=' . $service_body_id;
        } else {
            $services = '&services[]=' . $service_body_id;
        }
        if (empty($service_body_id)) {
            $queryUrl = "client_interface/json/?switcher=GetFormats";
        } else {
            $queryUrl = "client_interface/json/?switcher=GetSearchResults$services&get_formats_only";
        }
        $results = $this->bread->bmlt()->get_configured_root_server_request($queryUrl);
        $results = empty($service_body_id) ? $results : $results['formats'];
        $this->sortBySubkey($results, 'key_string');
        return $results;
    }
    /**
     * Convenient front end to array_multisort.  Sorts the array in place.
     *
     * @param array $array The array to be sorted.
     * @param string $subkey The key to be sorted by.
     * @param [type] $sortType SORT_ASC (default) or SORT_DESC
     * @return void
     */
    public function sortBySubkey(array &$array, string $subkey, int $sortType = SORT_ASC): void
    {
        if (empty($array)) {
            return;
        }
        foreach ($array as $subarray) {
            $keys[] = $subarray[$subkey];
        }
        array_multisort($keys, $sortType, $array);
    }
    private $default_query = false;
    /**
     * Generate that part of the BMLT query-string that reflects the service bodies being queried.
     *
     * @return string Something to paste into the URL
     */
    public function generateDefaultQuery(): string
    {
        // addServiceBody has the side effect that
        // the service body option is overridden, so that it contains
        // only the name of the service body.  So we cache the value so it only
        // needs to be called once.
        if (!$this->default_query) {
            $this->default_query = $this->addServiceBody('service_body_1');
            $this->default_query .= $this->addServiceBody('service_body_2');
            $this->default_query .= $this->addServiceBody('service_body_3');
            $this->default_query .= $this->addServiceBody('service_body_4');
            $this->default_query .= $this->addServiceBody('service_body_5');
        }
        return $this->default_query;
    }
    private function addServiceBody($service_body_name)
    {
        if (false === ($this->bread->getOption($service_body_name) == 'Not Used')) {
            $area_data = explode(',', $this->bread->getOption($service_body_name));
            $area = $this->bread->arraySafeGet($area_data);
            $this->bread->setOption($service_body_name, ($area == 'NOT USED' ? '' : $area));
            $service_body_id = $this->bread->arraySafeGet($area_data, 1);
            if ($this->bread->getOption('recurse_service_bodies') == 1) {
                return '&recursive=1&services[]=' . $service_body_id;
            } else {
                return '&services[]=' . $service_body_id;
            }
        }
    }
    public function parse_field($text)
    {
        if ($text != '') {
            $exploded = explode("#@-@#", $text);
            $knt = count($exploded);
            if ($knt > 1) {
                $text = $exploded[$knt - 1];
            }
        }
        return $text;
    }
}
