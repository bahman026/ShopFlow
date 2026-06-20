<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'fa'], true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back(fallback: '/admin');
})->name('locale.switch');
