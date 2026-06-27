<?php

declare(strict_types=1);

namespace App\Actions\Faq;

use App\DTOs\FaqDTO;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Builder;

class GetFaqs
{
    /**
     * Ordered FAQs for a placement. A null position is the main FAQ page;
     * any other value scopes to that section.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(?string $position): array
    {
        return Faq::query()
            ->when(
                $position === null,
                fn (Builder $query) => $query->whereNull('position'),
                fn (Builder $query) => $query->where('position', $position),
            )
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(fn (Faq $faq): array => (new FaqDTO(
                id: $faq->id,
                heading: $faq->heading,
                content: $faq->content,
            ))->toArray())
            ->all();
    }
}
