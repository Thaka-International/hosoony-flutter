<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتهاء الاشتراك</title>
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
            background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
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
        .expiry-box {
            background-color: #fff3cd;
            border: 2px solid #feca57;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .expiry-date {
            font-size: 24px;
            font-weight: bold;
            color: #856404;
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
            <h1>انتهاء الاشتراك</h1>
        </div>
        <div class="content">
            <p>مرحباً {{ $user->name }}،</p>
            <p>نود إعلامك بأن اشتراكك في نظام حصوني سينتهي قريباً.</p>
            
            <div class="expiry-box">
                <p>تاريخ انتهاء الاشتراك:</p>
                <div class="expiry-date">{{ $data['expiry_date'] ?? 'غير محدد' }}</div>
            </div>
            
            <p>يرجى تجديد الاشتراك قبل انتهاء المدة لضمان استمرارية الخدمة.</p>
            <p>يمكنك تجديد الاشتراك من خلال التواصل معنا أو زيارة الموقع.</p>
        </div>
        <div class="footer">
            <p>هذا إشعار تلقائي من نظام حصوني</p>
            <p>للاستفسارات، يرجى التواصل معنا</p>
        </div>
    </div>
</body>
</html>


