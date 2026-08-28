# Banners, Sliders & Positions

Status: **built (2026-08-07).** Every position in both enums has a render site
on the storefront, and the admin form shows a wireframe of where the selected
position lands. The `home_sections` table — an admin-composed home page the
storefront never read — has been removed; the storefront layout is fixed in
code.

## What a "position" is

A position is a **named slot on the storefront** — "home page, main slider",
"product page, sidebar". It is a fixed list defined in code, not free text an
admin types, because something has to render each slot and that something is a
Vue component.

Banners and sliders each carry one position. Slides do **not**: a slide belongs
to a slider (`slides.slider_id`), and the slider holds the position.

| Position | Kind | Where it renders | Component |
| --- | --- | --- | --- |
| `home-top` | banner | Full-width strip above the hero, home page | `BannerSlot` (`wide`) |
| `home-main` | slider | The hero, home page | `SliderSlot` (`hero`) |
| `home-middle` | banner | Promo grid below the categories, home page | `BannerSlot` (`grid`) |
| `home-secondary` | slider | Wide band between the promo grid and product rows | `SliderSlot` (`wide`) |
| `category-top` | slider | Under the title on every category page | `SliderSlot` (`wide`) |
| `category-side` | banner | Stacked in the category sidebar, under the filters | `BannerSlot` (`stack`) |
| `product-side` | slider | Under the buy box on every product page | `SliderSlot` (`portrait`) |

## Aspect ratios

Each slot pins its own ratio, so one oddly-shaped upload cannot stretch a grid
row or push the page around. The admin crops to the same ratio on upload
(`BannerPositionEnum::aspectRatio()` / `SliderPositionEnum::aspectRatio()`), and
the storefront component enforces it in CSS — **keep the two in step**.

| Position | Ratio | Upload at least | Storefront class |
| --- | --- | --- | --- |
| `home-top` | 5:1 | 1920 × 384 | `aspect-[3/1] sm:aspect-[5/1]` |
| `home-main` | 3:1 | 1920 × 640 | `aspect-[21/9] sm:aspect-[3/1]` |
| `home-middle` | 16:9 | 800 × 450 | `aspect-[16/9]` |
| `home-secondary` | 4:1 | 1920 × 480 | `aspect-[16/9] sm:aspect-[4/1]` |
| `category-top` | 4:1 | 1920 × 480 | `aspect-[16/9] sm:aspect-[4/1]` |
| `category-side` | 4:5 | 600 × 750 | `aspect-[4/5]` |
| `product-side` | 4:5 | 600 × 750 | `aspect-[4/5]` |

Product imagery (variety photos) is **1:1** everywhere — the card grid and the
product gallery are both `aspect-square` — so `VarietyResource` crops to `1:1`
and asks for at least 1000 × 1000.

The wide slots stay taller on phones (3:1 rather than 5:1, 16:9 rather than 4:1)
because a full-width strip at its desktop ratio would be an unreadable sliver on
a narrow screen. Recommended sizes are roughly 2× the rendered size, so images
stay sharp on a retina screen without being wasteful.

Every slot renders **nothing at all** when no published banner/slider is
assigned to it — the components return early rather than emitting an empty
container, so a page never has to guard the call.

Tables (all admin-owned migrations):

- `banners` — `position`, `heading`, `url`, `sort`, `status`
- `sliders` — `name`, `position`, `status`
- `slides` — `slider_id`, `heading`, `label`, `url`, `order`

## How admin and the storefront agree

The position is never a loose string on either side.

**Admin** offers a radio group built from the enum, each option carrying a
sentence describing exactly where it lands, plus a wireframe of the three
storefront pages with the selected slot highlighted
(`admin/resources/views/filament/forms/position-guide.blade.php`). Free text is
not possible.

**The storefront** looks up by the same enum case. Two actions serve every slot
on every page:

```php
// App\Actions\Layout
$getSliderByPosition(SliderPositionEnum::PRODUCT_SIDE);
$getBannersByPosition(BannerPositionEnum::CATEGORY_SIDE);
```

They live under `App\Actions\Layout` rather than `App\Actions\Home` because the
home, category and product pages all call them.

### The enums are mirrored, not shared

Each enum exists twice, once per app, with identical string values and
different translation keys. This matches how the rest of the repo mirrors admin
enums into read-focused shop models — but **nothing enforces the match**. Add a
case to one side only and the failure is silent: admins get a new option to
save, and the storefront never reads it.

The admin copies carry two extra methods the storefront does not need:
`description()` (the help line under each option) and `page()` (which wireframe
to highlight).

## Adding a new position

Not a config change — a code change in both apps, by design, because a
component has to render the slot.

1. `admin/app/Enums/<X>PositionEnum.php` — add the case, its `label()`,
   `description()`, `page()`, `aspectRatio()` and `recommendedSize()` arms.
2. `shop/app/Enums/<X>PositionEnum.php` — add the **same string value**.
   (`EnumMirrorTest` fails the build if you forget this step.)
3. `admin/lang/en/<banner|slider>.php` and `admin/lang/fa/…` — the label and
   description keys.
4. `shop/lang/fa/enums.php` — the label under `banner_position` /
   `slider_position`.
5. `admin/resources/views/filament/forms/position-guide.blade.php` — add a rect
   for the new slot so the wireframe shows it.
6. Render it: pass the slot from the page's controller and drop a `SliderSlot` /
   `BannerSlot` into the Vue page, with an aspect matching `aspectRatio()`.
   Without this the position is configurable but invisible.
7. `shop/tests/Feature/PositionSlotsTest.php` — assert it fills when assigned
   and is empty when not.

## Why `home_sections` was removed

The `home_sections` table let staff compose the home page from an ordered list
of typed blocks. The admin side was complete — resource, drag-to-reorder, a
`config` JSON bag — but the storefront never read the table, so reordering or
disabling a row changed nothing on the site.

Rather than finish the storefront half, the feature was dropped: the layout is
fixed in `Home.vue`, and the flexibility that was actually wanted — *what*
appears in each slot — is already covered by positions. What is no longer
possible is reordering blocks or adding a second product row without a deploy.

The rollback is `2026_08_07_000000_drop_home_sections_table`. The original
create migration is deleted, so a fresh database never builds the table;
`dropIfExists` removes it from databases that already ran it. The migration's
`down()` recreates the table, but restoring the feature would also mean
restoring the model, resource, enum, factory and seeder from git history.

## Keeping the two enums in step

The mirroring is enforced: `shop/tests/Feature/EnumMirrorTest.php` reads
`admin/app/Enums/*.php` and asserts that every storefront enum has an admin twin
with identical case names and backing values. A case added to one side only
fails the build instead of silently giving staff a value the storefront ignores.

That guard covers all 17 mirrored enums, not just the two position ones.
