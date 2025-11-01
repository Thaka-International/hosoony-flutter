@extends('layouts.pwa')

@section('title', 'الدفع - حسوني')
@section('header-title', 'الدفع')
@section('header-subtitle', 'دفع رسوم الاشتراك')

@section('content')
<div class="pwa-card">
    <h2>تفاصيل الفاتورة</h2>
    
    <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; padding: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
            <span>المبلغ:</span>
            <span style="font-weight: 700;">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
            <span>تاريخ الاستحقاق:</span>
            <span>{{ $payment->due_date->format('Y/m/d') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
            <span>البرنامج:</span>
            <span>{{ $subscription->feesPlan->name ?? 'غير محدد' }}</span>
        </div>
    </div>
</div>

<div class="pwa-card">
    <h2>اختر طريقة الدفع</h2>
    
    <div style="display: grid; gap: 1rem;">
        <!-- PayPal Payment -->
        <div class="payment-method" data-method="paypal">
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;">
                <div style="width: 40px; height: 40px; background: #0070ba; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    PP
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">PayPal</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">الدفع الآمن عبر PayPal</div>
                </div>
                <div style="width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 50%; position: relative;">
                    <div class="payment-radio" style="width: 12px; height: 12px; background: #0070ba; border-radius: 50%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;"></div>
                </div>
            </div>
        </div>

        <!-- Fastlane PayPal Payment -->
        <div class="payment-method" data-method="fastlane_paypal">
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;">
                <div style="width: 40px; height: 40px; background: #ff6b35; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    FL
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Fastlane PayPal</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">الدفع السريع عبر Fastlane</div>
                </div>
                <div style="width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 50%; position: relative;">
                    <div class="payment-radio" style="width: 12px; height: 12px; background: #ff6b35; border-radius: 50%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;"></div>
                </div>
            </div>
        </div>

        <!-- Bank Transfer -->
        <div class="payment-method" data-method="bank_transfer">
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;">
                <div style="width: 40px; height: 40px; background: #059669; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    🏦
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">تحويل بنكي</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">تحويل مباشر إلى الحساب البنكي</div>
                </div>
                <div style="width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 50%; position: relative;">
                    <div class="payment-radio" style="width: 12px; height: 12px; background: #059669; border-radius: 50%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Button -->
<div class="pwa-card">
    <button id="pay-button" class="pwa-btn pwa-btn-primary" style="width: 100%; padding: 1rem; font-size: 1.125rem; font-weight: 600;" disabled>
        اختر طريقة الدفع أولاً
    </button>
</div>

<!-- Payment Instructions -->
<div class="pwa-card" id="bank-instructions" style="display: none;">
    <h2>تعليمات التحويل البنكي</h2>
    <div style="background: #f0fdf4; padding: 1rem; border-radius: 0.5rem; border-right: 4px solid #059669;">
        <div style="margin-bottom: 1rem;">
            <strong>اسم البنك:</strong> البنك الأهلي السعودي
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>رقم الحساب:</strong> 1234567890123456
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>اسم المستفيد:</strong> حصوني للتعليم القرآني
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>المبلغ:</strong> {{ number_format($payment->amount, 2) }} {{ $payment->currency }}
        </div>
        <div style="font-size: 0.875rem; color: #6b7280;">
            يرجى إرسال صورة من إيصال التحويل عبر الواتساب أو البريد الإلكتروني
        </div>
    </div>
</div>

<script>
let selectedMethod = null;

document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.payment-method').forEach(m => {
            m.querySelector('div').style.borderColor = '#e5e7eb';
            m.querySelector('.payment-radio').style.display = 'none';
        });
        
        // Select current method
        this.querySelector('div').style.borderColor = this.dataset.method === 'paypal' ? '#0070ba' : 
                                                     this.dataset.method === 'fastlane_paypal' ? '#ff6b35' : '#059669';
        this.querySelector('.payment-radio').style.display = 'block';
        
        selectedMethod = this.dataset.method;
        
        // Update button
        const button = document.getElementById('pay-button');
        button.disabled = false;
        button.textContent = 'المتابعة للدفع';
        
        // Show/hide bank instructions
        const bankInstructions = document.getElementById('bank-instructions');
        if (selectedMethod === 'bank_transfer') {
            bankInstructions.style.display = 'block';
        } else {
            bankInstructions.style.display = 'none';
        }
    });
});

document.getElementById('pay-button').addEventListener('click', function() {
    if (!selectedMethod) return;
    
    if (selectedMethod === 'bank_transfer') {
        // Show bank transfer instructions
        alert('يرجى إتباع تعليمات التحويل البنكي المعروضة أعلاه');
        return;
    }
    
    // Process online payment
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    const url = selectedMethod === 'paypal' ? 
        '{{ route("student.payment.paypal") }}' : 
        '{{ route("student.payment.fastlane") }}';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.approval_url;
        } else {
            alert('فشل في إنشاء طلب الدفع: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء معالجة الدفع');
    });
});
</script>

<style>
.payment-method:hover > div {
    border-color: #d1d5db !important;
    background-color: #f9fafb;
}

.payment-method.selected > div {
    background-color: #f0f9ff;
}
</style>
@endsection


