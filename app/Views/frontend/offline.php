<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - <?= e(setting('site_name', 'FWP Gallery')) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .offline-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .offline-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .offline-icon svg {
            width: 40px;
            height: 40px;
            color: #6b7280;
        }
        h1 {
            font-size: 24px;
            color: #1f2937;
            margin-bottom: 12px;
        }
        p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #6366f1;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #4f46e5;
        }
        .cached-pages {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }
        .cached-pages h3 {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cached-list {
            list-style: none;
        }
        .cached-list li {
            margin: 8px 0;
        }
        .cached-list a {
            color: #6366f1;
            text-decoration: none;
        }
        .cached-list a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3"/>
            </svg>
        </div>

        <h1>You're Offline</h1>
        <p>It looks like you've lost your internet connection. Some content may still be available from cache.</p>

        <button class="btn" onclick="window.location.reload()">
            Try Again
        </button>

        <div class="cached-pages" id="cachedPages" style="display: none;">
            <h3>Available Offline</h3>
            <ul class="cached-list" id="cachedList"></ul>
        </div>
    </div>

    <script>
        // Show cached pages if available
        if ('caches' in window) {
            caches.open('fwp-dynamic-v1.0.0').then(cache => {
                cache.keys().then(keys => {
                    const htmlPages = keys.filter(req =>
                        req.url.includes(location.origin) &&
                        !req.url.includes('/api/') &&
                        !req.url.includes('/assets/')
                    );

                    if (htmlPages.length > 0) {
                        document.getElementById('cachedPages').style.display = 'block';
                        const list = document.getElementById('cachedList');

                        htmlPages.slice(0, 5).forEach(req => {
                            const url = new URL(req.url);
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.href = url.pathname;
                            a.textContent = url.pathname === '/' ? 'Home' : url.pathname.replace(/\//g, ' ').trim();
                            li.appendChild(a);
                            list.appendChild(li);
                        });
                    }
                });
            });
        }

        // Auto-retry when back online
        window.addEventListener('online', () => {
            window.location.reload();
        });
    </script>
</body>
</html>
