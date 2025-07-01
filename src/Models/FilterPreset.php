<?php

namespace Guiu\FilamentFilterPresets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FilterPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'resource_class',
        'filters',
        'description',
        'is_default',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }

    /**
     * Get filter presets for a specific user and resource
     */
    public static function getForUserAndResource(int $userId, string $resourceClass): Collection
    {
        return static::where('user_id', $userId)
            ->where('resource_class', $resourceClass)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get the default filter preset for a user and resource
     */
    public static function getDefaultForUserAndResource(int $userId, string $resourceClass): ?self
    {
        return static::where('user_id', $userId)
            ->where('resource_class', $resourceClass)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Set this filter as default and disable others
     */
    public function setAsDefault(): void
    {
        // Disable all other default filters
        static::where('user_id', $this->user_id)
            ->where('resource_class', $this->resource_class)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Enable this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Get available filters for a specific resource
     * This method should be overridden by the implementing application
     */
    public static function getAvailableFiltersForResource(string $resourceClass): array
    {
        // Check if the resource class has a method to define its filters
        if (method_exists($resourceClass, 'getFilterPresetConfiguration')) {
            return $resourceClass::getFilterPresetConfiguration();
        }

        return [];
    }

    /**
     * Get filter configuration for a specific filter
     */
    public function getFilterConfig(string $filterName): ?array
    {
        $availableFilters = static::getAvailableFiltersForResource($this->resource_class);
        return $availableFilters[$filterName] ?? null;
    }

    /**
     * Normalize filters from Filament's complex structure to a flat structure
     */
    public function getNormalizedFilters(): array
    {
        return $this->filters ?? [];
    }

    /**
     * Convert normalized filters to Filament's expected structure
     */
    public static function convertToFilamentStructure(array $normalizedFilters): array
    {
        return $normalizedFilters;
    }

    /**
     * Check if a filter is a custom filter
     * This can be extended by applications
     */
    protected static function isCustomFilter(string $filterName): bool
    {
        // Common custom filter patterns
        $customFilters = ['data', 'date_range'];

        return in_array($filterName, $customFilters) ||
               str_contains($filterName, '_range') ||
               str_contains($filterName, '_custom');
    }

    /**
     * Validate if filters are valid for the resource
     */
    public function validateFilters(): bool
    {
        try {
            $normalizedFilters = $this->getNormalizedFilters();
            $availableFilters = static::getAvailableFiltersForResource($this->resource_class);

            foreach ($normalizedFilters as $filterName => $value) {
                // Ensure the key is a valid string or integer
                if (!is_string($filterName) && !is_int($filterName)) {
                    continue;
                }

                // Check if the filter is available (optional check)
                if (!empty($availableFilters) && !array_key_exists($filterName, $availableFilters)) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Filter validation error', [
                'preset_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
