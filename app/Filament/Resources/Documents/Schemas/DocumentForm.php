<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Models\Tax;
use App\Models\Document;
use Filament\Schemas\Schema;
use App\Models\DocumentStatus;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Forms\Components\InfoField;

class DocumentForm
{
    public static $hasExtraFields = true;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['lg' => 3])
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
                            
                        ]),


                Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Section::make('Items')
                            ->description('The items selected for purchase')
                            ->icon('heroicon-m-shopping-cart')
                            ->schema([
                                
                                Repeater::make('items')
                                    ->label('')
                                    ->relationship()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, Get $get) {
                                        static::recomputeTotals($set, $get);
                                    })
                                    ->schema([

                                        Grid::make(3)->schema([

                                            // Option 4
                                            Select::make('item_id')
                                                ->label('Item')
                                                ->required()
                                                ->relationship('item', 'name')   // simplest & fastest since you only need name
                                                ->searchable()
                                                ->live()                         // make sure state updates immediately
                                                ->afterStateUpdated(function ($state, callable $set, $get) {
                                                    if (! $state) {
                                                        return;
                                                    }

                                                    $item = \App\Models\Item::query()
                                                        ->select('id', 'price')
                                                        ->find($state);

                                                    if (! $item) {
                                                        return;
                                                    }

                                                    // These are relative to the current repeater item
                                                    $set('price', (float) $item->price);
                                                    $set('quantity', 1);
                                                    $set('subtotal', (float) $item->price);
                                                })
                                                ->disabled(fn () =>
                                                    ! auth()->user()->hasAnyRole([
                                                        'Superadmin', 'Admin', 'Accounting', 'Encoder',
                                                        'Order Control Specialist', 'Customer',
                                                    ])
                                                )
                                                ->columnSpan(['md' => 2]),

                                            TextInput::make('description')
                                                ->label('Description')
                                                ->columnSpan(['md' => 1]),

                                            TextInput::make('quantity')
                                                ->label('Quantity')
                                                ->numeric()
                                                // ->minValue(0)
                                                ->default(1)
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(
                                                    function ($state, callable $set, Get $get) {
                                                        $sub = $get('quantity') * $get('price');
                                                        $set('subtotal', $sub);
                                                    }
                                                )
                                                ->disabled(
                                                    fn (bool $context, $state)
                                                    =>
                                                    auth()->user()->hasRole(['Superadmin', 'Admin', 'Accounting', 'Encoder', 'Order Control Specialist', 'Customer']) ? false : true
                                                    )
                                                ->columnSpan(['md' => 1]),

                                            TextInput::make('price')
                                                ->label('Price')
                                                ->numeric()
                                                ->required()
                                                ->prefix('₱')
                                                ->reactive()
                                                ->afterStateUpdated(
                                                    // working
                                                    //fn ($state, callable $set) => $set('subtotal', rand(1000,10000))

                                                    function ($state, callable $set, Get $get) {
                                                        $sub = $get('quantity') * $get('price');
                                                        $set('subtotal', $sub);
                                                    }
                                                )
                                                ->disabled(
                                                    fn (bool $context, $state)
                                                    =>
                                                    auth()->user()->hasRole(['Superadmin', 'Admin', 'Accounting', 'Encoder', 'Order Control Specialist', 'Customer']) ? false : true
                                                    )
                                                ->columnSpan(['md' => 1]),

                                            TextInput::make('subtotal')
                                                ->label('Amount')
                                                ->numeric()
                                                ->required()
                                                ->prefix('₱')
                                                ->disabled()
                                                ->dehydrated()
                                                ->columnSpan(['md' => 1]),


                                            Group::make()
                                            ->schema([
                                                Grid::make(4)
                                                ->schema([
                                                    Toggle::make('is_partial_delivery') // no stock
                                                        ->label('Out of stock')
                                                        //->helperText('Toggle on if this item has partial delivery.')
                                                        ->default(false)
                                                        // ->required()
                                                        ->inline(false)
                                                        ->hidden(function() {
                                                            if (static::$hasExtraFields) {
                                                                return false;
                                                            }
                                                            return true;
                                                        })
                                                        ->disabled(
                                                            // fn (?Document $record) => $record === null
                                                            function($context, $state, Get $get, callable $set) {
                                                                // if create then disabled
                                                                if ($context === 'view') {
                                                                    return true;
                                                                }
                                                            }
                                                        )
                                                        ->columnSpan(['md' => 1]),

                                                    Toggle::make('is_freebie')
                                                        ->label('Freebie')
                                                        ->default(false)
                                                        ->inline(false)
                                                        ->hidden(function() {
                                                            if (static::$hasExtraFields) {
                                                                return false;
                                                            }
                                                            return true;
                                                        })
                                                        ->disabled(
                                                            function($context, $state, Get $get, callable $set) {
                                                                if ($context === 'view') {
                                                                    return true;
                                                                }
                                                            }
                                                        )
                                                        ->columnSpan(['md' => 1]),

                                                    Toggle::make('is_preorder')
                                                        ->label('Pre-order')
                                                        ->default(false)
                                                        ->inline(false)
                                                        ->hidden(function() {
                                                            if (static::$hasExtraFields) {
                                                                return false;
                                                            }
                                                            return true;
                                                        })
                                                        ->disabled(
                                                            function($context, $state, Get $get, callable $set) {
                                                                if ($context === 'view') {
                                                                    return true;
                                                                }
                                                            }
                                                        )
                                                        ->columnSpan(['md' => 1]),

                                                    Toggle::make('is_returns')
                                                        ->label('Returns')
                                                        ->default(false)
                                                        ->inline(false)
                                                        ->hidden(function() {
                                                            if (static::$hasExtraFields) {
                                                                return false;
                                                            }
                                                            return true;
                                                        })
                                                        ->disabled(
                                                            function($context, $state, Get $get, callable $set) {
                                                                if ($context === 'view') {
                                                                    return true;
                                                                }
                                                            }
                                                        )
                                                        ->columnSpan(['md' => 1]),

                                                    Toggle::make('is_refunds')
                                                        ->label('Refunds')
                                                        ->default(false)
                                                        ->inline(false)
                                                        ->hidden(function() {
                                                            if (static::$hasExtraFields) {
                                                                return false;
                                                            }
                                                            return true;
                                                        })
                                                        ->disabled(
                                                            function($context, $state, Get $get, callable $set) {
                                                                if ($context === 'view') {
                                                                    return true;
                                                                }
                                                            }
                                                        )
                                                        ->columnSpan(['md' => 1]),

                                                    Toggle::make('is_offset')
                                                        ->label('Offset')
                                                        ->default(false)
                                                        // ->required()
                                                        ->inline(false)
                                                        ->hidden(function() {
                                                            if (static::$hasExtraFields) {
                                                                return false;
                                                            }
                                                            return true;
                                                        })
                                                        ->disabled(
                                                            function($context, $state, Get $get, callable $set) {
                                                                if ($context === 'view') {
                                                                    return true;
                                                                }
                                                            }
                                                        )
                                                        ->columnSpan(['md' => 1]),

                                                    Toggle::make('is_cancelled')
                                                        ->label('Cancelled')
                                                        ->default(false)
                                                        // ->required()
                                                        ->inline(false)
                                                        ->hidden(function() {
                                                            if (static::$hasExtraFields) {
                                                                return false;
                                                            }
                                                            return true;
                                                        })
                                                        ->disabled(
                                                            function($context, $state, Get $get, callable $set) {
                                                                if ($context === 'view') {
                                                                    return true;
                                                                }
                                                            }
                                                        )
                                                        ->columnSpan(['md' => 1]),


                                                ]),
                                            ])
                                            ->columnSpanFull(),

                                        ]), // end Grid::make(3)

                                    ])
                                    ->defaultItems(1)
                                    ->addActionLabel('Add item')
                                    ->reorderable(true)
                                    ->reorderableWithButtons()
                                    ->orderColumn('sort')
                                    ->addable(
                                        fn (bool $context, $state)
                                        =>
                                        auth()->user()->hasRole(['Superadmin', 'Admin', 'Accounting', 'Encoder', 'Order Control Specialist', 'Customer']) ? true : false
                                        )
                                    ->required(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        
                        Section::make()
                            ->schema([

                            

                            Select::make('payment_sort_id')
                                ->relationship('paymentSort', 'name')
                                ->hidden(function() {
                                    if (auth()->user()->hasRole(['Customer Service'])) {
                                        return true;
                                    }
                                    return false;
                                })
                                ->label('COD ODR OK REMIT'),

                            Select::make('bankStatementBanks')
                                ->label('Bank')
                                ->multiple()
                                ->relationship('bankStatementBanks', 'name')
                                ->preload()
                                ->searchable(),

                            TextInput::make('tracking_number')
                                ->label('Tracking Number')
                                ->disabled()
                                ->dehydrated(false),

                            Hidden::make('grand_subtotal')->dehydrated(true),
                            TextInput::make('subtotal_display')
                                ->label('Subtotal')
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($state, Get $get) => number_format((float) ($get('grand_subtotal') ?? 0), 2)),


                            Select::make('discount_type')
                                ->label('Discount Type')
                                ->reactive()
                                ->disabled(
                                    function(?Document $record) {
                                        if ($record) {
                                            return Document::setIfDisabled($record->id);
                                        }
                                        return false;
                                    }
                                )
                                ->options([
                                    1 => 'Percentage (%)',
                                    2 => 'Fix Amount (₱)',
                                ])
                                ->afterStateUpdated(fn (callable $set, Get $get) => static::recomputeTotals($set, $get)),

                            TextInput::make('discount_value')
                                ->label('Discount Value')
                                ->reactive()
                                ->disabled(
                                    function(?Document $record) {
                                        if ($record) {
                                            if ($record->document_status_id >= 5) {
                                                return true;
                                            }
                                        }
                                        return false;
                                    }
                                )
                                ->numeric()
                                ->afterStateUpdated(fn (callable $set, Get $get) => static::recomputeTotals($set, $get)),

                            Hidden::make('tax_value'),

                            Select::make('tax_id')
                                ->label('Tax')
                                ->reactive()
                                ->disabled(
                                    function(?Document $record) {
                                        if ($record) {
                                            return Document::setIfDisabled($record->id);
                                        }
                                        return false;
                                    }
                                )
                                ->options(Tax::where('enabled', 1)->pluck('name', 'id'))
                                ->searchable()
                                ->afterStateUpdated(fn (callable $set, Get $get) => static::recomputeTotals($set, $get)),

                         
                            Hidden::make('grand_amount')->dehydrated(true),
                            TextInput::make('grand_amount_display')
                                ->label('Grand Total')
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($state, Get $get) => number_format((float) ($get('grand_amount') ?? 0), 2)),

                             

                            RichEditor::make('notes')
                                ->label('Notes (optional)')
                                ->toolbarButtons([
                                    // 'attachFiles', // ERROR IN EXCEL

                                    // 'blockquote',
                                    // 'codeBlock',
                                    // 'link',
                                    'orderedList',
                                    'bulletList',

                                    'bold',
                                    // 'h2',
                                    // 'h3',
                                    'italic',
                                    'redo',
                                    'strike',
                                    'underline',
                                    'undo',
                                ])
                                ->columnSpan('full'),

                            DatePicker::make('return_at')
                                ->label('Date Return')
                                ->hidden(function() {
                                    if (auth()->user()->hasRole(['Customer Service'])) {
                                        return true;
                                    }
                                    return false;
                                })
                                ->default(null),

                            DatePicker::make('refunded_at')
                                ->label('Date Refunded')
                                ->hidden(function() {
                                    if (auth()->user()->hasRole(['Customer Service'])) {
                                        return true;
                                    }
                                    return false;
                                })
                                ->default(null),
                        ])
                        ->columnSpanFull(),
                    ]),
    


                Group::make()
                    ->columnSpan(['lg' => 3])
                    ->schema([
                        Section::make()
                            ->schema([
                            
                                FileUpload::make('attachments')
                                    ->label('Photos/Videos (New)')
                                    ->directory('order-attachments') // Optional subdirectory
                                    ->disk('spaces') // Ensure it's pointing to the right disk
                                    ->previewable()
                                    ->openable()
                                    ->downloadable()
                                    ->panelLayout('grid')
                                    ->multiple()
                                    ->visibility('public') // Ensure visibility is public

                                    ->maxFiles(10)
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                        // add office docs if needed:
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    ])
                                    
                                    // ->preserveFilenames() // Optional to keep original filenames
                                    ->maxSize(env('MAX_FILE_UPLOAD')), // Set max size to 5.2MB. 1024=1MB


                                Section::make()
                                    ->schema([
                                        InfoField::make('ph_attachments_qc')
                                            ->label('Photos/Videos for Quality Control')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) => empty($record?->attachments_qc))
                                            ->text(fn (?Document $record) => static::attachmentsHtml($record?->attachments_qc)),

                                        InfoField::make('ph_attachments_packing')
                                            ->label('Photos/Videos for Packing')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) => empty($record?->attachments_packing))
                                            ->text(fn (?Document $record) => static::attachmentsHtml($record?->attachments_packing)),

                                        InfoField::make('ph_attachments_clerk')
                                            ->label('Photos/Videos for Clerk Checker')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) => empty($record?->attachments_clerk))
                                            ->text(fn (?Document $record) => static::attachmentsHtml($record?->attachments_clerk)),

                                        InfoField::make('ph_attachments_delivery')
                                            ->label('Photos for Delivery Rider')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) => empty($record?->attachments_delivery))
                                            ->text(fn (?Document $record) => static::attachmentsHtml($record?->attachments_delivery)),

                                        InfoField::make('ph_attachments_orderchecker')
                                            ->label('Photos for Order Checker')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) => empty($record?->attachments_orderchecker))
                                            ->text(fn (?Document $record) => static::attachmentsHtml($record?->attachments_orderchecker)),
                                    ]),

                                /*
                                Section::make()
                                    ->schema([
                                        Placeholder::make('ph_attachments_qc')
                                            ->label('Photos/Videos for Quality Control')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) =>
                                                !($record && isset($record->attachments_qc) && count($record->attachments_qc) > 0)
                                            )
                                            ->content(function (?Document $record) {
                                                if ($record && isset($record->attachments_qc)) {
                                                    $counter = 1;
                                                    $attachmentsHtml = '';
                                                    foreach (array_reverse($record->attachments_qc) as $item) {
                                                        $url = Storage::disk('spaces')->url($item);
                                                        $attachmentsHtml .= "
                                                        <p><a href=\"{$url}\" target=\"_blank\">
                                                            View attachment {$counter}
                                                        </a></p>";
                                                        $counter++;
                                                    }
                                                    return new HtmlString($attachmentsHtml);
                                                }
                                                return null;
                                            }),

                                        // Repeat similar changes for other placeholders below
                                        Placeholder::make('ph_attachments_packing')
                                            ->label('Photos/Videos for Packing')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) =>
                                                !($record && isset($record->attachments_packing) && count($record->attachments_packing) > 0)
                                            )
                                            ->content(function (?Document $record) {
                                                if ($record && isset($record->attachments_packing)) {
                                                    $counter = 1;
                                                    $attachmentsHtml = '';
                                                    foreach (array_reverse($record->attachments_packing) as $item) {
                                                        $url = Storage::disk('spaces')->url($item);
                                                        $attachmentsHtml .= "
                                                        <p><a href=\"{$url}\" target=\"_blank\">
                                                            View attachment {$counter}
                                                        </a></p>";
                                                        $counter++;
                                                    }
                                                    return new HtmlString($attachmentsHtml);
                                                }
                                                return null;
                                            }),

                                        Placeholder::make('ph_attachments_clerk')
                                            ->label('Photos/Videos for Clerk Checker')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) =>
                                                !($record && isset($record->attachments_clerk) && count($record->attachments_clerk) > 0)
                                            )
                                            ->content(function (?Document $record) {
                                                if ($record && isset($record->attachments_clerk)) {
                                                    $counter = 1;
                                                    $attachmentsHtml = '';
                                                    foreach (array_reverse($record->attachments_clerk) as $item) {
                                                        $url = Storage::disk('spaces')->url($item);
                                                        $attachmentsHtml .= "
                                                        <p><a href=\"{$url}\" target=\"_blank\">
                                                            View attachment {$counter}
                                                        </a></p>";
                                                        $counter++;
                                                    }
                                                    return new HtmlString($attachmentsHtml);
                                                }
                                                return null;
                                            }),

                                        Placeholder::make('ph_attachments_delivery')
                                            ->label('Photos for Delivery Rider')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) =>
                                                !($record && isset($record->attachments_delivery) && count($record->attachments_delivery) > 0)
                                            )
                                            ->content(function (?Document $record) {
                                                if ($record && isset($record->attachments_delivery)) {
                                                    $counter = 1;
                                                    $attachmentsHtml = '';
                                                    foreach (array_reverse($record->attachments_delivery) as $item) {
                                                        $url = Storage::disk('spaces')->url($item);
                                                        $attachmentsHtml .= "
                                                        <p><a href=\"{$url}\" target=\"_blank\">
                                                            View attachment {$counter}
                                                        </a></p>";
                                                        $counter++;
                                                    }
                                                    return new HtmlString($attachmentsHtml);
                                                }
                                                return null;
                                            }),

                                        Placeholder::make('ph_attachments_orderchecker')
                                            ->label('Photos for Order Checker')
                                            ->extraAttributes(['class' => 'z-10'])
                                            ->hidden(fn (?Document $record) =>
                                                !($record && isset($record->attachments_orderchecker) && count($record->attachments_orderchecker) > 0)
                                            )
                                            ->content(function (?Document $record) {
                                                if ($record && isset($record->attachments_orderchecker)) {
                                                    $counter = 1;
                                                    $attachmentsHtml = '';
                                                    foreach (array_reverse($record->attachments_orderchecker) as $item) {
                                                        $url = Storage::disk('spaces')->url($item);
                                                        $attachmentsHtml .= "
                                                        <p><a href=\"{$url}\" target=\"_blank\">
                                                            View attachment {$counter}
                                                        </a></p>";
                                                        $counter++;
                                                    }
                                                    return new HtmlString($attachmentsHtml);
                                                }
                                                return null;
                                            }),
                                    ]),
                                */

                                Section::make()
                                    ->schema([
                                        InfoField::make('ph_notes_clerk')
                                            ->label('Notes for Clerk Checker')
                                            ->hidden(fn (?Document $record) => blank($record?->notes_clerk))
                                            ->text(fn (?Document $record) => new HtmlString('<b>' . e($record?->notes_clerk ?? '') . '</b>')),

                                        InfoField::make('ph_notes_delivery')
                                            ->label('Notes for Delivery Rider')
                                            ->hidden(fn (?Document $record) => blank($record?->notes_delivery))
                                            ->text(fn (?Document $record) => new HtmlString('<b>' . e($record?->notes_delivery ?? '') . '</b>')),

                                        InfoField::make('ph_orderchecker')
                                            ->label('Notes for Order Checker')
                                            ->hidden(fn (?Document $record) => blank($record?->notes_orderchecker))
                                            ->text(fn (?Document $record) => new HtmlString('<b>' . e($record?->notes_orderchecker ?? '') . '</b>')),
                                    ]),

                                /*
                                Section::make()
                                    ->schema([
                                        Placeholder::make('ph_notes_clerk')
                                            ->label('Notes for Clerk Checker')
                                            ->hidden(function (?Document $record) {
                                                if ($record) {
                                                    if ($record->notes_clerk != ''):
                                                        return false;
                                                    endif;
                                                }
                                                return true;
                                            })
                                            ->content(function (?Document $record) {
                                                return new HtmlString(
                                                    '<b style="">' . $record->notes_clerk . '</b>' .
                                                    '');
                                            }),

                                        Placeholder::make('ph_notes_delivery')
                                            ->label('Notes for Delivery Rider')
                                            ->hidden(function (?Document $record) {
                                                if ($record) {
                                                    if ($record->notes_delivery != ''):
                                                        return false;
                                                    endif;
                                                }
                                                return true;
                                            })
                                            ->content(function (?Document $record) {
                                                return new HtmlString(
                                                    '<b style="">' . $record->notes_delivery . '</b>' .
                                                    '');
                                            }),

                                        Placeholder::make('ph_orderchecker')
                                            ->label('Notes for Order Checker')
                                            ->hidden(function (?Document $record) {
                                                if ($record) {
                                                    if ($record->notes_orderchecker != ''):
                                                        return false;
                                                    endif;
                                                }
                                                return true;
                                            })
                                            ->content(function (?Document $record) {
                                                return new HtmlString(
                                                    '<b style="">' . $record->notes_orderchecker . '</b>' .
                                                    '');
                                            }),
                                    ]),
                                */

                                Section::make('History')
                                    ->schema([

                                        Grid::make(4)->schema([

                                            
                                            InfoField::make('ph_customer_name')
                                                ->text(fn (Document $record) => 'Created by<br /> <b>' . e($record->created_name) . '</b>'),
                                            
                                            InfoField::make('ph_created_at')
                                                ->text(fn (Document $record) => 'Created at<br /> <b>' . $record->created_at?->diffForHumans() . '</b>'),

                                            InfoField::make('ph_created_name')
                                                ->text(fn (Document $record) => 'Last modified by<br /> <b>' . $record->created_name . '</b>'),

                                            InfoField::make('ph_cupdated_at')
                                                ->text(fn (Document $record) => 'Last modified at<br /> <b>' . $record->updated_at?->diffForHumans() . '</b>'),

                                            

                                            // Placeholder::make('created_at')
                                            //     ->label('Created at')
                                            //     ->content(fn (Document $record): ?string => $record->created_at?->diffForHumans()),

                                            // Placeholder::make('updated_name')
                                            //     ->label('Last updated by')
                                            //     ->content(fn (Document $record): ?string => $record->created_name),

                                            // Placeholder::make('updated_at')
                                            //     ->label('Last modified at')
                                            //     ->content(fn (Document $record): ?string => $record->updated_at?->diffForHumans()),

                                        ]),

                                    ])
                                    ->hidden(fn (?Document $record) => $record === null)
                                    ->columnSpanFull(),

                                    ]),

                                ]),
            
          
                
            ]);
    }


    public static function getSubtotal(Get $get): float
    {
        $total = 0.0;

        $items = $get('items') ?? [];
        if (! is_array($items)) {
            return 0.0;
        }

        foreach ($items as $key => $item) {
            $total += (float) ($get("items.{$key}.subtotal") ?? 0);
        }

        return $total;
    }


    protected static function recomputeGrandTotal(callable $set, Get $get): void
    {
        $subtotal = static::getSubtotal($get);

        $discountType  = $get('discount_type');
        $discountValue = (float) ($get('discount_value') ?? 0);
        $taxId         = $get('tax_id');

        $discountTotal = 0.0;
        if ($discountType) {
            $discountTotal = $discountType == 1
                ? $subtotal * ($discountValue / 100)
                : $discountValue;
        }

        $taxAmount = 0.0;
        if ($taxId) {
            $tax = Tax::find($taxId);
            $taxAmount = $tax ? $subtotal * ((float) $tax->rate / 100) : 0.0;
        }

        $total = $subtotal - $discountTotal + $taxAmount;

        // ✅ store raw numeric value
        $set('grand_amount', round($total, 2));
    }

    protected static function recomputeTotals(callable $set, Get $get): void
    {
        $subtotal = (float) static::getSubtotal($get);
        $set('grand_subtotal', round($subtotal, 2));

        $discountType  = $get('discount_type');
        $discountValue = (float) ($get('discount_value') ?? 0);
        $taxId         = $get('tax_id');

        $discountTotal = 0.0;
        if ($discountType) {
            $discountTotal = $discountType == 1
                ? $subtotal * ($discountValue / 100)
                : $discountValue;
        }

        $taxAmount = 0.0;
        if ($taxId) {
            $tax = Tax::find($taxId);
            $taxAmount = $tax ? $subtotal * ((float) $tax->rate / 100) : 0.0;
        }

        $grand = $subtotal - $discountTotal + $taxAmount;

        $set('grand_amount', round($grand, 2));
    }

    protected static function attachmentsHtml(?array $attachments): ?HtmlString
    {
        if (empty($attachments) || ! is_array($attachments)) {
            return null;
        }

        $counter = 1;
        $html = '';

        foreach (array_reverse($attachments) as $item) {
            if (! $item) {
                continue;
            }

            $url = Storage::disk('spaces')->url($item);

            $html .= '<p><a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">'
                . 'View attachment ' . $counter
                . '</a></p>';

            $counter++;
        }

        return $html !== '' ? new HtmlString($html) : null;
    }





}
