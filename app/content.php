<?php

/**
 * Editable content used by the templates: rooms and the stay offer.
 */

namespace App;

/**
 * Rooms. These are the existing "hb_accommodation" records left by the old
 * hotel-booking plugin (Family, Double, Twin, Single), registered again so they
 * can be edited under wp-admin › Rooms. Not public pages: shown on the Stay page.
 */
add_action('init', function () {
    register_post_type('hb_accommodation', [
        'labels' => [
            'name' => __('Rooms', 'jennylind'),
            'singular_name' => __('Room', 'jennylind'),
            'add_new_item' => __('Add room', 'jennylind'),
            'edit_item' => __('Edit room', 'jennylind'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-admin-home',
        'menu_position' => 21,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ]);
});

/**
 * Rooms in menu order, largest first.
 *
 * @return array<int, array{title: string, text: string, image: ?string, sleeps: int, count: int}>
 */
function rooms(): array
{
    $posts = get_posts([
        'post_type' => 'hb_accommodation',
        'post_status' => 'publish',
        'numberposts' => 12,
        'orderby' => ['menu_order' => 'ASC', 'ID' => 'ASC'],
    ]);

    return array_map(fn (\WP_Post $room) => [
        'title' => get_the_title($room),
        'text' => wp_strip_all_tags($room->post_content),
        'image' => get_the_post_thumbnail_url($room, 'medium_large') ?: null,
        'sleeps' => (int) get_post_meta($room->ID, 'accom_max_occupancy', true),
        'count' => (int) get_post_meta($room->ID, 'accom_quantity', true),
    ], $posts);
}

/**
 * Stay offer (wp-admin › Stay offer, via ACF). Falls back to the current winter
 * deal until it is edited, and hides itself after its end date.
 *
 * @return array{badge: string, text: string, code: string}|null
 */
function stay_offer(): ?array
{
    $get = fn (string $key, $default) => function_exists('get_field')
        ? (get_field($key, 'option') ?? $default)
        : $default;

    $enabled = $get('stay_offer_enabled', true);
    $until = $get('stay_offer_until', '20270314');
    $offer = [
        'badge' => (string) $get('stay_offer_badge', '15% off'),
        'text' => (string) $get('stay_offer_text', 'Winter deal on Sun–Fri stays to 14 March 2027 (not Saturdays, Christmas or festival dates).'),
        'code' => (string) $get('stay_offer_code', 'JLWINTRA26'),
    ];

    if (! $enabled || ($until && wp_date('Ymd') > preg_replace('/\D/', '', (string) $until))) {
        return null;
    }

    return $offer['badge'] || $offer['text'] ? $offer : null;
}

add_action('acf/init', function () {
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Stay offer', 'jennylind'),
        'menu_slug' => 'jennylind-stay-offer',
        'parent_slug' => 'edit.php?post_type=hb_accommodation',
        'capability' => 'edit_pages',
    ]);

    acf_add_local_field_group([
        'key' => 'group_jl_stay_offer',
        'title' => __('Stay offer', 'jennylind'),
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'jennylind-stay-offer']]],
        'fields' => [
            ['key' => 'field_jl_offer_enabled', 'name' => 'stay_offer_enabled', 'label' => __('Show the offer', 'jennylind'), 'type' => 'true_false', 'ui' => 1, 'default_value' => 1],
            ['key' => 'field_jl_offer_badge', 'name' => 'stay_offer_badge', 'label' => __('Badge', 'jennylind'), 'type' => 'text', 'default_value' => '15% off'],
            ['key' => 'field_jl_offer_text', 'name' => 'stay_offer_text', 'label' => __('Details', 'jennylind'), 'type' => 'textarea', 'rows' => 3, 'default_value' => 'Winter deal on Sun–Fri stays to 14 March 2027 (not Saturdays, Christmas or festival dates).'],
            ['key' => 'field_jl_offer_code', 'name' => 'stay_offer_code', 'label' => __('Code', 'jennylind'), 'type' => 'text', 'default_value' => 'JLWINTRA26'],
            ['key' => 'field_jl_offer_until', 'name' => 'stay_offer_until', 'label' => __('Hide after', 'jennylind'), 'type' => 'date_picker', 'return_format' => 'Ymd', 'default_value' => '20270314'],
        ],
    ]);
});

/**
 * The Sirvoy booking form (same form as the Reservations page). Falls back to
 * a link when the Sirvoy plugin is off.
 */
function booking_form(): string
{
    if (! shortcode_exists('sirvoy')) {
        return sprintf(
            '<a class="btn btn-accent w-full" href="%s">%s</a>',
            esc_url(home_url('/stay/reservations/')),
            esc_html__('Check availability', 'jennylind')
        );
    }

    return do_shortcode(sprintf(
        '[sirvoy form-id="a8a385971d6bdd2e" target-result-url="%s"]',
        esc_url(home_url('/stay/reservations/'))
    ));
}

/**
 * Top-level items of a menu location, with WordPress's "current" detection,
 * so the header can split the menu either side of the logo.
 *
 * @return array<int, array{title: string, url: string, current: bool}>
 */
function menu_items(string $location): array
{
    $locations = get_nav_menu_locations();
    $items = isset($locations[$location]) ? wp_get_nav_menu_items($locations[$location]) : [];

    if (! $items) {
        return [];
    }

    _wp_menu_item_classes_by_context($items);

    return array_values(array_map(fn ($item) => [
        'title' => $item->title,
        'url' => $item->url,
        'current' => (bool) array_intersect(['current-menu-item', 'current-menu-ancestor', 'current_page_item'], (array) $item->classes),
    ], array_filter($items, fn ($item) => ! $item->menu_item_parent)));
}

/**
 * Opening hours, used in the footer, the homepage "Find us" and the Contact
 * sidebar. Change them here.
 *
 * @return array<int, array{0: string, 1: string}>
 */
function opening_hours(): array
{
    return [
        ['Mon–Wed', '1pm–11pm'],
        ['Thu', '12pm–11pm'],
        ['Fri–Sun', '12pm–12am'],
    ];
}

/**
 * Split page HTML just before its second <h2>, so something can be slotted in
 * after the first section. No second heading: everything goes first.
 *
 * @return array{0: string, 1: string}
 */
function split_after_first_section(string $html): array
{
    $first = stripos($html, '<h2');
    $second = $first === false ? false : stripos($html, '<h2', $first + 3);

    return $second === false ? [$html, ''] : [substr($html, 0, $second), substr($html, $second)];
}
