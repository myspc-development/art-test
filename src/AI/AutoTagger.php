<?php
namespace ArtPulse\AI;

use WP_Post;

/**
 * Automatically tag posts using the OpenAI API.
 */
class AutoTagger
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('save_post', [self::class, 'maybe_tag'], 20, 3);
        add_action('add_meta_boxes', [self::class, 'add_suggested_tags_box']);
        add_action('save_post', [self::class, 'apply_suggested_tags'], 20, 3);
    }

    /**
     * Analyze post content and add suggested tags.
     */
    public static function maybe_tag(int $post_id, WP_Post $post, bool $update): void
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        $types = ['artpulse_event', 'artpulse_artist', 'post'];
        if (!in_array($post->post_type, $types, true)) {
            return;
        }

        $opts = get_option('artpulse_settings', []);
        $key  = $opts['openai_api_key'] ?? '';
        if (!$key) {
            return;
        }

        $content = trim(wp_strip_all_tags($post->post_content));
        if (!$content) {
            return;
        }

        $lang = self::detect_language($content);
        $prompts = [
            'en' => 'You are an art domain expert. Analyze the following text and suggest three descriptive tags focusing on artistic genre, medium, and cultural theme. Return the tags as a comma-separated list.',
            'es' => 'Sugiere tres etiquetas para este contenido artístico (género, medio o estilo).',
            'ru' => 'Предложите три тега для этого художественного контента (жанр, техника, стиль).',
            'fr' => 'Suggérez trois tags pour ce contenu artistique (genre, technique, style).',
            'de' => 'Schlage drei Tags für diesen künstlerischen Inhalt vor (Genre, Technik, Stil).',
            'zh' => '为此艺术内容推荐三个标签（题材、媒介或风格）。'
        ];
        $prompt = ($prompts[$lang] ?? $prompts['en']) . ' ' . $content;

        switch ($post->post_type) {
            case 'artpulse_artist':
                $prompt = 'Provide three tags about the artist’s style, influence, and medium. Text: ' . $content;
                break;
            case 'artpulse_event':
                $prompt = 'Suggest tags for this art event based on theme, audience, and genre. Text: ' . $content;
                break;
        }

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens'  => 20,
                'temperature' => 0.5,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $text = $data['choices'][0]['message']['content'] ?? '';
        if (!$text) {
            return;
        }

        $tags = array_filter(array_map('trim', explode(',', strip_tags($text))));
        if ($tags) {
            update_post_meta($post_id, '_suggested_tags', $tags);
        }
    }

    /**
     * Basic language detection.
     */
    private static function detect_language(string $text): string
    {
        if (preg_match('/[áéíóúñ]/i', $text)) {
            return 'es';
        }
        if (preg_match('/[а-яё]/iu', $text)) {
            return 'ru';
        }
        if (preg_match('/[àâçéèêëîïôûùüÿœ]/i', $text)) {
            return 'fr';
        }
        if (preg_match('/[äöüß]/i', $text)) {
            return 'de';
        }
        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
            return 'zh';
        }
        return 'en';
    }

    /**
     * Add meta box showing AI suggested tags.
     */
    public static function add_suggested_tags_box(): void
    {
        foreach (['artpulse_event', 'artpulse_artist', 'post'] as $pt) {
            add_meta_box('artpulse_suggested_tags', 'AI Suggested Tags', [self::class, 'render_suggested_tags_box'], $pt, 'side', 'high');
        }
    }

    /**
     * Render the suggested tags meta box.
     */
    public static function render_suggested_tags_box(WP_Post $post): void
    {
        $tags = get_post_meta($post->ID, '_suggested_tags', true);
        if ($tags && is_array($tags)) {
            echo '<p>Suggested Tags: ' . esc_html(implode(', ', $tags)) . '</p>';
            echo '<button type="submit" name="apply_suggested_tags" class="button">Apply Suggested Tags</button>';
        } else {
            echo '<p>No tags suggested yet.</p>';
        }
    }

    /**
     * Apply suggested tags if admin approves on save.
     */
    public static function apply_suggested_tags(int $post_id, WP_Post $post, bool $update): void
    {
        if (!isset($_POST['apply_suggested_tags'])) {
            return;
        }

        $tags = get_post_meta($post_id, '_suggested_tags', true);
        if ($tags && is_array($tags)) {
            wp_set_post_tags($post_id, $tags, true);
            delete_post_meta($post_id, '_suggested_tags');
        }
    }
}
