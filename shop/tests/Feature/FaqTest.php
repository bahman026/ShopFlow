<?php

declare(strict_types=1);

use App\Models\Faq;
use Inertia\Testing\AssertableInertia;

it('renders the main FAQ page with null-position questions', function (): void {
    Faq::create(['heading' => 'سوال اصلی', 'content' => 'پاسخ', 'order' => 1, 'position' => null]);
    Faq::create(['heading' => 'سوال صفحه خانه', 'content' => 'پاسخ', 'order' => 1, 'position' => 'homepage']);

    $this->get('/faq')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $assert): AssertableInertia => $assert
            ->component('Faq/Show')
            ->where('position', null)
            ->has('faqs', 1)
            ->where('faqs.0.heading', 'سوال اصلی')
        );
});

it('renders a section FAQ page by position', function (): void {
    Faq::create(['heading' => 'سوال اصلی', 'content' => 'پاسخ', 'order' => 1, 'position' => null]);
    Faq::create(['heading' => 'سوال صفحه خانه', 'content' => 'پاسخ', 'order' => 1, 'position' => 'homepage']);

    $this->get('/faq/homepage')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $assert): AssertableInertia => $assert
            ->where('position', 'homepage')
            ->has('faqs', 1)
            ->where('faqs.0.heading', 'سوال صفحه خانه')
        );
});

it('orders FAQs by their order column', function (): void {
    Faq::create(['heading' => 'دوم', 'content' => 'ب', 'order' => 2, 'position' => null]);
    Faq::create(['heading' => 'اول', 'content' => 'الف', 'order' => 1, 'position' => null]);

    $this->get('/faq')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $assert): AssertableInertia => $assert
            ->where('faqs.0.heading', 'اول')
            ->where('faqs.1.heading', 'دوم')
        );
});
