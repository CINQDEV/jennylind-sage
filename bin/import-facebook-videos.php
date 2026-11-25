<?php

/**
 * Add Facebook videos tagged #JennySessions as Jenny Sessions, right now.
 * (The website also does this by itself twice a day.)
 *
 * Run from the Site folder:
 *   php wp-content/themes/jennylind/bin/import-facebook-videos.php           publish tagged videos
 *   php wp-content/themes/jennylind/bin/import-facebook-videos.php --list    show what it would add
 *
 * Options:
 *   --limit=50   how many of the latest page videos to look through
 *   --all        ignore the tag and import every playable video as a DRAFT
 *   --page=<id>  a different connected Facebook page
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

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'jennylindhastings.test';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/';
define('WP_USE_THEMES', false);
require dirname(__DIR__, 4).'/wp-load.php';

if (! function_exists('App\\import_facebook_sessions')) {
    exit("The theme isn't active or is out of date.\n");
}

foreach (App\import_facebook_sessions($args) as $line) {
    echo "  {$line}\n";
}
