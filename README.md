# 🔧 Filament Filter Presets

[![Latest Version on Packagist](https://img.shields.io/packagist/v/guiu/filament-filter-presets.svg?style=flat-square)](https://packagist.org/packages/guiu/filament-filter-presets)
[![Total Downloads](https://img.shields.io/packagist/dt/guiu/filament-filter-presets.svg?style=flat-square)](https://packagist.org/packages/guiu/filament-filter-presets)

A comprehensive filter preset system for Filament that allows users to save, load, and manage table filters across any resource. Perfect for applications where users frequently need to apply the same set of filters.

## ✨ Features

- 🔖 **Save Filters**: Save any combination of applied filters with a name and description
- 🔄 **Load Filters**: Quickly apply previously saved filter combinations
- ⭐ **Default Filters**: Set filters to be applied automatically when loading pages
- 👁️ **Rich Preview**: See exactly what filters a preset contains before applying
- 🏗️ **Modular Design**: Easy to add to any existing Filament resource
- 🎨 **Customizable**: Configure labels, behavior, and appearance
- 🔐 **User-Scoped**: Each user has their own filter presets
- 📱 **Responsive**: Works seamlessly across all device sizes

## 📸 Screenshots

*Coming soon - showing the save/load filter modals and preview functionality*

## 🚀 Installation

You can install the package via composer:

```bash
composer require guiu/filament-filter-presets
```

Run the installation command:

```bash
php artisan filament-filter-presets:install
```

This will:
- Publish the configuration file
- Publish and run the migrations
- Set up the necessary database tables

## 📋 Basic Usage

### 1. Add the Trait to Your Resource

```php
<?php

namespace App\Filament\Resources;

use Guiu\FilamentFilterPresets\Traits\HasFilterPresets;
use Filament\Resources\Resource;

class YourResource extends Resource
{
    use HasFilterPresets;

    // ... your existing resource code
}
```

### 2. Add Filter Actions to Your Table

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ... your columns
        ])
        ->filters([
            // ... your filters
        ])
        ->headerActions([
            // Add this line to enable filter presets
            ...static::getFilterPresetActions(),
        ]);
}
```

### 3. Auto-apply Default Filters (Optional)

In your `ListRecords` page:

```php
<?php

namespace App\Filament\Resources\YourResource\Pages;

use App\Filament\Resources\YourResource;
use Filament\Resources\Pages\ListRecords;

class ListYourResources extends ListRecords
{
    protected static string $resource = YourResource::class;

    public function mount(): void
    {
        parent::mount();
        
        // Auto-apply default filters
        YourResource::applyDefaultFilterPreset($this);
    }
}
```

That's it! You now have filter presets available in your resource.

## 🔧 Advanced Configuration

### Custom Filter Configuration

For better user experience, you can define which filters are available for configuration:

```php
public static function getFilterPresetConfiguration(): array
{
    return [
        'status' => [
            'type' => 'select',
            'label' => 'Status',
            'options' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'pending' => 'Pending',
            ],
            'multiple' => false,
        ],
        'category_id' => [
            'type' => 'select',
            'label' => 'Category',
            'options' => Category::pluck('name', 'id')->toArray(),
            'multiple' => false,
        ],
        'tags' => [
            'type' => 'select',
            'label' => 'Tags',
            'options' => Tag::pluck('name', 'id')->toArray(),
            'multiple' => true,
        ],
        'created_date' => [
            'type' => 'date',
            'label' => 'Created Date',
        ],
        'search_term' => [
            'type' => 'text',
            'label' => 'Search Term',
        ],
    ];
}
```

### Configuration Options

Publish the config file to customize the package:

```bash
php artisan vendor:publish --tag=filament-filter-presets-config
```

Available configuration options:

```php
return [
    // Table name for filter presets
    'table_name' => 'filter_presets',
    
    // User model to use
    'user_model' => \App\Models\User::class,
    
    // Enable/disable specific actions
    'actions' => [
        'save_filters' => true,
        'load_filters' => true,
        'manage_filters' => true,
    ],
    
    // Customize labels for internationalization
    'labels' => [
        'save_filters' => 'Save Filters',
        'load_filters' => 'Load Filters',
        // ... more labels
    ],
    
    // Auto-apply default filters
    'auto_apply_defaults' => true,
    
    // And more...
];
```

## 🎯 How It Works

### Saving Filters
1. Apply any combination of filters to your table
2. Click "Save Filters" in the header actions
3. Enter a name and optional description
4. Optionally set as default filter
5. Save!

### Loading Filters
1. Click "Load Filters" in the header actions
2. Select from your saved filter presets
3. See a preview of what the filter contains
4. Apply the filters instantly

### Managing Filters
- View all your saved filters in a dedicated management page
- Edit filter names and descriptions
- Set/unset default filters
- Delete filters you no longer need

## 🔄 Filter Types Supported

The package automatically handles different types of Filament filters:

- **SelectFilter** (single selection)
- **SelectFilter with multiple()** (multiple selection)
- **Custom Filters** (like date ranges, text inputs)
- **TernaryFilter** (true/false/null)
- **Filter with custom form components**

## 🎨 Customization

### Custom Preview Formatting

Override the preview generation in your resource:

```php
protected static function generateFilterPreview(FilterPreset $preset): string
{
    // Your custom preview logic
    return "Custom preview for: " . $preset->name;
}
```

### Custom Filter Display Names

```php
protected static function getFilterDisplayName(string $filterName): string
{
    return match($filterName) {
        'category_id' => 'Product Category',
        'status' => 'Publication Status',
        default => ucfirst(str_replace('_', ' ', $filterName)),
    };
}
```

### Custom Value Formatting

```php
protected static function formatFilterValue(string $filterName, $value): ?string
{
    return match($filterName) {
        'category_id' => Category::find($value)?->name ?? "ID: $value",
        'status' => ucfirst($value),
        default => parent::formatFilterValue($filterName, $value),
    };
}
```

## 🧪 Testing

```bash
composer test
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒 Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## 🙏 Credits

- [Guiu](https://github.com/your-username)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🆘 Support

If you discover any issues or have questions, please create an issue on GitHub.

## 🌟 Star the Project

If this package helps you, please consider starring the repository. It means a lot! ⭐

---

**Made with ❤️ for the Filament community** 
