@extends('layouts.pwa')

@section('title', 'النقاط والشارات - حسوني')
@section('header-title', 'النقاط والشارات')
@section('header-subtitle', '{{ auth()->user()->name }}')

@section('content')
<!-- Total Points -->
<div class="pwa-card">
    <h2>النقاط الإجمالية</h2>
    <div style="text-align: center; padding: 2rem;">
        <div style="font-size: 4rem; font-weight: 700; color: #fbbf24; margin-bottom: 1rem;">⭐</div>
        <div style="font-size: 3rem; font-weight: 700; color: #1e40af; margin-bottom: 0.5rem;">{{ $totalPoints }}</div>
        <div style="color: #6b7280;">نقطة</div>
    </div>
</div>

<!-- Recent Points -->
@if($recentPoints->count() > 0)
<div class="pwa-card">
    <h2>آخر النقاط المكتسبة</h2>
    @foreach($recentPoints as $point)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem; margin-bottom: 0.5rem;">
            <div>
                <div style="font-weight: 600;">{{ $point->description ?? 'نقطة مكتسبة' }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">{{ $point->created_at->diffForHumans() }}</div>
            </div>
            <div style="color: #059669; font-weight: 600;">+{{ $point->points }}</div>
        </div>
    @endforeach
</div>
@endif

<!-- Badges -->
@if($badges->count() > 0)
<div class="pwa-card">
    <h2>الشارات المكتسبة</h2>
    <div class="pwa-badges">
        @foreach($badges as $studentBadge)
            <div style="background: white; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; text-align: center; margin-bottom: 0.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏆</div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $studentBadge->badge->name ?? 'شارة' }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">{{ $studentBadge->created_at->format('Y/m/d') }}</div>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="pwa-card">
    <h2>الشارات</h2>
    <div style="text-align: center; padding: 2rem; color: #6b7280;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🏆</div>
        <p>لم تحصل على أي شارات بعد</p>
        <p style="font-size: 0.875rem;">أكمل المهام والأنشطة لتحصل على شارات جديدة!</p>
    </div>
</div>
@endif

<!-- Points History Chart Placeholder -->
<div class="pwa-card">
    <h2>تطور النقاط</h2>
    <div style="text-align: center; padding: 2rem; color: #6b7280;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📈</div>
        <p>رسم بياني لتطور النقاط</p>
        <p style="font-size: 0.875rem;">سيتم إضافة الرسم البياني في التحديثات القادمة</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('student.ranking') }}" class="pwa-btn pwa-btn-secondary">الترتيب</a>
        <a href="{{ route('student.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
    </div>
</div>
@endsection


