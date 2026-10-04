<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\VelocityService;
use App\Services\ActivityLogger;

class DualAudienceTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('solo');
    }

    public function testSoloHelperFunctionsExist()
    {
        $this->assertTrue(function_exists('is_solo_mode'));
        $this->assertTrue(function_exists('solo_label'));
    }

    public function testSoloLabelReturnsCorrectString()
    {
        // When solo mode is forced / mock
        $result = solo_label('Team Velocity', 'My Velocity');
        $this->assertIsString($result);
        $this->assertTrue(in_array($result, ['Team Velocity', 'My Velocity']));
    }

    public function testVelocityServiceCalculatesSafeFallbacks()
    {
        $teamVelocity = VelocityService::calculateTeamVelocity(999999);
        $this->assertIsFloat($teamVelocity);
        $this->assertEquals(0.0, $teamVelocity);

        $personalVelocity = VelocityService::calculatePersonalVelocity(999999, 999999);
        $this->assertIsFloat($personalVelocity);
        $this->assertEquals(0.0, $personalVelocity);
    }

    public function testActivityLoggerReturnsGracefully()
    {
        $result = ActivityLogger::log(1, 'test_action', 'Test details', 1);
        $this->assertIsBool($result);

        $activities = ActivityLogger::getForTask(1);
        $this->assertIsArray($activities);
    }
}
