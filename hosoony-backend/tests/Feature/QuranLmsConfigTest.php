<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuranLmsConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Quran LMS configuration loads correctly.
     */
    public function testQuranLmsConfigLoads(): void
    {
        $config = config('quran_lms');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('tasks', $config);
        $this->assertArrayHasKey('bonus', $config);
        $this->assertArrayHasKey('streak', $config);
        $this->assertArrayHasKey('reminders', $config);
        $this->assertArrayHasKey('daily_logs', $config);
        $this->assertArrayHasKey('ui', $config);
    }

    /**
     * Test task weights configuration.
     */
    public function testTaskWeightsConfiguration(): void
    {
        $weights = config('quran_lms.tasks.weights');

        $this->assertIsArray($weights);
        $this->assertEquals(20, $weights['hifz']);
        $this->assertEquals(20, $weights['murajaah']);
        $this->assertEquals(15, $weights['tilawah']);
        $this->assertEquals(15, $weights['tajweed']);
        $this->assertEquals(10, $weights['tafseer']);
    }

    /**
     * Test bonus ranks configuration.
     */
    public function testBonusRanksConfiguration(): void
    {
        $ranks = config('quran_lms.bonus.ranks');

        $this->assertIsArray($ranks);
        $this->assertEquals(10, $ranks[1]);
        $this->assertEquals(6, $ranks[2]);
        $this->assertEquals(3, $ranks[3]);
    }

    /**
     * Test streak milestones configuration.
     */
    public function testStreakMilestonesConfiguration(): void
    {
        $milestones = config('quran_lms.streak.milestones');

        $this->assertIsArray($milestones);
        $this->assertEquals([5, 10, 20], $milestones);
    }

    /**
     * Test reminders configuration.
     */
    public function testRemindersConfiguration(): void
    {
        $reminders = config('quran_lms.reminders.times');

        $this->assertIsArray($reminders);
        $this->assertEquals(15, $reminders['session_t_minus_minutes']);
        $this->assertEquals('19:30', $reminders['daily_tasks_at']);
        $this->assertEquals('Sun', $reminders['weekly_at']['weekday']);
        $this->assertEquals('08:00', $reminders['weekly_at']['time']);
        $this->assertEquals([7, 3, 1], $reminders['payments_days_offsets']);
    }

    /**
     * Test daily logs verification policy.
     */
    public function testDailyLogsVerificationPolicy(): void
    {
        $policy = config('quran_lms.daily_logs.verification_policy');

        $this->assertEquals('manual', $policy);
        $this->assertContains($policy, ['manual', 'auto']);
    }

    /**
     * Test UI configuration.
     */
    public function testUiConfiguration(): void
    {
        $ui = config('quran_lms.ui');

        $this->assertIsArray($ui);
        $this->assertTrue($ui['rtl']);
        $this->assertIsArray($ui['fonts']['pdf']);
        $this->assertContains('Tajawal', $ui['fonts']['pdf']);
        $this->assertContains('Cairo', $ui['fonts']['pdf']);
    }
}
