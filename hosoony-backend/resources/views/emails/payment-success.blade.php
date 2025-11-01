<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم الدفع بنجاح</title>
    <style>
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #48cae4 0%, #023e8a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .success-box {
            background-color: #d4edda;
            border: 2px solid #48cae4;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .success-icon {
            font-size: 48px;
            color: #48cae4;
            margin-bottom: 10px;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #155724;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>تم الدفع بنجاح</h1>
        </div>
        <div class="content">
            <p>مرحباً {{ $user->name }}،</p>
            <p>نود إعلامك بأنه تم استلام دفعتك بنجاح.</p>
            
            <div class="success-box">
                <div class="success-icon">✓</div>
                <p>تم تأكيد الدفع</p>
                <div class="amount">{{ $data['amount'] ?? '0' }} {{ $data['currency'] ?? 'SAR' }}</div>
            </div>
            
            <p>شكراً لك على ثقتك في نظام حصوني.</p>
            <p>يمكنك الآن الاستمتاع بجميع خدمات النظام.</p>
        </div>
        <div class="footer">
            <p>هذا تأكيد تلقائي من نظام حصوني</p>
            <p>للاستفسارات، يرجى التواصل معنا</p>
        </div>
    </div>
</body>
</html>


