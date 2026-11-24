# Jenny Lind – Sage theme

WordPress theme for [The Jenny Lind Inn](https://jennylindhastings.co.uk), built on
[Sage 11](https://roots.io/sage/) (Acorn, Blade, Vite, Tailwind v4). It replaces the
Oxygen Builder templates the site used before.

## Oxygen → Sage map

| Oxygen template | Sage |
| --- | --- |
| Main Structure (6417) | `resources/views/layouts/app.blade.php` |
| Header v2 (8221) | `sections/header.blade.php` + `partials/hero.blade.php` |
| Footer (6734) | `sections/footer.blade.php` |
| Page (7214) – default | `page.blade.php` |
| Homepage (7225) + Welcome page layout | `front-page.blade.php` |
| Page – 2 col (7217) | `template-two-column.blade.php` ("Two Column + Whats On") |
| Contact (8414) | `template-contact.blade.php` ("Contact") |
| Page – Events – All (7429) | `template-whats-on.blade.php` ("Whats On") |
| Side events (8604) / Whats on side (8385) / Event Card (7076) | `partials/events/side.blade.php` |
| Page – Stay (7449) | content moved into the Stay page as blocks |
| Breadcrumbs (7213), trip advisor (8654), Header (6914) | not used on the live pages; not ported |

Design tokens (colours, Paddington + Source Sans) are in `resources/css/app.css`.
Event queries are in `app/events.php` (Event Organiser, same ordering as Oxygen's repeaters).

## Install (local, Herd)

The theme lives at `wp-content/themes/jennylind` in the site.

```bash
cd wp-content/themes
git clone git@github.com:CINQDEV/jennylind-sage.git jennylind
cd jennylind
composer install
npm install
npm run build        # or: npm run dev
```

Requires PHP 8.2+, Node 20.19+ / 22.12+.

## One-off content migration

`bin/migrate-oxygen.php` moves the site off Oxygen:

- converts Our Bars, Stay, Welcome, Contact and Shanty content to Gutenberg blocks
  (old content kept in the `_jl_pre_sage_content` post meta),
- assigns the new page templates,
- activates this theme and sets the menus (Main Menu → primary, Footer → legal),
- turns off Event Organiser's own templates,
- deactivates Oxygen, OxyExtras, Swiss Knife and Classic Editor (reactivate to roll back).

From the site root:

```bash
php wp-content/themes/jennylind/bin/migrate-oxygen.php --dry-run   # preview
php wp-content/themes/jennylind/bin/migrate-oxygen.php             # apply
```

## Deploying

`public/build` and `vendor` are not committed: run `composer install --no-dev` and
`npm ci && npm run build` as part of the deploy, or upload them with the theme.
