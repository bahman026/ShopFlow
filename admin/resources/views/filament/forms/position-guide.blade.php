{{--
    Wireframe of the storefront pages, with the slot for the currently selected
    position highlighted.

    Receives:
      $get   - Filament's state accessor (injected into every component view)
      $kind  - 'banner' | 'slider', passed via ->viewData()

    The highlight is driven by a `data-selected` attribute on the wrapper plus
    one CSS rule per position, not by classes computed in PHP. Filament wraps
    each schema component in a wire:partial and this component's own state never
    changes, so a server round-trip cannot be relied on to repaint it — Alpine
    keeps the attribute in step with the radio instantly, and the server-rendered
    value covers the first paint.

    Coordinates are right-to-left, matching the Persian storefront: sidebars sit
    on the right, headings start on the right.

    Colours are inline rather than Tailwind classes: Filament ships a
    precompiled stylesheet that does not scan this file, so utility classes
    invented here would not exist.
--}}
@php
    // Banner/slider forms select by a `position` field. Other forms (tags) have
    // no position column, so they pass the slot to highlight directly.
    $selected ??= $get('position');
    $alpineExpression ??= "\$wire.data?.position ?? ''";

    /**
     * Slots per page. `kind` decides which ones this form can fill; the rest are
     * drawn as fixed page furniture so the layout stays recognisable.
     */
    $pages = [
        'home' => [
            'title' => trans('position_guide.page_home'),
            'height' => 198,
            'slots' => [
                ['x' => 4,  'y' => 4,  'w' => 112, 'h' => 8],
                ['x' => 4,  'y' => 16, 'w' => 112, 'h' => 11, 'kind' => 'banner', 'value' => 'home-top'],
                ['x' => 4,  'y' => 31, 'w' => 112, 'h' => 26, 'kind' => 'slider', 'value' => 'home-main'],
                // Category strip.
                ['x' => 4,  'y' => 61, 'w' => 112, 'h' => 10],
                ['x' => 81, 'y' => 75, 'w' => 35,  'h' => 17, 'kind' => 'banner', 'value' => 'home-middle'],
                ['x' => 43, 'y' => 75, 'w' => 34,  'h' => 17, 'kind' => 'banner', 'value' => 'home-middle'],
                ['x' => 4,  'y' => 75, 'w' => 35,  'h' => 17, 'kind' => 'banner', 'value' => 'home-middle'],
                ['x' => 4,  'y' => 96, 'w' => 112, 'h' => 15, 'kind' => 'slider', 'value' => 'home-secondary'],
                // Newest / most viewed product carousels.
                ['x' => 4,  'y' => 115,'w' => 112, 'h' => 19],
                ['x' => 4,  'y' => 138,'w' => 112, 'h' => 19],
                // One carousel per featured tag, after the standard rows.
                ['x' => 4,  'y' => 161,'w' => 112, 'h' => 19, 'kind' => 'tags', 'value' => 'home-tags'],
                // Brand strip.
                ['x' => 4,  'y' => 184,'w' => 112, 'h' => 10],
            ],
        ],
        'category' => [
            'title' => trans('position_guide.page_category'),
            'height' => 155,
            'slots' => [
                ['x' => 4,  'y' => 4,  'w' => 112, 'h' => 8],
                // Heading starts on the right.
                ['x' => 56, 'y' => 16, 'w' => 60,  'h' => 8],
                ['x' => 4,  'y' => 28, 'w' => 112, 'h' => 15, 'kind' => 'slider', 'value' => 'category-top'],
                // Sidebar on the right: filters, then the banner column.
                ['x' => 84, 'y' => 47, 'w' => 32,  'h' => 36],
                ['x' => 84, 'y' => 87, 'w' => 32,  'h' => 30, 'kind' => 'banner', 'value' => 'category-side'],
                ['x' => 4,  'y' => 47, 'w' => 76,  'h' => 32],
                ['x' => 4,  'y' => 83, 'w' => 76,  'h' => 32],
                ['x' => 4,  'y' => 119,'w' => 76,  'h' => 32],
            ],
        ],
        'product' => [
            'title' => trans('position_guide.page_product'),
            'height' => 155,
            'slots' => [
                ['x' => 4,  'y' => 4,  'w' => 112, 'h' => 8],
                ['x' => 56, 'y' => 16, 'w' => 60,  'h' => 6],
                // Gallery on the right, details in the middle, buy box on the
                // left — the RTL order of the real page.
                ['x' => 70, 'y' => 26, 'w' => 46,  'h' => 50],
                ['x' => 36, 'y' => 26, 'w' => 30,  'h' => 50],
                ['x' => 4,  'y' => 26, 'w' => 28,  'h' => 30],
                ['x' => 4,  'y' => 60, 'w' => 28,  'h' => 34, 'kind' => 'slider', 'value' => 'product-side'],
                ['x' => 4,  'y' => 100,'w' => 112, 'h' => 22],
                ['x' => 4,  'y' => 126,'w' => 112, 'h' => 22],
            ],
        ],
    ];

    // value => page, used to build one highlight rule per position.
    $slotPages = [];

    foreach ($pages as $pageKey => $page) {
        foreach ($page['slots'] as $slot) {
            if (isset($slot['value'])) {
                $slotPages[$slot['value']] = $pageKey;
            }
        }
    }
@endphp

<div
    class="pg"
    data-selected="{{ $selected }}"
    x-data
    {{-- Resource forms keep their state under `data`. --}}
    x-bind:data-selected="{{ $alpineExpression }}"
>
    <div class="pg__grid">
        @foreach ($pages as $pageKey => $page)
            @php
                // A page is dimmed unless it holds a slot this form can fill.
                $hasKind = collect($page['slots'])
                    ->contains(fn (array $slot): bool => ($slot['kind'] ?? null) === $kind);
            @endphp

            <figure
                class="pg__card {{ $hasKind ? '' : 'pg__card--muted' }}"
                data-page="{{ $pageKey }}"
            >
                <svg viewBox="0 0 120 {{ $page['height'] }}" class="pg__svg" role="img"
                     aria-label="{{ $page['title'] }}">
                    @foreach ($page['slots'] as $slot)
                        <rect
                            x="{{ $slot['x'] }}" y="{{ $slot['y'] }}"
                            width="{{ $slot['w'] }}" height="{{ $slot['h'] }}"
                            rx="3"
                            @isset($slot['value']) data-slot="{{ $slot['value'] }}" @endisset
                            class="pg__slot {{ ($slot['kind'] ?? null) === $kind ? 'pg__slot--available' : '' }}"
                        />
                    @endforeach
                </svg>
                <figcaption class="pg__caption">{{ $page['title'] }}</figcaption>
            </figure>
        @endforeach
    </div>

    <p class="pg__legend">
        <span class="pg__key pg__key--active"></span>{{ trans('position_guide.legend_selected') }}
        <span class="pg__key pg__key--available"></span>{{ trans('position_guide.legend_available') }}
        <span class="pg__key"></span>{{ trans('position_guide.legend_other') }}
    </p>
</div>

<style>
    .pg__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .pg__card {
        border: 1px solid rgb(229 231 235);
        border-radius: .5rem;
        padding: .5rem;
        background: rgb(249 250 251);
        transition: border-color .15s, opacity .15s;
    }
    .pg__card--muted { opacity: .45; }
    .pg__svg { width: 100%; height: auto; display: block; }
    .pg__caption {
        margin-top: .375rem;
        text-align: center;
        font-size: .6875rem;
        color: rgb(107 114 128);
    }
    .pg__slot { fill: rgb(229 231 235); transition: fill .15s; }
    .pg__slot--available { fill: rgb(191 219 254); }

    /* One rule per position: the wrapper's data-selected drives the highlight,
       so Alpine only has to keep a single attribute up to date. */
    @foreach ($slotPages as $value => $pageKey)
    .pg[data-selected="{{ $value }}"] [data-slot="{{ $value }}"] { fill: #ff8615; }
    .pg[data-selected="{{ $value }}"] .pg__card[data-page="{{ $pageKey }}"] { border-color: #ff8615; }
    @endforeach

    .pg__legend {
        display: flex;
        align-items: center;
        gap: .375rem;
        flex-wrap: wrap;
        margin-top: .625rem;
        font-size: .6875rem;
        color: rgb(107 114 128);
    }
    .pg__key {
        display: inline-block;
        width: .75rem; height: .75rem;
        border-radius: .1875rem;
        background: rgb(229 231 235);
        margin-inline-start: .5rem;
    }
    .pg__key:first-child { margin-inline-start: 0; }
    .pg__key--active { background: #ff8615; }
    .pg__key--available { background: rgb(191 219 254); }

    .dark .pg__card { background: rgb(31 41 55); border-color: rgb(55 65 81); }
    .dark .pg__slot { fill: rgb(55 65 81); }
    .dark .pg__slot--available { fill: rgb(30 64 175); }
    .dark .pg__key { background: rgb(55 65 81); }
    .dark .pg__key--available { background: rgb(30 64 175); }
</style>
