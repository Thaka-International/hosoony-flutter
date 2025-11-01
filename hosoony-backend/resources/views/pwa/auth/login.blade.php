@extends('layouts.pwa')

@section('title', 'تسجيل الدخول - حسوني')
@section('header-title', 'تسجيل الدخول')
@section('header-subtitle', 'ادخل بياناتك للوصول إلى حسابك')

@section('content')
<div class="pwa-card">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <div class="pwa-form-group">
            <label for="email" class="pwa-form-label">البريد الإلكتروني</label>
            <input type="email" id="email" name="email" class="pwa-form-input" 
                   value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="pwa-message error">{{ $message }}</div>
            @enderror
        </div>

        <div class="pwa-form-group">
            <label for="password" class="pwa-form-label">كلمة المرور</label>
            <input type="password" id="password" name="password" class="pwa-form-input" required>
            @error('password')
                <div class="pwa-message error">{{ $message }}</div>
            @enderror
        </div>

        <div class="pwa-form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                تذكرني
            </label>
        </div>

        <button type="submit" class="pwa-btn" style="width: 100%;">
            تسجيل الدخول
        </button>
    </form>
</div>

<div class="pwa-card">
    <p style="text-align: center;">
        <a href="{{ route('home') }}" class="pwa-btn pwa-btn-secondary">
            العودة للرئيسية
        </a>
    </p>
</div>
@endsection


