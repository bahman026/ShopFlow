<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Versioning;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\Column;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        TextEntry::configureUsing(function (TextEntry $textEntry): void {
            $textEntry
                ->placeholder('-');
        });

        Column::configureUsing(function (Column $column): void {
            $column
                ->placeholder('-')
                ->toggleable()
                ->searchable()
                ->sortable();
        });

        FilamentIcon::register([
            'panels::sidebar.expand-button' => 'heroicon-o-bars-3',
            'panels::sidebar.collapse-button' => 'heroicon-o-bars-3',
        ]);

        FilamentView::registerRenderHook(
            name: 'panels::sidebar.footer',
            hook: function (): View {
                $versioning = new Versioning;

                return view('filament.sidebar-widget', [
                    'name' => $versioning->getName(),
                    'version' => $versioning->getVersion(),
                ]);
            }
        );
    }
}
