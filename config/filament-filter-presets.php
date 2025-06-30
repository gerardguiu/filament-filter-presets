<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filter Presets Table Name
    |--------------------------------------------------------------------------
    |
    | This is the name of the table that will be created to store filter presets.
    | You can change this if you need to avoid conflicts with existing tables.
    |
    */
    'table_name' => 'filter_presets',

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the User model that will be used for relationships.
    | It should implement the Authenticatable contract.
    |
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Default Actions
    |--------------------------------------------------------------------------
    |
    | Configure which actions should be available by default.
    | You can disable actions globally here.
    |
    */
    'actions' => [
        'save_filters' => true,
        'load_filters' => true,
        'manage_filters' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Labels
    |--------------------------------------------------------------------------
    |
    | Customize the labels for the filter preset actions.
    | Useful for internationalization.
    |
    */
    'labels' => [
        'save_filters' => 'Save Filters',
        'load_filters' => 'Load Filters',
        'manage_filters' => 'Manage Filters',
        'filter_name' => 'Filter Name',
        'description' => 'Description',
        'set_as_default' => 'Set as default filter',
        'select_saved_filter' => 'Select a saved filter',
        'preview_placeholder' => 'Select a filter to see its description',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Management Resource
    |--------------------------------------------------------------------------
    |
    | Enable or disable the built-in filter management resource.
    | Set to false if you want to create your own management interface.
    |
    */
    'enable_resource' => true,

    /*
    |--------------------------------------------------------------------------
    | Resource Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the filter presets resource if enabled.
    |
    */
    'resource' => [
        'navigation_group' => null,
        'navigation_sort' => null,
        'navigation_icon' => 'heroicon-o-funnel',
        'slug' => 'filter-presets',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-apply Default Filters
    |--------------------------------------------------------------------------
    |
    | Whether to automatically apply default filters when loading list pages.
    | This can be disabled globally and controlled per resource.
    |
    */
    'auto_apply_defaults' => true,

    /*
    |--------------------------------------------------------------------------
    | Custom Filter Types
    |--------------------------------------------------------------------------
    |
    | Define custom filter patterns that should be handled specially.
    | These filters will use the custom filter structure instead of
    | the standard SelectFilter structure.
    |
    */
    'custom_filter_patterns' => [
        'date_range',
        'custom',
        '_range',
        '_custom',
    ],

    /*
    |--------------------------------------------------------------------------
    | Preview Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the filter preview functionality.
    |
    */
    'preview' => [
        'show_description' => true,
        'show_filters' => true,
        'max_filters_shown' => 10,
    ],
];
