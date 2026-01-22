<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Replicate AI Service
 *
 * Handles AI-powered image analysis using Replicate's LLaVA model.
 * NSFW-friendly alternative to Claude for adult content sites.
 */
class ReplicateAIService
{
    private const API_ENDPOINT = 'https://api.replicate.com/v1/predictions';
    // LLaVA 1.6 34B - more capable model
    private const MODEL_VERSION = 'yorickvp/llava-v1.6-34b:41ecfbfb261e6c1adf3ad896c9066ca98346996d7c4045c5bc944a79d430f174';
    private const TIMEOUT = 120;
    private const POLL_INTERVAL = 2; // seconds

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
    }

    /**
     * Analyze an image and generate all metadata
     */
    public function analyzeImage(string $imagePath, array $existingCategories = []): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Replicate API key not configured');
        }

        if (!file_exists($imagePath)) {
            throw new \RuntimeException('Image file not found: ' . $imagePath);
        }

        // Convert image to base64 data URL
        $imageUrl = $this->imageToDataUrl($imagePath);

        // Build the prompt
        $prompt = $this->buildAnalysisPrompt($existingCategories);

        // Call Replicate API
        $response = $this->callAPI($imageUrl, $prompt);

        // Parse the response
        return $this->parseResponse($response);
    }

    /**
     * Generate just tags for an image
     */
    public function generateTags(string $imagePath, int $count = 10): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $imageUrl = $this->imageToDataUrl($imagePath);

        $prompt = "Analyze this image and generate exactly {$count} relevant tags for SEO.
Return ONLY a JSON array of lowercase tags, no other text.
Example: [\"sunset\", \"beach\", \"ocean\", \"waves\", \"orange sky\"]";

        $response = $this->callAPI($imageUrl, $prompt);

        try {
            // Extract JSON from response
            if (preg_match('/\[.*\]/s', $response, $matches)) {
                $tags = json_decode($matches[0], true);
                if (is_array($tags)) {
                    return array_slice($tags, 0, $count);
                }
            }
        } catch (\Throwable $e) {
            // Fall through
        }

        return [];
    }

    /**
     * Generate alt text for accessibility
     */
    public function generateAltText(string $imagePath): string
    {
        if (!$this->isConfigured()) {
            return '';
        }

        $imageUrl = $this->imageToDataUrl($imagePath);

        $prompt = "Generate a concise, descriptive alt text for this image for accessibility purposes.
The alt text should:
- Be under 125 characters
- Describe the key visual elements
- Not start with 'Image of' or 'Picture of'
Return ONLY the alt text, no quotes or other text.";

        return trim($this->callAPI($imageUrl, $prompt));
    }

    /**
     * Build the comprehensive analysis prompt
     */
    private function buildAnalysisPrompt(array $existingCategories): string
    {
        $categoriesList = !empty($existingCategories)
            ? "Available categories to choose from: " . implode(', ', array_column($existingCategories, 'name'))
            : "Suggest appropriate category names";

        return "Analyze this image and provide comprehensive metadata for an image gallery website.

{$categoriesList}

Return your analysis in this exact JSON format (no other text before or after):
{
    \"title\": \"A compelling, SEO-friendly title (5-10 words)\",
    \"description\": \"A detailed description for SEO (2-3 sentences, 150-200 characters)\",
    \"alt_text\": \"Accessible alt text (under 125 characters)\",
    \"caption\": \"A short caption for display (under 100 characters)\",
    \"tags\": [\"tag1\", \"tag2\", \"tag3\", \"tag4\", \"tag5\", \"tag6\", \"tag7\", \"tag8\"],
    \"categories\": [\"Primary Category\", \"Secondary Category\"],
    \"colors\": [\"#hexcode1\", \"#hexcode2\", \"#hexcode3\"],
    \"dominant_color\": \"#hexcode\",
    \"mood\": \"The overall mood/feeling of the image\",
    \"style\": \"Photography style (portrait, landscape, macro, etc.)\"
}

Return ONLY the JSON object, nothing else.";
    }

    /**
     * Call the Replicate API
     */
    private function callAPI(string $imageUrl, string $prompt): string
    {
        // Create prediction
        $payload = [
            'version' => explode(':', self::MODEL_VERSION)[1],
            'input' => [
                'image' => $imageUrl,
                'prompt' => $prompt,
                'max_tokens' => 1024,
                'temperature' => 0.2,
            ],
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 30,
                'ignore_errors' => true,
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey,
                ],
                'content' => json_encode($payload),
            ],
        ]);

        $response = @file_get_contents(self::API_ENDPOINT, false, $context);

        if ($response === false) {
            throw new \RuntimeException('Failed to connect to Replicate API');
        }

        $data = json_decode($response, true);

        if (!$data) {
            throw new \RuntimeException('Invalid response from Replicate API: ' . substr($response, 0, 200));
        }

        // Check for various error formats
        if (isset($data['error'])) {
            throw new \RuntimeException('Replicate API error: ' . ($data['error'] ?? 'Unknown error'));
        }

        if (isset($data['detail'])) {
            throw new \RuntimeException('Replicate API error: ' . $data['detail']);
        }

        if (isset($data['status']) && $data['status'] === 'failed') {
            throw new \RuntimeException('Replicate prediction failed: ' . ($data['error'] ?? 'Unknown error'));
        }

        // Poll for completion
        $predictionUrl = $data['urls']['get'] ?? null;
        if (!$predictionUrl) {
            // Log full response for debugging
            error_log('Replicate response missing urls.get: ' . json_encode($data));
            throw new \RuntimeException('No prediction URL returned. Response: ' . json_encode(array_keys($data)));
        }

        return $this->pollForResult($predictionUrl);
    }

    /**
     * Poll for prediction result
     */
    private function pollForResult(string $url): string
    {
        $startTime = time();
        $maxWait = self::TIMEOUT;

        while (time() - $startTime < $maxWait) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 30,
                    'ignore_errors' => true,
                    'header' => [
                        'Authorization: Bearer ' . $this->apiKey,
                    ],
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                sleep(self::POLL_INTERVAL);
                continue;
            }

            $data = json_decode($response, true);

            if (!$data) {
                sleep(self::POLL_INTERVAL);
                continue;
            }

            $status = $data['status'] ?? '';

            if ($status === 'succeeded') {
                $output = $data['output'] ?? '';
                // LLaVA returns output as a string or array of strings
                if (is_array($output)) {
                    return implode('', $output);
                }
                return (string) $output;
            }

            if ($status === 'failed') {
                throw new \RuntimeException('Prediction failed: ' . ($data['error'] ?? 'Unknown error'));
            }

            if ($status === 'canceled') {
                throw new \RuntimeException('Prediction was canceled');
            }

            // Still processing, wait and retry
            sleep(self::POLL_INTERVAL);
        }

        throw new \RuntimeException('Prediction timed out after ' . $maxWait . ' seconds');
    }

    /**
     * Convert image to data URL
     */
    private function imageToDataUrl(string $imagePath): string
    {
        $content = file_get_contents($imagePath);
        if ($content === false) {
            throw new \RuntimeException('Failed to read image file');
        }

        $mimeType = $this->getMimeType($imagePath);
        $base64 = base64_encode($content);

        return "data:{$mimeType};base64,{$base64}";
    }

    /**
     * Get MIME type of image
     */
    private function getMimeType(string $imagePath): string
    {
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        return $mimeTypes[$extension] ?? 'image/jpeg';
    }

    /**
     * Parse the JSON response
     */
    private function parseResponse(string $response): array
    {
        $response = trim($response);

        // Remove markdown code blocks if present
        if (str_contains($response, '```')) {
            $response = preg_replace('/^```(?:json)?\s*/i', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);
        }

        // Try to extract JSON object
        if (preg_match('/\{[\s\S]*\}/m', $response, $matches)) {
            $response = $matches[0];
        }

        $data = json_decode($response, true);

        if (!$data) {
            return $this->getDefaultMetadata();
        }

        return [
            'title' => $this->sanitizeString($data['title'] ?? ''),
            'description' => $this->sanitizeString($data['description'] ?? ''),
            'alt_text' => $this->sanitizeString(substr($data['alt_text'] ?? '', 0, 255)),
            'caption' => $this->sanitizeString(substr($data['caption'] ?? '', 0, 500)),
            'tags' => $this->sanitizeArray($data['tags'] ?? []),
            'categories' => $this->sanitizeArray($data['categories'] ?? []),
            'colors' => $this->sanitizeArray($data['colors'] ?? []),
            'dominant_color' => $this->sanitizeString($data['dominant_color'] ?? ''),
            'mood' => $this->sanitizeString($data['mood'] ?? ''),
            'style' => $this->sanitizeString($data['style'] ?? ''),
        ];
    }

    /**
     * Get default metadata structure
     */
    private function getDefaultMetadata(): array
    {
        return [
            'title' => '',
            'description' => '',
            'alt_text' => '',
            'caption' => '',
            'tags' => [],
            'categories' => [],
            'colors' => [],
            'dominant_color' => '',
            'mood' => '',
            'style' => '',
        ];
    }

    /**
     * Sanitize string
     */
    private function sanitizeString(string $value): string
    {
        return trim(strip_tags($value));
    }

    /**
     * Sanitize array of strings
     */
    private function sanitizeArray(array $values): array
    {
        return array_values(array_filter(array_map(function ($v) {
            return is_string($v) ? trim(strip_tags($v)) : '';
        }, $values)));
    }

    /**
     * Check if API is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get API key from settings
     */
    private function getApiKey(): string
    {
        // Try database setting first
        try {
            $db = \app()->getDatabase();
            $result = $db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = 'replicate_api_key'"
            );

            // Auto-create setting if it doesn't exist
            if (!$result) {
                $this->ensureSettingsExist($db);
                return '';
            }

            if (!empty($result['setting_value'])) {
                return $result['setting_value'];
            }
        } catch (\Throwable $e) {
            // Fall through to config
        }

        // Try config file
        if (function_exists('config')) {
            return config('api.replicate.key', '');
        }

        return '';
    }

    /**
     * Auto-create settings if they don't exist
     */
    private function ensureSettingsExist($db): void
    {
        try {
            // Check and create ai_provider setting
            $exists = $db->fetch("SELECT 1 FROM settings WHERE setting_key = 'ai_provider'");
            if (!$exists) {
                $db->insert('settings', [
                    'setting_key' => 'ai_provider',
                    'setting_value' => 'replicate',
                    'setting_type' => 'select',
                    'description' => 'AI provider for image analysis (claude or replicate)',
                    'is_public' => 0,
                ]);
            }

            // Check and create replicate_api_key setting
            $exists = $db->fetch("SELECT 1 FROM settings WHERE setting_key = 'replicate_api_key'");
            if (!$exists) {
                $db->insert('settings', [
                    'setting_key' => 'replicate_api_key',
                    'setting_value' => '',
                    'setting_type' => 'encrypted',
                    'description' => 'Replicate API key for LLaVA image analysis',
                    'is_public' => 0,
                ]);
            }
        } catch (\Throwable $e) {
            // Silently fail - settings will be created manually
        }
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
            // Check account by listing models
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'ignore_errors' => true,
                    'header' => [
                        'Authorization: Bearer ' . $this->apiKey,
                    ],
                ],
            ]);

            $response = @file_get_contents('https://api.replicate.com/v1/account', false, $context);

            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to API',
                ];
            }

            $data = json_decode($response, true);

            if (isset($data['username'])) {
                return [
                    'success' => true,
                    'message' => 'Replicate connected successfully',
                    'username' => $data['username'],
                ];
            }

            return [
                'success' => false,
                'error' => $data['detail'] ?? 'Invalid API key',
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
