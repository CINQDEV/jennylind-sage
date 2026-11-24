<?php

/**
 * Pull videos from the Jenny Lind Facebook page into Jenny Sessions as DRAFTS.
 * Nothing goes on the site until you publish the ones you want.
 *
 * Uses the Facebook connection already set up in "Import Facebook Events".
 *
 * Run from the Site folder:
 *   php wp-content/themes/jennylind/bin/import-facebook-videos.php --list
 *   php wp-content/themes/jennylind/bin/import-facebook-videos.php
 *
 * Options:
 *   --list               Only show the videos found; change nothing.
 *   --limit=50           How many of the latest videos to look at (default 50).
 *   --since=2025-01-01   Only videos posted on or after this date.
 *   --page=<id>          Which Facebook page (default: the one named "Jenny…").
 *
 * For each new video it creates a draft session with the video link, the date
 * posted, the Facebook text as the write-up, the video's thumbnail as the
 * featured image, and (when there was one that day) the gig it came from.
 * Videos already imported are skipped, so it's safe to run again later.
 */

if (PHP_SAPI !== 'cli') {
    exit("Run this from the command line.\n");
}

@set_time_limit(0);

$args = [];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $arg, $m)) {
        $args[$m[1]] = $m[2] ?? true;
    }
}
$list_only = isset($args['list']);
$limit = max(1, (int) ($args['limit'] ?? 50));
$since = isset($args['since']) ? strtotime($args['since']) : null;

$site = dirname(__DIR__, 4);
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'jennylindhastings.test';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/';
define('WP_USE_THEMES', false);
require $site.'/wp-load.php';
require_once ABSPATH.'wp-admin/includes/media.php';
require_once ABSPATH.'wp-admin/includes/file.php';
require_once ABSPATH.'wp-admin/includes/image.php';

const GRAPH = 'https://graph.facebook.com/v21.0/';

// Running without a logged-in user, so stop WordPress escaping & in titles.
kses_remove_filters();

/* Which page, and its access token (saved by Import Facebook Events) ---------- */

$pages = get_option('ife_fb_user_pages') ?: [];
if (! $pages) {
    exit("No Facebook pages are connected. In wp-admin › Facebook Import › Settings, connect Facebook again, then re-run this.\n");
}

$page = null;
if (! empty($args['page'])) {
    $page = $pages[$args['page']] ?? null;
} else {
    foreach ($pages as $candidate) {
        if (stripos($candidate['name'] ?? '', 'jenny') !== false) {
            $page = $candidate;
            break;
        }
    }
    $page ??= reset($pages);
}

if (! $page || empty($page['access_token'])) {
    echo "Couldn't pick a Facebook page. Connected pages:\n";
    foreach ($pages as $id => $p) {
        echo "  {$id}  {$p['name']}\n";
    }
    exit("Run again with --page=<id>.\n");
}

echo "Facebook page: {$page['name']} ({$page['id']})\n";

/* Fetch the videos ------------------------------------------------------------ */

function jl_graph(string $url): array
{
    $response = wp_remote_get($url, ['timeout' => 30]);
    if (is_wp_error($response)) {
        exit('Could not reach Facebook: '.$response->get_error_message()."\n");
    }
    $body = json_decode(wp_remote_retrieve_body($response), true) ?: [];
    if (! empty($body['error'])) {
        $msg = $body['error']['message'] ?? 'unknown error';
        exit("Facebook said: {$msg}\n\nIf it mentions an expired or invalid token, reconnect Facebook in wp-admin › Facebook Import › Settings and run this again.\n");
    }

    return $body;
}

$fields = 'id,title,description,created_time,permalink_url,length,picture,thumbnails{uri,width,is_preferred}';
$url = GRAPH.rawurlencode($page['id']).'/videos?'.http_build_query([
    'fields' => $fields,
    'limit' => min(50, $limit),
    'access_token' => $page['access_token'],
]);

$videos = [];
while ($url && count($videos) < $limit) {
    $body = jl_graph($url);
    foreach ($body['data'] ?? [] as $video) {
        if ($since && strtotime($video['created_time']) < $since) {
            $url = null;
            break;
        }
        $videos[] = $video;
    }
    $url = $url ? ($body['paging']['next'] ?? null) : null;
}
$videos = array_slice($videos, 0, $limit);

if (! $videos) {
    exit("No videos found on the page.\n");
}

/* Helpers --------------------------------------------------------------------- */

function jl_already_imported(string $fb_id): bool
{
    return (bool) get_posts([
        'post_type' => 'jl_session',
        // Include binned ones, so videos you've removed don't come back.
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'numberposts' => 1,
        'fields' => 'ids',
        'meta_key' => 'session_fb_id',
        'meta_value' => $fb_id,
    ]);
}

/** A short title from the Facebook title, or the first line of the post. */
function jl_video_title(array $video, DateTimeImmutable $when): string
{
    $title = trim($video['title'] ?? '');
    if ($title === '') {
        $line = trim(strtok((string) ($video['description'] ?? ''), "\n") ?: '');
        $title = $line !== '' ? wp_trim_words($line, 9, '…') : '';
    }

    return $title !== '' ? $title : 'Live at the Jenny – '.wp_date('j F Y', $when->getTimestamp());
}

/** The gig on the day it was posted (or the evening before). */
function jl_matching_event(DateTimeImmutable $when): ?int
{
    if (! function_exists('eo_get_events')) {
        return null;
    }

    $events = eo_get_events([
        'event_start_after' => $when->modify('-30 hours')->format('Y-m-d H:i'),
        'event_start_before' => $when->format('Y-m-d H:i'),
        'showpastevents' => true,
        'numberposts' => 1,
        'orderby' => 'eventstart',
        'order' => 'DESC',
    ]);

    return $events ? (int) $events[0]->ID : null;
}

function jl_best_thumbnail(array $video): ?string
{
    $thumbs = $video['thumbnails']['data'] ?? [];
    usort($thumbs, fn ($a, $b) => [! empty($b['is_preferred']), $b['width'] ?? 0] <=> [! empty($a['is_preferred']), $a['width'] ?? 0]);

    return $thumbs[0]['uri'] ?? ($video['picture'] ?? null);
}

/* List or import ------------------------------------------------------------- */

$tz = wp_timezone();
$created = $skipped = 0;

foreach ($videos as $video) {
    $when = (new DateTimeImmutable($video['created_time']))->setTimezone($tz);
    $event = jl_matching_event($when);
    $title = jl_video_title($video, $when);
    // No title on Facebook: name it after the band from that night's gig.
    if ($event && str_starts_with($title, 'Live at the Jenny')) {
        $title = html_entity_decode(get_the_title($event), ENT_QUOTES, 'UTF-8');
    }
    $minutes = isset($video['length']) ? sprintf('%d:%02d', $video['length'] / 60, $video['length'] % 60) : '?';
    $done = jl_already_imported($video['id']);

    printf("  %s  %-6s %s%s\n", $when->format('Y-m-d'), $minutes, $title, $done ? '  (already imported)' : '');

    if ($list_only) {
        continue;
    }
    if ($done) {
        $skipped++;
        continue;
    }

    $link = $video['permalink_url'] ?? '';
    $link = str_starts_with($link, 'http') ? $link : 'https://www.facebook.com'.$link;

    $post_id = wp_insert_post([
        'post_type' => 'jl_session',
        'post_status' => 'draft',
        'post_title' => $title,
        'post_content' => ! empty($video['description'])
            ? "<!-- wp:paragraph -->\n<p>".nl2br(esc_html(trim($video['description'])))."</p>\n<!-- /wp:paragraph -->"
            : '',
    ], true);

    if (is_wp_error($post_id)) {
        echo '    could not create: '.$post_id->get_error_message()."\n";
        continue;
    }

    update_post_meta($post_id, 'session_youtube', $link);
    update_post_meta($post_id, 'session_date', $when->format('Ymd'));
    update_post_meta($post_id, 'session_fb_id', $video['id']);
    if ($event) {
        update_post_meta($post_id, 'session_event', $event);
        echo '    linked to the gig: '.html_entity_decode(get_the_title($event), ENT_QUOTES)."\n";
    }

    if ($thumb = jl_best_thumbnail($video)) {
        $tmp = download_url($thumb, 30);
        if (! is_wp_error($tmp)) {
            $attachment = media_handle_sideload(['name' => "facebook-video-{$video['id']}.jpg", 'tmp_name' => $tmp], $post_id, $title);
            if (is_wp_error($attachment)) {
                @unlink($tmp);
            } else {
                set_post_thumbnail($post_id, $attachment);
            }
        }
    }

    $created++;
}

if ($list_only) {
    echo "\n".count($videos)." videos found. Nothing changed (--list). Run without --list to import them as drafts.\n";
    exit;
}

echo "\nImported {$created} as drafts".($skipped ? ", skipped {$skipped} already imported" : '').".\n";
echo "Next: wp-admin › Jenny Sessions › Drafts. Publish the great ones (add a style, tidy the title); bin the rest.\n";
