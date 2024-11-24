<?php

use PHPUnit\Framework\TestCase;

class BadgeSystemTest extends TestCase {
    protected function setUp(): void {
        global $badges;
        $badges = array(
            'bronze' => 100,
            'silver' => 500,
            'gold' => 1000,
        );
    }

    public function testAwardBadge() {
        $member_id = 1;
        $points = 600;
        
        mkwa_award_badge($member_id, $points);
        $badge = get_user_meta($member_id, 'mkwa_badge', true);
        $this->assertEquals('silver', $badge);
    }
}