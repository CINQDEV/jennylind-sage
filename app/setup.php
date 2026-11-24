<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }

    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 */
add_action('after_setup_theme', function () {
    remove_theme_support('block-templates');

    /**
     * Menus. The migration script assigns the existing Oxygen menus:
     * "primary_navigation" was Oxygen menu #2, "legal_navigation" was #1233.
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'jennylind'),
        'legal_navigation' => __('Footer – Legal', 'jennylind'),
    ]);

    remove_theme_support('core-block-patterns');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    // Card images used by the event lists.
    add_image_size('event-card', 600, 400, true);
}, 20);

/**
 * Page-specific body classes, so the block content can be styled per page.
 */
add_filter('body_class', function (array $classes) {
    if (is_page()) {
        $classes[] = 'page-'.get_post_field('post_name', get_queried_object_id());
    }

    return $classes;
});
