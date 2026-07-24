<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Faq\GetFaqs;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function show(GetFaqs $getFaqs, ?string $position = null): Response
    {
        return Inertia::render('Faq/Show', [
            'position' => $position,
            'faqs' => $getFaqs($position),
            'breadcrumbs' => [
                ['heading' => 'خانه', 'url' => '/'],
                ['heading' => 'سوالات متداول', 'url' => null],
            ],
        ]);
    }
}
