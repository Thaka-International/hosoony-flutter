<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - حسوني</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            font-weight: bold;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            font-family: 'Tajawal', sans-serif;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s;
            font-family: 'Tajawal', sans-serif;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: #6c757d;
            margin-top: 10px;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .back-btn {
            background: none;
            border: none;
            color: #667eea;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 20px;
            font-family: 'Tajawal', sans-serif;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        .whatsapp-icon {
            color: #25D366;
            font-size: 20px;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">ح</div>
        <h1>حسوني</h1>
        <p class="subtitle">منصة تعلم القرآن الكريم</p>

        <!-- Step 1: Phone Number -->
        <div id="step1" class="step active">
            <form id="phoneForm">
                <div class="form-group">
                    <label for="phone">رقم الجوال</label>
                    <input type="tel" id="phone" name="phone" placeholder="05xxxxxxxx" required>
                </div>
                <button type="submit" class="btn" id="sendCodeBtn">
                    إرسال رمز التحقق <span class="whatsapp-icon">📱</span>
                </button>
            </form>
        </div>

        <!-- Step 2: Verification Code -->
        <div id="step2" class="step">
            <button class="back-btn" onclick="goBack()">← العودة</button>
            <form id="codeForm">
                <div class="form-group">
                    <label for="code">رمز التحقق</label>
                    <input type="text" id="code" name="code" placeholder="123456" maxlength="6" required>
                </div>
                <button type="submit" class="btn" id="verifyCodeBtn">
                    تأكيد الرمز
                </button>
                <button type="button" class="btn btn-secondary" id="resendBtn">
                    إعادة إرسال الرمز
                </button>
            </form>
        </div>

        <div id="message"></div>
    </div>

    <script>
        let currentStep = 1;
        let phoneNumber = '';

        // Phone form submission
        document.getElementById('phoneForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const phone = document.getElementById('phone').value;
            const sendCodeBtn = document.getElementById('sendCodeBtn');
            
            sendCodeBtn.disabled = true;
            sendCodeBtn.innerHTML = '<div class="loading"></div> جاري الإرسال...';
            
            try {
                const response = await fetch('/phone-auth/send-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ phone: phone })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    phoneNumber = data.phone;
                    showMessage('تم إرسال رمز التحقق إلى واتساب الخاص بك', 'success');
                    showStep(2);
                } else {
                    showMessage(data.message || 'حدث خطأ في الإرسال', 'error');
                }
            } catch (error) {
                showMessage('حدث خطأ في الاتصال', 'error');
            } finally {
                sendCodeBtn.disabled = false;
                sendCodeBtn.innerHTML = 'إرسال رمز التحقق <span class="whatsapp-icon">📱</span>';
            }
        });

        // Code form submission
        document.getElementById('codeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const code = document.getElementById('code').value;
            const verifyCodeBtn = document.getElementById('verifyCodeBtn');
            
            verifyCodeBtn.disabled = true;
            verifyCodeBtn.innerHTML = '<div class="loading"></div> جاري التحقق...';
            
            try {
                const response = await fetch('/phone-auth/verify-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ 
                        phone: phoneNumber,
                        code: code 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('تم تسجيل الدخول بنجاح!', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || '/student/dashboard';
                    }, 1500);
                } else {
                    showMessage(data.message || 'رمز التحقق غير صحيح', 'error');
                }
            } catch (error) {
                showMessage('حدث خطأ في الاتصال', 'error');
            } finally {
                verifyCodeBtn.disabled = false;
                verifyCodeBtn.innerHTML = 'تأكيد الرمز';
            }
        });

        // Resend code
        document.getElementById('resendBtn').addEventListener('click', async function() {
            const resendBtn = document.getElementById('resendBtn');
            
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<div class="loading"></div> جاري الإرسال...';
            
            try {
                const response = await fetch('/phone-auth/resend-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ phone: phoneNumber })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('تم إعادة إرسال رمز التحقق', 'success');
                } else {
                    showMessage(data.message || 'فشل في إعادة الإرسال', 'error');
                }
            } catch (error) {
                showMessage('حدث خطأ في الاتصال', 'error');
            } finally {
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'إعادة إرسال الرمز';
            }
        });

        function showStep(step) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById(`step${step}`).classList.add('active');
            currentStep = step;
        }

        function goBack() {
            showStep(1);
            document.getElementById('phone').value = '';
            document.getElementById('code').value = '';
            clearMessage();
        }

        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `<div class="message ${type}">${text}</div>`;
            messageDiv.scrollIntoView({ behavior: 'smooth' });
        }

        function clearMessage() {
            document.getElementById('message').innerHTML = '';
        }

        // Auto-format phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('0')) {
                value = '0' + value;
            }
            e.target.value = value;
        });

        // Auto-submit code when 6 digits entered
        document.getElementById('code').addEventListener('input', function(e) {
            if (e.target.value.length === 6) {
                document.getElementById('codeForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>


