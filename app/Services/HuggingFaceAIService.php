<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Hugging Face AI Service
 *
 * Uses Florence-2 for captions and WD14 Tagger for NSFW tags.
 * Cost-effective solution for adult image galleries.
 */
class HuggingFaceAIService
{
    private const FLORENCE_MODEL = 'microsoft/Florence-2-large';
    private const WD14_MODEL = 'SmilingWolf/wd-v1-4-vit-tagger-v2';
    private const API_URL = 'https://api-inference.huggingface.co/models/';
    private const TIMEOUT = 120;

    private string $apiKey;
    private array $errors = [];

    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
    }

    /**
     * Analyze an image and generate all metadata
     */
    public function analyzeImage(string $imagePath, array $existingCategories = []): array
    {
        $this->errors = [];

        if (!$this->isConfigured()) {
            throw new \RuntimeException('Hugging Face API key not configured');
        }

        if (!file_exists($imagePath)) {
            throw new \RuntimeException('Image file not found: ' . $imagePath);
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            throw new \RuntimeException('Failed to read image file');
        }

        // Get caption from Florence-2
        $caption = $this->getFlorence2Caption($imageData);

        // Get tags from WD14
        $wd14Tags = $this->getWD14Tags($imageData);

        // Build metadata from combined results
        return $this->buildMetadata($caption, $wd14Tags, $existingCategories);
    }

    /**
     * Get caption from Florence-2 model
     */
    private function getFlorence2Caption(string $imageData): string
    {
        $response = $this->callInferenceAPI(self::FLORENCE_MODEL, $imageData, [
            'task' => '<MORE_DETAILED_CAPTION>'
        ]);

        if (isset($response[0]['generated_text'])) {
            return $response[0]['generated_text'];
        }

        // Try alternative response format
        if (isset($response['generated_text'])) {
            return $response['generated_text'];
        }

        // Fallback for different model response
        if (is_string($response)) {
            return $response;
        }

        $this->errors[] = 'Florence-2 returned unexpected format';
        return '';
    }

    /**
     * Get tags from WD14 Tagger
     */
    private function getWD14Tags(string $imageData): array
    {
        $response = $this->callInferenceAPI(self::WD14_MODEL, $imageData);

        if (!is_array($response)) {
            $this->errors[] = 'WD14 returned unexpected format';
            return [];
        }

        // WD14 returns array of [label => score]
        $tags = [];
        foreach ($response as $item) {
            if (isset($item['label']) && isset($item['score'])) {
                // Only include tags with confidence > 0.35
                if ($item['score'] > 0.35) {
                    $tags[] = [
                        'name' => $this->cleanTag($item['label']),
                        'score' => $item['score']
                    ];
                }
            }
        }

        // Sort by score descending
        usort($tags, fn($a, $b) => $b['score'] <=> $a['score']);

        return $tags;
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
     * Call Hugging Face Inference API
     */
    private function callInferenceAPI(string $model, string $imageData, array $parameters = []): mixed
    {
        $url = self::API_URL . $model;

        // For image models, send raw image data
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/octet-stream',
        ];

        if (!empty($parameters)) {
            $headers[] = 'X-Parameters: ' . json_encode($parameters);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => self::TIMEOUT,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
                'content' => $imageData,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new \RuntimeException("Failed to connect to Hugging Face API for {$model}");
        }

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            // Check if model is loading
            if (stripos($data['error'], 'loading') !== false) {
                // Wait and retry once
                sleep(20);
                $response = @file_get_contents($url, false, $context);
                if ($response === false) {
                    throw new \RuntimeException("Model {$model} is still loading, try again later");
                }
                $data = json_decode($response, true);
                if (isset($data['error'])) {
                    throw new \RuntimeException("Hugging Face API error: " . $data['error']);
                }
            } else {
                throw new \RuntimeException("Hugging Face API error: " . $data['error']);
            }
        }

        return $data;
    }

    /**
     * Build metadata from Florence-2 caption and WD14 tags
     */
    private function buildMetadata(string $caption, array $wd14Tags, array $existingCategories): array
    {
        // Extract tag names
        $tagNames = array_column($wd14Tags, 'name');

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
            // Test with a simple whoami call
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'ignore_errors' => true,
                    'header' => 'Authorization: Bearer ' . $this->apiKey,
                ],
            ]);

            $response = @file_get_contents('https://huggingface.co/api/whoami-v2', false, $context);

            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to Hugging Face API',
                ];
            }

            $data = json_decode($response, true);

            if (isset($data['name'])) {
                return [
                    'success' => true,
                    'message' => 'Connected as: ' . $data['name'],
                    'username' => $data['name'],
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
}
