# 📘 Filament Filter Presets - Usage Examples

This file contains practical examples of how to use the Filament Filter Presets package in real-world scenarios.

## 🏪 E-commerce Product Management

```php
<?php

namespace App\Filament\Resources;

use Guiu\FilamentFilterPresets\Traits\HasFilterPresets;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms;

class ProductResource extends Resource
{
    use HasFilterPresets;

    protected static ?string $model = Product::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\BadgeColumn::make('status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'draft' => 'Draft',
                    ]),
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('price_min')
                            ->numeric()
                            ->label('Min Price'),
                        Forms\Components\TextInput::make('price_max')
                            ->numeric()
                            ->label('Max Price'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['price_min'] ?? null, fn ($query, $price) => 
                                $query->where('price', '>=', $price))
                            ->when($data['price_max'] ?? null, fn ($query, $price) => 
                                $query->where('price', '<=', $price));
                    }),
            ])
            ->headerActions([
                ...static::getFilterPresetActions(),
            ]);
    }

    // Define filter configuration for better UX
    public static function getFilterPresetConfiguration(): array
    {
        return [
            'category_id' => [
                'type' => 'select',
                'label' => 'Categories',
                'options' => \App\Models\Category::pluck('name', 'id')->toArray(),
                'multiple' => true,
            ],
            'status' => [
                'type' => 'select',
                'label' => 'Status',
                'options' => [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'draft' => 'Draft',
                ],
                'multiple' => false,
            ],
            'price_min' => [
                'type' => 'text',
                'label' => 'Minimum Price',
                'numeric' => true,
            ],
            'price_max' => [
                'type' => 'text',
                'label' => 'Maximum Price',
                'numeric' => true,
            ],
        ];
    }

    // Custom formatting for better preview
    protected static function formatFilterValue(string $filterName, $value): ?string
    {
        return match($filterName) {
            'category_id' => \App\Models\Category::whereIn('id', (array) $value)
                ->pluck('name')->implode(', '),
            'status' => ucfirst($value),
            'price_min' => '$' . number_format($value, 2),
            'price_max' => '$' . number_format($value, 2),
            default => parent::formatFilterValue($filterName, $value),
        };
    }
}
```

## 👥 User Management

```php
<?php

namespace App\Filament\Resources;

use Guiu\FilamentFilterPresets\Traits\HasFilterPresets;

class UserResource extends Resource
{
    use HasFilterPresets;

    protected static ?string $model = User::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'manager' => 'Manager',
                        'user' => 'User',
                    ]),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn ($query, $date) => 
                                $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn ($query, $date) => 
                                $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->headerActions([
                ...static::getFilterPresetActions(),
            ]);
    }
}
```

## 📊 Reports and Analytics

```php
<?php

namespace App\Filament\Resources;

use Guiu\FilamentFilterPresets\Traits\HasFilterPresets;

class OrderResource extends Resource
{
    use HasFilterPresets;

    protected static ?string $model = Order::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('customer.name'),
                Tables\Columns\TextColumn::make('total'),
                Tables\Columns\BadgeColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('total_range')
                    ->form([
                        Forms\Components\TextInput::make('total_min')
                            ->numeric()
                            ->label('Min Total'),
                        Forms\Components\TextInput::make('total_max')
                            ->numeric()
                            ->label('Max Total'),
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'credit_card' => 'Credit Card',
                        'paypal' => 'PayPal',
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                    ]),
            ])
            ->headerActions([
                ...static::getFilterPresetActions(),
            ]);
    }

    // Example of custom filter display names
    protected static function getFilterDisplayName(string $filterName): string
    {
        return match($filterName) {
            'total_min' => 'Minimum Order Value',
            'total_max' => 'Maximum Order Value',
            'payment_method' => 'Payment Method',
            default => parent::getFilterDisplayName($filterName),
        };
    }
}
```

## 🎯 Common Use Cases

### 1. Sales Dashboard Filters
Save common filter combinations like:
- "High Value Orders" (status: completed, total > $1000)
- "Recent Orders" (created in last 30 days)
- "Problem Orders" (status: cancelled or refunded)

### 2. Content Management
- "Published Articles" (status: published, category: blog)
- "Draft Content" (status: draft, author: current user)
- "Recent Updates" (updated in last week)

### 3. Customer Support
- "Urgent Tickets" (priority: high, status: open)
- "My Assigned" (assignee: current user, status: in progress)
- "Overdue Items" (due date < today, status: open)

## 🔧 Advanced Customization

### Auto-applying Filters Based on User Role

```php
public function mount(): void
{
    parent::mount();
    
    // Apply different default filters based on user role
    if (auth()->user()->hasRole('manager')) {
        // Managers see only their team's data by default
        $this->tableFilters = [
            'team_id' => ['value' => auth()->user()->team_id],
        ];
    } else {
        // Regular users get normal default filters
        static::applyDefaultFilterPreset($this);
    }
}
```

### Custom Filter Validation

```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // Validate filter combinations before saving
    if (isset($data['filters']['price_min']) && isset($data['filters']['price_max'])) {
        if ($data['filters']['price_min'] > $data['filters']['price_max']) {
            throw new \Exception('Minimum price cannot be greater than maximum price.');
        }
    }
    
    return parent::mutateFormDataBeforeCreate($data);
}
```

## 💡 Tips and Best Practices

1. **Name your presets descriptively**: Use names like "High Priority Open Tickets" instead of "Filter 1"

2. **Use descriptions**: Add context about when to use each filter preset

3. **Set sensible defaults**: Choose the most commonly used filter combination as default

4. **Group related filters**: Create presets for different user roles or departments

5. **Regular cleanup**: Periodically review and remove unused filter presets

6. **Test with real data**: Ensure your filter combinations return expected results

---

*These examples show the flexibility and power of the Filament Filter Presets package. Adapt them to your specific use cases!* 
