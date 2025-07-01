<div
    x-data
    x-on:refresh-modal.window="$wire.$refresh()"
>
    @php
        $presets = \Guiu\FilamentFilterPresets\Models\FilterPreset::where('user_id', auth()->id())
            ->where('resource_class', $resourceClass)
            ->get();
    @endphp

    @if($presets->isEmpty())
        <p class="text-sm text-gray-500">{{ __('filament-filter-presets::labels.no_presets') }}</p>
    @else
        <div class="space-y-4">
            @foreach($presets as $preset)
                <div class="flex items-center justify-between p-4 bg-white rounded-lg shadow">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">
                            {{ $preset->name }}
                            @if($preset->is_default)
                                <span class="ml-2 text-xs text-gray-500">({{ __('filament-filter-presets::labels.default') }})</span>
                            @endif
                        </h3>
                        @if($preset->description)
                            <p class="mt-1 text-sm text-gray-500">{{ $preset->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            wire:click="toggleDefaultFilter({{ $preset->id }})"
                            class="flex items-center gap-1 px-2 py-1 text-sm text-gray-500 hover:text-gray-700 rounded-md hover:bg-gray-100"
                        >
                            @if($preset->is_default)
                                <x-filament::icon
                                    icon="heroicon-m-star"
                                    class="w-4 h-4 text-amber-500"
                                />
                                {{ __('filament-filter-presets::labels.unset_default') }}
                            @else
                                <x-filament::icon
                                    icon="heroicon-m-star"
                                    class="w-4 h-4"
                                />
                                {{ __('filament-filter-presets::labels.set_default') }}
                            @endif
                        </button>
                        <button
                            wire:click="deletePreset({{ $preset->id }})"
                            class="flex items-center gap-1 px-2 py-1 text-sm text-red-500 hover:text-red-700 rounded-md hover:bg-red-50"
                        >
                            <x-filament::icon
                                icon="heroicon-m-trash"
                                class="w-4 h-4"
                            />
                            {{ __('filament-filter-presets::labels.delete') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @script
    <script>
        $wire.on('refresh-modal', () => {
            setTimeout(() => {
                window.location.reload();
            }, 300);
        });
    </script>
    @endscript
</div>
