<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير يومي - {{ $student_name }}</title>
    <style>
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .header h2 {
            color: #6c757d;
            margin: 10px 0 0 0;
            font-size: 18px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-right: 4px solid #007bff;
        }
        .info-item h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        .info-item p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .summary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .summary h3 {
            margin: 0 0 15px 0;
            font-size: 20px;
        }
        .summary .stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .stat {
            text-align: center;
            margin: 10px;
        }
        .stat .number {
            font-size: 24px;
            font-weight: bold;
            display: block;
        }
        .stat .label {
            font-size: 14px;
            opacity: 0.9;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        @media print {
            body {
                background: white;
            }
            .container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>تقرير يومي</h1>
            <h2>نظام حصوني القرآني</h2>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <h3>اسم الطالب</h3>
                <p>{{ $student_name }}</p>
            </div>
            <div class="info-item">
                <h3>الفصل</h3>
                <p>{{ $class_name }}</p>
            </div>
            <div class="info-item">
                <h3>التاريخ</h3>
                <p>{{ $date }}</p>
            </div>
            <div class="info-item">
                <h3>المدة الإجمالية</h3>
                <p>{{ $duration_minutes }} دقيقة</p>
            </div>
        </div>

        <div class="summary">
            <h3>ملخص الأداء اليومي</h3>
            <div class="stats">
                <div class="stat">
                    <span class="number">{{ $tasks_completed }}</span>
                    <span class="label">مهمة مكتملة</span>
                </div>
                <div class="stat">
                    <span class="number">{{ $total_points }}</span>
                    <span class="label">نقطة مكتسبة</span>
                </div>
                <div class="stat">
                    <span class="number">{{ $duration_minutes }}</span>
                    <span class="label">دقيقة دراسة</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>تم إنشاء هذا التقرير تلقائياً من نظام حصوني القرآني</p>
            <p>تاريخ الإنشاء: {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>


