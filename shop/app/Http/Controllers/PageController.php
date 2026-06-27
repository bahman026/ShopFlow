<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Page\BuildPageDetail;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug, BuildPageDetail $buildPageDetail): Response
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->with('image')
            ->firstOrFail();

        return Inertia::render('Page/Show', [
            'page' => $buildPageDetail($page)->toArray(),
            'breadcrumbs' => [
                ['heading' => 'خانه', 'url' => '/'],
                ['heading' => $page->heading, 'url' => null],
            ],
        ]);
    }
}
