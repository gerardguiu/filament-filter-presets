<?php

namespace Guiu\FilamentFilterPresets\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'filament-filter-presets:install';

    protected $description = 'Install Filament Filter Presets package';

    public function handle(): int
    {
        $this->comment('Installing Filament Filter Presets...');

        $this->comment('Publishing configuration...');
        $this->callSilently('vendor:publish', [
            '--tag' => 'filament-filter-presets-config',
        ]);

        $this->comment('Publishing migrations...');
        $this->callSilently('vendor:publish', [
            '--tag' => 'filament-filter-presets-migrations',
        ]);

        $this->comment('Running migrations...');
        $this->callSilently('migrate');

        $this->info('✅ Filament Filter Presets installed successfully!');

        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Add the HasFilterPresets trait to your Filament Resources');
        $this->line('2. Add filter preset actions to your table headers');
        $this->line('3. Configure available filters in your resources');
        $this->line('');
        $this->line('📖 Check the documentation for detailed setup instructions.');

        return self::SUCCESS;
    }
}
