<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعار النظام</title>
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
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
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
        .system-box {
            background-color: #e3f2fd;
            border: 2px solid #6c5ce7;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .system-icon {
            font-size: 48px;
            color: #6c5ce7;
            margin-bottom: 10px;
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
            <h1>إشعار النظام</h1>
        </div>
        <div class="content">
            <p>مرحباً {{ $user->name }}،</p>
            <p>لديك إشعار جديد من نظام حصوني.</p>
            
            <div class="system-box">
                <div class="system-icon">⚙️</div>
                <p>{{ $title }}</p>
            </div>
            
            <div class="message">
                {!! nl2br(e($message)) !!}
            </div>
        </div>
        <div class="footer">
            <p>هذا إشعار تلقائي من نظام حصوني</p>
            <p>للاستفسارات، يرجى التواصل معنا</p>
        </div>
    </div>
</body>
</html>


