<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Models\Item;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Hidden::make('created_by')
                            ->default(auth()->id()),

                        Section::make()
                            ->columns(2)
                            ->schema([
                                Select::make('type')
                                    ->options([
                                        'product' => 'Product',
                                        'service' => 'Service',
                                        'downloadable' => 'Downloadable',
                                    ])
                                    ->default('product')
                                    ->disabled()
                                    ->required()
                                    ->columnSpanFull(),

                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null)
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->unique(Item::class, 'slug', ignoreRecord: true)
                                    ->columnSpanFull(),

                                MarkdownEditor::make('description')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Pricing')
                            ->columns(3)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('purchase_price')
                                    ->prefix('$')
                                    ->label('Old Cost Price')
                                    ->numeric()
                                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin'])),

                                TextInput::make('purchase_price_new')
                                    ->prefix('$')
                                    ->label('New Cost Price')
                                    ->numeric()
                                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin'])),
                            ]),

                        Section::make('Inventory')
                            ->columns(2)
                            ->schema([
                                TextInput::make('sku')
                                    ->label('SKU (Stock Keeping Unit)')
                                    ->unique(Item::class, 'sku', ignoreRecord: true)
                                    ->required(),

                                TextInput::make('barcode')
                                    ->label('ISBN, UPC, GTIN, etc.')
                                    ->unique(Item::class, 'barcode', ignoreRecord: true),

                                TextInput::make('quantity')
                                    ->label(fn (?Item $record) => $record === null ? 'Opening stock' : 'Stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->disabledOn('edit')
                                    ->required(),

                                TextInput::make('security_stock')
                                    ->label('Reorder stock')
                                    ->helperText('The reorder stock is the limit stock for your products which alerts you if the product stock will soon be out of stock.')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),

                                Checkbox::make('outofstock_sell_on')
                                    ->default(1)
                                    ->inline(false)
                                    ->label('Continue selling when out of stock'),

                                TextInput::make('supplier_name')
                                    ->label('Supplier name')
                                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin'])),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('enabled')
                                    ->label('Visible')
                                    ->helperText('This product will be hidden from all sales channels.')
                                    ->default(true),

                                DatePicker::make('published_at')
                                    ->label('Availability')
                                    ->default(now())
                                    ->required(),

                                Toggle::make('is_showroom')
                                    ->label('Showroom')
                                    ->default(true),
                            ]),

                        Section::make('Associations')
                            ->schema([
                                Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable(),

                                Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->required()
                                    ->preload(),
                            ]),
                    ]),
            ]);
    }
}
