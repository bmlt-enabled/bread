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
    private function enhanceMeetings(&$meetings, $options)
    {
        $enhancer = new Bread_Meeting_Enhancer($options, array());
        foreach ($meetings as &$meeting) {
            $meeting = $enhancer->enhance_meeting($meeting, 'de', null);
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
        $this->doTest('berlin-booklet', 'berlin', -1,
            ['Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag'],
            [[0],[0],[0],[0],[0],[0],[0]], 'de');
    }
    public function testBerlinByDayAdditional()
    {
        $this->doTest('berlin-booklet', 'berlin', 1,
            ['','','','','','',''],
            [[0],[0],[0],[0],[0],[0],[0]], 'de');
    }
    public function testBerlinByCityPlusDayMain()
    {
        $this->doTest('berlin-by-city-plus-day', 'berlin', -1,
            ['Berlin','Dallgow-Döberitz','Eberswalde','Potsdam','Rathenow'],
            [[0],[0],[0],[0],[0]], 'de');
    }
    public function testBerlinByCityPlusDayAdditional()
    {
        $this->doTest('berlin-booklet', 'berlin', 1,
            ['','','','','','',''],
            [[0],[0],[0],[0],[0],[0],[0]], 'de');
    }
    public function testBerlinByDayThenCityPlusDayMain()
    {
        $this->doTest('berlin-by-city-plus-day', 'berlin', -1,
            ['Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag'],
            [['Berlin',],['Berlin','Potsdam','Rathenow'],['Berlin',],['Berlin',],['Berlin',],['Berlin',],['Berlin',]], 'de');
    }
    public function testBerlinByDayThenCityPlusDayAdditional()
    {
        $this->doTest('berlin-booklet', 'berlin', 1,
            ['','','','','','',''],
            [[0],[0],[0],[0],[0],[0],[0]], 'de');
    }
    public function doTest($config, $meetingJson, $include, $expectedHeading, $expectedSubHeading, $lang): void
    {
        new Bread();
        $options = $this->getConfiguration($config);
        $meetings = $this->getMeetings($meetingJson);
        $this->enhanceMeetings($meetings, $options);

        $bms = new Bread_Meetingslist_Structure($options, $meetings, $lang, $include);
        $knt = 0;
        $expectedHeaderStyle = $this->calculateExpectedHeadingStyle($options);
        while ($subs = $bms->iterateMainHeading()) {
            assertEquals(count($expectedSubHeading[$knt]), count($subs));
            $knt++;
            while ($meetings = $bms->iterateSubHeading($subs)) {
                $knt3 = 0;
                while ($meeting = $bms->iterateMeetings($meetings)) {
                    if ($knt3++ == 0 && !empty($expectedHeading[$knt-1])) {
                        $expected = "<div style='" . $expectedHeaderStyle . "'>" . $expectedHeading[$knt-1] . "</div>";
                        assertEquals($expected, $bms->calculateHeading());
                    } else {
                        assertEquals('', $bms->calculateHeading());
                    }
                }
            }
        }
        assertEquals(count($expectedHeading), $knt);
    }
}
