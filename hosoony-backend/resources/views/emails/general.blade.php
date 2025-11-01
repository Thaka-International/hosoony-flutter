<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .message {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $title }}</h1>
        </div>
        <div class="content">
            <p>مرحباً {{ $user->name }}،</p>
            <div class="message">
                {!! nl2br(e($message)) !!}
            </div>
            @if(isset($data['amount']))
                <p><strong>المبلغ:</strong> {{ $data['amount'] }} {{ $data['currency'] ?? 'SAR' }}</p>
            @endif
            @if(isset($data['expiry_date']))
                <p><strong>تاريخ الانتهاء:</strong> {{ $data['expiry_date'] }}</p>
            @endif
        </div>
        <div class="footer">
            <p>هذا إشعار تلقائي من نظام حصوني</p>
            <p>للاستفسارات، يرجى التواصل معنا</p>
        </div>
    </div>
</body>
</html>


