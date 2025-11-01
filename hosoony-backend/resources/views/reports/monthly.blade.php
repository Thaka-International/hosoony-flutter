<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقرير الشهري - {{ $class['name'] }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', 'Cairo', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            line-height: 1.6;
            color: #333;
            background: #fff;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header h2 {
            font-size: 20px;
            font-weight: 400;
            opacity: 0.9;
        }
        
        .month-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-right: 5px solid #667eea;
        }
        
        .month-info h3 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .month-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .month-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .month-item strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        
        .summary {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-right: 5px solid #28a745;
        }
        
        .summary h3 {
            color: #28a745;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .summary-item .number {
            font-size: 24px;
            font-weight: 700;
            color: #28a745;
            display: block;
        }
        
        .summary-item .label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .grades-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            font-weight: 600;
            text-align: center;
        }
        
        .grades-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        
        .grades-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .grades-table tr:hover {
            background: #e3f2fd;
        }
        
        .student-name {
            font-weight: 600;
            color: #333;
            text-align: right;
        }
        
        .rank {
            font-weight: 700;
            color: #667eea;
        }
        
        .points {
            font-weight: 600;
            color: #28a745;
        }
        
        .attendance-percentage {
            font-weight: 600;
        }
        
        .attendance-percentage.high {
            color: #28a745;
        }
        
        .attendance-percentage.medium {
            color: #ffc107;
        }
        
        .attendance-percentage.low {
            color: #dc3545;
        }
        
        .grade {
            font-weight: 700;
            font-size: 16px;
            padding: 5px 10px;
            border-radius: 15px;
        }
        
        .grade.excellent {
            background: #d4edda;
            color: #155724;
        }
        
        .grade.very-good {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .grade.good {
            background: #fff3cd;
            color: #856404;
        }
        
        .grade.acceptable {
            background: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            margin-top: 40px;
            padding: 20px;
            text-align: center;
            color: #666;
            border-top: 2px solid #eee;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .legend {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .legend h4 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .legend-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
        }
        
        @media print {
            body {
                font-size: 12px;
            }
            
            .header {
                background: #667eea !important;
                -webkit-print-color-adjust: exact;
            }
            
            .grades-table {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>التقرير الشهري</h1>
        <h2>{{ $class['name'] }}</h2>
    </div>

    <div class="month-info">
        <h3>معلومات الشهر</h3>
        <div class="month-grid">
            <div class="month-item">
                <strong>الشهر الميلادي</strong>
                {{ $month_name }}
            </div>
            @if($hijri_month_name)
            <div class="month-item">
                <strong>الشهر الهجري</strong>
                {{ $hijri_month_name }}
            </div>
            @endif
            <div class="month-item">
                <strong>إجمالي الأيام</strong>
                {{ $summary['total_students'] > 0 ? $students[0]['total_days'] : 0 }} يوم
            </div>
        </div>
    </div>

    <div class="summary">
        <h3>ملخص الأداء الشهري</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="number">{{ $summary['total_students'] }}</span>
                <span class="label">إجمالي الطلاب</span>
            </div>
            <div class="summary-item">
                <span class="number">{{ $summary['average_attendance'] }}%</span>
                <span class="label">متوسط الحضور</span>
            </div>
            <div class="summary-item">
                <span class="number">{{ $summary['average_points'] }}</span>
                <span class="label">متوسط النقاط</span>
            </div>
        </div>
    </div>

    <div class="legend">
        <h4>مفتاح الدرجات</h4>
        <div class="legend-items">
            <div class="legend-item">
                <div class="legend-color" style="background: #d4edda;"></div>
                <span>ممتاز (90-100%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #d1ecf1;"></div>
                <span>جيد جداً (80-89%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #fff3cd;"></div>
                <span>جيد (70-79%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #f8d7da;"></div>
                <span>مقبول (60-69%)</span>
            </div>
        </div>
    </div>

    <table class="grades-table">
        <thead>
            <tr>
                <th>الترتيب</th>
                <th>اسم الطالب</th>
                <th>إجمالي النقاط</th>
                <th>أيام الحضور</th>
                <th>نسبة الحضور</th>
                <th>الدرجة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $studentData)
            @php
                $attendancePercentage = $studentData['attendance_percentage'];
                $grade = '';
                $gradeClass = '';
                
                if ($attendancePercentage >= 90) {
                    $grade = 'ممتاز';
                    $gradeClass = 'excellent';
                } elseif ($attendancePercentage >= 80) {
                    $grade = 'جيد جداً';
                    $gradeClass = 'very-good';
                } elseif ($attendancePercentage >= 70) {
                    $grade = 'جيد';
                    $gradeClass = 'good';
                } else {
                    $grade = 'مقبول';
                    $gradeClass = 'acceptable';
                }
            @endphp
            <tr>
                <td class="rank">{{ $studentData['rank'] ?? ($index + 1) }}</td>
                <td class="student-name">{{ $studentData['student']['name'] }}</td>
                <td class="points">{{ $studentData['total_points'] }}</td>
                <td>{{ $studentData['attendance_days'] }}</td>
                <td class="attendance-percentage {{ $attendancePercentage >= 80 ? 'high' : ($attendancePercentage >= 60 ? 'medium' : 'low') }}">
                    {{ $attendancePercentage }}%
                </td>
                <td>
                    <span class="grade {{ $gradeClass }}">
                        {{ $grade }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>نظام حصوني - إدارة التعلم القرآني</strong></p>
        <p>تم إنشاء التقرير في: {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>الصفحة 1 من 1</p>
    </div>
</body>
</html>


