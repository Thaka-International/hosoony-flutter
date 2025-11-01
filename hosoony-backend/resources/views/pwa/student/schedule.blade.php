@extends('layouts.pwa')

@section('title', 'جدول الجلسات - حسوني')
@section('header-title', 'جدول الجلسات')
@section('header-subtitle', 'جلسات هذا الأسبوع')

@section('content')
<!-- Week Navigation -->
<div class="pwa-card">
    <h2>أسبوع {{ now()->format('Y/m/d') }}</h2>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <button class="pwa-btn pwa-btn-secondary" onclick="previousWeek()">الأسبوع السابق</button>
        <span style="font-weight: 600;">الأسبوع الحالي</span>
        <button class="pwa-btn pwa-btn-secondary" onclick="nextWeek()">الأسبوع التالي</button>
    </div>
</div>

<!-- Sessions by Day -->
@php
$daysOfWeek = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
$weekStart = now()->startOfWeek();
@endphp

@foreach($daysOfWeek as $index => $dayName)
    @php
        $currentDay = $weekStart->copy()->addDays($index);
        $daySessions = $sessions->filter(function($session) use ($currentDay) {
            return $session->starts_at->format('Y-m-d') === $currentDay->format('Y-m-d');
        });
    @endphp
    
    <div class="pwa-card">
        <h2>{{ $dayName }} - {{ $currentDay->format('m/d') }}</h2>
        
        @if($daySessions->count() > 0)
            @foreach($daySessions as $session)
                <div class="pwa-schedule-item">
                    <div class="pwa-schedule-time">
                        {{ $session->starts_at->format('H:i') }}
                    </div>
                    <div class="pwa-schedule-info">
                        <div class="pwa-schedule-title">{{ $session->title }}</div>
                        <div class="pwa-schedule-teacher">مع {{ $session->teacher->name ?? 'المعلم' }}</div>
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
            <div style="text-align: center; padding: 1rem; color: #6b7280;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📅</div>
                <p>لا توجد جلسات لهذا اليوم</p>
            </div>
        @endif
    </div>
@endforeach

<!-- Session Statistics -->
<div class="pwa-card">
    <h2>إحصائيات الأسبوع</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">{{ $sessions->where('status', 'completed')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">مكتملة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #d97706;">{{ $sessions->where('status', 'scheduled')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">مجدولة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #eff6ff; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #1e40af;">{{ $sessions->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">إجمالي</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('student.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
        <a href="{{ route('student.tasks') }}" class="pwa-btn pwa-btn-secondary">المهام</a>
    </div>
</div>

<script>
function previousWeek() {
    // Implementation for previous week navigation
    console.log('Previous week');
}

function nextWeek() {
    // Implementation for next week navigation
    console.log('Next week');
}
</script>
@endsection


