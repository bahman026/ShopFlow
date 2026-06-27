<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Search\GetSearchSuggestions;
use App\Contracts\ProductSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request, ProductSearch $search): Response
    {
        $term = trim((string) $request->query('q', ''));
        $sort = $this->sort($request);

        return Inertia::render('Search/Results', [
            'query' => $term,
            'products' => $search->search($term, ['sort' => $sort]),
            'applied' => ['sort' => $sort],
        ]);
    }

    public function suggest(Request $request, GetSearchSuggestions $getSuggestions): JsonResponse
    {
        return response()->json($getSuggestions((string) $request->query('q', '')));
    }

    private function sort(Request $request): string
    {
        $sort = (string) $request->query('sort', 'newest');

        return in_array($sort, ['newest', 'cheapest', 'expensive', 'popular'], true)
            ? $sort
            : 'newest';
    }
}
