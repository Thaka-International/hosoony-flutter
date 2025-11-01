<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="application-name" content="حسوني">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="حسوني">
    <meta name="description" content="منصة تعليم القرآن الكريم للطلاب والمعلمين">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <meta name="msapplication-TileColor" content="#1e40af">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="theme-color" content="#1e40af">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/images/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/icons/icon-180x180.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/images/icons/icon-167x167.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icons/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/icons/icon-16x16.png">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/css/pwa.css">
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <title>@yield('title', 'حسوني - منصة القرآن الكريم')</title>
</head>
<body>
    <!-- Offline Indicator -->
    <div id="offline-indicator" class="pwa-offline" style="display: none;">
        أنت غير متصل بالإنترنت
    </div>

    <!-- Main Container -->
    <div class="pwa-container">
        <!-- Header -->
        <header class="pwa-header">
            <h1>@yield('header-title', 'حسوني')</h1>
            <p>@yield('header-subtitle', 'منصة تعليم القرآن الكريم')</p>
            
            @auth
                <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span>مرحباً، {{ auth()->user()->name }}</span>
                        <br>
                        <small>{{ auth()->user()->role === 'student' ? 'طالب' : (auth()->user()->role === 'teacher' ? 'معلم' : 'مشرف') }}</small>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="pwa-btn pwa-btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            @endauth
        </header>

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer style="text-align: center; padding: 2rem 0; color: #6b7280; font-size: 0.875rem;">
            <p>&copy; {{ date('Y') }} حسوني. جميع الحقوق محفوظة.</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // Offline Detection
        window.addEventListener('online', () => {
            document.getElementById('offline-indicator').style.display = 'none';
        });

        window.addEventListener('offline', () => {
            document.getElementById('offline-indicator').style.display = 'block';
        });

        // Check initial connection status
        if (!navigator.onLine) {
            document.getElementById('offline-indicator').style.display = 'block';
        }

        // Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show install button
            const installBtn = document.createElement('button');
            installBtn.textContent = 'تثبيت التطبيق';
            installBtn.className = 'pwa-btn';
            installBtn.style.marginTop = '1rem';
            installBtn.onclick = () => {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    }
                    deferredPrompt = null;
                });
            };
            
            document.querySelector('.pwa-header').appendChild(installBtn);
        });
    </script>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>


