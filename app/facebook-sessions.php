<?php

/**
 * Jenny Sessions from Facebook and Instagram, picked with a hashtag.
 *
 * Put #JennySessions in the caption of a Facebook or Instagram video and it becomes a
 * published Jenny Session on the website: named after the band from that
 * night's gig, with the post text as the write-up and the video's thumbnail.
 * Videos without the tag are ignored.
 *
 * Runs on its own twice a day (WordPress cron), or straight away from the
 * Site folder with:
 *   php wp-content/themes/jennylind/bin/import-facebook-videos.php
 *
 * Uses the Facebook connection saved by "Import Facebook Events".
 */

namespace App;

const SESSIONS_TAG = '#JennySessions';
const GRAPH = 'https://graph.facebook.com/v21.0/';

/** Does this text carry the #JennySessions tag (any capitalisation, with or without the "s")? */
function has_sessions_tag(string $text): bool
{
    return (bool) preg_match('/#jennysessions?\b/i', $text);
}

/** The text without the #JennySessions tag. */
function strip_sessions_tag(string $text): string
{
    return trim(preg_replace('/\s*#jennysessions?\b/i', '', $text));
}

/** The connected Facebook page (the one named "Jenny…") and its token, or null. */
function facebook_page(?string $page_id = null): ?array
{
    $pages = get_option('ife_fb_user_pages') ?: [];

    if ($page_id) {
        return $pages[$page_id] ?? null;
    }
    foreach ($pages as $page) {
        if (stripos($page['name'] ?? '', 'jenny') !== false) {
            return $page;
        }
    }

    return $pages ? reset($pages) : null;
}

/** The session already made from this Facebook video (including binned ones), if any. */
function facebook_video_session(string $fb_id): ?\WP_Post
{
    $posts = get_posts([
        'post_type' => 'jl_session',
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'numberposts' => 1,
        'meta_key' => 'session_fb_id',
        'meta_value' => $fb_id,
    ]);

    return $posts[0] ?? null;
}

/** The gig on the day it was posted (or the evening before). */
function gig_before(\DateTimeImmutable $when): ?int
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

/**
 * Look through the page's latest videos and turn tagged ones into sessions.
 *
 * Options: limit (videos to look at, default 50), all (ignore the tag; import
 * as drafts), list (report only), page (Facebook page id).
 *
 * @return string[] log lines
 */
function import_facebook_sessions(array $o = []): array
{
    $limit = max(1, (int) ($o['limit'] ?? 50));
    $all = ! empty($o['all']);
    $list_only = ! empty($o['list']);
    $log = [];

    $page = facebook_page($o['page'] ?? null);
    if (! $page || empty($page['access_token'])) {
        return ['No Facebook page connected. Reconnect Facebook in wp-admin › Facebook Import › Settings.'];
    }

    $url = GRAPH.rawurlencode($page['id']).'/videos?'.http_build_query([
        'fields' => 'id,title,description,created_time,permalink_url,length,picture,embeddable,published,thumbnails{uri,width,is_preferred}',
        'limit' => min(50, $limit),
        'access_token' => $page['access_token'],
    ]);

    $videos = [];
    while ($url && count($videos) < $limit) {
        $response = wp_remote_get($url, ['timeout' => 30]);
        if (is_wp_error($response)) {
            return ['Could not reach Facebook: '.$response->get_error_message()];
        }
        $body = json_decode(wp_remote_retrieve_body($response), true) ?: [];
        if (! empty($body['error'])) {
            return ['Facebook said: '.($body['error']['message'] ?? 'unknown error').' – if it mentions the token, reconnect Facebook in wp-admin › Facebook Import › Settings.'];
        }
        array_push($videos, ...($body['data'] ?? []));
        $url = $body['paging']['next'] ?? null;
    }
    $videos = array_slice($videos, 0, $limit);

    require_once ABSPATH.'wp-admin/includes/media.php';
    require_once ABSPATH.'wp-admin/includes/file.php';
    require_once ABSPATH.'wp-admin/includes/image.php';

    // Imports run without a logged-in user: stop WordPress escaping & in titles.
    kses_remove_filters();

    $tz = wp_timezone();
    $created = 0;

    foreach ($videos as $video) {
        $text = trim(($video['title'] ?? '')."\n".($video['description'] ?? ''));
        $when = (new \DateTimeImmutable($video['created_time']))->setTimezone($tz);
        $day = $when->format('Y-m-d');

        if (! $all && ! has_sessions_tag($text)) {
            continue;
        }
        if ($existing = facebook_video_session($video['id'])) {
            // Tagged after it was binned (e.g. by the clear-out): bring it back.
            if (! $all && $existing->post_status === 'trash' && ! $list_only) {
                wp_untrash_post($existing->ID);
                wp_update_post(['ID' => $existing->ID, 'post_status' => 'publish']);
                update_post_meta($existing->ID, 'session_tagged', 1);
                $log[] = "{$day}  restored from the bin: ".html_entity_decode($existing->post_title, ENT_QUOTES);
            }
            continue;
        }

        $link = $video['permalink_url'] ?? '';
        $link = str_starts_with($link, 'http') ? $link : 'https://www.facebook.com'.$link;

        if ((isset($video['embeddable']) && ! $video['embeddable']) || (isset($video['published']) && ! $video['published'])
            || facebook_embeddable($link) === false) {
            $log[] = "{$day}  can't play on the website – check it's a public post that allows embedding: {$link}";
            continue;
        }

        $event = gig_before($when);
        $title = strip_sessions_tag(trim($video['title'] ?? ''));
        if ($title === '' && $event) {
            $title = html_entity_decode(get_the_title($event), ENT_QUOTES, 'UTF-8');
        }
        if ($title === '') {
            $line = strip_sessions_tag(trim(strtok((string) ($video['description'] ?? ''), "\n") ?: ''));
            $title = $line !== '' ? wp_trim_words($line, 9, '…') : 'Live at the Jenny – '.wp_date('j F Y', $when->getTimestamp());
        }

        if ($list_only) {
            $log[] = "{$day}  would add: {$title}";
            continue;
        }

        $description = strip_sessions_tag((string) ($video['description'] ?? ''));
        $post_id = wp_insert_post([
            'post_type' => 'jl_session',
            // Tagged = picked, so publish. --all imports for review as drafts.
            'post_status' => $all ? 'draft' : 'publish',
            'post_title' => $title,
            'post_date' => min($when, new \DateTimeImmutable('now', $tz))->format('Y-m-d H:i:s'),
            'post_content' => $description !== ''
                ? "<!-- wp:paragraph -->\n<p>".nl2br(esc_html($description))."</p>\n<!-- /wp:paragraph -->"
                : '',
        ], true);

        if (is_wp_error($post_id)) {
            $log[] = "{$day}  could not add {$title}: ".$post_id->get_error_message();
            continue;
        }

        update_post_meta($post_id, 'session_youtube', $link);
        update_post_meta($post_id, 'session_date', $when->format('Ymd'));
        update_post_meta($post_id, 'session_fb_id', $video['id']);
        if (! $all) {
            update_post_meta($post_id, 'session_tagged', 1);
        }
        if ($event) {
            update_post_meta($post_id, 'session_event', $event);
        }

        $thumbs = $video['thumbnails']['data'] ?? [];
        usort($thumbs, fn ($a, $b) => [! empty($b['is_preferred']), $b['width'] ?? 0] <=> [! empty($a['is_preferred']), $a['width'] ?? 0]);
        if ($thumb = $thumbs[0]['uri'] ?? ($video['picture'] ?? null)) {
            $tmp = download_url($thumb, 30);
            if (! is_wp_error($tmp)) {
                $attachment = media_handle_sideload(['name' => "facebook-video-{$video['id']}.jpg", 'tmp_name' => $tmp], $post_id, $title);
                is_wp_error($attachment) ? @unlink($tmp) : set_post_thumbnail($post_id, $attachment);
            }
        }

        $created++;
        $log[] = "{$day}  added: {$title}".($event ? '' : ' (no gig that night – check the name)');
    }

    if (! $list_only) {
        $log[] = 'Facebook: '.($created ? "{$created} new session(s) ".($all ? 'imported as drafts.' : 'published.') : 'no new '.($all ? '' : 'tagged ').'videos.');
    }

    // Instagram, if the page has a linked Instagram account.
    return array_merge($log, import_instagram_sessions($page, $o));
}

/** The session already made from this Instagram post, if any (including binned). */
function instagram_post_session(string $ig_id): ?\WP_Post
{
    $posts = get_posts([
        'post_type' => 'jl_session',
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'numberposts' => 1,
        'meta_key' => 'session_ig_id',
        'meta_value' => $ig_id,
    ]);

    return $posts[0] ?? null;
}

/**
 * Tagged Instagram videos/reels from the Instagram account linked to the
 * Facebook page. Needs the connection to include Instagram access; if it
 * doesn't, this says so and the Facebook part still works.
 *
 * @return string[] log lines
 */
function import_instagram_sessions(array $page, array $o = []): array
{
    $limit = max(1, (int) ($o['limit'] ?? 50));
    $list_only = ! empty($o['list']);
    $token = $page['access_token'];

    $get = function (string $url) {
        $response = wp_remote_get($url, ['timeout' => 30]);
        if (is_wp_error($response)) {
            return ['error' => ['message' => $response->get_error_message()]];
        }

        return json_decode(wp_remote_retrieve_body($response), true) ?: [];
    };

    $account = $get(GRAPH.rawurlencode($page['id']).'?'.http_build_query(['fields' => 'instagram_business_account{id,username}', 'access_token' => $token]));
    if (! empty($account['error'])) {
        return ["Instagram: couldn't check for a linked account (".($account['error']['message'] ?? 'error').')'];
    }
    $ig = $account['instagram_business_account'] ?? null;
    if (! $ig) {
        return ["Instagram: no Instagram account is linked to the Facebook page, or the connection can't see it. Link them in Meta Business Suite › Settings, then reconnect Facebook in wp-admin › Facebook Import › Settings (accepting the Instagram permissions)."];
    }

    $url = GRAPH.rawurlencode($ig['id']).'/media?'.http_build_query([
        'fields' => 'id,caption,media_type,media_product_type,permalink,thumbnail_url,timestamp',
        'limit' => min(50, $limit),
        'access_token' => $token,
    ]);

    $media = [];
    while ($url && count($media) < $limit) {
        $body = $get($url);
        if (! empty($body['error'])) {
            return ['Instagram: '.($body['error']['message'] ?? 'error').' – reconnect Facebook in wp-admin › Facebook Import › Settings and accept the Instagram permissions.'];
        }
        array_push($media, ...($body['data'] ?? []));
        $url = $body['paging']['next'] ?? null;
    }

    $tz = wp_timezone();
    $log = [];
    $created = 0;

    foreach (array_slice($media, 0, $limit) as $item) {
        if (($item['media_type'] ?? '') !== 'VIDEO' || ! has_sessions_tag((string) ($item['caption'] ?? ''))) {
            continue;
        }

        $when = (new \DateTimeImmutable($item['timestamp']))->setTimezone($tz);
        $day = $when->format('Y-m-d');

        if ($existing = instagram_post_session($item['id'])) {
            if ($existing->post_status === 'trash' && ! $list_only) {
                wp_untrash_post($existing->ID);
                wp_update_post(['ID' => $existing->ID, 'post_status' => 'publish']);
                $log[] = "{$day}  Instagram, restored from the bin: ".html_entity_decode($existing->post_title, ENT_QUOTES);
            }
            continue;
        }

        $event = gig_before($when);
        $caption = strip_sessions_tag((string) ($item['caption'] ?? ''));
        $title = $event ? html_entity_decode(get_the_title($event), ENT_QUOTES, 'UTF-8') : '';
        if ($title === '') {
            $line = trim(preg_replace('/#\w+/u', '', strtok($caption, "\n") ?: ''));
            $title = $line !== '' ? wp_trim_words($line, 9, '…') : 'Live at the Jenny – '.wp_date('j F Y', $when->getTimestamp());
        }

        // Same night already imported from a Facebook cross-post? Don't double up.
        $twin = get_posts(['post_type' => 'jl_session', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'title' => $title,
            'meta_query' => [['key' => 'session_date', 'value' => $when->format('Ymd')], ['key' => 'session_fb_id', 'compare' => 'EXISTS']]]);
        if ($twin) {
            continue;
        }

        if ($list_only) {
            $log[] = "{$day}  Instagram, would add: {$title}";
            continue;
        }

        $post_id = wp_insert_post([
            'post_type' => 'jl_session',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_date' => min($when, new \DateTimeImmutable('now', $tz))->format('Y-m-d H:i:s'),
            'post_content' => $caption !== '' ? "<!-- wp:paragraph -->\n<p>".nl2br(esc_html($caption))."</p>\n<!-- /wp:paragraph -->" : '',
        ], true);
        if (is_wp_error($post_id)) {
            $log[] = "{$day}  Instagram, could not add {$title}: ".$post_id->get_error_message();
            continue;
        }

        update_post_meta($post_id, 'session_youtube', $item['permalink']);
        update_post_meta($post_id, 'session_date', $when->format('Ymd'));
        update_post_meta($post_id, 'session_ig_id', $item['id']);
        update_post_meta($post_id, 'session_tagged', 1);
        if ($event) {
            update_post_meta($post_id, 'session_event', $event);
        }

        if (! empty($item['thumbnail_url'])) {
            $tmp = download_url($item['thumbnail_url'], 30);
            if (! is_wp_error($tmp)) {
                $attachment = media_handle_sideload(['name' => "instagram-{$item['id']}.jpg", 'tmp_name' => $tmp], $post_id, $title);
                is_wp_error($attachment) ? @unlink($tmp) : set_post_thumbnail($post_id, $attachment);
            }
        }

        $created++;
        $log[] = "{$day}  Instagram, added: {$title}".($event ? '' : ' (no gig that night – check the name)');
    }

    if (! $list_only) {
        $log[] = 'Instagram (@'.($ig['username'] ?? '?').'): '.($created ? "{$created} new session(s) published." : 'no new tagged videos.');
    }

    return $log;
}

/* Check twice a day on the website ------------------------------------------- */

add_action('init', function () {
    if (! wp_next_scheduled('jl_import_facebook_sessions')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'twicedaily', 'jl_import_facebook_sessions');
    }
});

add_action('jl_import_facebook_sessions', function () {
    import_facebook_sessions(['limit' => 25]);
});
