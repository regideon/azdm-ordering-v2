<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Models\Document;
use Filament\Schemas\Schema;
use App\Models\DocumentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Create Orders')
                            ->description('Allows staff to quickly create a new customer order by selecting items, entering quantities, applying pricing details, and capturing customer information. This ensures accurate order recording, proper tracking, and smooth processing from confirmation to fulfillment.')						
                            ->icon('heroicon-m-document-text')
                            ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
                            ->schema([

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->searchable()
                                    ->searchDebounce(400)       // fewer queries as you type
                                    ->optionsLimit(25)          // cap results
                                    ->lazy()                    // fetch only when needed
                                    // remove ->preload() and ->live() to avoid extra queries
                                    ->getSearchResultsUsing(function (string $search) {
                                        return \App\Models\Customer::query()
                                            ->select('id', 'nick', 'name', 'phone')
                                            ->whereNotNull('nick')
                                            ->when($search !== '', function ($q) use ($search) {
                                                $q->where(function ($qq) use ($search) {
                                                    $qq->where('nick', 'like', "%{$search}%");
                                                    // ->orWhere('name', 'like', "%{$search}%")
                                                    // ->orWhere('phone', 'like', "%{$search}%");
                                                });
                                            })
                                            // prioritize “starts with” matches
                                            ->orderByRaw("CASE WHEN nick LIKE ? THEN 0 ELSE 1 END", ["{$search}%"])
                                            ->orderBy('nick')
                                            ->limit(25)
                                            ->pluck('nick', 'id')
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        if (! $value) return null;
                                        return \App\Models\Customer::query()
                                            ->whereKey($value)
                                            ->value('nick');
                                    })
                                    ->required()
                                    ->afterStateHydrated(function ($state) {
                                        if (! $state) return;

                                        $customer = \App\Models\Customer::with([
                                            'customerType:id,description,has_discount',
                                        ])->select('id','name','nick','phone','address','customer_type_id')->find($state);

                                        $freebies = \App\Models\FreebieCustomerDocumentItem::query()
                                            ->where('customer_id', $state)
                                            ->with([
                                                'document:id,document_number',
                                                'documentItem:id,name',
                                            ])
                                            ->limit(20) // keep UI snappy
                                            ->get();

                                        if ($freebies->isNotEmpty()) {
                                            $orders = $freebies->map(function ($f) {
                                                $itemName = $f->documentItem?->name ?? '';
                                                $docNo    = $f->document?->document_number ?? '';
                                                return "{$itemName} (<b>{$docNo}</b>)";
                                            })->implode(', ');

                                            \Filament\Notifications\Notification::make()
                                                ->title('Customer Balance Freebies')
                                                ->icon('heroicon-o-gift')
                                                ->body(new \Illuminate\Support\HtmlString(
                                                    'Customer Name: <b>' . e($customer?->name) . '</b><br/>Orders: ' . $orders
                                                ))
                                                ->danger()
                                                ->duration(12000)
                                                ->send();
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (! $state) return;

                                        $customer = \App\Models\Customer::with([
                                            'customerType:id,description,has_discount',
                                        ])->select('id','name','nick','phone','address','customer_type_id')->find($state);

                                        if ($customer?->customerType?->has_discount) {
                                            \Filament\Notifications\Notification::make()
                                                ->title($customer->customerType->description)
                                                ->icon('heroicon-o-currency-dollar')
                                                ->success()
                                                ->duration(8000)
                                                ->send();
                                        }

                                        \Filament\Notifications\Notification::make()
                                            ->title('Customer Information')
                                            ->icon('heroicon-o-user-group')
                                            ->body(new \Illuminate\Support\HtmlString(
                                                'Name: <b>' . e($customer?->name) . '</b><br/>' .
                                                'FB Name: <b>' . e($customer?->nick) . '</b><br/>' .
                                                'Phone: <b>' . e($customer?->phone) . '</b><br/>' .
                                                'Address: <b>' . e($customer?->address) . '</b>'
                                            ))
                                            ->color('primary')
                                            ->duration(20000)
                                            ->send();

                                        if ($customer?->address) {
                                            $set('customer_address', $customer->address);
                                        }
                                    })
                                    ->disabled(function ($context, $state, Get $get) {
                                        $documentStatusId = (int) $get('document_status_id');
                                        return $documentStatusId >= 5;
                                    })
                                    ->columnSpan(['sm' => 1, 'md' => 4, 'xl' => 4]),

                                TextInput::make('customer_address')
                                    ->label('Shipping Address')
                                    ->dehydrated()
                                    ->columnSpan(['sm' => 1, 'md' => 8, 'xl' => 8]),

                                TextInput::make('document_number')
                                    ->label('Invoice Number')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->hidden(fn ($record) => $record === null)
                                    ->columnSpan(['sm' => 1, 'md' => 4, 'xl' => 4]),

                                
                                DatePicker::make('issued_at')
                                    ->label('Invoice Date')
                                    ->default(date("m/d/Y"))
                                    ->required()
                                    ->columnSpan(['sm' => 1, 'md' => 4, 'xl' => 4]),

                                DatePicker::make('due_at')
                                    ->label('Due Date')
                                    ->default(date("m/d/Y"))
                                    ->required()
                                    ->columnSpan(['sm' => 1, 'md' => 4, 'xl' => 4]),

                                Select::make('document_status_id')
                                    ->label('Status')
                                    ->options(function($context, ?Document $record) {
                                        if ($context === 'create') {
                                            // return DocumentStatus::where('name', 'new')->pluck('name', 'id');
                                            return DocumentStatus::whereIn('name', ['processing'])->pluck('name', 'id');
                                        }
                                        $currentStatus = $record->documentStatus->name;

                                        // if (auth()->user()->hasRole(['Superadmin', 'Admin', 'Encoder', 'Accounting', 'Order Control Specialist', 'Customer'])) {
                                        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
                                            return DocumentStatus::where('enabled', true)->pluck('name', 'id');

                                        } else if (auth()->user()->hasRole(['Accounting'])) {
                                            return DocumentStatus::whereIn('name', ['partial payments', 'processing', 'paid', 'released', 'cancelled', 'order received', 'cash on delivery', "$currentStatus"])->pluck('name', 'id');

                                        } else if (auth()->user()->hasRole(['Order Control Specialist'])) {
                                            return DocumentStatus::whereIn('name', ['processing', 'cancelled', 'order received', 'packed and ready to ship', 'qc checked', 'refunds', 'returns', 'released', "$currentStatus"])->pluck('name', 'id');

                                        } else if (auth()->user()->hasRole(['Encoder'])) {
                                            return DocumentStatus::whereIn('name', ['processing', 'cash on delivery', "$currentStatus"])->pluck('name', 'id');

                                        } else {
                                            if ($record) {
                                                $recordStatusName = $record->documentStatus->name;
                                                switch ($recordStatusName) {
                                                    case 'paid':
                                                        return DocumentStatus::whereIn('name', ['paid', 'released'])->pluck('name', 'id');
                                                        break;

                                                    default:
                                                        return DocumentStatus::whereIn('name', [$recordStatusName])->pluck('name', 'id');
                                                        break;
                                                }
                                            }
                                            return DocumentStatus::whereIn('name', ['new', 'processing'])->pluck('name', 'id');
                                        }
                                    })
                                    ->default(4)
                                    ->required()
                                    ->columnSpan(['sm' => 1, 'md' => 4, 'xl' => 4]),

                                TextInput::make('order_number')
                                    ->label('P.O./S.O. Number')
                                    ->columnSpan(['sm' => 1, 'md' => 4, 'xl' => 4]),
                                
                            ]),
                            
                        ])
                    ->columnSpan(['lg' => 3]),

                Group::make()
                    ->schema([
                        Section::make('Items')
                            ->description('The items selected for purchase')
                            ->icon('heroicon-m-shopping-cart')
                            ->schema([
                                // items for supplier
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Right Panel')
                            ->description('Totals and actions')
                            ->icon('heroicon-m-calculator')
                            ->schema([
                                // totals/actions for supplier
                            ]),
                    ]),
    

            
          
                
            ]);
    }
}
