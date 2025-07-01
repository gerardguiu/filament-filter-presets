<?php

namespace Guiu\FilamentFilterPresets\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Collection;
use Guiu\FilamentFilterPresets\Models\FilterPreset;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ManageFiltersModal extends Component
{
    public string $resourceClass;
    public Collection $presets;

    public function mount(string $resourceClass)
    {
        $this->resourceClass = $resourceClass;
        $this->loadPresets();
    }

    protected function loadPresets(): void
    {
        $this->presets = FilterPreset::where('user_id', Auth::id())
            ->where('resource_class', $this->resourceClass)
            ->get();
    }

    public function getPresets(): Collection
    {
        return $this->presets;
    }

    public function deletePreset(int $presetId): void
    {
        FilterPreset::where('id', $presetId)->delete();
        $this->loadPresets();
    }

    public function toggleDefault(int $presetId): void
    {
        $preset = FilterPreset::find($presetId);

        if (!$preset) {
            return;
        }

        // Si estem activant aquest com a predeterminat, desactivem els altres
        if (!$preset->is_default) {
            FilterPreset::where('resource_class', $this->resourceClass)
                ->where('user_id', Auth::id())
                ->where('id', '!=', $presetId)
                ->update(['is_default' => false]);
        }

        $preset->update(['is_default' => !$preset->is_default]);
        $this->loadPresets();
    }

    public function render()
    {
        return view('filament-filter-presets::components.manage-filters-modal');
    }
}
