@extends('layouts.pwa')

@section('title', 'تقييم الأداء - حسوني')
@section('header-title', 'تقييم أداء الطالبات')
@section('header-subtitle', 'جلسة: ' . $session->title)

@section('content')
<div class="pwa-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>تقييم أداء الطالبات</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button id="save-all-btn" class="pwa-btn pwa-btn-primary" style="display: none;">
                حفظ جميع التقييمات
            </button>
            <button id="bulk-evaluate-btn" class="pwa-btn pwa-btn-secondary">
                تقييم جماعي
            </button>
        </div>
    </div>
    
    <div style="background: #f0f9ff; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <strong>الفصل:</strong> {{ $session->class->name }}
            </div>
            <div>
                <strong>التاريخ:</strong> {{ $session->date->format('Y/m/d') }}
            </div>
            <div>
                <strong>الوقت:</strong> {{ $session->start_time }} - {{ $session->end_time }}
            </div>
            <div>
                <strong>عدد الطالبات:</strong> {{ $students->count() }}
            </div>
        </div>
    </div>
</div>

<!-- Students List -->
<div id="students-list">
    @foreach($students as $student)
        @php
            $existingEvaluation = $existingEvaluations->get($student->id);
        @endphp
        <div class="pwa-card student-evaluation-card" data-student-id="{{ $student->id }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 50px; height: 50px; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 style="margin: 0;">{{ $student->name }}</h3>
                        <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">
                            {{ $student->email }}
                        </p>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="evaluate-btn pwa-btn pwa-btn-primary" data-student-id="{{ $student->id }}">
                        {{ $existingEvaluation ? 'تعديل التقييم' : 'تقييم' }}
                    </button>
                    @if($existingEvaluation)
                        <span class="evaluation-status" style="padding: 0.25rem 0.5rem; background: #dcfce7; color: #166534; border-radius: 0.25rem; font-size: 0.75rem;">
                            تم التقييم ({{ number_format($existingEvaluation->total_score, 1) }})
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Evaluation Form (Hidden by default) -->
            <div class="evaluation-form" style="display: none;">
                <form class="evaluation-form-data">
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <!-- التلاوة -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">التلاوة</label>
                            <input type="number" name="recitation_score" 
                                   value="{{ $existingEvaluation?->recitation_score ?? '' }}"
                                   min="0" max="10" step="0.1" 
                                   style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                        </div>
                        
                        <!-- النطق -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">النطق</label>
                            <input type="number" name="pronunciation_score" 
                                   value="{{ $existingEvaluation?->pronunciation_score ?? '' }}"
                                   min="0" max="10" step="0.1" 
                                   style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                        </div>
                        
                        <!-- الحفظ -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الحفظ</label>
                            <input type="number" name="memorization_score" 
                                   value="{{ $existingEvaluation?->memorization_score ?? '' }}"
                                   min="0" max="10" step="0.1" 
                                   style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                        </div>
                        
                        <!-- الفهم -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الفهم</label>
                            <input type="number" name="understanding_score" 
                                   value="{{ $existingEvaluation?->understanding_score ?? '' }}"
                                   min="0" max="10" step="0.1" 
                                   style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                        </div>
                        
                        <!-- المشاركة -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">المشاركة</label>
                            <input type="number" name="participation_score" 
                                   value="{{ $existingEvaluation?->participation_score ?? '' }}"
                                   min="0" max="10" step="0.1" 
                                   style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                        </div>
                    </div>
                    
                    <!-- التوصيات -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">التوصيات للمعلمة</label>
                        <textarea name="recommendations" rows="3" 
                                  style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; resize: vertical;">{{ $existingEvaluation?->recommendations ?? '' }}</textarea>
                    </div>
                    
                    <!-- ملاحظات للطالبة -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">ملاحظات للطالبة</label>
                        <textarea name="student_feedback" rows="3" 
                                  style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; resize: vertical;">{{ $existingEvaluation?->student_feedback ?? '' }}</textarea>
                    </div>
                    
                    <!-- مجالات التحسين -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">مجالات التحسين</label>
                        <textarea name="improvement_areas" rows="3" 
                                  style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; resize: vertical;">{{ $existingEvaluation?->improvement_areas ?? '' }}</textarea>
                    </div>
                    
                    <!-- Total Score Display -->
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">الدرجة الإجمالية:</span>
                            <span id="total-score-{{ $student->id }}" style="font-size: 1.25rem; font-weight: bold; color: #059669;">
                                {{ $existingEvaluation ? number_format($existingEvaluation->total_score, 1) : '0.0' }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <button type="button" class="cancel-evaluation-btn pwa-btn pwa-btn-secondary">
                            إلغاء
                        </button>
                        <button type="submit" class="save-evaluation-btn pwa-btn pwa-btn-primary">
                            حفظ التقييم
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>

<!-- Bulk Evaluation Modal -->
<div id="bulk-evaluation-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 0.5rem; padding: 2rem; max-width: 90%; max-height: 90%; overflow-y: auto;">
        <h2 style="margin-bottom: 1rem;">التقييم الجماعي</h2>
        <p style="margin-bottom: 1rem; color: #6b7280;">يمكنك تقييم جميع الطالبات في نموذج واحد</p>
        
        <form id="bulk-evaluation-form">
            <div style="display: grid; gap: 1rem; max-height: 400px; overflow-y: auto;">
                @foreach($students as $student)
                    <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem;">
                        <h4 style="margin: 0 0 1rem 0;">{{ $student->name }}</h4>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.5rem;">
                            <div>
                                <label style="font-size: 0.75rem;">التلاوة</label>
                                <input type="number" name="evaluations[{{ $student->id }}][recitation_score]" 
                                       min="0" max="10" step="0.1" 
                                       style="width: 100%; padding: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem;">النطق</label>
                                <input type="number" name="evaluations[{{ $student->id }}][pronunciation_score]" 
                                       min="0" max="10" step="0.1" 
                                       style="width: 100%; padding: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem;">الحفظ</label>
                                <input type="number" name="evaluations[{{ $student->id }}][memorization_score]" 
                                       min="0" max="10" step="0.1" 
                                       style="width: 100%; padding: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem;">الفهم</label>
                                <input type="number" name="evaluations[{{ $student->id }}][understanding_score]" 
                                       min="0" max="10" step="0.1" 
                                       style="width: 100%; padding: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem;">المشاركة</label>
                                <input type="number" name="evaluations[{{ $student->id }}][participation_score]" 
                                       min="0" max="10" step="0.1" 
                                       style="width: 100%; padding: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                            </div>
                        </div>
                        
                        <input type="hidden" name="evaluations[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                    </div>
                @endforeach
            </div>
            
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" id="cancel-bulk-evaluation" class="pwa-btn pwa-btn-secondary">
                    إلغاء
                </button>
                <button type="submit" class="pwa-btn pwa-btn-primary">
                    حفظ جميع التقييمات
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Global variables
const sessionId = {{ $session->id }};
let openEvaluationForm = null;

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Individual evaluation buttons
    document.querySelectorAll('.evaluate-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const studentId = this.dataset.studentId;
            openStudentEvaluation(studentId);
        });
    });
    
    // Cancel evaluation buttons
    document.querySelectorAll('.cancel-evaluation-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            closeStudentEvaluation();
        });
    });
    
    // Save evaluation buttons
    document.querySelectorAll('.save-evaluation-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            saveEvaluation(form);
        });
    });
    
    // Bulk evaluation button
    document.getElementById('bulk-evaluate-btn').addEventListener('click', function() {
        document.getElementById('bulk-evaluation-modal').style.display = 'block';
    });
    
    // Cancel bulk evaluation
    document.getElementById('cancel-bulk-evaluation').addEventListener('click', function() {
        document.getElementById('bulk-evaluation-modal').style.display = 'none';
    });
    
    // Bulk evaluation form
    document.getElementById('bulk-evaluation-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveBulkEvaluations(this);
    });
    
    // Score calculation for individual forms
    document.querySelectorAll('.evaluation-form-data').forEach(form => {
        const inputs = form.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                calculateTotalScore(form);
            });
        });
    });
});

function openStudentEvaluation(studentId) {
    // Close any open form
    if (openEvaluationForm) {
        closeStudentEvaluation();
    }
    
    // Open the selected form
    const card = document.querySelector(`[data-student-id="${studentId}"]`);
    const form = card.querySelector('.evaluation-form');
    form.style.display = 'block';
    openEvaluationForm = studentId;
    
    // Scroll to the form
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function closeStudentEvaluation() {
    if (openEvaluationForm) {
        const card = document.querySelector(`[data-student-id="${openEvaluationForm}"]`);
        const form = card.querySelector('.evaluation-form');
        form.style.display = 'none';
        openEvaluationForm = null;
    }
}

function calculateTotalScore(form) {
    const inputs = form.querySelectorAll('input[type="number"]');
    let total = 0;
    let count = 0;
    
    inputs.forEach(input => {
        const value = parseFloat(input.value);
        if (!isNaN(value)) {
            total += value;
            count++;
        }
    });
    
    const average = count > 0 ? total / count : 0;
    const studentId = form.querySelector('input[name="student_id"]').value;
    const totalScoreElement = document.getElementById(`total-score-${studentId}`);
    
    if (totalScoreElement) {
        totalScoreElement.textContent = average.toFixed(1);
        totalScoreElement.style.color = average >= 8 ? '#059669' : average >= 6 ? '#d97706' : '#dc2626';
    }
}

function saveEvaluation(form) {
    const formData = new FormData(form);
    const studentId = formData.get('student_id');
    
    // Validate scores
    const scores = ['recitation_score', 'pronunciation_score', 'memorization_score', 'understanding_score', 'participation_score'];
    let isValid = true;
    
    scores.forEach(score => {
        const value = parseFloat(formData.get(score));
        if (isNaN(value) || value < 0 || value > 10) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        alert('يرجى إدخال درجات صحيحة من 0 إلى 10');
        return;
    }
    
    // Send request
    fetch(`/api/sessions/${sessionId}/evaluations`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم حفظ التقييم بنجاح');
            location.reload(); // Refresh to show updated status
        } else {
            alert('فشل في حفظ التقييم: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء حفظ التقييم');
    });
}

function saveBulkEvaluations(form) {
    const formData = new FormData(form);
    const evaluations = {};
    
    // Process form data
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('evaluations[')) {
            const match = key.match(/evaluations\[(\d+)\]\[(.+)\]/);
            if (match) {
                const studentId = match[1];
                const field = match[2];
                
                if (!evaluations[studentId]) {
                    evaluations[studentId] = {};
                }
                
                evaluations[studentId][field] = value;
            }
        }
    }
    
    // Validate all evaluations
    let isValid = true;
    Object.values(evaluations).forEach(evaluation => {
        const scores = ['recitation_score', 'pronunciation_score', 'memorization_score', 'understanding_score', 'participation_score'];
        scores.forEach(score => {
            const value = parseFloat(evaluation[score]);
            if (isNaN(value) || value < 0 || value > 10) {
                isValid = false;
            }
        });
    });
    
    if (!isValid) {
        alert('يرجى إدخال درجات صحيحة من 0 إلى 10 لجميع الطالبات');
        return;
    }
    
    // Send request
    fetch(`/api/sessions/${sessionId}/evaluations/bulk`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ evaluations: Object.values(evaluations) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`تم حفظ ${data.evaluations_count} تقييم بنجاح`);
            document.getElementById('bulk-evaluation-modal').style.display = 'none';
            location.reload();
        } else {
            alert('فشل في حفظ التقييمات: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء حفظ التقييمات');
    });
}
</script>

<style>
.student-evaluation-card {
    transition: all 0.2s ease;
}

.student-evaluation-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.evaluation-form {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
    margin-top: 1rem;
}

.evaluation-status {
    font-size: 0.75rem;
    font-weight: 600;
}

#bulk-evaluation-modal {
    backdrop-filter: blur(4px);
}

#bulk-evaluation-modal > div {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
</style>
@endsection


