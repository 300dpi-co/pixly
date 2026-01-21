<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * DeepSeek API Service
 *
 * Handles communication with DeepSeek API for text and DeepInfra for vision.
 */
class DeepSeekService
{
    private string $apiKey;
    private string $endpoint;
    private string $model;
    private string $visionEndpoint;
    private string $visionModel;
    private string $visionKey;
    private array $lastError = [];

    public function __construct()
    {
        $config = config('api.deepseek');
        $this->apiKey = $config['key'] ?? '';
        $this->endpoint = $config['endpoint'] ?? 'https://api.deepseek.com';
        $this->model = $config['model'] ?? 'deepseek-chat';
        $this->visionEndpoint = $config['vision_endpoint'] ?? 'https://api.deepinfra.com/v1/openai';
        $this->visionModel = $config['vision_model'] ?? 'deepseek-ai/Janus-Pro-7B';
        $this->visionKey = $config['vision_key'] ?? '';
    }

    /**
     * Check if API is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Check if Vision API is configured
     */
    public function isVisionConfigured(): bool
    {
        return !empty($this->visionKey);
    }

    /**
     * Analyze an image and generate metadata
     */
    public function analyzeImage(string $imagePath, ?string $existingTitle = null): ?array
    {
        if (!file_exists($imagePath)) {
            $this->lastError = ['message' => 'Image file not found'];
            return null;
        }

        // If vision is configured, use actual image analysis
        if ($this->isVisionConfigured()) {
            return $this->analyzeImageWithVision($imagePath);
        }

        // Fallback to text-based generation from filename
        if (!$this->isConfigured()) {
            $this->lastError = ['message' => 'No API configured'];
            return null;
        }

        return $this->generateMetadataFromTitle($imagePath, $existingTitle);
    }

    /**
     * Analyze image using DeepInfra Janus-Pro vision model
     */
    private function analyzeImageWithVision(string $imagePath): ?array
    {
        // Read and encode image
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        $prompt = <<<PROMPT
Analyze this image and provide the following in JSON format:
{
    "title": "SEO-optimized title (max 60 characters, descriptive and engaging)",
    "alt_text": "Accessible alt text describing the image (max 125 characters)",
    "description": "SEO meta description (max 160 characters, compelling and keyword-rich)",
    "tags": ["tag1", "tag2", "tag3", "tag4", "tag5", "tag6", "tag7", "tag8"],
    "category": "suggested category from: Nature, Technology, Art, Travel, People, Animals, Food, Abstract",
    "safety_score": 0.95
}

Rules:
- Title should be catchy and SEO-friendly
- Tags should be relevant keywords for search
- Safety score: 1.0 = completely safe, 0.0 = inappropriate
- Be concise but descriptive
- Return ONLY valid JSON, no markdown or extra text
PROMPT;

        $payload = [
            'model' => $this->visionModel,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$imageData}"
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];

        $ch = curl_init($this->visionEndpoint . '/chat/completions');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->visionKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 120,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = ['message' => 'Vision API cURL error: ' . $error];
            $this->logApiCall($this->visionModel, [], null, $error);
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $this->lastError = $data['error'] ?? ['message' => 'Vision API error: HTTP ' . $httpCode];
            $this->logApiCall($this->visionModel, [], $data, 'HTTP ' . $httpCode);
            return null;
        }

        $this->logApiCall($this->visionModel, [], $data);

        return $this->parseJsonResponse($data['choices'][0]['message']['content'] ?? '');
    }

    /**
     * Generate metadata from title/filename (fallback when vision not available)
     */
    private function generateMetadataFromTitle(string $imagePath, ?string $existingTitle = null): ?array
    {
        $filename = pathinfo($imagePath, PATHINFO_FILENAME);
        $title = $existingTitle ?: $this->cleanFilename($filename);

        $prompt = <<<PROMPT
Based on this image filename/title: "{$title}"

Generate SEO-optimized metadata in JSON format:
{
    "title": "SEO-optimized title (max 60 characters, descriptive and engaging)",
    "alt_text": "Accessible alt text describing the image (max 125 characters)",
    "description": "SEO meta description (max 160 characters, compelling and keyword-rich)",
    "tags": ["tag1", "tag2", "tag3", "tag4", "tag5", "tag6", "tag7", "tag8"],
    "category": "suggested category from: Nature, Technology, Art, Travel, People, Animals, Food, Abstract",
    "safety_score": 1.0
}

Rules:
- Make educated guesses about the image content based on the filename
- Title should be catchy and SEO-friendly
- Tags should be relevant keywords that someone might search for
- Be creative but reasonable in your assumptions
- Return ONLY valid JSON, no markdown or extra text
PROMPT;

        $response = $this->chat([
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ]);

        if (!$response) {
            return null;
        }

        return $this->parseJsonResponse($response['choices'][0]['message']['content'] ?? '');
    }

    /**
     * Parse JSON response from AI
     */
    private function parseJsonResponse(string $content): ?array
    {
        // Clean up response - remove markdown code blocks if present
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/```$/i', '', $content);
        $content = trim($content);

        $parsed = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->lastError = ['message' => 'Failed to parse AI response: ' . json_last_error_msg()];
            return null;
        }

        return $parsed;
    }

    /**
     * Clean filename for better AI context
     */
    private function cleanFilename(string $filename): string
    {
        $clean = str_replace(['_', '-'], ' ', $filename);
        $clean = preg_replace('/\s*\d+$/', '', $clean);
        return ucwords(trim($clean));
    }

    /**
     * Generate tags for given text/title
     */
    public function generateTags(string $text, int $count = 10): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $response = $this->chat([
            [
                'role' => 'user',
                'content' => "Generate {$count} SEO-relevant tags for this content: \"{$text}\"\n\nReturn only a JSON array of tags, nothing else. Example: [\"tag1\", \"tag2\", \"tag3\"]"
            ]
        ]);

        if (!$response) {
            return [];
        }

        $content = $response['choices'][0]['message']['content'] ?? '[]';
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/```$/i', '', $content);

        $tags = json_decode(trim($content), true);

        return is_array($tags) ? $tags : [];
    }

    /**
     * Send chat completion request to DeepSeek
     */
    private function chat(array $messages): ?array
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];

        $ch = curl_init($this->endpoint . '/v1/chat/completions');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = ['message' => 'cURL error: ' . $error];
            $this->logApiCall($this->model, $payload, null, $error);
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $this->lastError = $data['error'] ?? ['message' => 'API error: HTTP ' . $httpCode];
            $this->logApiCall($this->model, $payload, $data, 'HTTP ' . $httpCode);
            return null;
        }

        $this->logApiCall($this->model, $payload, $data);

        return $data;
    }

    /**
     * Log API call to database
     */
    private function logApiCall(string $model, array $request, ?array $response, ?string $error = null): void
    {
        try {
            $db = app()->getDatabase();
            $db->insert('api_logs', [
                'api_name' => 'deepseek',
                'endpoint' => str_contains($model, 'Janus') ? '/vision' : '/chat',
                'request_data' => json_encode(['model' => $model]),
                'response_code' => $response ? 200 : 0,
                'response_data' => $response ? json_encode(['usage' => $response['usage'] ?? null]) : null,
                'tokens_used' => $response['usage']['total_tokens'] ?? null,
                'error_message' => $error,
            ]);
        } catch (\Exception $e) {
            // Silently fail logging
        }
    }

    /**
     * Get last error
     */
    public function getLastError(): array
    {
        return $this->lastError;
    }
}
