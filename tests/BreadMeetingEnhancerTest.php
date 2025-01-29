<?php

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

final class BreadMeetingEnhancerTest extends TestCase
{
    private function getFormats($formats): array
    {
        $json = (new WP_Filesystem_Direct(null))->get_contents('tests/formats/' . $formats . ".json");
        return json_decode($json, true)['formats'];
    }
    private function getFormatMgr($usedFormat, $lang, $bmlt)
    {
        return new Bread_FormatsManager($this->getFormats($usedFormat), $lang, $bmlt);
    }
    public function testMeetingEnhancer()
    {
        $bread = new Bread([]);
        $bread->bmlt()->setFormatBase('german-formats');
        $mgr = $this->getFormatMgr('berlin-formats-de', 'de', $bread->bmlt());
        $used = $mgr->getFormatsUsed();
        assertEquals(50, count($used));
        $o1 = $mgr->getFormatByKey('de', 'O1');
        assertNotNull($o1);
        $o1e = $mgr->getFormatByKey('en', 'O1');
        assertNotNull($o1e);
        $used = $mgr->getFormatsUsed('en');
        assertEquals(50, count($used));
    }
}
