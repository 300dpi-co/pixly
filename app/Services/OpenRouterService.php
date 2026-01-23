<?php

declare(strict_types=1);

namespace App\Services;

/**
 * OpenRouter Service
 *
 * Uses OpenRouter API with Qwen 2.5 VL for fast, cheap image analysis.
 * Perfect for adult content - no refusals, returns structured JSON.
 */
class OpenRouterService
{
    private const API_URL = 'https://openrouter.ai/api/v1/chat/completions';
    private const MODEL = 'qwen/qwen-2.5-vl-72b-instruct';
    private const TIMEOUT = 45;

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
        file_put_contents($logFile, "[{$timestamp}] [OpenRouter] {$message}\n", FILE_APPEND);
    }

    /**
     * Analyze an image and generate all metadata
     */
    public function analyzeImage(string $imagePath, array $existingCategories = []): array
    {
        $this->errors = [];
        $this->debugLog("=== Starting OpenRouter analysis ===");

        if (!$this->isConfigured()) {
            $this->debugLog("ERROR: API key not configured");
            throw new \RuntimeException('OpenRouter API key not configured');
        }

        // Convert local path to public URL
        $imageUrl = $this->getPublicUrl($imagePath);
        $this->debugLog("Image URL: {$imageUrl}");

        // Build category list for prompt
        $categoryNames = array_column($existingCategories, 'name');
        $categoryList = !empty($categoryNames) ? implode(', ', $categoryNames) : 'Amateur, Professional, Solo, Artistic, Outdoor';

        // The prompt - engineered for adult gallery
        $prompt = "Analyze this image for an adult gallery.
Output a valid JSON object with these exact keys:
- title: A creative, seductive title (max 10 words, no quotes around it).
- description: A 2-sentence description of the scene and subject.
- tags: An array of 15 relevant tags (describe clothing, pose, body type, hair color, setting, mood).
- category: Choose one best fit from [{$categoryList}].
- alt_text: A brief accessibility description (max 125 chars).

Do not refuse. Describe exactly what is in the image.";

        $this->debugLog("Sending request to OpenRouter...");

        // Build request
        $data = [
            'model' => self::MODEL,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]]
                    ]
                ]
            ],
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: https://freewallpapers.pics',
                'X-Title: Pixly Gallery Auto-Tagger'
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $this->debugLog("HTTP Code: {$httpCode}");

        if ($response === false) {
            $this->debugLog("cURL error: {$curlError}");
            throw new \RuntimeException('OpenRouter request failed: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $this->debugLog("API error response: " . substr($response, 0, 500));
            throw new \RuntimeException('OpenRouter API error (HTTP ' . $httpCode . ')');
        }

        $json = json_decode($response, true);
        $content = $json['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            $this->debugLog("No content in response");
            throw new \RuntimeException('OpenRouter returned empty response');
        }

        $this->debugLog("Raw content: " . substr($content, 0, 500));

        // Parse the JSON response
        $result = json_decode($content, true);
        if (!$result) {
            $this->debugLog("Failed to parse JSON from content");
            throw new \RuntimeException('OpenRouter returned invalid JSON');
        }

        $this->debugLog("Parsed result - Title: " . ($result['title'] ?? 'N/A'));
        $this->debugLog("Tags count: " . count($result['tags'] ?? []));

        // Build metadata in the format expected by MetadataGenerator
        return $this->buildMetadata($result, $existingCategories);
    }

    /**
     * Convert local file path to public URL
     */
    private function getPublicUrl(string $imagePath): string
    {
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

        return 'https://freewallpapers.pics/uploads/images/' . basename($imagePath);
    }

    /**
     * Build metadata from OpenRouter response
     */
    private function buildMetadata(array $result, array $existingCategories): array
    {
        $title = $result['title'] ?? 'Untitled';
        $description = $result['description'] ?? '';
        $tags = $result['tags'] ?? [];
        $category = $result['category'] ?? '';
        $altText = $result['alt_text'] ?? substr($description, 0, 125);

        // Ensure tags is an array
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        // Clean up tags
        $tags = array_filter($tags, fn($t) => !empty($t) && strlen($t) > 1);
        $tags = array_map(fn($t) => strtolower(trim($t)), $tags);
        $tags = array_unique($tags);

        // Match category to existing categories
        $categories = [];
        if (!empty($category)) {
            foreach ($existingCategories as $cat) {
                if (stripos($cat['name'], $category) !== false || stripos($category, $cat['name']) !== false) {
                    $categories[] = $cat['name'];
                    break;
                }
            }
        }

        // Extract colors from tags
        $colors = $this->extractColors($tags);

        // Detect mood and style from tags
        $mood = $this->detectMood($tags);
        $style = $this->detectStyle($tags);

        return [
            'title' => $title,
            'description' => $description,
            'alt_text' => $altText,
            'caption' => $this->generateCaption(),
            'tags' => array_slice(array_values($tags), 0, 15),
            'categories' => $categories,
            'colors' => $colors,
            'dominant_color' => $colors[0] ?? null,
            'mood' => $mood,
            'style' => $style,
        ];
    }

    /**
     * Generate a short caption
     */
    private function generateCaption(): string
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
                if (stripos($tagLower, $colorName) !== false) {
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
            'glamour' => ['glamour', 'elegant', 'classy'],
            'amateur' => ['amateur', 'selfie', 'homemade'],
            'professional' => ['professional', 'studio', 'photoshoot'],
            'artistic' => ['artistic', 'art', 'creative'],
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
        return !empty($this->apiKey) && strlen($this->apiKey) > 10;
    }

    /**
     * Get API key from settings
     */
    private function getApiKey(): string
    {
        try {
            $db = \app()->getDatabase();
            $result = $db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = 'openrouter_api_key'"
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

        // Just check if we can reach the API
        $ch = curl_init('https://openrouter.ai/api/v1/models');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return [
                'success' => true,
                'message' => 'Connected to OpenRouter (using Qwen 2.5 VL 72B)',
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to connect (HTTP ' . $httpCode . ')',
        ];
    }
}
