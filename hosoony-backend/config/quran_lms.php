<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Quran LMS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the central configuration for the Quran LMS system.
    | All task weights, bonus ranks, streaks, reminders, and UI settings
    | are defined here for easy management and modification.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Task Weights
    |--------------------------------------------------------------------------
    |
    | Defines the point weights for different types of Quranic activities.
    | These weights are used to calculate user scores and rankings.
    |
    */
    'tasks' => [
        'weights' => [
            'hifz' => 20,      // Memorization
            'murajaah' => 20,   // Review
            'tilawah' => 15,   // Recitation
            'tajweed' => 15,   // Proper pronunciation
            'tafseer' => 10,   // Interpretation
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bonus Ranks
    |--------------------------------------------------------------------------
    |
    | Defines bonus points awarded for achieving different ranks.
    | Rank 1 gets 10 points, Rank 2 gets 6 points, etc.
    |
    */
    'bonus' => [
        'ranks' => [
            1 => 10,
            2 => 6,
            3 => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Streak Milestones
    |--------------------------------------------------------------------------
    |
    | Defines milestone days for consecutive activity streaks.
    | Users receive special recognition at these intervals.
    |
    */
    'streak' => [
        'milestones' => [5, 10, 20],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reminder Settings
    |--------------------------------------------------------------------------
    |
    | Configures various reminder timings for the system.
    | Includes session reminders, daily tasks, weekly reports, and payment alerts.
    |
    */
    'reminders' => [
        'times' => [
            'session_t_minus_minutes' => 15,
            'daily_tasks_at' => '19:30',
            'weekly_at' => [
                'weekday' => 'Sun',
                'time' => '08:00',
            ],
            'payments_days_offsets' => [7, 3, 1],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Daily Logs Verification Policy
    |--------------------------------------------------------------------------
    |
    | Defines how daily activity logs are verified.
    | "manual" requires teacher/admin approval
    | "auto" allows automatic verification
    |
    */
    'daily_logs' => [
        'verification_policy' => 'manual', // "manual" | "auto"
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    |
    | User interface configuration including RTL support and font settings.
    |
    */
    'ui' => [
        'rtl' => true,
        'fonts' => [
            'pdf' => ['Tajawal', 'Cairo'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Companions System
    |--------------------------------------------------------------------------
    |
    | Configuration for the companions pairing system including attendance
    | requirements and default publication settings.
    |
    */
    'companions' => [
        'attendance_window_days' => 14,
        'min_rate' => 0.6,
        'default_publish_time' => '23:59',
        'default_attendance_source' => 'committed_only', // 'all' or 'committed_only'
    ],
];
