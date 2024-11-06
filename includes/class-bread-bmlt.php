<?php
class Bread_Bmlt
{
    public static $connection_error;
    private static $bmlt_server_lang = '';
    private static array $unique_areas;
    /**
     * Prepare to make a call that requires user authentication (because the meeting's e-mail is included).
     * First call 'login', then make the call.
     *
     * @return WP_Error | Array
     */
    public static function authenticate_root_server() : WP_Error | Array
    {
        $query_string = http_build_query(
            array(
            'admin_action' => 'login',
            'c_comdef_admin_login' => Bread::getOption('bmlt_login_id'),
            'c_comdef_admin_password' => Bread::getOption('bmlt_login_password'), '&')
        );
        return Bread_Bmlt::get(Bread::getOption('root_server')."/local_server/server_admin/xml.php?" . $query_string);
    }
    /**
     * We only want to make a login call if really necessary.
     *
     * @return boolean
     */
    public static function requires_authentication()
    {
        return (Bread::getOption('include_meeting_email') == 1);
    }

    public static function get_root_server_request(string $url)
    {
        $cookies = array();

        if (Bread_Bmlt::requires_authentication()) {
            $auth_response = Bread_Bmlt::authenticate_root_server();
            $cookies = wp_remote_retrieve_cookies($auth_response);
        }

        return Bread_Bmlt::get($url, $cookies);
    }

    public static function get_configured_root_server_request($url, $raw = false)
    {
        $results = Bread_Bmlt::get_root_server_request(Bread::getOption('root_server')."/".$url);
        if ($raw) {
            return $results;
        }
        return json_decode(wp_remote_retrieve_body($results), true);
    }
    public static function get_formats_by_language(string $lang)
    {
        return Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetFormats$lang");
    }
    /**
     * Undocumented function
     *
     * @param string $url The BMLT calls.
     * @param array $cookies Any cookies that should be added.
     * @return WP_Error | array The result of the call.
     */
    private static function get(string $url, array $cookies = array()) : WP_Error | array
    {
        $args = array(
            'timeout' => '120',
            'cookies' => $cookies,
        );
        if (Bread::getOption('user_agent') != 'None') {
            $args['headers'] = array(
                'User-Agent' => Bread::getOption('user_agent')
            );
        }
        if (Bread::getOption('sslverify') == '1') {
            $args['sslverify'] = false;
        }
        return wp_remote_get($url, $args);
    }
    public static function get_all_meetings()
    {
        $lang = Bread_Bmlt::get_bmlt_server_lang();
        $result = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults&data_field_key=weekday_tinyint,start_time,service_body_bigint,id_bigint,meeting_name,location_text,email_contact&sort_keys=meeting_name,service_body_bigint,weekday_tinyint,start_time");

        $unique_areas = Bread_Bmlt::get_areas();
        $all_meetings = array();
        foreach ($result as $value) {
            foreach ($unique_areas as $unique_area) {
                $area_data = explode(',', $unique_area);
                $area_id = Bread::arraySafeGet($area_data, 1);
                if ($area_id === $value['service_body_bigint']) {
                    $area_name = Bread::arraySafeGet($area_data);
                }
            }

            $value['start_time'] = date("g:iA", strtotime($value['start_time']));
            $all_meetings[] = $value['meeting_name'].'||| ['.Bread::getday($value['weekday_tinyint'], true, $lang).'] ['.$value['start_time'].']||| ['.$area_name.']||| ['.$value['id_bigint'].']';
        }

        return $all_meetings;
    }
    public static function get_fieldkeys()
    {
        return Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetFieldKeys");
    }
    private static $standard_keys = array(
        "id_bigint","worldid_mixed","service_body_bigint",
        "weekday_tinyint","start_time","duration_time","formats",
        "lang_enum","longitude","latitude","meeting_name"."location_text",
        "location_info","location_street","location_city_subsection",
        "location_neighborhood","location_municipality","location_sub_province",
        "location_province","location_postal_code_1","location_nation","comments","zone");
    public static function get_nonstandard_fieldkeys()
    {
        $all_fks = Bread_Bmlt::get_fieldkeys();
        $ret = array();
        foreach ($all_fks as $fk) {
            if (!in_array($fk['key'], Bread_Bmlt::$standard_keys)) {
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
    public static function get_areas()
    {
        if (!empty(Bread_Bmlt::$unique_areas)) {
            return Bread_Bmlt::$unique_areas;
        }
        $result = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetServiceBodies");
        Bread_Bmlt::$unique_areas = array();

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
            Bread_Bmlt::$unique_areas[] = $value['name'] . ',' . $value['id'] . ',' . $value['parent_id'] . ',' . $parent_name;
        }

        return Bread_Bmlt::$unique_areas;
    }
    /**
     * Gets the default language of the root server.
     *
     * @return string 2 character string ISO standard for the language.
     */
    public static function get_bmlt_server_lang() : string
    {
        if (Bread_Bmlt::$bmlt_server_lang == '') {
            $result = Bread_Bmlt::testRootServer();
            if (!($result && is_array($result) && is_array($result[0]))) {
                return 'en';
            }
            Bread_Bmlt::$bmlt_server_lang = ($result==null) ? 'en' : $result["0"]["nativeLang"];
        }
        return Bread_Bmlt::$bmlt_server_lang;
    }
    /**
     * Check if this is a valid BMLT server.
     *
     * @param  $override_root_server
     * @return array the results of GetServerInfo
     */
    public static function testRootServer(string $override_root_server = null) : array
    {
        if ($override_root_server == null) {
            $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetServerInfo", true);
        } else {
            $results = Bread_Bmlt::get_root_server_request($override_root_server."/client_interface/json/?switcher=GetServerInfo", true);
        }
        if ($results instanceof WP_Error) {
            Bread_Bmlt::$connection_error = $results->get_error_message();
            return false;
        }
        $httpcode = wp_remote_retrieve_response_code($results);
        if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
            Bread_Bmlt::$connection_error = "HTTP Return Code: ".$httpcode;
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
    public static function getFormatsForSelect(bool $all = false): array
    {
        if ($all) {
            $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetFormats");
            Bread_Bmlt::sortBySubkey($results, 'key_string');
            return $results;
        }
        $area_data = explode(',', Bread::getOption('service_body_1'));
        $service_body_id = Bread::arraySafeGet($area_data, 1);
        if (Bread::getOption('recurse_service_bodies') == 1) {
            $services = '&recursive=1&services[]=' . $service_body_id;
        } else {
            $services = '&services[]='.$service_body_id;
        }
        if (empty($service_body_id)) {
            $queryUrl = "client_interface/json/?switcher=GetFormats";
        } else {
            $queryUrl = "client_interface/json/?switcher=GetSearchResults$services&get_formats_only";
        }
        $results = Bread_Bmlt::get_configured_root_server_request($queryUrl);
        $results = empty($service_body_id) ? $results : $results['formats'];
        Bread_Bmlt::sortBySubkey($results, 'key_string');
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
    public static function sortBySubkey(array &$array, string $subkey, int $sortType = SORT_ASC): void
    {
        if (empty($array)) {
            return;
        }
        foreach ($array as $subarray) {
            $keys[] = $subarray[$subkey];
        }
        array_multisort($keys, $sortType, $array);
    }
    /**
     * Generate that part of the BMLT query-string that reflects the service bodies being queried.
     *
     * @return string Something to paste into the URL
     */
    public static function generateDefaultQuery(): string
    {
        // addServiceBody has the side effect that
        // the service body option is overridden, so that it contains
        // only the name of the service body.
        $services = Bread_Bmlt::addServiceBody('service_body_1');
        $services .= Bread_Bmlt::addServiceBody('service_body_2');
        $services .= Bread_Bmlt::addServiceBody('service_body_3');
        $services .= Bread_Bmlt::addServiceBody('service_body_4');
        $services .=Bread_Bmlt::addServiceBody('service_body_5');
        return $services;
    }
    private static function addServiceBody($service_body_name)
    {
        if (false === ( Bread::getOption($service_body_name) == 'Not Used' )) {
            $area_data = explode(',', Bread::getOption($service_body_name));
            $area = Bread::arraySafeGet($area_data);
            Bread::setOption($service_body_name, ($area == 'NOT USED' ? '' : $area));
            $service_body_id = Bread::arraySafeGet($area_data, 1);
            if (Bread::getOption('recurse_service_bodies') == 1) {
                return '&recursive=1&services[]=' . $service_body_id;
            } else {
                return '&services[]='.$service_body_id;
            }
        }
    }
    public static function parse_field($text)
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
}
