<?php

/**
 * Creates "The Jenny Sessions" page under What's On (template "Jenny Sessions")
 * and a starter set of styles. Safe to run more than once.
 *
 * Run from the Site folder:
 *   php wp-content/themes/jennylind/bin/setup-sessions.php
 */

if (PHP_SAPI !== 'cli') {
    exit("Run this from the command line.\n");
}

$site = dirname(__DIR__, 4);
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'jennylindhastings.test';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/';
define('WP_USE_THEMES', false);
require $site.'/wp-load.php';

$parent = get_page_by_path('whats-on');
if (! $parent) {
    exit("Couldn't find the What's On page (slug whats-on), so nothing was changed.\n");
}

$page = get_page_by_path('whats-on/jenny-sessions');

if (! $page) {
    $id = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'The Jenny Sessions',
        'post_name' => 'jenny-sessions',
        'post_parent' => $parent->ID,
        'post_content' => "<!-- wp:paragraph -->\n<p>Some of the best nights in the bar, filmed live. Folk, blues, shanties and everything in between – press play and pull up a stool.</p>\n<!-- /wp:paragraph -->",
    ], true);

    if (is_wp_error($id)) {
        exit('Could not create the page: '.$id->get_error_message()."\n");
    }

    // Set directly: WordPress only lists theme templates in the admin.
    update_post_meta($id, '_wp_page_template', 'template-jenny-sessions.blade.php');

    // Same band photo as What's On until a better one is chosen.
    if ($thumb = get_post_thumbnail_id($parent)) {
        set_post_thumbnail($id, $thumb);
    }

    echo "Created page: The Jenny Sessions (/whats-on/jenny-sessions/)\n";
} else {
    update_post_meta($page->ID, '_wp_page_template', 'template-jenny-sessions.blade.php');
    echo "Page already there: The Jenny Sessions (template checked)\n";
}

foreach (['Folk', 'Blues', 'Rock', 'Shanty', 'Acoustic', 'Jazz'] as $style) {
    if (! term_exists($style, 'session_style')) {
        wp_insert_term($style, 'session_style');
    }
}
echo "Styles ready: Folk, Blues, Rock, Shanty, Acoustic, Jazz (edit under Jenny Sessions › Styles)\n";

flush_rewrite_rules(false);
echo "Done. Add videos under wp-admin › Jenny Sessions › Add session.\n";
