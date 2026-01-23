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

        // Build category list with descriptions for better matching
        $categoryNames = array_column($existingCategories, 'name');
        $categoryList = !empty($categoryNames) ? implode(', ', $categoryNames) : 'Amateur, Professional, Solo, Artistic, Outdoor';

        // Build detailed category guide
        $categoryGuide = $this->buildCategoryGuide($categoryNames);

        // The prompt - engineered for dual-market SEO (India + USA)
        $prompt = "You are an SEO metadata generator for an adult image gallery targeting INDIA and USA markets.

CRITICAL RULES:
1. Output in ENGLISH + Hindi Romaji terms for SEO
2. Output ACTUAL descriptive content, never placeholders or boolean values
3. Be specific and descriptive about what you see in the image

TARGET MARKETS - DUAL SEO STRATEGY:
For INDIAN subjects, include Hindi Romaji search terms that Indian users search for:
- Relationship terms: desi, bhabhi, aunty, maal, pataka, mast, jawani
- Regional: punjabi, bengali, tamil, telugu, marathi, gujarati, mallu, north indian, south indian
- Clothing: saree, blouse, petticoat, salwar, dupatta, lehenga, kurti
- Body: moti, gori, sanwli, busty bhabhi, thick aunty

For USA market, include Western terms:
- Standard: milf, hot wife, exotic, curvy, busty, thicc, amateur, homemade
- Cross-cultural: indian milf, desi beauty, exotic indian, brown beauty

CATEGORY SELECTION GUIDE:
{$categoryGuide}

OUTPUT FORMAT (JSON):
{
  \"title\": \"Creative English title with one Hindi Romaji term if Indian (5-10 words)\",
  \"description\": \"2-3 English sentences describing the subject, pose, setting, mood\",
  \"tags\": [\"20 tags mixing: ethnicity, Hindi Romaji terms (if Indian), Western terms, body type, hair, clothing, pose, setting\"],
  \"categories\": [\"1-3 best matching categories from: {$categoryList}\"],
  \"alt_text\": \"Brief 10-15 word English description\"
}

EXAMPLE 1 - Indian woman in saree:
{
  \"title\": \"Sexy Desi Bhabhi Strips Her Red Saree\",
  \"description\": \"A gorgeous Indian bhabhi teases as she slowly removes her traditional red saree. Her curvy figure and seductive eyes promise an unforgettable experience.\",
  \"tags\": [\"desi\", \"bhabhi\", \"indian\", \"saree\", \"milf\", \"aunty\", \"curvy\", \"busty\", \"exotic\", \"north indian\", \"hot wife\", \"traditional\", \"seductive\", \"brown\", \"homemade\", \"maal\", \"gori\", \"thick\", \"amateur\", \"bedroom\"],
  \"categories\": [\"Indian\", \"MILF\", \"Amateur\"],
  \"alt_text\": \"Curvy Indian bhabhi removing red saree seductively in bedroom\"
}

EXAMPLE 2 - South Indian woman:
{
  \"title\": \"Hot Mallu Aunty Shows Her Curves\",
  \"description\": \"A voluptuous South Indian mallu aunty flaunts her incredible curves. Her dark skin glows as she poses confidently in her bedroom.\",
  \"tags\": [\"mallu\", \"aunty\", \"south indian\", \"tamil\", \"desi\", \"indian\", \"curvy\", \"thick\", \"busty\", \"milf\", \"exotic\", \"brown beauty\", \"sanwli\", \"hot wife\", \"amateur\", \"homemade\", \"moti\", \"bedroom\", \"mature\", \"seductive\"],
  \"categories\": [\"Indian\", \"Mature\", \"BBW\"],
  \"alt_text\": \"Voluptuous South Indian mallu aunty posing in bedroom\"
}

EXAMPLE 3 - Non-Indian (Caucasian):
{
  \"title\": \"Blonde Bombshell in Lace Lingerie\",
  \"description\": \"A stunning blonde beauty poses in delicate white lace lingerie. Her piercing blue eyes and perfect figure create an irresistible allure.\",
  \"tags\": [\"caucasian\", \"blonde\", \"lingerie\", \"white lace\", \"blue eyes\", \"busty\", \"curvy\", \"babe\", \"model\", \"glamour\", \"sexy\", \"seductive\", \"bedroom\", \"intimate\", \"beautiful\", \"hot\", \"american\", \"milf\", \"sensual\", \"elegant\"],
  \"categories\": [\"Lingerie\", \"Blonde\", \"Babe\"],
  \"alt_text\": \"Blonde woman in white lace lingerie posing seductively\"
}

Now analyze this image. If subject appears INDIAN, use Hindi Romaji terms + Western terms for dual SEO. Pick 1-3 BEST categories.";

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
     * Build category guide with descriptions for better AI matching
     */
    private function buildCategoryGuide(array $categoryNames): string
    {
        // Map category names to helpful descriptions
        $categoryDescriptions = [
            'amateur' => 'Amateur: Selfies, home photos, non-professional shots, casual poses',
            'anal' => 'Anal: Anal sex, anal play, butt-focused content',
            'asian' => 'Asian: Asian women, Japanese, Chinese, Korean, Thai models',
            'ass' => 'Ass: Focus on buttocks, rear views, booty shots',
            'babe' => 'Babe: Beautiful young women, attractive models, hotties',
            'bbw' => 'BBW: Big beautiful women, plus-size, curvy/thick body types',
            'big tits' => 'Big Tits: Large breasts, busty women, big boobs focus',
            'bikini' => 'Bikini: Swimwear, beach wear, poolside in bikinis',
            'blonde' => 'Blonde: Blonde hair color as main feature',
            'blowjob' => 'Blowjob: Oral sex, sucking, mouth action',
            'bondage' => 'Bondage: BDSM, tied up, restraints, ropes',
            'brunette' => 'Brunette: Brown/dark hair color as main feature',
            'celebrity' => 'Celebrity: Famous people, celebrities, known personalities',
            'cosplay' => 'Cosplay: Costumes, anime characters, fantasy outfits',
            'couple' => 'Couple: Two people together, romantic pairs',
            'ebony' => 'Ebony: Black women, African American models',
            'feet' => 'Feet: Foot focus, toes, barefoot shots',
            'fetish' => 'Fetish: Specific kinks, unusual interests, niche content',
            'glamour' => 'Glamour: Professional beauty shots, elegant, high-fashion style',
            'hardcore' => 'Hardcore: Explicit sex acts, penetration, intense action',
            'hentai' => 'Hentai: Anime/cartoon porn, drawn/illustrated content',
            'interracial' => 'Interracial: Mixed race couples, different ethnicities together',
            'latina' => 'Latina: Hispanic/Latin women, Spanish-speaking origin',
            'lesbian' => 'Lesbian: Women with women, girl-on-girl content',
            'lingerie' => 'Lingerie: Underwear, bras, panties, intimate apparel',
            'mature' => 'Mature: Older women, MILFs, experienced ladies (35+)',
            'milf' => 'MILF: Attractive older women, mothers, mature hotties',
            'nude' => 'Nude: Naked, no clothes, full nudity',
            'outdoor' => 'Outdoor: Outside locations, nature, beach, public places',
            'petite' => 'Petite: Small/slim body type, tiny women',
            'pornstar' => 'Pornstar: Known adult performers, professional porn actresses',
            'pov' => 'POV: Point of view shots, first-person perspective',
            'public' => 'Public: Public places, exhibitionism, outdoor exposure',
            'pussy' => 'Pussy: Vagina focus, spread shots, close-ups',
            'redhead' => 'Redhead: Red/ginger hair color as main feature',
            'selfie' => 'Selfie: Self-taken photos, mirror shots, phone pics',
            'solo' => 'Solo: Single person, alone, masturbation',
            'teen' => 'Teen: Young adults 18-19, youthful appearance',
            'threesome' => 'Threesome: Three people together, group of three',
            'vintage' => 'Vintage: Retro, classic, old-style photos',
        ];

        $guide = [];
        foreach ($categoryNames as $name) {
            $nameLower = strtolower($name);
            if (isset($categoryDescriptions[$nameLower])) {
                $guide[] = "- " . $categoryDescriptions[$nameLower];
            } else {
                // Generic description for unknown categories
                $guide[] = "- {$name}: Content related to {$name}";
            }
        }

        if (empty($guide)) {
            return "Pick the category that best matches the image content.";
        }

        return implode("\n", $guide);
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
        $altText = $result['alt_text'] ?? substr($description, 0, 125);

        // Get categories from AI (supports both old "category" string and new "categories" array)
        $aiCategories = $result['categories'] ?? [];
        if (empty($aiCategories) && !empty($result['category'])) {
            $aiCategories = [$result['category']]; // Backwards compatibility
        }
        if (is_string($aiCategories)) {
            $aiCategories = [$aiCategories];
        }

        // Ensure tags is an array
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        // Clean up tags
        $tags = array_filter($tags, fn($t) => !empty($t) && strlen($t) > 1);
        $tags = array_map(fn($t) => strtolower(trim($t)), $tags);
        $tags = array_unique($tags);

        // Match AI categories to existing categories (max 3)
        $categories = [];
        foreach ($aiCategories as $aiCat) {
            if (count($categories) >= 3) break; // Max 3 categories
            if (empty($aiCat)) continue;

            $aiCatLower = strtolower(trim($aiCat));
            $matched = false;

            // First try exact match (case-insensitive)
            foreach ($existingCategories as $cat) {
                if (strtolower($cat['name']) === $aiCatLower) {
                    if (!in_array($cat['name'], $categories)) {
                        $categories[] = $cat['name'];
                        $this->debugLog("Category exact match: {$cat['name']}");
                    }
                    $matched = true;
                    break;
                }
            }

            // If no exact match, try partial match
            if (!$matched) {
                foreach ($existingCategories as $cat) {
                    $catLower = strtolower($cat['name']);
                    if (str_contains($catLower, $aiCatLower) || str_contains($aiCatLower, $catLower)) {
                        if (!in_array($cat['name'], $categories)) {
                            $categories[] = $cat['name'];
                            $this->debugLog("Category partial match: {$cat['name']} (AI said: {$aiCat})");
                        }
                        $matched = true;
                        break;
                    }
                }
            }

            if (!$matched) {
                $this->debugLog("No category match found for: {$aiCat}");
            }
        }

        $this->debugLog("Final categories (" . count($categories) . "): " . implode(', ', $categories));

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
            'tags' => array_slice(array_values($tags), 0, 20),
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
