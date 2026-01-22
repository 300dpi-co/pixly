<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Hugging Face AI Service
 *
 * Uses WD Tagger Space API for adult content tagging.
 * The Inference API doesn't deploy these models, so we use the Gradio Space directly.
 */
class HuggingFaceAIService
{
    // WD Tagger Space URL (Gradio-based)
    private const WD_TAGGER_SPACE = 'https://smilingwolf-wd-tagger.hf.space';
    // Best WD14 model for v3 dataset
    private const WD14_MODEL = 'SmilingWolf/wd-vit-large-tagger-v3';
    private const TIMEOUT = 120;

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
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }

    /**
     * Analyze an image and generate all metadata
     */
    public function analyzeImage(string $imagePath, array $existingCategories = []): array
    {
        $this->errors = [];
        $this->debugLog("=== Starting AI analysis for: {$imagePath} ===");

        if (!$this->isConfigured()) {
            $this->debugLog("ERROR: API key not configured");
            throw new \RuntimeException('Hugging Face API key not configured');
        }

        $this->debugLog("API key is configured (length: " . strlen($this->apiKey) . ")");

        if (!file_exists($imagePath)) {
            $this->debugLog("ERROR: Image file not found");
            throw new \RuntimeException('Image file not found: ' . $imagePath);
        }

        // Get tags from WD14 tagger via Gradio Space API (best for adult content)
        $this->debugLog("Calling WD14 tagger via Gradio Space API...");
        $wd14Tags = [];
        $rating = [];
        $lastError = '';

        try {
            $result = $this->getWD14TagsViaSpace($imagePath);
            $wd14Tags = $result['tags'];
            $rating = $result['rating'];
            $this->debugLog("WD14 returned " . count($wd14Tags) . " tags");
            $this->debugLog("Rating: " . json_encode($rating));
        } catch (\Throwable $e) {
            $lastError = $e->getMessage();
            $this->debugLog("WD14 tagger failed: " . $lastError);
        }

        // If WD14 failed, throw exception
        if (empty($wd14Tags)) {
            $this->debugLog("WD14 tagger failed - throwing exception");
            throw new \RuntimeException("HuggingFace WD Tagger not available: " . $lastError);
        }

        // Build metadata from WD14 tags (caption generated from tags)
        $this->debugLog("Building metadata from tags...");
        $result = $this->buildMetadata('', $wd14Tags, $existingCategories);
        $result['rating'] = $rating;
        $this->debugLog("Generated title: " . ($result['title'] ?? 'none'));
        $this->debugLog("Generated " . count($result['tags'] ?? []) . " final tags");
        $this->debugLog("=== AI analysis complete ===");

        return $result;
    }

    /**
     * Get tags from WD14 Tagger via Gradio Space API
     * This uses the HuggingFace Space directly since the model isn't deployed on the Inference API
     */
    private function getWD14TagsViaSpace(string $imagePath): array
    {
        // Step 1: Upload image to the Space
        $this->debugLog("Uploading image to WD Tagger Space...");
        $uploadedPath = $this->uploadImageToSpace($imagePath);

        if (!$uploadedPath) {
            throw new \RuntimeException('Failed to upload image to WD Tagger Space');
        }

        $this->debugLog("Image uploaded, path: " . $uploadedPath);

        // Step 2: Call the predict endpoint
        $this->debugLog("Calling predict endpoint...");
        $predictUrl = self::WD_TAGGER_SPACE . '/call/predict';

        // Prepare the request data
        // Parameters: image, model_repo, general_thresh, general_mcut, character_thresh, character_mcut
        $requestData = [
            'data' => [
                ['path' => $uploadedPath],  // Image reference
                self::WD14_MODEL,            // Model to use
                0.35,                        // General threshold
                false,                       // General MCut disabled
                0.85,                        // Character threshold
                false,                       // Character MCut disabled
            ]
        ];

        $this->debugLog("Request data: " . json_encode($requestData));

        $ch = curl_init($predictUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $this->debugLog("Predict HTTP Code: {$httpCode}");
        $this->debugLog("Predict Response: " . substr($response, 0, 500));

        if ($response === false) {
            throw new \RuntimeException("Failed to connect to WD Tagger Space: {$curlError}");
        }

        $data = json_decode($response, true);

        if (!isset($data['event_id'])) {
            $this->debugLog("No event_id in response: " . $response);
            throw new \RuntimeException('WD Tagger Space returned unexpected response');
        }

        // Step 3: Fetch results using event_id
        $eventId = $data['event_id'];
        $this->debugLog("Got event_id: {$eventId}, fetching results...");

        $resultUrl = self::WD_TAGGER_SPACE . '/call/predict/' . $eventId;

        $ch = curl_init($resultUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
            ],
        ]);

        $resultResponse = curl_exec($ch);
        $resultHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->debugLog("Result HTTP Code: {$resultHttpCode}");
        $this->debugLog("Result Response: " . substr($resultResponse, 0, 1000));

        // Parse SSE response format
        return $this->parseSpaceResponse($resultResponse);
    }

    /**
     * Upload image to the WD Tagger Space
     */
    private function uploadImageToSpace(string $imagePath): ?string
    {
        $uploadUrl = self::WD_TAGGER_SPACE . '/upload';

        // Read the image file
        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            $this->debugLog("Failed to read image file");
            return null;
        }

        // Get mime type
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';
        $filename = basename($imagePath);

        // Create multipart form data
        $boundary = '----WebKitFormBoundary' . bin2hex(random_bytes(16));

        $body = "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"files\"; filename=\"{$filename}\"\r\n";
        $body .= "Content-Type: {$mimeType}\r\n\r\n";
        $body .= $imageData . "\r\n";
        $body .= "--{$boundary}--\r\n";

        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Content-Type: multipart/form-data; boundary=' . $boundary,
                'Authorization: Bearer ' . $this->apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $this->debugLog("Upload HTTP Code: {$httpCode}");
        $this->debugLog("Upload Response: " . $response);

        if ($response === false || $httpCode !== 200) {
            $this->debugLog("Upload failed: " . ($curlError ?: "HTTP {$httpCode}"));
            return null;
        }

        $data = json_decode($response, true);

        // Response format: [{"path": "...", "url": "...", ...}]
        if (is_array($data) && !empty($data[0]['path'])) {
            return $data[0]['path'];
        }

        // Alternative response format
        if (is_array($data) && !empty($data['path'])) {
            return $data['path'];
        }

        $this->debugLog("Unexpected upload response format");
        return null;
    }

    /**
     * Parse Gradio Space SSE response
     */
    private function parseSpaceResponse(string $response): array
    {
        $tags = [];
        $rating = [];

        // Parse SSE format - look for "event: complete" and "data: ..."
        $lines = explode("\n", $response);
        $isComplete = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === 'event: complete') {
                $isComplete = true;
                continue;
            }

            if ($isComplete && str_starts_with($line, 'data: ')) {
                $jsonStr = substr($line, 6);
                $data = json_decode($jsonStr, true);

                if (is_array($data) && count($data) >= 4) {
                    // Format: [sorted_tags_string, rating_dict, character_dict, general_dict]
                    $sortedTagsStr = $data[0] ?? '';
                    $ratingDict = $data[1] ?? [];
                    $generalDict = $data[3] ?? [];

                    // Parse rating
                    $rating = $ratingDict;

                    // Parse general tags (dict with tag => score)
                    if (is_array($generalDict)) {
                        foreach ($generalDict as $tagName => $score) {
                            if ($score > 0.35) {
                                $tags[] = [
                                    'name' => $this->cleanTag($tagName),
                                    'score' => $score,
                                ];
                            }
                        }
                    }

                    // Sort by score descending
                    usort($tags, fn($a, $b) => $b['score'] <=> $a['score']);

                    $this->debugLog("Parsed " . count($tags) . " tags from Space response");
                    break;
                }
            }

            // Check for error event
            if (str_starts_with($line, 'event: error')) {
                $this->debugLog("Space returned error event");
                throw new \RuntimeException('WD Tagger Space returned an error');
            }
        }

        return [
            'tags' => $tags,
            'rating' => $rating,
        ];
    }

    /**
     * Clean WD14 tag format
     */
    private function cleanTag(string $tag): string
    {
        // WD14 uses underscores, convert to spaces
        $tag = str_replace('_', ' ', $tag);
        // Remove parentheses content like (medium)
        $tag = preg_replace('/\s*\([^)]*\)/', '', $tag);
        return trim($tag);
    }

    /**
     * Extract tags from caption when WD14 is unavailable
     */
    private function extractTagsFromCaption(string $caption): array
    {
        $caption = strtolower($caption);
        $tags = [];

        // Keywords to look for in adult content captions
        $keywords = [
            'woman', 'women', 'girl', 'lady', 'female', 'model',
            'blonde', 'brunette', 'redhead', 'black hair', 'brown hair',
            'bikini', 'lingerie', 'underwear', 'dress', 'naked', 'nude', 'topless',
            'bedroom', 'bathroom', 'outdoor', 'beach', 'pool', 'couch', 'bed',
            'sitting', 'standing', 'lying', 'posing', 'smiling',
            'sexy', 'beautiful', 'gorgeous', 'stunning', 'attractive',
            'curvy', 'slim', 'petite', 'athletic', 'fit',
            'breasts', 'legs', 'ass', 'body',
            'young', 'mature', 'milf',
            'selfie', 'photo', 'picture',
        ];

        foreach ($keywords as $keyword) {
            if (strpos($caption, $keyword) !== false) {
                $tags[] = $keyword;
            }
        }

        // Also extract nouns from caption using simple word extraction
        $words = preg_split('/\s+/', $caption);
        foreach ($words as $word) {
            $word = preg_replace('/[^a-z]/', '', $word);
            if (strlen($word) > 4 && !in_array($word, $tags)) {
                // Skip common stop words
                $stopWords = ['with', 'this', 'that', 'from', 'have', 'been', 'were', 'what', 'when', 'where', 'which', 'there', 'their', 'about'];
                if (!in_array($word, $stopWords)) {
                    $tags[] = $word;
                }
            }
        }

        return array_slice(array_unique($tags), 0, 20);
    }

    /**
     * Build metadata from WD14 tags
     */
    private function buildMetadata(string $caption, array $wd14Tags, array $existingCategories): array
    {
        // Extract tag names from WD14, or extract from caption if empty
        $tagNames = array_column($wd14Tags, 'name');

        if (empty($tagNames) && !empty($caption)) {
            $tagNames = $this->extractTagsFromCaption($caption);
            error_log("Extracted " . count($tagNames) . " tags from caption");
        }

        // Generate title from caption (first sentence, cleaned up)
        $title = $this->generateTitle($caption, $tagNames);

        // Generate description
        $description = $this->generateDescription($caption, $tagNames);

        // Categorize based on tags
        $categories = $this->matchCategories($tagNames, $existingCategories);

        // Filter and prioritize tags for adult content
        $finalTags = $this->prioritizeTags($tagNames);

        // Extract colors (WD14 sometimes includes color tags)
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
     * Generate sexy title from caption and tags
     */
    private function generateTitle(string $caption, array $tags): string
    {
        // Priority body/appearance tags for title
        $bodyTags = ['big breasts', 'huge breasts', 'large breasts', 'small breasts', 'medium breasts',
            'big ass', 'huge ass', 'thick thighs', 'curvy', 'petite', 'slim', 'athletic',
            'busty', 'voluptuous'];
        $hairTags = ['blonde', 'brunette', 'redhead', 'black hair', 'brown hair', 'pink hair', 'blue hair'];
        $poseTags = ['standing', 'sitting', 'lying', 'bent over', 'on knees', 'spreading'];

        $body = '';
        $hair = '';
        $pose = '';

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

        // Build title
        $parts = array_filter([$body, $hair]);
        if (!empty($parts)) {
            $adjectives = ['Stunning', 'Gorgeous', 'Sexy', 'Hot', 'Beautiful', 'Naughty', 'Sensual'];
            $adj = $adjectives[array_rand($adjectives)];
            return $adj . ' ' . implode(' ', $parts) . ' Babe';
        }

        // Fallback: use first part of caption
        $sentences = preg_split('/[.!?]/', $caption);
        $first = trim($sentences[0] ?? 'Beautiful Model');
        return ucwords(substr($first, 0, 60));
    }

    /**
     * Generate enticing description
     */
    private function generateDescription(string $caption, array $tags): string
    {
        // Take relevant tags for description
        $relevantTags = array_slice($tags, 0, 5);
        $tagStr = implode(', ', $relevantTags);

        // Clean caption
        $cleanCaption = trim($caption);
        if (strlen($cleanCaption) > 150) {
            $cleanCaption = substr($cleanCaption, 0, 147) . '...';
        }

        if (!empty($tagStr)) {
            return $cleanCaption . ' Features: ' . $tagStr . '.';
        }

        return $cleanCaption;
    }

    /**
     * Generate short flirty caption
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
                // Check if tag matches or contains category name
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
        // High priority adult tags
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

        // Merge prioritized first, then others
        return array_merge($prioritized, array_slice($other, 0, 5));
    }

    /**
     * Extract color information from tags
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
        return !empty($this->apiKey);
    }

    /**
     * Get API key from settings
     */
    private function getApiKey(): string
    {
        try {
            $db = \app()->getDatabase();
            $result = $db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = 'huggingface_api_key'"
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
            // Fall through
        }

        return '';
    }

    /**
     * Auto-create settings if they don't exist
     */
    private function ensureSettingsExist($db): void
    {
        try {
            // Check and create huggingface_api_key setting
            $exists = $db->fetch("SELECT 1 FROM settings WHERE setting_key = 'huggingface_api_key'");
            if (!$exists) {
                $db->insert('settings', [
                    'setting_key' => 'huggingface_api_key',
                    'setting_value' => '',
                    'setting_type' => 'encrypted',
                    'setting_group' => 'api_keys',
                    'description' => 'Hugging Face API key for Florence-2 and WD14 tagger',
                    'is_public' => 0,
                ]);
            }
        } catch (\Throwable $e) {
            // Silently fail - settings will be created manually
        }
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
            // Test with whoami call to verify API key
            $ch = curl_init('https://huggingface.co/api/whoami-v2');
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

            if ($response === false || $httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to Hugging Face API (HTTP ' . $httpCode . ')',
                ];
            }

            $data = json_decode($response, true);

            if (isset($data['name'])) {
                // Also check if WD Tagger Space is reachable
                $spaceCheck = $this->checkSpaceAvailability();

                return [
                    'success' => true,
                    'message' => 'Connected as: ' . $data['name'] . ($spaceCheck ? ' | WD Tagger Space: OK' : ' | WD Tagger Space: Unavailable'),
                    'username' => $data['name'],
                    'space_available' => $spaceCheck,
                ];
            }

            return [
                'success' => false,
                'error' => $data['error'] ?? 'Invalid API key',
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if WD Tagger Space is available
     */
    private function checkSpaceAvailability(): bool
    {
        try {
            $ch = curl_init(self::WD_TAGGER_SPACE);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_NOBODY => true,  // HEAD request
            ]);

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 200;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
