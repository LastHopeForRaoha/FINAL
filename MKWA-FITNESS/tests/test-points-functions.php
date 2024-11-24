<?php

use PHPUnit\Framework\TestCase;

class PointsFunctionsTest extends TestCase {
    protected function setUp(): void {
        // Initialize necessary components
    }

    public function testAddPoints() {
        $member_id = 1;
        $points = 100;
        $activity_type = 'exercise';
        
        $result = mkwa_add_points($member_id, $points, $activity_type);
        $this->assertTrue($result);
    }

    public function testSubtractPoints() {
        $member_id = 1;
        $points = 50;
        
        $result = mkwa_subtract_points($member_id, $points);
        $this->assertTrue($result);
    }

    public function testGetMemberPoints() {
        $member_id = 1;
        
        $points = mkwa_get_member_points($member_id);
        $this->assertIsInt($points);
    }
}