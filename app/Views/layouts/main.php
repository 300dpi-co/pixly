<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Home') ?><?= config('seo.title_separator') ?><?= e(setting('site_name', config('app.name'))) ?></title>
    <meta name="description" content="<?= e($meta_description ?? config('seo.site_description')) ?>">

    <!-- Tracking Codes (GA, GTM, FB Pixel, Custom) -->
    <?= head_tracking_codes() ?>

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($title ?? setting('site_name', config('app.name'))) ?>">
    <meta property="og:description" content="<?= e($meta_description ?? config('seo.site_description')) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($view->url()) ?>">
    <?php if (isset($og_image)): ?>
    <meta property="og:image" content="<?= e($og_image) ?>">
    <?php endif; ?>

    <!-- Custom Theme Colors -->
    <?php
    $primaryColor = setting('primary_color', '#0284c7');
    $secondaryColor = setting('secondary_color', '#6366f1');
    $accentColor = setting('accent_color', '#f59e0b');

    // Helper to generate color shades from a base color
    function hexToHsl($hex) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r: $h = (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6; break;
                case $g: $h = (($b - $r) / $d + 2) / 6; break;
                case $b: $h = (($r - $g) / $d + 4) / 6; break;
            }
        }
        return [$h * 360, $s * 100, $l * 100];
    }

    function hslToHex($h, $s, $l) {
        $h /= 360; $s /= 100; $l /= 100;
        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = hueToRgb($p, $q, $h + 1/3);
            $g = hueToRgb($p, $q, $h);
            $b = hueToRgb($p, $q, $h - 1/3);
        }
        return sprintf('#%02x%02x%02x', round($r * 255), round($g * 255), round($b * 255));
    }

    function hueToRgb($p, $q, $t) {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    function generateShades($hex) {
        list($h, $s, $l) = hexToHsl($hex);
        return [
            50 => hslToHex($h, min($s, 30), 97),
            100 => hslToHex($h, min($s, 40), 93),
            200 => hslToHex($h, min($s, 50), 85),
            300 => hslToHex($h, min($s, 60), 72),
            400 => hslToHex($h, min($s, 70), 58),
            500 => hslToHex($h, $s, 50),
            600 => $hex,
            700 => hslToHex($h, $s, 38),
            800 => hslToHex($h, $s, 30),
            900 => hslToHex($h, $s, 22),
        ];
    }

    $primaryShades = generateShades($primaryColor);
    $secondaryShades = generateShades($secondaryColor);
    $accentShades = generateShades($accentColor);
    ?>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '<?= $primaryShades[50] ?>',
                            100: '<?= $primaryShades[100] ?>',
                            200: '<?= $primaryShades[200] ?>',
                            300: '<?= $primaryShades[300] ?>',
                            400: '<?= $primaryShades[400] ?>',
                            500: '<?= $primaryShades[500] ?>',
                            600: '<?= $primaryShades[600] ?>',
                            700: '<?= $primaryShades[700] ?>',
                            800: '<?= $primaryShades[800] ?>',
                            900: '<?= $primaryShades[900] ?>',
                        },
                        secondary: {
                            50: '<?= $secondaryShades[50] ?>',
                            100: '<?= $secondaryShades[100] ?>',
                            200: '<?= $secondaryShades[200] ?>',
                            300: '<?= $secondaryShades[300] ?>',
                            400: '<?= $secondaryShades[400] ?>',
                            500: '<?= $secondaryShades[500] ?>',
                            600: '<?= $secondaryShades[600] ?>',
                            700: '<?= $secondaryShades[700] ?>',
                            800: '<?= $secondaryShades[800] ?>',
                            900: '<?= $secondaryShades[900] ?>',
                        },
                        accent: {
                            50: '<?= $accentShades[50] ?>',
                            100: '<?= $accentShades[100] ?>',
                            200: '<?= $accentShades[200] ?>',
                            300: '<?= $accentShades[300] ?>',
                            400: '<?= $accentShades[400] ?>',
                            500: '<?= $accentShades[500] ?>',
                            600: '<?= $accentShades[600] ?>',
                            700: '<?= $accentShades[700] ?>',
                            800: '<?= $accentShades[800] ?>',
                            900: '<?= $accentShades[900] ?>',
                        },
                    },
                },
            },
            darkMode: 'class',
        }
    </script>

    <?php
    // Load layout preset early for dark mode decision
    $layoutPreset = setting('layout_preset', 'clean-minimal');
    ?>

    <!-- Dark Mode Script (before page render to prevent flash) -->
    <script>
        (function() {
            // Pexels theme is always light - skip dark mode
            var isPexelsTheme = <?= json_encode($layoutPreset === 'pexels-stock') ?>;
            if (isPexelsTheme) {
                document.documentElement.classList.remove('dark');
                return;
            }
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <?php
    // Load site mode settings
    $adultModeEnabled = (bool) setting('adult_mode_enabled', false);
    $ageGateEnabled = $adultModeEnabled && (bool) setting('age_gate_enabled', false);
    $nsfwBlurEnabled = $adultModeEnabled && (bool) setting('nsfw_blur_enabled', false);
    $quickExitEnabled = $adultModeEnabled && (bool) setting('quick_exit_enabled', false);
    $rightClickDisabled = $adultModeEnabled && (bool) setting('right_click_disabled', false);
    $privateBrowsingNotice = $adultModeEnabled && (bool) setting('private_browsing_notice', false);
    $disclaimerEnabled = $adultModeEnabled && (bool) setting('disclaimer_enabled', false);

    // Check if adult layout selected
    $adultLayouts = ['dark-cinematic', 'neon-nights', 'premium-luxury', 'minimal-dark'];
    $isAdultLayout = in_array($layoutPreset, $adultLayouts);
    ?>

    <!-- Custom styles -->
    <style>
        [x-cloak] { display: none !important; }

        /* ============================================
           THEME: <?= strtoupper($layoutPreset) ?>
           ============================================ */

        <?php if ($layoutPreset === 'magazine-grid'): ?>
        /* MAGAZINE GRID THEME - Editorial, elegant */
        :root {
            --theme-font-heading: 'Georgia', 'Times New Roman', serif;
            --theme-radius: 0;
        }
        h1, h2, h3, .font-serif { font-family: var(--theme-font-heading) !important; }

        /* Header */
        header {
            border-bottom: 1px solid #e5e5e5 !important;
            box-shadow: none !important;
        }
        .dark header { border-bottom-color: #333 !important; }

        /* Gallery - Masonry */
        .gallery-grid {
            display: block !important;
            column-count: 2 !important;
            column-gap: 1rem !important;
        }
        @media (min-width: 640px) { .gallery-grid { column-count: 3 !important; } }
        @media (min-width: 1024px) { .gallery-grid { column-count: 4 !important; } }
        @media (min-width: 1280px) { .gallery-grid { column-count: 5 !important; } }
        .gallery-grid > * {
            break-inside: avoid;
            margin-bottom: 1rem;
            display: inline-block;
            width: 100%;
        }
        .gallery-grid .aspect-square,
        .gallery-grid [class*="aspect-"] {
            aspect-ratio: auto !important;
        }
        .gallery-grid img {
            height: auto !important;
            width: 100% !important;
        }

        <?php elseif ($layoutPreset === 'bold-modern'): ?>
        /* BOLD MODERN THEME - Dramatic, impactful */
        :root {
            --theme-radius: 1.5rem;
        }
        h1 { font-weight: 900 !important; letter-spacing: -0.03em; }
        h2 { font-weight: 800 !important; }

        /* Header */
        header {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
        }

        /* Gallery - Large cards */
        .gallery-grid {
            display: grid !important;
            grid-template-columns: repeat(1, 1fr) !important;
            gap: 1.5rem !important;
        }
        @media (min-width: 640px) { .gallery-grid { grid-template-columns: repeat(2, 1fr) !important; } }
        @media (min-width: 1024px) { .gallery-grid { grid-template-columns: repeat(3, 1fr) !important; } }
        .gallery-grid > a, .gallery-grid > div {
            border-radius: var(--theme-radius) !important;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .gallery-grid > a:hover, .gallery-grid > div:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        /* Rounded elements */
        .rounded-lg, .rounded-xl { border-radius: var(--theme-radius) !important; }

        <?php else: ?>
        /* CLEAN MINIMAL THEME (Default) */
        :root {
            --theme-radius: 0.5rem;
        }

        /* Gallery - Standard Grid */
        .gallery-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 1rem !important;
        }
        @media (min-width: 640px) { .gallery-grid { grid-template-columns: repeat(3, 1fr) !important; } }
        @media (min-width: 1024px) { .gallery-grid { grid-template-columns: repeat(4, 1fr) !important; } }
        @media (min-width: 1280px) { .gallery-grid { grid-template-columns: repeat(5, 1fr) !important; } }

        .gallery-grid a:hover img, .gallery-grid > div:hover img {
            transform: scale(1.05);
        }
        <?php endif; ?>

        <?php if ($nsfwBlurEnabled): ?>
        /* NSFW Blur Styles */
        .nsfw-blur {
            filter: blur(<?= (int)setting('nsfw_blur_strength', '20') ?>px);
            transition: filter 0.3s ease;
        }
        <?php if (setting('nsfw_reveal_on', 'click') === 'hover'): ?>
        .nsfw-blur:hover {
            filter: blur(0);
        }
        <?php else: ?>
        .nsfw-blur.revealed {
            filter: blur(0);
        }
        <?php endif; ?>
        .nsfw-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.3);
            color: white;
            font-size: 0.875rem;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        .nsfw-blur.revealed + .nsfw-overlay,
        <?php if (setting('nsfw_reveal_on', 'click') === 'hover'): ?>
        .nsfw-blur:hover + .nsfw-overlay,
        <?php endif; ?>
        .nsfw-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        <?php endif; ?>

        <?php if ($isAdultLayout): ?>
        /* Adult Layout Preset Styles */
        <?php if ($layoutPreset === 'dark-cinematic'): ?>
        /* DARK CINEMATIC - Professional Adult Theme */
        :root {
            --layout-bg: #0a0a0a;
            --layout-surface: #111111;
            --layout-surface-elevated: #1a1a1a;
            --layout-border: rgba(255,255,255,0.06);
            --layout-border-hover: rgba(255,255,255,0.12);
            --layout-text: #f5f5f5;
            --layout-muted: #888888;
            --accent-primary: #b91c1c;
            --accent-secondary: #dc2626;
            --accent-gold: #d4a574;
            --accent-gold-light: #e8c89e;
        }

        /* Base styling - Deep black with subtle texture */
        body {
            background: #0a0a0a !important;
            background-image:
                radial-gradient(ellipse at 20% 0%, rgba(185, 28, 28, 0.03) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(212, 165, 116, 0.02) 0%, transparent 50%) !important;
            background-attachment: fixed !important;
        }
        .dark body {
            background: #0a0a0a !important;
            background-image:
                radial-gradient(ellipse at 20% 0%, rgba(185, 28, 28, 0.03) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(212, 165, 116, 0.02) 0%, transparent 50%) !important;
        }

        /* Sleek header */
        .dark header, header {
            background: rgba(10, 10, 10, 0.95) !important;
            backdrop-filter: blur(20px) saturate(150%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(150%) !important;
            border-bottom: 1px solid rgba(212, 165, 116, 0.1) !important;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.03) !important;
        }

        /* Elegant navigation */
        header nav a {
            position: relative;
            font-weight: 400 !important;
            letter-spacing: 0.02em !important;
            transition: all 0.3s ease !important;
        }
        header nav a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 1px;
            background: var(--accent-gold);
            transition: width 0.3s ease;
        }
        header nav a:hover::after {
            width: 80%;
        }
        header nav a:hover {
            color: var(--accent-gold-light) !important;
        }

        /* Premium search input */
        header input[type="text"] {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            transition: all 0.3s ease !important;
        }
        header input[type="text"]:focus {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(212, 165, 116, 0.3) !important;
            box-shadow: 0 0 0 2px rgba(212, 165, 116, 0.1) !important;
        }

        /* Primary buttons - Deep crimson */
        .bg-primary-600, [class*="bg-primary-"] {
            background: linear-gradient(135deg, #991b1b 0%, #b91c1c 50%, #991b1b 100%) !important;
            border: 1px solid rgba(220, 38, 38, 0.3) !important;
            box-shadow: 0 2px 10px rgba(185, 28, 28, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
            transition: all 0.3s ease !important;
        }
        .bg-primary-600:hover, [class*="bg-primary-"]:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 50%, #b91c1c 100%) !important;
            box-shadow: 0 4px 20px rgba(185, 28, 28, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.15) !important;
            transform: translateY(-1px) !important;
        }

        /* Card styling - Subtle elegance */
        .dark .bg-white, .bg-white {
            background: rgba(17, 17, 17, 0.8) !important;
            backdrop-filter: blur(10px) !important;
            border: 1px solid var(--layout-border) !important;
            transition: all 0.3s ease !important;
        }
        .dark .bg-white:hover, .bg-white:hover {
            border-color: rgba(212, 165, 116, 0.15) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
        }

        /* Dark surface cards */
        .dark .bg-neutral-800, .bg-neutral-800 {
            background: rgba(17, 17, 17, 0.9) !important;
            border: 1px solid var(--layout-border) !important;
        }

        /* Image cards - Professional hover */
        .gallery-grid > a, .grid > a {
            position: relative;
            overflow: hidden;
            border-radius: 8px !important;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
        }
        .gallery-grid > a:hover, .grid > a:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(212, 165, 116, 0.1) !important;
        }
        .gallery-grid > a img, .grid > a img {
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
        }
        .gallery-grid > a:hover img, .grid > a:hover img {
            transform: scale(1.05) !important;
        }

        /* Badge styling - Gold accents */
        .bg-yellow-400 { background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-light)) !important; color: #1a1a1a !important; }
        .bg-neutral-300 { background: linear-gradient(135deg, #71717a, #a1a1aa) !important; }
        .bg-orange-400 { background: linear-gradient(135deg, #92400e, #b45309) !important; }

        /* Premium footer */
        .dark footer, footer {
            background: rgba(10, 10, 10, 0.98) !important;
            border-top: 1px solid rgba(212, 165, 116, 0.08) !important;
        }

        /* Social links hover */
        footer a[title] {
            transition: all 0.3s ease !important;
        }
        footer a[title]:hover {
            transform: translateY(-2px) !important;
            color: var(--accent-gold) !important;
        }

        /* Tag pills - Subtle styling */
        a[href*="/tag/"] {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            transition: all 0.3s ease !important;
        }
        a[href*="/tag/"]:hover {
            background: rgba(185, 28, 28, 0.1) !important;
            border-color: rgba(185, 28, 28, 0.3) !important;
            color: #fca5a5 !important;
        }

        /* Minimal scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0a0a0a; }
        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover { background: #444; }

        /* Accent text - Gold */
        .text-primary-600, [class*="text-primary-"] {
            color: var(--accent-gold) !important;
            -webkit-text-fill-color: var(--accent-gold) !important;
        }

        /* Pagination - Refined */
        nav[class*="pagination"] a, .flex.items-center.gap-2 a {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid var(--layout-border) !important;
            transition: all 0.3s ease !important;
        }
        nav[class*="pagination"] a:hover, .flex.items-center.gap-2 a:hover {
            background: rgba(185, 28, 28, 0.15) !important;
            border-color: rgba(185, 28, 28, 0.3) !important;
        }

        /* Form inputs - Premium feel */
        input, textarea, select {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid var(--layout-border) !important;
            transition: all 0.3s ease !important;
        }
        input:focus, textarea:focus, select:focus {
            border-color: rgba(212, 165, 116, 0.3) !important;
            box-shadow: 0 0 0 2px rgba(212, 165, 116, 0.08) !important;
            outline: none !important;
        }

        /* Modal overlays */
        [class*="fixed"][class*="inset-0"][class*="bg-black"] {
            backdrop-filter: blur(12px) !important;
            background: rgba(0, 0, 0, 0.85) !important;
        }

        /* Premium link styling */
        a[href*="/premium"] {
            color: var(--accent-gold) !important;
        }
        a[href*="/premium"]:hover {
            color: var(--accent-gold-light) !important;
        }

        /* Stats and metadata text */
        .text-neutral-400, .text-neutral-500 {
            color: #666 !important;
        }

        /* View all links */
        a.text-violet-400, a.text-primary-600 {
            color: var(--accent-gold) !important;
        }
        a.text-violet-400:hover, a.text-primary-600:hover {
            color: var(--accent-gold-light) !important;
        }
        <?php elseif ($layoutPreset === 'neon-nights'): ?>
        :root {
            --layout-bg: #0f0f1a;
            --layout-surface: #1a1a2e;
            --layout-border: #2d2d44;
            --layout-text: #e0e0ff;
            --layout-muted: #8888aa;
            --neon-primary: #ff00ff;
            --neon-secondary: #00ffff;
        }
        body { background: var(--layout-bg) !important; }
        a:hover { text-shadow: 0 0 10px var(--neon-primary); }
        <?php elseif ($layoutPreset === 'premium-luxury'): ?>
        :root {
            --layout-bg: #0d0d0d;
            --layout-surface: #1a1a1a;
            --layout-border: #333;
            --layout-text: #f5f5f5;
            --layout-muted: #999;
            --gold: #d4af37;
            --gold-light: #f4d03f;
        }
        body { background: var(--layout-bg) !important; }
        <?php elseif ($layoutPreset === 'minimal-dark'): ?>
        :root {
            --layout-bg: #000;
            --layout-surface: #111;
            --layout-border: #222;
            --layout-text: #fff;
            --layout-muted: #666;
        }
        body { background: var(--layout-bg) !important; }
        <?php endif; ?>
        <?php endif; ?>

        /* Quick Exit Button */
        .quick-exit-btn {
            position: fixed;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            z-index: 9999;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            padding: 12px 8px;
            background: #dc2626;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 8px 0 0 8px;
            cursor: pointer;
            transition: padding 0.2s;
        }
        .quick-exit-btn:hover {
            padding: 12px 12px;
        }

        /* Age Gate Styles */
        .age-gate-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.95);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .age-gate-modal {
            background: #1a1a1a;
            border-radius: 16px;
            padding: 2rem;
            max-width: 420px;
            width: 90%;
            text-align: center;
            color: white;
        }
        .age-gate-fullpage {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
            color: white;
        }
    </style>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="<?= e($primaryColor) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= e(setting('site_name', 'FWP Gallery')) ?>">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192x192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="<?= e(setting('site_name', 'FWP Gallery')) ?>">
    <meta name="msapplication-TileColor" content="<?= e($primaryColor) ?>">
    <meta name="msapplication-TileImage" content="/assets/icons/icon-144x144.png">
</head>
<body class="bg-neutral-50 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 antialiased min-h-screen flex flex-col transition-colors duration-200">
    <!-- Body Tracking Codes (GTM noscript, etc.) -->
    <?= body_tracking_codes() ?>

    <!-- Announcement Bar -->
    <?= render_announcement() ?>

    <?php if ($ageGateEnabled): ?>
    <!-- Age Verification Gate -->
    <div id="ageGate" class="age-gate-overlay" style="display: none;">
        <?php if (setting('age_gate_style', 'modal') === 'fullpage'): ?>
        <div class="age-gate-fullpage">
        <?php else: ?>
        <div class="age-gate-modal">
        <?php endif; ?>
            <div class="mb-6">
                <svg class="w-16 h-16 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold mb-4"><?= e(setting('age_gate_title', 'Age Verification Required')) ?></h2>
            <p class="text-neutral-400 mb-6"><?= e(setting('age_gate_message', 'This website contains age-restricted content. By entering, you confirm that you are at least 18 years old.')) ?></p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="verifyAge(true)" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">
                    I am <?= e(setting('age_gate_min_age', '18')) ?>+ years old
                </button>
                <button onclick="verifyAge(false)" class="px-8 py-3 bg-neutral-700 hover:bg-neutral-600 text-white font-semibold rounded-lg transition">
                    Exit
                </button>
            </div>
            <p class="text-xs text-neutral-500 mt-6">By entering, you agree to our Terms of Service and Privacy Policy.</p>
        </div>
    </div>
    <script>
    (function() {
        const ageVerified = localStorage.getItem('age_verified') || sessionStorage.getItem('age_verified');
        const remember = '<?= e(setting('age_gate_remember', '7d')) ?>';

        if (!ageVerified) {
            document.getElementById('ageGate').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        window.verifyAge = function(confirmed) {
            if (confirmed) {
                if (remember === 'session') {
                    sessionStorage.setItem('age_verified', '1');
                } else {
                    localStorage.setItem('age_verified', '1');
                    // Set expiry based on remember duration
                    const expiry = {
                        '24h': 1, '7d': 7, '30d': 30
                    }[remember] || 7;
                    localStorage.setItem('age_verified_expiry', Date.now() + (expiry * 24 * 60 * 60 * 1000));
                }
                document.getElementById('ageGate').style.display = 'none';
                document.body.style.overflow = '';
            } else {
                window.location.href = 'https://www.google.com';
            }
        };

        // Check expiry
        const expiry = localStorage.getItem('age_verified_expiry');
        if (expiry && Date.now() > parseInt(expiry)) {
            localStorage.removeItem('age_verified');
            localStorage.removeItem('age_verified_expiry');
            document.getElementById('ageGate').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    })();
    </script>
    <?php endif; ?>

    <?php if ($quickExitEnabled): ?>
    <!-- Quick Exit Button -->
    <a href="<?= e(setting('quick_exit_url', 'https://www.google.com')) ?>" class="quick-exit-btn" title="Quick Exit">
        <?= e(setting('quick_exit_text', 'Exit')) ?>
    </a>
    <?php endif; ?>

    <?php if ($privateBrowsingNotice): ?>
    <!-- Private Browsing Notice -->
    <div id="privateBrowsingNotice" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 sm:max-w-sm bg-neutral-800 text-white p-4 rounded-lg shadow-lg z-50" style="display: none;">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium">Privacy Tip</p>
                <p class="text-xs text-neutral-400 mt-1">For enhanced privacy, consider using your browser's private/incognito mode.</p>
            </div>
            <button onclick="dismissPrivacyNotice()" class="text-neutral-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <script>
    (function() {
        if (!sessionStorage.getItem('privacy_notice_dismissed')) {
            setTimeout(() => {
                document.getElementById('privateBrowsingNotice').style.display = 'block';
            }, 2000);
        }
        window.dismissPrivacyNotice = function() {
            document.getElementById('privateBrowsingNotice').style.display = 'none';
            sessionStorage.setItem('privacy_notice_dismissed', '1');
        };
    })();
    </script>
    <?php endif; ?>

    <!-- Header -->
    <header class="bg-white dark:bg-neutral-800 shadow-sm dark:shadow-neutral-700/50 sticky top-0 z-50 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="<?= $view->url('/') ?>" class="flex items-center gap-2">
                    <?php
                    $siteLogo = setting('site_logo');
                    $siteName = setting('site_name', config('app.name'));
                    $logoHeight = setting('logo_height', '40');
                    ?>
                    <?php if ($siteLogo): ?>
                        <img src="<?= uploads_url(e($siteLogo)) ?>" alt="<?= e($siteName) ?>" style="height: <?= (int)$logoHeight ?>px;" class="object-contain">
                    <?php else: ?>
                        <span class="text-xl font-bold text-primary-600"><?= e($siteName) ?></span>
                    <?php endif; ?>
                </a>

                <!-- Navigation -->
                <nav class="hidden md:flex items-center gap-6">
                    <a href="<?= $view->url('/gallery') ?>" class="text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white">Gallery</a>
                    <a href="<?= $view->url('/trending') ?>" class="text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white">Trending</a>
                    <a href="<?= $view->url('/blog') ?>" class="text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white">Blog</a>
                    <?php
                    // Check premium settings and status for nav badge
                    $premiumEnabled = setting('premium_enabled', '1') === '1';
                    $navUserPremium = false;
                    if (isset($_SESSION['user_id'])) {
                        $navDb = app()->getDatabase();
                        $navUser = $navDb->fetch("SELECT is_premium, premium_until FROM users WHERE id = :id", ['id' => $_SESSION['user_id']]);
                        $navUserPremium = $navUser && $navUser['is_premium'] && strtotime($navUser['premium_until']) > time();
                    }
                    ?>
                    <?php if ($premiumEnabled): ?>
                    <?php if (!$navUserPremium): ?>
                    <a href="<?= $view->url('/premium') ?>" class="text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        Premium
                    </a>
                    <?php else: ?>
                    <span class="text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1" title="Premium Member">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        Premium
                    </span>
                    <?php endif; ?>
                    <?php endif; ?>
                </nav>

                <!-- Search -->
                <form action="<?= $view->url('/search') ?>" method="GET" class="hidden md:block">
                    <div class="relative">
                        <input type="text" name="q" placeholder="Search images..."
                               class="w-64 px-4 py-2 pl-10 bg-neutral-100 dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-neutral-600">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>

                <!-- Auth Links & Theme Toggle -->
                <div class="flex items-center gap-4">
                    <?php if (setting('dark_mode_toggle_enabled', '1') === '1' && $layoutPreset !== 'pexels-stock'): ?>
                    <!-- Dark Mode Toggle (hidden for Pexels theme) -->
                    <button type="button" id="theme-toggle"
                            class="p-2 rounded-lg text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            title="Toggle dark mode">
                        <!-- Sun icon (shown in dark mode) -->
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"/>
                        </svg>
                        <!-- Moon icon (shown in light mode) -->
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])):
                        // Get current user info for header
                        $headerDb = app()->getDatabase();
                        $headerUser = $headerDb->fetch("SELECT id, username, email, role FROM users WHERE id = :id", ['id' => $_SESSION['user_id']]);
                        $isAdmin = $headerUser && in_array($headerUser['role'], ['admin', 'moderator']);
                    ?>
                        <!-- Upload Button -->
                        <a href="<?= $view->url('/upload') ?>" class="text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white" title="Upload">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </a>

                        <!-- Favorites -->
                        <a href="<?= $view->url('/favorites') ?>" class="text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white" title="Favorites">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </a>

                        <!-- Admin Panel Button (for admins/mods) -->
                        <?php if ($isAdmin): ?>
                        <a href="<?= $view->url('/admin') ?>" class="flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition" title="Admin Panel">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="hidden lg:inline">Admin</span>
                        </a>
                        <?php endif; ?>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false"
                                    class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700 transition">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-sm font-semibold">
                                    <?= strtoupper(substr($headerUser['username'] ?? 'U', 0, 1)) ?>
                                </div>
                                <svg class="w-4 h-4 text-neutral-500 dark:text-neutral-400" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-neutral-800 rounded-xl shadow-lg border border-neutral-200 dark:border-neutral-700 py-2 z-50">

                                <!-- User Info -->
                                <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white"><?= e($headerUser['username'] ?? 'User') ?></p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate"><?= e($headerUser['email'] ?? '') ?></p>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-1">
                                    <a href="<?= $view->url('/user/' . ($headerUser['username'] ?? '')) ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        My Profile
                                    </a>
                                    <a href="<?= $view->url('/profile') ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Settings
                                    </a>
                                    <a href="<?= $view->url('/favorites') ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        Favorites
                                    </a>
                                    <a href="<?= $view->url('/upload') ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Upload
                                    </a>
                                </div>

                                <?php if ($isAdmin): ?>
                                <div class="border-t border-neutral-200 dark:border-neutral-700 py-1">
                                    <a href="<?= $view->url('/admin') ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-primary-600 dark:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Admin Panel
                                    </a>
                                </div>
                                <?php endif; ?>

                                <div class="border-t border-neutral-200 dark:border-neutral-700 py-1">
                                    <a href="<?= $view->url('/logout') ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Sign Out
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= $view->url('/login') ?>" class="text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white font-medium">Sign In</a>
                        <?php if (setting('registration_enabled', '1') === '1'): ?>
                        <a href="<?= $view->url('/register') ?>" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-medium transition">Get Started</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if ($view->hasFlash('success')): ?>
    <div class="bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative max-w-7xl mx-auto mt-4" role="alert">
        <?= e($view->flash('success')) ?>
    </div>
    <?php endif; ?>

    <?php if ($view->hasFlash('error')): ?>
    <div class="bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded relative max-w-7xl mx-auto mt-4" role="alert">
        <?= e($view->flash('error')) ?>
    </div>
    <?php endif; ?>

    <!-- Header Ad -->
    <?php if ($headerAd = render_ad('header_banner')): ?>
    <div class="bg-neutral-100 dark:bg-neutral-800 py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-center">
            <?= $headerAd ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1">
        <?= $content ?>
    </main>

    <!-- Footer Ad -->
    <?php if ($footerAd = render_ad('footer_banner')): ?>
    <div class="bg-neutral-100 dark:bg-neutral-800 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-center">
            <?= $footerAd ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-white dark:bg-neutral-800 border-t dark:border-neutral-700 mt-auto transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <?php if ($disclaimerEnabled): ?>
            <div class="text-center mb-6 pb-6 border-b dark:border-neutral-700">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    <?= e(setting('disclaimer_text', 'All models are 18 years of age or older. All content complies with applicable laws.')) ?>
                </p>
                <?php $compliance2257Url = setting('compliance_2257_url'); if ($compliance2257Url): ?>
                <a href="<?= e($compliance2257Url) ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:underline mt-2 inline-block">
                    18 U.S.C. 2257 Record-Keeping Requirements Compliance Statement
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php $socials = social_links(); if (!empty($socials)): ?>
            <div class="flex justify-center gap-4 mb-6">
                <?php if (!empty($socials['twitter'])): ?>
                <a href="<?= e($socials['twitter']) ?>" target="_blank" rel="noopener" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" title="Twitter/X">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($socials['facebook'])): ?>
                <a href="<?= e($socials['facebook']) ?>" target="_blank" rel="noopener" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" title="Facebook">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($socials['instagram'])): ?>
                <a href="<?= e($socials['instagram']) ?>" target="_blank" rel="noopener" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" title="Instagram">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($socials['tiktok'])): ?>
                <a href="<?= e($socials['tiktok']) ?>" target="_blank" rel="noopener" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" title="TikTok">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($socials['youtube'])): ?>
                <a href="<?= e($socials['youtube']) ?>" target="_blank" rel="noopener" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" title="YouTube">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($socials['discord'])): ?>
                <a href="<?= e($socials['discord']) ?>" target="_blank" rel="noopener" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" title="Discord">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"/></svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($socials['telegram'])): ?>
                <a href="<?= e($socials['telegram']) ?>" target="_blank" rel="noopener" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" title="Telegram">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-neutral-500 dark:text-neutral-400 text-sm">&copy; <?= date('Y') ?> <?= e(setting('site_name', config('app.name'))) ?>. All rights reserved.</p>
                <div class="flex flex-wrap justify-center gap-4 text-sm text-neutral-500 dark:text-neutral-400">
                    <?php
                    $footerPages = \App\Models\Page::forFooter();
                    foreach ($footerPages as $footerPage):
                    ?>
                    <a href="<?= $view->url('/' . $footerPage['slug']) ?>" class="hover:text-neutral-700 dark:hover:text-neutral-200"><?= e($footerPage['title']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Dark Mode Toggle
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Only initialize if toggle button exists
        if (themeToggleBtn) {
            // Set initial icon state
            if (document.documentElement.classList.contains('dark')) {
                themeToggleLightIcon.classList.remove('hidden');
            } else {
                themeToggleDarkIcon.classList.remove('hidden');
            }

            themeToggleBtn.addEventListener('click', function() {
                // Toggle icons
                themeToggleDarkIcon.classList.toggle('hidden');
                themeToggleLightIcon.classList.toggle('hidden');

                // Toggle dark class and save preference
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            });
        }

        // Lazy loading for images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        });

        <?php if ($rightClickDisabled): ?>
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });
        <?php endif; ?>

        <?php if ($nsfwBlurEnabled && setting('nsfw_reveal_on', 'click') === 'click'): ?>
        // NSFW blur click to reveal
        document.addEventListener('click', function(e) {
            const blurEl = e.target.closest('.nsfw-blur');
            if (blurEl) {
                blurEl.classList.add('revealed');
            }
            const overlay = e.target.closest('.nsfw-overlay');
            if (overlay) {
                const blurSibling = overlay.previousElementSibling;
                if (blurSibling && blurSibling.classList.contains('nsfw-blur')) {
                    blurSibling.classList.add('revealed');
                    overlay.classList.add('hidden');
                }
            }
        });
        <?php endif; ?>
    </script>

    <!-- Popup Ads -->
    <?= render_popups() ?>
    <script>
    // Popup Ad Handler
    (function() {
        if (!window.activePopups || window.activePopups.length === 0) return;

        // Create popup container
        function createPopup(popup) {
            const overlay = document.createElement('div');
            overlay.id = 'popup-overlay-' + popup.id;
            overlay.className = 'fixed inset-0 z-[9998] flex items-center justify-center';
            overlay.style.backgroundColor = 'rgba(0,0,0,' + (popup.overlay_opacity / 100) + ')';

            const positionClasses = {
                'center': 'items-center justify-center',
                'top': 'items-start justify-center pt-20',
                'bottom': 'items-end justify-center pb-20',
                'bottom_right': 'items-end justify-end p-6',
                'bottom_left': 'items-end justify-start p-6'
            };
            overlay.className += ' ' + (positionClasses[popup.position] || positionClasses.center);

            const modal = document.createElement('div');
            modal.className = 'bg-white dark:bg-neutral-800 rounded-xl shadow-2xl relative';
            modal.style.width = popup.width;
            modal.style.maxWidth = '95vw';
            modal.style.maxHeight = '90vh';
            modal.style.overflow = 'auto';

            // Animation
            if (popup.animation === 'fade') {
                modal.style.opacity = '0';
                modal.style.transition = 'opacity 0.3s';
                setTimeout(() => modal.style.opacity = '1', 10);
            } else if (popup.animation === 'slide') {
                modal.style.transform = 'translateY(30px)';
                modal.style.opacity = '0';
                modal.style.transition = 'all 0.3s';
                setTimeout(() => { modal.style.transform = 'translateY(0)'; modal.style.opacity = '1'; }, 10);
            } else if (popup.animation === 'zoom') {
                modal.style.transform = 'scale(0.8)';
                modal.style.opacity = '0';
                modal.style.transition = 'all 0.3s';
                setTimeout(() => { modal.style.transform = 'scale(1)'; modal.style.opacity = '1'; }, 10);
            }

            // Close button
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            closeBtn.className = 'absolute top-3 right-3 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 z-10';
            closeBtn.onclick = () => closePopup(popup);

            modal.appendChild(closeBtn);
            modal.innerHTML += '<div class="p-6">' + popup.content + '</div>';

            overlay.appendChild(modal);
            document.body.appendChild(overlay);

            // Close on overlay click
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closePopup(popup);
            });

            // Close on Escape
            document.addEventListener('keydown', function escHandler(e) {
                if (e.key === 'Escape') {
                    closePopup(popup);
                    document.removeEventListener('keydown', escHandler);
                }
            });
        }

        function closePopup(popup) {
            const overlay = document.getElementById('popup-overlay-' + popup.id);
            if (overlay) overlay.remove();

            // Set cookie based on frequency
            const days = popup.cookie_days || 7;
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = 'popup_' + popup.id + '=1;expires=' + date.toUTCString() + ';path=/';
        }

        function showPopup(popup) {
            if (document.getElementById('popup-overlay-' + popup.id)) return;
            createPopup(popup);
        }

        // Initialize popups based on triggers
        window.activePopups.forEach(function(popup) {
            switch(popup.trigger_type) {
                case 'page_load':
                    setTimeout(() => showPopup(popup), popup.trigger_delay * 1000);
                    break;
                case 'timed':
                    setTimeout(() => showPopup(popup), popup.trigger_delay * 1000);
                    break;
                case 'scroll':
                    let scrollTriggered = false;
                    window.addEventListener('scroll', function() {
                        if (scrollTriggered) return;
                        const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
                        if (scrollPercent >= popup.trigger_scroll_percent) {
                            scrollTriggered = true;
                            showPopup(popup);
                        }
                    });
                    break;
                case 'exit_intent':
                    document.addEventListener('mouseout', function(e) {
                        if (e.clientY < 10) {
                            showPopup(popup);
                        }
                    });
                    break;
            }
        });
    })();

    // Announcement dismiss handler
    function dismissAnnouncement(id, days) {
        const el = document.getElementById('announcement-' + id);
        if (el) el.remove();
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = 'dismissed_announcement_' + id + '=1;expires=' + date.toUTCString() + ';path=/';
    }

    // PWA Service Worker Registration
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered:', registration.scope);

                    // Check for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New version available
                                if (confirm('A new version is available. Reload to update?')) {
                                    window.location.reload();
                                }
                            }
                        });
                    });
                })
                .catch(err => console.log('SW registration failed:', err));
        });
    }

    // PWA Install Prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;

        // Show custom install button if you have one
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'block';
            installBtn.addEventListener('click', () => {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choice) => {
                    if (choice.outcome === 'accepted') {
                        console.log('PWA installed');
                    }
                    deferredPrompt = null;
                    installBtn.style.display = 'none';
                });
            });
        }
    });
    </script>

    <?php
    // Check if user is premium
    $userIsPremium = false;
    if (isset($_SESSION['user_id'])) {
        $db = app()->getDatabase();
        $user = $db->fetch(
            "SELECT is_premium, premium_until FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        );
        if ($user && $user['is_premium'] && strtotime($user['premium_until']) > time()) {
            $userIsPremium = true;
        }
    }
    $adblockDetectionEnabled = (bool) setting('adblock_detection_enabled', '1');
    ?>

    <?php if ($adblockDetectionEnabled && !$userIsPremium): ?>
    <!-- Ad Blocker Detection -->
    <script>
        window.USER_IS_PREMIUM = false;
        window.USER_IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    </script>
    <script src="<?= $view->url('/assets/js/ads.js') ?>"></script>
    <script src="<?= $view->url('/assets/js/adblock-detect.js') ?>"></script>
    <?php else: ?>
    <script>window.USER_IS_PREMIUM = true;</script>
    <?php endif; ?>
</body>
</html>
