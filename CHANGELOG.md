# Changelog

All notable changes to `filament-filter-presets` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.1] - 2024-03-26

### Changed
- Updated release process and documentation

## [1.0.0] - 2024-03-26

### Added
- 🎉 Initial release of Filament Filter Presets
- 🔖 Save filter combinations with custom names and descriptions
- 🔄 Load previously saved filter presets
- ⭐ Set default filters that auto-apply when loading pages
- 👁️ Rich preview showing filter details before applying
- 🏗️ Modular `HasFilterPresets` trait for easy integration
- 🎨 Configurable labels and behavior
- 🔐 User-scoped filter presets (each user has their own)
- 📱 Responsive design working on all device sizes
- 🔧 Support for all Filament filter types:
  - SelectFilter (single and multiple)
  - Custom filters with form components
  - TernaryFilter
  - Date range filters
- 🌍 Internationalization support through configuration
- 📋 Management interface for organizing saved filters
- 🚀 One-command installation with `filament-filter-presets:install`
- 📖 Comprehensive documentation with real-world examples
- 🧪 Robust error handling and validation
- ⚡ Performance optimized with proper database indexing

### Features
- **Filter Management**: Create, edit, delete, and organize filter presets
- **Auto-apply Defaults**: Automatically load preferred filters on page load
- **Smart Preview**: See exactly what each filter preset contains
- **Type Safety**: Proper handling of different Filament filter structures
- **Customizable Display**: Override filter names and value formatting
- **Bulk Operations**: Manage multiple filter presets efficiently
- **Search & Sort**: Find and organize presets easily

### Technical Details
- Compatible with Filament ^3.0
- Requires PHP ^8.1
- Laravel ^10.0|^11.0 support
- Uses spatie/laravel-package-tools for robust package structure
- Comprehensive migration with proper indexes
- Configurable through published config file

## [0.1.0] - 2024-01-XX

### Added
- Initial development and testing phase
- Core functionality implementation
- Basic trait and model structure

---

**Legend:**
- 🎉 Major feature
- 🔖 New functionality  
- 🔄 Enhancement
- ⭐ User experience improvement
- 👁️ Interface improvement
- 🏗️ Architecture change
- 🎨 Styling/Design
- 🔐 Security
- 📱 Mobile/Responsive
- 🔧 Technical improvement
- 🌍 Internationalization
- 📋 Management features
- 🚀 Developer experience
- 📖 Documentation
- 🧪 Testing
- ⚡ Performance
- 🐛 Bug fix
- �� Breaking change 
