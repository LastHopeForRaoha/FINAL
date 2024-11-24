<?php
// badge-system.php

// Define badges and criteria
$badges = array(
    'bronze' => 100,
    'silver' => 500,
    'gold' => 1000,
);

function mkwa_award_badge($member_id, $points) {
    global $badges;

    foreach ($badges as $badge => $threshold) {
        if ($points >= $threshold) {
            // Award badge
            update_user_meta($member_id, 'mkwa_badge', $badge);
        }
    }
}