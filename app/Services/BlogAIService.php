<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Blog AI Service
 *
 * Handles AI-powered content generation for blog posts.
 */
class BlogAIService
{
    private string $apiKey;
    private string $endpoint;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('api.deepseek.key', '');
        $this->endpoint = config('api.deepseek.endpoint', 'https://api.deepseek.com');
        $this->model = config('api.deepseek.model', 'deepseek-chat');
    }

    /**
     * Generate a complete blog post
     */
    public function generatePost(string $topic, string $tone = 'professional', string $length = 'medium', string $keywords = ''): array
    {
        $wordCounts = [
            'short' => '500-700',
            'medium' => '1000-1200',
            'long' => '1800-2200',
        ];

        $targetWords = $wordCounts[$length] ?? '1000-1200';

        $prompt = $this->buildGenerationPrompt($topic, $tone, $targetWords, $keywords);

        $response = $this->callAPI($prompt);

        return $this->parseGeneratedContent($response, $topic);
    }

    /**
     * Improve existing content
     */
    public function improveContent(string $content, string $action = 'improve'): array
    {
        $prompts = [
            'improve' => "Improve the following blog content. Make it more engaging, clear, and well-structured while maintaining the original message. Fix any grammatical errors and improve the flow:\n\n{$content}\n\nProvide only the improved content, no explanations.",

            'expand' => "Expand the following content with more details, examples, and explanations. Add relevant information to make it more comprehensive. Double the length while keeping it focused:\n\n{$content}\n\nProvide only the expanded content.",

            'summarize' => "Summarize the following content into a concise version that retains the key points. Reduce it to about 30% of the original length:\n\n{$content}\n\nProvide only the summarized content.",

            'seo' => "Optimize the following content for SEO. Add relevant keywords naturally, improve headings for better structure, and make it more search-engine friendly while maintaining readability:\n\n{$content}\n\nProvide only the optimized content.",
        ];

        $prompt = $prompts[$action] ?? $prompts['improve'];

        $response = $this->callAPI($prompt);

        return [
            'content' => $this->cleanContent($response),
        ];
    }

    /**
     * Generate SEO metadata
     */
    public function generateMetadata(string $title, string $content): array
    {
        $prompt = "Based on this blog post, generate SEO metadata:

Title: {$title}
Content (excerpt): " . substr(strip_tags($content), 0, 500) . "

Provide in this exact JSON format:
{
    \"meta_title\": \"SEO optimized title under 60 chars\",
    \"meta_description\": \"Compelling meta description under 160 chars\",
    \"keywords\": \"keyword1, keyword2, keyword3, keyword4, keyword5\"
}

Return ONLY the JSON, no other text.";

        $response = $this->callAPI($prompt);

        try {
            $data = json_decode($response, true);
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            // Fall through to defaults
        }

        return [
            'meta_title' => substr($title, 0, 60),
            'meta_description' => substr(strip_tags($content), 0, 160),
            'keywords' => '',
        ];
    }

    /**
     * Generate tags from content
     */
    public function generateTags(string $title, string $content): array
    {
        $prompt = "Analyze this blog post and suggest 5-8 relevant tags (single words or short phrases).

Title: {$title}
Content: " . substr(strip_tags($content), 0, 1000) . "

Return ONLY a comma-separated list of tags, nothing else. Example: technology, artificial intelligence, machine learning, data science";

        $response = $this->callAPI($prompt);

        $tags = array_filter(array_map('trim', explode(',', $response)));
        return array_slice($tags, 0, 8);
    }

    /**
     * Build generation prompt
     */
    private function buildGenerationPrompt(string $topic, string $tone, string $wordCount, string $keywords): string
    {
        $toneInstructions = [
            'professional' => 'Use a professional, authoritative tone. Be informative and credible.',
            'casual' => 'Use a casual, conversational tone. Be friendly and approachable.',
            'friendly' => 'Use a warm, friendly tone. Be encouraging and supportive.',
            'authoritative' => 'Use an expert, authoritative tone. Demonstrate deep knowledge.',
            'humorous' => 'Use a light, humorous tone where appropriate. Keep it engaging and fun.',
        ];

        $toneGuide = $toneInstructions[$tone] ?? $toneInstructions['professional'];

        $keywordGuide = $keywords ? "\n\nIncorporate these SEO keywords naturally: {$keywords}" : '';

        return "Write a comprehensive blog post about: {$topic}

REQUIREMENTS:
- Word count: {$wordCount} words
- {$toneGuide}
- Use proper HTML formatting with <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em> tags
- Include an engaging introduction that hooks the reader
- Use subheadings (h2, h3) to organize content
- Include practical examples or tips where relevant
- End with a conclusion or call-to-action
- Make it original, valuable, and SEO-friendly{$keywordGuide}

After the blog content, provide on separate lines:
TITLE: [engaging title under 70 chars]
EXCERPT: [compelling excerpt under 200 chars]
META_DESC: [SEO meta description under 160 chars]
TAGS: [5-8 relevant tags, comma separated]

Write the blog post now:";
    }

    /**
     * Parse generated content
     */
    private function parseGeneratedContent(string $response, string $fallbackTitle): array
    {
        $result = [
            'title' => $fallbackTitle,
            'content' => '',
            'excerpt' => '',
            'meta_description' => '',
            'tags' => '',
        ];

        // Try to extract metadata from the end
        $lines = explode("\n", $response);
        $contentLines = [];
        $metadataStarted = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^TITLE:\s*(.+)$/i', $line, $matches)) {
                $result['title'] = trim($matches[1]);
                $metadataStarted = true;
            } elseif (preg_match('/^EXCERPT:\s*(.+)$/i', $line, $matches)) {
                $result['excerpt'] = trim($matches[1]);
                $metadataStarted = true;
            } elseif (preg_match('/^META_DESC:\s*(.+)$/i', $line, $matches)) {
                $result['meta_description'] = trim($matches[1]);
                $metadataStarted = true;
            } elseif (preg_match('/^TAGS:\s*(.+)$/i', $line, $matches)) {
                $result['tags'] = trim($matches[1]);
                $metadataStarted = true;
            } elseif (!$metadataStarted) {
                $contentLines[] = $line;
            }
        }

        $content = implode("\n", $contentLines);
        $result['content'] = $this->cleanContent($content);

        // Generate excerpt if not provided
        if (empty($result['excerpt']) && !empty($result['content'])) {
            $text = strip_tags($result['content']);
            $result['excerpt'] = substr($text, 0, 200);
            if (strlen($text) > 200) {
                $result['excerpt'] = substr($result['excerpt'], 0, strrpos($result['excerpt'], ' ')) . '...';
            }
        }

        return $result;
    }

    /**
     * Clean generated content
     */
    private function cleanContent(string $content): string
    {
        // Remove markdown code blocks if present
        $content = preg_replace('/```html?\s*/i', '', $content);
        $content = preg_replace('/```\s*/', '', $content);

        // Ensure proper HTML structure
        $content = trim($content);

        // Convert markdown-style headings if present
        $content = preg_replace('/^##\s+(.+)$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^###\s+(.+)$/m', '<h3>$1</h3>', $content);

        // Convert markdown bold/italic if present
        $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $content);

        // Wrap plain paragraphs
        $lines = explode("\n\n", $content);
        $processed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Skip if already has block-level tags
            if (preg_match('/^<(h[1-6]|p|ul|ol|li|div|blockquote)/i', $line)) {
                $processed[] = $line;
            } else {
                $processed[] = '<p>' . $line . '</p>';
            }
        }

        return implode("\n\n", $processed);
    }

    /**
     * Call the AI API
     */
    private function callAPI(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('AI API key is not configured');
        }

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert blog writer and content strategist. You create engaging, well-structured, SEO-optimized content.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ];

        $ch = curl_init($this->endpoint . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 120,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('API request failed: ' . $error);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            throw new \Exception('API error: ' . ($errorData['error']['message'] ?? 'Unknown error'));
        }

        $data = json_decode($response, true);

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid API response format');
        }

        return $data['choices'][0]['message']['content'];
    }
}
