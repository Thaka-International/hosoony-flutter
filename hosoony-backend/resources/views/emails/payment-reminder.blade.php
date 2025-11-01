<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تذكير بالدفع</title>
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
        .amount-box {
            background-color: #f8f9fa;
            border: 2px solid #ff6b6b;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #ff6b6b;
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
            <h1>تذكير بالدفع</h1>
        </div>
        <div class="content">
            <p>مرحباً {{ $user->name }}،</p>
            <p>نود تذكيرك بأنه يجب دفع رسوم الاشتراك في أقرب وقت ممكن.</p>
            
            <div class="amount-box">
                <p>المبلغ المطلوب:</p>
                <div class="amount">{{ $data['amount'] ?? '0' }} {{ $data['currency'] ?? 'SAR' }}</div>
            </div>
            
            <p>يرجى مراجعة حسابك وتجديد الاشتراك لضمان استمرارية الخدمة.</p>
            <p>شكراً لك على ثقتك في نظام حصوني.</p>
        </div>
        <div class="footer">
            <p>هذا تذكير تلقائي من نظام حصوني</p>
            <p>للاستفسارات، يرجى التواصل معنا</p>
        </div>
    </div>
</body>
</html>


