<?php
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

final class BreadMeetinglistStructureTest extends TestCase
{
    private function getConfiguration(string $config): array
    {
        $json = file_get_contents('tests/configurations/'.$config.".json");
        return json_decode($json, true);
    }
    private function getMeetings(string $service_body): array
    {
        $json = file_get_contents('tests/serviceBodies/'.$service_body.".json");
        return json_decode($json, true);
    }
    private function getFormats($formats)
    {
        $json = file_get_contents('tests/formats/'.$formats.".json");
        return json_decode($json, true);
    }
    private function getFormatMgr($usedFormat, $lang)
    {
        return new Bread_FormatsManager($this->getFormats($usedFormat), $lang);
    }
    private function enhanceMeetings(&$meetings, $options, $formatMgr)
    {
        $enhancer = new Bread_Meeting_Enhancer($options, array());
        foreach ($meetings as &$meeting) {
            $meeting = $enhancer->enhance_meeting($meeting, 'de', $formatMgr);
        }
    }
    public function calculateExpectedHeadingStyle($options): string
    {
        $header_style = "color:" . $options['header_text_color'] . ";";
        $header_style .= "background-color:" . $options['header_background_color'] . ";";
        $header_style .= "font-size:" . $options['header_font_size'] . "pt;";
        $header_style .= "line-height:" . $options['content_line_height'] . ";";
        $header_style .= "text-align:center;padding-top:2px;padding-bottom:3px;";
        if ($options['header_uppercase'] == 1) {
            $header_style .= 'text-transform: uppercase;';
        }
        if ($options['header_bold'] == 0) {
            $header_style .= 'font-weight: normal;';
        }
        if ($options['header_bold'] == 1) {
            $header_style .= 'font-weight: bold;';
        }
        return $header_style;
    }
    public function testBerlinByDayMain()
    {
        $this->doTest('berlin-booklet', 'berlin', 'berlin-formats-de', 'german-formats', -1,
            ['Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag'],
            [[0],[0],[0],[0],[0],[0],[0]], 'de');
    }
    public function testBerlinByDayAdditional()
    {
        $this->doTest('berlin-booklet', 'berlin', 'berlin-formats-de', 'german-formats', 1,
            ['','','','','','',''],
            [[0],[0],[0],[0],[0],[0],[0]], 'de');
    }
    public function testBerlinByCityPlusDayMain()
    {
        $this->doTest('berlin-by-city-plus-day', 'berlin', 'berlin-formats-de', 'german-formats', -1,
            ['Berlin','Dallgow-Döberitz','Eberswalde','Potsdam','Rathenow'],
            [[0],[0],[0],[0],[0]], 'de');
    }
    public function testBerlinByCityPlusDayAdditional()
    {
        $this->doTest('berlin-booklet', 'berlin', 'berlin-formats-de', 'german-formats', 1,
            ['','','','','','',''],
            [[0],[0],[0],[0],[0],[0],[0]], 'de');
    }
    public function testBerlinByDayThenCityPlusDayAdditionalMain()
    {
        $this->doTest('berlin-by-day-then-city-plus-day', 'berlin', 'berlin-formats-de', 'german-formats', -1,
            ['Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag'],
            [['Berlin',],['Berlin','Potsdam','Rathenow'],['Berlin','Dallgow-Döberitz','Eberswalde'],['Berlin','Potsdam'],['Berlin',],['Berlin','Potsdam'],['Berlin','Potsdam']], 'de');
    }
    public function testBerlinByDayThenCityPlusDayAdditional()
    {
        $this->doTest('berlin-by-day-then-city-plus-day', 'berlin', 'berlin-formats-de', 'german-formats', 1,
            ['','','','','','',''],
            [[0],[0],[0],[0],[0],[0],[0]], 'de');
    }
    public function doTest($config, $meetingJson, $usedFormats, $formatBase, $include, $expectedHeading, $expectedSubHeading, $lang): void
    {
        new Bread();
        $options = $this->getConfiguration($config);
        $meetings = $this->getMeetings($meetingJson);
        $formatMgr = $this->getFormatMgr($usedFormats, $lang);
        Bread_Bmlt::setFormatBase($formatBase);
        $this->enhanceMeetings($meetings, $options,$formatMgr);

        $bms = new Bread_Meetingslist_Structure($options, $meetings, $lang, $include);
        $knt = 0;
        $expectedHeaderStyle = $this->calculateExpectedHeadingStyle($options);
        while ($subs = $bms->iterateMainHeading()) {
            assertEquals(count($expectedSubHeading[$knt]), count($subs));
            $knt++;
            $knt2 = 0;
            while ($meetings = $bms->iterateSubHeading($subs)) {
                $expected = '';
                if ($knt2++ == 0 && !empty($expectedHeading[$knt-1])) {
                    $expected = '<div style="' . $expectedHeaderStyle . '">' . $expectedHeading[$knt-1] . "</div>";
                }
                $knt3 = 0;
                while ($meeting = $bms->iterateMeetings($meetings)) {
                    $expectedSub = '';
                    if ($knt3++ == 0) {
                        if (!empty($subs[$knt2-1])) {
                            $expectedSub = "<p style='margin-top:1pt; padding-top:1pt; font-weight:bold;'>" . $subs[$knt2-1] . "</p>";
                        }
                    } else {
                        $expected = '';
                    }
                    assertEquals($expected.$expectedSub, $bms->calculateHeading());
                }
            }
        }
        assertEquals(count($expectedHeading), $knt);
    }
}
