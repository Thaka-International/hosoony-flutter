@extends('layouts.pwa')

@section('title', 'مراجعة التقارير - حسوني')
@section('header-title', 'مراجعة التقارير')
@section('header-subtitle', 'اعتماد تقارير الطلاب')

@section('content')
<!-- Daily Reports -->
<div class="pwa-card">
    <h2>تقارير اليوم - {{ now()->format('Y/m/d') }}</h2>
    
    @if($dailyLogs->count() > 0)
        @foreach($dailyLogs as $log)
            <div class="pwa-card" style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <div style="font-weight: 600; font-size: 1.125rem;">{{ $log->student->name ?? 'طالب غير محدد' }}</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">{{ $log->created_at->format('H:i') }}</div>
                    </div>
                    <span style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500;
                                @if($log->status === 'verified') background: #f0fdf4; color: #059669;
                                @elseif($log->status === 'rejected') background: #fef2f2; color: #dc2626;
                                @else background: #fef3c7; color: #d97706; @endif">
                        @if($log->status === 'verified') معتمد
                        @elseif($log->status === 'rejected') مرفوض
                        @else معلق @endif
                    </span>
                </div>
                
                @if($log->notes)
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <div style="font-weight: 600; margin-bottom: 0.5rem;">ملاحظات الطالب:</div>
                        <div style="color: #6b7280;">{{ $log->notes }}</div>
                    </div>
                @endif
                
                @if($log->status === 'pending')
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="pwa-btn pwa-btn-success" onclick="approveReport({{ $log->id }})">
                            اعتماد
                        </button>
                        <button class="pwa-btn pwa-btn-warning" onclick="rejectReport({{ $log->id }})">
                            رفض
                        </button>
                    </div>
                @elseif($log->status === 'verified')
                    <div style="color: #059669; font-size: 0.875rem;">
                        ✓ تم الاعتماد بواسطة {{ $log->verifiedBy->name ?? 'المعلم' }} في {{ $log->verified_at ? $log->verified_at->format('H:i') : 'وقت غير محدد' }}
                    </div>
                @elseif($log->status === 'rejected')
                    <div style="color: #dc2626; font-size: 0.875rem;">
                        ✗ تم الرفض بواسطة {{ $log->verifiedBy->name ?? 'المعلم' }} في {{ $log->verified_at ? $log->verified_at->format('H:i') : 'وقت غير محدد' }}
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
            <p>لا توجد تقارير لهذا اليوم</p>
            <p style="font-size: 0.875rem;">ستظهر التقارير هنا عندما يرسلها الطلاب</p>
        </div>
    @endif
</div>

<!-- Report Statistics -->
<div class="pwa-card">
    <h2>إحصائيات التقارير</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #d97706;">{{ $dailyLogs->where('status', 'pending')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">معلقة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #059669;">{{ $dailyLogs->where('status', 'verified')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">معتمدة</div>
        </div>
        <div style="text-align: center; padding: 1rem; background: #fef2f2; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #dc2626;">{{ $dailyLogs->where('status', 'rejected')->count() }}</div>
            <div style="font-size: 0.875rem; color: #6b7280;">مرفوضة</div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
<div class="pwa-card">
    <h2>إجراءات جماعية</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <button class="pwa-btn pwa-btn-success" onclick="approveAllPending()">
            اعتماد جميع المعلقة
        </button>
        <button class="pwa-btn pwa-btn-secondary" onclick="exportReports()">
            تصدير التقارير
        </button>
    </div>
</div>

<!-- Quick Actions -->
<div class="pwa-card">
    <h2>إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
        <a href="{{ route('teacher.dashboard') }}" class="pwa-btn pwa-btn-secondary">الرئيسية</a>
        <a href="{{ route('teacher.attendance') }}" class="pwa-btn pwa-btn-secondary">الحضور</a>
    </div>
</div>

<script>
function approveReport(logId) {
    if (confirm('هل تريد اعتماد هذا التقرير؟')) {
        updateReportStatus(logId, 'verified');
    }
}

function rejectReport(logId) {
    if (confirm('هل تريد رفض هذا التقرير؟')) {
        updateReportStatus(logId, 'rejected');
    }
}

function updateReportStatus(logId, status) {
    fetch(`/api/v1/daily-logs/${logId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم تحديث حالة التقرير بنجاح');
            location.reload();
        } else {
            alert('حدث خطأ في تحديث حالة التقرير');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال');
    });
}

function approveAllPending() {
    if (confirm('هل تريد اعتماد جميع التقارير المعلقة؟')) {
        const pendingLogs = document.querySelectorAll('[data-status="pending"]');
        // Implementation for bulk approval
        alert('سيتم اعتماد جميع التقارير المعلقة');
    }
}

function exportReports() {
    // Implementation for exporting reports
    alert('سيتم تصدير التقارير');
}
</script>
@endsection


