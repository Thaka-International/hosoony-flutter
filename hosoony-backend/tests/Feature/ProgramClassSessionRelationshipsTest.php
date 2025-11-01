<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\ClassSchedule;
use App\Models\Program;
use App\Models\Session;
use App\Models\SessionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramClassSessionRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Program -> Classes relationship (hasMany).
     */
    public function testProgramHasManyClasses(): void
    {
        $program = Program::factory()->create();
        $class1 = ClassModel::factory()->create(['program_id' => $program->id]);
        $class2 = ClassModel::factory()->create(['program_id' => $program->id]);

        $this->assertCount(2, $program->classes);
        $this->assertTrue($program->classes->contains($class1));
        $this->assertTrue($program->classes->contains($class2));
    }

    /**
     * Test Class -> Program relationship (belongsTo).
     */
    public function testClassBelongsToProgram(): void
    {
        $program = Program::factory()->create();
        $class = ClassModel::factory()->create(['program_id' => $program->id]);

        $this->assertEquals($program->id, $class->program->id);
        $this->assertEquals($program->name, $class->program->name);
    }

    /**
     * Test Class -> Schedules relationship (hasMany).
     */
    public function testClassHasManySchedules(): void
    {
        $class = ClassModel::factory()->create();
        $schedule1 = ClassSchedule::factory()->create(['class_id' => $class->id]);
        $schedule2 = ClassSchedule::factory()->create(['class_id' => $class->id]);

        $this->assertCount(2, $class->schedules);
        $this->assertTrue($class->schedules->contains($schedule1));
        $this->assertTrue($class->schedules->contains($schedule2));
    }

    /**
     * Test ClassSchedule -> Class relationship (belongsTo).
     */
    public function testClassScheduleBelongsToClass(): void
    {
        $class = ClassModel::factory()->create();
        $schedule = ClassSchedule::factory()->create(['class_id' => $class->id]);

        $this->assertEquals($class->id, $schedule->class->id);
        $this->assertEquals($class->name, $schedule->class->name);
    }

    /**
     * Test Class -> Sessions relationship (hasMany).
     */
    public function testClassHasManySessions(): void
    {
        $class = ClassModel::factory()->create();
        $teacher = User::factory()->teacher()->create();

        $session1 = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);
        $session2 = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->assertCount(2, $class->sessions);
        $this->assertTrue($class->sessions->contains($session1));
        $this->assertTrue($class->sessions->contains($session2));
    }

    /**
     * Test Session -> Class relationship (belongsTo).
     */
    public function testSessionBelongsToClass(): void
    {
        $class = ClassModel::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $session = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->assertEquals($class->id, $session->class->id);
        $this->assertEquals($class->name, $session->class->name);
    }

    /**
     * Test Session -> Teacher relationship (belongsTo).
     */
    public function testSessionBelongsToTeacher(): void
    {
        $teacher = User::factory()->teacher()->create();
        $class = ClassModel::factory()->create();
        $session = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->assertEquals($teacher->id, $session->teacher->id);
        $this->assertEquals($teacher->name, $session->teacher->name);
    }

    /**
     * Test Session -> SessionItems relationship (hasMany).
     */
    public function testSessionHasManySessionItems(): void
    {
        $class = ClassModel::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $session = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);

        $item1 = SessionItem::factory()->create(['session_id' => $session->id]);
        $item2 = SessionItem::factory()->create(['session_id' => $session->id]);

        $this->assertCount(2, $session->items);
        $this->assertTrue($session->items->contains($item1));
        $this->assertTrue($session->items->contains($item2));
    }

    /**
     * Test SessionItem -> Session relationship (belongsTo).
     */
    public function testSessionItemBelongsToSession(): void
    {
        $class = ClassModel::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $session = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);
        $item = SessionItem::factory()->create(['session_id' => $session->id]);

        $this->assertEquals($session->id, $item->session->id);
        $this->assertEquals($session->title, $item->session->title);
    }

    /**
     * Test Program active classes relationship.
     */
    public function testProgramActiveClassesRelationship(): void
    {
        $program = Program::factory()->create();
        $activeClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'status' => 'active',
        ]);
        $inactiveClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'status' => 'inactive',
        ]);

        $activeClasses = $program->activeClasses;
        $this->assertCount(1, $activeClasses);
        $this->assertTrue($activeClasses->contains($activeClass));
        $this->assertFalse($activeClasses->contains($inactiveClass));
    }

    /**
     * Test Program male/female classes relationships.
     */
    public function testProgramGenderClassesRelationships(): void
    {
        $program = Program::factory()->create();
        $maleClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'gender' => 'male',
        ]);
        $femaleClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'gender' => 'female',
        ]);

        $maleClasses = $program->maleClasses;
        $femaleClasses = $program->femaleClasses;

        $this->assertCount(1, $maleClasses);
        $this->assertCount(1, $femaleClasses);
        $this->assertTrue($maleClasses->contains($maleClass));
        $this->assertTrue($femaleClasses->contains($femaleClass));
    }

    /**
     * Test Class active schedules relationship.
     */
    public function testClassActiveSchedulesRelationship(): void
    {
        $class = ClassModel::factory()->create();
        $activeSchedule = ClassSchedule::factory()->create([
            'class_id' => $class->id,
            'is_active' => true,
        ]);
        $inactiveSchedule = ClassSchedule::factory()->create([
            'class_id' => $class->id,
            'is_active' => false,
        ]);

        $activeSchedules = $class->activeSchedules;
        $this->assertCount(1, $activeSchedules);
        $this->assertTrue($activeSchedules->contains($activeSchedule));
        $this->assertFalse($activeSchedules->contains($inactiveSchedule));
    }

    /**
     * Test Class upcoming sessions relationship.
     */
    public function testClassUpcomingSessionsRelationship(): void
    {
        $class = ClassModel::factory()->create();
        $teacher = User::factory()->teacher()->create();

        $upcomingSession = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'starts_at' => now()->addDays(1),
        ]);
        $pastSession = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'starts_at' => now()->subDays(1),
        ]);

        $upcomingSessions = $class->upcomingSessions;
        $this->assertCount(1, $upcomingSessions);
        $this->assertTrue($upcomingSessions->contains($upcomingSession));
        $this->assertFalse($upcomingSessions->contains($pastSession));
    }

    /**
     * Test Session completed/pending items relationships.
     */
    public function testSessionItemsStatusRelationships(): void
    {
        $class = ClassModel::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $session = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);

        $completedItem = SessionItem::factory()->create([
            'session_id' => $session->id,
            'status' => 'completed',
        ]);
        $pendingItem = SessionItem::factory()->create([
            'session_id' => $session->id,
            'status' => 'pending',
        ]);

        $completedItems = $session->completedItems;
        $pendingItems = $session->pendingItems;

        $this->assertCount(1, $completedItems);
        $this->assertCount(1, $pendingItems);
        $this->assertTrue($completedItems->contains($completedItem));
        $this->assertTrue($pendingItems->contains($pendingItem));
    }

    /**
     * Test cascade delete relationships.
     */
    public function testCascadeDeleteRelationships(): void
    {
        $program = Program::factory()->create();
        $class = ClassModel::factory()->create(['program_id' => $program->id]);
        $schedule = ClassSchedule::factory()->create(['class_id' => $class->id]);
        $teacher = User::factory()->teacher()->create();
        $session = Session::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);
        $item = SessionItem::factory()->create(['session_id' => $session->id]);

        // Delete program should cascade to class, schedule, session, and item
        $program->delete();

        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
        $this->assertDatabaseMissing('class_schedules', ['id' => $schedule->id]);
        $this->assertDatabaseMissing('sessions', ['id' => $session->id]);
        $this->assertDatabaseMissing('session_items', ['id' => $item->id]);
    }
}


