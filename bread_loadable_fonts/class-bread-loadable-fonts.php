<?php

use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\TraitNameSuffixSniff;

use function PHPUnit\Framework\throwException;

class BreadLoadableFonts
{

        var $custom_fonts = [
            'roboto' => ['name' => 'Roboto',
                                      'stack' => 'Helvetica, sans-serif',
                                      'scripts' => ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext', 'greek', 'greek-ext'],
                                      'description' => 'Roboto has a dual nature. It has a mechanical skeleton and the forms are largely geometric. At the same time, the font features friendly and open curves. While some grotesks distort their letterforms to force a rigid rhythm, Roboto doesn’t compromise, allowing letters to be settled into their natural width. This makes for a more natural reading rhythm more commonly found in humanist and serif types.',
                                      'remote' => [
                                        'directory' => 'https://github.com/google/fonts/raw/refs/heads/main/ofl/roboto/',
                                        'R' => 'Roboto[wdth,wght].ttf',
                                        'I' => 'Roboto-Italic[wdth,wght].ttf',
                                        'type' => 'variable',
                                      ],
                                      'configuration' => [
                                        'useOTL' => '0xFF',
                                        'kashida' => 75,
                                      ]],
            'rubik' => ['name' => 'Rubik',
                                      'stack' => 'Helvetica, sans-serif',
                                      'scripts' => ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext', 'arabic'],
                                      'remote' => [
                                        'directory' => 'https://github.com/google/fonts/raw/refs/heads/main/ofl/rubik/',
                                        'R' => 'Rubik[wght].ttf',
                                        'I' => 'Rubik-Italic[wght].ttf',
                                        'type' => 'variable',
                                      ],
                                      'configuration' => [
                                        'useOTL' => '0xFF',
                                        'kashida' => 75,
                                      ]],
        ];
        public function __construct()
        {
            foreach ($this->custom_fonts as $key => &$font) {
                $font['actions'] = $this->calcActions($key);
            }
            $this->addAdminHooks();
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
        public function bread_active_fonts($fonts)
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
                throw new Exception("$fontFamily not in list of defined fonts");
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
        public function installFont($fontFamily)
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
            $directory = $fontInfo['remote']['directory'];
            $localDir = $this->getUploadDirectory($fontFamily);
            foreach (['R', 'B', 'I', 'BI'] as $key) {
                if (!isset($fontInfo['remote'][$key])) {
                    continue;
                }

                $file = $fontInfo['remote'][$key];
                $local = trailingslashit($localDir) . $file;
                $url = $directory . rawurlencode($file);
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
            $this->outputSuccess("Font $fontFamily successfully uploaded");
        }
        private function outputWarning($str)
        {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p style="color: #F00;">'.esc_html($str).'</p>';
            echo '</div>';
        }
        private function outputSuccess($str)
        {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p style="color: #000;">'.esc_html($str).'</p>';
            echo '</div>';
        }
        public function removeFont($fontFamily)
        {
            if (!current_user_can('manage_options')) {
                $this->outputWarning("You must be an administrator to uninstall fonts");
                return;
            }
            $dirname = trailingslashit($this->getUploadDirectory()) . $fontFamily;
            array_map('unlink', glob("$dirname/*.*"));
            rmdir($dirname);
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
                    if (isset($fontInfo['remote']['variable']) && $fontInfo['remote']['variable']) {
                        $content_style .= "font-weight: 100 1000;";
                    } elseif ($key == 'B' || $key == 'BI') {
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
    public function mpdf_init_options( $options ) {
		$fontDirs = $options['fontDir']??[];
		$fontdata = $options['fontdata']??[];
        foreach($this->getUploadedFonts() as $fontKey) {
            $fontDirs[] = $this->getUploadDirectory($fontKey);
            $info = $this->custom_fonts[$fontKey];
            $fontdata[$fontKey] = [];
            foreach(['R', 'B', 'I', 'BI'] as $style) {
                if (isset($info['remote'][$style])) {
                    $fontdata[$fontKey][$style] = $info['remote'][$style];
                } elseif ($style === 'B') {
                    $fontdata[$fontKey][$style] = $info['remote']['R'];
                } elseif ($style === 'BI') {
                    $fontdata[$fontKey][$style] = $info['remote']['I'];
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
    }
}