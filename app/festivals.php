<?php

/**
 * Hastings festival year: the town's annual festivals, shown as a strip on
 * What's On. Edit them in wp-admin › Events › Hastings festivals. Until that
 * list is filled in, the researched defaults below are used.
 */

namespace App;

/** Researched defaults (Nov 2026). "when" is shown as written; "month" orders the strip. */
function festival_defaults(): array
{
    return [
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
 * The festivals, ordered from this month round the year, with "next" marking
 * the first one still to come.
 *
 * @return array<int, array{name:string, month:int, when:string, blurb:string, url:string, jenny:bool, next:bool, month_label:string}>
 */
function festivals(): array
{
    $rows = function_exists('get_field') ? (get_field('jl_festivals', 'option') ?: []) : [];
    $list = $rows
        ? array_map(fn ($r) => [
            'name' => (string) $r['name'],
            'month' => (int) $r['month'],
            'when' => (string) $r['when'],
            'blurb' => (string) ($r['blurb'] ?? ''),
            'url' => (string) ($r['url'] ?? ''),
            'jenny' => ! empty($r['jenny']),
        ], $rows)
        : festival_defaults();

    $now = (int) wp_date('n');
    $late = (int) wp_date('j') > 15;
    // Months from now round the year. This month's festivals count as gone
    // after the 15th, so they move to the end rather than showing "Next up".
    $offset = function (int $month) use ($now, $late) {
        $o = ($month - $now + 12) % 12;

        return ($o === 0 && $late) ? 12 : $o;
    };
    usort($list, fn ($a, $b) => [$offset($a['month']), $a['name']] <=> [$offset($b['month']), $b['name']]);

    foreach ($list as $i => &$f) {
        $f['next'] = $i === 0;
        $f['month_label'] = wp_date('M', mktime(0, 0, 0, max(1, min(12, $f['month'])), 1));
    }

    return $list;
}

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

    $months = [];
    foreach (range(1, 12) as $m) {
        $months[$m] = wp_date('F', mktime(0, 0, 0, $m, 1));
    }

    acf_add_local_field_group([
        'key' => 'group_jl_festivals',
        'title' => __('Hastings festival year (What\'s On page)', 'jennylind'),
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'jennylind-festivals']]],
        'fields' => [[
            'key' => 'field_jl_festivals',
            'name' => 'jl_festivals',
            'type' => 'repeater',
            'label' => __('Festivals', 'jennylind'),
            'instructions' => __('Leave empty to use the built-in list. Once you add one here, only these are shown. "When" is shown as written – put exact dates in once they\'re announced.', 'jennylind'),
            'layout' => 'block',
            'button_label' => __('Add festival', 'jennylind'),
            'sub_fields' => [
                ['key' => 'field_jl_fest_name', 'name' => 'name', 'type' => 'text', 'label' => __('Name', 'jennylind'), 'required' => 1, 'wrapper' => ['width' => 40]],
                ['key' => 'field_jl_fest_month', 'name' => 'month', 'type' => 'select', 'label' => __('Month', 'jennylind'), 'choices' => $months, 'required' => 1, 'wrapper' => ['width' => 20]],
                ['key' => 'field_jl_fest_when', 'name' => 'when', 'type' => 'text', 'label' => __('When', 'jennylind'), 'placeholder' => 'Late June weekend / Fri 4 – Mon 7 May', 'wrapper' => ['width' => 40]],
                ['key' => 'field_jl_fest_blurb', 'name' => 'blurb', 'type' => 'textarea', 'rows' => 2, 'label' => __('One line about it', 'jennylind')],
                ['key' => 'field_jl_fest_url', 'name' => 'url', 'type' => 'url', 'label' => __('Festival website', 'jennylind'), 'wrapper' => ['width' => 70]],
                ['key' => 'field_jl_fest_jenny', 'name' => 'jenny', 'type' => 'true_false', 'ui' => 1, 'label' => __('The Jenny takes part', 'jennylind'), 'wrapper' => ['width' => 30]],
            ],
        ]],
    ]);
});
