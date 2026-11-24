<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Plain ellipsis after excerpts (as on the Oxygen site).
 */
add_filter('excerpt_more', fn () => '&hellip;');

/**
 * Let menu links carry the theme's nav classes.
 */
add_filter('nav_menu_link_attributes', function (array $atts, $item, $args) {
    if (($args->theme_location ?? '') === 'primary_navigation') {
        $atts['class'] = trim(($atts['class'] ?? '').' nav-link');
    }

    return $atts;
}, 10, 3);

/**
 * Photo cards (Our Bars): render the group's background as a real <img> behind
 * the caption instead of an inline CSS background. Smush's lazy-loader rewrites
 * WordPress's background-image style into a broken URL, but handles <img> fine.
 */
add_filter('render_block_data', function (array $block) {
    if (($block['blockName'] ?? '') !== 'core/group'
        || ! str_contains($block['attrs']['className'] ?? '', 'jl-photo-card')
        || str_contains($block['attrs']['className'] ?? '', 'jl-photo-card__')) {
        return $block;
    }

    $url = $block['attrs']['style']['background']['backgroundImage']['url'] ?? null;

    if ($url) {
        $block['attrs']['jlPhoto'] = $url;
        unset($block['attrs']['style']['background']);
    }

    return $block;
});

add_filter('render_block_core/group', function (string $html, array $block) {
    $url = $block['attrs']['jlPhoto'] ?? null;

    if (! $url) {
        return $html;
    }

    $id = attachment_url_to_postid($url);
    $img = $id
        ? wp_get_attachment_image($id, 'large', false, ['class' => 'jl-photo-card__img', 'alt' => '', 'loading' => 'lazy'])
        : sprintf('<img class="jl-photo-card__img" src="%s" alt="" loading="lazy">', esc_url($url));

    // Insert the image as the first child of the card.
    return preg_replace('/^(\s*<div\b[^>]*>)/', '$1'.$img, $html, 1);
}, 10, 2);
