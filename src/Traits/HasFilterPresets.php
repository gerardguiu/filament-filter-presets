<?php

namespace Guiu\FilamentFilterPresets\Traits;

use Guiu\FilamentFilterPresets\Models\FilterPreset;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

trait HasFilterPresets
{
    /**
     * Get filter preset actions for table headers
     */
    public static function getFilterPresetActions(): array
    {
        return [
            Action::make('save_filter_preset')
                ->label('Save Filters')
                ->icon('heroicon-o-bookmark')
                ->color('success')
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label('Filter Name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->maxLength(500)
                        ->rows(2),
                    Forms\Components\Toggle::make('is_default')
                        ->label('Set as default filter')
                        ->default(false),
                ])
                ->modalWidth(MaxWidth::Medium)
                ->action(function (array $data, $livewire) {
                    try {
                        $currentFilters = $livewire->tableFilters ?? [];

                        // Ensure we have a valid array
                        if (!is_array($currentFilters)) {
                            $currentFilters = [];
                        }

                        // Remove empty filters and ensure valid keys
                        $currentFilters = array_filter($currentFilters, function ($value, $key) {
                            // Ensure the key is valid
                            if (!is_string($key) && !is_int($key)) {
                                return false;
                            }
                            return !empty($value) && $value !== [] && $value !== null;
                        }, ARRAY_FILTER_USE_BOTH);

                        if (empty($currentFilters)) {
                            Notification::make()
                                ->title('Error')
                                ->body('No filters applied to save.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $resourceClass = static::class;
                        $filterPreset = FilterPreset::create([
                            'user_id' => Auth::id(),
                            'name' => $data['name'],
                            'resource_class' => $resourceClass,
                            'filters' => $currentFilters,
                            'description' => $data['description'] ?? null,
                            'is_default' => $data['is_default'] ?? false,
                        ]);

                        if ($data['is_default'] ?? false) {
                            $filterPreset->setAsDefault();
                        }

                        Notification::make()
                            ->title('Filter Saved')
                            ->body("Filter '{$data['name']}' has been saved successfully.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Could not save filters: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('load_filter_preset')
                ->label('Load Filters')
                ->icon('heroicon-o-funnel')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('preset_id')
                        ->label('Select a saved filter')
                        ->options(function () {
                            if (!Auth::check()) {
                                return [];
                            }

                            $resourceClass = static::class;
                            return FilterPreset::getForUserAndResource(Auth::id(), $resourceClass)
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $preset = FilterPreset::find($state);
                                if ($preset) {
                                    $previewText = static::generateFilterPreview($preset);
                                    $set('preview', $previewText);
                                } else {
                                    $set('preview', '❌ Filter not found');
                                }
                            } else {
                                $set('preview', 'Select a filter to see its description');
                            }
                        }),
                    Forms\Components\Placeholder::make('preview')
                        ->content('Select a filter to see its description'),
                ])
                ->modalWidth(MaxWidth::Medium)
                ->action(function (array $data, $livewire) {
                    try {
                        $preset = FilterPreset::find($data['preset_id']);

                        if (!$preset) {
                            Notification::make()
                                ->title('Error')
                                ->body('Filter not found.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Get normalized filters and convert them to Filament structure
                        $normalizedFilters = $preset->getNormalizedFilters();
                        $filamentFilters = FilterPreset::convertToFilamentStructure($normalizedFilters);

                        // Apply the filters
                        $livewire->tableFilters = $filamentFilters;

                        Notification::make()
                            ->title('Filters Applied')
                            ->body("Applied filters from preset '{$preset->name}'.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Could not apply filters: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('manage_filter_presets')
                ->label('Manage Filters')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(function () {
                    // Try to generate URL to filter presets resource if it exists
                    try {
                        return route('filament.admin.resources.filter-presets.index');
                    } catch (\Exception $e) {
                        return '#';
                    }
                })
                ->openUrlInNewTab(),
        ];
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
        $previewText = '';

        // Add description
        if ($preset->description) {
            $previewText .= "📝 Description: " . $preset->description . "\n\n";
        }

        // Add applied filters
        $normalizedFilters = $preset->getNormalizedFilters();
        if (!empty($normalizedFilters)) {
            $previewText .= "🔍 Applied filters:\n";

            foreach ($normalizedFilters as $filterName => $value) {
                $displayName = static::getFilterDisplayName($filterName);
                $displayValue = static::formatFilterValue($filterName, $value);

                if ($displayValue !== null) {
                    $previewText .= "• {$displayName}: {$displayValue}\n";
                }
            }
        } else {
            $previewText .= "ℹ️ This filter has no applied filters.";
        }

        return trim($previewText);
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
