<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\CompanionsPublication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanionsPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_class_with_zoom_fields()
    {
        $program = \App\Models\Program::create([
            'name' => 'برنامج تجريبي',
            'description' => 'وصف البرنامج',
            'status' => 'active',
        ]);

        $class = ClassModel::create([
            'name' => 'أ-1',
            'description' => 'الحلقة النسائية رقم 1',
            'program_id' => $program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
            'zoom_url' => 'https://zoom.us/j/123456789',
            'zoom_password' => 'password123',
            'zoom_room_start' => 1,
        ]);

        $this->assertEquals('https://zoom.us/j/123456789', $class->zoom_url);
        $this->assertEquals('password123', $class->zoom_password);
        $this->assertEquals(1, $class->zoom_room_start);
    }

    public function test_companions_publication_unique_constraint_works()
    {
        $program = \App\Models\Program::create([
            'name' => 'برنامج تجريبي',
            'description' => 'وصف البرنامج',
            'status' => 'active',
        ]);

        $class = ClassModel::create([
            'name' => 'أ-1',
            'description' => 'الحلقة النسائية رقم 1',
            'program_id' => $program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
        ]);
        
        CompanionsPublication::create([
            'class_id' => $class->id,
            'target_date' => '2025-10-07',
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        CompanionsPublication::create([
            'class_id' => $class->id,
            'target_date' => '2025-10-07',
            'grouping' => 'triplets',
            'algorithm' => 'rotation',
            'attendance_source' => 'committed_only',
        ]);
    }
}