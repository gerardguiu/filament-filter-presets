<?php

namespace Guiu\FilamentFilterPresets;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentFilterPresetsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-filter-presets';

    public static string $viewNamespace = 'filament-filter-presets';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_filter_presets_table',
            ])
            ->hasCommands([
                Commands\InstallCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        parent::packageRegistered();
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        // Register the asset files
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
    }

    protected function getAssets(): array
    {
        return [
            // You can add custom CSS/JS assets here if needed
        ];
    }

    protected function getAssetPackageName(): ?string
    {
        return 'guiu/filament-filter-presets';
    }
}
