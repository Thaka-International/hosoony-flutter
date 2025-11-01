@extends('layouts.pwa')

@section('title', 'لوحة الطالب - حسوني')
@section('header-title', 'لوحة الطالب')
@section('header-subtitle', 'مرحباً بك، {{ auth()->user()->name }}')

@section('content')
<!-- Quick Stats -->
<div class="pwa-card">
    <h2>إحصائيات اليوم</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #059669;">{{ $todayTasks->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">المهام المكتملة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #eff6ff; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #1e40af;">{{ $totalPoints }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">النقاط الإجمالية</div>
        </div>
    </div>
</div>

<!-- Today's Tasks -->
<div class="pwa-card">
    <h2>مهام اليوم</h2>
    @if($todayTasks->count() > 0)
        @foreach($todayTasks as $task)
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
                            {{ $task->points_weight }} نقاط
                        </span>
                    </div>
                </div>
                @if($task->status === 'verified')
                    <span style="color: #059669; font-size: 0.875rem;">✓ مكتملة</span>
                @else
                    <span style="color: #d97706; font-size: 0.875rem;">⏳ معلقة</span>
                @endif
            </div>
        @endforeach
    @else
        <p style="text-align: center; color: #6b7280;">لا توجد مهام لهذا اليوم</p>
    @endif
    
    <div style="margin-top: 1rem;">
        <a href="{{ route('student.tasks') }}" class="pwa-btn pwa-btn-secondary">عرض جميع المهام</a>
    </div>
</div>

<!-- Today's Sessions -->
<div class="pwa-card">
    <h2>جلسات اليوم</h2>
    @if($todaySessions->count() > 0)
        @foreach($todaySessions as $session)
            <div class="pwa-schedule-item">
                <div class="pwa-schedule-time">
                    {{ $session->starts_at->format('H:i') }}
                </div>
                <div class="pwa-schedule-info">
                    <div class="pwa-schedule-title">{{ $session->title }}</div>
                    <div class="pwa-schedule-teacher">مع {{ $session->teacher->name ?? 'المعلم' }}</div>
                </div>
                <div style="color: #6b7280; font-size: 0.875rem;">
                    {{ $session->status === 'completed' ? 'مكتملة' : ($session->status === 'in_progress' ? 'جارية' : 'مجدولة') }}
                </div>
            </div>
        @endforeach
    @else
        <p style="text-align: center; color: #6b7280;">لا توجد جلسات لهذا اليوم</p>
    @endif
    
    <div style="margin-top: 1rem;">
        <a href="{{ route('student.schedule') }}" class="pwa-btn pwa-btn-secondary">عرض الجدول الكامل</a>
    </div>
</div>

<!-- Points and Badges -->
<div class="pwa-card">
    <h2>النقاط والشارات</h2>
    <div class="pwa-points">
        <div class="pwa-points-icon">⭐</div>
        <div>
            <div style="font-weight: 600;">{{ $totalPoints }} نقطة</div>
            <div style="font-size: 0.875rem; color: #6b7280;">إجمالي النقاط</div>
        </div>
    </div>
    
    @if($badges->count() > 0)
        <div style="margin-top: 1rem;">
            <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">الشارات المكتسبة</h3>
            <div class="pwa-badges">
                @foreach($badges as $studentBadge)
                    <span class="pwa-badge">{{ $studentBadge->badge->name ?? 'شارة' }}</span>
                @endforeach
            </div>
        </div>
    @endif
    
    <div style="margin-top: 1rem;">
        <a href="{{ route('student.points') }}" class="pwa-btn pwa-btn-secondary">عرض التفاصيل</a>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('student.ranking') }}" class="pwa-btn pwa-btn-secondary">الترتيب</a>
        <a href="{{ route('student.subscription') }}" class="pwa-btn pwa-btn-secondary">الاشتراك</a>
    </div>
</div>
@endsection


