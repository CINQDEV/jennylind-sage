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
