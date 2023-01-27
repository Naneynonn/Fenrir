<?php

namespace Tests\Exan\Dhp\Rest\Helpers\Channel\Channel\Shared;

use Exan\Dhp\Rest\Helpers\Channel\Channel\Shared\SetRtcRegion;
use PHPUnit\Framework\TestCase;

class SetRtcRegionTest extends TestCase
{
    public function testSetRtcRegion()
    {
        $class = new class extends DummyTraitTester {
            use SetRtcRegion;
        };

        $class->setRtcRegion('::rtc region::');

        $this->assertEquals(['rtc_region' => '::rtc region::'], $class->get());
    }
}