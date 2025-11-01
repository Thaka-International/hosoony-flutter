@extends('layouts.pwa')

@section('title', 'لوحة المعلم - حسوني')
@section('header-title', 'لوحة المعلم')
@section('header-subtitle', 'مرحباً بك، {{ auth()->user()->name }}')

@section('content')
<!-- Quick Stats -->
<div class="pwa-card">
    <h2>إحصائيات اليوم</h2>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
        <div style="text-align: center; padding: 1rem; background: #eff6ff; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #1e40af;">{{ $todaySessions->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">جلسات اليوم</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #d97706;">{{ $pendingLogs->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">تقارير معلقة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #059669;">{{ $classStats['attendance_today'] }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">حضور اليوم</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fdf2f8; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #be185d;">{{ $classStats['average_performance'] }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">متوسط الأداء</div>
        </div>
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
                    <div class="pwa-schedule-teacher">{{ $session->class->name ?? 'فصل غير محدد' }}</div>
                    @if($session->description)
                        <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                            {{ Str::limit($session->description, 50) }}
                        </div>
                    @endif
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem;">
                    <span style="font-size: 0.875rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; 
                                @if($session->status === 'completed') background: #f0fdf4; color: #059669;
                                @elseif($session->status === 'in_progress') background: #fef3c7; color: #d97706;
                                @else background: #eff6ff; color: #1e40af; @endif">
                        @if($session->status === 'completed') مكتملة
                        @elseif($session->status === 'in_progress') جارية
                        @else مجدولة @endif
                    </span>
                    @if($session->ends_at)
                        <span style="font-size: 0.75rem; color: #6b7280;">
                            حتى {{ $session->ends_at->format('H:i') }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
            <p>لا توجد جلسات لهذا اليوم</p>
        </div>
    @endif
    
    <div style="margin-top: 1rem;">
        <a href="{{ route('teacher.timeline') }}" class="pwa-btn pwa-btn-secondary">عرض الجدول الكامل</a>
    </div>
</div>

<!-- Upcoming Sessions -->
@if($upcomingSessions->count() > 0)
<div class="pwa-card">
    <h2>الجلسات القادمة (7 أيام)</h2>
    @foreach($upcomingSessions->take(3) as $session)
        <div class="pwa-schedule-item">
            <div class="pwa-schedule-time">
                {{ $session->starts_at->format('M d') }}<br>
                {{ $session->starts_at->format('H:i') }}
            </div>
            <div class="pwa-schedule-info">
                <div class="pwa-schedule-title">{{ $session->title }}</div>
                <div class="pwa-schedule-teacher">{{ $session->class->name ?? 'فصل غير محدد' }}</div>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem;">
                <span style="font-size: 0.75rem; color: #6b7280;">
                    {{ $session->starts_at->diffForHumans() }}
                </span>
            </div>
        </div>
    @endforeach
    
    @if($upcomingSessions->count() > 3)
        <div style="margin-top: 1rem;">
            <a href="{{ route('teacher.timeline') }}" class="pwa-btn pwa-btn-secondary">عرض جميع الجلسات</a>
        </div>
    @endif
</div>
@endif

<!-- Recent Performance Evaluations -->
@if($recentEvaluations->count() > 0)
<div class="pwa-card">
    <h2>آخر التقييمات</h2>
    @foreach($recentEvaluations as $evaluation)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem; margin-bottom: 0.5rem;">
            <div>
                <div style="font-weight: 600;">{{ $evaluation->student->name }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">{{ $evaluation->session->title ?? 'تقييم عام' }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-weight: 700; color: #059669;">{{ $evaluation->total_score }}/10</div>
                <div style="font-size: 0.75rem; color: #6b7280;">{{ $evaluation->evaluated_at->diffForHumans() }}</div>
            </div>
        </div>
    @endforeach
    
    <div style="margin-top: 1rem;">
        <a href="{{ route('teacher.timeline') }}" class="pwa-btn pwa-btn-secondary">عرض جميع التقييمات</a>
    </div>
</div>
@endif

<!-- Pending Reports -->
@if($pendingLogs->count() > 0)
<div class="pwa-card">
    <h2>تقارير تحتاج مراجعة</h2>
    @foreach($pendingLogs->take(3) as $log)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #fef3c7; border-radius: 0.5rem; margin-bottom: 0.5rem;">
            <div>
                <div style="font-weight: 600;">{{ $log->student->name }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">{{ $log->log_date->format('Y-m-d') }}</div>
            </div>
            <div style="font-size: 0.75rem; color: #d97706;">في انتظار المراجعة</div>
        </div>
    @endforeach
    
    <div style="margin-top: 1rem;">
        <a href="{{ route('teacher.reports') }}" class="pwa-btn pwa-btn-warning">مراجعة التقارير</a>
    </div>
</div>
@endif

<!-- Class Statistics -->
<div class="pwa-card">
    <h2>إحصائيات الفصل</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #1e40af;">{{ $classStats['total_students'] }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">إجمالي الطلاب</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #059669;">{{ $classStats['active_students'] }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">طلاب نشطين</div>
        </div>
    </div>
</div>

<!-- Notifications -->
@if($notifications->count() > 0)
<div class="pwa-card">
    <h2>الإشعارات</h2>
    @foreach($notifications as $notification)
        <div style="padding: 0.75rem; background: #f0f9ff; border-radius: 0.5rem; margin-bottom: 0.5rem; border-right: 4px solid #0ea5e9;">
            <div style="font-weight: 600;">{{ $notification->title }}</div>
            <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">{{ Str::limit($notification->message, 100) }}</div>
            <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">{{ $notification->created_at->diffForHumans() }}</div>
        </div>
    @endforeach
</div>
@endif

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('teacher.attendance') }}" class="pwa-btn pwa-btn-secondary">تسجيل الحضور</a>
        <a href="{{ route('teacher.segments') }}" class="pwa-btn pwa-btn-secondary">إدخال المقاطع</a>
        <a href="{{ route('teacher.reports') }}" class="pwa-btn pwa-btn-secondary">مراجعة التقارير</a>
        <a href="{{ route('teacher.bulk-entry') }}" class="pwa-btn pwa-btn-secondary">إدخال جماعي</a>
    </div>
</div>
@endsection
