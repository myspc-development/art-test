<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\SEO\JsonLdGenerator;

/**
 * Output Open Graph and Twitter meta tags with custom descriptions.
 */
function ap_output_seo_meta(): void
{
    if (!is_singular()) {
        return;
    }

    global $post;
    $title = get_post_meta($post->ID, 'ap_meta_title', true) ?: get_the_title($post);

    $desc = get_post_meta($post->ID, 'ap_meta_description', true);
    if (!$desc) {
        $desc = $post->post_excerpt ?: wp_trim_words(strip_tags($post->post_content), 30);
    }
    $desc = wp_strip_all_tags($desc);

    $image = get_post_meta($post->ID, 'ap_meta_image_url', true);
    if (!$image) {
        $thumb = get_post_thumbnail_id($post);
        if ($thumb) {
            $image = wp_get_attachment_url($thumb);
        }
    }

    $url = get_permalink($post);

    if ($post->post_status !== 'publish') {
        echo '<meta name="robots" content="noindex" />' . "\n";
    }

    if (is_page(['login', 'dashboard'])) {
        echo '<meta name="robots" content="noindex" />' . "\n";
    }

    echo '<link rel="canonical" href="' . esc_url($url) . '" />' . "\n";

    echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
    }

    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($desc) . '" />' . "\n";
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
    }

    JsonLdGenerator::output();

    if (function_exists('ap_page_has_shortcode') && ap_page_has_shortcode('ap_event_directory')) {
        global $wp_query;
        $ids = wp_list_pluck($wp_query->posts, 'ID');
        if ($ids) {
            $schema = JsonLdGenerator::directory_schema($ids);
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }
    }
}
add_action('wp_head', 'ap_output_seo_meta');
