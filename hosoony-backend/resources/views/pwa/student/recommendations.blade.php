@extends('layouts.pwa')

@section('title', 'التوصيات - حسوني')
@section('header-title', 'التوصيات')
@section('header-subtitle', 'ملاحظات المعلمات وتوصيات التحسين')

@section('content')
<div class="pwa-card">
    <h2>تقييمات الأداء الأخيرة</h2>
    
    @if($recentEvaluations->count() > 0)
        <div style="display: grid; gap: 1rem;">
            @foreach($recentEvaluations as $evaluation)
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="margin: 0;">{{ $evaluation->session->title }}</h3>
                            <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">
                                {{ $evaluation->evaluated_at->format('Y/m/d') }} - {{ $evaluation->teacher->name }}
                            </p>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: {{ $evaluation->performance_color === 'success' ? '#059669' : ($evaluation->performance_color === 'warning' ? '#d97706' : '#dc2626') }};">
                                {{ number_format($evaluation->total_score, 1) }}
                            </div>
                            <div style="font-size: 0.75rem; color: #6b7280;">{{ $evaluation->performance_level }}</div>
                        </div>
                    </div>
                    
                    <!-- Score Breakdown -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.5rem; margin-bottom: 1rem;">
                        @foreach($evaluation->getScoreBreakdown() as $key => $score)
                            <div style="text-align: center; padding: 0.5rem; background: #f8fafc; border-radius: 0.25rem;">
                                <div style="font-size: 0.75rem; color: #6b7280;">{{ $score['label'] }}</div>
                                <div style="font-weight: bold; color: {{ $score['score'] >= 8 ? '#059669' : ($score['score'] >= 6 ? '#d97706' : '#dc2626') }};">
                                    {{ number_format($score['score'], 1) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Feedback -->
                    @if($evaluation->student_feedback)
                        <div style="background: #f0f9ff; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                            <h4 style="margin: 0 0 0.5rem 0; color: #0369a1;">ملاحظات المعلمة</h4>
                            <p style="margin: 0; line-height: 1.6;">{{ $evaluation->student_feedback }}</p>
                        </div>
                    @endif
                    
                    <!-- Improvement Areas -->
                    @if($evaluation->improvement_areas)
                        <div style="background: #fef3c7; padding: 1rem; border-radius: 0.5rem;">
                            <h4 style="margin: 0 0 0.5rem 0; color: #92400e;">مجالات التحسين</h4>
                            <p style="margin: 0; line-height: 1.6;">{{ $evaluation->improvement_areas }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
            <h3>لا توجد تقييمات بعد</h3>
            <p>ستظهر هنا التقييمات والتوصيات من معلماتك بعد كل جلسة</p>
        </div>
    @endif
</div>

<!-- Performance Statistics -->
@if($averages)
<div class="pwa-card">
    <h2>إحصائيات الأداء</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <!-- Weekly Average -->
        <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #0369a1; margin-bottom: 0.5rem;">
                {{ number_format($averages['weekly'], 1) }}
            </div>
            <div style="font-weight: 600; margin-bottom: 0.25rem;">المتوسط الأسبوعي</div>
            <div style="font-size: 0.75rem; color: #6b7280;">آخر 7 أيام</div>
        </div>
        
        <!-- Monthly Average -->
        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #059669; margin-bottom: 0.5rem;">
                {{ number_format($averages['monthly'], 1) }}
            </div>
            <div style="font-weight: 600; margin-bottom: 0.25rem;">المتوسط الشهري</div>
            <div style="font-size: 0.75rem; color: #6b7280;">آخر 30 يوم</div>
        </div>
        
        <!-- Semester Average -->
        <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #d97706; margin-bottom: 0.5rem;">
                {{ number_format($averages['semester'], 1) }}
            </div>
            <div style="font-weight: 600; margin-bottom: 0.25rem;">المتوسط الفصلي</div>
            <div style="font-size: 0.75rem; color: #6b7280;">آخر 4 أشهر</div>
        </div>
    </div>
</div>
@endif

<!-- Performance Trends -->
@if($performanceTrend)
<div class="pwa-card">
    <h2>اتجاه الأداء</h2>
    
    <div style="text-align: center; padding: 2rem;">
        @if($performanceTrend === 'improving')
            <div style="font-size: 3rem; margin-bottom: 1rem;">📈</div>
            <h3 style="color: #059669;">أداء متطور</h3>
            <p style="color: #6b7280;">أداؤك في تحسن مستمر! استمري على هذا النهج</p>
        @elseif($performanceTrend === 'declining')
            <div style="font-size: 3rem; margin-bottom: 1rem;">📉</div>
            <h3 style="color: #dc2626;">أداء يحتاج تحسين</h3>
            <p style="color: #6b7280;">راجعي مجالات التحسين واعملي على تطوير أدائك</p>
        @else
            <div style="font-size: 3rem; margin-bottom: 1rem;">➡️</div>
            <h3 style="color: #6b7280;">أداء مستقر</h3>
            <p style="color: #6b7280;">أداؤك مستقر، يمكنك العمل على تطويره أكثر</p>
        @endif
    </div>
</div>
@endif

<!-- Recommendations Summary -->
@if($recommendationsSummary)
<div class="pwa-card">
    <h2>ملخص التوصيات</h2>
    
    <div style="display: grid; gap: 1rem;">
        @foreach($recommendationsSummary as $category => $recommendations)
            <div style="border-right: 4px solid #3b82f6; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
                <h4 style="margin: 0 0 0.5rem 0; color: #1e40af;">{{ $category }}</h4>
                <ul style="margin: 0; padding-right: 1rem;">
                    @foreach($recommendations as $recommendation)
                        <li style="margin-bottom: 0.25rem; line-height: 1.6;">{{ $recommendation }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Action Buttons -->
<div class="pwa-card">
    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="{{ route('student.dashboard') }}" class="pwa-btn pwa-btn-secondary">
            العودة للرئيسية
        </a>
        <a href="{{ route('student.schedule') }}" class="pwa-btn pwa-btn-primary">
            الجدول الدراسي
        </a>
    </div>
</div>

<script>
// Auto-refresh every 5 minutes to get new evaluations
setInterval(function() {
    // Only refresh if user is still on this page
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 300000); // 5 minutes

// Add smooth scrolling for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add animation to cards
    const cards = document.querySelectorAll('.pwa-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

<style>
.pwa-card {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Performance level colors */
.performance-excellent {
    color: #059669;
}

.performance-good {
    color: #3b82f6;
}

.performance-average {
    color: #d97706;
}

.performance-poor {
    color: #dc2626;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .pwa-card h2 {
        font-size: 1.25rem;
    }
    
    .pwa-card h3 {
        font-size: 1.125rem;
    }
}
</style>
@endsection


