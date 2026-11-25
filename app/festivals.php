<?php

/**
 * Hastings festival year: the town's annual festivals, shown as a compact
 * grid on What's On, from this month round the year. Each shows its usual
 * timing, or its exact dates once you add them. Manage them in wp-admin ›
 * Events › Hastings festivals (pre-filled with the researched list).
 */

namespace App;

/** Researched list (Nov 2026), used to pre-fill the admin list once. */
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
 * All festivals, from this month round the year (this month's count as gone
 * after the 15th). Exact dates replace the usual timing when they're set.
 *
 * @return array<int, array{name:string, when:string, url:string, jenny:bool, next:bool, month_label:string}>
 */
function festivals(): array
{
    seed_festivals();
    $rows = function_exists('get_field') ? (get_field('jl_festivals', 'option') ?: []) : [];
    $rows = $rows ?: festival_defaults();
    $months = array_column(festival_defaults(), 'month', 'name');
    $tz = wp_timezone();
    $now = (int) wp_date('n');
    $late = (int) wp_date('j') > 15;
    $list = [];

    foreach ($rows as $r) {
        $start = (string) ($r['start'] ?? '');
        $end = (string) ($r['end'] ?? '') ?: $start;
        $month = (int) ($r['month'] ?? 0) ?: (int) ($months[$r['name']] ?? 0);
        $when = (string) ($r['when'] ?? '');

        if ($start) {
            $s = \DateTimeImmutable::createFromFormat('Ymd', $start, $tz);
            $e = \DateTimeImmutable::createFromFormat('Ymd', $end, $tz);
            $month = (int) $s->format('n');
            $when = $start === $end
                ? wp_date('D j M', $s->getTimestamp())
                : wp_date($s->format('Ym') === $e->format('Ym') ? 'j' : 'j M', $s->getTimestamp()).'–'.wp_date('j M', $e->getTimestamp());
        }
        if (! $month) {
            continue;
        }

        $offset = ($month - $now + 12) % 12;
        $list[] = [
            'sort' => [($offset === 0 && $late) ? 12 : $offset, $start ?: '99', (string) $r['name']],
            'name' => (string) $r['name'],
            'when' => $when,
            'url' => (string) ($r['url'] ?? ''),
            'jenny' => ! empty($r['jenny']),
            'month_label' => wp_date('M', mktime(0, 0, 0, $month, 1)),
        ];
    }

    usort($list, fn ($a, $b) => $a['sort'] <=> $b['sort']);
    foreach ($list as $k => &$f) {
        $f['next'] = $k === 0;
    }

    return $list;
}

/** Pre-fill the admin list with the researched festivals, once. */
function seed_festivals(): void
{
    // v2 replaces the first pre-fill (which had no months), unless edited since.
    if (get_option('jl_festivals_seeded') === '2' || ! function_exists('update_field')) {
        return;
    }
    if (! get_option('jl_festivals_seeded') && get_field('jl_festivals', 'option')) {
        update_option('jl_festivals_seeded', '2', false);

        return;
    }

    $rows = array_map(fn ($f) => [
        'name' => $f['name'],
        'month' => $f['month'],
        'start' => $f['start'] ?? '',
        'end' => $f['end'] ?? '',
        'when' => $f['when'],
        'blurb' => $f['blurb'],
        'url' => $f['url'],
        'jenny' => $f['jenny'] ? 1 : 0,
    ], festival_defaults());

    update_field('field_jl_festivals', $rows, 'option');
    update_option('jl_festivals_seeded', '2', false);
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
            'instructions' => __('Shown from this month round the year. "When" is shown until you add exact dates, which then replace it.', 'jennylind'),
            'layout' => 'block',
            'button_label' => __('Add festival', 'jennylind'),
            'sub_fields' => [
                ['key' => 'field_jl_fest_name', 'name' => 'name', 'type' => 'text', 'label' => __('Name', 'jennylind'), 'required' => 1, 'wrapper' => ['width' => 40]],
                ['key' => 'field_jl_fest_month', 'name' => 'month', 'type' => 'select', 'label' => __('Month', 'jennylind'), 'choices' => $months, 'wrapper' => ['width' => 20]],
                ['key' => 'field_jl_fest_start', 'name' => 'start', 'type' => 'date_picker', 'label' => __('Starts (once confirmed)', 'jennylind'), 'display_format' => 'j F Y', 'return_format' => 'Ymd', 'first_day' => 1, 'wrapper' => ['width' => 20]],
                ['key' => 'field_jl_fest_end', 'name' => 'end', 'type' => 'date_picker', 'label' => __('Ends', 'jennylind'), 'display_format' => 'j F Y', 'return_format' => 'Ymd', 'first_day' => 1, 'instructions' => __('Blank for one day', 'jennylind'), 'wrapper' => ['width' => 20]],
                ['key' => 'field_jl_fest_when', 'name' => 'when', 'type' => 'text', 'label' => __('When (usual timing)', 'jennylind'), 'placeholder' => 'Late June weekend'],
                ['key' => 'field_jl_fest_blurb', 'name' => 'blurb', 'type' => 'textarea', 'rows' => 2, 'label' => __('One line about it', 'jennylind')],
                ['key' => 'field_jl_fest_url', 'name' => 'url', 'type' => 'url', 'label' => __('Festival website', 'jennylind'), 'wrapper' => ['width' => 70]],
                ['key' => 'field_jl_fest_jenny', 'name' => 'jenny', 'type' => 'true_false', 'ui' => 1, 'label' => __('The Jenny takes part', 'jennylind'), 'wrapper' => ['width' => 30]],
            ],
        ]],
    ]);
});
