<?php

namespace Guiu\FilamentFilterPresets;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Guiu\FilamentFilterPresets\Components\ManageFiltersModal;

class FilamentFilterPresetsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-filter-presets';

    public static string $viewNamespace = 'filament-filter-presets';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-filter-presets')
            ->hasViews()
            ->hasTranslations()
            ->hasMigration('create_filter_presets_table');

        if (file_exists($this->getDistPath())) {
            $this->registerAssets();
        }
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        $this->registerLivewireComponents();

        // Registrem les traduccions
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-filter-presets');

        // Publiquem les traduccions
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/filament-filter-presets'),
        ], 'filament-filter-presets-translations');
    }

    public function packageRegistered(): void
    {
        // Registrem els serveis si n'hi ha
    }

    protected function registerLivewireComponents(): void
    {
        if (class_exists(Livewire::class)) {
            Livewire::component('manage-filters-modal', ManageFiltersModal::class);
        }
    }

    protected function registerAssets(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentIcon::register($this->getIcons());
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-filter-presets', __DIR__ . '/../dist/components/filament-filter-presets.js'),
            // Css::make('filament-filter-presets-styles', __DIR__ . '/../dist/filament-filter-presets.css'),
            // Js::make('filament-filter-presets-scripts', __DIR__ . '/../dist/filament-filter-presets.js'),
        ];
    }

    protected function getAssetPackageName(): ?string
    {
        return 'guiu/filament-filter-presets';
    }

    /**
     * @return array<string, string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    protected function getDistPath(): string
    {
        return __DIR__ . '/../dist';
    }
}
