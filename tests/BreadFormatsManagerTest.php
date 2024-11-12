<?php
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

final class BreadFormatsManagerTest extends TestCase
{
    private function getFormats($formats): array
    {
        $json = file_get_contents('tests/formats/'.$formats.".json");
        return json_decode($json, true)['formats'];
    }
    private function getFormatMgr($usedFormat, $lang)
    {
        Bread_Bmlt::setFormatBase('german-formats');
        return new Bread_FormatsManager($this->getFormats($usedFormat), $lang);
    }
    public function testGetFormatsUsed()
    {
        $mgr = $this->getFormatMgr('berlin-formats-de', 'de');
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
