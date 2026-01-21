<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-50 min-h-screen flex items-center justify-center">
    <div class="text-center px-4">
        <h1 class="text-9xl font-bold text-neutral-200">500</h1>
        <h2 class="text-2xl font-semibold text-neutral-800 mt-4">Server Error</h2>
        <p class="text-neutral-600 mt-2 mb-8">Something went wrong on our end. Please try again later.</p>
        <a href="/" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-6 py-3 rounded-lg inline-block transition">
            Go Home
        </a>
    </div>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 600: '#0284c7', 700: '#0369a1' }
                    }
                }
            }
        }
    </script>
</body>
</html>
