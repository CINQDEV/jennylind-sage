<?php

/**
 * One-off migration from Oxygen to the Jenny Lind Sage theme.
 *
 * Run from the site root with Herd's PHP:
 *   php wp-content/themes/jennylind/bin/migrate-oxygen.php            (apply)
 *   php wp-content/themes/jennylind/bin/migrate-oxygen.php --dry-run  (preview)
 *
 * What it does:
 *  1. Converts the Oxygen-built page content to Gutenberg blocks:
 *     - Our Bars (intro + four photo cards)
 *     - Stay (the content that lived in the Oxygen "Page - Stay" template)
 *     - Welcome, Contact, Shanty, Shanty Songsheet (classic content → blocks,
 *       plus the Shanty PDF button that Oxygen added)
 *     The previous post_content is kept in the `_jl_pre_sage_content` meta.
 *  2. Assigns the Sage page templates that replace the Oxygen ones.
 *  3. Activates the theme and assigns the existing menus to its locations.
 *  4. Tells Event Organiser to leave single-event templates to the theme.
 *  5. Deactivates Oxygen, OxyExtras, Swiss Knife (an Oxygen add-on) and
 *     Classic Editor (reactivate them in
 *     Plugins to roll back; nothing is deleted).
 *
 * Safe to run more than once: content is always converted from its Oxygen
 * source or the saved original, and the backup meta is written only once.
 */

if (PHP_SAPI !== 'cli') {
    exit("Run this from the command line.\n");
}

$dryRun = in_array('--dry-run', $argv, true);

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'jennylindhastings.test';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/';
define('WP_USE_THEMES', false);

require dirname(__DIR__, 4).'/wp-load.php';
require_once ABSPATH.'wp-admin/includes/plugin.php';

/* -------------------------------------------------------------------------
 * Block builders
 * ---------------------------------------------------------------------- */

function jl_block(string $name, array $attrs, string $html): string
{
    return get_comment_delimited_block_content($name, $attrs, $html);
}

function jl_heading(string $html, int $level = 2, ?string $class = null): string
{
    $attrs = [];
    if ($level !== 2) {
        $attrs['level'] = $level;
    }
    if ($class) {
        $attrs['className'] = $class;
    }
    $cls = trim('wp-block-heading '.($class ?? ''));

    return jl_block('core/heading', $attrs, "<h{$level} class=\"{$cls}\">".trim($html)."</h{$level}>");
}

function jl_paragraph(string $html, ?string $class = null): string
{
    $html = trim(preg_replace('/(\s*<br\s*\/?>)+\s*$/i', '', trim($html)));
    if ($html === '' || $html === '&nbsp;' || $html === "\xC2\xA0") {
        return '';
    }
    $attrs = $class ? ['className' => $class] : [];
    $open = $class ? "<p class=\"{$class}\">" : '<p>';

    return jl_block('core/paragraph', $attrs, $open.$html.'</p>');
}

function jl_list(DOMElement $el): string
{
    $ordered = strtolower($el->nodeName) === 'ol';
    $items = '';
    foreach ($el->childNodes as $li) {
        if ($li instanceof DOMElement && strtolower($li->nodeName) === 'li') {
            $items .= jl_block('core/list-item', [], '<li>'.jl_inner_html($li).'</li>');
        }
    }
    $tag = $ordered ? 'ol' : 'ul';

    return jl_block('core/list', $ordered ? ['ordered' => true] : [], "<{$tag} class=\"wp-block-list\">{$items}</{$tag}>");
}

function jl_html(string $html): string
{
    return jl_block('core/html', [], trim($html));
}

function jl_shortcode(string $code): string
{
    return jl_block('core/shortcode', [], trim($code));
}

function jl_image(int $id): string
{
    $src = wp_get_attachment_image_url($id, 'large');
    if (! $src) {
        return '';
    }
    $alt = esc_attr(get_post_meta($id, '_wp_attachment_image_alt', true));

    return jl_block(
        'core/image',
        ['id' => $id, 'sizeSlug' => 'large', 'linkDestination' => 'none'],
        "<figure class=\"wp-block-image size-large\"><img src=\"".esc_url($src)."\" alt=\"{$alt}\" class=\"wp-image-{$id}\"/></figure>"
    );
}

function jl_button(string $text, string $url): string
{
    $button = jl_block(
        'core/button',
        [],
        '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="'.esc_url($url).'">'.esc_html($text).'</a></div>'
    );

    return jl_block('core/buttons', [], '<div class="wp-block-buttons">'.$button.'</div>');
}

function jl_group(string $inner, ?string $class = null, ?string $background = null): string
{
    $attrs = [];
    if ($class) {
        $attrs['className'] = $class;
    }
    if ($background) {
        $attrs['style'] = ['background' => [
            'backgroundImage' => ['url' => $background, 'source' => 'file'],
            'backgroundSize' => 'cover',
            'backgroundPosition' => '50% 50%',
        ]];
    }
    $cls = trim('wp-block-group '.($class ?? ''));

    return jl_block('core/group', $attrs, "<div class=\"{$cls}\">{$inner}</div>");
}

function jl_columns(array $columns, ?string $class = null): string
{
    $html = '';
    foreach ($columns as [$inner, $width]) {
        $attrs = $width ? ['width' => $width] : [];
        $style = $width ? " style=\"flex-basis:{$width}\"" : '';
        $html .= jl_block('core/column', $attrs, "<div class=\"wp-block-column\"{$style}>{$inner}</div>");
    }
    $cls = trim('wp-block-columns '.($class ?? ''));

    return jl_block('core/columns', $class ? ['className' => $class] : [], "<div class=\"{$cls}\">{$html}</div>");
}

/* -------------------------------------------------------------------------
 * HTML → blocks (for Oxygen rich text and classic content)
 * ---------------------------------------------------------------------- */

function jl_inner_html(DOMNode $node): string
{
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }

    return trim($html);
}

function jl_html_to_blocks(string $html, ?string $headingClass = null): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $doc = new DOMDocument;
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="jl-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('jl-root') ?? $doc->documentElement;

    $blocks = '';
    $inline = '';
    $flush = function () use (&$inline, &$blocks) {
        $blocks .= jl_paragraph(preg_replace('/^(<br\s*\/?>\s*)+|(<br\s*\/?>\s*)+$/i', '', trim($inline)));
        $inline = '';
    };

    foreach ($root->childNodes as $node) {
        $name = $node instanceof DOMElement ? strtolower($node->nodeName) : '#text';

        if (preg_match('/^h([1-6])$/', $name, $m)) {
            $flush();
            $blocks .= jl_heading(jl_inner_html($node), (int) $m[1], $headingClass);
        } elseif ($name === 'p') {
            $flush();
            $inner = jl_inner_html($node);
            // A paragraph holding just a library image becomes an image block.
            if (preg_match('/^(?:<a [^>]*>)?<img [^>]*wp-image-(\d+)[^>]*>(?:<\/a>)?$/i', $inner, $img)) {
                $blocks .= jl_image((int) $img[1]);
            } else {
                $blocks .= jl_paragraph($inner);
            }
        } elseif (in_array($name, ['ul', 'ol'], true)) {
            $flush();
            $blocks .= jl_list($node);
        } elseif (in_array($name, ['div', 'table', 'figure', 'iframe', 'script', 'blockquote', 'hr'], true)) {
            $flush();
            $blocks .= jl_html($doc->saveHTML($node));
        } else {
            $inline .= $doc->saveHTML($node);
        }
    }
    $flush();

    return $blocks;
}

/* -------------------------------------------------------------------------
 * Oxygen JSON → blocks
 * ---------------------------------------------------------------------- */

function jl_oxygen_tree(int $postId): ?array
{
    $json = get_post_meta($postId, 'ct_builder_json', true);
    $tree = $json ? json_decode($json, true) : null;

    return is_array($tree) ? $tree : null;
}

/**
 * @param  array  $map  selector => ['class' => ?, 'skip' => bool, 'columns' => [widths], 'flatten' => bool]
 */
function jl_oxygen_to_blocks(array $node, array $map = []): string
{
    $out = '';
    foreach ($node['children'] ?? [] as $child) {
        $out .= jl_oxygen_node($child, $map);
    }

    return $out;
}

function jl_oxygen_node(array $node, array $map): string
{
    $o = $node['options'] ?? [];
    $orig = $o['original'] ?? [];
    $selector = $o['selector'] ?? '';
    $rule = $map[$selector] ?? [];

    if (! empty($rule['skip'])) {
        return '';
    }

    $content = html_entity_decode((string) ($o['ct_content'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $class = $rule['class'] ?? null;

    switch ($node['name']) {
        case 'ct_headline':
            $level = (int) ltrim((string) ($orig['tag'] ?? 'h1'), 'h') ?: 1;

            return jl_heading($content, $level, $class);

        case 'ct_text_block':
            return jl_paragraph(str_replace(["\r", "\n"], ' ', $content), $class);

        case 'oxy_rich_text':
            $blocks = jl_html_to_blocks(wpautop($content));

            return $class ? jl_group($blocks, $class) : $blocks;

        case 'ct_image':
            return ! empty($orig['attachment_id']) ? jl_image((int) $orig['attachment_id']) : '';

        case 'ct_code_block':
            $code = trim((string) ($orig['code-php'] ?? ''));

            return ($code === '' || $code === '<!-- -->' || str_contains($code, '<?')) ? '' : jl_html($code);

        case 'ct_shortcode':
            return ! empty($orig['full_shortcode']) ? jl_shortcode($orig['full_shortcode']) : '';

        case 'ct_link_button':
            return jl_button(wp_strip_all_tags($content), (string) ($orig['url'] ?? '#'));

        case 'ct_new_columns':
            $widths = $rule['columns'] ?? [];
            $columns = [];
            foreach ($node['children'] ?? [] as $i => $col) {
                $columns[] = [jl_oxygen_to_blocks($col, $map), $widths[$i] ?? null];
            }

            return jl_columns($columns, $class);

        case 'ct_section':
        case 'ct_div_block':
        default:
            $inner = jl_oxygen_to_blocks($node, $map);
            $background = $orig['background-image'] ?? null;
            $background = ($background && ! str_starts_with($background, '[oxygen')) ? $background : null;

            if ($class || $background) {
                return jl_group($inner, $class, $background);
            }

            return $inner;
    }
}

/* -------------------------------------------------------------------------
 * Migration
 * ---------------------------------------------------------------------- */

$log = [];

$pages = [
    'home' => get_page_by_path('home'),
    'contact' => get_page_by_path('contact'),
    'bars' => get_page_by_path('bars'),
    'whats-on' => get_page_by_path('whats-on'),
    'stay' => get_page_by_path('stay'),
    'shanty' => get_page_by_path('shanty'),
    'privacy-policy' => get_page_by_path('privacy-policy'),
    'cookie-policy' => get_page_by_path('cookie-policy'),
];

$content = [];

// Our Bars: intro text, then the four photo cards in a two-column grid.
if ($pages['bars'] && ($tree = jl_oxygen_tree($pages['bars']->ID))) {
    $content[$pages['bars']->ID] = jl_oxygen_to_blocks($tree, [
        'div_block-144-65' => ['class' => 'jl-photo-grid'],
        'div_block-150-65' => ['class' => 'jl-photo-card'],
        'div_block-14-65' => ['class' => 'jl-photo-card'],
        'div_block-79-65' => ['class' => 'jl-photo-card'],
        'div_block-111-65' => ['class' => 'jl-photo-card'],
        'div_block-151-65' => ['class' => 'jl-photo-card__caption'],
        'div_block-138-65' => ['class' => 'jl-photo-card__caption'],
        'div_block-121-65' => ['class' => 'jl-photo-card__caption'],
        'div_block-112-65' => ['class' => 'jl-photo-card__caption'],
    ]);
}

// Stay: the visible content lived in the Oxygen "Page - Stay" template (7449).
if ($pages['stay'] && ($tree = jl_oxygen_tree(7449))) {
    $content[$pages['stay']->ID] = jl_oxygen_to_blocks($tree, [
        'new_columns-732-7449' => ['class' => 'jl-stay-intro', 'columns' => ['60%', '40%']],
        'headline-744-7449' => ['class' => 'is-style-soft'],
        'new_columns-802-7449' => ['class' => 'jl-info-columns', 'columns' => ['33.33%', '33.33%', '33.34%']],
        '_rich_text-763-7449' => ['class' => 'jl-info'],
        '_rich_text-760-7449' => ['class' => 'jl-info'],
        '_rich_text-773-7449' => ['class' => 'jl-info'],
        '_rich_text-789-7449' => ['class' => 'jl-info'],
        '_rich_text-806-7449' => ['class' => 'jl-info'],
        '_rich_text-777-7449' => ['class' => 'jl-info'],
        '_rich_text-784-7449' => ['class' => 'jl-info'],
    ]);
}

// Whats On: the events lists are in the Sage template; the old content was a placeholder.
if ($pages['whats-on']) {
    $content[$pages['whats-on']->ID] = '';
}

// Classic content → blocks. On a re-run, convert from the saved original so
// already-converted content is not processed twice.
function jl_original_content(WP_Post $post): string
{
    $saved = get_post_meta($post->ID, '_jl_pre_sage_content', true);

    return metadata_exists('post', $post->ID, '_jl_pre_sage_content') ? (string) $saved : $post->post_content;
}

foreach (['home', 'contact', 'shanty'] as $slug) {
    if ($pages[$slug]) {
        $content[$pages[$slug]->ID] = jl_html_to_blocks(wpautop(jl_original_content($pages[$slug])));
    }
}
if ($songsheet = get_page_by_path('shanty-songsheet', OBJECT, 'post')) {
    $content[$songsheet->ID] = jl_html_to_blocks(wpautop(jl_original_content($songsheet)));
}

// Shanty: Oxygen added a PDF download button under the content.
if ($pages['shanty'] && ($tree = jl_oxygen_tree($pages['shanty']->ID))) {
    foreach ($tree['children'] ?? [] as $child) {
        if (($child['name'] ?? '') === 'ct_link_button') {
            $content[$pages['shanty']->ID] .= jl_oxygen_node($child, []);
        }
    }
}

$templates = [
    'contact' => 'template-contact.blade.php',
    'whats-on' => 'template-whats-on.blade.php',
    'privacy-policy' => 'template-two-column.blade.php',
    'cookie-policy' => 'template-two-column.blade.php',
];

if ($dryRun) {
    foreach ($content as $id => $blocks) {
        echo "\n===== ".get_the_title($id)." ({$id}) =====\n{$blocks}\n";
    }
    echo "\nTemplates:\n";
    foreach ($templates as $slug => $tpl) {
        echo "  {$slug} → {$tpl}\n";
    }
    exit("\nDry run only: nothing was changed.\n");
}

foreach ($content as $id => $blocks) {
    if (! metadata_exists('post', $id, '_jl_pre_sage_content')) {
        add_post_meta($id, '_jl_pre_sage_content', wp_slash(get_post_field('post_content', $id)));
    }
    wp_update_post(['ID' => $id, 'post_content' => wp_slash($blocks)]);
    $log[] = sprintf('Content → blocks: %s (%d)', get_the_title($id), $id);
}

foreach ($templates as $slug => $tpl) {
    if ($pages[$slug]) {
        update_post_meta($pages[$slug]->ID, '_wp_page_template', $tpl);
        $log[] = "Template: {$slug} → {$tpl}";
    }
}

// Theme + menus.
switch_theme('jennylind');
$locations = [];
foreach (['primary_navigation' => 2, 'legal_navigation' => 1233] as $location => $menuId) {
    if (wp_get_nav_menu_object($menuId)) {
        $locations[$location] = $menuId;
    }
}
set_theme_mod('nav_menu_locations', $locations);
$log[] = 'Theme activated: jennylind; menus: '.json_encode($locations);

// Event Organiser: let the theme render single events.
$eo = get_option('eventorganiser_options', []);
if (is_array($eo)) {
    $eo['templates'] = 0;
    update_option('eventorganiser_options', $eo);
    $log[] = 'Event Organiser templates: off (theme handles them)';
}

// Plugins that Sage replaces.
$off = array_values(array_filter([
    'oxygen/functions.php',
    'oxyextras/oxy-extras.php',
    'swiss-knife/swiss-knife.php',
    'classic-editor/classic-editor.php',
], 'is_plugin_active'));
if ($off) {
    deactivate_plugins($off);
    $log[] = 'Deactivated: '.implode(', ', $off);
}

flush_rewrite_rules();

echo implode("\n", $log)."\n\nDone. Open https://jennylindhastings.test in an Incognito window.\n";
