<?php

declare(strict_types=1);

namespace App\Services;

/**
 * AI Horde Service
 *
 * Uses AI Horde's free interrogation API for image analysis.
 * Returns captions and Danbooru-style tags perfect for adult content.
 */
class AIHordeService
{
    private const API_BASE = 'https://stablehorde.net/api/v2';
    private const TIMEOUT = 120;
    private const POLL_INTERVAL = 3;
    private const MAX_ATTEMPTS = 40; // 40 * 3 = 120 seconds max

    private string $apiKey;
    private array $errors = [];

    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
    }

    private function debugLog(string $message): void
    {
        $logFile = \ROOT_PATH . '/storage/logs/ai_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] [AIHorde] {$message}\n", FILE_APPEND);
    }

    /**
     * Analyze an image and generate all metadata
     */
    public function analyzeImage(string $imagePath, array $existingCategories = []): array
    {
        $this->errors = [];
        $this->debugLog("=== Starting AI Horde analysis ===");

        // Increase PHP timeout for async polling
        set_time_limit(self::TIMEOUT + 30);

        if (!$this->isConfigured()) {
            $this->debugLog("ERROR: API key not configured");
            throw new \RuntimeException('AI Horde API key not configured');
        }

        // Convert local path to public URL
        $imageUrl = $this->getPublicUrl($imagePath);
        $this->debugLog("Image URL: {$imageUrl}");

        // Submit interrogation request
        $requestId = $this->submitInterrogation($imageUrl);
        if (!$requestId) {
            throw new \RuntimeException('Failed to submit image to AI Horde');
        }

        $this->debugLog("Request ID: {$requestId}");

        // Poll for results
        $result = $this->pollForResults($requestId);
        if (!$result) {
            throw new \RuntimeException('AI Horde processing timed out or failed');
        }

        $this->debugLog("Got results - Caption: " . substr($result['caption'] ?? '', 0, 100));
        $this->debugLog("Tags count: " . substr_count($result['tags'] ?? '', ','));

        // Build metadata from results
        return $this->buildMetadata($result['caption'], $result['tags'], $existingCategories);
    }

    /**
     * Convert local file path to public URL
     */
    private function getPublicUrl(string $imagePath): string
    {
        // Extract relative path from full path
        // /home/.../public_html/uploads/images/2026/01/hash.jpg -> images/2026/01/hash.jpg
        if (preg_match('#uploads[/\\\\](.+)$#', $imagePath, $matches)) {
            $relativePath = str_replace('\\', '/', $matches[1]);

            // Check if WebP version exists
            $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $relativePath);
            $fullWebpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $imagePath);

            if (file_exists($fullWebpPath)) {
                $relativePath = $webpPath;
            }

            return 'https://freewallpapers.pics/uploads/' . $relativePath;
        }

        // Fallback - assume it's already a URL or construct from filename
        return 'https://freewallpapers.pics/uploads/images/' . basename($imagePath);
    }

    /**
     * Submit image for interrogation
     */
    private function submitInterrogation(string $imageUrl): ?string
    {
        $payload = [
            'source_image' => $imageUrl,
            'forms' => [
                ['name' => 'caption'],
                ['name' => 'interrogation'], // Danbooru-style tags
            ],
        ];

        $this->debugLog("Submitting to AI Horde...");

        $ch = curl_init(self::API_BASE . '/interrogate/async');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->apiKey,
                'Content-Type: application/json',
                'Client-Agent: PixlyGallery:1.4:contact@freewallpapers.pics',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $this->debugLog("Submit HTTP Code: {$httpCode}");
        $this->debugLog("Submit Response: " . substr($response, 0, 500));

        if ($response === false) {
            $this->debugLog("cURL error: {$curlError}");
            return null;
        }

        if ($httpCode !== 200 && $httpCode !== 202) {
            $this->debugLog("HTTP error: {$httpCode}");
            return null;
        }

        $data = json_decode($response, true);
        return $data['id'] ?? null;
    }

    /**
     * Poll for interrogation results
     */
    private function pollForResults(string $requestId): ?array
    {
        $this->debugLog("Polling for results...");

        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            sleep(self::POLL_INTERVAL);

            $ch = curl_init(self::API_BASE . '/interrogate/status/' . $requestId);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => [
                    'apikey: ' . $this->apiKey,
                    'Client-Agent: PixlyGallery:1.4:contact@freewallpapers.pics',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode !== 200) {
                $this->debugLog("Poll attempt {$i}: HTTP {$httpCode}");
                continue;
            }

            $data = json_decode($response, true);
            $state = $data['state'] ?? 'unknown';

            $this->debugLog("Poll attempt {$i}: state={$state}");

            if ($state === 'done') {
                // Extract results from forms
                $caption = '';
                $tags = '';

                foreach ($data['forms'] ?? [] as $form) {
                    if ($form['form'] === 'caption') {
                        $caption = $form['result']['caption'] ?? '';
                    } elseif ($form['form'] === 'interrogation') {
                        $tags = $form['result']['interrogation'] ?? '';
                    }
                }

                return [
                    'caption' => $caption,
                    'tags' => $tags,
                ];
            }

            if ($state === 'faulted' || $state === 'cancelled') {
                $this->debugLog("Request failed with state: {$state}");
                return null;
            }
        }

        $this->debugLog("Polling timed out after " . (self::MAX_ATTEMPTS * self::POLL_INTERVAL) . " seconds");
        return null;
    }

    /**
     * Build metadata from AI Horde results
     */
    private function buildMetadata(string $caption, string $tagsString, array $existingCategories): array
    {
        // Parse tags from comma-separated string
        $tagNames = array_map('trim', explode(',', $tagsString));
        $tagNames = array_filter($tagNames, fn($t) => !empty($t) && strlen($t) > 1);

        // Clean up tags (remove underscores, parentheses content)
        $tagNames = array_map(function($tag) {
            $tag = str_replace('_', ' ', $tag);
            $tag = preg_replace('/\s*\([^)]*\)/', '', $tag);
            return trim($tag);
        }, $tagNames);

        $this->debugLog("Parsed " . count($tagNames) . " tags");

        // Generate title from tags
        $title = $this->generateTitle($tagNames);

        // Generate description
        $description = $this->generateDescription($caption, $tagNames);

        // Match categories
        $categories = $this->matchCategories($tagNames, $existingCategories);

        // Prioritize adult-relevant tags
        $finalTags = $this->prioritizeTags($tagNames);

        // Extract colors
        $colors = $this->extractColors($tagNames);

        return [
            'title' => $title,
            'description' => $description,
            'alt_text' => substr($caption, 0, 125),
            'caption' => $this->generateCaption($tagNames),
            'tags' => array_slice($finalTags, 0, 15),
            'categories' => $categories,
            'colors' => $colors,
            'dominant_color' => $colors[0] ?? null,
            'mood' => $this->detectMood($tagNames),
            'style' => $this->detectStyle($tagNames),
        ];
    }

    /**
     * Generate title from tags
     */
    private function generateTitle(array $tags): string
    {
        $bodyTags = ['big breasts', 'huge breasts', 'large breasts', 'small breasts', 'medium breasts',
            'big ass', 'huge ass', 'thick thighs', 'curvy', 'petite', 'slim', 'athletic',
            'busty', 'voluptuous'];
        $hairTags = ['blonde', 'brunette', 'redhead', 'black hair', 'brown hair', 'pink hair', 'blue hair'];

        $body = '';
        $hair = '';

        foreach ($tags as $tag) {
            $tagLower = strtolower($tag);
            if (!$body) {
                foreach ($bodyTags as $bt) {
                    if (stripos($tagLower, $bt) !== false) {
                        $body = ucwords($bt);
                        break;
                    }
                }
            }
            if (!$hair) {
                foreach ($hairTags as $ht) {
                    if (stripos($tagLower, $ht) !== false) {
                        $hair = ucwords($ht);
                        break;
                    }
                }
            }
        }

        $parts = array_filter([$body, $hair]);
        if (!empty($parts)) {
            $adjectives = ['Stunning', 'Gorgeous', 'Sexy', 'Hot', 'Beautiful', 'Sensual'];
            $adj = $adjectives[array_rand($adjectives)];
            return $adj . ' ' . implode(' ', $parts) . ' Babe';
        }

        // Fallback
        return 'Beautiful Model Photo';
    }

    /**
     * Generate description
     */
    private function generateDescription(string $caption, array $tags): string
    {
        $relevantTags = array_slice($tags, 0, 5);
        $tagStr = implode(', ', $relevantTags);

        $cleanCaption = trim($caption);
        if (strlen($cleanCaption) > 150) {
            $cleanCaption = substr($cleanCaption, 0, 147) . '...';
        }

        if (!empty($tagStr)) {
            return $cleanCaption . ' Features: ' . $tagStr . '.';
        }

        return $cleanCaption ?: 'High quality photo.';
    }

    /**
     * Generate short caption
     */
    private function generateCaption(array $tags): string
    {
        $templates = [
            'Come and see more...',
            'Like what you see?',
            'Ready to play?',
            'Feeling naughty today',
            'Just for you',
            'Enjoying the view?',
        ];

        return $templates[array_rand($templates)];
    }

    /**
     * Match tags to existing categories
     */
    private function matchCategories(array $tags, array $existingCategories): array
    {
        if (empty($existingCategories)) {
            return [];
        }

        $matched = [];
        $categoryNames = array_column($existingCategories, 'name');

        foreach ($categoryNames as $catName) {
            $catLower = strtolower($catName);
            foreach ($tags as $tag) {
                $tagLower = strtolower($tag);
                if ($tagLower === $catLower || stripos($tagLower, $catLower) !== false || stripos($catLower, $tagLower) !== false) {
                    $matched[] = $catName;
                    break;
                }
            }
        }

        return array_slice(array_unique($matched), 0, 2);
    }

    /**
     * Prioritize adult-relevant tags
     */
    private function prioritizeTags(array $tags): array
    {
        $adultPriority = [
            'big breasts', 'huge breasts', 'large breasts', 'medium breasts', 'small breasts',
            'big ass', 'huge ass', 'ass', 'booty', 'bubble butt',
            'thick thighs', 'thighs', 'legs', 'feet',
            'pussy', 'shaved pussy', 'hairy pussy',
            'nipples', 'areolae', 'cleavage',
            'nude', 'naked', 'topless', 'bottomless',
            'lingerie', 'bikini', 'underwear', 'bra', 'panties', 'thong',
            'stockings', 'garter', 'high heels', 'boots',
            'blonde', 'brunette', 'redhead', 'black hair',
            'curvy', 'petite', 'slim', 'athletic', 'busty', 'thicc',
            'milf', 'teen', 'mature',
            'solo', '1girl', 'looking at viewer',
            'bedroom', 'outdoor', 'bathroom', 'pool',
            'spreading', 'bent over', 'on back', 'on knees', 'doggy style',
        ];

        $prioritized = [];
        $other = [];

        foreach ($tags as $tag) {
            $tagLower = strtolower($tag);
            $isPriority = false;

            foreach ($adultPriority as $priority) {
                if (stripos($tagLower, $priority) !== false) {
                    $prioritized[] = $tag;
                    $isPriority = true;
                    break;
                }
            }

            if (!$isPriority && !in_array($tag, ['1girl', 'solo', 'simple background', 'white background'])) {
                $other[] = $tag;
            }
        }

        return array_merge($prioritized, array_slice($other, 0, 5));
    }

    /**
     * Extract colors from tags
     */
    private function extractColors(array $tags): array
    {
        $colorMap = [
            'red' => '#e53935',
            'blue' => '#1e88e5',
            'green' => '#43a047',
            'yellow' => '#fdd835',
            'pink' => '#e91e63',
            'purple' => '#8e24aa',
            'orange' => '#fb8c00',
            'black' => '#212121',
            'white' => '#fafafa',
            'brown' => '#6d4c41',
        ];

        $colors = [];
        foreach ($tags as $tag) {
            $tagLower = strtolower($tag);
            foreach ($colorMap as $colorName => $hex) {
                if (stripos($tagLower, $colorName) !== false && stripos($tagLower, 'hair') === false) {
                    $colors[] = $hex;
                }
            }
        }

        return array_slice(array_unique($colors), 0, 3);
    }

    /**
     * Detect mood from tags
     */
    private function detectMood(array $tags): string
    {
        $moods = [
            'sensual' => ['sensual', 'seductive', 'alluring', 'romantic'],
            'playful' => ['playful', 'smiling', 'happy', 'fun', 'cheerful'],
            'naughty' => ['naughty', 'teasing', 'provocative', 'kinky'],
            'innocent' => ['innocent', 'cute', 'sweet', 'shy'],
            'dominant' => ['dominant', 'confident', 'powerful', 'commanding'],
            'submissive' => ['submissive', 'shy', 'timid'],
        ];

        foreach ($moods as $mood => $keywords) {
            foreach ($tags as $tag) {
                foreach ($keywords as $kw) {
                    if (stripos($tag, $kw) !== false) {
                        return $mood;
                    }
                }
            }
        }

        return 'sexy';
    }

    /**
     * Detect style from tags
     */
    private function detectStyle(array $tags): string
    {
        $styles = [
            'glamour' => ['glamour', 'elegant', 'classy', 'sophisticated'],
            'amateur' => ['amateur', 'selfie', 'homemade', 'candid'],
            'professional' => ['professional', 'studio', 'photoshoot'],
            'artistic' => ['artistic', 'art', 'creative', 'abstract'],
            'outdoor' => ['outdoor', 'nature', 'beach', 'pool'],
            'boudoir' => ['boudoir', 'bedroom', 'intimate', 'lingerie'],
        ];

        foreach ($styles as $style => $keywords) {
            foreach ($tags as $tag) {
                foreach ($keywords as $kw) {
                    if (stripos($tag, $kw) !== false) {
                        return $style;
                    }
                }
            }
        }

        return 'photography';
    }

    /**
     * Check if API is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== '0000000000';
    }

    /**
     * Get API key from settings
     */
    private function getApiKey(): string
    {
        try {
            $db = \app()->getDatabase();
            $result = $db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = 'aihorde_api_key'"
            );

            if ($result && !empty($result['setting_value'])) {
                return $result['setting_value'];
            }
        } catch (\Throwable $e) {
            // Fall through
        }

        return '';
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Test the API connection
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API key not configured',
            ];
        }

        try {
            $ch = curl_init(self::API_BASE . '/find_user');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'apikey: ' . $this->apiKey,
                    'Client-Agent: PixlyGallery:1.4:contact@freewallpapers.pics',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to AI Horde (HTTP ' . $httpCode . ')',
                ];
            }

            $data = json_decode($response, true);

            if (isset($data['username'])) {
                return [
                    'success' => true,
                    'message' => 'Connected as: ' . $data['username'] . ' (Kudos: ' . ($data['kudos'] ?? 0) . ')',
                    'username' => $data['username'],
                ];
            }

            return [
                'success' => false,
                'error' => 'Invalid API key',
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
