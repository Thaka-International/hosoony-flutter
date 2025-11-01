<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير اليوم - {{ $class->name }}</title>
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
        
        .date-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-right: 5px solid #667eea;
        }
        
        .date-info h3 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .date-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .date-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .date-item strong {
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
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .students-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            font-weight: 600;
            text-align: center;
        }
        
        .students-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        
        .students-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .students-table tr:hover {
            background: #e3f2fd;
        }
        
        .student-name {
            font-weight: 600;
            color: #333;
            text-align: right;
        }
        
        .finish-order {
            font-weight: 700;
            color: #667eea;
        }
        
        .completion-rate {
            font-weight: 600;
        }
        
        .completion-rate.high {
            color: #28a745;
        }
        
        .completion-rate.medium {
            color: #ffc107;
        }
        
        .completion-rate.low {
            color: #dc3545;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status.verified {
            background: #d4edda;
            color: #155724;
        }
        
        .status.submitted {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status.not_submitted {
            background: #f8d7da;
            color: #721c24;
        }
        
        .notes {
            max-width: 200px;
            font-size: 12px;
            color: #666;
            text-align: right;
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
        
        @media print {
            body {
                font-size: 12px;
            }
            
            .header {
                background: #667eea !important;
                -webkit-print-color-adjust: exact;
            }
            
            .students-table {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>تقرير اليوم</h1>
        <h2>{{ $class->name }}</h2>
    </div>

    <div class="date-info">
        <h3>معلومات التاريخ</h3>
        <div class="date-grid">
            <div class="date-item">
                <strong>التاريخ الميلادي</strong>
                {{ $date->format('Y-m-d') }}
            </div>
            <div class="date-item">
                <strong>التاريخ الهجري</strong>
                {{ $hijri_date }}
            </div>
            <div class="date-item">
                <strong>اليوم</strong>
                {{ $date->locale('ar')->dayName }}
            </div>
        </div>
    </div>

    <div class="summary">
        <h3>ملخص الأداء</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="number">{{ $summary['total_students'] }}</span>
                <span class="label">إجمالي الطلاب</span>
            </div>
            <div class="summary-item">
                <span class="number">{{ $summary['completed_students'] }}</span>
                <span class="label">الطلاب المكملين</span>
            </div>
            <div class="summary-item">
                <span class="number">{{ $summary['completion_rate'] }}%</span>
                <span class="label">نسبة الإكمال</span>
            </div>
        </div>
    </div>

    <table class="students-table">
        <thead>
            <tr>
                <th>اسم الطالب</th>
                <th>ترتيب الإنجاز</th>
                <th>المهام المكتملة</th>
                <th>إجمالي المهام</th>
                <th>نسبة الإكمال</th>
                <th>الحالة</th>
                <th>ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $studentData)
            <tr>
                <td class="student-name">{{ $studentData['student']->name }}</td>
                <td class="finish-order">
                    @if($studentData['finish_order'])
                        {{ $studentData['finish_order'] }}
                    @else
                        <span style="color: #999;">لم يرسل</span>
                    @endif
                </td>
                <td>{{ $studentData['completed_tasks'] }}</td>
                <td>{{ $studentData['total_tasks'] }}</td>
                <td class="completion-rate {{ $studentData['completion_rate'] >= 80 ? 'high' : ($studentData['completion_rate'] >= 50 ? 'medium' : 'low') }}">
                    {{ $studentData['completion_rate'] }}%
                </td>
                <td>
                    <span class="status {{ $studentData['status'] }}">
                        @switch($studentData['status'])
                            @case('verified')
                                محقق
                                @break
                            @case('submitted')
                                مرسل
                                @break
                            @case('not_submitted')
                                لم يرسل
                                @break
                            @default
                                {{ $studentData['status'] }}
                        @endswitch
                    </span>
                </td>
                <td class="notes">
                    @if(count($studentData['notes']) > 0)
                        {{ implode('; ', array_slice($studentData['notes'], 0, 2)) }}
                        @if(count($studentData['notes']) > 2)
                            ...
                        @endif
                    @else
                        <span style="color: #999;">لا توجد ملاحظات</span>
                    @endif
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


