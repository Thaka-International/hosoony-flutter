@extends('layouts.pwa')

@section('title', 'مهامي اليوم - حسوني')
@section('header-title', 'مهامي اليوم')
@section('header-subtitle', '{{ auth()->user()->name }}')

@section('content')
<div class="pwa-card">
    <h2>مهام اليوم - {{ now()->format('Y/m/d') }}</h2>
    
    @if($tasks->count() > 0)
        @foreach($tasks as $task)
            <div class="pwa-task {{ $task->status === 'verified' ? 'completed' : '' }}">
                <input type="checkbox" {{ $task->status === 'verified' ? 'checked' : '' }} disabled>
                <div style="flex: 1;">
                    <div class="pwa-task-text">{{ $task->name }}</div>
                    @if($task->description)
                        <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">{{ $task->description }}</div>
                    @endif
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem; font-size: 0.75rem;">
                        <span style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                            {{ $task->type === 'hifz' ? 'حفظ' : ($task->type === 'murajaah' ? 'مراجعة' : ($task->type === 'tilawah' ? 'تلاوة' : ($task->type === 'tajweed' ? 'تجويد' : ($task->type === 'tafseer' ? 'تفسير' : 'أخرى')))) }}
                        </span>
                        <span style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                            {{ $task->task_location === 'in_class' ? 'أثناء الحلقة' : 'واجب منزلي' }}
                        </span>
                        <span style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                            {{ $task->points_weight }} نقاط
                        </span>
                        <span style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                            {{ $task->duration_minutes }} دقيقة
                        </span>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem;">
                    @if($task->status === 'verified')
                        <span style="color: #059669; font-size: 0.875rem;">✓ مكتملة</span>
                        @if($task->verified_at)
                            <span style="color: #6b7280; font-size: 0.75rem;">{{ $task->verified_at->format('H:i') }}</span>
                        @endif
                    @else
                        <span style="color: #d97706; font-size: 0.875rem;">⏳ معلقة</span>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
            <p>لا توجد مهام لهذا اليوم</p>
            <p style="font-size: 0.875rem;">ستظهر المهام هنا عندما يضيفها المعلم</p>
        </div>
    @endif
</div>

<!-- Task Statistics -->
<div class="pwa-card">
    <h2>إحصائيات المهام</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">{{ $tasks->where('status', 'verified')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">مكتملة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #d97706;">{{ $tasks->where('status', 'pending')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">معلقة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;">{{ $tasks->where('status', 'rejected')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">مرفوضة</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('student.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
        <a href="{{ route('student.schedule') }}" class="pwa-btn pwa-btn-secondary">جدول الجلسات</a>
    </div>
</div>
@endsection


