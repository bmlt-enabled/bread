<?php
if (! defined('ABSPATH')) {
    exit;
}
class BreadLoadableFonts
{

        var $custom_fonts = [
            'roboto' => ['name' => 'Roboto',
                                      'stack' => 'Sans-Serif - Neo-Grotesque',
                                      'scripts' => ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext', 'greek', 'greek-ext'],
                                      'specimen' => '<a href="https://fonts.google.com/specimen/Roboto" target="_blank">Google Fonts</a>',
                                      'remote' => [
                                        'manifest' => 'https://fonts.google.com/download/list?family=Roboto',
                                        'R' => 'Roboto_Condensed-Regular.ttf',
                                        'B' => 'Roboto_Condensed-Bold.ttf',
                                        'I' => 'Roboto_Condensed-Italic.ttf',
                                        'BI' => 'Roboto_Condensed-BoldItalic.ttf',
                                      ],
                                      'configuration' => [
                                        'useOTL' => 0xFF,
                                        'kashida' => 75,
                                      ]],
            'rubik' => ['name' => 'Rubik',
                                      'stack' => 'Sans-Serif - Neo-Grotesque',
                                      'scripts' => ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext', 'arabic'],
                                      'specimen' => '<a href="https://fonts.google.com/specimen/Rubik" target="_blank">Google Fonts</a>',
                                      'remote' => [
                                        'manifest' => 'https://fonts.google.com/download/list?family=Rubik',
                                        'R' => 'Rubik-Regular.ttf',
                                        'B' => 'Rubik-Bold.ttf',
                                        'I' => 'Rubik-Italic.ttf',
                                        'BI' => 'Rubik-BoldItalic.ttf',
                                      ],
                                      'configuration' => [
                                        'useOTL' => 0xFF,
                                        'kashida' => 75,
                                      ]],
            'oswald' => ['name' => 'Oswald',
                                      'stack' => 'Sans-Serif - Neo-Grotesque',
                                      'scripts' => ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext'],
                                      'specimen' => '<a href="https://fonts.google.com/specimen/Oswald" target="_blank">Google Fonts</a>',
                                      'remote' => [
                                        'manifest' => 'https://fonts.google.com/download/list?family=Oswald',
                                        'R' => 'Oswald-Regular.ttf',
                                        'B' => 'Oswald-Bold.ttf',
                                      ],
                                      'configuration' => [
                                        'useOTL' => 0xFF,
                                        'kashida' => 75,
                                      ]],
            'opensans' => ['name' => 'Open Sans Condensed',
                                      'stack' => 'Sans-Serif - Humanist',
                                      'scripts' => ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext', 'greek'],
                                      'specimen' => '<a href="https://fonts.google.com/specimen/Open+Sans" target="_blank">Google Fonts</a>',
                                      'remote' => [
                                        'manifest' => 'https://fonts.google.com/download/list?family=Open%20Sans',
                                        'R' => 'OpenSans_Condensed-Regular.ttf',
                                        'B' => 'OpenSans_Condensed-Bold.ttf',
                                        'I' => 'OpenSans_Condensed-Italic.ttf',
                                        'BI' => 'OpenSans_Condensed-BoldItalic.ttf',
                                      ],
                                      'configuration' => [
                                        'useOTL' => 0xFF,
                                        'kashida' => 75,
                                      ]],
            'arimo' => ['name' => 'Arimo',
                                      'stack' => 'Sans-Serif - Arial-like',
                                      'scripts' => ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext', 'greek', 'hebrew'],
                                      'specimen' => '<a href="https://fonts.google.com/specimen/Animo" target="_blank">Google Fonts</a>',
                                      'remote' => [
                                        'manifest' => 'https://fonts.google.com/download/list?family=Arimo',
                                        'R' => 'Arimo-Regular.ttf',
                                        'B' => 'Arimo-Bold.ttf',
                                        'I' => 'Arimo-Italic.ttf',
                                        'BI' => 'Arimo-BoldItalic.ttf',
                                      ],
                                      'configuration' => [
                                        'useOTL' => 0xFF,
                                        'kashida' => 75,
                                      ]],
            'ibmplexsans' => ['name' => 'IBM Plex Sans',
                                      'stack' => 'Sans-Serif - Neo Grotesque',
                                      'scripts' => ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext', 'greek'],
                                      'specimen' => '<a href="https://fonts.google.com/specimen/IBM+Plex+Sans" target="_blank">Google Fonts</a>',
                                      'remote' => [
                                        'manifest' => 'https://fonts.google.com/download/list?family=IBM%20Plex%20Sans%20Condensed',
                                        'R' => 'IBMPlexSansCondensed-Regular.ttf',
                                        'B' => 'IBMPlexSansCondensed-Bold.ttf',
                                        'I' => 'IBMPlexSansCondensed-Italic.ttf',
                                        'BI' => 'IBMPlexSansCondensed-BoldItalic.ttf',
                                      ],
                                      'configuration' => [
                                        'useOTL' => 0xFF,
                                        'kashida' => 75,
                                      ]],
            'arvo' => ['name' => 'Arvo',
                                      'stack' => 'Slab-Serif',
                                      'scripts' => ['latin', 'latin-ext'],
                                      'specimen' => '<a href="https://fonts.google.com/specimen/Arvo" target="_blank">Google Fonts</a>',
                                      'remote' => [
                                        'manifest' => 'https://fonts.google.com/download/list?family=Arvo',
                                        'R' => 'Arvo-Regular.ttf',
                                        'B' => 'Arvo-Bold.ttf',
                                        'I' => 'Arvo-Italic.ttf',
                                        'BI' => 'Arvo-BoldItalic.ttf',
                                      ],
                                      'configuration' => [
                                      ]],
            'cairo' => ['name' => 'Cairo',
                                      'stack' => 'Sans-Serif - Superellipse',
                                      'scripts' => ['latin', 'latin-ext', 'arabic'],
                                      'specimen' => '<a href="https://fonts.google.com/specimen/Cairo" target="_blank">Google Fonts</a>',
                                      'remote' => [
                                        'manifest' => 'https://fonts.google.com/download/list?family=Cairo',
                                        'R' => 'Cairo-Regular.ttf',
                                        'B' => 'Cairo-Bold.ttf',
                                      ],
                                      'configuration' => [
                                        'useOTL' => 0xFF,
                                        'kashida' => 75,
                                      ]],
            ];
        public function __construct()
        {
            foreach ($this->custom_fonts as $key => &$font) {
                $font['actions'] = $this->calcActions($key);
            }
            $this->addAdminHooks();
            $this->addPublicHooks();
        }
        public function addAdminHooks()
        {
            add_filter('bread_custom_fonts', [$this, 'bread_custom_fonts'], 1);
            add_filter('bread_content_style', [$this, 'bread_content_style'], 1);
            add_filter('Bread_active_fonts', [$this, 'bread_active_fonts'], 1);
        }
        public function addPublicHooks()
        {
            add_filter("Bread_Mpdf_Init_Options", [$this, 'mpdf_init_options']);
        }
        public function bread_custom_fonts($fonts)
        {
              return array_merge($fonts, $this->custom_fonts);
        }
        private function getUploadDirectory($fontFamily = '', $url = false)
        {
            $uploads = wp_upload_dir();
            if (!empty($uploads['error']) || empty($uploads['basedir'])) {
                return false;
            }
            $dir = $url ? $uploads['baseurl'] : $uploads['basedir'];
            $dir = trailingslashit($dir) . 'bread-uploaded-fonts';
            if (!$url && !wp_mkdir_p($dir)) {
                return false;
            }
            if ($fontFamily != '') {
                $dir = trailingslashit($dir) . $fontFamily;
                if (!$url && !wp_mkdir_p($dir)) {
                    return false;
                }
            }
            return $dir;
        }
        public function bread_active_fonts(array $fonts): array
        {
            return array_merge($fonts, $this->getUploadedFonts());
        }
        private function getUploadedFonts()
        {
            $ret = [];
            $subdirs = (new WP_Filesystem_Direct(null))->dirlist($this->getUploadDirectory(), false, true);
            foreach ($subdirs as $subdir) {
                if ($subdir['type'] !== 'd') {
                    continue;
                }
                if ($this->fontComplete($subdir['name'], array_keys($subdir['files']))) {
                    $ret[] = $subdir['name'];
                }
            }
            return $ret;
        }
        private function fontComplete($fontFamily, $fontFiles)
        {
            if ($fontFamily === '') {
                return false;
            }
            if (!isset($this->custom_fonts[$fontFamily])) {
                return false;
            }

            $fontInfo = $this->custom_fonts[$fontFamily];
            foreach (['R', 'B', 'I', 'BI'] as $key) {
                if (!isset($fontInfo['remote'][$key])) {
                    continue;
                }
                if (!in_array($fontInfo['remote'][$key], $fontFiles)) {
                      return false;
                }
            }

            return true;
        }
        private function getURLtoFileMap(string $manifest): array|false
        {
            $response = wp_remote_get($manifest, array('timeout' => 15, 'redirection' => 3));
            if (is_wp_error($response)) {
                $this->outputWarning("Could not retrieve $manifest");
                return false;
            }
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            if ($code === 200 && is_string($body)) {
                $contents = json_decode(substr($body, 4), true);
                $ret = [];
                foreach ($contents['manifest']['fileRefs'] as $item) {
                    $split = explode('/', $item['filename']);
                    $ret[end($split)] = $item['url'];
                }
                return $ret;
            }
            return false;
        }
        public function installFont(string $fontFamily)
        {
            if (!current_user_can('manage_options')) {
                $this->outputWarning("You must be an administrator to install fonts");
                return;
            }
            if (!isset($this->custom_fonts[$fontFamily])) {
                $this->outputWarning("$fontFamily not in list of defined fonts");
                return;
            }

            $fontInfo = $this->custom_fonts[$fontFamily];
            $fileToUrl = [];
            if (isset($fontInfo['remote']['manifest'])) {
                $fileToUrl = $this->getURLtoFileMap($fontInfo['remote']['manifest']);
                if (!$fileToUrl) {
                    $this->outputWarning("Could not parse manifest file");
                    return;
                }
            } elseif (isset(($fontInfo['remote']['directory']))) {
                $directory = $fontInfo['remote']['directory'];
                foreach (['R', 'B', 'I', 'BI'] as $key) {
                    if (!isset($fontInfo['remote'][$key])) {
                        continue;
                    }
                    $fileToUrl[$fontInfo['remote'][$key]] = $directory . rawurlencode($fontInfo['remote'][$key]);
                }
            }
            $localDir = $this->getUploadDirectory($fontFamily);
            foreach (['R', 'B', 'I', 'BI'] as $key) {
                if (!isset($fontInfo['remote'][$key])) {
                    continue;
                }

                $file = $fontInfo['remote'][$key];
                $local = trailingslashit($localDir) . $file;
                if (!isset($fileToUrl[$fontInfo['remote'][$key]])) {
                    $this->outputWarning("No URL for " . $fileToUrl[$fontInfo['remote'][$key]]);
                    return;
                }
                $url = $fileToUrl[$fontInfo['remote'][$key]];
                $response = wp_remote_get($url, array('timeout' => 15, 'redirection' => 3));
                if (is_wp_error($response)) {
                    $this->outputWarning("Could not retrieve $url");
                    return;
                }
                $code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                if ($code === 200 && is_string($body) && strlen($body) > 1000) {
                    $saved = file_put_contents($local, $body);
                    if (!$saved || $saved < 1000) {
                        $this->outputWarning("Could not retrieve $url");
                        return;
                    }
                }
            }
            $this->custom_fonts[$fontFamily]['actions'] = $this->calcActions($fontFamily);
            $this->outputSuccess("Font $fontFamily successfully uploaded");
        }
        private function outputWarning($str)
        {
            wp_redirect(admin_url('admin.php?page=bmlt-enabled-bread&fontAction=warning&message=' . rawurlencode($str) . '&nonce=' . wp_create_nonce('bread_font_action')));
            exit;
        }
        private function outputSuccess($str)
        {
            wp_redirect(admin_url('admin.php?page=bmlt-enabled-bread&fontAction=success&message=' . rawurlencode($str) . '&nonce=' . wp_create_nonce('bread_font_action')));
            exit;
        }
        public function removeFont($fontFamily)
        {
            if (!current_user_can('manage_options')) {
                $this->outputWarning("You must be an administrator to uninstall fonts");
                return;
            }
            $dirname = trailingslashit($this->getUploadDirectory()) . $fontFamily;
            array_map('unlink', glob("$dirname/*.*"));
            (new WP_Filesystem_Direct(null))->rmdir($dirname);
            $this->custom_fonts[$fontFamily]['actions'] = $this->calcActions($fontFamily);
            $this->outputSuccess("Font $fontFamily removed.");
        }
        public function bread_content_style($content_style)
        {
            foreach ($this->getUploadedFonts() as $font) {
                $fontInfo = $this->custom_fonts[$font];
                $dir = trailingslashit($this->getUploadDirectory($font, true));
                foreach (['R', 'B', 'I', 'BI'] as $key) {
                    if (!isset($fontInfo['remote'][$key])) {
                        continue;
                    }
                    $loc = $dir . rawurlencode($fontInfo['remote'][$key]);
                    $content_style .= "@font-face {";
                    $content_style .= "font-family: $font;";
                    $content_style .= "src:url($loc) format('truetype');";
                    $content_style .= "font-display: block;";
                    if ($key == 'B' || $key == 'BI') {
                        $content_style .= "font-weight: bold;";
                    }
                    if ($key == 'I' || $key == 'BI') {
                        $content_style .= "font-style: italic;";
                    }
                    $content_style .= "}";
                }
            }
            return $content_style;
        }
        function calcActions($font)
        {
            if (in_array($font, $this->getUploadedFonts())) {
                return [
                'removefont' => [
                'text' => __('remove', 'bread'),
                'action' => 'removefont',
                'lambda' => [$this, 'removeFont']
                ]];
            } else {
                return [
                'installfont' => [
                'text' => __('Install', 'bread'),
                'action' => 'installfont',
                'lambda' => [$this, 'installFont']
                ]];
            }
        }
        public function mpdf_init_options($options)
        {
            $fontDirs = $options['fontDir']??[];
            $fontdata = $options['fontdata']??[];
            foreach ($this->getUploadedFonts() as $fontKey) {
                $fontDirs[] = $this->getUploadDirectory($fontKey);
                $info = $this->custom_fonts[$fontKey];
                $fontdata[$fontKey] = [];
                foreach (['R', 'B', 'I', 'BI'] as $style) {
                    if (isset($info['remote'][$style])) {
                        $fontdata[$fontKey][$style] = $info['remote'][$style];
                    }
                }
                if (isset($info['configuration'])) {
                    foreach ($info['configuration'] as $key => $value) {
                        $fontdata[$fontKey][$key] = $value;
                    }
                }
            }
            $options['fontDir'] = $fontDirs;
            $options['fontdata'] = $fontdata;
            return $options;
        }
}
