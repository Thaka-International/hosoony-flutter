<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فشل الدفع</title>
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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
        .error-box {
            background-color: #f8d7da;
            border: 2px solid #ff6b6b;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .error-icon {
            font-size: 48px;
            color: #ff6b6b;
            margin-bottom: 10px;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #721c24;
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
            <h1>فشل الدفع</h1>
        </div>
        <div class="content">
            <p>مرحباً {{ $user->name }}،</p>
            <p>نود إعلامك بأنه فشل في معالجة دفعتك.</p>
            
            <div class="error-box">
                <div class="error-icon">✗</div>
                <p>فشل في معالجة الدفع</p>
                <div class="amount">{{ $data['amount'] ?? '0' }} {{ $data['currency'] ?? 'SAR' }}</div>
            </div>
            
            <p>يرجى المحاولة مرة أخرى أو التواصل معنا لحل المشكلة.</p>
            <p>إذا استمرت المشكلة، يرجى التأكد من صحة بيانات الدفع.</p>
        </div>
        <div class="footer">
            <p>هذا إشعار تلقائي من نظام حصوني</p>
            <p>للاستفسارات، يرجى التواصل معنا</p>
        </div>
    </div>
</body>
</html>


