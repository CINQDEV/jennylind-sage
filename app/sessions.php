<?php

/**
 * The Jenny Sessions: filmed gigs hosted on YouTube.
 *
 * Each session is a "Session" in wp-admin (YouTube link, artist, date played,
 * style, optional link to the event it came from). They're listed on the
 * "The Jenny Sessions" page (template "Jenny Sessions", under What's On) and
 * each has its own page at /whats-on/jenny-sessions/<name>/ for sharing.
 */

namespace App;

const SESSIONS_PAGE = 'whats-on/jenny-sessions';

add_action('init', function () {
    register_post_type('jl_session', [
        'labels' => [
            'name' => __('Jenny Sessions', 'jennylind'),
            'singular_name' => __('Session', 'jennylind'),
            'menu_name' => __('Jenny Sessions', 'jennylind'),
            'add_new' => __('Add session', 'jennylind'),
            'add_new_item' => __('Add session', 'jennylind'),
            'edit_item' => __('Edit session', 'jennylind'),
            'all_items' => __('All sessions', 'jennylind'),
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-video-alt3',
        'menu_position' => 22,
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'rewrite' => ['slug' => SESSIONS_PAGE, 'with_front' => false],
    ]);

    register_taxonomy('session_style', 'jl_session', [
        'labels' => [
            'name' => __('Styles', 'jennylind'),
            'singular_name' => __('Style', 'jennylind'),
            'add_new_item' => __('Add style', 'jennylind'),
        ],
        'hierarchical' => true, // tick-box picker in the editor
        'public' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
    ]);
});

add_action('acf/init', function () {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_jl_session',
        'title' => __('Session video', 'jennylind'),
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'jl_session']]],
        'position' => 'acf_after_title',
        'fields' => [
            [
                'key' => 'field_jl_session_youtube', 'name' => 'session_youtube', 'type' => 'url', 'required' => 1,
                'label' => __('Video link (YouTube, Facebook or Instagram)', 'jennylind'),
                'instructions' => __('Paste the video\'s address (Share › Copy link). YouTube videos get their thumbnail automatically; for Facebook and Instagram set a featured image. Facebook and Instagram posts must be public.', 'jennylind'),
            ],
            [
                'key' => 'field_jl_session_artist', 'name' => 'session_artist', 'type' => 'text',
                'label' => __('Artist / band', 'jennylind'),
                'instructions' => __('Leave blank to use the title.', 'jennylind'),
            ],
            [
                'key' => 'field_jl_session_date', 'name' => 'session_date', 'type' => 'date_picker',
                'label' => __('Date played', 'jennylind'), 'display_format' => 'j F Y', 'return_format' => 'Ymd',
                'instructions' => __('Sessions are numbered in date order.', 'jennylind'),
            ],
            [
                'key' => 'field_jl_session_event', 'name' => 'session_event', 'type' => 'post_object',
                'label' => __('From this event (optional)', 'jennylind'), 'post_type' => ['event'],
                'return_format' => 'id', 'allow_null' => 1, 'ui' => 1,
                'instructions' => __('The event page will then show "Watch the session".', 'jennylind'),
            ],
            [
                'key' => 'field_jl_session_featured', 'name' => 'session_featured', 'type' => 'true_false', 'ui' => 1,
                'label' => __('Feature at the top of the Sessions page', 'jennylind'),
                'instructions' => __('If none is ticked, the newest session is featured.', 'jennylind'),
            ],
        ],
    ]);
});

/** Is this a Facebook video link (facebook.com/…/videos/…, /watch/?v=, /reel/, fb.watch)? */
function is_facebook_video(?string $url): bool
{
    return (bool) ($url && preg_match('~^https?://((www|m|web)\.)?(facebook\.com|fb\.watch)/~i', $url));
}

/**
 * Can this Facebook video play on other sites? Facebook refuses for videos
 * that aren't public (e.g. Stories) and ones it thinks contain music owned by
 * someone else. Returns null if Facebook couldn't be asked.
 */
function facebook_embeddable(string $link): ?bool
{
    $response = wp_remote_get(
        'https://www.facebook.com/plugins/video.php?show_text=false&width=560&href='.rawurlencode($link),
        ['timeout' => 20, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130 Safari/537.36']
    );

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return null;
    }

    $html = html_entity_decode(wp_remote_retrieve_body($response), ENT_QUOTES);

    // The real error messages; the player page also carries a hidden "Video
    // unavailable" template, so match these exactly.
    return ! preg_match("/can't be embedded|may no longer exist|>Video Unavailable</", $html);
}

/** Instagram post/reel link as [type, shortcode], or null. */
function instagram_post(?string $url): ?array
{
    if ($url && preg_match('~instagram\.com/(?:[A-Za-z0-9_.]+/)?(reel|reels|p|tv)/([A-Za-z0-9_-]+)~i', $url, $m)) {
        return [strtolower($m[1]) === 'reels' ? 'reel' : strtolower($m[1]), $m[2]];
    }

    return null;
}

/** YouTube video ID from any normal YouTube link (watch, youtu.be, shorts, live, embed). */
function youtube_id(?string $url): ?string
{
    if (! $url) {
        return null;
    }

    if (preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/))([A-Za-z0-9_-]{11})~', $url, $m)) {
        return $m[1];
    }

    return preg_match('~^[A-Za-z0-9_-]{11}$~', trim($url)) ? trim($url) : null;
}

/**
 * Everything the templates need for one session.
 *
 * @return array{id:int, title:string, artist:string, number:int, url:string, video:?string, embed:?string, watch:?string, thumb:?string, thumb_fallback:?string, date:?string, date_iso:?string, styles:array, style_slugs:string, excerpt:string, event:?int}
 */
function session(\WP_Post|int $post): array
{
    $post = get_post($post);
    $link = (string) get_post_meta($post->ID, 'session_youtube', true);
    $video = youtube_id($link);
    $facebook = ! $video && is_facebook_video($link);
    $instagram = ! $video && ! $facebook ? instagram_post($link) : null;
    $played = get_post_meta($post->ID, 'session_date', true);
    $played = $played ? \DateTimeImmutable::createFromFormat('Ymd', $played, wp_timezone()) : null;
    $styles = get_the_terms($post, 'session_style') ?: [];

    $thumb = get_the_post_thumbnail_url($post, 'large')
        ?: ($video ? "https://i.ytimg.com/vi/{$video}/maxresdefault.jpg" : null)
        ?: get_the_post_thumbnail_url(get_page_by_path(SESSIONS_PAGE), 'large') ?: null;

    if ($video) {
        // Privacy-enhanced player: no YouTube cookies until someone presses play.
        $embed = "https://www.youtube-nocookie.com/embed/{$video}?autoplay=1&rel=0&modestbranding=1";
        $watch = "https://www.youtube.com/watch?v={$video}";
    } elseif ($facebook) {
        $embed = 'https://www.facebook.com/plugins/video.php?href='.rawurlencode($link).'&show_text=false&autoplay=true&width=1280';
        $watch = $link;
    } elseif ($instagram) {
        [$type, $code] = $instagram;
        $embed = "https://www.instagram.com/{$type}/{$code}/embed/";
        $watch = "https://www.instagram.com/{$type}/{$code}/";
    } else {
        $embed = $watch = null;
    }

    return [
        'id' => $post->ID,
        'title' => html_entity_decode(get_the_title($post), ENT_QUOTES, 'UTF-8'),
        'artist' => (string) (get_post_meta($post->ID, 'session_artist', true) ?: html_entity_decode(get_the_title($post), ENT_QUOTES, 'UTF-8')),
        'number' => session_number($post->ID),
        'url' => get_permalink($post),
        'video' => $video,
        'source' => $video ? 'YouTube' : ($facebook ? 'Facebook' : ($instagram ? 'Instagram' : null)),
        // Instagram's player is portrait (it carries the post header and caption).
        'portrait' => (bool) $instagram,
        'embed' => $embed,
        'watch' => $watch,
        'thumb' => $thumb,
        'thumb_fallback' => $video ? "https://i.ytimg.com/vi/{$video}/hqdefault.jpg" : null,
        'date' => $played ? wp_date('j F Y', $played->getTimestamp()) : null,
        'date_iso' => $played ? $played->format('Y-m-d') : get_the_date('Y-m-d', $post),
        'styles' => wp_list_pluck($styles, 'name'),
        'style_slugs' => implode(' ', wp_list_pluck($styles, 'slug')),
        'excerpt' => has_excerpt($post) ? get_the_excerpt($post) : wp_trim_words(wp_strip_all_tags($post->post_content), 28),
        'event' => ((int) get_post_meta($post->ID, 'session_event', true)) ?: null,
    ];
}

/**
 * Published session posts in date-played order (sessions without a date
 * use their publish date).
 *
 * @return \WP_Post[]
 */
function session_posts(string $order = 'DESC'): array
{
    static $cache = null;

    if ($cache === null) {
        $cache = get_posts(['post_type' => 'jl_session', 'post_status' => 'publish', 'numberposts' => -1]);
        $key = fn (\WP_Post $p) => (get_post_meta($p->ID, 'session_date', true) ?: get_the_date('Ymd', $p)).'-'.get_post_time('U', true, $p);
        usort($cache, fn ($a, $b) => strcmp($key($a), $key($b)));
    }

    return $order === 'ASC' ? $cache : array_reverse($cache);
}

/**
 * Published sessions, newest first.
 *
 * @return array<int, array> see session()
 */
function sessions(int $count = -1): array
{
    $posts = session_posts('DESC');

    return array_map(fn ($p) => session($p), $count > 0 ? array_slice($posts, 0, $count) : $posts);
}

/** Session number (#01 is the earliest played). */
function session_number(int $id): int
{
    $ids = array_map(fn ($p) => $p->ID, session_posts('ASC'));
    $i = array_search($id, $ids, true);

    return $i === false ? 0 : $i + 1;
}

/** The featured session: the ticked one, or the newest. */
function featured_session(array $sessions): ?array
{
    foreach ($sessions as $session) {
        if (get_post_meta($session['id'], 'session_featured', true)) {
            return $session;
        }
    }

    return $sessions[0] ?? null;
}

/** Session filmed at a given event, if any. */
function session_for_event(int $event_id): ?array
{
    $posts = get_posts([
        'post_type' => 'jl_session',
        'post_status' => 'publish',
        'numberposts' => 1,
        'meta_key' => 'session_event',
        'meta_value' => $event_id,
    ]);

    return $posts ? session($posts[0]) : null;
}

/** Address of the Sessions page. */
function sessions_url(): string
{
    $page = get_page_by_path(SESSIONS_PAGE);

    return $page ? get_permalink($page) : home_url('/'.SESSIONS_PAGE.'/');
}

/** Search engines: describe the video on a session's own page. */
add_action('wp_head', function () {
    if (! is_singular('jl_session')) {
        return;
    }

    $s = session(get_queried_object());
    if (! $s['embed']) {
        return;
    }

    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'VideoObject',
        'name' => $s['title'].' – live at the Jenny Lind, Hastings',
        'description' => $s['excerpt'] ?: sprintf('%s playing live at the Jenny Lind Inn, Hastings Old Town.', $s['artist']),
        'thumbnailUrl' => array_values(array_filter([$s['thumb'], $s['thumb_fallback']])),
        'uploadDate' => $s['date_iso'],
        'embedUrl' => $s['video'] ? "https://www.youtube.com/embed/{$s['video']}" : $s['embed'],
        'contentUrl' => $s['watch'],
        'publisher' => ['@type' => 'Organization', 'name' => get_bloginfo('name')],
    ];

    echo '<script type="application/ld+json">'.wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."</script>\n";
});
