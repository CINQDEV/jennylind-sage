<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class App extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '*',
    ];

    /**
     * Retrieve the site name.
     */
    public function siteName(): string
    {
        return get_bloginfo('name', 'display');
    }

    /**
     * Title shown in the red label on the hero (Oxygen "Header v2" section).
     */
    public function heroTitle(): string
    {
        if (is_home()) {
            return get_the_title(get_option('page_for_posts')) ?: __('News', 'jennylind');
        }

        if (is_singular()) {
            return get_the_title(get_queried_object_id());
        }

        if (is_post_type_archive('event') || is_tax('event-category') || is_tax('event-venue')) {
            return __('Whats On', 'jennylind');
        }

        if (is_archive()) {
            return wp_strip_all_tags(get_the_archive_title());
        }

        if (is_search()) {
            /* translators: %s is replaced with the search query */
            return sprintf(__('Search: %s', 'jennylind'), get_search_query());
        }

        if (is_404()) {
            return __('Not Found', 'jennylind');
        }

        return get_bloginfo('name', 'display');
    }

    /**
     * Hero background: the featured image of the current page/post (as Oxygen
     * did), falling back to the Whats On page image for archives.
     */
    public function heroImage(): ?string
    {
        $id = null;

        if (is_singular()) {
            $id = get_queried_object_id();
        } elseif (is_home()) {
            $id = (int) get_option('page_for_posts');
        } elseif (is_post_type_archive('event')) {
            $id = get_page_by_path('whats-on')?->ID;
        }

        $url = $id ? get_the_post_thumbnail_url($id, 'large') : null;

        return $url ?: null;
    }
}
