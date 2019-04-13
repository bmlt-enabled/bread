<?php
/**
Plugin Name: bread
Plugin URI: http://wordpress.org/extend/plugins/bread/
Description: Maintains and generates a PDF Meeting List from BMLT.
Author: odathp, radius314, pjaudiomv, klgrimley, jbraswell
Version: 1.9.7
*/
/* Disallow direct access to the plugin file */
use Mpdf\Mpdf;
error_reporting(1);
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

require_once plugin_dir_path(__FILE__).'mpdf/vendor/autoload.php';
include 'partials/_helpers.php';
if (!class_exists("Bread")) {
	class Bread {
		var $lang = '';
		var $mpdf = '';
		var $meeting_count = 0;
		var $formats_used = '';
		var $formats_spanish = '';
		var $formats_all = '';
		
		var $service_meeting_result ='';
		var $optionsName = 'bmlt_meeting_list_options';
		var $options = array();
		function __construct() {
		    $this->protocol = (strpos(strtolower(home_url()), "https") !== false ? "https" : "http") . "://";
		    if (is_admin() || intval($_GET['current-meeting-list']) == 1 ) {
                $this->getMLOptions();
                $this->lang = $this->get_bmlt_server_lang();

                if (is_admin()) {
                    // Back end
                    //Initialize the options
                    add_action("admin_init", array(&$this, 'my_sideload_image'));
                    add_action("admin_menu", array(&$this, "admin_menu_link"));
                    add_filter('tiny_mce_before_init', array(&$this, 'tiny_tweaks' ));
                    add_filter('mce_external_plugins', array(&$this, 'my_custom_plugins'));
                    add_filter('mce_buttons', array(&$this, 'my_register_mce_button'));
                    add_action("admin_notices", array(&$this, "is_root_server_missing"));
                    add_action("admin_init", array(&$this, "pwsix_process_settings_export"));
                    add_action("admin_init", array(&$this, "pwsix_process_settings_import"));
                    add_action("admin_init", array(&$this, "pwsix_process_default_settings"));
                    add_action("admin_init", array(&$this, "my_theme_add_editor_styles"));
                    add_action("admin_enqueue_scripts", array(&$this, "enqueue_backend_files"));
                    add_action("wp_default_editor", array(&$this, "ml_default_editor"));
                    add_filter('tiny_mce_version', array( __CLASS__, 'force_mce_refresh' ) );
                } else if ( intval($_GET['current-meeting-list']) == 1 ) {
                    $this->bmlt_meeting_list();
                }
            }
		}

		function ml_default_editor() {
			global $my_admin_page;
			$screen = get_current_screen();
			if ( $screen->id == $my_admin_page ) {
				return "tinymce";	
			}
		}

		function force_mce_refresh( $ver ) {
			global $my_admin_page;
			$screen = get_current_screen();
			if ( $screen->id == $my_admin_page ) {
				return $ver + 99;	
			}
		}

		function my_sideload_image() {
			global $my_admin_page;
			$screen = get_current_screen();
			if ( isset($screen) && $screen->id == $my_admin_page ) {
				if ( get_option($this->optionsName) === false ) {
					$url = plugin_dir_url(__FILE__) . "includes/nalogo.jpg";
					media_sideload_image( $url, 0 );
				}
			}
		}

		// Register new button in the editor
		function my_register_mce_button( $buttons ) {
			global $my_admin_page;
			$screen = get_current_screen();
			if ( $screen->id == $my_admin_page ) {
				array_push( $buttons, 'front_page_button', 'custom_template_button_1', 'custom_template_button_2' );
			}
			return $buttons;
		}

		function my_custom_plugins () {
			global $my_admin_page;
			$screen = get_current_screen();
			if ( $screen->id == $my_admin_page ) {
				$plugins = array('table', 'front_page_button', 'code', 'contextmenu' ); //Add any more plugins you want to load here
				$plugins_array = array();
				//Build the response - the key is the plugin name, value is the URL to the plugin JS
				foreach ($plugins as $plugin ) {
				  $plugins_array[ $plugin ] = plugins_url('tinymce/', __FILE__) . $plugin . '/plugin.min.js';
				}
			}
			return $plugins_array;
		}	

		// Enable font size & font family selects in the editor
		function tiny_tweaks( $initArray ){
			global $my_admin_page;
			$screen = get_current_screen();
			if ( $screen->id == $my_admin_page ) {
				$initArray['fontsize_formats'] = "5pt 6pt 7pt 8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 22pt 24pt 26pt 28pt 30pt 32pt 34pt 36pt 38pt";
				$initArray['theme_advanced_blockformats'] = 'h2,h3,h4,p';
				$initArray['wordpress_adv_hidden'] = false;
				$initArray['font_formats']='Arial (Default)=arial;';
				$initArray['font_formats'].='Times (Sans-Serif)=times;';
				$initArray['font_formats'].='Courier (Monospace)=courier;';
			}
			return $initArray;
		}

		function is_root_server_missing() {
			global $my_admin_page;
			$screen = get_current_screen();
			if ( $screen->id == $my_admin_page ) {
				$root_server = $this->options['root_server'];
				if ( $root_server == '' ) {
					echo '<div id="message" class="error"><p>Missing BMLT Server in settings for bread.</p>';
					$url = admin_url( 'options-general.php?page=bmlt-meeting-list.php' );
					echo "<p><a href='$url'>Settings</a></p>";
					echo '</div>';
				} else if (!get_temp_dir()) {
					echo '<div id="message" class="error"><p>' . get_temp_dir() . ' temporary directory is not writable.</p>';
					$url = admin_url( 'options-general.php?page=bmlt-meeting-list.php' );
					echo "<p><a href='$url'>Settings</a></p>";
					echo '</div>';
				}
				add_action("admin_notices", array(
					&$this,
					"clear_admin_message"
				));
			}
		}

		function clear_admin_message() {
			remove_action("admin_notices", array(
				&$this,
				"is_root_server_missing"
			));
		}

		function clear_admin_message2() {
			echo '<div id="message" class="error"><p>what</p></div>';
		}

		function Bread() {
			$this->__construct();
		}

		/**
		* @desc Adds JS/CSS to the header
		*/
		function enqueue_backend_files($hook) {
			if( $hook == 'toplevel_page_bmlt-meeting-list' ) {
				wp_enqueue_script('common');
				wp_enqueue_script('jquery-ui-tabs');
				wp_enqueue_script('jquery-ui-accordion');
				wp_enqueue_script('jquery-ui-dialog');
				wp_enqueue_style("jquery-ui", plugin_dir_url(__FILE__) . "css/jquery-ui.min.css", false, "1.2", 'all');
				wp_enqueue_style("spectrum", plugin_dir_url(__FILE__) . "css/spectrum.css", false, "1.2", 'all');
				wp_enqueue_style("admin", plugin_dir_url(__FILE__) . "css/admin.css", false, "1.2", 'all');
				wp_enqueue_style("chosen", plugin_dir_url(__FILE__) . "css/chosen.min.css", false, "1.2", 'all');
				wp_enqueue_script("bmlt_meeting_list", plugin_dir_url(__FILE__) . "js/bmlt_meeting_list.js", array('jquery'), "1.2", true);
				wp_enqueue_script("tooltipster", plugin_dir_url(__FILE__) . "js/jquery.tooltipster.min.js", array('jquery'), "1.2", true);
				wp_enqueue_script("spectrum", plugin_dir_url(__FILE__) . "js/spectrum.js", array('jquery'), "1.2", true);
				wp_enqueue_script("chosen", plugin_dir_url(__FILE__) . "js/chosen.jquery.min.js", array('jquery'), "1.2", true);
			}
		}

		function my_theme_add_editor_styles() {
			global $my_admin_page;
			$screen = get_current_screen();
			if ( isset($screen) && $screen->id == $my_admin_page ) {
				add_editor_style( plugin_dir_url(__FILE__) . "css/editor-style.css" );
			}
		}

		function getday( $day, $abbreviate = false, $language = '') {
			$data = '';
			if ( $day == 1 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Sun' : "Sunday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'Dom' : "Domingo");
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Dim' : "Dimanche");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Sun / Dom' : "Sunday / Domingo");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'Dom' : "Domingo");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Sun / Dom' : "Sunday / Domingo");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Dim / Sun' : "Dimanche / Sunday");
				} elseif ( $language == 'se') {
					$data = ($abbreviate ? "S&#246;n" : "S&#246;ndag");
				} elseif ( $language == 'dk') {
                    $data = ($abbreviate ? "S&#248;" : "S&#248;ndag");
                }
			} elseif ( $day == 2 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Mon' : "Monday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'Lun' : "Lunes");
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Lun' : "Lundi");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Mon / Lun / Seg' : "Monday / Lunes / Segunda-feira");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'Seq' : "Segunda-feira");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Mon / Lun' : "Monday / Lunes");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Lun / Mon' : "Lundi / Monday");
				} elseif ( $language == 'se') {
					$data = ($abbreviate ? "M&#229;n" : "M&#229;ndag");
				} elseif ( $language == 'dk') {
                    $data = ($abbreviate ? "Ma" : "Mandag");
                }
			} elseif ( $day == 3 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Tue' : "Tuesday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'Mar' : "Martes");
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Mar' : "Mardi");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Tue / Mar / Ter' : "Tuesday / Martes / Terça-feira");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'Ter' : "Terça-feira");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Tue / Mar' : "Tuesday / Martes");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Mar / Tues' : "Mardi / Tuesday");
				} elseif ( $language == 'se') {
					$data = ($abbreviate ? "Tis" : "Tisdag");
				} elseif ( $language == 'dk') {
                    $data = ($abbreviate ? "Ti" : "Tirsdag");
                }
			} elseif ( $day == 4 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Wed' : "Wednesday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'Mi&eacute;' : "Mi&eacute;rcoles");
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Mer' : "Mercredi");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Wed / Mi&eacute; / Qua' : "Wednesday / Mi&eacute;rcoles / Quarta-feira");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'Qua' : "Quarta-feira");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Wed / Mi&eacute;' : "Wednesday / Mi&eacute;rcoles");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Mer / Wed' : "Mercredi / Wednesday");
				} elseif ( $language == 'se') {
					$data = ($abbreviate ? "Ons" : "Onsdag");
				} elseif ( $language == 'dk') {
                    $data = ($abbreviate ? "On" : "Onsdag");
                }
			} elseif ( $day == 5 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Thu' : "Thursday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'Jue' : "Jueves");
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Jeu' : "Jeudi");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Thu / Jue / Qui' : "Thursday / Jueves / Quinta-feira");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'Qui' : "Quinta-feira");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Thu / Jue' : "Thursday / Jueves");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Jeu / Thu' : "Jeudi / Thursday");
				} elseif ( $language == 'se') {
					$data = ($abbreviate ? "Tors" : "Torsdag");
				} elseif ( $language == 'dk') {
                    $data = ($abbreviate ? "To" : "Torsdag");
                }
			} elseif ( $day == 6 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Fri' : "Friday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'Vie' : "Viernes");
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Ven' : "Vendredi");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Fri / Vie / Sex' : "Friday / Viernes / Sexta-feira");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'Sex' : "Sexta-feira");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Fri / Vie' : "Friday / Viernes");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Ven / Fri' : "Vendredi / Friday");
				} elseif ( $language == 'se') {
					$data = ($abbreviate ? "Fre" : "Fredag");
				} elseif ( $language == 'dk') {
                    $data = ($abbreviate ? "Fr" : "Fredag");
                }
			} elseif ( $day == 7 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Sat' : "Saturday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'S&aacute;b' : "S&aacute;bado");
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Sam' : "Samedi");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Sat / S&aacute;b' : "Saturday / S&aacute;bado");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'S&aacute;b' : "S&aacute;bado");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Sat / S&aacute;b' : "Saturday / S&aacute;bado");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Sam / Sat' : "Samedi / Saturday");
				} elseif ( $language == 'se') {
					$data = ($abbreviate ? "L&#246;r" : "L&#246;rdag");
				} elseif ( $language == 'dk') {
                    $data = ($abbreviate ? "L&#248;" : "L&#248;rdag");
                }
			}
			
			Return utf8_encode($data);
		}

		function authenticate_root_server() {
			$query_string = http_build_query(array(
				'admin_action' => 'login', 
				'c_comdef_admin_login' => $this->options['bmlt_login_id'], 
				'c_comdef_admin_password' => $this->options['bmlt_login_password'], '&'));
			return $this->get($this->options['root_server']."/local_server/server_admin/xml.php?" . $query_string);				
		} 

		function get_root_server_request($url) {
		    $cookies = null;

			if ($this->options['include_meeting_email'] == 1 || $this->options['include_asm'] == 1) {
				$auth_response = $this->authenticate_root_server();
                $cookies = wp_remote_retrieve_cookies($auth_response);
			}

			return $this->get($url, $cookies);
		}

		function get_configured_root_server_request($url) {
			return $this->get_root_server_request($this->options['root_server']."/".$url);
		}

		function get($url, $cookies = null) {
			$args = array(
				'timeout' => '120',
				'headers' => array(
					'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bread'
				),
                'cookies' => isset($cookies) ? $cookies : null
			);

            return wp_remote_get($url, $args);
		}

		function get_all_meetings() {
			$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults&data_field_key=weekday_tinyint,start_time,service_body_bigint,id_bigint,meeting_name,location_text,email_contact&sort_keys=meeting_name,service_body_bigint,weekday_tinyint,start_time");
			$result = json_decode(wp_remote_retrieve_body($results),true);
			
			$unique_areas = $this->get_areas();			
			$all_meetings = array();
			foreach ($result as $value) {
				foreach($unique_areas as $unique_area){
					$area_data = explode(',',$unique_area);
					$area_id = $area_data[1];
					if ( $area_id === $value['service_body_bigint'] ) {
						$area_name = $area_data[0];
					}
				}							
				
				$value['start_time'] = date("g:iA",strtotime($value['start_time']));
				$all_meetings[] = $value['meeting_name'].'||| ['.$this->getday($value['weekday_tinyint'], true, $this->lang).'] ['.$value['start_time'].']||| ['.$area_name.']||| ['.$value['id_bigint'].']';
			}
			
			return $all_meetings;
		}

		function get_areas() {
			$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetServiceBodies");
			$result = json_decode(wp_remote_retrieve_body($results), true);
			$unique_areas = array();
			
			foreach ($result as $value) {
				$parent_name = 'Parent ID';
				foreach ($result as $parent) {
					if ( $value['parent_id'] == $parent['id'] ) {
						$parent_name = $parent['name'];
					}
				}
				if ( $value['parent_id'] == '' ) {
					$value['parent_id'] = '0';
				}
				$unique_areas[] = $value['name'] . ',' . $value['id'] . ',' . $value['parent_id'] . ',' . $parent_name;
			}
						
			return $unique_areas;
		}

		function get_bmlt_server_lang () {
			$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetServerInfo");
			$result = json_decode(wp_remote_retrieve_body($results), true);
			$result = $result["0"]["nativeLang"];
			
			return $result;
		}
		
		function testRootServer($override_root_server = null) {
			if ($override_root_server == null) {
				$results = $this->get_configured_root_server_request("client_interface/serverInfo.xml");
			} else {
				$results = $this->get_root_server_request($override_root_server."/client_interface/serverInfo.xml");
			}
			$httpcode = wp_remote_retrieve_response_code($results);
			if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
				return false;
			}
			$results = simplexml_load_string(wp_remote_retrieve_body($results));
			$results = json_encode($results);
			$results = json_decode($results,TRUE);
			$results = $results["serverVersion"]["readableString"];
			return $results;
		}

		function getUsedFormats() {
            if ( !isset($this->options['recurse_service_bodies']) ) {$this->options['recurse_service_bodies'] = 1;}
			$area_data = explode(',',$this->options['service_body_1']);
			$service_body_id = $area_data[1];
			$parent_body_id = $area_data[2];
			if ( $this->options['recurse_service_bodies'] == 1 ) {
				$services = '&recursive=1&services[]=' . $service_body_id;
			} else {
				$services = '&services[]='.$service_body_id;
			}
			$area_data = explode(',',$this->options['service_body_1']);
			$service_body_id = $area_data[1];
			$parent_body_id = $area_data[2];
            if ( $this->options['recurse_service_bodies'] == 1 ) {
				$services = '&recursive=1&services[]=' . $service_body_id;
			} else {
				$services = '&services[]='.$service_body_id;
			}

			$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&get_formats_only");
			$results = json_decode(wp_remote_retrieve_body($results), true);
			$results = $results['formats'];
			$this->sortBySubkey($results, 'key_string');
			return $results;
		}

		function sortBySubkey(&$array, $subkey, $sortType = SORT_ASC) {
			if ( empty( $array ) ) { return; }
			foreach ($array as $subarray) {
				$keys[] = $subarray[$subkey];
			}
			array_multisort($keys, $sortType, $array);
		}

		function bmlt_meeting_list($atts = null, $content = null) {
			ini_set('max_execution_time', 600); // tomato server can take a long time to generate a schedule, override the server setting
			$area_data = explode(',',$this->options['service_body_1']);
			$area = $area_data[0];
			$this->options['service_body_1'] = $area;
			$service_body_id = $area_data[1];
			$parent_body_id = $area_data[2];
            if ( !isset($this->options['recurse_service_bodies']) ) {$this->options['recurse_service_bodies'] = 1;}
            if ( $this->options['recurse_service_bodies'] == 1 ) {
				$services = '&recursive=1&services[]=' . $service_body_id;
				$services_service_body_1 = '&recursive=1&services[]=' . $service_body_id;
			} else {
				$services = '&services[]='.$service_body_id;
				$services_service_body_1 = '&services[]='.$service_body_id;
			}
			if ( false === ( $this->options['service_body_2'] == 'Not Used' ) ) {
				$area_data = explode(',',$this->options['service_body_2']);
				$area = $area_data[0];
				$this->options['service_body_2'] = ($area == 'NOT USED' ? '' : $area);
				$service_body_id = $area_data[1];
				$parent_body_id = $area_data[2];
                if ( $this->options['recurse_service_bodies'] == 1 ) {
					$services .= '&recursive=1&services[]=' . $service_body_id;
				} else {
					$services .= '&services[]='.$service_body_id;
				}
			}
			if ( false === ( $this->options['service_body_3'] == 'Not Used' ) ) {
				$area_data = explode(',',$this->options['service_body_3']);
				$area = $area_data[0];
				$this->options['service_body_3'] = ($area == 'NOT USED' ? '' : $area);
				$service_body_id = $area_data[1];
				$parent_body_id = $area_data[2];
                if ( $this->options['recurse_service_bodies'] == 1 ) {
					$services .= '&recursive=1&services[]=' . $service_body_id;
				} else {
					$services .= '&services[]='.$service_body_id;
				}
			}
			if ( false === ( $this->options['service_body_4'] == 'Not Used' ) ) {
				$area_data = explode(',',$this->options['service_body_4']);
				$area = $area_data[0];
				$this->options['service_body_4'] = ($area == 'NOT USED' ? '' : $area);
				$service_body_id = $area_data[1];
				$parent_body_id = $area_data[2];
                if ( $this->options['recurse_service_bodies'] == 1 ) {
					$services .= '&recursive=1&services[]=' . $service_body_id;
				} else {
					$services .= '&services[]='.$service_body_id;
				}
			}
			if ( false === ( $this->options['service_body_5'] == 'Not Used' ) ) {
				$area_data = explode(',',$this->options['service_body_5']);
				$area = $area_data[0];
				$this->options['service_body_5'] = ($area == 'NOT USED' ? '' : $area);
				$service_body_id = $area_data[1];
				$parent_body_id = $area_data[2];
                if ( $this->options['recurse_service_bodies'] == 1 ) {
					$services .= '&recursive=1&services[]=' . $service_body_id;
				} else {
					$services .= '&services[]='.$service_body_id;
				}
			}
			if (isset($_GET['custom_query'])) {
				$services = $_GET['custom_query'];
			} elseif ( false === ( $this->options['custom_query'] == '' )) {
				$services = $this->options['custom_query'];
			}
			if ( $this->options['root_server'] == '' ) {
				echo '<p><strong>bread Error: BMLT Server missing.<br/><br/>Please go to Settings -> bread and verify BMLT Server</strong></p>';
				exit;
			}
			if ( $this->options['service_body_1'] == 'Not Used' && true === ($this->options['custom_query'] == '' ) ) {
				echo '<p><strong>bread Error: Service Body 1 missing from configuration.<br/><br/>Please go to Settings -> bread and verify Service Body</strong><br/><br/>Contact the bread administrator and report this problem!</p>';
				exit;
			}

			$num_columns = 0;
			if ( !isset($this->options['header_font_size']) ) {$this->options['header_font_size'] = $this->options['content_font_size'];}
			if ( !isset($this->options['header_text_color']) ) {$this->options['header_text_color'] = '#ffffff';}
			if ( !isset($this->options['header_background_color']) ) {$this->options['header_background_color'] = '#000000';}
			if ( !isset($this->options['margin_left']) ) {$this->options['margin_left'] = 3;}
			if ( !isset($this->options['margin_bottom']) ) {$this->options['margin_bottom'] = 3;}
			if ( !isset($this->options['margin_top']) ) {$this->options['margin_top'] = 3;}
			if ( !isset($this->options['page_size']) ) {$this->options['page_size'] = 'legal';}
			if ( !isset($this->options['page_orientation']) ) {$this->options['page_orientation'] = 'L';}
			if ( !isset($this->options['page_fold']) ) {$this->options['page_fold'] = 'quad';}
			if ( !isset($this->options['meeting_sort']) ) {$this->options['meeting_sort'] = 'day';}
			if ( !isset($this->options['borough_suffix']) ) {$this->options['borough_suffix'] = 'Borough';}
			if ( !isset($this->options['county_suffix']) ) {$this->options['county_suffix'] = 'County';}
			if ( !isset($this->options['neighborhood_suffix']) ) {$this->options['neighborhood_suffix'] = 'Neighborhood';}
			if ( !isset($this->options['city_suffix']) ) {$this->options['city_suffix'] = 'City';}
			if ( !isset($this->options['column_line']) ) {$this->options['column_line'] = 0;}
			if ( !isset($this->options['col_color']) ) {$this->options['col_color'] = '#bfbfbf';}
			if ( !isset($this->options['custom_section_content']) ) {$this->options['custom_section_content'] = '';}
			if ( !isset($this->options['custom_section_line_height']) ) {$this->options['custom_section_line_height'] = '1';}
			if ( !isset($this->options['custom_section_font_size']) ) {$this->options['custom_section_font_size'] = '9';}
			if ( !isset($this->options['pagenumbering_font_size']) ) {$this->options['pagenumbering_font_size'] = '9';}
			if ( !isset($this->options['include_zip']) ) {$this->options['include_zip'] = 0;}
			if ( !isset($this->options['include_meeting_email']) ) {$this->options['include_meeting_email'] = 0;}
			if ( !isset($this->options['include_protection']) ) {$this->options['include_protection'] = 0;}
			if ( !isset($this->options['base_font']) ) {$this->options['base_font'] = 'dejavusanscondensed';}
			if ( !isset($this->options['weekday_language']) ) {$this->options['weekday_language'] = 'en';}
			if ( !isset($this->options['weekday_start']) ) {$this->options['weekday_start'] = '1';}
			if ( !isset($this->options['include_asm']) ) {$this->options['include_asm'] = '0';}
			if ( !isset($this->options['header_uppercase']) ) {$this->options['header_uppercase'] = '0';}
			if ( !isset($this->options['header_bold']) ) {$this->options['header_bold'] = '1';}
			if ( !isset($this->options['sub_header_shown']) ) {$this->options['sub_header_shown'] = '1';}
			if ( !isset($this->options['bmlt_login_id']) ) {$this->options['bmlt_login_id'] = '';}
			if ( !isset($this->options['bmlt_login_password']) ) {$this->options['bmlt_login_password'] = '';}
			if ( !isset($this->options['protection_password']) ) {$this->options['protection_password'] = '';}
			if ( !isset($this->options['cache_time']) ) {$this->options['cache_time'] = 0;}
			if ( !isset($this->options['extra_meetings']) ) {$this->options['extra_meetings'] = '';}
			if ( !isset($this->options['custom_query']) ) {$this->options['custom_query'] = '';}
			if ( !isset($this->options['used_format_1']) ) {$this->options['used_format_1'] = '';}
			if ( !isset($this->options['used_format_2']) ) {$this->options['used_format_2'] = '';}
			if ( intval($this->options['cache_time']) > 0 && ! isset($_GET['nocache']) ) {
				$transient_key = 'bmlt_ml_'.md5($this->options['root_server'].$services);
				if ( false !== ( $content = get_transient( $transient_key ) ) ) {
					$content = pack("H*" , $content );
					$name = "current_meeting_list_".strtolower( date ( "njYghis" ) ).".pdf";
					header('Content-Type: application/pdf');
					header('Content-Length: '.strlen($content));
					header('Content-disposition: inline; filename="'.$name.'"');
					header('Cache-Control: public, must-revalidate, max-age=0'); 
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					echo $content;
					exit;
				}
			}

			if ( $this->options['page_fold'] == 'half' && $this->options['page_size'] == '5inch' ) {
			    $page_type_settings = ['format' => array(197.2,279.4), 'margin_footer' => 5];
			} elseif ( $this->options['page_fold'] == 'half' && $this->options['page_size'] == 'A5' ) {
                $page_type_settings = ['format' => 'A5', 'margin_footer' => 5];
			} elseif ( $this->options['page_size'] . '-' .$this->options['page_orientation'] == 'ledger-L' ) {
                $page_type_settings = ['format' => array(432,279), 'margin_footer' => 0];
			} elseif ( $this->options['page_size'] . '-' .$this->options['page_orientation'] == 'ledger-P' ) {
                $page_type_settings = ['format' => array(279,432), 'margin_footer' => 0];
			} else {
                $page_type_settings = ['format' => $this->options['page_size']."-".$this->options['page_orientation'], 'margin_footer' => 0];
			}

            $default_font = $this->options['base_font'] == "freesans" ? "dejavusanscondensed" : $this->options['base_font'];
            $mode = 's';
            if ($default_font == 'arial' || $default_font == 'times' || $default_font == 'courier') {
            	$mpdf_init_options = [
            		'fontDir' => array(
            			__DIR__ . '/mpdf/vendor/mpdf/mpdf/ttfonts',
						__DIR__ . '/fonts',
						),
					'tempDir' => get_temp_dir(),
					'mode' => $mode,
					'default_font_size' => 7,
					'fontdata' => [
						"arial" => [
							'R' => "Arial.ttf",
							'B' => "ArialBold.ttf",
							'I' => "ArialItalic.ttf",
							'BI' => "ArialBoldItalic.ttf",
						],
						"times" => [
							'R' => "Times.ttf",
							'B' => "TimesBold.ttf",
							'I' => "TimesItalic.ttf",
							'BI' => "TimesBoldItalic.ttf",
						],
						"courier" => [
							'R' => "CourierNew.ttf",
							'B' => "CourierNewBold.ttf",
							'I' => "CourierNewItalic.ttf",
							'BI' => "CourierNewBoldItalic.ttf",
						]
					],
					'default_font' => $default_font,
					'margin_left' => $this->options['margin_left'],
					'margin_right' => $this->options['margin_right'],
					'margin_top' => $this->options['margin_top'],
					'margin_bottom' => $this->options['margin_bottom'],
					'orientation' => 'P'
				];
            }
            else {
            	$mpdf_init_options = [
            		'mode' => $mode,
					'tempDir' => get_temp_dir(),
					'default_font_size' => 7,
					'default_font' => $default_font,
					'margin_left' => $this->options['margin_left'],
					'margin_right' => $this->options['margin_right'],
					'margin_top' => $this->options['margin_top'],
					'margin_bottom' => $this->options['margin_bottom'],
					'orientation' => 'P'
				];
            }
   
            $this->mpdf = new mPDF(array_merge($mpdf_init_options, $page_type_settings));
            $this->mpdf->setAutoBottomMargin = 'pad';

            // TODO: Adding a page number really could just be an option or tag.
            if ( $this->options['page_fold'] == 'half' &&
                 ($this->options['page_size'] == '5inch' || $this->options['page_size'] == 'A5')) {
                $this->mpdf->DefHTMLFooterByName('MyFooter','<div style="text-align:center;font-size:' . $this->options['pagenumbering_font_size'] . 'pt;font-style: italic;">Page {PAGENO}</div>');
            }

			$this->mpdf->simpleTables = false;
			$this->mpdf->useSubstitutions = false;
			$blog = get_bloginfo( "name" );
			$this->mpdf->mirrorMargins = false;
			$this->mpdf->list_indent_first_level = 1; // 1 or 0 - whether to indent the first level of a list
			// LOAD a stylesheet
			$header_stylesheet = file_get_contents(plugin_dir_path( __FILE__ ).'css/mpdfstyletables.css');
			$this->mpdf->WriteHTML($header_stylesheet,1); // The parameter 1 tells that this is css/style only and no body/html/text
			$this->mpdf->SetDefaultBodyCSS('line-height', $this->options['content_line_height']);
			$this->mpdf->SetDefaultBodyCSS('background-color', 'transparent');
			if ( $this->options['column_line'] == 1 ) {
				$html = '<body style="background-color:#fff;">';
				if ( $this->options['page_fold'] == 'tri' ) {
					$html .= '<table style="background-color: #fff;width: 100%; border-collapse: collapse;">
					<tbody>
					<tr>
					<td style="background-color: #fff;width: 33.33%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 33.33%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 33.33%; height: 279.4mm;">&nbsp;</td>
					</tr>
					</tbody>
					</table>';
				}
				if ( $this->options['page_fold'] == 'quad' ) {
					$html .= '<table style="background-color: #fff;width: 100%; border-collapse: collapse;">
					<tbody>
					<tr>
					<td style="background-color: #fff;width: 25%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 25%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 25%; border-right: 1px solid '.$this->options['col_color']. '; height: 279.4mm;">&nbsp;</td>
					<td style="background-color: #fff;width: 25%; height: 279.4mm;">&nbsp;</td>
					</tr>
					</tbody>
					</table>';
				}
				$this->mpdf->SetImportUse(); 		
				$this->mpdf_column=new mPDF([
                    'mode' => $mode,
                    'tempDir' => get_temp_dir(),
                    'format' => $this->options['page_size']."-".$this->options['page_orientation'],
                    'default_font_size' => 7,
                    'default_font' => $default_font,
                    'margin_left' => $this->options['margin_left'],
                    'margin_right' => $this->options['margin_right'],
                    'margin_top' => $this->options['margin_top'],
                    'margin_bottom' => $this->options['margin_bottom'],
                    'margin_footer' => 0,
                    'orientation' => 'P'
                ]);
				
				$this->mpdf_column->WriteHTML($html);
				$FilePath = ABSPATH . "column_tmp_".strtolower( date ( "njYghis" ) ).".pdf";
				$this->mpdf_column->Output($FilePath,'F');
				$pagecount = $this->mpdf->SetSourceFile($FilePath);
				$tplId = $this->mpdf->ImportPage($pagecount);
				$this->mpdf->SetPageTemplate($tplId);
				unlink($FilePath);
			}
			if ( $this->options['meeting_sort'] == 'state' ) {
				$sort_keys = 'location_province,location_municipality,weekday_tinyint,start_time,meeting_name';
			} elseif ( $this->options['meeting_sort'] == 'city' ) {
				$sort_keys = 'location_municipality,weekday_tinyint,start_time,meeting_name';
			} elseif ( $this->options['meeting_sort'] == 'borough' ) {
				$sort_keys = 'location_city_subsection,weekday_tinyint,start_time,meeting_name';
			} elseif ( $this->options['meeting_sort'] == 'county' ) {
				$sort_keys = 'location_sub_province,weekday_tinyint,start_time,meeting_name';
			} elseif ( $this->options['meeting_sort'] == 'borough_county' ) {
				$sort_keys = 'location_city_subsection,location_sub_province,weekday_tinyint,start_time,meeting_name';
			} elseif ( $this->options['meeting_sort'] == 'neighborhood_city' ) {
				$sort_keys = 'location_neighborhood,location_municipality,weekday_tinyint,start_time,meeting_name';
			} elseif ( $this->options['meeting_sort'] == 'group' ) {
				$sort_keys = 'meeting_name,weekday_tinyint,start_time';
			} elseif ( $this->options['meeting_sort'] == 'weekday_area' ) {
				$sort_keys = 'weekday_tinyint,service_body_bigint,start_time';
			} elseif ( $this->options['meeting_sort'] == 'weekday_city' ) {
				$sort_keys = 'weekday_tinyint,location_municipality,start_time';
            } elseif ( $this->options['meeting_sort'] == 'weekday_county' ) {
                $sort_keys = 'weekday_tinyint,location_sub_province,location_municipality,start_time';
			} else {
				$this->options['meeting_sort'] = 'day';
				$sort_keys = 'weekday_tinyint,start_time,meeting_name';
			}

            $get_used_formats = '&get_used_formats';

            if ( $this->options['used_format_1'] == '' && $this->options['used_format_2'] == '' ) {
                $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys$get_used_formats");
            } elseif ( $this->options['used_format_1'] != '' ) {
                $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys&get_used_formats&formats[]=".$this->options['used_format_1'] );
            } elseif ( $this->options['used_format_2'] != '' ) {
                $results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys&get_used_formats&formats[]=".$this->options['used_format_2'] );
            }

            $result = json_decode(wp_remote_retrieve_body($results), true);
            if ( $this->options['extra_meetings'] ) {
                $extras = "";
                foreach ($this->options['extra_meetings'] as $value) {
                    $data = array(" [", "]");
                    $value = str_replace($data, "", $value);
                    $extras .= "&meeting_ids[]=".$value;
                }

                $extra_results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults&sort_keys=".$sort_keys."".$extras."".$get_used_formats );
                $extra_result = json_decode(wp_remote_retrieve_body($extra_results), true);
                if ( $extra_result <> null ) {
                    $result_meetings = array_merge($result['meetings'], $extra_result['meetings']);
                    foreach ($result_meetings as $key => $row) {
                        $weekday[$key] = $row['weekday_tinyint'];
                        $start_time[$key] = $row['start_time'];
                    }

                    array_multisort($weekday, SORT_ASC, $start_time, SORT_ASC, $result_meetings);
                    $this->formats_used = array_merge($result['formats'], $extra_result['formats']);
                } else {
                    $this->formats_used = $result['formats'];
                    $result_meetings = $result['meetings'];
                }
            } else {
                $this->formats_used = $result['formats'];
                $result_meetings = $result['meetings'];
            }

			if ( $result_meetings == null ) {
				echo "<script type='text/javascript'>\n";
				echo "document.body.innerHTML = ''";
				echo "</script>";
				echo '<div style="font-size: 20px;text-align:center;font-weight:normal;color:#F00;margin:0 auto;margin-top: 30px;"><p>No Meetings Found</p><p>Or</p><p>Internet or Server Problem</p><p>'.$this->options['root_server'].'</p><p>Please try again or contact your BMLT Administrator</p></div>';
				exit;
			}
			if ( strpos($this->options['custom_section_content'].$this->options['front_page_content'].$this->options['last_page_content'], "[service_meetings]") !== false ) {
				$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services_service_body_1&sort_keys=meeting_name&advanced_published=0" );
				$this->service_meeting_result = json_decode(wp_remote_retrieve_body($results), true);
			}
			$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetFormats");
			$this->formats_all = json_decode(wp_remote_retrieve_body($results), true);
			if ( strpos($this->options['custom_section_content'].$this->options['front_page_content'].$this->options['last_page_content'], '[format_codes_used_basic_es') !== false ) {
				if ( $this->options['used_format_1'] == '' && $this->options['used_format_2'] == '' ) {
					$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=time$get_used_formats&lang_enum=es" );
				} elseif ( $this->options['used_format_1'] != '' ) {
					$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=time&get_used_formats&lang_enum=es&formats[]=".$this->options['used_format_1'] );
				} elseif ( $this->options['used_format_2'] != '' ) {
					$results = $this->get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=time&get_used_formats&lang_enum=es&formats[]=".$this->options['used_format_2'] );
				}
				$result_es = json_decode(wp_remote_retrieve_body($results), true);
				$this->formats_spanish = $result_es['formats'];
				$this->sortBySubkey($this->formats_spanish, 'key_string');
			}
			
			if ( $this->options['include_asm'] === '0' ) {
				$countmax = count ( $this->formats_used );
				for ( $count = 0; $count < $countmax; $count++ ) {
					if ( $this->formats_used[$count]['key_string'] == 'ASM' ) {
						unset($this->formats_used[$count]);
					}
				}
				$this->formats_used = array_values($this->formats_used);
			}
			$this->sortBySubkey($this->formats_used, 'key_string');
			$this->sortBySubkey($this->formats_all, 'key_string');

            $this->uniqueFormat($this->formats_used, 'key_string');
            $this->uniqueFormat($this->formats_all, 'key_string');

			$this->meeting_count = count($result_meetings);
			$unique_areas = $this->get_areas();			
			$unique_states = array();
			$unique_data = array();

			$days = array_column($result_meetings, 'weekday_tinyint');
			$today_str = $this->options['weekday_start'];
			$result_meetings = array_merge(
				array_splice($result_meetings, array_search($today_str, $days)),
				array_splice($result_meetings, 0)
			);

			foreach ($result_meetings as $value) {
				$unique_states[] = $value['location_province'];
				if ( $this->options['meeting_sort'] === 'state' ) {
					$unique_data[] = $value['location_municipality'] . ', '.$value['location_province'];
				} elseif ( $this->options['meeting_sort'] === 'city' ) {
					$unique_data[] = strtoupper($value['location_municipality']);
				} elseif ( $this->options['meeting_sort'] === 'borough' ) {
					$unique_data[] = $value['location_city_subsection'];
				} elseif ( $this->options['meeting_sort'] === 'county' ) {
					$unique_data[] = $value['location_sub_province'];
				} elseif ( $this->options['meeting_sort'] === 'borough_county' ) {
					if ( $value['location_city_subsection'] !== '' ) {
						$unique_data[] = $value['location_city_subsection'];
					} else {
						$unique_data[] = $value['location_sub_province'];
					}
				} elseif ( $this->options['meeting_sort'] === 'neighborhood_city' ) {
					if ( $value['location_neighborhood'] !== '' ) {
						$unique_data[] = $value['location_neighborhood'];
					} else {
						$unique_data[] = $value['location_municipality'];
					}
				} elseif ( $this->options['meeting_sort'] === 'group' ) {
					$unique_data[] = $value['meeting_name'];
				} elseif ( $this->options['meeting_sort'] === 'weekday_area' ) {
					foreach($unique_areas as $unique_area){
						$area_data = explode(',',$unique_area);
						$area_name = $area_data[0];
						$area_id = $area_data[1];
						if ( $area_id === $value['service_body_bigint'] ) {
							$unique_data[] = $value['weekday_tinyint'] . ',' . $area_name . ',' . $area_id;
						}
					}
				} elseif ( $this->options['meeting_sort'] === 'weekday_city' ) {
					$unique_data[] = $value['weekday_tinyint'] . ',' . $value['location_municipality'];
				} elseif ( $this->options['meeting_sort'] === 'weekday_county' ) {
                    $unique_data[] = $value['weekday_tinyint'] . ',' . $value['location_sub_province'];
				} else {
					$unique_data[] = $value['weekday_tinyint'];
				}
			}

			$unique_states = array_unique($unique_states);
			if (strpos($this->options['meeting_sort'], "weekday") === false && strpos($this->options['meeting_sort'], "day") === false) {
				asort($unique_data, SORT_NATURAL | SORT_FLAG_CASE);
			}

			$unique_data = array_unique($unique_data);
			if ( $this->options['page_fold'] === 'full' || $this->options['page_fold'] === 'half' ) {
				$num_columns = 0;
			} elseif ( $this->options['page_fold'] === 'tri' ) {
				$num_columns = 3;
			} elseif ( $this->options['page_fold'] === 'quad' ) {
				$num_columns = 4;
			} elseif ( $this->options['page_fold'] === '' ) {
				$this->options['page_fold'] = 'quad';
				$num_columns = 4;
			}
			$this->mpdf->SetColumns($num_columns, '', $this->options['column_gap']);
			$header_style = "color:".$this->options['header_text_color'].";";
			$header_style .= "background-color:".$this->options['header_background_color'].";";
			$header_style .= "font-size:".$this->options['header_font_size']."pt;";
			$header_style .= "line-height:".$this->options['content_line_height'].";";

			if ( $this->options['header_uppercase'] == 1 ) {
				$header_style .= 'text-transform: uppercase;';
			}
			if ( $this->options['header_bold'] == 0 ) {
				$header_style .= 'font-weight: normal;';
			}
			if ( $this->options['header_bold'] == 1 ) {
				$header_style .= 'font-weight: bold;';
			}
			if ( $this->options['page_fold'] == 'half' ) {
				$this->write_front_page();
			}
			$x = 0;
			$this->mpdf->WriteHTML('td{font-size: '.$this->options['content_font_size']."pt;line-height:".$this->options['content_line_height'].';}',1);
			$this->mpdf->SetDefaultBodyCSS('font-size', $this->options['content_font_size'] . 'pt');			
			$this->mpdf->SetDefaultBodyCSS('line-height', $this->options['content_line_height']);
			if ( $this->options['page_fold'] == 'full' ) {
				$this->mpdf->WriteHTML("<table style='border-collapse:separate; width:100%;'>");
				$data = '';
			}
			if ( $unique_states == null ) {
				$unique_states[] = 'null';
			}			
			$this->options['meeting_template_content'] = wpautop(stripslashes($this->options['meeting_template_content']));
			$this->options['meeting_template_content'] = preg_replace('/[[:^print:]]/', ' ', $this->options['meeting_template_content']);
			foreach ($unique_states as $this_state) {
				$x++;

				if ( $this->options['meeting_sort'] === 'weekday_area' || $this->options['meeting_sort'] === 'weekday_city' || $this->options['meeting_sort'] === 'weekday_county' ) {
					$current_weekday = intval($this->options['weekday_start']);
					$show_first_weekday = true;
				}
				foreach ($unique_data as $this_unique_value) {
					if ( $this->options['meeting_sort'] === 'weekday_area' || $this->options['meeting_sort'] === 'weekday_city' || $this->options['meeting_sort'] === 'weekday_county' ) {
						$area_data = explode(',',$this_unique_value);
						$weekday_tinyint = intval($area_data[0]);
						if ( $weekday_tinyint !== $current_weekday ) {
							$current_weekday++;
							if ($current_weekday > 7) {
								$current_weekday = 1;
							}
							$show_first_weekday = true;
						}
					}
					$newVal = true;
					if ( $this->options['meeting_sort'] === 'state' && strpos($this_unique_value, $this_state) === false ) { continue; }

					foreach ($result_meetings as $meeting_value) {			
						if ( $this->options['meeting_sort'] === 'weekday_area' || $this->options['meeting_sort'] === 'weekday_city' || $this->options['meeting_sort'] === 'weekday_county' ) {
							$area_data = explode(',',$this_unique_value);
							$weekday_tinyint = $area_data[0];
							$area_name = $area_data[1];
							$service_body_bigint = $area_data[2];

							if ( $this->options['meeting_sort'] === 'weekday_city' ) {
								if ( $meeting_value['weekday_tinyint'] . ',' . $meeting_value['location_municipality'] !== $weekday_tinyint . ',' . $area_name ) { continue; }
							} elseif ( $this->options['meeting_sort'] === 'weekday_county' ) {
                                if ( $meeting_value['weekday_tinyint'] . ',' . $meeting_value['location_sub_province'] !== $weekday_tinyint . ',' . $area_name ) { continue; }
							} else {
								if ( $meeting_value['weekday_tinyint'] . ',' . $meeting_value['service_body_bigint'] !== $weekday_tinyint . ',' . $service_body_bigint ) { continue; }
							}
						} else {
							foreach($unique_areas as $unique_area){
								$area_data = explode(',',$unique_area);
								$area_id = $area_data[1];
								if ( $area_id === $meeting_value['service_body_bigint'] ) {
									$area_name = $area_data[0];
								}
							}							
						}
						if ( $this->options['meeting_sort'] === 'state' && $meeting_value['location_municipality'] . ', ' . $meeting_value['location_province'] !== $this_unique_value ) { continue; }
						if ( $this->options['meeting_sort'] === 'group' && $meeting_value['meeting_name'] !== $this_unique_value ) { continue; }
						if ( $this->options['meeting_sort'] === 'city' && strtoupper($meeting_value['location_municipality']) !== $this_unique_value ) { continue; }
						if ( $this->options['meeting_sort'] === 'borough' && $meeting_value['location_city_subsection'] !== $this_unique_value ) { continue; }
						if ( $this->options['meeting_sort'] === 'county' && $meeting_value['location_sub_province'] !== $this_unique_value ) { continue; }
						if ( $this->options['meeting_sort'] === 'borough_county' ) {
							if ( $meeting_value['location_city_subsection'] !== '' ) {
								if ( $meeting_value['location_city_subsection'] !== $this_unique_value ) { continue; }
							} else {
								if ( $meeting_value['location_sub_province'] !== $this_unique_value ) { continue; }
							}
						}
						if ( $this->options['meeting_sort'] === 'neighborhood_city' ) {
							if ( $meeting_value['location_neighborhood'] !== '' ) {
								if ( $meeting_value['location_neighborhood'] !== $this_unique_value ) { continue; }
							} else {
								if ( $meeting_value['location_municipality'] !== $this_unique_value ) { continue; }
							}
						}
						if ( $this->options['meeting_sort'] === 'day' && $meeting_value['weekday_tinyint'] !== $this_unique_value ) { continue; }
						$enFormats = explode ( ",", $meeting_value['formats'] );
						if ( $this->options['include_asm'] == 0 && in_array ( "ASM", $enFormats ) ) { continue; }
						$header = '';
						
						if ( $this->options['weekday_language'] === 'fr' ) {
							$cont = '(suite)';							
						} else if ( $this->options['weekday_language'] === 'se' ) {
                            $cont = '(forts)';
                        }  else if ( $this->options['weekday_language'] === 'dk' ) {
                            $cont = '(forts)';
                        } else {
							$cont = '(cont)';
						}

						if ( $this->options['page_fold'] !== 'full' ) {
							if ( $this->options['meeting_sort'] === 'county' || $this->options['meeting_sort'] === 'borough' ) {
								if ( $this->options['borough_suffix'] ) {$this->options['borough_suffix'] = ' ' . $this->options['borough_suffix'];}
								if ( $this->options['county_suffix'] ) {$this->options['county_suffix'] = ' ' . $this->options['county_suffix'];}
								$header_suffix = '';

								if ( $this->options['meeting_sort'] === 'borough' ) {
									if ( $this_unique_value == '' ) {
										$this_unique_data = '[NO BOROUGH DATA]';
									} else {
										$this_unique_data = $this_unique_value;
									}
									$header_suffix = $this->options['borough_suffix'];
								}
								if ( $this->options['meeting_sort'] === 'county' ) {
									if ( $this_unique_value == '' ) {
										$this_unique_data = '[NO COUNTY DATA]';
									} else {
										$this_unique_data = $this_unique_value;
									}
									$header_suffix = $this->options['county_suffix'];
								}
								if ( $newVal ) {
									$header .= "<h2 style='".$header_style."'>".$this_unique_data.''.$header_suffix."</h2>";
								} elseif ( $newCol ) {
									$header .= "<h2 style='".$header_style."'>".$this_unique_data.''.$header_suffix." " . $cont . "</h2>";
								}
							}
							if ( $this->options['meeting_sort'] === 'neighborhood_city' ) {
								if ( $this->options['neighborhood_suffix'] ) {$this->options['neighborhood_suffix'] = ' ' . $this->options['neighborhood_suffix'];}
								if ( $this->options['city_suffix'] ) {$this->options['city_suffix'] = ' ' . $this->options['city_suffix'];}

								if ( $newVal ) {
									if ( $meeting_value['location_neighborhood'] !== '' ) {
										$header .= "<h2 style='".$header_style."'>".$meeting_value['location_neighborhood'].''.$this->options['neighborhood_suffix']."</h2>";
									} elseif ( $meeting_value['location_municipality'] !== '' ) {
										$header .= "<h2 style='".$header_style."'>".$meeting_value['location_municipality'].''.$this->options['city_suffix']."</h2>";
									} else {
										$header .= "<h2 style='".$header_style."'>[NO NEIGHBORHOOD OR CITY DATA]</h2>";
									}
								} elseif ( $newCol ) {
									if ( $meeting_value['location_neighborhood'] !== '' ) {
										$header .= "<h2 style='".$header_style."'>".$meeting_value['location_neighborhood'].''.$this->options['neighborhood_suffix']." " . $cont . "</h2>";
									} elseif ( $meeting_value['location_municipality'] !== '' ) {
										$header .= "<h2 style='".$header_style."'>".$meeting_value['location_municipality'].''.$this->options['city_suffix']." " . $cont . "</h2>";
									} else {
										$header .= "<h2 style='".$header_style."'>[NO NEIGHBORHOOD OR CITY DATA] " . $cont . "</h2>";
									}
								}
							}
							if ( $this->options['meeting_sort'] === 'weekday_area' || $this->options['meeting_sort'] === 'weekday_city' || $this->options['meeting_sort'] === 'weekday_county' ) {
								if ( $newVal ) {
									if ( $show_first_weekday === true ) {
										if ( $current_weekday === $this->options['weekday_start'] ) {
											$header .= "<h2 style='".$header_style."'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])."</h2>";
										} else {
											$header .= "<h2 style='".$header_style."margin-top:2pt;'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])."</h2>";
										}
										$show_first_weekday = false;
									} elseif ( utf8_encode($this->mpdf->y) == $this->options['margin_top'] ) {
										$header .= "<h2 style='".$header_style."'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])." " . $cont . "</h2>";
									}
									
									$header .= $this->options['sub_header_shown'] == 1 ? "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>".$area_name."</p>" : "";
									
                                } elseif ( utf8_encode($this->mpdf->y) == $this->options['margin_top'] ) {
									$header .= "<h2 style='".$header_style."'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])." " . $cont . "</h2>";
									$header .= $this->options['sub_header_shown'] == 1 ? "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>".$area_name."</p>" : "";
								}
							}
							if ( $this->options['meeting_sort'] === 'city' || $this->options['meeting_sort'] === 'state' ) {
								if ( $meeting_value['location_municipality'] == '' ) {
									$meeting_value['location_municipality'] = '[NO CITY DATA IN BMLT]';
								}
								if ( $newVal ) {
									$header .= "<h2 style='".$header_style."'>".$meeting_value['location_municipality']."</h2>";
								} elseif ( $newCol ) {
									$header .= "<h2 style='".$header_style."'>".$meeting_value['location_municipality']." " . $cont . "</h2>";
								}
							}
							if ( $this->options['meeting_sort'] === 'group' ) {
								if ( $newVal ) {
									$header .= "<h2 style='".$header_style."'>".$meeting_value['meeting_name']."</h2>";
								} elseif ( $newCol ) {
									$header .= "<h2 style='".$header_style."'>".$meeting_value['meeting_name']." " . $cont . "</h2>";
								}
							}
							if ( $this->options['meeting_sort'] === 'day' ) {
								if ( $newVal ) {
									$header .= "<h2 style='".$header_style."'>".$this->getday($this_unique_value, false, $this->options['weekday_language'])."</h2>";
								} elseif ( $newCol ) {
									$header .= "<h2 style='".$header_style."'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])." " . $cont . "</h2>";
								}
							}
							if ( $this->options['meeting_sort'] === 'borough_county' ) {
								if ( $this->options['borough_suffix'] ) {$this->options['borough_suffix'] = ' ' . $this->options['borough_suffix'];}
								if ( $this->options['county_suffix'] ) {$this->options['county_suffix'] = ' ' . $this->options['county_suffix'];}
									
								if ( $newVal ) {
									if ( $meeting_value['location_city_subsection'] !== '' ) {
										$header .= "<h2 style='".$header_style."'>".$meeting_value['location_city_subsection'].''.$this->options['borough_suffix']."</h2>";
									} elseif ( $meeting_value['location_sub_province'] !== '' ) {
										$header .= "<h2 style='".$header_style."'>".$meeting_value['location_sub_province'].''.$this->options['county_suffix']."</h2>";
									} else {
										$header .= "<h2 style='".$header_style."'>[NO BOROUGH OR COUNTY DATA]</h2>";
									}
								} elseif ( $newCol ) {
									if ( $meeting_value['location_city_subsection'] !== '' ) {
										$header .= "<h2 style='".$header_style."'>".$meeting_value['location_city_subsection'].''.$this->options['borough_suffix']." " . $cont . "</h2>";
									} elseif ( $meeting_value['location_sub_province'] !== '' ) {
										$header .= "<h2 style='".$header_style."'>".$meeting_value['location_sub_province'].''.$this->options['county_suffix']." " . $cont . "</h2>";
									} else {
										$header .= "<h2 style='".$header_style."'>[NO BOROUGH OR COUNTY DATA] " . $cont . "</h2>";
									}
								}
							}
						}
						$newVal = false;
						$newCol = false;
						$duration = explode(':',$meeting_value[duration_time]);
						$minutes = intval($duration[0])*60 + intval($duration[1]) + intval($duration[2]);
						$duration_m = $minutes;
						$duration_h = rtrim(rtrim(number_format($duration_m/60,2),0),'.');
						$space = ' ';
						if ( $this->options['remove_space'] == 1 ) {
							$space = '';
						}
						if ( $this->options['time_clock'] == null || $this->options['time_clock'] == '12' || $this->options['time_option'] == '' ) {
							$time_format = "g:i".$space."A";
							
						} elseif ( $this->options['time_clock'] == '24fr' ) {
							$time_format = "H\hi";
						} else {
							$time_format = "H:i";
						}
						if ( $this->options['time_option'] == 1 || $this->options['time_option'] == '' ) {
							$meeting_value[start_time] = date($time_format,strtotime($meeting_value[start_time]));
							if ( $meeting_value[start_time] == '12:00PM' || $meeting_value[start_time] == '12:00 PM' ) {
								$meeting_value[start_time] = 'NOON';
							}
						} elseif ( $this->options['time_option'] == '2' ) {
							$addtime = '+ ' . $minutes . ' minutes';
							$end_time = date ($time_format,strtotime($meeting_value[start_time] . ' ' . $addtime));
							$meeting_value[start_time] = date($time_format,strtotime($meeting_value[start_time]));
							$meeting_value[start_time] = $meeting_value[start_time].$space.'-'.$space.$end_time;
						} elseif ( $this->options['time_option'] == '3' ) {
							$time_array = array("1:00", "2:00", "3:00", "4:00", "5:00", "6:00", "7:00", "8:00", "9:00", "10:00", "11:00", "12:00");
							$temp_start_time = date("g:i",strtotime($meeting_value[start_time]));
							$temp_start_time_2 = date("g:iA",strtotime($meeting_value[start_time]));
							if ( $temp_start_time_2 == '12:00PM' ) {
								$start_time = 'NOON';
							} elseif ( in_array($temp_start_time, $time_array) ) {
								$start_time = date("g",strtotime($meeting_value[start_time]));
							} else {
								$start_time = date("g:i",strtotime($meeting_value[start_time]));
							}
							$addtime = '+ ' . $minutes . ' minutes';
							$temp_end_time = date ("g:iA",strtotime($meeting_value[start_time] . ' ' . $addtime));
							$temp_end_time_2 = date ("g:i",strtotime($meeting_value[start_time] . ' ' . $addtime));
							if ( $temp_end_time == '12:00PM' ) {
								$end_time = 'NOON';
							} elseif ( in_array($temp_end_time_2, $time_array) ) {
								$end_time = date("g".$space."A",strtotime($temp_end_time));
							} else {
								$end_time = date("g:i".$space."A",strtotime($temp_end_time));
							}
							$meeting_value[start_time] = $start_time.$space.'-'.$space.$end_time;
						}
						if ( $this->options['page_fold'] !== 'full' ) {
							if ( isset($meeting_value['email_contact']) && $meeting_value['email_contact'] !== '' && $this->options['include_meeting_email'] == 1 ) {
								$str = explode("#@-@#",$meeting_value['email_contact']);
								$meeting_value['email_contact'] = $str['2'];
							} else {
								$meeting_value['email_contact'] = '';
							}
							$data = $this->options['meeting_template_content'];
							$data = str_replace("&nbsp;", " ", $data);
							$data = str_replace('borough', $meeting_value['location_city_subsection'], $data);	//borough
							$data = str_replace('day_abbr', $this->getday($meeting_value['weekday_tinyint'], true, $this->lang), $data);
							$data = str_replace('weekday_tinyint_abbr', $this->getday($meeting_value['weekday_tinyint'], true, $this->lang), $data);
							$data = str_replace('day', $this->getday($meeting_value['weekday_tinyint'], false, $this->lang), $data);
							$data = str_replace('weekday_tinyint', $this->getday($meeting_value['weekday_tinyint'], false, $this->lang), $data);
							$data = str_replace('start_time', $meeting_value['start_time'], $data);
							$data = str_replace('time', $meeting_value['start_time'], $data);
							
							$meeting_value['formats'] = str_replace(',', ', ', $meeting_value['formats']);
							$data = str_replace('formats', $meeting_value['formats'], $data);
							$data = str_replace('duration_h', $duration_h, $data);
							$data = str_replace('hrs', $duration_h, $data);
							$data = str_replace('duration_m', $duration_m, $data);
							$data = str_replace('mins', $duration_m, $data);
							$data = str_replace('location_text', $meeting_value['location_text'], $data);
							$data = str_replace('location_info', $meeting_value['location_info'], $data);
							$data = str_replace('location_street', $meeting_value['location_street'], $data);
							$data = str_replace('bus_line', $meeting_value['bus_line'], $data);						
							$data = str_replace('state', $meeting_value['location_province'], $data);							
							$data = str_replace('street', $meeting_value['location_street'], $data);
							$data = str_replace('neighborhood', $meeting_value['location_neighborhood'], $data);
							$data = str_replace('location_municipality', $meeting_value['location_municipality'], $data);
							$data = str_replace('city', $meeting_value['location_municipality'], $data);
							$data = str_replace('location_province', $meeting_value['location_province'], $data);
							$data = str_replace('location_postal_code_1', $meeting_value['location_postal_code_1'], $data);
							$data = str_replace('zip', $meeting_value['location_postal_code_1'], $data);
							$data = str_replace('location', $meeting_value['location_text'], $data);						
							$data = str_replace('info', $meeting_value['location_info'], $data);
							$data = str_replace('area_name', $area_name, $data);
							$data = str_replace('area_i', substr($area_name, 0, 1), $data);
							$data = str_replace('area', $area_name, $data);
							$data = str_replace('location_city_subsection', $meeting_value['location_city_subsection'], $data);	//borough
							$data = str_replace('county', $meeting_value['location_sub_province'], $data);			//county
							$data = str_replace('location_sub_province', $meeting_value['location_sub_province'], $data);			//county
                            $data = str_replace('meeting_name', $meeting_value['meeting_name'], $data);
							$data = str_replace('group', $meeting_value['meeting_name'], $data);
							$data = str_replace('comments', $meeting_value['comments'], $data);
							$data = str_replace('email_contact', $meeting_value['email_contact'], $data);
							$data = str_replace('email', $meeting_value['email_contact'], $data);
							$data = str_replace('<p></p>', '', $data);
							$data = str_replace('<em></em>', '', $data);
							$data = str_replace('<em> </em>', '', $data);
							$data = str_replace('()', '', $data);
							$data = str_replace('    ', ' ', $data);
							$data = str_replace('   ', ' ', $data);
							$data = str_replace('  ', ' ', $data);
							$data = str_replace('<br/>', 'line_break', $data);
							$data = str_replace('<br />', 'line_break', $data);
							$data = str_replace('line_break line_break', '<br />', $data);
							$data = str_replace('line_breakline_break', '<br />', $data);
							$data = str_replace('line_break', '<br />', $data);
							$data = str_replace('<br />,', '<br />', $data);
							$data = str_replace(', <br />', '<br />', $data);
							$data = str_replace(',<br />', '<br />', $data);
							$data = str_replace(", , ,", ",", $data);					
							$data = str_replace(", *,", ",", $data);							
							$data = str_replace(", ,", ",", $data);
							$data = str_replace(" , ", " ", $data);
							$data = str_replace(", (", " (", $data);
							$data = str_replace(',</', '</', $data);
							$data = str_replace(', </', '</', $data);							
						} else {
							$data = '<tr>';
							if ( $this->options['meeting_sort'] == 'group' ) {
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['meeting_name']."</td>";
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->lang)."</td>".$meeting_value['start_time'];
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['location_text']."</td>";
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['location_street']."</td>";
								$data .= "<td style='vertical-align:top;'>".$meeting_value['location_municipality']."</td>";
							} elseif ( $this->options['meeting_sort'] == 'city' || $this->options['meeting_sort'] == 'state' || $this->options['meeting_sort'] == 'borough' || $this->options['meeting_sort'] == 'county' || $this->options['meeting_sort'] == 'borough_county' || $this->options['meeting_sort'] == 'neighborhood_city' ) {
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['location_municipality']."</td>";
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->lang)."</td>".$meeting_value['start_time'];
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['meeting_name']."</td>";
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['location_text']."</td>";
								$data .= "<td style='vertical-align:top;'>".$meeting_value['location_street']."</td>";
							} else {
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->lang)."</td>".$meeting_value['start_time'];
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['location_municipality']."</td>";
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['meeting_name']."</td>";
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['location_text']."</td>";
								$data .= "<td style='vertical-align:top;'>".$meeting_value['location_street']."</td>";
							}
							$data .= '</tr>';
						}

						$data = $header . $data;
											
						$data = mb_convert_encoding($data, 'HTML-ENTITIES');
						
						$data = utf8_encode($data);
						$this->mpdf->WriteHTML($data);
						$ph = intval($this->options['margin_bottom']) + intval($this->options['margin_top']) + $this->mpdf->y + -intval($this->options['page_height_fix']);

                        $ph_footer_fix_top = 0;
                        $ph_footer_fix_bot = 0;

						if (intval($this->options['margin_bottom']) < 5) {
							$ph_footer_fix_bot = 5 - intval($this->options['margin_bottom']);
						}
						if (intval($this->options['margin_top']) < 5) {
							$ph_footer_fix_top = 5 - intval($this->options['top']);
						}

						$DAY_HEADER_HEIGHT = 22;
						$PH_FOOTER_MM = $DAY_HEADER_HEIGHT + $ph_footer_fix_top + $ph_footer_fix_bot;

						if ( strpos($this->options['front_page_content'], 'sethtmlpagefooter') !== false ) {
							$ph += $PH_FOOTER_MM;
						}

						if ( $ph + $PH_FOOTER_MM >= $this->mpdf->h  ) {
							$newCol = true;
							if ( $this->options['page_fold'] === 'half' ) {
								$this->mpdf->WriteHTML("<pagebreak>");
							} else {
								$this->mpdf->WriteHTML("<columnbreak />");
							}
						}
					}
				}
				if ( $this->options['meeting_sort'] !== 'state' ) { break; }
			}

			if ( $this->options['page_fold'] == 'full' ) {
				$this->mpdf->WriteHTML('</table>');
			}
			if ( $this->options['page_fold'] !== 'half' && $this->options['page_fold'] !== 'full' ) {
				$this->write_custom_section();
				$this->write_front_page();
			}
			if ( $this->options['page_fold'] == 'half' ) {
				if ( trim($this->options['last_page_content']) !== '' ) {
					$this->write_last_page();
				}
			}
			$this->mpdf->SetDisplayMode('fullpage','two');
			$upload_dir = wp_upload_dir();
			$FilePath = ABSPATH . "current_meeting_list_".strtolower( date ( "njYghis" ) ).".pdf";
			if ( $this->options['page_fold'] == 'half' ) {
				$this->mpdf->Output($FilePath,'F');
				if ( $this->options['page_size'] == '5inch' ) {
					$this->mpdftmp=new mPDF([
                        'mode' => $mode,
                        'tempDir' => get_temp_dir(),
                        'format' => array(197.2,279.4),
                        'default_font_size' => '',
                        'margin_left' => 0,
                        'margin_right' => 0,
                        'margin_top' => 0,
                        'margin_bottom' => 0,
                        'margin_footer' => 6,
                        'orientation' => 'L'
                    ]);
				} else {
					$this->mpdftmp=new mPDF([
                        'mode' => $mode,
                        'tempDir' => get_temp_dir(),
                        'format' => 'A4-L',
                        'default_font_size' => '7',
                        'margin_left' => 0,
                        'margin_right' => 0,
                        'margin_top' => 0,
                        'margin_bottom' => 0,
                        'margin_footer' => 0,
                        'orientation' => 'P'
                    ]);
				}
				$this->mpdftmp->SetImportUse();    
				$ow = $this->mpdftmp->h;
				$oh = $this->mpdftmp->w;
				$pw = $this->mpdftmp->w / 2;
				$ph = $this->mpdftmp->h;
				$pagecount = $this->mpdftmp->SetSourceFile($FilePath);
				$pp = $this->get_booklet_pages($pagecount);
				foreach($pp AS $v) {
					$this->mpdftmp->AddPage(); 
					if ($v[0]>0 & $v[0]<=$pagecount) {
						$tplIdx = $this->mpdftmp->ImportPage($v[0]);
						$this->mpdftmp->UseTemplate($tplIdx, 0, 0, $pw, $ph);
					}
					if ($v[1]>0 & $v[1]<=$pagecount) {
						$tplIdx = $this->mpdftmp->ImportPage($v[1]);
						$this->mpdftmp->UseTemplate($tplIdx, $pw, 0, $pw, $ph);
					}
				}					
				unlink($FilePath);
				$FilePath = ABSPATH . "current_meeting_list_".strtolower( date ( "njYghis" ) ).".pdf";
				$this->mpdf = $this->mpdftmp;
			}
			if ( $this->options['include_protection'] == 1 ) {
				// 'copy','print','modify','annot-forms','fill-forms','extract','assemble','print-highres'
				$this->mpdf->SetProtection(array('copy','print','print-highres'), '', $this->options['protection_password']);
			}
			if ( intval($this->options['cache_time']) > 0 && ! isset($_GET['nocache']) ) {
				$content = $this->mpdf->Output('', 'S');
				$content = bin2hex($content);
				$transient_key = 'bmlt_ml_'.md5($this->options['root_server'].$services);
				set_transient( $transient_key, $content, intval($this->options['cache_time']) * HOUR_IN_SECONDS );
			}			
			$FilePath = "current_meeting_list_".strtolower( date ( "njYghis" ) ).".pdf";
				
			$this->mpdf->Output($FilePath,'I');
			exit;
		}

		function get_booklet_pages($np, $backcover=true) {
			$lastpage = $np;
			$np = 4*ceil($np/4);
			$pp = array();
			for ($i=1; $i<=$np/2; $i++) {
				$p1 = $np - $i + 1;
				if ($backcover) {    
					if ($i == 1) { $p1 = $lastpage; }
					else if ($p1 >= $lastpage) { $p1 = 0; }
				}
				if ($i % 2 == 1) { 
					$pp[] = array( $p1,  $i ); 
				}
				else { 
					$pp[] = array( $i, $p1 ); 
				}
			}
			return $pp;
		}

		function write_front_page() {
			$this->mpdf->WriteHTML('td{font-size: '.$this->options['front_page_font_size']."pt;line-height:".$this->options['front_page_line_height'].';}',1);
			$this->mpdf->SetDefaultBodyCSS('line-height', $this->options['front_page_line_height']);
			$this->mpdf->SetDefaultBodyCSS('font-size', $this->options['front_page_font_size'] . 'pt');
			$this->options['front_page_content'] = str_replace('[format_codes_used_basic]', $this->write_formats($this->formats_used, 'front_page'), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[format_codes_used_detailed]', $this->write_detailed_formats($this->formats_used, 'front_page'), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[format_codes_used_basic_es]', $this->write_formats($this->formats_spanish, 'front_page'), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[format_codes_used_detailed_es]', $this->write_detailed_formats($this->formats_spanish, 'front_page'), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[format_codes_all_basic]', $this->write_formats($this->formats_all, 'front_page'), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[format_codes_all_detailed]', $this->write_detailed_formats($this->formats_all, 'front_page'), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[meeting_count]', $this->meeting_count, $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('<p>[service_meetings]</p>', $this->write_service_meetings($this->options['front_page_font_size'], $this->options['front_page_line_height'] ), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[service_meetings]', $this->write_service_meetings($this->options['front_page_font_size'], $this->options['front_page_line_height']), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('<h2>', '<h2 style="font-size:'.$this->options['front_page_font_size'] . 'pt!important;">', $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('<div>[page_break]</div>', '<pagebreak />', $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('<p>[page_break]</p>', '<pagebreak />', $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[page_break]', '<pagebreak />', $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('<!--nextpage-->', '<pagebreak />', $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[date]", strtoupper( date ( "F Y" ) ), $this->options['front_page_content']);
			if ( strpos($this->options['front_page_content'], '[month_lower_fr') !== false ) {
				setlocale( LC_TIME, 'fr_FR' );
				$month = ucfirst(utf8_encode(strftime("%B")));
				setlocale(LC_TIME,NULL);
				$this->options['front_page_content'] = str_replace("[month_lower_fr]", $month, $this->options['front_page_content']);
			}
			
			if ( strpos($this->options['front_page_content'], '[month_upper_fr') !== false ) {
				setlocale( LC_TIME, 'fr_FR' );
				$month = utf8_encode(strftime("%^B"));
				setlocale(LC_TIME,NULL);;
				$this->options['front_page_content'] = str_replace("[month_upper_fr]", $month, $this->options['front_page_content']);
			}
			
			if ( strpos($this->options['front_page_content'], '[month_lower_es') !== false ) {
				setlocale( LC_TIME, 'es_ES' );
				$month = ucfirst(utf8_encode(strftime("%B")));
				setlocale(LC_TIME,NULL);
				$this->options['front_page_content'] = str_replace("[month_lower_es]", $month, $this->options['front_page_content']);
			}
			
			if ( strpos($this->options['front_page_content'], '[month_upper_es') !== false ) {
				setlocale( LC_TIME, 'es_ES' );
				$month = utf8_encode(strftime("%^B"));
				setlocale(LC_TIME,NULL);
				$this->options['front_page_content'] = str_replace("[month_upper_es]", $month, $this->options['front_page_content']);
			}
			$this->options['front_page_content'] = str_replace("[month_lower]", date ( "F" ), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[month_upper]", strtoupper( date ( "F" ) ), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[month]", strtoupper( date ( "F" ) ), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[day]", strtoupper( date ( "j" ) ), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[year]", strtoupper( date ( "Y" ) ), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[service_body]", strtoupper($this->options['service_body_1']), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[service_body_1]", strtoupper($this->options['service_body_1']), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[service_body_2]", strtoupper($this->options['service_body_2']), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[service_body_3]", strtoupper($this->options['service_body_3']), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[service_body_4]", strtoupper($this->options['service_body_4']), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace("[service_body_5]", strtoupper($this->options['service_body_5']), $this->options['front_page_content']);
			$querystring_custom_items = array();
			preg_match_all('/(\[querystring_custom_\d+\])/', $this->options['front_page_content'], $querystring_custom_items);
			foreach ($querystring_custom_items[0] as $querystring_custom_item) {
				$mod_qs_ci = str_replace("]", "", str_replace("[", "" ,$querystring_custom_item));
				$this->options['front_page_content'] = str_replace($querystring_custom_item, (isset($_GET[$mod_qs_ci]) ? $_GET[$mod_qs_ci] : "NOT SET"), $this->options['front_page_content']);
			}
			$this->options['front_page_content'] = str_replace("[area]", strtoupper($this->options['service_body_1']), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[page_break no_page_number]', '<sethtmlpagefooter name="" value="0" /><pagebreak />', $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[start_page_numbers]', '<sethtmlpagefooter name="MyFooter" page="ALL" value="1" />', $this->options['front_page_content']);
			$this->options['front_page_content'] = mb_convert_encoding($this->options['front_page_content'], 'HTML-ENTITIES');
			$this->mpdf->WriteHTML(utf8_encode(wpautop(stripslashes($this->options['front_page_content']))));

		}

		function write_last_page() {
			$this->mpdf->WriteHTML('td{font-size: '.$this->options['last_page_font_size']."pt;line-height:".$this->options['last_page_line_height'].';}',1);
			$this->mpdf->SetDefaultBodyCSS('font-size', $this->options['last_page_font_size'] . 'pt');
			$this->mpdf->SetDefaultBodyCSS('line-height', $this->options['last_page_line_height']);
			$this->options['last_page_content'] = str_replace('[format_codes_used_basic]', $this->write_formats($this->formats_used, 'last_page'), $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('[format_codes_used_detailed]', $this->write_detailed_formats($this->formats_used, 'last_page'), $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('[format_codes_all_basic]', $this->write_formats($this->formats_all, 'last_page'), $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('[format_codes_all_detailed]', $this->write_detailed_formats($this->formats_all, 'last_page'), $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('[meeting_count]', $this->meeting_count, $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('<p>[service_meetings]</p>', $this->write_service_meetings($this->options['last_page_font_size'], $this->options['last_page_line_height']), $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('[service_meetings]', $this->write_service_meetings($this->options['last_page_font_size'], $this->options['last_page_line_height']), $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('<h2>', '<h2 style="font-size:'.$this->options['last_page_font_size'] . 'pt!important;">', $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('<div>[page_break]</div>', '<pagebreak />', $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('<p>[page_break]</p>', '<pagebreak />', $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('[page_break]', '<pagebreak />', $this->options['last_page_content']);
			$this->options['last_page_content'] = str_replace('<!--nextpage-->', '<pagebreak />', $this->options['last_page_content']);
			$this->options['last_page_content'] = mb_convert_encoding($this->options['last_page_content'], 'HTML-ENTITIES');
			$this->mpdf->WriteHTML(utf8_encode(wpautop(stripslashes($this->options['last_page_content']))));
		}

		function write_custom_section() {
			$this->mpdf->SetDefaultBodyCSS('line-height', $this->options['custom_section_line_height']);
			$this->mpdf->SetDefaultBodyCSS('font-size', $this->options['custom_section_font_size'] . 'pt');
			$this->options['custom_section_content'] = str_replace('[format_codes_used_basic_es]', $this->write_formats($this->formats_spanish, 'custom_section'), $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('[format_codes_used_detailed_es]', $this->write_detailed_formats($this->formats_spanish, 'custom_section'), $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('[format_codes_used_basic]', $this->write_formats($this->formats_used, 'custom_section'), $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('[format_codes_used_detailed]', $this->write_detailed_formats($this->formats_used, 'custom_section'), $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('[format_codes_all_basic]', $this->write_formats($this->formats_all, 'custom_section'), $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('[format_codes_all_detailed]', $this->write_detailed_formats($this->formats_all, 'custom_section'), $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('[meeting_count]', $this->meeting_count, $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('<p>[service_meetings]</p>', $this->write_service_meetings($this->options['custom_section_font_size'], $this->options['last_page_line_height']), $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('[service_meetings]', $this->write_service_meetings($this->options['custom_section_font_size'], $this->options['last_page_line_height']), $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('<p>[new_column]</p>', '<columnbreak />', $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('[new_column]', '<columnbreak />', $this->options['custom_section_content']);
			$this->options['custom_section_content'] = str_replace('<h2>', '<h2 style="font-size:'.$this->options['custom_section_font_size'] . 'pt!important;">', $this->options['custom_section_content']);
			$this->mpdf->WriteHTML('td{font-size: '.$this->options['custom_section_font_size']."pt;line-height:".$this->options['custom_section_line_height'].';}',1);
			$this->options['custom_section_content'] = mb_convert_encoding($this->options['custom_section_content'], 'HTML-ENTITIES');
			$this->mpdf->WriteHTML(utf8_encode(wpautop(stripslashes($this->options['custom_section_content']))));
		}

		function write_formats($formats, $page) {
			if ( $formats == Null ) { return ''; }
			$this->mpdf->WriteHTML('td{font-size: '.$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height'].';}',1);
			$data .= "<table style='width:100%;font-size:".$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height'].";'>";
			$countmax = count ( $formats );
			for ( $count = 0; $count < $countmax; $count++ ) {
				$data .= '<tr>';
				$data .= "<td style='padding-left:4px;border:1px solid #555;border-right:0;width:12%;vertical-align:top;'>".$formats[$count]['key_string']."</td>";
				$data .= "<td style='border: 1px solid #555;border-left:0;width:38%;vertical-align:top;'>".$formats[$count]['name_string']."</td>";
				$count++;
				$data .= "<td style='padding-left:4px;border: 1px solid #555;border-right:0;width:12%;vertical-align:top;'>".$formats[$count]['key_string']."</td>";
				$data .= "<td style='border: 1px solid #555;border-left:0;width:38%;vertical-align:top;'>".$formats[$count]['name_string']."</td>";
				$data .= "</tr>";
			}
			$data .= "</table>";
			return $data;
		}

		function write_detailed_formats($formats, $page) {
			if ( $formats == Null ) { return ''; }
			$this->mpdf->WriteHTML('td{font-size: '.$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height'].';}',1);
			$data = "<table style='font-size:".$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height']."; width: 100%;'>";
			$countmax = count ( $formats );
			for ( $count = 0; $count < $countmax; $count++ ) {
				$data .= '<tr>';
				$data .= "<td style='border-bottom:1px solid #555;width:8%;vertical-align:top;'><span style='font-size:".$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_page_line_height'].";font-weight:bold;'>".$formats[$count]['key_string']."</span></td>";
				$data .= "<td style='border-bottom:1px solid #555;width:92%;vertical-align:top;'><span style='font-size:".$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_page_line_height'].";'>(".$formats[$count]['name_string'].") ".$formats[$count]['description_string']."</span></td>";
				$data .= "</tr>";
			}
			$data .= "</table>";
			return $data;
		}

		function write_service_meetings($font_size, $line_height) {
			if ( $this->service_meeting_result == Null ) {
				return '';
			}
			$data = '';
			$x = 0;
			foreach ($this->service_meeting_result as $value) {
				$enFormats = explode ( ",", $value['formats'] );
				if ( ! in_array ( "ASM", $enFormats )  ) {
					continue;
				}
				$x++;
			}
			if ( $x == 0 ) {
				return $data;
			}
			$data .= "<table style='line-height:".$line_height."; font-size:".$font_size."pt; width:100%;'>";
			foreach ($this->service_meeting_result as $value) {
				$enFormats = explode ( ",", $value['formats'] );
				if ( ! in_array ( "ASM", $enFormats )  ) {
					continue;
				}
				$display_string = '<strong>'.$value['meeting_name'].'</strong>';
				if ( !strstr($value['comments'],'Open Position') ) {
					$display_string .= '<strong> - ' . date ('g:i A',strtotime($value['start_time'])) . '</strong>';
				}

				if ( trim ( $value['location_text'] ) ) {
					$display_string .= ' - '.trim ( $value['location_text'] );
				}
				if ( trim ( $value['location_street'] ) ) {
					$display_string .= ' - ' . trim ( $value['location_street'] );
				}
				if ( trim ( $value['location_city_subsection'] ) ) {
					$display_string .= ' ' . trim ( $value['location_city_subsection'] );
				}
				if ( trim ( $value['location_neighborhood'] ) ) {
					$display_string .= ' ' . trim ( $value['location_neighborhood'] );
				}
				if ( trim ( $value['location_municipality'] ) ) {
					$display_string .= ' '.trim ( $value['location_municipality'] );
				}
				if ( trim ( $value['location_province'] ) ) {
					//$display_string .= ' '.trim ( $value['location_province'] );
				}
				if ( trim ( $value['location_postal_code_1'] ) ) {
					$display_string .= ' ' . trim ( $value['location_postal_code_1'] );
				}
				if ( trim ( $value['location_info'] ) ) {
					$display_string .= " (".trim ( $value['location_info'] ).")";
				}

				if ( isset($value['email_contact']) && $value['email_contact'] != '' && $this->options['include_meeting_email'] == 1 ) {
					$str = explode("#@-@#",$value['email_contact']);
					$value['email_contact'] = $str['2'];
					$value['email_contact'] = ' (<i>'.$value['email_contact'].'</i>)';
				} else {
					$value['email_contact'] = '';
				}
				$display_string .=  $value['email_contact'];
				$data .= "<tr><td style='border-bottom: 1px solid #555;'>".$display_string."</td></tr>";
			}
			$data .= "</table>";
			return $data;
		}

		/**
		* @desc Adds the options sub-panel
		*/
		function admin_menu_link() 	{
			global $my_admin_page;
			$my_admin_page = add_menu_page( 'Meeting List', 'Meeting List', 'manage_options', basename(__FILE__), array(&$this, 'admin_options_page'), 'dashicons-admin-page');
		}

		function bmltrootserverurl_meta_box() {
			global $connect;
			?>
			<label for="root_server">BMLT Server: </label>
			<input class="bmlt-input" id="root_server" type="text" size="80" name="root_server" value="<?php echo $this->options['root_server'] ;?>" /> <?php echo $connect; ?>
			<p><a target="_blank" href="http://bmlt.magshare.net/what-is-the-bmlt/hit-parade/#bmlt-server">BMLT Server Implementations</a></p>
			<?php			   
		}

		/**
		* Adds settings/options page
		*/
		function admin_options_page() {
			
		?>		
			<div class="connecting"></div>
			<div class="saving"></div>
			<div style="display:none;">
				<form method="POST" id="three_column_default_settings" name="three_column_default_settings" enctype="multipart/form-data">
					<?php wp_nonce_field( 'pwsix_submit_three_column', 'pwsix_submit_three_column' ); ?>
					<input type="hidden" name="pwsix_action" value="three_column_default_settings" />
					<div id="basicModal1">
						<p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
						<p>Consider backing up your settings by using the Backup/Restore Tab.</p>
					</div>
				</form>
				<form method="POST" id="four_column_default_settings" name="four_column_default_settings" enctype="multipart/form-data">
					<?php wp_nonce_field( 'pwsix_submit_four_column', 'pwsix_submit_four_column' ); ?>
					<input type="hidden" name="pwsix_action" value="four_column_default_settings" />
					<div id="basicModal2">
						<p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
						<p>Consider backing up your settings by using the Backup/Restore Tab.</p>
					</div>
				</form>
				<form method="POST" id="booklet_default_settings" name="booklet_default_settings" enctype="multipart/form-data">
					<?php wp_nonce_field( 'pwsix_submit_booklet', 'pwsix_submit_booklet' ); ?>
					<input type="hidden" name="pwsix_action" value="booklet_default_settings" />
					<div id="basicModal3">
						<p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
						<p>Consider backing up your settings by using the Backup/Restore Tab.</p>
					</div>
				</form>
			</div>
			<?php
			if ( !isset($_POST['bmltmeetinglistsave']) ) {
				$_POST['bmltmeetinglistsave'] = false;
			}
			if ($_POST['bmltmeetinglistsave']) {
				if (!wp_verify_nonce($_POST['_wpnonce'], 'bmltmeetinglistupdate-options'))
					die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$this->options['front_page_content'] = wp_kses_post($_POST['front_page_content']);
				$this->options['last_page_content'] = wp_kses_post($_POST['last_page_content']);
				$this->options['front_page_line_height'] = $_POST['front_page_line_height'];   
				$this->options['front_page_font_size'] = floatval($_POST['front_page_font_size']);
				$this->options['last_page_font_size'] = floatval($_POST['last_page_font_size']);
				$this->options['last_page_line_height'] = floatval($_POST['last_page_line_height']);
				$this->options['content_font_size'] = floatval($_POST['content_font_size']);
				$this->options['header_font_size'] = floatval($_POST['header_font_size']);
				$this->options['header_text_color'] = validate_hex_color($_POST['header_text_color']);
				$this->options['header_background_color'] = validate_hex_color($_POST['header_background_color']);
				$this->options['header_uppercase'] = intval($_POST['header_uppercase']);
				$this->options['header_bold'] = intval($_POST['header_bold']);
				$this->options['sub_header_shown'] = intval($_POST['sub_header_shown']);
				$this->options['page_height_fix'] = intval($_POST['page_height_fix']);
				$this->options['column_gap'] = intval($_POST['column_gap']);
				$this->options['margin_right'] = intval($_POST['margin_right']);
				$this->options['margin_left'] = intval($_POST['margin_left']);
				$this->options['margin_bottom'] = intval($_POST['margin_bottom']);
				$this->options['margin_top'] = intval($_POST['margin_top']);
				$this->options['page_size'] = sanitize_text_field($_POST['page_size']);
				$this->options['page_orientation'] = validate_page_orientation($_POST['page_orientation']);
				$this->options['page_fold'] = sanitize_text_field($_POST['page_fold']);
				$this->options['meeting_sort'] = sanitize_text_field($_POST['meeting_sort']);
				$this->options['borough_suffix'] = sanitize_text_field($_POST['borough_suffix']);
				$this->options['county_suffix'] = sanitize_text_field($_POST['county_suffix']);
				$this->options['neighborhood_suffix'] = sanitize_text_field($_POST['neighborhood_suffix']);
				$this->options['city_suffix'] = sanitize_text_field($_POST['city_suffix']);
				$this->options['meeting_template'] = wp_kses_post($_POST['meeting_template']);
				$this->options['meeting_template_content'] = wp_kses_post($_POST['meeting_template_content']);
				$this->options['column_line'] = boolval($_POST['column_line']); #seperator
				$this->options['col_color'] = validate_hex_color($_POST['col_color']);
				$this->options['custom_section_content'] = wp_kses_post($_POST['custom_section_content']);
				$this->options['custom_section_line_height'] = intval($_POST['custom_section_line_height']);
				$this->options['custom_section_font_size'] = floatval($_POST['custom_section_font_size']);
				$this->options['pagenumbering_font_size'] = floatval($_POST['pagenumbering_font_size']);
				$this->options['include_zip'] = boolval($_POST['include_zip']);
				$this->options['used_format_1'] = sanitize_text_field($_POST['used_format_1']);
				$this->options['include_meeting_email'] = boolval($_POST['include_meeting_email']);
				$this->options['recurse_service_bodies'] = intval($_POST['recurse_service_bodies']);
				$this->options['extra_meetings_enabled'] = intval($_POST['extra_meetings_enabled']);
				$this->options['include_protection'] = boolval($_POST['include_protection']);
				$this->options['weekday_language'] = sanitize_text_field($_POST['weekday_language']);
				$this->options['weekday_start'] = sanitize_text_field($_POST['weekday_start']);
				$this->options['include_asm'] = boolval($_POST['include_asm']);
				$this->options['bmlt_login_id'] = sanitize_text_field($_POST['bmlt_login_id']);
				$this->options['bmlt_login_password'] = sanitize_text_field($_POST['bmlt_login_password']);
				$this->options['base_font'] = sanitize_text_field($_POST['base_font']);
				$this->options['protection_password'] = sanitize_text_field($_POST['protection_password']);
				$this->options['time_clock'] = sanitize_text_field($_POST['time_clock']);
				$this->options['time_option'] = intval($_POST['time_option']);
				$this->options['remove_space'] = boolval($_POST['remove_space']);
				$this->options['content_line_height'] = floatval($_POST['content_line_height']);
				$this->options['root_server'] = validate_url($_POST['root_server']);
				$this->options['service_body_1'] = sanitize_text_field($_POST['service_body_1']);
				$this->options['service_body_2'] = sanitize_text_field($_POST['service_body_2']);
				$this->options['service_body_3'] = sanitize_text_field($_POST['service_body_3']);
				$this->options['service_body_4'] = sanitize_text_field($_POST['service_body_4']);
				$this->options['service_body_5'] = sanitize_text_field($_POST['service_body_5']);
				$this->options['cache_time'] = intval($_POST['cache_time']);
				$this->options['custom_query'] = sanitize_text_field($_POST['custom_query']);
				$this->options['extra_meetings'] = wp_kses_post($_POST['extra_meetings']);
				$this->save_admin_options();
				set_transient( 'admin_notice', 'Please put down your weapon. You have 20 seconds to comply.' );
				echo '<div class="updated"><p style="color: #F00;">Your changes were successfully saved!</p>';
				$num = $this->delete_transient_cache();
				if ( $num > 0 ) {
					echo "<p>$num Cache entries deleted</p>";
				}
				echo '</div>';
			} elseif ( isset($_COOKIE['pwsix_action']) && $_COOKIE['pwsix_action'] == "import_settings" ) {
				echo '<div class="updated"><p style="color: #F00;">Your file was successfully imported!</p></div>';
				setcookie('pwsix_action', NULL, -1);
				$num = $this->delete_transient_cache();
			} elseif ( isset($_COOKIE['pwsix_action']) && $_COOKIE['pwsix_action'] == "default_settings_success" ) {
				echo '<div class="updated"><p style="color: #F00;">Your default settings were successfully updated!</p></div>';
				setcookie('pwsix_action', NULL, -1);
				$num = $this->delete_transient_cache();
			}
			global $wpdb;
			$query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid LIKE '%default_nalogo.jpg%'";
			if ( $wpdb->get_var($query) == 0 ) {
				$url = plugin_dir_url(__FILE__) . "includes/default_nalogo.jpg";
				media_sideload_image( $url, 0 );
			}
			if ( !isset($this->options['front_page_line_height']) || strlen(trim($this->options['front_page_line_height'])) == 0 ) {
				$this->options['front_page_line_height'] = '1.0';
			}
			if ( !isset($this->options['front_page_font_size']) || strlen(trim($this->options['front_page_font_size'])) == 0 ) {
				$this->options['front_page_font_size'] = '10';
			}
			if ( !isset($this->options['last_page_font_size']) || strlen(trim($this->options['last_page_font_size'])) == 0 ) {
				$this->options['last_page_font_size'] = '10';
			}
			if ( !isset($this->options['content_font_size']) || strlen(trim($this->options['content_font_size'])) == 0 ) {
				$this->options['content_font_size'] = '9';
			}
			if ( !isset($this->options['header_font_size']) || strlen(trim($this->options['header_font_size'])) == 0 ) {
				$this->options['header_font_size'] = $this->options['content_font_size'];
			}
			if ( !isset($this->options['header_text_color']) || strlen(trim($this->options['header_text_color'])) == 0 ) {
				$this->options['header_text_color'] = '#ffffff';
			}
			if ( !isset($this->options['header_background_color']) || strlen(trim($this->options['header_background_color'])) == 0 ) {
				$this->options['header_background_color'] = '#000000';
			}
			if ( !isset($this->options['header_uppercase']) || strlen(trim($this->options['header_uppercase'])) == 0 ) {
				$this->options['header_uppercase'] = '0';
			}
			if ( !isset($this->options['header_bold']) || strlen(trim($this->options['header_bold'])) == 0 ) {
				$this->options['header_bold'] = '1';
			}
			if ( !isset($this->options['sub_header_shown']) || strlen(trim($this->options['sub_header_shown'])) == 0 ) {
				$this->options['sub_header_shown'] = '1';
			}
			if ( !isset($this->options['margin_top']) || strlen(trim($this->options['margin_top'])) == 0 ) {
				$this->options['margin_top'] = 3;
			}
			if ( !isset($this->options['margin_bottom']) || strlen(trim($this->options['margin_bottom'])) == 0 ) {
				$this->options['margin_bottom'] = 3;
			}
			if ( !isset($this->options['margin_left']) || strlen(trim($this->options['margin_left'])) == 0 ) {
				$this->options['margin_left'] = 3;
			}
			if ( !isset($this->options['margin_right']) || strlen(trim($this->options['margin_right'])) == 0 ) {
				$this->options['margin_right'] = 3;
			}
			if ( !isset($this->options['page_height_fix']) || strlen(trim($this->options['page_height_fix'])) == 0 ) {
				$this->options['page_height_fix'] = 0;
			}
			if ( !isset($this->options['column_gap']) || strlen(trim($this->options['column_gap'])) == 0 ) {
				$this->options['column_gap'] = "5";
			}
			if ( !isset($this->options['content_line_height']) || strlen(trim($this->options['content_line_height'])) == 0 ) {
				$this->options['content_line_height'] = '1.0';
			}
			if ( !isset($this->options['last_page_line_height']) || strlen(trim($this->options['last_page_line_height'])) == 0 ) {
				$this->options['last_page_line_height'] = '1.0';
			}
			if ( !isset($this->options['page_size']) || strlen(trim($this->options['page_size'])) == 0 ) {
				$this->options['page_size'] = 'legal';
			}
			if ( !isset($this->options['page_orientation']) || strlen(trim($this->options['page_orientation'])) == 0 ) {
				$this->options['page_orientation'] = 'L';
			}
			if ( !isset($this->options['page_fold']) || strlen(trim($this->options['page_fold'])) == 0 ) {
				$this->options['page_fold'] = 'quad';
			}
			if ( !isset($this->options['meeting_sort']) || strlen(trim($this->options['meeting_sort'])) == 0 ) {
				$this->options['meeting_sort'] = 'day';
			}
			if ( !isset($this->options['borough_suffix']) ) {
				$this->options['borough_suffix'] = 'Borough';
			}
			if ( !isset($this->options['county_suffix']) ) {
				$this->options['county_suffix'] = 'County';
			}
			if ( !isset($this->options['neighborhood_suffix']) ) {
				$this->options['neighborhood_suffix'] = 'Neighborhood';
			}
			if ( !isset($this->options['city_suffix']) ) {
				$this->options['city_suffix'] = 'City';
			}
			if ( !isset($this->options['meeting_template']) || strlen(trim($this->options['meeting_template'])) == 0 ) {
				$this->options['meeting_template'] = '1';
			}
			if ( !isset($this->options['meeting_template_content']) || strlen(trim($this->options['meeting_template_content'])) == 0 ) {
				$this->options['meeting_template_content'] = '';
			}
			if ( !isset($this->options['column_line']) || strlen(trim($this->options['column_line'])) == 0 ) {
				$this->options['column_line'] = 0;
			}
			if ( !isset($this->options['col_color']) || strlen(trim($this->options['col_color'])) == 0 ) {
				$this->options['col_color'] = '#bfbfbf';
			}
			if ( !isset($this->options['custom_section_content']) || strlen(trim($this->options['custom_section_content'])) == 0 ) {
				$this->options['custom_section_content'] = '';
			}
			if ( !isset($this->options['custom_section_line_height']) || strlen(trim($this->options['custom_section_line_height'])) == 0 ) {
				$this->options['custom_section_line_height'] = '1';
			}
			if ( !isset($this->options['custom_section_font_size']) || strlen(trim($this->options['custom_section_font_size'])) == 0 ) {
				$this->options['custom_section_font_size'] = '9';
			}
			if ( !isset($this->options['pagenumbering_font_size']) || strlen(trim($this->options['pagenumbering_font_size'])) == 0 ) {
				$this->options['pagenumbering_font_size'] = '9';
			}
			if ( !isset($this->options['include_zip']) || strlen(trim($this->options['include_zip'])) == 0 ) {
				$this->options['include_zip'] = 0;
			}
			if ( !isset($this->options['used_format_1']) || strlen(trim($this->options['used_format_1'])) == 0 ) {
				$this->options['used_format_1'] = '';
			}
			if ( !isset($this->options['used_format_2']) || strlen(trim($this->options['used_format_2'])) == 0 ) {
				$this->options['used_format_2'] = '';
			}
			if ( !isset($this->options['include_meeting_email']) || strlen(trim($this->options['include_meeting_email'])) == 0 ) {
				$this->options['include_meeting_email'] = 0;
			}
            if ( !isset($this->options['base_font']) || strlen(trim($this->options['base_font'])) == 0 ) {
                $this->options['base_font'] = 'dejavusanscondensed';
            }
            if ( !isset($this->options['recurse_service_bodies']) || strlen(trim($this->options['recurse_service_bodies'])) == 0) {
                $this->options['recurse_service_bodies'] = 1;
            }
			if ( !isset($this->options['extra_meetings_enabled']) || strlen(trim($this->options['extra_meetings_enabled'])) == 0) {
				$this->options['extra_meetings_enabled'] = 0;
			}
            if ( !isset($this->options['include_protection']) || strlen(trim($this->options['include_protection'])) == 0 ) {
				$this->options['include_protection'] = 0;
			}			
			if ( !isset($this->options['weekday_language']) || strlen(trim($this->options['weekday_language'])) == 0 ) {
				$this->options['weekday_language'] = 'en';
			}
			if ( !isset($this->options['weekday_start']) || strlen(trim($this->options['weekday_start'])) == 0 ) {
				$this->options['weekday_start'] = '1';
			}
			if ( !isset($this->options['include_asm']) || strlen(trim($this->options['include_asm'])) == 0 ) {
				$this->options['include_asm'] = '0';
			}			
			if ( !isset($this->options['bmlt_login_id']) || strlen(trim($this->options['bmlt_login_id'])) == 0 ) {
				$this->options['bmlt_login_id'] = '';
			}			
			if ( !isset($this->options['bmlt_login_password']) || strlen(trim($this->options['bmlt_login_password'])) == 0 ) {
				$this->options['bmlt_login_password'] = '';
			}			
			if ( !isset($this->options['protection_password']) || strlen(trim($this->options['protection_password'])) == 0 ) {
				$this->options['protection_password'] = '';
			}
			if ( !isset($this->options['custom_query']) || strlen(trim($this->options['custom_query'])) == 0 ) {
				$this->options['custom_query'] = '';
			}			
			if ( !isset($this->options['cache_time']) || strlen(trim($this->options['cache_time'])) == 0 ) {
				$this->options['cache_time'] = 0;
			}
			if ( !isset($this->options['extra_meetings']) || $this->options['extra_meetings'] == '' ) {
				$this->options['extra_meetings'] = '';
			} else {
				$this->options['extra_meetings_enabled'] = 1;
			}
			
			?>
			<?php include 'partials/_help_videos.php'; ?>
			<div class="hide wrap" id="meeting-list-tabs-wrapper">
				<h2>bread</h2>
				<div id="meeting-list-tabs">
					<ul class="nav">
						<li><a href="#setup"><?php _e('Meeting List Setup', 'root-server'); ?></a></li>
						<li><a href="#tabs-first"><?php _e('BMLT Server', 'root-server'); ?></a></li>
						<li><a href="#layout"><?php _e('Page Layout', 'root-server'); ?></a></li>
						<li><a href="#front-page"><?php _e('Front Page', 'root-server'); ?></a></li>
						<li><a href="#meetings"><?php _e('Meetings', 'root-server'); ?></a></li>
						<li><a href="#custom-section"><?php _e('Custom Content', 'root-server'); ?></a></li>
						<li><a href="#last-page"><?php _e('Last Page', 'root-server'); ?></a></li>
						<li><a href="#import-export"><?php _e('Backup/Restore', 'root-server'); ?></a></li>
					</ul>
					<form style=" display:inline!important;" method="POST" id="bmlt_meeting_list_options">
					<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
					<?php
					wp_nonce_field('bmltmeetinglistupdate-options');
					$this_connected = $this->testRootServer();
					$bmlt_version = $this_connected;
					if ($bmlt_version == "5.0.0") {
						$ThisVersion = "<span style='color: #00AD00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-admin-site'></div>Using Tomato Server</span>";
                    } else {
                        $this_version = intval(str_replace(".", "", $this_connected));
                        $source_of_truth = $this->getLatestRootVersion();
                        $source_of_truth_version = intval(str_replace(".", "", $source_of_truth));
                        $connect = "<p><div style='color: #f00;font-size: 16px;vertical-align: middle;' class='dashicons dashicons-no'></div><span style='color: #f00;'>Connection to BMLT Server Failed.  Check spelling or try again.  If you are certain spelling is correct, BMLT Server could be down.</span></p>";
                        if ( $this_connected ) {
                            $ThisVersion = "<span style='color: #00AD00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-smiley'></div>Your BMLT Server is running the latest Version ".$bmlt_version."</span>";
                            if ( $this_version !== $source_of_truth_version ) {
                                $ThisVersion = "<span style='color: #f00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>Notice: BMLT Server Update Available! Your Version = ".$bmlt_version.". </span>";
                                $ThisVersion .= "<span style='color: #7AD03A;'><i>Updated version = " . $source_of_truth . "</i></span><br />";
                            }
                        }
					}
					?>
					<div id="setup">						
						<?php include 'partials/_meeting_list_setup.php'; ?>
					</div>
					<div id="tabs-first">						
						<?php include 'partials/_bmlt_server_setup.php'; ?>
					</div>
					<div id="layout">
						<?php include 'partials/_layout_setup.php'; ?>
					</div>
					<div id="front-page">
						<?php include 'partials/_front_page_setup.php'; ?>
					</div>
					<div id="meetings">
						<?php include 'partials/_meetings_setup.php'; ?>
					</div>
					<div id="custom-section">
						<?php include 'partials/_custom_section_setup.php'; ?>
					</div>
					<div id="last-page">
						<?php include 'partials/_last_page_setup.php'; ?>
					</div>
					</form>
					<div id="import-export">
						<?php include 'partials/_backup_restore_setup.php'; ?>						
					</div>
				</div>
			</div>
			<div id="dialog" title="TinyMCE dialog" style="display: none">
				<textarea>test</textarea>
			</div>
<?php
		}

		/**
		 * Deletes transient cache
		 */
		function delete_transient_cache() {
			global $wpdb, $_wp_using_ext_object_cache;
			wp_cache_flush();
			$num1 = $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s ", '_transient_bmlt_ml_%'));
			$num2 = $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s ", '_transient_timeout_bmlt_ml_%'));
			wp_cache_flush();
			return $num1 + $num2;
		}

		/**
		 * count transient cache
		 */
		function count_transient_cache() {
			global $wpdb, $_wp_using_ext_object_cache;
			wp_cache_flush();
			$num1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s ", '_transient_bmlt_ml_%'));
			$num2 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s ", '_transient_timeout_bmlt_ml_%'));
			wp_cache_flush();
			return $num1 + $num2;
		}

		/**
		 * Process a settings export that generates a .json file of the shop settings
		 */
		function pwsix_process_settings_export() {
            if ( isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes' )
                return;
            if( empty( $_POST['pwsix_action'] ) || 'export_settings' != $_POST['pwsix_action'] )
                return;
            if( ! wp_verify_nonce( $_POST['pwsix_export_nonce'], 'pwsix_export_nonce' ) )
                return;
            if( ! current_user_can( 'manage_options' ) )
                return;

			$blogname = str_replace(" - ", " ", get_option('blogname'));
			$blogname = str_replace(" ", "-", $blogname);
			$date = date("m-d-Y");
			$blogname = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($blogname)), '-');
			$json_name = $blogname."-meeting-list-settings-".$date.".json"; // Naming the filename will be generated.
			$settings = get_option( $this->optionsName );
			foreach ($settings as $key => $value) {

				$value = maybe_unserialize($value);
				$need_options[$key] = $value;
			}
			$json_file = json_encode($need_options); // Encode data into json data
			ignore_user_abort( true );
			header( 'Content-Type: application/json; charset=utf-8' );
			header("Content-Disposition: attachment; filename=$json_name");
			header( "Expires: 0" );
			echo json_encode( $settings );
			exit;
		}

		/**
		 * Process a settings import from a json file
		 */
		function pwsix_process_settings_import() {
			if ( isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes' )
				return;
			if( empty( $_POST['pwsix_action'] ) || 'import_settings' != $_POST['pwsix_action'] )
				return;
			if( ! wp_verify_nonce( $_POST['pwsix_import_nonce'], 'pwsix_import_nonce' ) )
				return;
			if( ! current_user_can( 'manage_options' ) )
				return;
			$extension = end( explode( '.', $_FILES['import_file']['name'] ) );
			if( $extension != 'json' ) {
				wp_die( __( 'Please upload a valid .json file' ) );
			}
			$import_file = $_FILES['import_file']['tmp_name'];
			if( empty( $import_file ) ) {
				wp_die( __( 'Please upload a file to import' ) );
			}
			$file_size = $_FILES['import_file']['size'];
			if( $file_size > 500000 ) {
				wp_die( __( 'File size greater than 500k' ) );
			}
            $encode_options = file_get_contents($import_file);
            $settings = json_decode($encode_options, true);
			update_option( $this->optionsName, $settings );
			setcookie('pwsix_action', "import_settings", time()+10); 
			wp_safe_redirect( admin_url( '?page=bmlt-meeting-list.php' ) );
		}

		/**
		 * Process a default settings
		 */
		function pwsix_process_default_settings() {
			if ( ! current_user_can( 'manage_options' ) ||
				(isset($_POST['bmltmeetinglistsave']) && $_POST['bmltmeetinglistsave'] == 'Save Changes' )) {
				return;
			} elseif ( isset($_POST['pwsix_action']) && 'three_column_default_settings' == $_POST['pwsix_action'] ) {
				if( ! wp_verify_nonce( $_POST['pwsix_submit_three_column'], 'pwsix_submit_three_column' ) )
					die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$import_file = plugin_dir_path(__FILE__) . "includes/three_column_settings.json";
			} elseif ( isset($_POST['pwsix_action']) && 'four_column_default_settings' == $_POST['pwsix_action'] ) {
				if( ! wp_verify_nonce( $_POST['pwsix_submit_four_column'], 'pwsix_submit_four_column' ) )
					die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$import_file = plugin_dir_path(__FILE__) . "includes/four_column_settings.json";
			} elseif ( isset($_POST['pwsix_action']) && 'booklet_default_settings' == $_POST['pwsix_action'] ) {
				if( ! wp_verify_nonce( $_POST['pwsix_submit_booklet'], 'pwsix_submit_booklet' ) )
					die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$import_file = plugin_dir_path(__FILE__) . "includes/booklet_settings.json";
			} else {
				return;
			}
			if( empty( $import_file ) )
				wp_die( __( 'Error importing default settings file' ) );
            $encode_options = file_get_contents($import_file);
            $settings = json_decode($encode_options, true);
			update_option( $this->optionsName, $settings );
			setcookie('pwsix_action', "default_settings_success", time()+10); 
			wp_safe_redirect( admin_url( '?page=bmlt-meeting-list.php' ) );
		}

		/**
		* @desc Adds the Settings link to the plugin activate/deactivate page
		*/
		function filter_plugin_actions($links, $file) {
			$settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
			array_unshift($links, $settings_link); // before other links
			return $links;
		}

		/**
		* Retrieves the plugin options from the database.
		* @return array
		*/
		function getMLOptions() {
			//Don't forget to set up the default options
			if (!$theOptions = get_option($this->optionsName)) {
				$import_file = plugin_dir_path(__FILE__) . "includes/three_column_settings.json";
				$encode_options = file_get_contents($import_file);
				$theOptions = json_decode($encode_options, true);
				update_option( $this->optionsName, $theOptions );
			}
			$this->options = $theOptions;
		}

		/**
		* Saves the admin options to the database.
		*/
		function save_admin_options(){
			update_option($this->optionsName, $this->options);
			return;
		}

        public function uniqueFormat(&$array_of_formats, $subkey) {
            $lastFormat = "";
            for ( $i = 0; $i < count( $array_of_formats ); $i++ ) {
                if ( strtoupper($lastFormat) == strtoupper($array_of_formats[$i][$subkey]) ) {
                    array_splice($array_of_formats, $i, 1);
                    $i--;
                } else {
                    $lastFormat = $array_of_formats[$i][$subkey];
                }
            }
        }

		public function getLatestRootVersion() {
			$results = $this->get("https://api.github.com/repos/bmlt-enabled/bmlt-root-server/releases/latest");
			$httpcode = wp_remote_retrieve_response_code( $results );
			$response_message = wp_remote_retrieve_response_message( $results );
			if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty( $response_message )) {
				return 'Problem Connecting to Server!';
			};
			$body = wp_remote_retrieve_body($results);
			$result = json_decode($body, true);
			return $result['name'];
		}
    } //End Class bread
} // end if
//instantiate the class
if (class_exists("Bread")) {
	$BMLTMeetinglist_instance = new Bread();
}
?>
