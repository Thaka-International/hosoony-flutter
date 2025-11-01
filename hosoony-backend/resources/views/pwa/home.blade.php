@extends('layouts.pwa')

@section('title', 'حسوني - منصة القرآن الكريم')
@section('header-title', 'حسوني')
@section('header-subtitle', 'منصة تعليم القرآن الكريم')

@section('content')
<div class="pwa-card">
    <h2>مرحباً بك في حسوني</h2>
    <p>منصة تعليم القرآن الكريم للطلاب والمعلمين</p>
    
    @guest
        <div style="margin-top: 2rem;">
            <a href="{{ route('login') }}" class="pwa-btn">تسجيل الدخول</a>
        </div>
    @else
        <div style="margin-top: 2rem;">
            @if(auth()->user()->isStudent())
                <a href="{{ route('student.dashboard') }}" class="pwa-btn">لوحة الطالب</a>
            @elseif(auth()->user()->isTeacher())
                <a href="{{ route('teacher.dashboard') }}" class="pwa-btn">لوحة المعلم</a>
            @endif
        </div>
    @endguest
</div>

<div class="pwa-card">
    <h2>المميزات</h2>
    <ul style="list-style: none; padding: 0;">
        <li style="margin-bottom: 0.5rem;">📚 تعلم القرآن الكريم بطريقة تفاعلية</li>
        <li style="margin-bottom: 0.5rem;">🏆 نظام النقاط والشارات</li>
        <li style="margin-bottom: 0.5rem;">📅 جدول الجلسات والمهام</li>
        <li style="margin-bottom: 0.5rem;">📊 متابعة التقدم والإنجازات</li>
        <li style="margin-bottom: 0.5rem;">📱 تطبيق ويب متقدم (PWA)</li>
    </ul>
</div>
@endsection


