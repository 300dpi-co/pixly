<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Claude AI Service
 *
 * Handles AI-powered image analysis using Claude's vision capabilities.
 * Generates titles, descriptions, tags, categories, and alt text automatically.
 */
class ClaudeAIService
{
    private const API_ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';
    private const DEFAULT_MODEL = 'claude-sonnet-4-20250514';
    private const TIMEOUT = 60;

    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
        $this->model = self::DEFAULT_MODEL;
    }

    /**
     * Analyze an image and generate all metadata
     *
     * @param string $imagePath Path to the image file
     * @param array $existingCategories Available categories to choose from
     * @return array Generated metadata
     */
    public function analyzeImage(string $imagePath, array $existingCategories = []): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Claude AI API key not configured');
        }

        if (!file_exists($imagePath)) {
            throw new \RuntimeException('Image file not found: ' . $imagePath);
        }

        // Read and encode the image
        $imageData = $this->encodeImage($imagePath);
        $mimeType = $this->getMimeType($imagePath);

        // Build the prompt
        $prompt = $this->buildAnalysisPrompt($existingCategories);

        // Call Claude API
        $response = $this->callAPI($imageData, $mimeType, $prompt);

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

        $imageData = $this->encodeImage($imagePath);
        $mimeType = $this->getMimeType($imagePath);

        $prompt = "Analyze this image and generate exactly {$count} relevant tags.
Return ONLY a JSON array of lowercase tags, no other text.
Example: [\"sunset\", \"beach\", \"ocean\", \"waves\", \"orange sky\"]";

        $response = $this->callAPI($imageData, $mimeType, $prompt);

        try {
            $tags = json_decode($response, true);
            if (is_array($tags)) {
                return array_slice($tags, 0, $count);
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

        $imageData = $this->encodeImage($imagePath);
        $mimeType = $this->getMimeType($imagePath);

        $prompt = "Generate a concise, descriptive alt text for this image for accessibility purposes.
The alt text should:
- Be under 125 characters
- Describe the key visual elements
- Not start with 'Image of' or 'Picture of'
Return ONLY the alt text, no quotes or other text.";

        return trim($this->callAPI($imageData, $mimeType, $prompt));
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

Return your analysis in this exact JSON format:
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

Return ONLY valid JSON, no other text or explanation.";
    }

    /**
     * Call the Claude API
     */
    private function callAPI(string $imageBase64, string $mimeType, string $prompt): string
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mimeType,
                                'data' => $imageBase64,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => self::TIMEOUT,
                'ignore_errors' => true,
                'header' => [
                    'Content-Type: application/json',
                    'x-api-key: ' . $this->apiKey,
                    'anthropic-version: ' . self::API_VERSION,
                ],
                'content' => json_encode($payload),
            ],
        ]);

        $response = @file_get_contents(self::API_ENDPOINT, false, $context);

        if ($response === false) {
            throw new \RuntimeException('Failed to connect to Claude API');
        }

        $data = json_decode($response, true);

        if (!$data) {
            throw new \RuntimeException('Invalid response from Claude API');
        }

        if (isset($data['error'])) {
            throw new \RuntimeException('Claude API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        // Extract the text content from Claude's response
        if (isset($data['content'][0]['text'])) {
            return $data['content'][0]['text'];
        }

        throw new \RuntimeException('Unexpected response format from Claude API');
    }

    /**
     * Parse the JSON response from Claude
     */
    private function parseResponse(string $response): array
    {
        // Try to extract JSON from the response
        $response = trim($response);

        // Remove markdown code blocks if present
        if (str_starts_with($response, '```')) {
            $response = preg_replace('/^```(?:json)?\s*/i', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);
        }

        $data = json_decode($response, true);

        if (!$data) {
            // Return default structure if parsing fails
            return $this->getDefaultMetadata();
        }

        // Normalize and validate the data
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
     * Encode image to base64
     */
    private function encodeImage(string $imagePath): string
    {
        $content = file_get_contents($imagePath);
        if ($content === false) {
            throw new \RuntimeException('Failed to read image file');
        }

        return base64_encode($content);
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
                "SELECT setting_value FROM settings WHERE setting_key = 'claude_api_key'"
            );
            if ($result && !empty($result['setting_value'])) {
                return $result['setting_value'];
            }
        } catch (\Throwable $e) {
            // Fall through to config
        }

        // Try config file
        if (function_exists('config')) {
            return config('api.claude.key', '');
        }

        return '';
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
            // Simple test with a text-only request
            $payload = [
                'model' => $this->model,
                'max_tokens' => 50,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Say "API connection successful" and nothing else.',
                    ],
                ],
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'timeout' => 10,
                    'ignore_errors' => true,
                    'header' => [
                        'Content-Type: application/json',
                        'x-api-key: ' . $this->apiKey,
                        'anthropic-version: ' . self::API_VERSION,
                    ],
                    'content' => json_encode($payload),
                ],
            ]);

            $response = @file_get_contents(self::API_ENDPOINT, false, $context);

            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to API',
                ];
            }

            $data = json_decode($response, true);

            if (isset($data['error'])) {
                return [
                    'success' => false,
                    'error' => $data['error']['message'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => true,
                'message' => 'Claude AI connected successfully',
                'model' => $this->model,
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
