<?php

/**
 * Event helpers (Event Organiser).
 *
 * The Oxygen repeaters used a plain WP_Query on the `event` post type and let
 * Event Organiser order the results by start date. These helpers do the same,
 * so the lists match what Oxygen showed.
 */

namespace App;

/**
 * Upcoming events, in Event Organiser's order.
 *
 * @return \WP_Post[]
 */
function events(int $count, int $offset = 0): array
{
    $query = new \WP_Query([
        'post_type' => 'event',
        'posts_per_page' => $count,
        'offset' => $offset,
        'no_found_rows' => true,
        'suppress_filters' => false,
    ]);

    return $query->posts;
}

/**
 * Formatted start date/time of an event occurrence.
 */
function event_start(\WP_Post $event, string $format): string
{
    if (! function_exists('eo_get_the_start')) {
        return '';
    }

    return (string) eo_get_the_start($format, $event->ID, $event->occurrence_id ?? null);
}

/**
 * Plain-text excerpt for an event.
 */
function event_excerpt(\WP_Post $event, int $words = 40): string
{
    $text = has_excerpt($event) ? $event->post_excerpt : strip_shortcodes($event->post_content);

    return html_entity_decode(wp_trim_words(wp_strip_all_tags($text), $words), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
