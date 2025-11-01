@extends('layouts.pwa')

@section('title', 'تسجيل الحضور - حسوني')
@section('header-title', 'تسجيل الحضور')
@section('header-subtitle', 'تسجيل حضور الطلاب')

@section('content')
<!-- Today's Sessions -->
<div class="pwa-card">
    <h2>جلسات اليوم</h2>
    
    @if($todaySessions->count() > 0)
        @foreach($todaySessions as $session)
            <div class="pwa-card" style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <div style="font-weight: 600; font-size: 1.125rem;">{{ $session->title }}</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">{{ $session->starts_at->format('H:i') }} - {{ $session->ends_at ? $session->ends_at->format('H:i') : 'غير محدد' }}</div>
                    </div>
                    <span style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500;
                                @if($session->status === 'completed') background: #f0fdf4; color: #059669;
                                @elseif($session->status === 'in_progress') background: #fef3c7; color: #d97706;
                                @else background: #eff6ff; color: #1e40af; @endif">
                        @if($session->status === 'completed') مكتملة
                        @elseif($session->status === 'in_progress') جارية
                        @else مجدولة @endif
                    </span>
                </div>
                
                @if($session->status === 'in_progress' || $session->status === 'scheduled')
                    <div style="margin-top: 1rem;">
                        <button class="pwa-btn" onclick="takeAttendance({{ $session->id }})">
                            تسجيل الحضور
                        </button>
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
            <p>لا توجد جلسات لهذا اليوم</p>
            <p style="font-size: 0.875rem;">ستظهر الجلسات هنا عند جدولتها</p>
        </div>
    @endif
</div>

<!-- Quick Attendance -->
<div class="pwa-card">
    <h2>تسجيل سريع</h2>
    <form id="quickAttendanceForm">
        @csrf
        <div class="pwa-form-group">
            <label for="student_name" class="pwa-form-label">اسم الطالب</label>
            <input type="text" id="student_name" name="student_name" class="pwa-form-input" 
                   placeholder="اكتب اسم الطالب أو رقمه">
        </div>
        
        <div class="pwa-form-group">
            <label for="attendance_status" class="pwa-form-label">حالة الحضور</label>
            <select id="attendance_status" name="attendance_status" class="pwa-form-input">
                <option value="present">حاضر</option>
                <option value="absent">غائب</option>
                <option value="late">متأخر</option>
                <option value="excused">معذور</option>
            </select>
        </div>
        
        <div class="pwa-form-group">
            <label for="notes" class="pwa-form-label">ملاحظات (اختياري)</label>
            <textarea id="notes" name="notes" class="pwa-form-input" rows="3" 
                      placeholder="أي ملاحظات إضافية..."></textarea>
        </div>
        
        <button type="submit" class="pwa-btn" style="width: 100%;">
            تسجيل الحضور
        </button>
    </form>
</div>

<!-- Attendance Statistics -->
<div class="pwa-card">
    <h2>إحصائيات الحضور</h2>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">0</div>
            <div style="font-size: 0.875rem; color: #6b7280;">حاضر</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;">0</div>
            <div style="font-size: 0.875rem; color: #6b7280;">غائب</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #d97706;">0</div>
            <div style="font-size: 0.875rem; color: #6b7280;">متأخر</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f1f5f9; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #64748b;">0</div>
            <div style="font-size: 0.875rem; color: #6b7280;">معذور</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('teacher.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
        <a href="{{ route('teacher.reports') }}" class="pwa-btn pwa-btn-secondary">التقارير</a>
    </div>
</div>

<script>
function takeAttendance(sessionId) {
    // Implementation for taking attendance for a specific session
    console.log('Taking attendance for session:', sessionId);
    alert('سيتم فتح نافذة تسجيل الحضور للجلسة');
}

document.getElementById('quickAttendanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Simulate API call
    fetch('/api/v1/attendance', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم تسجيل الحضور بنجاح');
            this.reset();
        } else {
            alert('حدث خطأ في تسجيل الحضور');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال');
    });
});
</script>
@endsection


