<?php
/**
 * Example: output Article + Breadcrumb + FAQ schema on single posts via wp_head.
 *
 * Drop this (and src/SchemaJsonLd.php) into your theme, or require the class
 * from a small must-use plugin.
 */

require_once __DIR__ . '/../src/SchemaJsonLd.php';

add_action('wp_head', function () {
    if (!is_singular('post')) {
        return;
    }

    $post_id = get_the_ID();

    $schema = new SchemaJsonLd();
    $schema
        ->addArticle(
            get_the_title($post_id),
            get_permalink($post_id),
            get_the_date('c', $post_id),
            get_the_author_meta('display_name', get_post_field('post_author', $post_id)),
            get_the_post_thumbnail_url($post_id, 'full') ?: '',
            get_the_excerpt($post_id),
            get_the_modified_date('c', $post_id)
        )
        ->addBreadcrumb(array(
            array('name' => 'Home', 'url' => home_url('/')),
            array('name' => 'Blog', 'url' => home_url('/blog/')),
            array('name' => get_the_title($post_id)),
        ));

    // Optional: turn a post's H2 sections into FAQ schema.
    // $faqs = my_theme_extract_faqs($post_id); // returns [['question'=>..,'answer'=>..], ...]
    // if (!empty($faqs)) { $schema->addFaq($faqs); }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — JSON-LD is machine data, escaped by json_encode.
    echo $schema->render();
});
