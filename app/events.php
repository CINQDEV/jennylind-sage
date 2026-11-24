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

/**
 * Today's date in the site's timezone (Y-m-d).
 */
function today(): string
{
    return wp_date('Y-m-d');
}

/**
 * Y-m-d start date of an event occurrence.
 */
function event_date(\WP_Post $event): string
{
    return event_start($event, 'Y-m-d');
}

/**
 * Whether an event is a regular (weekly) night rather than a one-off gig:
 * the same title has another occurrence within four weeks either side.
 */
function event_is_regular(\WP_Post $event): bool
{
    static $titles = null;

    if ($titles === null) {
        $titles = [];
        $query = new \WP_Query([
            'post_type' => 'event',
            'posts_per_page' => 200,
            'no_found_rows' => true,
            'suppress_filters' => false,
            'showpastevents' => true,
            'event_start_after' => '-28 days',
            'event_start_before' => '+28 days',
        ]);

        foreach ($query->posts as $post) {
            $key = strtolower(trim(wp_strip_all_tags($post->post_title)));
            $titles[$key] = ($titles[$key] ?? 0) + 1;
        }
    }

    return ($titles[strtolower(trim(wp_strip_all_tags($event->post_title)))] ?? 0) > 1;
}

/**
 * Label for the week an event falls in: "This week", "Next week" or "Week of 14 Dec".
 */
function event_week_label(\WP_Post $event): string
{
    $tz = wp_timezone();
    $start = new \DateTimeImmutable(event_date($event), $tz);
    $monday = fn (\DateTimeImmutable $d) => $d->modify('monday this week')->setTime(0, 0);
    $weeks = (int) round(($monday($start)->getTimestamp() - $monday(new \DateTimeImmutable('today', $tz))->getTimestamp()) / WEEK_IN_SECONDS);

    return match (true) {
        $weeks <= 0 => __('This week', 'jennylind'),
        $weeks === 1 => __('Next week', 'jennylind'),
        default => sprintf(__('Week of %s', 'jennylind'), wp_date('j M', $monday($start)->getTimestamp())),
    };
}

/**
 * The regular weekly nights coming up, one per title, in weekday order.
 *
 * @return array<int, array{day: string, title: string, time: string}>
 */
function regular_nights(int $lookahead = 40): array
{
    $nights = [];

    foreach (events($lookahead) as $event) {
        if (! event_is_regular($event)) {
            continue;
        }

        $title = html_entity_decode(wp_strip_all_tags(get_the_title($event)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (isset($nights[$title])) {
            continue;
        }

        $nights[$title] = [
            'order' => (int) event_start($event, 'N'),
            'day' => event_start($event, 'D'),
            'days' => event_start($event, 'l').'s',
            'title' => $title,
            'time' => event_time($event),
            'url' => get_permalink($event),
        ];
    }

    usort($nights, fn ($a, $b) => $a['order'] <=> $b['order']);

    return array_values($nights);
}

/**
 * Short start time: "7pm" or "8.30pm".
 */
function event_time(\WP_Post $event): string
{
    return event_start($event, 'i') === '00'
        ? event_start($event, 'ga')
        : event_start($event, 'g.ia');
}

/**
 * Links for adding the events feed to a calendar app.
 *
 * @return array{feed: string, webcal: string, google: string}
 */
function calendar_links(): array
{
    $feed = function_exists('eo_get_events_feed') ? eo_get_events_feed() : home_url('/feed/eo-events/');
    $webcal = preg_replace('#^https?://#', 'webcal://', $feed);

    return [
        'feed' => $feed,
        'webcal' => $webcal,
        'google' => 'https://calendar.google.com/calendar/r?cid='.rawurlencode($webcal),
    ];
}
