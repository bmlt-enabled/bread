<?php
use PHPUnit\Framework\TestCase;

final class BreadMeetinglistStructureTest extends TestCase
{
    private function getConfiguration(string $config): array
    {
        phpinfo();
        $json = file_get_contents('tests/configurations/'.$config.".json");
        return json_decode($json, true);
    }
    private function getMeetings(string $service_body): array
    {
        $json = file_get_contents('tests/serviceBodies/'.$service_body.".json");
        return json_decode($json, true);
    }
    public function testSomething(): void
    {
        echo "hi";
        $options = $this->getConfiguration('berlin-booklet');
        $meetings = $this->getMeetings('berlin');
        $bms = new Bread_Meetingslist_Structure($options, $meetings, 'de', -1);
        while ($subs = $bms->iterateMainHeading()) {
            echo $subs;
            while ($meetings = $bms->iterateSubHeading($subs)) {
                echo count($meetings);
            }
        }
    }
}
