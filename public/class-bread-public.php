<?php

use Mpdf\Mpdf;

/**
 * Main class for generating the PDF meeting list.
 *
 * Sets up mPdf and the page layout, does the initial BMLT query, then calls the generator to
 * produce the meeting list contents.  Nasty mPDF stuff should be concentrated here.
 *
 * @package    Bread
 * @subpackage Bread/public
 * @author     bmlt-enabled <help@bmlt.app>
 */
class Bread_Public
{

    /**
     * The ID of this plugin.
     *
     * @since  2.8.0
     * @access private
     * @var    string    $plugin_name    The ID of this plugin.
     */
    private string $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since  2.8.0
     * @access private
     * @var    string    $version    The current version of this plugin.
     */
    private string $version;
    /**
     * The settings/ meeting list configuration.  Everything that was filled in in the WP Backend.
     *
     * @since  2.8.0
     * @access private
     * @var array The settings/ meeting list configuration.  Everything that was filled in in the WP Backend.
     */
    private array $options;
    /**
     * Does the work of translating the HTML to PDF.
     *
     * @since  2.8.0
     * @access private
     * @var object Does the work of translating the HTML to PDF.
     */
    private Mpdf $mpdf;
    /**
     * Initialize the class and set its properties.
     *
     * @since 2.8.0
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since 2.8.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/bread-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 2.8.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/bread-public.js', array('jquery'), $this->version, false);
    }

    public function bmlt_meeting_list($atts = null, $content = null)
    {
        $this->options = Bread::getMLOptions(Bread::getRequestedSetting());
        $import_streams = [];
        ini_set('max_execution_time', 600); // tomato server can take a long time to generate a schedule, override the server setting

        if ($this->options['root_server'] == '') {
            echo '<p><strong>bread Error: BMLT Server missing.<br/><br/>Please go to Settings -> bread and verify BMLT Server</strong></p>';
            exit;
        }
        if ($this->options['service_body_1'] == 'Not Used' && true === ($this->options['custom_query'] == '')) {
            echo '<p><strong>bread Error: Service Body 1 missing from configuration.<br/><br/>Please go to Settings -> bread and verify Service Body</strong><br/><br/>Contact the bread administrator and report this problem!</p>';
            exit;
        }
        if (headers_sent()) {
            echo '<div id="message" class="error"><p>Headers already sent before Meeting List generation</div>';
            exit;
        }

        if (intval($this->options['cache_time']) > 0 && ! isset($_GET['nocache'])
            && ! isset($_GET['custom_query'])
        ) {
            if (false !== ($content = get_transient(Bread::get_TransientKey(Bread::getRequestedSetting())))) {
                $content = pack("H*", $content);
                $name = $this->get_FilePath();
                header('Content-Type: application/pdf');
                header('Content-Length: ' . strlen($content));
                header('Content-disposition: inline; filename="' . $name . '"');
                header('Cache-Control: public, must-revalidate, max-age=0');
                header('Pragma: public');
                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                echo $content;
                exit;
            }
        }
        $page_type_settings = $this->constuct_page_type_settings();
        Bread::UpgradeSettings();
        $default_font = $this->options['base_font'] == "freesans" ? "dejavusanscondensed" : $this->options['base_font'];
        $mode = 's';
        $mpdf_init_options = $this->construct_init_options($default_font, $mode, $page_type_settings);
        @ob_end_clean();
        // We load mPDF only when we need to and as late as possible.  This prevents
        // conflicts with other plugins that use the same PSRs in different versions
        // by simply clobbering the other definitions.  Since we generate the PDF then
        // die, we shouldn't create any conflicts ourselves.
        include_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
        require_once __DIR__ . '/class-bread-content-generator.php';
        require_once __DIR__ . '/class-bread-format-manager.php';
        $this->mpdf = new mPDF($mpdf_init_options);
        $this->mpdf->setAutoBottomMargin = 'pad';
        $this->mpdf->shrink_tables_to_fit = 1;

        $this->mpdf->simpleTables = false;
        $this->mpdf->useSubstitutions = false;
        $this->mpdf->mirrorMargins = false;
        $this->mpdf->list_indent_first_level = 1; // 1 or 0 - whether to indent the first level of a list
        // LOAD a stylesheet
        $header_stylesheet = file_get_contents(plugin_dir_path(__FILE__) . 'css/mpdfstyletables.css');
        $this->mpdf->WriteHTML($header_stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
        $this->mpdf->SetDefaultBodyCSS('line-height', $this->options['content_line_height']);
        $this->mpdf->SetDefaultBodyCSS('background-color', '#ffffff00');
        if ($this->options['column_line'] == 1
            && ($this->options['page_fold'] == 'tri' || $this->options['page_fold'] == 'quad')
        ) {
            $this->drawLinesSeperatingColumns($mode, $mpdf_init_options['format'], $default_font);
        }
        $sort_keys = 'weekday_tinyint,start_time,meeting_name';
        $get_used_formats = '&get_used_formats';
        $select_language = '';
        if ($this->options['weekday_language'] != Bread_Bmlt::get_bmlt_server_lang()) {
            $select_language = '&lang_enum=' . $this->getSingleLanguage($this->options['weekday_language']);
        }
        $services = Bread_Bmlt::generateDefaultQuery();
        if (isset($_GET['custom_query'])) {
            $services = $_GET['custom_query'];
        } elseif ($this->options['custom_query'] !== '') {
            $services = $this->options['custom_query'];
        }
        if ($this->options['used_format_1'] == '') {
            $result = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys$get_used_formats$select_language");
        } elseif ($this->options['used_format_1'] != '') {
            $result = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults$services&sort_keys=$sort_keys&get_used_formats&formats[]=" . $this->options['used_format_1'] . $select_language);
        }

        if ($result_meetings == null) {
            echo "<script type='text/javascript'>\n";
            echo "document.body.innerHTML = ''";
            echo "</script>";
            echo '<div style="font-size: 20px;text-align:center;font-weight:normal;color:#F00;margin:0 auto;margin-top: 30px;"><p>No Meetings Found</p><p>Or</p><p>Internet or Server Problem</p><p>' . $this->options['root_server'] . '</p><p>Please try again or contact your BMLT Administrator</p></div>';
            exit;
        }
        if (!empty($this->options['extra_meetings'])) {
            $extras = "";
            foreach ((array)$this->options['extra_meetings'] as $value) {
                $data = array(" [", "]");
                $value = str_replace($data, "", $value);
                $extras .= "&meeting_ids[]=" . $value;
            }

            $extra_result = Bread_Bmlt::get_configured_root_server_request("client_interface/json/?switcher=GetSearchResults&sort_keys=" . $sort_keys . "" . $extras . "" . $get_used_formats . $select_language);
            $formatsManager = null;
            if ($extra_result <> null) {
                $result_meetings = array_merge($result['meetings'], $extra_result['meetings']);
                foreach ($result_meetings as $key => $row) {
                    $weekday[$key] = $row['weekday_tinyint'];
                    $start_time[$key] = $row['start_time'];
                }

                array_multisort($weekday, SORT_ASC, $start_time, SORT_ASC, $result_meetings);
                $formatsManager = new Bread_FormatsManager(array_merge($result['formats'], $extra_result['formats']), $this->options['weekday_language']);
            } else {
                $formatsManager = new Bread_FormatsManager($result['formats'], $this->options['weekday_language']);
                $result_meetings = $result['meetings'];
            }
        } else {
            $formatsManager = new Bread_FormatsManager($result['formats'], $this->options['weekday_language']);
            $result_meetings = $result['meetings'];
        }

        if ($this->options['additional_list_language'] == '') {
            $this->options['additional_list_language'] = $this->options['weekday_language'];
        }
        $num_columns = 0;
        if ($this->options['page_fold'] === 'full' || $this->options['page_fold'] === 'half' || $this->options['page_fold'] === 'flyer') {
            $num_columns = 0;
        } elseif ($this->options['page_fold'] === 'tri') {
            $num_columns = 3;
        } elseif ($this->options['page_fold'] === 'quad') {
            $num_columns = 4;
        } elseif ($this->options['page_fold'] === '') {
            $this->options['page_fold'] = 'quad';
            $num_columns = 4;
        }
        $generator = new Bread_ContentGenerator($this->mpdf, $this->options, $result_meetings, $formatsManager);
        $generator->generate($num_columns);
        $this->mpdf->SetDisplayMode('fullpage', 'two');
        $this->reorder_booklet_pages($mode);
        if ($this->options['include_protection'] == 1) {
            // 'copy','print','modify','annot-forms','fill-forms','extract','assemble','print-highres'
            $this->mpdf->SetProtection(array('copy', 'print', 'print-highres'), '', $this->options['protection_password']);
        }
        if (headers_sent()) {
            echo '<div id="message" class="error"><p>Headers already sent before PDF generation</div>';
        } else {
            if (intval($this->options['cache_time']) > 0 && ! isset($_GET['nocache'])
                && !isset($_GET['custom_query'])
            ) {
                $content = $this->mpdf->Output('', 'S');
                $content = bin2hex($content);
                $transient_key = Bread::get_TransientKey(Bread::getRequestedSetting());
                set_transient($transient_key, $content, intval($this->options['cache_time']) * HOUR_IN_SECONDS);
            }
            $FilePath = apply_filters("Bread_Download_Name", $this->get_FilePath(), $this->options['service_body_1'], Bread::getSettingName(Bread::getRequestedSetting()));
            $this->mpdf->Output($FilePath, 'I');
        }
        foreach ($import_streams as $FilePath => $stream) {
            @unlink($FilePath);
        }
        Bread::removeTempDir();
        exit;
    }
    private function constuct_page_type_settings()
    {
        // TODO: The page number is always 5 from botton...this should be adjustable
        if ($this->options['page_fold'] == 'half') {
            if ($this->options['page_size'] == 'letter') {
                $page_type_settings = ['format' => array(139.7, 215.9), 'margin_footer' => $this->options['margin_footer']];
            } elseif ($this->options['page_size'] == 'legal') {
                $page_type_settings = ['format' => array(177.8, 215.9), 'margin_footer' => $this->options['margin_footer']];
            } elseif ($this->options['page_size'] == 'ledger') {
                $page_type_settings = ['format' => 'letter-P', 'margin_footer' => $this->options['margin_footer']];
            } elseif ($this->options['page_size'] == 'A4') {
                $page_type_settings = ['format' => 'A5-P', 'margin_footer' => $this->options['margin_footer']];
            } elseif ($this->options['page_size'] == 'A5') {
                $page_type_settings = ['format' => 'A6-P', 'margin_footer' => $this->options['margin_footer']];
            } elseif ($this->options['page_size'] == '5inch') {
                $page_type_settings = ['format' => array(197.2, 279.4), 'margin_footer' => $this->options['margin_footer']];
            }
        } elseif ($this->options['page_fold'] == 'flyer') {
            if ($this->options['page_size'] == 'letter') {
                $page_type_settings = ['format' => array(93.13, 215.9), 'margin_footer' => $this->options['margin_footer']];
            } elseif ($this->options['page_size'] == 'legal') {
                $page_type_settings = ['format' => array(118.53, 215.9), 'margin_footer' => $this->options['margin_footer']];
            } elseif ($this->options['page_size'] == 'ledger') {
                $page_type_settings = ['format' => array(143.93, 279.4), 'margin_footer' => $this->options['margin_footer']];
            } elseif ($this->options['page_size'] == 'A4') {
                $page_type_settings = ['format' => array(99.0, 210.0), 'margin_footer' => $this->options['margin_footer']];
            }
        } elseif ($this->options['page_fold'] == 'full') {
            $ps = $this->options['page_size'];
            if ($ps == 'ledger') {
                $ps = 'tabloid';
            }
            $page_type_settings = ['format' => $ps . "-" . $this->options['page_orientation'], 'margin_footer' => $this->options['margin_footer']];
        } else {
            $ps = $this->options['page_size'];
            if ($ps == 'ledger') {
                $ps = 'tabloid';
            }
            $page_type_settings = ['format' => $ps . "-" . $this->options['page_orientation'], 'margin_footer' => 0];
        }
        return $page_type_settings;
    }
    private function construct_init_options($default_font, $mode, $page_type_settings): array
    {
        if ($default_font == 'arial' || $default_font == 'times' || $default_font == 'courier') {
            $mpdf_init_options = [
                'fontDir' => array(
                    __DIR__ . '/mpdf/vendor/mpdf/mpdf/ttfonts',
                    __DIR__ . '/fonts',
                ),
                'tempDir' => Bread::temp_dir(),
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
                'margin_header' => $this->options['margin_header'],
            ];
        } else {
            $mpdf_init_options = [
                'mode' => $mode,
                'tempDir' => Bread::temp_dir(),
                'default_font_size' => 7,
                'default_font' => $default_font,
                'margin_left' => $this->options['margin_left'],
                'margin_right' => $this->options['margin_right'],
                'margin_top' => $this->options['margin_top'],
                'margin_bottom' => $this->options['margin_bottom'],
                'margin_header' => $this->options['margin_header'],
            ];
        }
        $mpdf_init_options['restrictColorSpace'] = $this->options['colorspace'];
        $mpdf_init_options = array_merge($mpdf_init_options, $page_type_settings);
        $mpdf_init_options = apply_filters("Bread_Mpdf_Init_Options", $mpdf_init_options, $this->options);

        return $mpdf_init_options;
    }
    private function drawLinesSeperatingColumns($mode, $format, $default_font)
    {
        $html = '<body style="background-color:#fff;">';
        if ($this->options['page_fold'] == 'tri') {
            $html .= '<table style="background-color: #fff;width: 100%; border-collapse: collapse;">
            <tbody>
            <tr>
            <td style="background-color: #fff;width: 33.33%; border-right: 1px solid ' . $this->options['col_color'] . '; height: 279.4mm;">&nbsp;</td>
            <td style="background-color: #fff;width: 33.33%; border-right: 1px solid ' . $this->options['col_color'] . '; height: 279.4mm;">&nbsp;</td>
            <td style="background-color: #fff;width: 33.33%; height: 279.4mm;">&nbsp;</td>
            </tr>
            </tbody>
            </table></body>';
        }
        if ($this->options['page_fold'] == 'quad') {
            $html .= '<table style="background-color: #fff;width: 100%; border-collapse: collapse;">
            <tbody>
            <tr>
            <td style="background-color: #fff;width: 25%; border-right: 1px solid ' . $this->options['col_color'] . '; height: 279.4mm;">&nbsp;</td>
            <td style="background-color: #fff;width: 25%; border-right: 1px solid ' . $this->options['col_color'] . '; height: 279.4mm;">&nbsp;</td>
            <td style="background-color: #fff;width: 25%; border-right: 1px solid ' . $this->options['col_color'] . '; height: 279.4mm;">&nbsp;</td>
            <td style="background-color: #fff;width: 25%; height: 279.4mm;">&nbsp;</td>
            </tr>
            </tbody>
            </table>';
        }
        $mpdf_column = new mPDF(
            [
                'mode' => $mode,
                'tempDir' => Bread::temp_dir(),
                'format' => $format,
                'default_font_size' => 7,
                'default_font' => $default_font,
                'margin_left' => $this->options['margin_left'],
                'margin_right' => $this->options['margin_right'],
                'margin_top' => $this->options['margin_top'],
                'margin_bottom' => $this->options['margin_bottom'],
                'margin_footer' => 0,
                'orientation' => 'P',
                'restrictColorSpace' => $this->options['colorspace'],
            ]
        );

        $mpdf_column->WriteHTML($html);
        $FilePath = Bread::temp_dir() . DIRECTORY_SEPARATOR . $this->get_FilePath('_column');
        $mpdf_column->Output($FilePath, 'F');
        $h = \fopen($FilePath, 'rb');
        $stream = new \setasign\Fpdi\PdfParser\StreamReader($h, false);
        $import_streams[$FilePath] = $stream;
        $pagecount = $this->mpdf->SetSourceFile($stream);
        $tplId = $this->mpdf->importPage($pagecount);
        $this->mpdf->SetPageTemplate($tplId);
    }
    private function reorder_booklet_pages($mode)
    {
        if ($this->options['page_fold'] == 'half') {
            $FilePath = Bread::temp_dir() . DIRECTORY_SEPARATOR . $this->get_FilePath('_half');
            $this->mpdf->Output($FilePath, 'F');
            $mpdfOptions = [
                'mode' => $mode,
                'tempDir' => Bread::temp_dir(),
                'default_font_size' => '',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_footer' => 0,
                'orientation' => 'L',
                'restrictColorSpace' => $this->options['colorspace'],
            ];
            $ps = $this->options['page_size'];
            if ($ps == 'ledger') {
                $mpdfOptions['format'] = 'tabloid';
            } elseif ($ps == '5inch') {
                $mpdfOptions['format'] = array(197.2, 279.4);
            } else {
                $mpdfOptions['format'] = $ps . '-L';
            }
            $mpdfOptions = apply_filters("Bread_Mpdf_Init_Options", $mpdfOptions, $this->options);
            $mpdftmp = new mPDF($mpdfOptions);
            $this->mpdf->shrink_tables_to_fit = 1;
            $ow = $mpdftmp->h;
            $oh = $mpdftmp->w;
            $pw = $mpdftmp->w / 2;
            $ph = $mpdftmp->h;
            $h = \fopen($FilePath, 'rb');
            $stream = new \setasign\Fpdi\PdfParser\StreamReader($h, false);
            $import_streams[$FilePath] = $stream;
            $pagecount = $mpdftmp->SetSourceFile($stream);
            $pp = $this->get_booklet_pages($pagecount);
            foreach ($pp as $v) {
                $mpdftmp->AddPage();
                if ($v[0] > 0 & $v[0] <= $pagecount) {
                    $tplIdx = $mpdftmp->importPage($v[0]);
                    $mpdftmp->UseTemplate($tplIdx, 0, 0, $pw, $ph);
                }
                if ($v[1] > 0 & $v[1] <= $pagecount) {
                    $tplIdx = $mpdftmp->importPage($v[1]);
                    $mpdftmp->UseTemplate($tplIdx, $pw, 0, $pw, $ph);
                }
            }
            $this->mpdf = $mpdftmp;
        } else if ($this->options['page_fold'] == 'full' && $this->options['booklet_pages']) {
            $FilePath = Bread::temp_dir() . DIRECTORY_SEPARATOR . $this->get_FilePath('_full');
            $this->mpdf->Output($FilePath, 'F');
            $mpdfOptions = [
                'mode' => $mode,
                'tempDir' => Bread::temp_dir(),
                'default_font_size' => '',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_footer' => 6,
                'orientation' => $this->options['page_orientation'],
                'restrictColorSpace' => $this->options['colorspace'],
            ];
            $mpdfOptions['format'] =  $this->options['page_size'] . "-" . $this->options['page_orientation'];
            $mpdfOptions = apply_filters("Bread_Mpdf_Init_Options", $mpdfOptions, $this->options);
            $mpdftmp = new mPDF($mpdfOptions);
            $this->mpdf->shrink_tables_to_fit = 1;
            //$mpdftmp->SetImportUse();
            $h = \fopen($FilePath, 'rb');
            $stream = new \setasign\Fpdi\PdfParser\StreamReader($h, false);
            $import_streams[$FilePath] = $stream;
            $np = $mpdftmp->SetSourceFile($stream);
            $pp = 4 * ceil($np / 4);
            for ($i = 1; $i < $np; $i++) {
                $mpdftmp->AddPage();
                $tplIdx = $mpdftmp->ImportPage($i);
                $mpdftmp->UseTemplate($tplIdx);
            }
            for ($i = $np; $i < $pp; $i++) {
                $mpdftmp->AddPage();
            }
            $mpdftmp->AddPage();
            $tplIdx = $mpdftmp->ImportPage($np);
            $mpdftmp->UseTemplate($tplIdx);
            $this->mpdf = $mpdftmp;
        } else if ($this->options['page_fold'] == 'flyer') {
            $FilePath = Bread::temp_dir() . DIRECTORY_SEPARATOR . $this->get_FilePath('_flyer');
            $this->mpdf->Output($FilePath, 'F');
            $mpdfOptions = [
                'mode' => $mode,
                'tempDir' => Bread::temp_dir(),
                'default_font_size' => '',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_footer' => 6,
                'format' => $this->options['page_size'] . '-L',
                'orientation' => 'L',
                'restrictColorSpace' => $this->options['colorspace'],
            ];
            $mpdftmp = new mPDF($mpdfOptions);
            $this->mpdf->shrink_tables_to_fit = 1;
            //$mpdftmp->SetImportUse();
            $h = \fopen($FilePath, 'rb');
            $stream = new \setasign\Fpdi\PdfParser\StreamReader($h, false);
            $import_streams[$FilePath] = $stream;
            $np = $mpdftmp->SetSourceFile($stream);
            $ow = $mpdftmp->w;
            $oh = $mpdftmp->h;
            $fw = $ow / 3;
            $mpdftmp->AddPage();
            $tplIdx = $mpdftmp->importPage(1);
            $mpdftmp->UseTemplate($tplIdx, 0, 0);
            $mpdftmp->UseTemplate($tplIdx, $fw, 0);
            $mpdftmp->UseTemplate($tplIdx, $fw + $fw, 0);
            $sep = $this->columnSeparators($oh);
            if (!empty($sep)) {
                $mpdftmp->writeHTML($sep);
            }
            $mpdftmp->AddPage();
            $tplIdx = $mpdftmp->ImportPage(2);
            $mpdftmp->UseTemplate($tplIdx, 0, 0);
            $mpdftmp->UseTemplate($tplIdx, $fw, 0);
            $mpdftmp->UseTemplate($tplIdx, $fw + $fw, 0);
            if (!empty($sep)) {
                $mpdftmp->writeHTML($sep);
            }
            $this->mpdf = $mpdftmp;
        }
    }
    function get_booklet_pages($np, $backcover = true)
    {
        $lastpage = $np;
        $np = 4 * ceil($np / 4);
        $pp = array();
        for ($i = 1; $i <= $np / 2; $i++) {
            $p1 = $np - $i + 1;
            if ($backcover) {
                if ($i == 1) {
                    $p1 = $lastpage;
                } else if ($p1 >= $lastpage) {
                    $p1 = 0;
                }
            }
            if ($i % 2 == 1) {
                $pp[] = array($p1,  $i);
            } else {
                $pp[] = array($i, $p1);
            }
        }
        return $pp;
    }
    function get_FilePath($pos = '')
    {
        $site = '';
        if (is_multisite()) {
            $site = get_current_blog_id() . '_';
        }
        return "meetinglist_" . $site . Bread::getRequestedSetting() . $pos . '_' . strtolower(date("njYghis")) . ".pdf";
    }

    function getSingleLanguage($lang)
    {
        return substr($lang, 0, 2);
    }
    function columnSeparators($oh)
    {
        if ($this->options['column_line'] == 1) {
            return '<body style="background:none;">
            <table style="background: none;width: 100%; height:' . $oh . 'mm border-collapse: collapse;">
                <tbody>
                <tr>
                <td style="width: 33.33%; border-right: 1px solid ' . $this->options['col_color'] . '; height: ' . $oh . 'mm;">&nbsp;</td>
                <td style="width: 33.33%; border-right: 1px solid ' . $this->options['col_color'] . '; height: ' . $oh . 'mm;">&nbsp;</td>
                <td style="width: 33.33%; height: 100%;">&nbsp;</td>
                </tr>
                </tbody>
                </table>';
        }
    }
}
