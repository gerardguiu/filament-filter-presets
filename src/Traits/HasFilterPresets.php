<?php

namespace Guiu\FilamentFilterPresets\Traits;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Guiu\FilamentFilterPresets\Models\FilterPreset;
use Guiu\FilamentFilterPresets\Components\ManageFiltersModal;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Ray;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Log;

trait HasFilterPresets
{
    public function getTableFiltersForm(): Form
    {
        return parent::getTableFiltersForm();
    }

    public function getDefaultTableRecordsPerPageSelectOption(): int
    {
        // Primer cridem al mètode del pare per mantenir la funcionalitat original
        $result = parent::getDefaultTableRecordsPerPageSelectOption();

        // Després apliquem el filtre predeterminat si existeix
        $this->loadDefaultFilterPreset();

        return $result;
    }

    protected function loadDefaultFilterPreset(): void
    {
        try {
            $defaultPreset = FilterPreset::where('user_id', Auth::id())
                ->where('resource_class', get_class($this))
                ->where('is_default', true)
                ->first();

            if ($defaultPreset) {
                Log::info('Applying default filter preset', [
                    'preset' => $defaultPreset->name,
                    'filters' => $defaultPreset->filters,
                    'class' => get_class($this)
                ]);

                // Apliquem els filtres directament
                $this->tableFilters = $defaultPreset->filters;
            }
        } catch (\Exception $e) {
            Log::error('Error applying default filter preset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public static function getFilterPresetHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('save_filters')
                    ->label(__('filament-filter-presets::labels.save_filters'))
                    ->icon('heroicon-m-bookmark')
                    ->form([
                        TextInput::make('name')
                            ->label(__('filament-filter-presets::labels.filter_name'))
                            ->required(),
                        Textarea::make('description')
                            ->label(__('filament-filter-presets::labels.description')),
                        Toggle::make('is_default')
                            ->label(__('filament-filter-presets::labels.set_as_default')),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $filters = $livewire->getTableFiltersForm()->getState();

                        Log::info('Saving filters', [
                            'filters' => $filters,
                            'data' => $data,
                            'class' => get_class($livewire)
                        ]);

                        $livewire->saveFilterPreset($data);
                    }),

                Action::make('load_filters')
                    ->label(__('filament-filter-presets::labels.load_filters'))
                    ->icon('heroicon-m-funnel')
                    ->form([
                        Select::make('preset_id')
                            ->label(__('filament-filter-presets::labels.select_saved_filter'))
                            ->options(function ($livewire) {
                                return FilterPreset::where('user_id', Auth::id())
                                    ->where('resource_class', get_class($livewire))
                                    ->orderBy('is_default', 'desc')
                                    ->orderBy('name', 'asc')
                                    ->get()
                                    ->mapWithKeys(function ($preset) {
                                        return [
                                            $preset->id => $preset->is_default
                                                ? $preset->name . ' (' . __('filament-filter-presets::labels.default') . ')'
                                                : $preset->name
                                        ];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->placeholder(__('filament-filter-presets::labels.select_filter_placeholder')),
                    ])
                    ->action(function (array $data, $livewire) {
                        $livewire->loadFilterPreset((int) $data['preset_id']);
                    }),

                Action::make('manage_filters')
                    ->label(__('filament-filter-presets::labels.manage_filters'))
                    ->icon('heroicon-m-cog-6-tooth')
                    ->modalHeading(__('filament-filter-presets::labels.manage_filters'))
                    ->modalContent(fn ($livewire) => view('filament-filter-presets::components.manage-filters-modal', [
                        'presets' => FilterPreset::where('user_id', Auth::id())
                            ->where('resource_class', get_class($livewire))
                            ->get(),
                        'resourceClass' => get_class($livewire),
                    ])),
            ])
                ->label(__('filament-filter-presets::labels.filter_presets'))
                ->icon('heroicon-m-funnel')
                ->size(ActionSize::Small)
                ->color('gray')
                ->button(),
        ];
    }

    protected function saveFilterPreset(array $data): void
    {
        $rawFilters = $this->getTableFiltersForm()->getState();

        // Processem els filtres per mantenir l'estructura correcta
        $filters = collect($rawFilters)
            ->map(function ($filter) {
                if (is_array($filter)) {
                    return [
                        'values' => $filter['values'] ?? []
                    ];
                }
                return ['values' => [$filter]];
            })
            ->filter(function ($filter) {
                return !empty($filter['values']);
            })
            ->toArray();

        Log::info('saveFilterPreset method', [
            'rawFilters' => $rawFilters,
            'processedFilters' => $filters,
            'data' => $data,
            'class' => get_class($this)
        ]);

        if (empty($filters)) {
            Notification::make()
                ->title(__('filament-filter-presets::messages.error'))
                ->body(__('filament-filter-presets::messages.no_filters_to_save'))
                ->danger()
                ->send();
            return;
        }

        try {
            $filterPreset = FilterPreset::create([
                'user_id' => Auth::id(),
                'name' => $data['name'],
                'resource_class' => get_class($this),
                'filters' => $filters,
                'description' => $data['description'] ?? null,
                'is_default' => $data['is_default'] ?? false,
            ]);

            if ($data['is_default'] ?? false) {
                FilterPreset::where('resource_class', get_class($this))
                    ->where('user_id', Auth::id())
                    ->where('id', '!=', $filterPreset->id)
                    ->update(['is_default' => false]);
            }

            Notification::make()
                ->title(__('filament-filter-presets::messages.success'))
                ->body(__('filament-filter-presets::messages.filter_saved', ['name' => $data['name']]))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error saving filter preset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title(__('filament-filter-presets::messages.error'))
                ->body(__('filament-filter-presets::messages.save_error', ['error' => $e->getMessage()]))
                ->danger()
                ->send();
        }
    }

    protected function loadFilterPreset(int $presetId): void
    {
        try {
            $preset = FilterPreset::where('id', $presetId)
                ->where('user_id', Auth::id())
                ->where('resource_class', get_class($this))
                ->firstOrFail();

            $this->applyFilterPreset($preset);
        } catch (\Exception $e) {
            Log::error('Error loading filter preset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error')
                ->body(__('filament-filter-presets::messages.preset_not_found'))
                ->danger()
                ->send();
        }
    }

    protected function applyFilterPreset(FilterPreset $preset, bool $showNotification = true): void
    {
        try {
            $this->resetTableFiltersForm();
            $this->tableFilters = $preset->filters;
            $this->getTableFiltersForm()->fill($preset->filters);

            Log::info('Applying filter preset', [
                'filters' => $preset->filters,
                'class' => get_class($this)
            ]);

            if ($showNotification) {
                Notification::make()
                    ->title(__('filament-filter-presets::messages.success'))
                    ->body(__('filament-filter-presets::messages.filter_loaded', ['name' => $preset->name]))
                    ->success()
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error('Error applying filter preset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($showNotification) {
                Notification::make()
                    ->title(__('filament-filter-presets::messages.error'))
                    ->body(__('filament-filter-presets::messages.load_error', ['error' => $e->getMessage()]))
                    ->danger()
                    ->send();
            }
        }
    }

    public function toggleDefaultFilter(int $presetId): void
    {
        try {
            $preset = FilterPreset::where('id', $presetId)
                ->where('user_id', Auth::id())
                ->where('resource_class', get_class($this))
                ->firstOrFail();

            if (!$preset->is_default) {
                FilterPreset::where('resource_class', get_class($this))
                    ->where('user_id', Auth::id())
                    ->where('id', '!=', $preset->id)
                    ->update(['is_default' => false]);
            }

            $preset->update(['is_default' => !$preset->is_default]);

            Notification::make()
                ->title(__('filament-filter-presets::messages.success'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error toggling default filter', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title(__('filament-filter-presets::messages.error'))
                ->body(__('filament-filter-presets::messages.save_error', ['error' => $e->getMessage()]))
                ->danger()
                ->send();
        }
    }

    public function deletePreset(int $presetId): void
    {
        try {
            FilterPreset::where('id', $presetId)
                ->where('user_id', Auth::id())
                ->where('resource_class', get_class($this))
                ->delete();

            Notification::make()
                ->title(__('filament-filter-presets::messages.success'))
                ->body(__('filament-filter-presets::messages.delete_success'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error deleting filter preset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title(__('filament-filter-presets::messages.error'))
                ->body(__('filament-filter-presets::messages.delete_error', ['error' => $e->getMessage()]))
                ->danger()
                ->send();
        }
    }

    /**
     * Apply default filter preset if it exists
     */
    public static function applyDefaultFilterPreset($livewire): void
    {
        if (!Auth::check()) {
            return;
        }

        $resourceClass = $livewire->getResource();
        $defaultPreset = FilterPreset::getDefaultForUserAndResource(Auth::id(), $resourceClass);

        if ($defaultPreset) {
            // Get normalized filters and convert them to Filament structure
            $normalizedFilters = $defaultPreset->getNormalizedFilters();
            $filamentFilters = FilterPreset::convertToFilamentStructure($normalizedFilters);

            // Apply the filters
            $livewire->tableFilters = $filamentFilters;
        }
    }

    /**
     * Generate a preview of what filters the preset contains
     */
    protected static function generateFilterPreview(FilterPreset $preset): string
    {
        if (empty($preset->description)) {
            return 'No description available';
        }

        return $preset->description;
    }

    /**
     * Get display name for a filter
     */
    protected static function getFilterDisplayName(string $filterName): string
    {
        // Convert snake_case to Title Case
        return ucfirst(str_replace('_', ' ', $filterName));
    }

    /**
     * Format filter value for display
     */
    protected static function formatFilterValue(string $filterName, $value): ?string
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return null;
        }

        // Format arrays
        if (is_array($value)) {
            return implode(', ', $value);
        }

        // Format dates
        if (str_contains($filterName, 'date') || str_contains($filterName, 'created') || str_contains($filterName, 'updated')) {
            try {
                if ($value instanceof \Carbon\Carbon) {
                    return $value->format('d/m/Y');
                }
                if (is_string($value)) {
                    return \Carbon\Carbon::parse($value)->format('d/m/Y');
                }
            } catch (\Exception $e) {
                // Fall back to original value if date parsing fails
            }
        }

        return (string) $value;
    }

    /**
     * Define filter preset configuration for this resource
     * Override this method in your resource to define available filters
     */
    public static function getFilterPresetConfiguration(): array
    {
        return [];
    }
}
