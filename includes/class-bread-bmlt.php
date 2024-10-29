<?php
Class Bread_Bmlt
{
    public static $connection_error;
    public static function authenticate_root_server()
    {
        $query_string = http_build_query(
            array(
            'admin_action' => 'login',
            'c_comdef_admin_login' => Bread::getOption('bmlt_login_id'),
            'c_comdef_admin_password' => Bread::getOption('bmlt_login_password'), '&')
        );
        return Bread_Bmlt::get(Bread::getOption('root_server')."/local_server/server_admin/xml.php?" . $query_string);
    }
    public static function requires_authentication()
    {
        return (Bread::getOption('include_meeting_email') == 1 || Bread::getOption('include_asm') == 1);
    }

    public static function get_root_server_request($url)
    {
        $cookies = null;

        if (Bread_Bmlt::requires_authentication()) {
            $auth_response = Bread_Bmlt::authenticate_root_server();
            $cookies = wp_remote_retrieve_cookies($auth_response);
        }

        return Bread_Bmlt::get($url, $cookies);
    }

    public static function get_configured_root_server_request($url)
    {
        return Bread_Bmlt::get_root_server_request(Bread::getOption('root_server')."/".$url);
    }

    private static function get($url, $cookies = array())
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
        $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults&data_field_key=weekday_tinyint,start_time,service_body_bigint,id_bigint,meeting_name,location_text,email_contact&sort_keys=meeting_name,service_body_bigint,weekday_tinyint,start_time");
        $result = json_decode(wp_remote_retrieve_body($results), true);

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
        $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetFieldKeys");
        return json_decode(wp_remote_retrieve_body($results), true);
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
    public static function get_areas()
    {
        $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetServiceBodies");
        $result = json_decode(wp_remote_retrieve_body($results), true);
        $unique_areas = array();

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
            $unique_areas[] = $value['name'] . ',' . $value['id'] . ',' . $value['parent_id'] . ',' . $parent_name;
        }

        return $unique_areas;
    }

    public static function get_bmlt_server_lang()
    {
        $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetServerInfo");
        $result = json_decode(wp_remote_retrieve_body($results), true);
        if ($result==null) {
            return 'en';
        }
        $result = $result["0"]["nativeLang"];

        return $result;
    }

    public static function testRootServer($override_root_server = null)
    {
        if ($override_root_server == null) {
            $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetServerInfo");
        } else {
            $results = Bread_Bmlt::get_root_server_request($override_root_server."/client_interface/json/?switcher=GetServerInfo");
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
    // This is used from the AdminUI, not to generate the
    // meeting list.
    public static function getFormatsForSelect($all = false)
    {
        if ($all) {
            $results = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetFormats");
            $results = json_decode(wp_remote_retrieve_body($results), true);
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
        $results = json_decode(wp_remote_retrieve_body($results), true);
        $results = empty($service_body_id) ? $results : $results['formats'];
        Bread_Bmlt::sortBySubkey($results, 'key_string');
        return $results;
    }

    public static function sortBySubkey(&$array, $subkey, $sortType = SORT_ASC)
    {
        if (empty($array)) {
            return;
        }
        foreach ($array as $subarray) {
            $keys[] = $subarray[$subkey];
        }
        array_multisort($keys, $sortType, $array);
    }
    public static function generateDefaultQuery() {
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
}
