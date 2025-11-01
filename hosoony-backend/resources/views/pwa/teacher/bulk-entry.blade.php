@extends('layouts.pwa')

@section('title', 'الإدخال الجماعي - حسوني')
@section('header-title', 'الإدخال الجماعي')
@section('header-subtitle', 'إدخال بيانات متعددة')

@section('content')
<!-- Bulk Entry Form -->
<div class="pwa-card">
    <h2>إدخال جماعي للمهام</h2>
    <form id="bulkEntryForm">
        @csrf
        <div class="pwa-form-group">
            <label for="entry_type" class="pwa-form-label">نوع الإدخال</label>
            <select id="entry_type" name="entry_type" class="pwa-form-input" required>
                <option value="">اختر نوع الإدخال</option>
                <option value="daily_tasks">مهام يومية</option>
                <option value="attendance">حضور</option>
                <option value="segments">مقاطع قرآنية</option>
                <option value="points">نقاط</option>
            </select>
        </div>
        
        <div class="pwa-form-group">
            <label for="students" class="pwa-form-label">الطلاب</label>
            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 0.5rem;">
                @foreach($students as $student)
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.25rem;">
                        <input type="checkbox" name="students[]" value="{{ $student->id }}">
                        <span>{{ $student->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        
        <div class="pwa-form-group">
            <label for="bulk_data" class="pwa-form-label">البيانات</label>
            <textarea id="bulk_data" name="bulk_data" class="pwa-form-input" rows="5" 
                      placeholder="أدخل البيانات هنا (سطر واحد لكل طالب)"></textarea>
        </div>
        
        <div class="pwa-form-group">
            <label for="notes" class="pwa-form-label">ملاحظات عامة (اختياري)</label>
            <textarea id="notes" name="notes" class="pwa-form-input" rows="3" 
                      placeholder="ملاحظات إضافية..."></textarea>
        </div>
        
        <button type="submit" class="pwa-btn" style="width: 100%;">
            إدخال البيانات
        </button>
    </form>
</div>

<!-- Students List -->
<div class="pwa-card">
    <h2>قائمة الطلاب</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem;">
        @foreach($students as $student)
            <div style="padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $student->name }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">{{ $student->role === 'student' ? 'طالب' : $student->role }}</div>
                @if($student->status)
                    <div style="font-size: 0.875rem; color: #6b7280;">الحالة: {{ $student->status }}</div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<!-- Bulk Entry Templates -->
<div class="pwa-card">
    <h2>قوالب الإدخال</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <button class="pwa-btn pwa-btn-secondary" onclick="loadTemplate('daily_tasks')">
            قالب المهام اليومية
        </button>
        <button class="pwa-btn pwa-btn-secondary" onclick="loadTemplate('attendance')">
            قالب الحضور
        </button>
        <button class="pwa-btn pwa-btn-secondary" onclick="loadTemplate('segments')">
            قالب المقاطع
        </button>
        <button class="pwa-btn pwa-btn-secondary" onclick="loadTemplate('points')">
            قالب النقاط
        </button>
    </div>
</div>

<!-- Recent Bulk Entries -->
<div class="pwa-card">
    <h2>آخر الإدخالات الجماعية</h2>
    <div style="text-align: center; padding: 2rem; color: #6b7280;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
        <p>لا توجد إدخالات جماعية سابقة</p>
        <p style="font-size: 0.875rem;">ستظهر الإدخالات الجماعية هنا</p>
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
const templates = {
    daily_tasks: `مهمة حفظ سورة الفاتحة
مراجعة الدروس السابقة
تلاوة صفحة من القرآن
حفظ آيات جديدة`,
    
    attendance: `حاضر
حاضر
غائب
متأخر`,
    
    segments: `سورة الفاتحة - آية 1-7
سورة البقرة - آية 1-5
سورة آل عمران - آية 1-3`,
    
    points: `10
15
20
5`
};

function loadTemplate(type) {
    const textarea = document.getElementById('bulk_data');
    const entryType = document.getElementById('entry_type');
    
    entryType.value = type;
    textarea.value = templates[type] || '';
    
    // Select all students
    const checkboxes = document.querySelectorAll('input[name="students[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

document.getElementById('bulkEntryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const selectedStudents = Array.from(document.querySelectorAll('input[name="students[]"]:checked')).map(cb => cb.value);
    
    if (selectedStudents.length === 0) {
        alert('يرجى اختيار طالب واحد على الأقل');
        return;
    }
    
    // Add selected students to form data
    formData.delete('students[]');
    selectedStudents.forEach(studentId => {
        formData.append('students[]', studentId);
    });
    
    // Simulate API call
    fetch('/api/v1/bulk-entry', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`تم إدخال البيانات بنجاح لـ ${selectedStudents.length} طالب`);
            this.reset();
        } else {
            alert('حدث خطأ في إدخال البيانات');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال');
    });
});

// Auto-select all students when form loads
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="students[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
});
</script>
@endsection


