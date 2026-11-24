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
 * Upcoming events, in start-date order.
 *
 * An event stays listed until it has finished, not just until it starts.
 * Many events (e.g. Facebook imports) have no real finish time – their end
 * equals their start – so those stay listed until the end of their day.
 *
 * @return \WP_Post[]
 */
function events(int $count, int $offset = 0): array
{
    // Everything from today onwards, including events that have started.
    $query = new \WP_Query([
        'post_type' => 'event',
        'posts_per_page' => $offset + $count + 30,
        'no_found_rows' => true,
        'suppress_filters' => false,
        'showpastevents' => true,
        'event_end_after' => 'today',
        'orderby' => 'eventstart',
        'order' => 'ASC',
    ]);

    $live = array_values(array_filter($query->posts, fn (\WP_Post $event) => event_is_live($event)));

    return array_slice($live, $offset, $count);
}

/**
 * Whether an event occurrence is still on (not yet finished).
 */
function event_is_live(\WP_Post $event): bool
{
    if (! function_exists('eo_get_the_start')) {
        return true;
    }

    $start = (int) eo_get_the_start('U', $event->ID, $event->occurrence_id ?? null);
    $end = (int) eo_get_the_end('U', $event->ID, $event->occurrence_id ?? null);

    // No real finish time: treat it as running until the end of that day.
    if ($end <= $start) {
        $end = (new \DateTimeImmutable('@'.$start))
            ->setTimezone(wp_timezone())
            ->setTime(23, 59, 59)
            ->getTimestamp();
    }

    return $end > time();
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
