@extends('layouts.pwa')

@section('title', 'ترتيب الطلاب - حسوني')
@section('header-title', 'ترتيب الطلاب')
@section('header-subtitle', 'أفضل 5 طلاب في الفصل')

@section('content')
<!-- Top 5 Students -->
<div class="pwa-card">
    <h2>ترتيب اليوم - أفضل 5</h2>
    
    @if($topStudents->count() > 0)
        @foreach($topStudents as $index => $student)
            <div class="pwa-ranking">
                <div class="pwa-ranking-position {{ $index === 0 ? 'first' : ($index === 1 ? 'second' : ($index === 2 ? 'third' : '')) }}">
                    {{ $index + 1 }}
                </div>
                <div class="pwa-ranking-info">
                    <div class="pwa-ranking-name">
                        {{ $student->name }}
                        @if($student->id === auth()->id())
                            <span style="color: #1e40af; font-size: 0.875rem;">(أنت)</span>
                        @endif
                    </div>
                    <div class="pwa-ranking-points">
                        {{ $student->gamification_points_sum_points ?? 0 }} نقطة
                    </div>
                </div>
                @if($index < 3)
                    <div style="font-size: 1.5rem;">
                        @if($index === 0) 🥇
                        @elseif($index === 1) 🥈
                        @elseif($index === 2) 🥉
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🏆</div>
            <p>لا توجد بيانات ترتيب متاحة</p>
        </div>
    @endif
</div>

<!-- Your Position -->
@if($userPosition > 0)
<div class="pwa-card">
    <h2>ترتيبك</h2>
    <div style="text-align: center; padding: 2rem;">
        <div style="font-size: 3rem; font-weight: 700; color: #1e40af; margin-bottom: 1rem;">
            #{{ $userPosition }}
        </div>
        <p style="color: #6b7280;">من إجمالي {{ $topStudents->count() }} طالب</p>
        
        @if($userPosition <= 5)
            <div style="margin-top: 1rem;">
                <span class="pwa-badge gold">في الخمسة الأوائل</span>
            </div>
        @elseif($userPosition <= 10)
            <div style="margin-top: 1rem;">
                <span class="pwa-badge silver">في العشرة الأوائل</span>
            </div>
        @endif
    </div>
</div>
@endif

<!-- Motivation -->
<div class="pwa-card">
    <h2>نصائح للتفوق</h2>
    <div style="background: #f0fdf4; padding: 1rem; border-radius: 0.5rem; border-right: 4px solid #059669;">
        <p style="margin-bottom: 0.5rem;">💡 <strong>نصيحة:</strong></p>
        <p style="font-size: 0.875rem; color: #6b7280;">
            أكمل مهامك اليومية في الوقت المحدد لتحصل على نقاط إضافية وتتقدم في الترتيب!
        </p>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('student.points') }}" class="pwa-btn pwa-btn-secondary">النقاط</a>
        <a href="{{ route('student.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
    </div>
</div>
@endsection


