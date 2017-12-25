<?php
/**
Plugin Name: BMLT Meeting List Generator
Plugin URI: http://wordpress.org/extend/plugins/bmlt-meeting-list/
Description: Maintains and generates a PDF Meeting List from BMLT.
Version: 1.3.4
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}
if (!class_exists("BMLTMeetingList")) {
	class BMLTMeetingList
	{
		var $lang = '';
		
		var $version = '1.3.4';
		var $mpdf = '';
		var $meeting_count = 0;
		var $formats_used = '';
		var $formats_spanish = '';
		var $formats_all = '';
		
		var $service_meeting_result ='';
		var $optionsName = 'bmlt_meeting_list_options';
		var $options = array();
		function __construct()
		{
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
				//add_filter('show_admin_bar', '__return_false');
				add_filter('tiny_mce_version', array( __CLASS__, 'force_mce_refresh' ) );
			} else {
				// Front end
				if ( $_GET['current-meeting-list'] == '1' || $_GET['export-meeting-list'] == '1' ) {
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
			if ( $screen->id == $my_admin_page ) {
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
				$initArray['font_formats'].='Courier (Monospace)=courier ;';
			}
			return $initArray;
		}
		function is_root_server_missing() {
			global $my_admin_page;
			$screen = get_current_screen();
			if ( $screen->id == $my_admin_page ) {
				$root_server = $this->options['root_server'];
				if ( $root_server == '' ) {
					echo '<div id="message" class="error"><p>Missing BMLT Server in settings for BMLT Meeting List.</p>';
					$url = admin_url( 'options-general.php?page=bmlt-meeting-list.php' );
					echo "<p><a href='$url'>BMLT_Meetng_List Settings</a></p>";
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
		function BMLTMeetingList()
		{
			$this->__construct();
		}
		/**
		* @desc Adds JS/CSS to the header
		*/
		function enqueue_backend_files($hook) {
			if( $hook == 'toplevel_page_bmlt-meeting-list' ) {
				//wp_enqueue_script('post');
				wp_enqueue_script('common');
				//wp_enqueue_script('wp-lists');
				//wp_enqueue_script('postbox');
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
			if ( $screen->id == $my_admin_page ) {
				add_editor_style( plugin_dir_url(__FILE__) . "css/editor-style.css" );
				
			}
		}
		function getday( $day, $abbreviate = false, $language = '' ) {
			
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
				}				
			} elseif ( $day == 4 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Wed' : "Wednesday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'Mié' : "Miércoles");
					$data = "Miércoles";
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Mer' : "Mercredi");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Wed / Mié / Qua' : "Wednesday / Miércoles / Quarta-feira");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'Qua' : "Quarta-feira");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Wed / Mié' : "Wednesday / Miércoles");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Mer / Wed' : "Mercredi / Wednesday");
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
				}				
			} elseif ( $day == 7 ) {
				if ( $language == 'en' || $language == 'en' ) {
					$data = ($abbreviate ? 'Sat' : "Saturday");
				} elseif ( $language == 'es' ) {
					$data = ($abbreviate ? 'Sáb' : "Sábado");
				} elseif ( $language == 'fr' ) {
					$data = ($abbreviate ? 'Sam' : "Samedi");
				} elseif ( $language == 'both_po' ) {
					$data = ($abbreviate ? 'Sat / Sáb' : "Saturday / Sábado");
				} elseif ( $language == 'po' ) {
					$data = ($abbreviate ? 'Sáb' : "Sábado");
				} elseif ( $language == 'both' ) {
					$data = ($abbreviate ? 'Sat / Sáb' : "Saturday / Sábado");
				} elseif ( $language == 'fr_en' ) {
					$data = ($abbreviate ? 'Sam / Sat' : "Samedi / Saturday");
				}				
			}
			
			Return utf8_encode($data);
		}
		function get_all_meetings ( $root_server ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetSearchResults&data_field_key=weekday_tinyint,start_time,service_body_bigint,id_bigint,meeting_name,location_text&sort_keys=meeting_name,service_body_bigint,weekday_tinyint,start_time" );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			$results = curl_exec ( $ch );
			curl_close ( $ch );
			$result = json_decode($results,true);
			
			$unique_areas = $this->get_areas($this->options['root_server']);			
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
		function get_areas ( $root_server ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetServiceBodies" );			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			$results = curl_exec ( $ch );
			curl_close ( $ch );
			$result = json_decode($results,true);
			
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
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $this->options['root_server']."/client_interface/json/?switcher=GetServerInfo" );			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			$results = curl_exec ( $ch );
			curl_close ( $ch );
			$result = json_decode($results,true);
			
			$result = $result["0"]["nativeLang"];
			
			return $result;
		}
		function testEmailPassword($root_server,$login,$password) {
			$ch = curl_init();
			$cookie = ABSPATH . "cookie.txt";
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
			$data = http_build_query(array('admin_action' => 'login', 'c_comdef_admin_login' => $login, 'c_comdef_admin_password' => $password, '&'));
			curl_setopt($ch, CURLOPT_URL, "$root_server/local_server/server_admin/xml.php?".$data); 
			$results = curl_exec($ch);
			curl_close($ch);
			unlink($cookie);
			return $results;
		}
		
		function testRootServer($root_server) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/serverInfo.xml");
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			$results  = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$c_error  = curl_error($ch);
			$c_errno  = curl_errno($ch);
			curl_close($ch);
			if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
				return false;
			}
			$results = simplexml_load_string($results);
			$results = json_encode($results);
			$results = json_decode($results,TRUE);
			$results = $results["serverVersion"]["readableString"];
			return $results;
		}
		function newyorknaRootServer() {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://bmlt.newyorkna.org/main_server/client_interface/serverInfo.xml");
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			$results  = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$c_error  = curl_error($ch);
			$c_errno  = curl_errno($ch);
			curl_close($ch);
			if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
				return "Unknown";
			}
			$results = simplexml_load_string($results);
			$results = json_encode($results);
			$results = json_decode($results,TRUE);
			$results = $results["serverVersion"]["readableString"];
			return $results;
		}
		function getUsedFormats() {
			$root_server = $this->options['root_server'];
			$area_data = explode(',',$this->options['service_body_1']);
			$service_body_id = $area_data[1];
			$parent_body_id = $area_data[2];
			if ( $parent_body_id == '0' || $parent_body_id == '' ) {
				$services = '&recursive=1&services[]=' . $service_body_id;
			} else {
				$services = '&services[]='.$service_body_id;
			}
			$root_server = $this->options['root_server'];
			$area_data = explode(',',$this->options['service_body_1']);
			$service_body_id = $area_data[1];
			$parent_body_id = $area_data[2];
			if ( $parent_body_id == '0' || $parent_body_id == '' ) {
				$services = '&recursive=1&services[]=' . $service_body_id;
			} else {
				$services = '&services[]='.$service_body_id;
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
			curl_setopt($ch, CURLOPT_URL,"$root_server/client_interface/json/?switcher=GetSearchResults$services&get_formats_only" );
			$results = curl_exec($ch);
			curl_close($ch);
			$results = json_decode($results,true);
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
			if ( isset( $_GET['export-meeting-list'] ) && $_GET['export-meeting-list'] == '1' ) {
				$this->pwsix_process_settings_export();
			}
			$root_server = $this->options['root_server'];
			$area_data = explode(',',$this->options['service_body_1']);
			$area = $area_data[0];
			$this->options['service_body_1'] = $area;
			$service_body_id = $area_data[1];
			$parent_body_id = $area_data[2];
			if ( $parent_body_id == '0' || $parent_body_id == '' ) {
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
				if ( $parent_body_id == '0' || $parent_body_id == '' ) {
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
				if ( $parent_body_id == '0' || $parent_body_id == '' ) {
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
				if ( $parent_body_id == '0' || $parent_body_id == '' ) {
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
				if ( $parent_body_id == '0' || $parent_body_id == '' ) {
					$services .= '&recursive=1&services[]=' . $service_body_id;
				} else {
					$services .= '&services[]='.$service_body_id;
				}
			}
			if ( $root_server == '' ) {
				echo '<p><strong>BMLT Meeting List Error: BMLT Server missing.<br/><br/>Please go to Settings -> BMLT_Meetng_List and verify BMLT Server</strong></p>';
				exit;
			}
			if ( $this->options['service_body_1'] == 'Not Used' ) {
				echo '<p><strong>BMLT Meeting List Error: Service Body 1 missing from configuration.<br/><br/>Please go to Settings -> BMLT_Meetng_List and verify Service Body</strong><br/><br/>Contact the BMLT Meeting List Generator administrator and report this problem!</p>';
				exit;
			}
			//define('_MPDF_URI',plugin_dir_url(__FILE__).'mpdf/');
			//include(plugin_dir_path( __FILE__ ).'mpdf/mpdf.php');
			require_once plugin_dir_path(__FILE__).'mpdf/vendor/autoload.php';
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
			if ( !isset($this->options['show_status']) ) {$this->options['show_status'] = '0';}
			if ( !isset($this->options['column_line']) ) {$this->options['column_line'] = '0';}
			if ( !isset($this->options['col_color']) ) {$this->options['col_color'] = '#bfbfbf';}
			if ( !isset($this->options['custom_section_content']) ) {$this->options['custom_section_content'] = '';}
			if ( !isset($this->options['custom_section_line_height']) ) {$this->options['custom_section_line_height'] = '1';}
			if ( !isset($this->options['custom_section_font_size']) ) {$this->options['custom_section_font_size'] = '9';}
			if ( !isset($this->options['include_zip']) ) {$this->options['include_zip'] = '0';}
			if ( !isset($this->options['include_meeting_email']) ) {$this->options['include_meeting_email'] = '0';}
			if ( !isset($this->options['include_protection']) ) {$this->options['include_protection'] = '0';}
			if ( !isset($this->options['weekday_language']) ) {$this->options['weekday_language'] = 'en';}
			if ( !isset($this->options['include_asm']) ) {$this->options['include_asm'] = '0';}
			if ( !isset($this->options['header_uppercase']) ) {$this->options['header_uppercase'] = '0';}
			if ( !isset($this->options['header_bold']) ) {$this->options['header_bold'] = '1';}
			if ( !isset($this->options['bmlt_login_id']) ) {$this->options['bmlt_login_id'] = '';}
			if ( !isset($this->options['bmlt_login_password']) ) {$this->options['bmlt_login_password'] = '';}
			if ( !isset($this->options['protection_password']) ) {$this->options['protection_password'] = '';}
			if ( !isset($this->options['cache_time']) ) {$this->options['cache_time'] = '0';}
			if ( !isset($this->options['extra_meetings']) ) {$this->options['extra_meetings'] = '';}
			if ( !isset($this->options['used_format_1']) ) {$this->options['used_format_1'] = '';}
			if ( !isset($this->options['used_format_2']) ) {$this->options['used_format_2'] = '';}
			if ( intval($this->options['cache_time']) > 0 && ! isset($_GET['nocache']) ) {
				$transient_key = 'bmlt_ml_'.md5($root_server.$services);
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
				$this->mpdf=new mPDF('utf-8',array(203.2,279.4), 7, '', $this->options['margin_left'], $this->options['margin_right'], $this->options['margin_top'], $this->options['margin_bottom'], 5, 5, 'P');
				$this->mpdf->DefHTMLFooterByName('MyFooter','<div style="text-align: center; font-size: 9pt; font-style: italic;">Page {PAGENO}</div>');
			} elseif ( $this->options['page_fold'] == 'half' && $this->options['page_size'] == 'A5' ) {
				$this->mpdf=new mPDF('utf-8','A4', 7, '', $this->options['margin_left'], $this->options['margin_right'], $this->options['margin_top'], $this->options['margin_bottom'], 5, 5);
				$this->mpdf->DefHTMLFooterByName('MyFooter','<div style="text-align: center; font-size: 9pt; font-style: italic;">Page {PAGENO}</div>');
			} elseif ( $this->options['page_size'] . '-' .$this->options['page_orientation'] == 'ledger-L' ) {
				$this->mpdf=new mPDF('utf-8', array(432,279), 7, '', $this->options['margin_left'], $this->options['margin_right'], $this->options['margin_top'], $this->options['margin_bottom'], 0, 0);
			} elseif ( $this->options['page_size'] . '-' .$this->options['page_orientation'] == 'ledger-P' ) {
				$this->mpdf=new mPDF('utf-8', array(279,432), 7, '', $this->options['margin_left'], $this->options['margin_right'], $this->options['margin_top'], $this->options['margin_bottom'], 0, 0);
			} else {
				$this->mpdf=new mPDF('utf-8',$this->options['page_size']."-".$this->options['page_orientation'], 7, '', $this->options['margin_left'], $this->options['margin_right'], $this->options['margin_top'], $this->options['margin_bottom'], 0, 0);
			}					
			if ( $this->options['include_protection'] == '1' ) {
				// 'copy','print','modify','annot-forms','fill-forms','extract','assemble','print-highres'
				$this->mpdf->SetProtection(array('copy','print','print-highres'), '', $this->options['protection_password']);
				
			}
			$this->mpdf->simpleTables = false;
			$this->mpdf->useSubstitutions = false;
			$this->mpdf->progressBar = 0;				// Shows progress-bars whilst generating file 0 off, 1 simple, 2 advanced
			$this->mpdf->progbar_heading = 'Generating Meeting List from BMLT';
			$blog = get_bloginfo( "name" );
			//$this->mpdf->progbar_altHTML = '<html><body><div style="font-family: arial;text-align:center;width: 100%;position: absolute;top:0;bottom: 0;left: 0;right: 0;margin: 0 auto;margin-top: 50px;"><h2>'.$blog.'</h2><img src='.plugin_dir_url(__FILE__) . 'css/googleballs-animated.gif /><h2>Generating Meeting List</h2></div>';
			$this->mpdf->progbar_altHTML = '<html><body><div style="font-family: arial;text-align:center;width: 100%;position: absolute;top:0;bottom: 0;left: 0;right: 0;margin: 0 auto;margin-top: 50px;"><h2>'.$blog.'</h2><h2>Generating Meeting List</h2><h2>Please Wait...</h2></div>';
			if ( $this->options['show_status'] == '99' ) {			
				$this->mpdf->StartProgressBarOutput(1);
			}
			$this->mpdf->mirrorMargins = false;
			//$this->mpdf->showStats = false;
			//$this->mpdf->shrink_tables_to_fit=0;			
			//$this->mpdf->keep_table_proportions = TRUE;
			$this->mpdf->list_indent_first_level = 1; // 1 or 0 - whether to indent the first level of a list
			// LOAD a stylesheet
			$header_stylesheet = file_get_contents(plugin_dir_path( __FILE__ ).'css/mpdfstyletables.css');
			$this->mpdf->WriteHTML($header_stylesheet,1); // The parameter 1 tells that this is css/style only and no body/html/text
			$this->mpdf->SetDefaultBodyCSS('line-height', $this->options['content_line_height']);
			$this->mpdf->SetDefaultBodyCSS('background-color', 'transparent');
			if ( $this->options['column_line'] == '1' ) {
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
				$this->mpdf_column=new mPDF('utf-8',$this->options['page_size']."-".$this->options['page_orientation'], 7, '', $this->options['margin_left'], $this->options['margin_right'], $this->options['margin_top'], $this->options['margin_bottom'], 0, 0);
				
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
			} elseif ( $this->options['meeting_sort'] == 'group' ) {
				$sort_keys = 'meeting_name,weekday_tinyint,start_time';
			} elseif ( $this->options['meeting_sort'] == 'weekday_area' ) {
				$sort_keys = 'weekday_tinyint,service_body_bigint,start_time';
			} elseif ( $this->options['meeting_sort'] == 'weekday_city' ) {
				$sort_keys = 'weekday_tinyint,location_municipality,start_time';
			} else {
				$this->options['meeting_sort'] = 'day';
				$sort_keys = 'weekday_tinyint,start_time,meeting_name';
			}
			if ( $this->options['service_body_1'] == 'Florida Region' && $services == '&recursive=1&services[]=1&recursive=1&services[]=20' ) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				if ( $this->options['used_format_1'] == '' ) {
					curl_setopt($ch, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults$services&get_used_formats&sort_keys=$sort_keys" );
				} else {
					curl_setopt($ch, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys&get_used_formats&formats[]=".$this->options['used_format_1'] );
				}
				$results = curl_exec($ch);
				curl_close($ch);
				$florida = json_decode($results,true);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				if ( $this->options['used_format_1'] == '' ) {
					curl_setopt($ch, CURLOPT_URL, "http://www.alnwfl.org/main_server/client_interface/json/?switcher=GetSearchResults&sort_keys=$sort_keys&meeting_key=location_province&meeting_key_value=florida&get_used_formats&meeting_key_contains=1");
				} else {
					curl_setopt($ch, CURLOPT_URL, "http://www.alnwfl.org/main_server/client_interface/json/?switcher=GetSearchResults&sort_keys=$sort_keys&meeting_key=location_province&meeting_key_value=florida&get_used_formats&meeting_key_contains=1&formats[]=".$this->options['used_format_1']);
				}
				$results1 = curl_exec($ch);
				curl_close($ch);
				$alnwfl1 = json_decode($results1,true);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				if ( $this->options['used_format_1'] == '' ) {
					curl_setopt($ch, CURLOPT_URL, "http://www.alnwfl.org/main_server/client_interface/json/?switcher=GetSearchResults&sort_keys=$sort_keys&meeting_key=location_province&meeting_key_value=fl&get_used_formats&meeting_key_contains=1");
				} else {
					curl_setopt($ch, CURLOPT_URL, "http://www.alnwfl.org/main_server/client_interface/json/?switcher=GetSearchResults&sort_keys=$sort_keys&meeting_key=location_province&meeting_key_value=fl&get_used_formats&meeting_key_contains=1&formats[]=".$this->options['used_format_1']);
				}
				$results2 = curl_exec($ch);
				curl_close($ch);
				$alnwfl2 = json_decode($results2,true);
				//$result_meetings = array_merge($florida['meetings'], $alnwfl1['meetings'], $alnwfl2['meetings']);
				if ( isset($alnwfl1['meetings']) && isset($alnwfl2['meetings']) ) {
					$result_meetings = array_merge($florida['meetings'], $alnwfl1['meetings'], $alnwfl2['meetings']);
					$this->formats_used = array_merge($florida['formats'], $alnwfl1['formats'], $alnwfl2['formats']);
				} elseif ( isset($alnwfl1['meetings']) & ! isset($alnwfl2['meetings']) ) {
					$result_meetings = array_merge($florida['meetings'], $alnwfl1['meetings']);
					$this->formats_used = array_merge($florida['formats'], $alnwfl1['formats']);
				} elseif ( isset($alnwfl2['meetings']) & ! isset($alnwfl1['meetings']) ) {
					$result_meetings = array_merge($florida['meetings'], $alnwfl2['meetings']);
					$this->formats_used = array_merge($florida['formats'], $alnwfl2['formats']);
				} else {
					$result_meetings = $florida['meetings'];
					$this->formats_used = $florida['formats'];
				}
			} else {
				$ch = curl_init();
				$cookie = ABSPATH . "cookie.txt";
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				if ( $this->options['include_meeting_email'] == '1' ) { 
					curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
					curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
					curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
					$data = http_build_query(array('admin_action' => 'login', 'c_comdef_admin_login' => $this->options['bmlt_login_id'], 'c_comdef_admin_password' => $this->options['bmlt_login_password'], '&'));
					curl_setopt($ch, CURLOPT_URL, "$root_server/local_server/server_admin/xml.php?".$data); 
					curl_exec($ch);
				}
				$get_used_formats = '&get_used_formats';
				if ( $root_server == "http://naminnesota.org/bmlt/main_server/" ) {
					$get_used_formats = '';
				}
				if ( $this->options['used_format_1'] == '' && $this->options['used_format_2'] == '' ) {
					curl_setopt($ch, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys$get_used_formats" );
				} elseif ( $this->options['used_format_1'] != '' ) {
					curl_setopt($ch, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys&get_used_formats&formats[]=".$this->options['used_format_1'] );
				} elseif ( $this->options['used_format_2'] != '' ) {
					curl_setopt($ch, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys&get_used_formats&formats[]=".$this->options['used_format_2'] );
				}
				$results = curl_exec($ch);
				
				$result = json_decode($results,true);
				if ( $this->options['extra_meetings'] ) {
					
						foreach ($this->options['extra_meetings'] as $value) {
							
							$data = array(" [", "]");
							$value = str_replace($data, "", $value);
							$extras .= "&meeting_ids[]=".$value;
						}
						$ch2 = curl_copy_handle($ch);
						
						curl_setopt($ch2, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults&sort_keys=".$sort_keys."".$extras."".$get_used_formats );
						$extra_results = curl_exec($ch2);
				
						curl_close($ch2);
						$extra_result = json_decode($extra_results,true);
						if ( $extra_result <> Null ) {
							
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
				if ( $root_server == "http://naminnesota.org/bmlt/main_server/" ) {
					$result_meetings = $result;
				}
				
				curl_close($ch);
				if ( $this->options['include_meeting_email'] == '1' ) { 
					unlink($cookie);
				}
			}
			if ( $result_meetings == Null ) {
				echo "<script type='text/javascript'>\n";
				echo "document.body.innerHTML = ''";
				echo "</script>";
				echo '<div style="font-size: 20px;text-align:center;font-weight:normal;color:#F00;margin:0 auto;margin-top: 30px;"><p>No Meetings Found</p><p>Or</p><p>Internet or Server Problem</p><p>'.$root_server.'</p><p>Please try again or contact your BMLT Administrator</p></div>';
				exit;
			}
			if ( strpos($this->options['custom_section_content'].$this->options['front_page_content'].$this->options['last_page_content'], "[service_meetings]") !== false ) {
				$ch = curl_init();
				$cookie = ABSPATH . "cookie.txt";
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
				if ( $this->options['include_meeting_email'] == '1' ) { 
					curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
					curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
					$data = http_build_query(array('admin_action' => 'login', 'c_comdef_admin_login' => $this->options['bmlt_login_id'], 'c_comdef_admin_password' => $this->options['bmlt_login_password'], '&'));
					curl_setopt($ch, CURLOPT_URL, "$root_server/local_server/server_admin/xml.php?".$data); 
					curl_exec($ch);
				}
				
				curl_setopt($ch, CURLOPT_URL,"$root_server/client_interface/json/?switcher=GetSearchResults$services_service_body_1&sort_keys=meeting_name" );
				$results = curl_exec($ch);
				curl_close($ch);
				if ( $this->options['include_meeting_email'] == '1' ) { 
					unlink($cookie);
				}
				$this->service_meeting_result = json_decode($results,true);
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetFormats");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			$results = curl_exec($ch);
			curl_close($ch);
			$this->formats_all = json_decode($results,true);
			if ( strpos($this->options['custom_section_content'].$this->options['front_page_content'].$this->options['last_page_content'], '[format_codes_used_basic_es') !== false ) {
				$ch = curl_init();
				if ( $this->options['used_format_1'] == '' && $this->options['used_format_2'] == '' ) {
					curl_setopt($ch, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults$services&sort_keys=time$get_used_formats&lang_enum=es" );
				} elseif ( $this->options['used_format_1'] != '' ) {
					curl_setopt($ch, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults$services&sort_keys=time&get_used_formats&lang_enum=es&formats[]=".$this->options['used_format_1'] );
				} elseif ( $this->options['used_format_2'] != '' ) {
					curl_setopt($ch, CURLOPT_URL,$root_server."/client_interface/json/?switcher=GetSearchResults$services&sort_keys=time&get_used_formats&lang_enum=es&formats[]=".$this->options['used_format_2'] );
				}
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
				$results = curl_exec($ch);
				curl_close($ch);
				$result_es = json_decode($results,true);
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
			$this->meeting_count = count($result_meetings);
			$unique_areas = $this->get_areas($this->options['root_server']);			
			$unique_states = array();
			$unique_data = array();
			
			$seperate_nc = false;
			
			if ( (strpos($services, '&services[]=8') !== false || strpos($services, '&services[]=11') !== false) && ($this->options['service_body_1'] == 'Tidewater Area Service' || $this->options['service_body_2'] == 'Tidewater Area Service') ) {
				
				$seperate_nc = true;
			}
			foreach ($result_meetings as $value) {
				if ( $this->options['service_body_1'] == 'Florida Region' && $services == '&recursive=1&services[]=1&recursive=1&services[]=20' && strtolower($value['location_province'][0]) === 'f' ) {
					$value['location_province'] = 'Florida';
				}
				$result_meetings_temp[] = $value;
				$unique_states[] = $value['location_province'];
				if ( $this->options['meeting_sort'] === 'state' ) {
					$unique_data[] = $value['location_municipality'] . ', '.$value['location_province'];
				} elseif ( $this->options['meeting_sort'] === 'city' ) {
					$unique_data[] = $value['location_municipality'];
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
				} else {
					$unique_data[] = $value['weekday_tinyint'];
				}
			}
			//$result_meetings = $result_meetings_temp;
			$unique_states = array_unique($unique_states);
			asort($unique_data);
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
			//$header_style .= "font-weight: bold;";
			if ( $this->options['header_uppercase'] === '1' ) { 
				$header_style .= 'text-transform: uppercase;';
			}
			if ( $this->options['header_bold'] === '0' ) { 
				$header_style .= 'font-weight: normal;';
			}
			if ( $this->options['header_bold'] === '1' ) { 
				$header_style .= 'font-weight: bold;';
			}
			if ( $this->options['page_fold'] == 'half' ) {
				if ( strpos($this->options['front_page_content'], '[start_toc]') !== false ) {
					//$start_toc = true;
				}
				$this->write_front_page();
				if ( $start_toc ) {
					$this->mpdf->WriteHTML('<tocentry content="Meeting Directory" />');
				}
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
				//if ( $this->options['meeting_sort'] == 'state' ) { $this->mpdf->WriteHTML('<tocentry content="'.$this_state.'" Level=1 />'); }
				if ( $this->options['meeting_sort'] === 'weekday_area' || $this->options['meeting_sort'] === 'weekday_city' ) {
					$current_weekday = 1;
					$show_first_weekday = true;
				}
				foreach ($unique_data as $this_unique_value) {
					if ( $this->options['meeting_sort'] === 'weekday_area' || $this->options['meeting_sort'] === 'weekday_city' ) {
						$area_data = explode(',',$this_unique_value);
						$weekday_tinyint = intval($area_data[0]);
						if ( $weekday_tinyint !== $current_weekday ) {
							$current_weekday++;
							$show_first_weekday = true;
						}
					}
					$newVal = true;
					if ( $this->options['meeting_sort'] === 'state' && strpos($this_unique_value, $this_state) === false ) { continue; }
					foreach ($result_meetings as $meeting_value) {
						//if ( strpos($root_server, 'naflorida') !== false ) { $meeting_value['location_province'] = ( substr(strtolower($meeting_value['location_province']), 0, 1) == 'f' ? 'Florida' : $meeting_value['location_province'] ); }						
						if ( $this->options['meeting_sort'] === 'weekday_area' || $this->options['meeting_sort'] === 'weekday_city' ) {
							$area_data = explode(',',$this_unique_value);
							$weekday_tinyint = $area_data[0];
							$area_name = $area_data[1];
							$service_body_bigint = $area_data[2];
//var_dump($meeting_value['weekday_tinyint'] . ',' . $meeting_value['location_municipality']);exit;
							if ( $this->options['meeting_sort'] === 'weekday_city' ) {
								if ( $meeting_value['weekday_tinyint'] . ',' . $meeting_value['location_municipality'] !== $weekday_tinyint . ',' . $area_name ) { continue; }
								
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
						if ( $this->options['meeting_sort'] === 'city' && $meeting_value['location_municipality'] !== $this_unique_value ) { continue; }
						if ( $this->options['meeting_sort'] === 'borough' && $meeting_value['location_city_subsection'] !== $this_unique_value ) { continue; }
						if ( $this->options['meeting_sort'] === 'county' && $meeting_value['location_sub_province'] !== $this_unique_value ) { continue; }
						if ( $this->options['meeting_sort'] === 'borough_county' ) {
							if ( $meeting_value['location_city_subsection'] !== '' ) {
								if ( $meeting_value['location_city_subsection'] !== $this_unique_value ) { continue; }
							} else {
								if ( $meeting_value['location_sub_province'] !== $this_unique_value ) { continue; }
							}
						}				
						if ( $this->options['meeting_sort'] === 'day' && $meeting_value['weekday_tinyint'] !== $this_unique_value ) { continue; }
						$enFormats = explode ( ",", $meeting_value['formats'] );
						if ( $this->options['include_asm'] === '0' && in_array ( "ASM", $enFormats ) ) { continue; }
						$header = '';
						
						if ( $this->lang == 'fr' ) {
							
							$cont = '(suite)';
							
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
							if ( $this->options['meeting_sort'] === 'weekday_area' || $this->options['meeting_sort'] === 'weekday_city' ) {
								if ( $newVal ) {
									if ( $show_first_weekday === true ) {
										if ( $current_weekday === 1 ) {
											$header .= "<h2 style='".$header_style."'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])."</h2>";
										} else {
											$header .= "<h2 style='".$header_style."margin-top:2pt;'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])."</h2>";
										}
										$show_first_weekday = false;
									} elseif ( utf8_encode($this->mpdf->y) === $this->options['margin_top'] ) {
										$header .= "<h2 style='".$header_style."'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])." " . $cont . "</h2>";
									}
									$header .= "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>".$area_name."</p>";
									
								} elseif ( utf8_encode($this->mpdf->y) === $this->options['margin_top'] ) {
								
									$header .= "<h2 style='".$header_style."'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->options['weekday_language'])." " . $cont . "</h2>";
									$header .= "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>".$area_name."</p>";
								}
							}
							if ( $this->options['meeting_sort'] === 'city' || $this->options['meeting_sort'] === 'state' ) {
								if ( $meeting_value['location_municipality'] == '' ) {
									
									$meeting_value['location_municipality'] = '[NO CITY DATA IN BMLT]';
									
								}
								if ( $newVal ) {
									//$header .= "<h2 style='".$header_style."'>".$meeting_value['location_municipality'] . ', '.$meeting_value['location_province']."</h2>";
									$header .= "<h2 style='".$header_style."'>".$meeting_value['location_municipality']."</h2>";
								} elseif ( $newCol ) {
									//$header .= "<h2 style='".$header_style."'>".$meeting_value['location_municipality'] . ', '.$meeting_value['location_province']." " . $cont . "</h2>";
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
						$duration_h = number_format($duration_m/60,1);
						$space = ' ';
						if ( $this->options['remove_space'] == '1' ) {
							$space = '';
						}
						if ( $this->options['time_clock'] == Null || $this->options['time_clock'] == '12' || $this->options['time_option'] == '' ) {
							$time_format = "g:i".$space."A";
							
						} elseif ( $this->options['time_clock'] == '24fr' ) {
							$time_format = "H\hi";
						} else {
							$time_format = "H:i";
						}
						if ( $this->options['time_option'] == '1' || $this->options['time_option'] == '' ) {
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
							if ( isset($meeting_value['email_contact']) && $meeting_value['email_contact'] !== '' && $this->options['include_meeting_email'] == '1' ) { 
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
							if ( ($seperate_nc) && (strtolower($meeting_value['location_province']) === 'nc' || strtolower($meeting_value['location_province']) === 'n.c.') ) {
								$data = str_replace('time', $this->getday($meeting_value['weekday_tinyint'], false, $this->lang).': '.$meeting_value['start_time'], $data);
							} else {
								$data = str_replace('time', $meeting_value['start_time'], $data);
							}
							
							$meeting_value['formats'] = str_replace(',', ', ', $meeting_value['formats']);
							$data = str_replace('formats', $meeting_value['formats'], $data);
							$data = str_replace('duration_h', $duration_h, $data);
							$data = str_replace('hrs', $duration_h, $data);
							$data = str_replace('duration_m', $duration_m, $data);
							$data = str_replace('mins', $duration_m, $data);
							$data = str_replace('meeting_name', $meeting_value['meeting_name'], $data);
							$data = str_replace('location_text', $meeting_value['location_text'], $data);
							$data = str_replace('location_info', $meeting_value['location_info'], $data);
							$data = str_replace('location_street', $meeting_value['location_street'], $data);
							$data = str_replace('bus_line', $meeting_value['bus_line'], $data);
							//$data = str_replace('[state]', $meeting_value['location_province'], $data, $count);
							
							//if ( $count = 0 ) {
								
								$data = str_replace('state', $meeting_value['location_province'], $data);
								
							//}
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
							//$data = str_replace('>, ', '>', $data);
							$data = str_replace(', </', '</', $data);							
							
						//if ( strpos($data, 'A Conscious Contact') !== false ) {
							//var_export($data);exit;
						//}
						} else {
							$data = '<tr>';
							if ( $this->options['meeting_sort'] == 'group' ) {
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['meeting_name']."</td>";
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$this->getday($meeting_value['weekday_tinyint'], false, $this->lang)."</td>".$meeting_value['start_time'];
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['location_text']."</td>";
								$data .= "<td style='padding-right:0px;vertical-align:top;'>".$meeting_value['location_street']."</td>";
								$data .= "<td style='vertical-align:top;'>".$meeting_value['location_municipality']."</td>";
							} elseif ( $this->options['meeting_sort'] == 'city' || $this->options['meeting_sort'] == 'state' || $this->options['meeting_sort'] == 'borough' || $this->options['meeting_sort'] == 'county' || $this->options['meeting_sort'] == 'borough_county' ) {
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
						if ( ($seperate_nc) && (strtolower($meeting_value['location_province']) === 'nc' || strtolower($meeting_value['location_province']) === 'n.c.') ) {
							
							$data_nc .= $data;
							
							continue;
							
						}
						
						$data = $header . $data;
											
						$data = mb_convert_encoding($data, 'HTML-ENTITIES');
						
						$data = utf8_encode($data);
						$this->mpdf->WriteHTML($data);
						$ph = intval($this->options['margin_bottom']) + intval($this->options['margin_top']) + $this->mpdf->y + -intval($this->options['page_height_fix']);
						if ( strpos($this->options['front_page_content'], 'sethtmlpagefooter') !== false ) {
							$ph = $ph + 15;
						}
						if ( $ph + 15 >= $this->mpdf->h  ) {
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
			if ( $seperate_nc ) {
				
				$header .= "<h2 style='".$header_style."'>North Carolina Meetings</h2>";
				$data_nc = $header . $data_nc;
				$data_nc = mb_convert_encoding($data_nc, 'HTML-ENTITIES');
				$data_nc = utf8_encode($data_nc);
				$this->mpdf->WriteHTML($data_nc);
				
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
					$this->mpdftmp=new mPDF('',array(203.2,279.4),'','',0,0,0,0,6,6,'L');
					//$this->mpdftmp=new mPDF('utf-8','Letter-L','7','',0,0,0,0,0,0);
				} else {
					$this->mpdftmp=new mPDF('utf-8','A4-L','7','',0,0,0,0,0,0);
				}
				if ( $this->options['show_status'] == '99' ) {			
					$this->mpdftmp->progbar_heading = 'Generating Meeting List from BMLT';
					$blog = get_bloginfo( "name" );
					$this->mpdftmp->progbar_altHTML = '<html><body><div style="font-family: arial;text-align:center;width: 100%;position: absolute;top:0;bottom: 0;left: 0;right: 0;margin: 0 auto;margin-top: 50px;"><h2>'.$blog.'</h2><img src='.plugin_dir_url(__FILE__) . 'css/googleballs-animated.gif /><h2>Generating Meeting List</h2></div>';
					$this->mpdftmp->StartProgressBarOutput(1);
				}
				$this->mpdftmp->SetImportUse();    
				$ow = $this->mpdftmp->h;
				$oh = $this->mpdftmp->w;
				$pw = $this->mpdftmp->w / 2;
				$ph = $this->mpdftmp->h;
				$pagecount = $this->mpdftmp->SetSourceFile($FilePath);
				$pp = $this->GetBookletPages($pagecount);
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
			if ( intval($this->options['cache_time']) > 0 && ! isset($_GET['nocache']) ) {
				$content = $this->mpdf->Output('', 'S');
				$content = bin2hex($content);
				$transient_key = 'bmlt_ml_'.md5($root_server.$services);
				set_transient( $transient_key, $content, intval($this->options['cache_time']) * HOUR_IN_SECONDS );
			}			
			$FilePath = "current_meeting_list_".strtolower( date ( "njYghis" ) ).".pdf";
				
			$this->mpdf->Output($FilePath,'I');
			exit;
		}
		function GetBookletPages($np, $backcover=true) {
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
			//$this->options['front_page_content'] = str_replace('[toc_entry]', '<tocentry content="', $this->options['front_page_content']);
			//$this->options['front_page_content'] = str_replace('[/toc_entry]', '" />', $this->options['front_page_content']);
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
			$this->options['front_page_content'] = str_replace("[area]", strtoupper($this->options['service_body_1']), $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[page_break no_page_number]', '<sethtmlpagefooter name="" value="0" /><pagebreak />', $this->options['front_page_content']);
			$this->options['front_page_content'] = str_replace('[start_page_numbers]', '<sethtmlpagefooter name="MyFooter" page="ALL" value="1" />', $this->options['front_page_content']);
			//$this->options['front_page_content'] = str_replace('[start_toc]', '<sethtmlpagefooter name="" value="0" /><pagebreak resetpagenum="3" /><tocpagebreak paging="on" links="on" toc-margin-top="40px" toc-margin-header="5mm" toc-odd-header-name="html_MyTOCHeader" toc-odd-header-value="1" toc-odd-footer-name="html_MyTOCFooter" toc-odd-footer-value="1" toc-even-footer-name="html_MyTOCFooter" toc-even-footer-value="1" />', $this->options['front_page_content']);
			
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
			//$this->options['last_page_content'] = str_replace('[toc_entry]', '<tocentry content="', $this->options['last_page_content']);
			//$this->options['last_page_content'] = str_replace('[/toc_entry]', '" />', $this->options['last_page_content']);
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
			$data .= "<table style='font-size:".$this->options[$page.'_font_size']."pt;line-height:".$this->options[$page.'_line_height']."; width: 100%;'>";
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
/*
				$desc = '';
				if ( trim ( $value['comments'] ) ) {
					$desc .= trim ( $value['comments'] );
				}
				$desc = preg_replace ( "/[\n|\r]/", ", ", $desc );
				$desc = preg_replace ( "/,\s*,/", ",", $desc );
				$desc = stripslashes ( stripslashes ( $desc ) );
				if ( $desc ) {
					$display_string .= ' - ' . $desc;
				}
*/
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
				if ( isset($value['email_contact']) && $value['email_contact'] != '' && $this->options['include_meeting_email'] == '1' ) { 
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
			$my_admin_page = add_menu_page( 'Meeting List', 'Meeting List', 'edit_posts', basename(__FILE__), array(&$this, 'admin_options_page'),'', 3 );
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
				$this->options['front_page_content'] = $_POST['front_page_content'];   
				$this->options['last_page_content'] = $_POST['last_page_content'];   
				$this->options['front_page_line_height'] = $_POST['front_page_line_height'];   
				$this->options['front_page_font_size'] = $_POST['front_page_font_size'];   
				$this->options['last_page_font_size'] = $_POST['last_page_font_size'];   
				$this->options['last_page_line_height'] = $_POST['last_page_line_height'];   
				$this->options['content_font_size'] = $_POST['content_font_size'];   
				$this->options['header_font_size'] = $_POST['header_font_size'];   
				$this->options['header_text_color'] = $_POST['header_text_color'];   
				$this->options['header_background_color'] = $_POST['header_background_color'];   
				$this->options['header_uppercase'] = $_POST['header_uppercase'];   
				$this->options['header_bold'] = $_POST['header_bold'];   
				$this->options['page_height_fix'] = $_POST['page_height_fix'];
				$this->options['column_gap'] = $_POST['column_gap'];
				$this->options['margin_right'] = $_POST['margin_right'];
				$this->options['margin_left'] = $_POST['margin_left'];   
				$this->options['margin_bottom'] = $_POST['margin_bottom'];   
				$this->options['margin_top'] = $_POST['margin_top'];   
				$this->options['page_size'] = $_POST['page_size'];   
				$this->options['page_orientation'] = $_POST['page_orientation'];   
				$this->options['page_fold'] = $_POST['page_fold'];   
				$this->options['meeting_sort'] = $_POST['meeting_sort'];   
				$this->options['borough_suffix'] = $_POST['borough_suffix'];   
				$this->options['county_suffix'] = $_POST['county_suffix'];   
				$this->options['meeting_template'] = $_POST['meeting_template'];   
				$this->options['meeting_template_content'] = $_POST['meeting_template_content'];   
				$this->options['show_status'] = $_POST['show_status'];   
				$this->options['column_line'] = $_POST['column_line'];   
				$this->options['col_color'] = $_POST['col_color'];   
				$this->options['custom_section_content'] = $_POST['custom_section_content'];   
				$this->options['custom_section_line_height'] = $_POST['custom_section_line_height'];   
				$this->options['custom_section_font_size'] = $_POST['custom_section_font_size'];   		
				$this->options['include_zip'] = $_POST['include_zip'];
				$this->options['used_format_1'] = $_POST['used_format_1'];
				$this->options['include_meeting_email'] = $_POST['include_meeting_email'];
				$this->options['include_protection'] = $_POST['include_protection'];
				$this->options['weekday_language'] = $_POST['weekday_language'];
				$this->options['include_asm'] = $_POST['include_asm'];
				$this->options['bmlt_login_id'] = $_POST['bmlt_login_id'];
				$this->options['bmlt_login_password'] = $_POST['bmlt_login_password'];
				$this->options['protection_password'] = $_POST['protection_password'];
				$this->options['time_option'] = $_POST['time_option'];   
				$this->options['time_clock'] = $_POST['time_clock'];   
				$this->options['remove_space'] = $_POST['remove_space'];   
				$this->options['content_line_height'] = $_POST['content_line_height'];   
				$this->options['root_server'] = $_POST['root_server'];   
				$this->options['service_body_1'] = $_POST['service_body_1'];   
				$this->options['service_body_2'] = $_POST['service_body_2'];   
				$this->options['service_body_3'] = $_POST['service_body_3'];   
				$this->options['service_body_4'] = $_POST['service_body_4'];   
				$this->options['service_body_5'] = $_POST['service_body_5'];
				$this->options['cache_time'] = $_POST['cache_time'];
				$this->options['extra_meetings'] = $_POST['extra_meetings'];
				$this->save_admin_options();
				set_transient( 'admin_notice', 'Please put down your weapon. You have 20 seconds to comply.' );
				echo '<div class="updated"><p style="color: #F00;">Your changes were successfully saved!</p>';
				$num = $this->delete_transient_cache();
				if ( $num > 0 ) {
					echo "<p>$num Cache entries deleted</p>";
				}
				echo '</div>';
			} elseif ( $_COOKIE['pwsix_action'] == "import_settings" ) {
				echo '<div class="updated"><p style="color: #F00;">Your file was successfully imported!</p></div>';
				setcookie('pwsix_action', NULL, -1);
				$num = $this->delete_transient_cache();
			} elseif ( $_COOKIE['pwsix_action'] == "default_settings_success" ) {
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
			if ( !isset($this->options['meeting_template']) || strlen(trim($this->options['meeting_template'])) == 0 ) {
				$this->options['meeting_template'] = '1';
			}
			if ( !isset($this->options['meeting_template_content']) || strlen(trim($this->options['meeting_template_content'])) == 0 ) {
				$this->options['meeting_template_content'] = '';
			}
			if ( !isset($this->options['show_status']) || strlen(trim($this->options['show_status'])) == 0 ) {
				$this->options['show_status'] = '0';
			}
			if ( !isset($this->options['column_line']) || strlen(trim($this->options['column_line'])) == 0 ) {
				$this->options['column_line'] = '0';
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
			if ( !isset($this->options['include_zip']) || strlen(trim($this->options['include_zip'])) == 0 ) {
				$this->options['include_zip'] = '0';
			}			
			if ( !isset($this->options['used_format_1']) || strlen(trim($this->options['used_format_1'])) == 0 ) {
				$this->options['used_format_1'] = '';
			}			
			if ( !isset($this->options['used_format_2']) || strlen(trim($this->options['used_format_2'])) == 0 ) {
				$this->options['used_format_2'] = '';
			}			
			if ( !isset($this->options['include_meeting_email']) || strlen(trim($this->options['include_meeting_email'])) == 0 ) {
				$this->options['include_meeting_email'] = '0';
			}			
			if ( !isset($this->options['include_protection']) || strlen(trim($this->options['include_protection'])) == 0 ) {
				$this->options['include_protection'] = '0';
			}			
			if ( !isset($this->options['weekday_language']) || strlen(trim($this->options['weekday_language'])) == 0 ) {
				$this->options['weekday_language'] = 'en';
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
			if ( !isset($this->options['cache_time']) || strlen(trim($this->options['cache_time'])) == 0 ) {
				$this->options['cache_time'] = '0';
			}
			if ( !isset($this->options['extra_meetings'])  ) {
				$this->options['extra_meetings'] = '';
				
			}
			
			?>
			<div class="help-video" id="root-server-video" style="overflow: hidden !important;display:none; height:650px !important; width:900px !important;">
				<span class="b-close"><span>X</span></span>
				<video preload="metadata" id="myVideo" width="900" height="650" controls="controls">
				  <source src="https://drive.google.com/uc?export=download&id=0B3q2TxZOVo34OEZ3X3JtUF91QkE" type="video/mp4">
				  Your browser does not support HTML5 video.
				</video>
			</div>
			<div class="help-video" id="service-body-video" style="overflow: hidden !important;display:none; height:650px !important; width:900px !important;">
				<span class="b-close"><span>X</span></span>
				<video preload="metadata" id="myVideo" width="900" height="650" controls="controls">
				  <source src="https://drive.google.com/uc?export=download&id=0B3q2TxZOVo34dVczQS1xSDZTeDA" type="video/mp4">
				  Your browser does not support HTML5 video.
				</video>
			</div>
			<div class="help-video" id="current-meeting-list-video" style="overflow: hidden !important;display:none; height:650px !important; width:900px !important;">
				<span class="b-close"><span>X</span></span>
				<video preload="none" id="myVideo" width="900" height="650" controls="controls">
					<source type="video/youtube" src="http://youtu.be/qG5Iu1vtCU0?vq=hd1080" />
				  Your browser does not support HTML5 video.
				</video>
			</div>
			<div class="hide wrap" id="meeting-list-tabs-wrapper">
				<h2>BMLT Meeting List Generator</h2>
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
					$this_connected = $this->testRootServer($this->options['root_server']);
					$bmlt_version = $this_connected;
					$this_version = intval(str_replace(".", "", $this_connected));
					$newyorkna = $this->newyorknaRootServer();
					$newyorkna_version = intval(str_replace(".", "", $newyorkna));
					$connect = "<p><div style='color: #f00;font-size: 16px;vertical-align: middle;' class='dashicons dashicons-no'></div><span style='color: #f00;'>Connection to BMLT Server Failed.  Check spelling or try again.  If you are certain spelling is correct, BMLT Server could be down.</span></p>";
					if ( $this_connected ) {
						$ThisVersion = "<span style='color: #00AD00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-smiley'></div>Your BMLT Server is running the latest Version ".$bmlt_version."</span>";
						if ( $this_version !== $newyorkna_version ) {
							$ThisVersion = "<span style='color: #f00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>Notice: BMLT Server Update Available! Your Version = ".$bmlt_version.". </span>";
							$ThisVersion .= "<span style='color: #7AD03A;'><i>Updated version = " . $newyorkna . "</i></span><br />";
							//$ThisVersion .= "<span style='color: #f00;'>Install the current version of BMLT Server for the latest features, optimal security and bug fixes.</span>";
						}
					}
					?>
					<div id="setup">						
						<div id="poststuff">
							<div id="postbox-container" class="postbox-container">
								<div id="accordion">
									<h3 class="help-accordian"><strong>Read This Section First</strong></h3>
									<div>
										<h2>Getting Started</h2>
										<p>The Meeting List Generator plugin is first activated using a "Tri Fold - Landscape - Letter Size" layout. This is a "starter" meeting list that uses an Area with about 100 meetings.  The starter meeting list will contain standard content for a basic meeting list that can be printed on a home computer.  A basic NA logo will be added to your media libray.  The starter meeting list uses a logo being hosted on <a target="_blank" href="http://nameetinglist.org">http://nameetinglist.org</a>.</p>
										<h2>Step 1.</h2>
										<p>Click on the BMLT Server tab to the left.  Change the BMLT Server and click the Save Changes button.</p>
										<p><em>To find your BMLT Server click on the red question (?) mark.</em></p>
										<h2>Step 2.</h2>
										<p>From the Service Body 1 dropdown select your Area or Region.  Then click Save Changes.</p>
										<h2>Step 3.</h2>
										<p>Click Generate Meeting List.  Your meeting list will open in a new tab or window.</p>
										<h2>Step 4.</h2>
										<p>See the "Meeting List Setup" section below for additional defaults.</p>
										<p><em>Repeat steps 1, 2 and 3 after changing to new Default Settings.</em></p>
										<h2>What Now?</h2>
										<p>From here you will move forward with setting up your meeting list by exploring the Page Layout, Front Page, Custom Section, Meetings, etc tabs.  There are countless ways to setup a meeting list.</p>
										<p>Please allow yourself to experiment with mixing and matching different settings and content.  There is a good chance you can find a way to match or at least come very close to your current meeting list.</p>
										<p>When setting up the meeting list it is helpful to have some knowledge of HTML when using the editors.  Very little or no knowledge of HTML is required to maintain the meeting list after the setup.  If you get stuck or would like some help with the setup, read the Support section below.</p>
									</div>
									<h3 class="help-accordian">Meeting List Setup</h3>
									<div>
										<h2>Default Settings and Content</h2>
										<p>Changing the Default Settings and Content should only be considered when first using the Meeting List Generator or when you decide to completely start over with setting up your meeting list.</p>
										<p><i>The buttons below will completely reset your meeting list settings (and content) to whichever layout you choose. There is no Undo.</i></p>
										<p style="color: #f00; margin-bottom: 15px;">Consider backing up settings by using the Backup/Restore Tab before changing your Meeting List Settings.</p>
										<input type="submit" value="Tri Fold - Letter Size" id="submit_three_column" class="button-primary" />
										<input type="submit" value="Quad Fold - Legal Size" id="submit_four_column" class="button-primary" />
										<input type="submit" value="Half Fold - Booklet" id="submit_booklet" class="button-primary" />
										<h2>Small or Medium Size Areas</h2>
										<p>Areas with up to about 100 meetings would benefit from using the tri-fold layout on letter sized paper.  Areas larger than 100 meetings would typically use a quad fold meeting list on legal sized paper.  These are just basic guidelines and are by no means set in stone.  For example, an Area with over 100 meetings could use the tri-fold on letter sized paper using smaller fonts to allow the content to fit.  The meeting list configuration is extremely flexible.</p>
										<p></i>The Custom Content section is used to add information like helplines, service meetings, meeting format legend, etc.</i></p>
										<h2>Large Areas, Metro Areas or Regions</h2>
										<p>Larger service bodies would benefit from using a booklet meeting list.</p>
										<p></i>The booklet uses the Front and Last pages for custom content.  There is no Custom Content section on a booklet meeting list.</i></p>
										<h2>Support</h2>
										<p>Assistance is available with setting up a meeting list.  Visit the support forum at <a href="http://nameetinglist.org/forums/forum/support/" target="_blank">nameetinglist.org</a> or send an email to webservant@nameetinglist.org.</p>
									</div>
									<h3 class="help-accordian">Multiple Meeting Lists</h3>
									<div>
										<p>Currently, this tool supports one meeting list per site.</p>
										<p>The following methods could be used to get additional meeting lists.</p>
										<p>Method 1. Host additional meeting lists on <a target="_blank" href="http://nameetinglist.org/get-a-meeting-list/">nameetinglist.org</a>.</p>
										<p>Method 2. Install additional Wordpress installations on your server.  For example:</p>
										<ol>
										<li>Add a sub-domain for each meeting list. For example:</li>
										<ul>
										<li>area1.region.org</li>
										<li>area2.region.org</li>
										<li>area3.region.org</li>
										</ul>
										<li>Install Wordpress on each sub-domain.</li>
										<li>Install the BMLT Meeting List Generator plugin on each sub-domain.</li>
										<li>Provide the login to each Wordpress installation to each local web-servant.</li>
										</ol>
										<p>Method 3. Create a Wordpress multi-site installation.  This is how nameetinglist.org is setup.</p>
									</div>
									<h3 class="help-accordian">Support and Help</h3>
									<div>
										<p>Visit the <a target="_blank" href="http://nameetinglist.org/forums/">Support Forum</a> or email <a target="_blank" href="http://nameetinglist.org/contact/">webservant@nameetinglist.org</a></p>
									</div>
									<!--
									<h3 class="help-accordian">Video Overview</h3>
									<div class="tutorial-video" style="overflow: hidden !important;height:496px !important;">
										<video preload="metadata" id="myVideo" height="496" controls="controls">
											<source src="http://nameetinglist.org/videos/overview-good.mp4" type="video/mp4">
											Your browser does not support HTML5 video.
										</video>
									</div>
									-->
								</div>
							</div>
							<br class="clear">
						</div>
					</div>
					<div id="tabs-first">						
						<div id="poststuff">
							<div id="postbox-container" class="postbox-container">
								<div id="normal-sortables" class="meta-box-sortables ui-sortable">
									<div id="bmltrootserverurl" class="postbox">
										<h3 class="hndle">BMLT Server<span title='<p>Visit <a target="_blank" href="http://bmlt.magshare.net/what-is-the-bmlt/hit-parade/#bmlt-server">BMLT Server Implementations</a> to find your BMLT server</p>' class="tooltip"></span></h3>
										<div class="inside">
											<p>
											<label for="root_server">BMLT Server URL: </label>
											<input class="bmlt-input" id="root_server" type="text" name="root_server" value="<?php echo $this->options['root_server']; ?>" />
											</p>
											<?
											if ( $this_connected ) {
												echo $ThisVersion;
											} elseif ( empty($this->options['root_server']) ) {
												echo "<span style='color: #f00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>ERROR: Please enter a BMLT Server</span>";
											} else {
												echo "<span style='color: #f00;'><div style='font-size: 16px;vertical-align: middle;' class='dashicons dashicons-dismiss'></div>ERROR: Problem Connecting to BMLT Server</span>";
											}
											?>
											<?php if ($this_connected) { ?>
												<?php $unique_areas = $this->get_areas($this->options['root_server']); ?>
												<?php asort($unique_areas); ?>
											<?php } ?>
											<ul>
												<li>
													<label for="service_body_1">Service Body 1: </label>
													<select class="service_body_select" id="service_body_1" name="service_body_1">
													<?php if ($this_connected) { ?>
														<option value="Not Used">Not Used</option>
														<?php foreach($unique_areas as $unique_area){ ?>
															<?php $area_data = explode(',',$unique_area); ?>
															<?php $area_name = $area_data[0]; ?>
															<?php $area_id = $area_data[1]; ?>
															<?php $area_parent = $area_data[2]; ?>
															<?php $area_parent_name = $area_data[3]; ?>
															<?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
															<?php $is_data = explode(',',$this->options['service_body_1']); ?>
															<?php if ( $area_id == $is_data[1] ) { ?>
																<option selected="selected" value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } else { ?>
																<option value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } ?>
														<?php } ?>
													<?php } else { ?>
														<option selected="selected" value="<?php echo $this->options['service_body_1']; ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
													<?php } ?>
													</select>
												</li> 
												<li>
													<label for="service_body_2">Service Body 2: </label>
													<select class="service_body_select" id="service_body_2" name="service_body_2">
													<?php if ($this_connected) { ?>
														<option value="Not Used">Not Used</option>
														<?php foreach($unique_areas as $unique_area){ ?>
															<?php $area_data = explode(',',$unique_area); ?>
															<?php $area_name = $area_data[0]; ?>
															<?php $area_id = $area_data[1]; ?>
															<?php $area_parent = $area_data[2]; ?>
															<?php $area_parent_name = $area_data[3]; ?>
															<?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
															<?php $is_data = explode(',',$this->options['service_body_2']); ?>
															<?php if ( $area_id == $is_data[1] ) { ?>
																<option selected="selected" value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } else { ?>
																<option value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } ?>
														<?php } ?>
													<?php } else { ?>
														<option selected="selected" value="<?php echo $this->options['service_body_2']; ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
													<?php } ?>
													</select>
												</li> 
												<li>
													<label for="service_body_3">Service Body 3: </label>
													<select class="service_body_select" id="service_body_3" name="service_body_3">
													<?php if ($this_connected) { ?>
														<option value="Not Used">Not Used</option>
														<?php foreach($unique_areas as $unique_area){ ?>
															<?php $area_data = explode(',',$unique_area); ?>
															<?php $area_name = $area_data[0]; ?>
															<?php $area_id = $area_data[1]; ?>
															<?php $area_parent = $area_data[2]; ?>
															<?php $area_parent_name = $area_data[3]; ?>
															<?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
															<?php $is_data = explode(',',$this->options['service_body_3']); ?>
															<?php if ( $area_id == $is_data[1] ) { ?>
																<option selected="selected" value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } else { ?>
																<option value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } ?>
														<?php } ?>
													<?php } else { ?>
														<option selected="selected" value="<?php echo $this->options['service_body_3']; ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
													<?php } ?>
													</select>
												</li> 
												<li>
													<label for="service_body_4">Service Body 4: </label>
													<select class="service_body_select" id="service_body_4" name="service_body_4">
													<?php if ($this_connected) { ?>
														<option value="Not Used">Not Used</option>
														<?php foreach($unique_areas as $unique_area){ ?>
															<?php $area_data = explode(',',$unique_area); ?>
															<?php $area_name = $area_data[0]; ?>
															<?php $area_id = $area_data[1]; ?>
															<?php $area_parent = $area_data[2]; ?>
															<?php $area_parent_name = $area_data[3]; ?>
															<?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
															<?php $is_data = explode(',',$this->options['service_body_4']); ?>
															<?php if ( $area_id == $is_data[1] ) { ?>
																<option selected="selected" value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } else { ?>
																<option value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } ?>
														<?php } ?>
													<?php } else { ?>
														<option selected="selected" value="<?php echo $this->options['service_body_4']; ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
													<?php } ?>
													</select>
												</li> 
												<li>
													<label for="service_body_5">Service Body 5: </label>
													<select class="service_body_select" id="service_body_5" name="service_body_5">
													<?php if ($this_connected) { ?>
														<option value="Not Used">Not Used</option>
														<?php foreach($unique_areas as $unique_area){ ?>
															<?php $area_data = explode(',',$unique_area); ?>
															<?php $area_name = $area_data[0]; ?>
															<?php $area_id = $area_data[1]; ?>
															<?php $area_parent = $area_data[2]; ?>
															<?php $area_parent_name = $area_data[3]; ?>
															<?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?></option>
															<?php $is_data = explode(',',$this->options['service_body_5']); ?>
															<?php if ( $area_id == $is_data[1] ) { ?>
																<option selected="selected" value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } else { ?>
																<option value="<?= $unique_area ?>"><?= $option_description ?></option>
															<?php } ?>
														<?php } ?>
													<?php } else { ?>
														<option selected="selected" value="<?php echo $this->options['service_body_5']; ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
													<?php } ?>
													</select>
												</li> 
											</ul>
										</div>
									</div>
									<div id="extrameetingsdiv" class="postbox">
										<h3 class="hndle">Include Extra Meetings<span title='<p>Inlcude Extra Meetings from Another Service Body.</p><p>All Meetings from your BMLT Server are shown in the list.</p><p>The Meetings you select will be merged into your meeting list.</p><p><em>Note: Be sure to select all meetings for each group.</em>' class="tooltip"></span></h3>
										<div class="inside">
											<?php if ($this_connected) { ?>
												<?php $extra_meetings_array = $this->get_all_meetings($this->options['root_server']); ?>
											<?php } ?>
											<p class="ctrl_key" style="display:none; color: #00AD00;">Hold CTRL Key down to select multiple meetings.</p>
											<select class="chosen-select" style="width: 100%;" data-placeholder="Select Extra Meetings" id="extra_meetings" name="extra_meetings[]" multiple="multiple">
											<?php if ($this_connected) { ?>								
												<?php foreach($extra_meetings_array as $extra_meeting){ ?>
													<?php $extra_meeting_x = explode('|||',$extra_meeting); ?>
													<?php $extra_meeting_id = $extra_meeting_x[3]; ?>									
													<?php $extra_meeting_display = substr($extra_meeting_x[0], 0, 30) . ';' . $extra_meeting_x[1] . ';' . $extra_meeting_x[2]; ?>
													<option <?= (in_array($extra_meeting_id, $this->options['extra_meetings']) ? 'selected="selected"' : '') ?> value="<?= $extra_meeting_id ?>"><?= $extra_meeting_display ?></option>
												<?php } ?>
											<?php } else { ?>
												<option selected="selected" value="none"><?php echo 'Not Connected - Can not get Extra Meetings'; ?></option>
											<?php } ?>
											</select>
												
											<p>Hint: Type a group name, weekday or area to narrow down your choices.</p>
										</div>
										
									</div>
									<div id="currentmeetinglistlinkdiv" class="postbox">
										<h3 class="hndle">Current Meeting List Link<span title='<p>Share the "Current Meeting List Link" on your website, email, etc to generate this meeting list.</p>' class="tooltip"></span></h3>
										<div class="inside">
											<p><a target="_blank" href='<?= home_url() ?>/?current-meeting-list=1'><?= home_url() ?>/?current-meeting-list=1</a></p>
										</div>
									</div>
									<div id="meetinglistcachediv" class="postbox">
										<h3 class="hndle">Meeting List Cache (<?= $this->count_transient_cache(); ?> Cached Entries)<span title='<p>Meeting List data is cached (as database transient) to generate a Meeting List faster.</p><p><i>CACHE is DELETED when you Save Changes.</i></p><p><b>The meeting list will not reflect changes to BMLT until the cache expires or is deleted.</b></p>' class="tooltip"></span></h3>
										<div class="inside">
											<?php global $_wp_using_ext_object_cache; ?>
											<?php if ( $_wp_using_ext_object_cache ) { ?>
												<p>This site is using an external object cache.</p>
											<?php } ?>
											<ul>
												<li>
													<label for="cache_time">Cache Time: </label>
													<input class="bmlt-input-field" id="cache_time" onKeyPress="return numbersonly(this, event)" type="number" min="0" max="999" size="3" maxlength="3" name="cache_time" value="<?php echo $this->options['cache_time'] ;?>" />&nbsp;&nbsp;<i>0 - 999 Hours (0 = disable cache)</i>&nbsp;&nbsp;
												</li>
											</ul>
											<p><i><b>CACHE is DELETED when you Save Changes.</b></i></p>
										</div>
									</div>
								</div>
							<input type="submit" value="Save Changes" id="bmltmeetinglistsave1" name="bmltmeetinglistsave" class="button-primary" />
							<?= '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
							<div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
							<br class="clear">
							</div>
						</div>
					</div>
					<div id="layout">
						<div id="poststuff">
							<div id="postbox-container" class="postbox-container">
								<div id="normal-sortables" class="meta-box-sortables ui-sortable">
									<div id="pagelayoutdiv" class="postbox">
										<?PHP $title = '
										<p class="bmlt-heading-h2">Page Layout Defaults</p>
										<table style="border-collapse: collapse; font-size:12px;" border="1" cellpadding="8">
										<tbody>
										<tr>
										<td><strong>Meeting List Size</strong></td>
										<td><strong>Page Layout</strong></td>
										<td><strong>Orientation</strong></td>
										<td><strong>Paper Size</strong></td>
										<td><strong>Page Height</strong></td>
										</tr>
										<tr>
										<td>Smaller Areas</td>
										<td>Tri-Fold</td>
										<td>Landscape</td>
										<td>Letter, A4</td>
										<td>195, 180</td>
										</tr>
										<tr>
										<td>Medium Area</td>
										<td>Quad-Fold</td>
										<td>Landscape</td>
										<td>Legal, A4</td>
										<td>195, 180</td>
										</tr>
										<tr>
										<td>Large Area, Region, Metro</td>
										<td>Half-Fold</td>
										<td>Landscape</td>
										<td>Booklet, A5</td>
										<td>250, 260</td>
										</tr>
										<tr>
										<td>Anything</td>
										<td>Full Page</td>
										<td>Portrait, Landscape</td>
										<td>Letter, Legal, A4</td>
										<td>None</td>
										</tr>
										</tbody>
										</table>
										<p>When a layout is clicked defaults are reset for orientation, paper size and page height.</p>
										';
										?>
										<h3 class="hndle">Page Layout<span title='<?PHP echo $title; ?>' class="bottom-tooltip"></span></h3>
										<div class="inside">
											<p>
											<input class="mlg" id="tri" type="radio" name="page_fold" value="tri" <?= ($this->options['page_fold'] == 'tri' ? 'checked' : '') ?>><label for="tri">Tri-Fold&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="quad" type="radio" name="page_fold" value="quad" <?= ($this->options['page_fold'] == 'quad' ? 'checked' : '') ?>><label for="quad">Quad-Fold&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="half" type="radio" name="page_fold" value="half" <?= ($this->options['page_fold'] == 'half' ? 'checked' : '') ?>><label for="half">Half-Fold&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="full" type="radio" name="page_fold" value="full" <?= ($this->options['page_fold'] == 'full' ? 'checked' : '') ?>><label for="full">Full Page</label>
											</p>
											<p>
											<input class="mlg" id="portrait" type="radio" name="page_orientation" value="P" <?= ($this->options['page_orientation'] == 'P' ? 'checked' : '') ?>><label for="portrait">Portrait&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="landscape" type="radio" name="page_orientation" value="L" <?= ($this->options['page_orientation'] == 'L' ? 'checked' : '') ?>><label for="landscape">Landscape</label>
											<p>
											<input class="mlg" id="5inch" type="radio" name="page_size" value="5inch" <?= ($this->options['page_size'] == '5inch' ? 'checked' : '') ?>><label for="5inch">Booklet (11" X 8.5")&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="A5" type="radio" name="page_size" value="A5" <?= ($this->options['page_size'] == 'A5' ? 'checked' : '') ?>><label for="A5">Booklet-A5 (297mm X 210mm)&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="letter" type="radio" name="page_size" value="letter" <?= ($this->options['page_size'] == 'letter' ? 'checked' : '') ?>><label for="letter">Letter (8.5" X 11")&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="legal" type="radio" name="page_size" value="legal" <?= ($this->options['page_size'] == 'legal' ? 'checked' : '') ?>><label for="legal">Legal (8.5" X 14")&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="ledger" type="radio" name="page_size" value="ledger" <?= ($this->options['page_size'] == 'ledger' ? 'checked' : '') ?>><label for="ledger">Ledger (17" X 11")&nbsp;&nbsp;&nbsp;</label>
											<input class="mlg" id="A4" type="radio" name="page_size" value="A4" <?= ($this->options['page_size'] == 'A4' ? 'checked' : '') ?>><label for="A4">A4 (210mm X 297mm)</label>
											</p>
											</p>
											<div id="marginsdiv" style="border-top: 1px solid #EEE;">
												<p>
												Page Margin Top: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_top" name="margin_top" value="<?php echo $this->options['margin_top'] ;?>" />&nbsp;&nbsp;&nbsp;
												Bottom: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_bottom" name="margin_bottom" value="<?php echo $this->options['margin_bottom'] ;?>" />&nbsp;&nbsp;&nbsp;
												Left: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_left" name="margin_left" value="<?php echo $this->options['margin_left'] ;?>" />&nbsp;&nbsp;&nbsp;
												Right: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_right" name="margin_right" value="<?php echo $this->options['margin_right'] ;?>" />&nbsp;&nbsp;&nbsp;
												</p>
											</div>
											<div id="columngapdiv" style="border-top: 1px solid #EEE;">
												<p>
												Column Gap Width: <input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="column_gap" name="column_gap" value="<?php echo $this->options['column_gap'] ;?>" />									
												</p>
											</div>
											<div id="columnseparatordiv" style="border-top: 1px solid #EEE;">
											
												<p>
												<table><tr>
												<input class="mlg" name="column_line" value="0" type="hidden">
												<td style="">Separator: <input type="checkbox" name="column_line" value="1" <?= ($this->options['column_line'] == '1' ? 'checked' : '') ?> /></td>
												<td style="">
													<div class="theme" id="sp-light">
														<label for="col_color">Color:</label>  <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="col_color" name="col_color" value="<?php echo $this->options['col_color'] ;?>" />
													</div>
												</td>
												</tr></table>
												
												</p>
											</div>
											<div id="includeprotection" style="border-top: 1px solid #EEE;">
												<?PHP $title = '
												<p>Enable <strong>PDF Protection</strong>.</p>
												<p>Encrypts and sets the PDF document permissions for the PDF file.</p>
												
												<p>PDF can be opened and printed.
												
												<p>Optional Password to allow editing in a PDF editor.
												<p>Note: PDF is encrypted and cannot be opened in MS Word at all.</p>
												';
												?>
												<input name="include_protection" value="0" type="hidden">
												<p><input type="checkbox" name="include_protection" value="1" <?= ($this->options['include_protection'] == '1' ? 'checked' : '') ?>>Enable PDF Protection<span title='<?PHP echo $title; ?>' class="top-tooltip"></span></p>
												<p>
												<label for="protection_password">Password: </label>
												<input class="protection_pass" id="protection_password" type="password" name="protection_password" value="<?php echo $this->options['protection_password'] ;?>" />
												</p>
											</div>
											<!--
											<div id="progressbardiv">
												<h3 class="hndle">Progress Bar</h3>
												<div class="inside">
													<input class="mlg" name="show_status" value="0" type="hidden">
													<p><input class="mlg" type="checkbox" name="show_status" value="1" <?//= ($this->options['show_status'] == '1' ? 'checked' : '') ?>>Show Progress Bar during Meeting List Generation</p>
												</div>
											</div>
											-->
										</div>
									</div>
								</div>
							</div>
							<br class="clear">
						</div>
						<input type="submit" value="Save Changes" id="bmltmeetinglistsave2" name="bmltmeetinglistsave" class="button-primary" />
						<?= '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
						<div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
					</div>
					<div id="front-page">
						<div id="poststuff">
							<div id="postbox-container" class="postbox-container">
								<div id="normal-sortables" class="meta-box-sortables ui-sortable">
									<div id="frontpagecontentdiv" class="postbox">
										<?PHP $title = '
										<p>The Front Page can be customized with text, graphics, tables, shortcodes, ect.</p>
										<p><strong>Add Media</strong> button - upload and add graphics.</p>
										<p><strong>Meeting List Shortcodes</strong> dropdown - insert custom data.</p>
										<p><strong>Default Font Size</strong> can be changed for specific text.</p>
										';
										?>
										<h3 class="hndle">Front Page Content<span title='<?PHP echo $title; ?>' class="tooltip"></span></h3>
											<div class="inside">
											<p>Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="front_page_font_size" name="front_page_font_size" value="<?php echo $this->options['front_page_font_size'] ;?>" />&nbsp;&nbsp;
											Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="front_page_line_height" type="text" maxlength="3" size="3" name="front_page_line_height" value="<?php echo $this->options['front_page_line_height'] ;?>" /></p>
											<div style="margin-top:15px; margin-bottom:20px; max-width:100%; width:100%;">
												<?
												$editor_id = "front_page_content";
												$settings    = array (
													'tabindex'      => FALSE,
													'editor_height'	=> 500,
													'resize'        => TRUE,
													"media_buttons"	=> TRUE,
													"drag_drop_upload" => TRUE,
													"editor_css"	=> "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
													"teeny"			=> FALSE,
													'quicktags'		=> TRUE,
													'wpautop'		=> FALSE,
													'textarea_name' => $editor_id,
													'tinymce'=> array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'front_page_button')
												);
												wp_editor( stripslashes($this->options['front_page_content']), $editor_id, $settings );
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<br class="clear">
						</div>
						<input type="submit" value="Save Changes" id="bmltmeetinglistsave3" name="bmltmeetinglistsave" class="button-primary" />
						<?= '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
						<div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
					</div>
					<div id="meetings">
						<div id="poststuff">
							<div id="postbox-container" class="postbox-container">
								<div id="normal-sortables" class="meta-box-sortables ui-sortable">
									<div id="meetingsheaderdiv" class="postbox">
										<?PHP $title = '
										<p>Customize the Meeting Group Header to your specification.</p>
										<p>The Meeting Group Header will contain the data from Group By.</p>
										';
										?>
										<h3 class="hndle">Meeting Group [Column] Header<span title='<?PHP echo $title; ?>' class="tooltip"></span></h3>
										<div class="inside">
											<div style="margin-bottom: 10px; padding:0;" id="accordion2">
												<h3 class="help-accordian">Instructions</h3>
												<div class="videocontent">
													<video id="my_video_1"  style="width:100%;height:100%;" controls="controls" width="100%" height="100%" preload="auto">
														<source src="http://nameetinglist.org/videos/meeting_group_header.mp4" type="video/mp4">
														Your browser does not support HTML5 video.
													</video>
												</div>
											</div>
											<table><tr>
											<td style="padding-right: 10px;">Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="header_font_size" name="header_font_size" value="<?php echo $this->options['header_font_size']; ?>" /></td>
											<td style="padding-right: 10px;">
												<div class="theme" id="sp-light">
													<label for="header_text_color">Text Color:</label>  <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="header_text_color" name="header_text_color" value="<?php echo $this->options['header_text_color']; ?>" />
												</div>
											</td>
											<td style="padding-right: 10px;">
												<div class="theme" id="sp-light">
													<label for="header_background_color">Background Color:</label>  <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="header_background_color" name="header_background_color" value="<?php echo $this->options['header_background_color']; ?>" />
												</div>
											</td>
											<td style="padding-right: 10px;">
												<input name="header_uppercase" value="0" type="hidden">
											<td><label for="header_uppercase">Uppercase: </label><input type="checkbox" name="header_uppercase" value="1" <?= ($this->options['header_uppercase'] == '1' ? 'checked' : '') ?>></td>
											<td style="padding-right: 10px;">
												<input name="header_bold" value="0" type="hidden">
											<td><label for="header_bold">Bold: </label><input type="checkbox" name="header_bold" value="1" <?= ($this->options['header_bold'] == '1' ? 'checked' : '') ?>></td>
											</tr></table>
											<p>
												<div class="group_by" style="margin-right: 10px; display: inline;">
													<label for="meeting_sort">Group Meetings By: </label>
													<select id="meeting_sort" name="meeting_sort">					
														<option <?= ($this->options['meeting_sort'] == 'day' ? 'selected="selected"' : '') ?> value="day">Weekday</option>
														<option <?= ($this->options['meeting_sort'] == 'city' ? 'selected="selected"' : '') ?> value="city">City</option>
														<option <?= ($this->options['meeting_sort'] == 'group' ? 'selected="selected"' : '') ?> value="group">Group</option>
														<option <?= ($this->options['meeting_sort'] == 'county' ? 'selected="selected"' : '') ?> value="county">County</option>
														<option <?= ($this->options['meeting_sort'] == 'borough' ? 'selected="selected"' : '') ?> value="borough">Borough</option>
														<option <?= ($this->options['meeting_sort'] == 'borough_county' ? 'selected="selected"' : '') ?> value="borough_county">Borough+County</option>
														<option <?= ($this->options['meeting_sort'] == 'state' ? 'selected="selected"' : '') ?> value="state">State+City</option>
														<option <?= ($this->options['meeting_sort'] == 'weekday_area' ? 'selected="selected"' : '') ?> value="weekday_area">Weekday+Area</option>
														<option <?= ($this->options['meeting_sort'] == 'weekday_city' ? 'selected="selected"' : '') ?> value="weekday_city">Weekday+City</option>
													</select>
												</div>
												<div class="borough_by_suffix">
												
													<p>
													<label for="borough_suffix">Borough Suffix: </label>
													<input class="borough-by-suffix" id="borough_suffix" type="text" name="borough_suffix" value="<?php echo $this->options['borough_suffix']; ?>" />
													
													</p>
													
												</div>
												<div class="county_by_suffix">
																								
													<p>
													<label for="county_suffix">County Suffix: </label>
													<input class="county-by-suffix" id="county_suffix" type="text" name="county_suffix" value="<?php echo $this->options['county_suffix']; ?>" />
													
													</p>
												</div>
												<div class="weekday_language_div" style="display: inline;">
													<label for="weekday_language">Weekday Language: </label>											
													<select name="weekday_language">
													<?php if ( $this->options['weekday_language'] == 'en' || $this->options['weekday_language'] == '' ) { ?>
														<option selected="selected" value="en">English</option>
														<option value="es">Spanish</option>
														<option value="fr">French</option>
														<option value="po">Portuguese</option>
														<option value="both">English/Spanish</option>
														<option value="both_po">English/Spanish/Portuguese</option>
														<option value="fr_en">French/English</option>
													<?php } elseif ( $this->options['weekday_language'] == 'es' ) { ?>
														<option selected="selected" value="es">Spanish</option>
														<option value="en">English</option>
														<option value="fr">French</option>
														<option value="po">Portuguese</option>
														<option value="both">English/Spanish</option>
														<option value="both_po">English/Spanish/Portuguese</option>
														<option value="fr_en">French/English</option>
													<?php } elseif ( $this->options['weekday_language'] == 'both' ) { ?>
														<option selected="selected" value="both">English/Spanish</option>
														<option value="en">English</option>
														<option value="es">Spanish</option>
														<option value="fr">French</option>
														<option value="po">Portuguese</option>
														<option value="both_po">English/Spanish/Portuguese</option>
														<option value="fr_en">French/English</option>
													<?php } elseif ( $this->options['weekday_language'] == 'fr' ) { ?>
														<option selected="selected" value="fr">French</option>
														<option value="en">English</option>
														<option value="es">Spanish</option>
														<option value="po">Portuguese</option>
														<option value="both">English/Spanish</option>
														<option value="both_po">English/Spanish/Portuguese</option>
														<option value="fr_en">French/English</option>
													<?php } elseif ( $this->options['weekday_language'] == 'po' ) { ?>
														<option selected="selected" value="po">Portuguese</option>
														<option value="en">English</option>
														<option value="es">Spanish</option>
														<option value="fr">French</option>
														<option value="both">English/Spanish</option>
														<option value="both_po">English/Spanish/Portuguese</option>
														<option value="fr_en">French/English</option>
													<?php } elseif ( $this->options['weekday_language'] == 'fr_en' ) { ?>
														<option selected="selected" value="fr_en">French/Engish</option>
														<option value="en">English</option>
														<option value="es">Spanish</option>
														<option value="fr">French</option>
														<option value="po">Portuguese</option>
														<option value="both">English/Spanish</option>
														<option value="both_po">English/Spanish/Portuguese</option>
													<?php } elseif ( $this->options['weekday_language'] == 'both_po' ) { ?>
														<option selected="selected" value="both_po">English/Spanish/Portuguese</option>
														<option value="en">English</option>
														<option value="es">Spanish</option>
														<option value="fr">French</option>
														<option value="po">Portuguese</option>
														<option value="both">English/Spanish</option>
														<option value="fr_en">French/English</option>
													<?php } ?>
													</select>
												</div>
											<p>
										</div>
									</div>
									<div id="custommeetingtemplatediv" class="postbox">
										<?PHP $title = '
										<div style="width:550px; margin-bottom:20px;">
										<p>The <strong>Meeting Template</strong> is a powerful and flexible method for customizing meetings using
										HTML markup and BMLT field names.  The template is set-up once and never needs to be messed
										with again.  Note: When changes are made to the Default Font Size or Line Height, the template
										may need to be adjusted to reflect those changes.</p>
										<p>Sample templates can be found in the editor drop down menu <strong>Meeting Template</strong>.</p>
										<p>BMLT fields can be found in the editor drop down menu <strong>Meeting Template Fields</strong>.</p>
										<p>The <strong>Default Font Size and Line Height</strong> will be used for the meeting template.</p> 
										<p>Font Size and Line Height can be overridden using HTML mark-up in the meeting text.</p> 
										</div>
										';
										?>
										<h3 class="hndle">Meeting Template<span title='<?PHP echo $title; ?>' class="top-tooltip"></span></h3>
										<div class="inside">
											<div style="margin-bottom: 10px; padding:0;" id="accordion3">
												<h3 class="help-accordian">Instructions</h3>
												<div class="videocontent">
													<video id="my_video_1"  style="width:100%;height:100%;" controls="controls" width="100%" height="100%" preload="auto">
														<source src="http://nameetinglist.org/videos/nameetinglist.mp4" type="video/mp4">
														Your browser does not support HTML5 video.
													</video>
												</div>
											</div>
											<p>
											Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_font_size" name="content_font_size" value="<?php echo $this->options['content_font_size'] ;?>" />&nbsp;&nbsp;
											Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="content_line_height" type="text" maxlength="3" size="3" name="content_line_height" value="<?php echo $this->options['content_line_height'] ;?>" />&nbsp;&nbsp
											<?PHP $title = '
											<p>Page Height Adjust will add or remove space at the bottom of the meeting list.</p>
											<p>1. Decrease value if a Group Header is missing at the top of the meeting list (-5, -10, etc).</p>
											<p>2. increase value when using a small meeting font to fit more meetings (+5, +10, etc).</p>
											';
											?>
											Page Height Adjust: <input min="-50" max="50" step="1" size="4" maxlength="4" type="number" class="bmlt-input-field" style="display:inline;" name="page_height_fix" value="<?php echo $this->options['page_height_fix'] ;?>" /><span title='<?PHP echo $title; ?>' class="top-middle-tooltip"></span>											
											</p>
											<div><i>Decrease Page Height Adjust if <strong>MEETING GROUP HEADER</strong> is missing.</i></div>
											<div style="margin-top:0px; margin-bottom:20px; max-width:100%; width:100%;">
												<?
												$editor_id = "meeting_template_content";
												$settings    = array (
													'tabindex'      => FALSE,
													'editor_height'	=> 110,
													'resize'        => TRUE,
													"media_buttons"	=> FALSE,
													"drag_drop_upload" => TRUE,
													"editor_css"	=> "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
													"teeny"			=> FALSE,
													'quicktags'		=> TRUE,
													'wpautop'		=> FALSE,
													'textarea_name' => $editor_id,
													'tinymce'=> array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'custom_template_button_1,custom_template_button_2')
												);
												wp_editor( stripslashes($this->options['meeting_template_content']), $editor_id, $settings );
												?>
											</div>
										</div>
									</div>
									<div id="starttimeformatdiv" class="postbox">
										<?PHP $title = '
										<p>Format the <strong>Start Time</strong> (start_time) field in the <strong>Meeting Template</strong>.</p>
										';
										?>
										<h3 class="hndle">Start Time Format<span title='<?PHP echo $title; ?>' class="top-tooltip"></span></h3>
										<div class="inside">
											<?php $space = ' '; ?>
											<?php if ( $this->options['remove_space'] == '1' ) { ?>
												<?php $space = ''; ?>
											<?php } ?>
											<?php if ( $this->options['time_clock'] == '12' ) { ?>
												<?php $start_time = "8:00".$space."PM"; ?>
												<?php $start_time_2 = "8".$space; ?>
												<?php $end_time = "9:00".$space."PM"; ?>
												<?php $end_time_2 = "9".$space."PM"; ?>
											<?php } elseif ( $this->options['time_clock'] == '24fr' ) { ?>
												<?php $start_time = "20h00"; ?>
												<?php $end_time = "21h00"; ?>
											<?php } else { ?>
												<?php $start_time = "20:00"; ?>
												<?php $end_time = "21:00"; ?>
											<?php } ?>
											<table>
											<tr>
											<td style="padding-right: 30px;">
												<div><input class="mlg" id="time_clock12" type="radio" name="time_clock" value="12" <?= ($this->options['time_clock'] == '12' || $this->options['time_clock'] == '' ? 'checked' : '') ?>><label for="time_clock">12 Hour</label></div>
											</td>
											<td style="padding-right: 30px;">
												<div><input class="mlg" id="option1" type="radio" name="time_option" value="1" <?= ($this->options['time_option'] == '1' || $this->options['time_option'] == '' ? 'checked' : '') ?>><label for="option1"><?= $start_time ?></label></div>
											</td>
											<td style="padding-right: 30px;">
											<?php if ( $this->options['remove_space'] == '0' || $this->options['remove_space'] == '' ) { ?>
												<div><input class="mlg" id="two" type="radio" name="remove_space" value="0" checked><label for="two">Add White Space</label></div>
											<?php } else { ?>
												<div><input class="mlg" id="two" type="radio" name="remove_space" value="0"><label for="two">Add White Space</label></div>
											<?php } ?>
											</td>
											</tr>
											<tr>
											<td style="padding-right: 30px;">
												<div><input class="mlg" id="time_clock24" type="radio" name="time_clock" value="24" <?= ($this->options['time_clock'] == '24' ? 'checked' : '') ?>><label for="time_clock">24 Hour</label></div>
											</td>
											<td style="padding-right: 30px;">
												<div><input class="mlg" id="option2" type="radio" name="time_option" value="2" <?= ($this->options['time_option'] == '2' ? 'checked' : '') ?>><label for="option2"><?= $start_time ?><?= $space ?>-<?= $space ?><?= $end_time ?></label></div>
											</td>
											<td style="padding-right: 30px;">								
											<?php if ( $this->options['remove_space'] == '1' ) { ?>
												<div><input class="mlg" id="four" type="radio" name="remove_space" value="1" checked><label for="four">Remove White Space</label></div>
											<?php } else { ?>
												<div><input class="mlg" id="four" type="radio" name="remove_space" value="1"><label for="four">Remove White Space</label></div>
											<?php } ?>									 
											</td>
											</tr>
											</tr>
											<tr>
											<td style="padding-right: 30px;">
												<div><input class="mlg" id="time_clock24fr" type="radio" name="time_clock" value="24fr" <?= ($this->options['time_clock'] == '24fr' ? 'checked' : '') ?>><label for="time_clock">24 Hour French</label></div>
											</td>
											<td style="padding-right: 30px;">
												<div><input class="mlg" id="option3" type="radio" name="time_option" value="3" <?= ($this->options['time_option'] == '3' ? 'checked' : '') ?>><label for="option3"><?= $start_time_2 ?><?= $space ?>-<?= $space ?><?= $end_time_2 ?></label></div>
											</td>
											<td style="padding-right: 30px;">								
											</td>
											</tr>
											</table>
										</div>
									</div>
									<div id="getusedformatsdiv" class="postbox">
										<?PHP $title = '
										<p>Create a special interest meeting list.</p>
										';
										?>
										<h3 class="hndle">Include Only This Meeting Format<span title='<?PHP echo $title; ?>' class="top-tooltip"></span></h3>
										<div class="inside">
											<?php if ($this_connected) { ?>
												<?php $used_formats = $this->getUsedFormats(); ?>
											<?php } ?>
											<label for="used_format_1">Meeting Format: </label>
											<select id="used_format_1" name="used_format_1">
											<?php if ($this_connected) { ?>
												<option value="">Not Used</option>
												<?php $countmax = count ( $used_formats ); ?>
												<?php for ( $count = 0; $count < $countmax; $count++ ) { ?>
													<?php if ( $used_formats[$count]['id'] == $this->options['used_format_1'] ) { ?>
														<option selected="selected" value="<?= $used_formats[$count]['id'] ?>"><?= $used_formats[$count]['name_string'] ?></option>
													<?php } else { ?>
														<option value="<?= $used_formats[$count]['id'] ?>"><?= $used_formats[$count]['name_string'] ?></option>
													<?php } ?>
												<?php } ?>
											<?php } else { ?>
												<option selected="selected" value="<?php echo $this->options['used_format_1']; ?>"><?php echo 'Not Connected - Can not get Formats'; ?></option>
											<?php } ?>
											</select>
										</div>
									</div>
									<?php $connected = ''; ?>
									<?php if ( $this->options['include_meeting_email'] == '1' ) { ?>
										<?php $logged_in = $this->testEmailPassword($this->options['root_server'],$this->options['bmlt_login_id'],$this->options['bmlt_login_password']); ?>
										<?php $connected = "<p><div style='color: #f00;font-size: 16px;vertical-align: middle;' class='dashicons dashicons-no'></div><span style='color: #f00;'>Login ID or Password Incorrect</span></p>"; ?>
										<?php if ( $logged_in == 'OK') { ?>
											<?php $connected = "<p><div style='color: #00AD00;font-size: 16px;vertical-align: middle;' class='dashicons dashicons-smiley'></div><span style='color: #00AD00;'>Login OK</span></p>"; ?>
										<?php } ?>
										
									<?php } ?>
									<div id="includeemaildiv" class="postbox">
										<?PHP $title = '
										<p>Enable the <strong>Meeting Email Contact</strong> (email_contact) field in the <strong>Meeting Template</strong>.</p>
										<p>This feature requires a login ID and password for the service body.</p>
										<p>This can be Service Body Administrator or Observer.</p>
										<p>Visit the <a target="_blank" href="http://bmlt.magshare.net/specific-topics/bmlt-roles/">BMLT Roles</a> page for more details.</p>
										';
										?>
										<h3 class="hndle">Meeting Email Contact<span title='<?PHP echo $title; ?>' class="top-tooltip"></span></h3>
										<div class="inside">
											<input name="include_meeting_email" value="0" type="hidden">
											<p><input type="checkbox" name="include_meeting_email" value="1" <?= ($this->options['include_meeting_email'] == '1' ? 'checked' : '') ?>>Enable</p>
											<p>
											<label for="bmlt_login_id">Login ID: </label>
											<input class="bmlt-login" id="bmlt_login_id" type="text" name="bmlt_login_id" value="<?php echo $this->options['bmlt_login_id'] ;?>" />&nbsp;&nbsp;&nbsp;&nbsp;
											<label for="bmlt_login_password">Password: </label>
											<input class="bmlt-login" id="bmlt_login_password" type="password" name="bmlt_login_password" value="<?php echo $this->options['bmlt_login_password'] ;?>" />  <?php echo $connected; ?>
											</p>
										</div>
									</div>
									<div id="includeasmdiv" class="postbox">
										<?PHP $title = '
										<p>Show <strong>Area Service Meetings</strong> (ASM) in the meeting list.</p>
										<p>In BMLT a meeting can have the format code ASM indicating it is a service meeting.</p>
										<p>Typically Areas show their Area Service Meetings separately on the meeting list</p>
										<p>and may not want to show the Area Service Meetings again in the list of regular meetings.</p>
										<p>To list the Area Service Meetings in the list of regular meetings enable this check-box.</p>
										';
										?>
										<h3 class="hndle">Show Area Service Meetings<span title='<?PHP echo $title; ?>' class="top-tooltip"></span></h3>
										<div class="inside">
											<div style="margin-bottom: 10px; padding:0;" id="accordion_asm">
												<h3 class="help-accordian">Instructions</h3>
												<div class="videocontent">
													<video id="my_video_1"  style="width:100%;height:100%;" controls="controls" width="100%" height="100%" preload="auto">
														<source src="http://nameetinglist.org/videos/show_area_service_meetings.mp4" type="video/mp4">
														Your browser does not support HTML5 video.
													</video>
												</div>
											</div>
											<input name="include_asm" value="0" type="hidden">
											<p><input type="checkbox" name="include_asm" value="1" <?= ($this->options['include_asm'] == '1' ? 'checked' : '') ?>>Enable</p>
										</div>
									</div>
								</div>
							</div>
							<br class="clear">
						</div>
						<input type="submit" value="Save Changes" id="bmltmeetinglistsave4" name="bmltmeetinglistsave" class="button-primary" />
						<?= '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
						<div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
					</div>
					<div id="custom-section">
						<div id="poststuff">
							<div id="postbox-container" class="postbox-container">
								<div id="normal-sortables" class="meta-box-sortables ui-sortable">
									<div id="custom-content-div" class="postbox">
										<?PHP $title = '
										<p>The Custom Content can be customized with text, graphics, tables, shortcodes, ect.</p>
										<p><strong>Default Font Size</strong> can be changed for specific text in the editor.</p>
										<p><strong>Add Media</strong> button - upload and add graphics.</p>
										<p><strong>Meeting List Shortcodes</strong> dropdown - insert variable data.</p>
										<p><i>The Custom Content will print immediately after the meetings in the meeting list.</i></p>
										';
										?>
										<h3 class="hndle">Custom Content<span title='<?PHP echo $title; ?>' class="bottom-tooltip"></span></h3>
										<div class="inside">
											<p>Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="custom_section_font_size" name="custom_section_font_size" value="<?php echo $this->options['custom_section_font_size'] ;?>" />&nbsp;&nbsp;
											Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="custom_section_line_height" type="text" maxlength="3" size="3" name="custom_section_line_height" value="<?php echo $this->options['custom_section_line_height'] ;?>" /></p>
											<div style="margin-top:15px; margin-bottom:20px; max-width:100%; width:100%;">
												<?
												$editor_id = "custom_section_content";
												$settings    = array (
													'tabindex'      => FALSE,
													'editor_height'	=> 500,
													'resize'        => TRUE,
													"media_buttons"	=> TRUE,
													"drag_drop_upload" => TRUE,
													"editor_css"	=> "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
													"teeny"			=> FALSE,
													'quicktags'		=> TRUE,
													'wpautop'		=> FALSE,
													'textarea_name' => $editor_id,
													'tinymce'=> array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'front_page_button')
												);
												wp_editor( stripslashes($this->options['custom_section_content']), $editor_id, $settings );
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<input type="submit" value="Save Changes" id="bmltmeetinglistsave5" name="bmltmeetinglistsave" class="button-primary" />
						<?= '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
						<div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
					</div>
					<div id="last-page">
						<div id="poststuff">
							<div id="postbox-container" class="postbox-container">
								<div id="normal-sortables" class="meta-box-sortables ui-sortable">
									<div id="lastpagecontentdiv" class="postbox">
										<?PHP $title = '
										<p class="bmlt-heading-h2">Last Page Content<p>
										<p>Any text or graphics can be entered into this section.
										';
										?>
										<h3 class="hndle">Last Page Content<span title='<?PHP echo $title; ?>' class="tooltip"></span></h3>
											<div class="inside">
											<p>Default Font Size: <input min="4" max="18" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="last_page_font_size" name="last_page_font_size" value="<?php echo $this->options['last_page_font_size'] ;?>" />&nbsp;&nbsp;
											Line Height: <input min="1" max="3" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="last_page_line_height" type="text" maxlength="3" size="3" name="last_page_line_height" value="<?php echo $this->options['last_page_line_height'] ;?>" /></p>
											<div style="margin-top:15px; margin-bottom:20px; max-width:100%; width:100%;">
												<?
												$editor_id = "last_page_content";
												$settings    = array (
													'tabindex'      => FALSE,
													'editor_height'	=> 500,
													'resize'        => TRUE,
													"media_buttons"	=> TRUE,
													"drag_drop_upload" => TRUE,
													"editor_css"	=> "<style>.aligncenter{display:block!important;margin-left:auto!important;margin-right:auto!important;}</style>",
													"teeny"			=> FALSE,
													'quicktags'		=> TRUE,
													'wpautop'		=> FALSE,
													'textarea_name' => $editor_id,
													'tinymce'=> array('toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,link,unlink,table,undo,redo,fullscreen', 'toolbar2' => 'formatselect,fontsizeselect,fontselect,forecolor,backcolor,indent,outdent,pastetext,removeformat,charmap,code', 'toolbar3' => 'front_page_button')
												);
												wp_editor( stripslashes($this->options['last_page_content']), $editor_id, $settings );
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<br class="clear">
						</div>
						<input type="submit" value="Save Changes" id="bmltmeetinglistsave6" name="bmltmeetinglistsave" class="button-primary" />
						<?= '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
						<div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
					</div>
					</form>
					<div id="import-export">
						<div id="poststuff">
							<div id="postbox-container" class="postbox-container">
								<div id="normal-sortables" class="meta-box-sortables ui-sortable">
									<div id="exportdiv" class="postbox">
										<h3 class="hndle">Export Meeting List Settings</h3>
										<div class="inside">
											<p><?php _e( 'Export or backup meeting list settings.' ); ?></p>
											<p><?php _e( 'This allows you to easily import meeting list settings into another site.' ); ?></p>
											<p><?php _e( 'Also useful for backing up before making significant changes to the meeting list settings.' ); ?></p>
											<form method="post">
												<p><input type="hidden" name="pwsix_action" value="export_settings" /></p>
												<p>
													<?php wp_nonce_field( 'pwsix_export_nonce', 'pwsix_export_nonce' ); ?>
													<?php submit_button( __( 'Export' ), 'button-primary', 'submit', false ); ?>
												</p>
											</form>
										</div>
									</div>
									<div style="margin-bottom: 0px;" id="exportdiv" class="postbox">
										<h3 class="hndle">Import Meeting List Settings</h3>
										<div class="inside">
											<p><?php _e( 'Import meeting list settings from a previously exported meeting list.' ); ?></p>
											<form id="form_import_file" method="post" enctype="multipart/form-data">
												<p><input type="file" required name="import_file"/></p>
												<p>
													<input type="hidden" name="pwsix_action" value="import_settings" />
													<?php wp_nonce_field( 'pwsix_import_nonce', 'pwsix_import_nonce' ); ?>
													<?php submit_button( __( 'Import' ), 'button-primary', 'submit_import_file', false, array( 'id' => 'submit_import_file' ) ); ?>
												</p>
												<div id="basicModal">
													<p style="color:#f00;">Your current meeting list settings will be replaced and lost forever.</p>
													<p>Consider backing up your settings by using the Export function.</p>
												</div>
												<div id="nofileModal" title="File Missing">
													<div style="color:#f00;">Please Choose a File.</div>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
							<br class="clear">
						</div>
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
			if ( isset( $_GET['export-meeting-list'] ) && $_GET['export-meeting-list'] == '1' ) {
			} else {
				if ( $_POST['bmltmeetinglistsave'] == 'Save Changes' )
					return;
				if( empty( $_POST['pwsix_action'] ) || 'export_settings' != $_POST['pwsix_action'] )
					return;
				if( ! wp_verify_nonce( $_POST['pwsix_export_nonce'], 'pwsix_export_nonce' ) )
					return;
				if( ! current_user_can( 'manage_options' ) )
					return;
					
			}
			$blogname = str_replace(" - ", " ", get_option('blogname'));
			$blogname = str_replace(" ", "-", $blogname);
			$date = date("m-d-Y");
			$blogname = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($blogname)), '-');
			$json_name = $blogname."-meeting-list-settings-".$date.".json"; // Namming the filename will be generated.
			$settings = get_option( $this->optionsName );
			foreach ($settings as $key => $value) {
				$value = maybe_unserialize($value);
				$need_options[$key] = $value;
			}
			$json_file = json_encode($need_options); // Encode data into json data
			ignore_user_abort( true );
//			nocache_headers();
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
			if ( $_POST['bmltmeetinglistsave'] == 'Save Changes' )
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
			if ( ! current_user_can( 'manage_options' ) || $_POST['bmltmeetinglistsave'] == 'Save Changes' ) {
				return;
			} elseif ( 'three_column_default_settings' == $_POST['pwsix_action'] ) {
				if( ! wp_verify_nonce( $_POST['pwsix_submit_three_column'], 'pwsix_submit_three_column' ) )
					die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$import_file = plugin_dir_path(__FILE__) . "includes/three_column_settings.json";
			} elseif ( 'four_column_default_settings' == $_POST['pwsix_action'] ) {
				if( ! wp_verify_nonce( $_POST['pwsix_submit_four_column'], 'pwsix_submit_four_column' ) )
					die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$import_file = plugin_dir_path(__FILE__) . "includes/four_column_settings.json";
			} elseif ( 'booklet_default_settings' == $_POST['pwsix_action'] ) {
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
	} //End Class bmltmeetinglist
} // end if
//instantiate the class
if (class_exists("BMLTMeetinglist")) {
	$BMLTMeetinglist_instance = new BMLTMeetinglist();
}
?>