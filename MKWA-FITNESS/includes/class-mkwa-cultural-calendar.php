<?php
/**
 * Cultural Calendar Integration
 *
 * @package MkwaFitness
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Cultural_Calendar {
    /**
     * Seasonal cycles with activities and events
     */
    private $seasonal_cycles = [
        'ziigwan' => [
            'name' => 'Spring',
            'duration' => [3, 4, 5],  // March, April, May
            'activities' => [
                'morning_ceremony' => [
                    'points' => 25,
                    'cultural_weight' => 2.0,
                    'description' => 'Traditional morning ceremony and exercise',
                    'requirements' => ['time_of_day' => 'morning']
                ],
                'nature_walk' => [
                    'points' => 15,
                    'cultural_weight' => 1.5,
                    'description' => 'Traditional nature walk and observation',
                    'requirements' => ['duration' => 30] // minutes
                ],
                'traditional_planting' => [
                    'points' => 30,
                    'cultural_weight' => 2.5,
                    'description' => 'Learning and practicing traditional planting',
                    'requirements' => ['location' => 'outdoor']
                ]
            ],
            'special_events' => [
                'sugar_bush' => [
                    'duration' => '2_weeks',
                    'bonus_multiplier' => 2.5,
                    'description' => 'Traditional maple syrup harvesting activities'
                ],
                'spring_feast' => [
                    'duration' => '1_day',
                    'bonus_multiplier' => 3.0,
                    'description' => 'Spring celebration and feast activities'
                ]
            ]
        ],
        'niibin' => [
            'name' => 'Summer',
            'duration' => [6, 7, 8],  // June, July, August
            'activities' => [
                'water_ceremony' => [
                    'points' => 30,
                    'cultural_weight' => 2.0,
                    'description' => 'Traditional water ceremony and activities',
                    'requirements' => ['location' => 'water']
                ],
                'canoe_training' => [
                    'points' => 25,
                    'cultural_weight' => 1.8,
                    'description' => 'Traditional canoe training and exercise',
                    'requirements' => ['location' => 'water', 'duration' => 45]
                ],
                'medicine_gathering' => [
                    'points' => 20,
                    'cultural_weight' => 2.2,
                    'description' => 'Learning and gathering traditional medicines',
                    'requirements' => ['location' => 'outdoor']
                ]
            ],
            'special_events' => [
                'summer_games' => [
                    'duration' => '3_days',
                    'bonus_multiplier' => 2.0,
                    'description' => 'Traditional summer games and activities'
                ]
            ]
        ]
        // Additional seasons will be added similarly
    ];

    /**
     * Get current season information
     *
     * @return array Season data including activities and events
     */
    public function get_current_season() {
        $current_month = (int)date('n');
        
        foreach ($this->seasonal_cycles as $season => $data) {
            if (in_array($current_month, $data['duration'])) {
                return [
                    'season' => $season,
                    'name' => $data['name'],
                    'activities' => $this->get_seasonal_activities($season),
                    'events' => $this->get_upcoming_events($season),
                    'multipliers' => $this->calculate_season_multipliers($season)
                ];
            }
        }

        // Default to spring if no season match found
        return $this->get_season_data('ziigwan');
    }

    /**
     * Get seasonal activities
     *
     * @param string $season Season identifier
     * @return array Available activities for the season
     */
    public function get_seasonal_activities($season) {
        if (!isset($this->seasonal_cycles[$season])) {
            return [];
        }

        return $this->seasonal_cycles[$season]['activities'];
    }

    /**
     * Get upcoming events for a season
     *
     * @param string $season Season identifier
     * @return array Upcoming events
     */
    public function get_upcoming_events($season) {
        if (!isset($this->seasonal_cycles[$season])) {
            return [];
        }

        $events = $this->seasonal_cycles[$season]['special_events'];
        $upcoming = [];

        foreach ($events as $event_id => $event_data) {
            // Calculate event dates based on season
            $event_dates = $this->calculate_event_dates($season, $event_id, $event_data);
            if ($event_dates) {
                $upcoming[$event_id] = array_merge($event_data, $event_dates);
            }
        }

        return $upcoming;
    }

    /**
     * Calculate season multipliers
     *
     * @param string $season Season identifier
     * @return array Multipliers for various activities
     */
    private function calculate_season_multipliers($season) {
        $base_multiplier = 1.0;
        $current_season = $this->seasonal_cycles[$season] ?? null;

        if (!$current_season) {
            return ['base' => $base_multiplier];
        }

        // Calculate multipliers based on season-specific factors
        return [
            'base' => $base_multiplier,
            'cultural_activities' => $base_multiplier * 1.5,
            'special_events' => $base_multiplier * 2.0,
            'season_specific' => $base_multiplier * 1.8
        ];
    }

    /**
     * Calculate event dates
     *
     * @param string $season Season identifier
     * @param string $event_id Event identifier
     * @param array $event_data Event configuration data
     * @return array|null Event dates or null if not applicable
     */
    private function calculate_event_dates($season, $event_id, $event_data) {
        $season_data = $this->seasonal_cycles[$season];
        if (!$season_data) {
            return null;
        }

        // Get the first month of the season
        $season_start_month = min($season_data['duration']);
        
        // Calculate event start date (example logic - can be customized)
        $event_start = date('Y-m-d', strtotime(date('Y') . "-{$season_start_month}-01"));
        
        // Parse duration
        $duration_parts = explode('_', $event_data['duration']);
        $duration_value = (int)$duration_parts[0];
        $duration_unit = $duration_parts[1];

        // Calculate end date based on duration
        $end_date = strtotime($event_start);
        switch ($duration_unit) {
            case 'weeks':
                $end_date = strtotime("+{$duration_value} weeks", $end_date);
                break;
            case 'days':
                $end_date = strtotime("+{$duration_value} days", $end_date);
                break;
            default:
                $end_date = strtotime('+1 day', $end_date);
        }

        return [
            'start_date' => $event_start,
            'end_date' => date('Y-m-d', $end_date)
        ];
    }

    /**
     * Get specific season data
     *
     * @param string $season Season identifier
     * @return array Season data
     */
    public function get_season_data($season) {
        if (!isset($this->seasonal_cycles[$season])) {
            return null;
        }

        return [
            'season' => $season,
            'name' => $this->seasonal_cycles[$season]['name'],
            'activities' => $this->get_seasonal_activities($season),
            'events' => $this->get_upcoming_events($season),
            'multipliers' => $this->calculate_season_multipliers($season)
        ];
    }

    /**
     * Check if an activity is available for the current season
     *
     * @param string $activity_id Activity identifier
     * @return bool|array False if not available, activity data if available
     */
    public function is_activity_available($activity_id) {
        $current_season = $this->get_current_season();
        
        if (isset($current_season['activities'][$activity_id])) {
            return $current_season['activities'][$activity_id];
        }

        return false;
    }

    /**
     * Calculate points for an activity considering seasonal multipliers
     *
     * @param string $activity_id Activity identifier
     * @param array $completion_data Activity completion data
     * @return int Calculated points
     */
    public function calculate_activity_points($activity_id, $completion_data = []) {
        $activity = $this->is_activity_available($activity_id);
        if (!$activity) {
            return 0;
        }

        $points = $activity['points'];
        $multipliers = $this->get_current_season()['multipliers'];

        // Apply cultural weight
        $points *= $activity['cultural_weight'];

        // Apply seasonal multipliers if applicable
        if (isset($completion_data['special_event'])) {
            $points *= $multipliers['special_events'];
        } else {
            $points *= $multipliers['base'];
        }

        return (int)round($points);
    }
}