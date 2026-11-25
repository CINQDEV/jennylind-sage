<?php

/**
 * Hastings festival year: the town's annual festivals, shown as a strip on
 * What's On. Only festivals with a confirmed date that hasn't passed are
 * shown. Manage them in wp-admin › Events › Hastings festivals, which starts
 * pre-filled with the researched list: add the dates as each is announced.
 */

namespace App;

/**
 * Researched list (Nov 2026), used to pre-fill the admin list once. Only
 * entries with a confirmed start date appear on the site.
 */
function festival_defaults(): array
{
    return [
        ['name' => 'Le Grand Mardi Gras Ball', 'month' => 2, 'start' => '20270206', 'end' => '', 'when' => 'White Rock Theatre, 7.30pm – part of Hastings Fat Tuesday', 'blurb' => "The Saturday-night party of the UK's biggest Mardi Gras, with DJ Fat Tony, She's Got Brass and King Size Slim.", 'url' => 'https://hastingsfattuesday.co.uk/', 'jenny' => false],
        ['name' => 'Hastings Fat Tuesday', 'month' => 2, 'when' => 'Five days up to Shrove Tuesday (9 Feb in 2027)', 'blurb' => "The UK's biggest Mardi Gras: hundreds of free gigs, the umbrella parade and the Fat Tuesday Tour round the Old Town pubs.", 'url' => 'https://hastingsfattuesday.co.uk/', 'jenny' => false],
        ['name' => 'Jack in the Green', 'month' => 5, 'when' => 'May Day bank holiday weekend', 'blurb' => 'Four days of Morris, drums and giants, ending in the Monday procession and the slaying of the Jack.', 'url' => 'https://hastingstraditionaljackinthegreen.co.uk/', 'jenny' => false],
        ['name' => 'Midsummer Fish Fest', 'month' => 6, 'when' => 'Late June weekend', 'blurb' => 'Local catch, cookery demos and live music on the Stade, yards from the fishing beach.', 'url' => 'https://www.visit1066country.com/whats-on/hastings-midsummer-fish-festival-p1058561', 'jenny' => false],
        ['name' => 'Beer & Music Festival', 'month' => 7, 'when' => 'Early July', 'blurb' => "Hastings Round Table's charity beer festival up on The Oval, with local bands all weekend.", 'url' => 'https://hbmf.co.uk/', 'jenny' => false],
        ['name' => 'Pirate Day', 'month' => 7, 'when' => 'Mid July weekend', 'blurb' => 'The town turns buccaneer: shanties, pirate fun and live music in the Old Town pubs.', 'url' => '', 'jenny' => true],
        ['name' => 'Old Town Carnival Week', 'month' => 8, 'when' => 'First week of August', 'blurb' => 'Pram race, sea-boot race, street parties and the grand carnival procession with fireworks.', 'url' => 'https://oldtowncarnivalweek.co.uk/', 'jenny' => false],
        ['name' => 'Coastal Currents', 'month' => 9, 'when' => 'All of September', 'blurb' => 'Open studios, exhibitions and performances across Hastings and St Leonards.', 'url' => 'https://coastalcurrents.org.uk/', 'jenny' => false],
        ['name' => 'Seafood, Wine & Music Festival', 'month' => 9, 'when' => 'Mid September weekend', 'blurb' => 'Oysters, fish rolls, local wine and themed music days on the Stade Open Space.', 'url' => 'https://www.visit1066country.com/whats-on/hastings-seafood-wine-and-music-festival-p2860371', 'jenny' => false],
        ['name' => 'Hastings Week & Bonfire', 'month' => 10, 'when' => 'Mid October', 'blurb' => 'A week marking 1066, finishing with the torchlit procession, beach bonfire and fireworks.', 'url' => 'https://www.hbbs.info/', 'jenny' => false],
        ['name' => 'Old Town Cider Festival', 'month' => 11, 'when' => 'Mid November', 'blurb' => 'Local and national ciders and perries across the Old Town pubs – the Jenny included.', 'url' => 'https://www.visit1066country.com/whats-on/hastings-old-town-cider-festival-p2645931', 'jenny' => true],
    ];
}

/**
 * Festivals with a confirmed date still to come, soonest first; the first is
 * marked "next".
 *
 * @return array<int, array{name:string, when:string, dates:string, blurb:string, url:string, jenny:bool, next:bool, month_label:string}>
 */
function festivals(): array
{
    seed_festivals();
    $rows = function_exists('get_field') ? (get_field('jl_festivals', 'option') ?: []) : [];
    $tz = wp_timezone();
    $today = (new \DateTimeImmutable('today', $tz))->format('Ymd');
    $list = [];

    foreach ($rows as $r) {
        $start = (string) ($r['start'] ?? '');
        $end = (string) ($r['end'] ?? '') ?: $start;
        if (! $start || $end < $today) {
            continue; // not confirmed yet, or over
        }

        $s = \DateTimeImmutable::createFromFormat('Ymd', $start, $tz);
        $e = \DateTimeImmutable::createFromFormat('Ymd', $end, $tz);
        $dates = $start === $end
            ? wp_date('D j M Y', $s->getTimestamp())
            : wp_date($s->format('Ym') === $e->format('Ym') ? 'D j' : 'D j M', $s->getTimestamp()).' – '.wp_date('D j M Y', $e->getTimestamp());

        $list[] = [
            'sort' => $start,
            'name' => (string) $r['name'],
            'dates' => $dates,
            'when' => (string) ($r['when'] ?? ''),
            'blurb' => (string) ($r['blurb'] ?? ''),
            'url' => (string) ($r['url'] ?? ''),
            'jenny' => ! empty($r['jenny']),
            'month_label' => wp_date('M', $s->getTimestamp()),
        ];
    }

    usort($list, fn ($a, $b) => strcmp($a['sort'], $b['sort']));
    foreach ($list as $k => &$f) {
        $f['next'] = $k === 0;
    }

    return $list;
}

/** Pre-fill the admin list with the researched festivals, once. */
function seed_festivals(): void
{
    if (get_option('jl_festivals_seeded') || ! function_exists('update_field') || get_field('jl_festivals', 'option')) {
        return;
    }

    $rows = array_map(fn ($f) => [
        'name' => $f['name'],
        'start' => $f['start'] ?? '',
        'end' => $f['end'] ?? '',
        'when' => $f['when'],
        'blurb' => $f['blurb'],
        'url' => $f['url'],
        'jenny' => $f['jenny'] ? 1 : 0,
    ], festival_defaults());

    update_field('field_jl_festivals', $rows, 'option');
    update_option('jl_festivals_seeded', 1, false);
}

add_action('admin_init', __NAMESPACE__.'\\seed_festivals');

add_action('acf/init', function () {
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Hastings festivals', 'jennylind'),
        'menu_slug' => 'jennylind-festivals',
        'parent_slug' => 'edit.php?post_type=event',
        'capability' => 'edit_pages',
    ]);

    acf_add_local_field_group([
        'key' => 'group_jl_festivals',
        'title' => __('Hastings festival year (What\'s On page)', 'jennylind'),
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'jennylind-festivals']]],
        'fields' => [[
            'key' => 'field_jl_festivals',
            'name' => 'jl_festivals',
            'type' => 'repeater',
            'label' => __('Festivals', 'jennylind'),
            'instructions' => __('Only festivals with a start date are shown on the site, and each one drops off once it\'s over. Add the dates as they\'re announced.', 'jennylind'),
            'layout' => 'block',
            'button_label' => __('Add festival', 'jennylind'),
            'sub_fields' => [
                ['key' => 'field_jl_fest_name', 'name' => 'name', 'type' => 'text', 'label' => __('Name', 'jennylind'), 'required' => 1, 'wrapper' => ['width' => 40]],
                ['key' => 'field_jl_fest_start', 'name' => 'start', 'type' => 'date_picker', 'label' => __('Starts (confirmed)', 'jennylind'), 'display_format' => 'j F Y', 'return_format' => 'Ymd', 'first_day' => 1, 'wrapper' => ['width' => 30]],
                ['key' => 'field_jl_fest_end', 'name' => 'end', 'type' => 'date_picker', 'label' => __('Ends', 'jennylind'), 'display_format' => 'j F Y', 'return_format' => 'Ymd', 'first_day' => 1, 'instructions' => __('Leave blank for one day', 'jennylind'), 'wrapper' => ['width' => 30]],
                ['key' => 'field_jl_fest_when', 'name' => 'when', 'type' => 'text', 'label' => __('Extra detail (optional)', 'jennylind'), 'placeholder' => 'Stade Open Space, from 11am', 'instructions' => __('Usual timing is noted here for festivals without dates yet.', 'jennylind')],
                ['key' => 'field_jl_fest_blurb', 'name' => 'blurb', 'type' => 'textarea', 'rows' => 2, 'label' => __('One line about it', 'jennylind')],
                ['key' => 'field_jl_fest_url', 'name' => 'url', 'type' => 'url', 'label' => __('Festival website', 'jennylind'), 'wrapper' => ['width' => 70]],
                ['key' => 'field_jl_fest_jenny', 'name' => 'jenny', 'type' => 'true_false', 'ui' => 1, 'label' => __('The Jenny takes part', 'jennylind'), 'wrapper' => ['width' => 30]],
            ],
        ]],
    ]);
});
