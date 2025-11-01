@extends('layouts.pwa')

@section('title', 'الاشتراك - حسوني')
@section('header-title', 'الاشتراك')
@section('header-subtitle', '{{ auth()->user()->name }}')

@section('content')
@if($subscription)
<!-- Current Subscription -->
<div class="pwa-card">
    <h2>الاشتراك الحالي</h2>
    <div style="background: #f0fdf4; padding: 1.5rem; border-radius: 0.5rem; border-right: 4px solid #059669;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div>
                <div style="font-weight: 600; font-size: 1.125rem;">{{ $subscription->feesPlan->name ?? 'خطة أساسية' }}</div>
                <div style="color: #6b7280; font-size: 0.875rem;">{{ $subscription->feesPlan->description ?? 'وصف الخطة' }}</div>
            </div>
            <div style="text-align: left;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">{{ $subscription->feesPlan->amount ?? 0 }} {{ $subscription->feesPlan->currency ?? 'ريال' }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">{{ $subscription->feesPlan->billing_period ?? 'شهري' }}</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <div>
                <div style="font-size: 0.875rem; color: #6b7280;">تاريخ البداية</div>
                <div style="font-weight: 600;">{{ $subscription->start_date ? $subscription->start_date->format('Y/m/d') : 'غير محدد' }}</div>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: #6b7280;">تاريخ الانتهاء</div>
                <div style="font-weight: 600;">{{ $subscription->end_date ? $subscription->end_date->format('Y/m/d') : 'غير محدد' }}</div>
            </div>
        </div>
        
        <div style="margin-top: 1rem;">
            <span style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500;
                        @if($subscription->status === 'active') background: #f0fdf4; color: #059669;
                        @elseif($subscription->status === 'expired') background: #fef2f2; color: #dc2626;
                        @else background: #fef3c7; color: #d97706; @endif">
                @if($subscription->status === 'active') نشط
                @elseif($subscription->status === 'expired') منتهي
                @elseif($subscription->status === 'cancelled') ملغي
                @elseif($subscription->status === 'suspended') معلق
                @else {{ $subscription->status }} @endif
            </span>
        </div>
    </div>
</div>

<!-- Subscription Progress -->
@if($subscription->start_date && $subscription->end_date)
<div class="pwa-card">
    <h2>تقدم الاشتراك</h2>
    @php
        $totalDays = $subscription->start_date->diffInDays($subscription->end_date);
        $passedDays = $subscription->start_date->diffInDays(now());
        $progress = min(100, max(0, ($passedDays / $totalDays) * 100));
    @endphp
    
    <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.875rem; color: #6b7280;">{{ round($progress) }}% مكتمل</span>
            <span style="font-size: 0.875rem; color: #6b7280;">{{ $passedDays }} من {{ $totalDays }} يوم</span>
        </div>
        <div style="background: #e2e8f0; height: 0.5rem; border-radius: 0.25rem; overflow: hidden;">
            <div style="background: #1e40af; height: 100%; width: {{ $progress }}%; transition: width 0.3s;"></div>
        </div>
    </div>
</div>
@endif

@else
<!-- No Subscription -->
<div class="pwa-card">
    <h2>لا يوجد اشتراك نشط</h2>
    <div style="text-align: center; padding: 2rem; color: #6b7280;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
        <p>لم يتم العثور على اشتراك نشط</p>
        <p style="font-size: 0.875rem;">يرجى التواصل مع الإدارة لتفعيل الاشتراك</p>
    </div>
</div>
@endif

<!-- Recent Payments -->
@if($payments->count() > 0)
<div class="pwa-card">
    <h2>آخر المدفوعات</h2>
    @foreach($payments as $payment)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem; margin-bottom: 0.5rem;">
            <div>
                <div style="font-weight: 600;">{{ $payment->amount }} {{ $payment->currency }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">{{ $payment->created_at->format('Y/m/d H:i') }}</div>
            </div>
            <div style="text-align: left;">
                <span style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500;
                            @if($payment->status === 'completed') background: #f0fdf4; color: #059669;
                            @elseif($payment->status === 'pending') background: #fef3c7; color: #d97706;
                            @elseif($payment->status === 'failed') background: #fef2f2; color: #dc2626;
                            @else background: #f1f5f9; color: #64748b; @endif">
                    @if($payment->status === 'completed') مكتمل
                    @elseif($payment->status === 'pending') معلق
                    @elseif($payment->status === 'failed') فشل
                    @elseif($payment->status === 'refunded') مسترد
                    @else {{ $payment->status }} @endif
                </span>
            </div>
        </div>
    @endforeach
</div>
@endif

<!-- Payment Methods -->
<div class="pwa-card">
    <h2>طرق الدفع المتاحة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">💳</div>
            <div style="font-size: 0.875rem;">بطاقة ائتمان</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏦</div>
            <div style="font-size: 0.875rem;">تحويل بنكي</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">💵</div>
            <div style="font-size: 0.875rem;">نقدي</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🌐</div>
            <div style="font-size: 0.875rem;">أونلاين</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('student.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
        <button class="pwa-btn pwa-btn-secondary" onclick="contactSupport()">تواصل مع الدعم</button>
    </div>
</div>

<script>
function contactSupport() {
    alert('يرجى التواصل مع الإدارة عبر الهاتف أو البريد الإلكتروني');
}
</script>
@endsection


