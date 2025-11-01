@extends('layouts.pwa')

@section('title', 'الجدول الزمني - حسوني')
@section('header-title', 'الجدول الزمني')
@section('header-subtitle', 'نشاطات اليوم')

@section('content')
<!-- Timeline -->
<div class="pwa-card">
    <h2>الجدول الزمني</h2>
    
    @if($timeline->count() > 0)
        <div class="pwa-timeline">
            @foreach($timeline as $activity)
                <div class="pwa-timeline-item">
                    <div class="pwa-timeline-content">
                        <div class="pwa-timeline-time">{{ $activity->created_at->format('H:i') }}</div>
                        <div class="pwa-timeline-title">{{ $activity->description ?? 'نشاط' }}</div>
                        @if($activity->user)
                            <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">
                                بواسطة: {{ $activity->user->name }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
            <p>لا توجد نشاطات مسجلة</p>
            <p style="font-size: 0.875rem;">ستظهر النشاطات هنا عند حدوثها</p>
        </div>
    @endif
</div>

<!-- Activity Statistics -->
<div class="pwa-card">
    <h2>إحصائيات النشاط</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        <div style="text-align: center; padding: 1rem; background: #eff6ff; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #1e40af;">{{ $timeline->where('action', 'like', '%session%')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">جلسات</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">{{ $timeline->where('action', 'like', '%attendance%')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">حضور</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #d97706;">{{ $timeline->where('action', 'like', '%report%')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">تقارير</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('teacher.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
        <a href="{{ route('teacher.attendance') }}" class="pwa-btn pwa-btn-secondary">تسجيل الحضور</a>
    </div>
</div>
@endsection


