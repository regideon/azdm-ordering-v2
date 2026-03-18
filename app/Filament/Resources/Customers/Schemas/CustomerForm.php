<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Placeholder::make('general')
                            ->label('')
                            ->content(new HtmlString('Your client\'s contact information will appear in invoices and their profiles.'))
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->maxLength(50)
                            ->required(),

                        TextInput::make('nick')
                            ->label('FB Name')
                            ->required()
                            ->maxLength(190),

                        Select::make('customer_type_id')
                            ->label('VIP')
                            ->relationship('customerType', 'title')
                            ->required(),

                        TextInput::make('email')
                            ->label('Email address')
                            ->default('maddox_customer_' . uniqid() . '@domain.com')
                            ->required()
                            ->email()
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->maxLength(50),

                        TextInput::make('website')
                            ->maxLength(190),

                        TextInput::make('address')
                            ->columnSpanFull(),

                        Hidden::make('currency_code')
                            ->default('PHP'),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn (?Customer $record) => $record === null ? 3 : 2]),

                Section::make()
                    ->schema([
                        Textarea::make('information')
                            ->label('Type of Customer / Additional Information')
                            ->columnSpanFull(),

                        Select::make('customer_classification_id')
                            ->relationship(
                                name: 'customerClassification',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('enabled', 1),
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn (?Customer $record) => $record === null ? 3 : 2]),
            ]);
    }
}
