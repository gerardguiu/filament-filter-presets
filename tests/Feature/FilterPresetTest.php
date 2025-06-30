<?php

use Guiu\FilamentFilterPresets\Models\FilterPreset;
use Guiu\FilamentFilterPresets\Tests\TestCase;

uses(TestCase::class);

it('can create a filter preset', function () {
    $user = createTestUser();
    
    $preset = FilterPreset::create([
        'user_id' => $user->id,
        'name' => 'Test Filter',
        'resource_class' => 'App\\Filament\\Resources\\TestResource',
        'filters' => ['status' => 'active'],
        'description' => 'Test description',
        'is_default' => false,
    ]);

    expect($preset->name)->toBe('Test Filter');
    expect($preset->filters)->toBe(['status' => 'active']);
});

it('can set a filter preset as default', function () {
    $user = createTestUser();
    
    $preset = FilterPreset::create([
        'user_id' => $user->id,
        'name' => 'Default Filter',
        'resource_class' => 'App\\Filament\\Resources\\TestResource',
        'filters' => ['status' => 'active'],
        'is_default' => true,
    ]);

    $preset->setAsDefault();

    expect($preset->is_default)->toBeTrue();
});

function createTestUser()
{
    return new class {
        public $id = 1;
    };
} 