@extends('layouts.pwa')

@section('title', 'إدخال المقاطع - حسوني')
@section('header-title', 'إدخال المقاطع')
@section('header-subtitle', 'إدخال مقاطع القرآن الكريم')

@section('content')
<!-- Segment Entry Form -->
<div class="pwa-card">
    <h2>إدخال مقطع جديد</h2>
    <form id="segmentForm">
        @csrf
        <div class="pwa-form-group">
            <label for="surah" class="pwa-form-label">السورة</label>
            <select id="surah" name="surah" class="pwa-form-input" required>
                <option value="">اختر السورة</option>
                <option value="1">الفاتحة</option>
                <option value="2">البقرة</option>
                <option value="3">آل عمران</option>
                <option value="4">النساء</option>
                <option value="5">المائدة</option>
                <!-- Add more surahs as needed -->
            </select>
        </div>
        
        <div class="pwa-form-group">
            <label for="verse_start" class="pwa-form-label">الآية الأولى</label>
            <input type="number" id="verse_start" name="verse_start" class="pwa-form-input" 
                   placeholder="رقم الآية الأولى" required>
        </div>
        
        <div class="pwa-form-group">
            <label for="verse_end" class="pwa-form-label">الآية الأخيرة</label>
            <input type="number" id="verse_end" name="verse_end" class="pwa-form-input" 
                   placeholder="رقم الآية الأخيرة" required>
        </div>
        
        <div class="pwa-form-group">
            <label for="recitation_type" class="pwa-form-label">نوع التلاوة</label>
            <select id="recitation_type" name="recitation_type" class="pwa-form-input" required>
                <option value="">اختر نوع التلاوة</option>
                <option value="memorization">حفظ</option>
                <option value="recitation">تلاوة</option>
                <option value="review">مراجعة</option>
                <option value="listening">استماع</option>
            </select>
        </div>
        
        <div class="pwa-form-group">
            <label for="difficulty_level" class="pwa-form-label">مستوى الصعوبة</label>
            <select id="difficulty_level" name="difficulty_level" class="pwa-form-input" required>
                <option value="">اختر مستوى الصعوبة</option>
                <option value="easy">سهل</option>
                <option value="medium">متوسط</option>
                <option value="hard">صعب</option>
            </select>
        </div>
        
        <div class="pwa-form-group">
            <label for="notes" class="pwa-form-label">ملاحظات (اختياري)</label>
            <textarea id="notes" name="notes" class="pwa-form-input" rows="3" 
                      placeholder="أي ملاحظات حول المقطع..."></textarea>
        </div>
        
        <button type="submit" class="pwa-btn" style="width: 100%;">
            إدخال المقطع
        </button>
    </form>
</div>

<!-- Recent Segments -->
@if($segments->count() > 0)
<div class="pwa-card">
    <h2>آخر المقاطع المدخلة</h2>
    @foreach($segments as $segment)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem; margin-bottom: 0.5rem;">
            <div>
                <div style="font-weight: 600;">{{ $segment->description ?? 'مقطع جديد' }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">{{ $segment->created_at->format('Y/m/d H:i') }}</div>
            </div>
            <div style="text-align: left;">
                <span style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500; background: #eff6ff; color: #1e40af;">
                    مقطع
                </span>
            </div>
        </div>
    @endforeach
</div>
@endif

<!-- Segment Statistics -->
<div class="pwa-card">
    <h2>إحصائيات المقاطع</h2>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">0</div>
            <div style="font-size: 0.875rem; color: #6b7280;">حفظ</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #eff6ff; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #1e40af;">0</div>
            <div style="font-size: 0.875rem; color: #6b7280;">تلاوة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #d97706;">0</div>
            <div style="font-size: 0.875rem; color: #6b7280;">مراجعة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f1f5f9; border-radius: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #64748b;">0</div>
            <div style="font-size: 0.875rem; color: #6b7280;">استماع</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('teacher.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
        <a href="{{ route('teacher.bulk-entry') }}" class="pwa-btn pwa-btn-secondary">إدخال جماعي</a>
    </div>
</div>

<script>
document.getElementById('segmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Simulate API call
    fetch('/api/v1/segments', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم إدخال المقطع بنجاح');
            this.reset();
            // Refresh the page or update the segments list
            location.reload();
        } else {
            alert('حدث خطأ في إدخال المقطع');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال');
    });
});

// Auto-fill verse_end when verse_start is entered
document.getElementById('verse_start').addEventListener('input', function() {
    const verseEnd = document.getElementById('verse_end');
    if (!verseEnd.value) {
        verseEnd.value = this.value;
    }
});
</script>
@endsection


