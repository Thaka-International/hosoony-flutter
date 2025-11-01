<?php

namespace Database\Seeders;

use App\Models\WeekdaySchedule;
use Illuminate\Database\Seeder;

class WeekdayScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default schedule for female circles (Sunday to Thursday, 7 AM - 8 AM)
        WeekdaySchedule::create([
            'name' => 'جدول الحلقات النسائية الافتراضي',
            'description' => 'جدول افتراضي للحلقات النسائية من الأحد إلى الخميس من 7 صباحاً إلى 8 صباحاً',
            'schedule' => [
                'sunday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => true,
                ],
                'monday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => true,
                ],
                'tuesday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => true,
                ],
                'wednesday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => true,
                ],
                'thursday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => true,
                ],
                'friday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => false,
                ],
                'saturday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => false,
                ],
            ],
            'is_active' => true,
            'is_default' => true,
        ]);

        // Alternative schedule for male circles (Sunday to Thursday, 8 AM - 9 AM)
        WeekdaySchedule::create([
            'name' => 'جدول الحلقات الذكورية',
            'description' => 'جدول للحلقات الذكورية من الأحد إلى الخميس من 8 صباحاً إلى 9 صباحاً',
            'schedule' => [
                'sunday' => [
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'is_active' => true,
                ],
                'monday' => [
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'is_active' => true,
                ],
                'tuesday' => [
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'is_active' => true,
                ],
                'wednesday' => [
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'is_active' => true,
                ],
                'thursday' => [
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'is_active' => true,
                ],
                'friday' => [
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'is_active' => false,
                ],
                'saturday' => [
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'is_active' => false,
                ],
            ],
            'is_active' => true,
            'is_default' => false,
        ]);

        // Weekend schedule (Friday and Saturday)
        WeekdaySchedule::create([
            'name' => 'جدول نهاية الأسبوع',
            'description' => 'جدول للحلقات في نهاية الأسبوع (الجمعة والسبت)',
            'schedule' => [
                'sunday' => [
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'is_active' => false,
                ],
                'monday' => [
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'is_active' => false,
                ],
                'tuesday' => [
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'is_active' => false,
                ],
                'wednesday' => [
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'is_active' => false,
                ],
                'thursday' => [
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'is_active' => false,
                ],
                'friday' => [
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'is_active' => true,
                ],
                'saturday' => [
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'is_active' => true,
                ],
            ],
            'is_active' => true,
            'is_default' => false,
        ]);

        // Evening schedule (Sunday to Thursday, 6 PM - 7 PM)
        WeekdaySchedule::create([
            'name' => 'جدول المساء',
            'description' => 'جدول للحلقات المسائية من الأحد إلى الخميس من 6 مساءً إلى 7 مساءً',
            'schedule' => [
                'sunday' => [
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'is_active' => true,
                ],
                'monday' => [
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'is_active' => true,
                ],
                'tuesday' => [
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'is_active' => true,
                ],
                'wednesday' => [
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'is_active' => true,
                ],
                'thursday' => [
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'is_active' => true,
                ],
                'friday' => [
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'is_active' => false,
                ],
                'saturday' => [
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'is_active' => false,
                ],
            ],
            'is_active' => true,
            'is_default' => false,
        ]);
    }
}