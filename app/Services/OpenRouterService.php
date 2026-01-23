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

        // The prompt - engineered for adult gallery with explicit examples
        $prompt = "You are an image metadata generator for an adult gallery website. Analyze this image and generate SEO-optimized metadata.

IMPORTANT: You must output ACTUAL descriptive content, not placeholders or boolean values.

Return a JSON object with these fields:
{
  \"title\": \"[Write a creative, seductive 5-10 word title describing the image]\",
  \"description\": \"[Write 2-3 sentences describing the subject, pose, setting, and mood]\",
  \"tags\": [\"tag1\", \"tag2\", ... up to 15 tags describing: body type, hair color, clothing, pose, setting, mood, style],
  \"category\": \"[Pick ONE from: {$categoryList}]\",
  \"alt_text\": \"[Brief 10-15 word description for accessibility]\"
}

Example output for a blonde woman in lingerie:
{
  \"title\": \"Stunning Blonde Beauty in Black Lace Lingerie\",
  \"description\": \"A gorgeous blonde woman poses seductively in elegant black lace lingerie. Her confident gaze and perfect curves create an alluring atmosphere.\",
  \"tags\": [\"blonde\", \"lingerie\", \"black lace\", \"seductive\", \"curvy\", \"bedroom\", \"glamour\", \"sexy\", \"confident\", \"beautiful\", \"model\", \"intimate\", \"sensual\", \"elegant\", \"alluring\"],
  \"category\": \"Glamour\",
  \"alt_text\": \"Blonde woman in black lace lingerie posing in bedroom\"
}

Now analyze the provided image and generate similar metadata. Be descriptive and specific.";

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

        // Validate response - reject garbage/placeholder values
        if (!$this->validateResponse($result)) {
            $this->debugLog("Response validation failed - got placeholder values");
            throw new \RuntimeException('OpenRouter returned placeholder values instead of actual content');
        }

        // Build metadata in the format expected by MetadataGenerator
        return $this->buildMetadata($result, $existingCategories);
    }

    /**
     * Validate that response contains actual content, not placeholders
     */
    private function validateResponse(array $result): bool
    {
        // Check title - must be a string with actual content
        $title = $result['title'] ?? null;
        if (!is_string($title) || strlen($title) < 5) {
            $this->debugLog("Invalid title: " . json_encode($title));
            return false;
        }
        // Reject boolean-like or placeholder values
        if (in_array(strtolower($title), ['true', 'false', 'text', 'string', 'null', '1', '0'])) {
            $this->debugLog("Title is a placeholder value: {$title}");
            return false;
        }

        // Check description
        $description = $result['description'] ?? null;
        if (!is_string($description) || strlen($description) < 10) {
            $this->debugLog("Invalid description: " . json_encode($description));
            return false;
        }
        if (in_array(strtolower($description), ['true', 'false', 'text', 'string', 'null'])) {
            return false;
        }

        // Check tags - must be array with actual tag strings
        $tags = $result['tags'] ?? [];
        if (!is_array($tags) || count($tags) < 3) {
            $this->debugLog("Invalid tags: " . json_encode($tags));
            return false;
        }
        // Check if tags are just "true" repeated
        $uniqueTags = array_unique($tags);
        if (count($uniqueTags) < 3 || in_array('true', $uniqueTags) || in_array('false', $uniqueTags)) {
            $this->debugLog("Tags contain placeholder values");
            return false;
        }

        return true;
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
