<?php

namespace Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class HijriHelperTest extends TestCase
{
    /**
     * Test that hijri_display function exists and works.
     */
    public function testHijriDisplayFunctionExists(): void
    {
        $this->assertTrue(function_exists('hijri_display'));
    }

    /**
     * Test hijri_display function returns a string.
     */
    public function testHijriDisplayReturnsString(): void
    {
        $date = Carbon::now();
        $result = hijri_display($date);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test hijri_display function format.
     */
    public function testHijriDisplayFormat(): void
    {
        $date = Carbon::create(2024, 1, 1);
        $result = hijri_display($date);

        // Should contain Arabic text and end with هـ
        $this->assertStringContainsString('هـ', $result);
        $this->assertMatchesRegularExpression('/\d+.*\d+ هـ/', $result);
    }

    /**
     * Test hijri_display with different dates.
     */
    public function testHijriDisplayWithDifferentDates(): void
    {
        $dates = [
            Carbon::create(2024, 1, 1),
            Carbon::create(2024, 6, 15),
            Carbon::create(2024, 12, 31),
        ];

        foreach ($dates as $date) {
            $result = hijri_display($date);
            $this->assertIsString($result);
            $this->assertNotEmpty($result);
            $this->assertStringContainsString('هـ', $result);
        }
    }

    /**
     * Test that hijri_display handles Carbon instances properly.
     */
    public function testHijriDisplayWithCarbonInstance(): void
    {
        $date = Carbon::parse('2024-03-21 12:00:00');
        $result = hijri_display($date);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}


