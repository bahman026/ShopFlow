<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Review\CreateReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product, CreateReview $createReview): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'heading' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $createReview($user, $product, [
            'heading' => $validated['heading'],
            'content' => $validated['content'],
            'rating' => (int) $validated['rating'],
        ]);

        return back()->with('status', trans('messages.review.submitted'));
    }
}
