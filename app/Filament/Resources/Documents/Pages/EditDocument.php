<?php

namespace App\Filament\Resources\Documents\Pages;

use Carbon\Carbon;
use App\Models\Tax;
use App\Models\Item;
use App\Models\Credit;
use App\Models\Customer;
use App\Models\Document;
use Filament\Forms\Form;
use App\Models\ItemHistory;
use App\Models\DocumentItem;
use Filament\Actions\Action;
use App\Models\DocumentTotal;
use App\Models\DocumentStatus;
use App\Models\DocumentHistory;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use App\Models\FreebieCustomerDocumentItem;
use App\Filament\Resources\Documents\DocumentResource;



class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Action::make('addCredit')
                ->label('Add Credit')
                ->icon('heroicon-o-currency-dollar')
                ->visible(fn () => auth()->user()->hasRole(['Superadmin', 'Admin', 'Accounting', 'Encoder', 'Customer']))
                ->form(function (Form $form) {
                    /** @var \App\Models\Document $record */
                    $record = $this->getRecord();

                    return $form->schema([
                        Section::make()
                            ->schema([
                                \App\Filament\Forms\Components\InfoField::make('ph_customer_name')
                                    ->text(fn () => 'Add credit to <b>' . e($record->customer_nick) . ' (' . e($record->customer_name) . ')</b>')
                                    ->tone('danger')
                                    ->columnSpanFull(),

                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->required()
                                    ->hint('+ or -')
                                    ->columnSpan(3),

                                TextInput::make('description')
                                    ->label('Notes')
                                    ->required()
                                    ->maxLength(191)
                                    ->columnSpan(9),
                            ])
                            ->columns(12),
                    ]);
                })
                ->action(function (array $data) {
                    /** @var \App\Models\Document $record */
                    $record = $this->getRecord();

                    $credit = new \App\Models\Credit();
                    $credit->customer_id = $record->customer_id;
                    $credit->amount = $data['amount'];
                    $credit->description = $data['description'];
                    $credit->created_by = auth()->id();
                    $credit->created_name = auth()->user()->name;
                    $credit->issued_at = now();
                    $credit->is_direct_create = 1;
                    $credit->save();

                    Notification::make()
                        ->title('Credit added')
                        ->icon('heroicon-o-currency-dollar')
                        ->body(new HtmlString(
                            'Credit added to <b>' . e($record->customer_name) . '</b> — <b>₱' . number_format((float) $data['amount'], 2) . '</b>.'
                        ))
                        ->duration(8000)
                        ->success()
                        ->send();
                })
                ->modalWidth('3xl'),
        ];
    }
    
    /*
    protected function getHeaderActions(): array
    {
        

        $record = $this->getRecord();
        if (auth()->user()->hasRole(['Superadmin', 'Admin', 'Accounting', 'Encoder', 'Customer'])) {
            return [
                //Actions\DeleteAction::make(),

                // Add credit feature
                Action::make('Add Credit')
                    ->label('Add Credit')
                    ->icon('heroicon-o-currency-dollar')
                    ->form([
                        Section::make()
                        ->schema([
                            // Placeholder::make('ph_customer_name')
                            //     ->label('')
                            //     ->content(new HtmlString('Add credit to <b style="color: red;">' . $record->customer_nick . ' ('.$record->customer_name.')</b>'))
                            //     ->columnSpan(12),
                            \App\Filament\Forms\Components\InfoField::make('ph_customer_name')
                                ->text(fn ($record) => 'Add credit to <b>' . e($record->customer_nick) . ' (' . e($record->customer_name) . ')</b>')
                                ->tone('danger')
                                ->columnSpanFull(),

                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->required()
                                ->hint('+ or -')
                                ->columnSpan(3),
                            TextInput::make('description')
                                ->label('Notes')
                                ->required()
                                ->maxLength(191)
                                ->columnSpan(9),
                        ])
                        ->columns(12)
                    ])
                    ->action(function (Document $record, array $data) {
                        // dd($record->toArray());
                        // dd($data);
                        $credit = new Credit();
                        $credit->customer_id = $record->customer_id;
                        $credit->amount = $data['amount'];
                        $credit->description = $data['description'];
                        $credit->created_by = auth()->user()->id;
                        $credit->created_name = auth()->user()->name;
                        $credit->issued_at = now();
                        $credit->is_direct_create = 1;
                        $credit->save();

                        Notification::make()
                            ->title('Credit Added!')
                            ->icon('heroicon-o-currency-dollar')
                            ->body(new HtmlString(
                                'Credit has been added to <b>' . $record->customer_name . '</b>' .
                                ' amounting to <b>₱' . $data['amount'] . '</b>.'
                                ))
                            // ->persistent()
                            ->duration(10000)
                            ->color('primary')
                            ->send();
                    })
                    ->modalWidth('3xl'),


            ];
        }
        return [];

        // return [
        //     ViewAction::make(),
        //     DeleteAction::make(),
        //     ForceDeleteAction::make(),
        //     RestoreAction::make(),
        // ];
    }
    */

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Order saved successfully.');
    }

    protected function beforeSave(): void
    {
        // $record = $this->getRecord(); // original record
        $data = $this->data; // changed record

        // 1. check if document_status_id=6(released) then notes should be required
        $documentStatusReleased = DocumentStatus::where('name', 'released')->firstOrFail();
        if ($data['document_status_id'] == $documentStatusReleased->id) {
            if (empty($data['notes']) || trim($data['notes']) == '') {
                Notification::make()
                    ->title('Error in updating order.')
                    ->body('You set order status to released, you need to enter payment instructions in Notes text box.')
                    ->icon('codicon-warning')
                    ->warning()
                    ->persistent()
                    // ->duration(10000)
                    ->send();
                    $this->halt();
            }
        }
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $id = $record['id'];
        $recordDocumentStatusId = $record['document_status_id'];

        $data = Document::findOrFail($id);

        $items = DocumentItem::where('document_id', $id)->get();

        $stockItemsOutOfStock = [];
        $documentStatusReleased = DocumentStatus::where('name', 'released')->firstOrFail(); // get the id of paid status
        // 0. check if status is released then check the material stock if available, if not, do not proceed IF none of the 4 toggle is on ()
        if ($recordDocumentStatusId == $documentStatusReleased->id) {
            foreach($items as $item) {
                $stockItem = Item::find($item->item_id);
                // check 0 qty and document_items.is_partial_delivery = false
                // if ($stockItem->quantity <= 0) {
                if (
                    $stockItem->quantity <= 0 &&
                    $item->is_partial_delivery == false &&
                    $item->is_preorder == false &&
                    $item->is_returns == false &&
                    $item->is_refunds == false &&
                    $item->is_offset == false
                    ) {
                    $stockItemsOutOfStock[] = $stockItem;
                }
            }

            if ($this->stockItemsOutOfStockDoNotAllow) {
                // check if $item->is_preorder do not check count $stockItemsOutOfStock
                if ($item->is_preorder) {
                } else {
                    if (count($stockItemsOutOfStock) > 0) {
                        // return order to paid
                        $data->document_status_id = 5;
                        $data->save();

                        Notification::make()
                            ->warning()
                            ->title('Out of Stock!')
                            ->body("Some items are out of stock. Please increase your material inventory to proceed on this order.")
                            ->persistent()
                            ->send();
                        $this->halt();
                    }
                }

            }
        }

        // 1. get and set customer info
        $customer = Customer::findOrFail($record['customer_id']);
        $data->customer_name = $customer->name;
        $data->customer_nick = $customer->nick;
        $data->customer_email = $customer->email;
        // $data['customer_tax_number'] = $customer->email;
        $data->customer_phone = $customer->phone;
        // $data->customer_address = $customer->address; // set inside the form
        $data->customer_city = $customer->city;
        $data->customer_zip_code = $customer->zip_code;
        $data->customer_state = $customer->state;
        $data->customer_country = $customer->country;

        // 1.1 change customer address if form.customer_address is not empty
        if ($record['customer_address'] != '') {
            $customerUpdate = Customer::findOrFail($record['customer_id']);
            $customerUpdate->address = $record['customer_address'];
            $customerUpdate->save();
        }

        // 2.1 subtotal and is_partial_delivery
        $grandTotal = 0;
        $subtotal = 0;
        foreach($items as $item) {
            // check if item set as partial delivery
            $itemIsPartialDelivery = $item->is_partial_delivery;

            if ($item->price_dp == 0 || $item->price_dp == '') {
                $itemSum = $item->price * $item->quantity;
            } else {
                $itemSum = $item->price_dp;
            }
            // $itemSum = $item->price * $item->quantity;
            $subtotal += $itemSum;

            // 2.1.2 save subtotal
            $documentItem = DocumentItem::findOrFail($item->id);
            //dump($documentItem->toArray());
            $documentItem->subtotal = $itemSum;

            // 2.1.3 get all item info to insert to document_items
            $itemModel = Item::findOrFail($item->item_id);
            //dump($itemModel->toArray());
            $documentItem->name = $itemModel->name;
            $documentItem->sku = $itemModel->sku;

            $documentItem->save();

            // 2.1.4 check for is_partial_delivery then set Document status to Partial delivery
            if ($itemIsPartialDelivery) {
                // get partial delivery id. DISABLED FOR NOW.
                // $documentStatusPartial = DocumentStatus::where('name', 'partial delivery')->firstOrFail();
                // $data->document_status_id = $documentStatusPartial->id; // 7 - partial delivery
            }
        }
        $grandTotal += $subtotal;

        // 2.4.0 delete first all data for this document in document_totals
        DocumentTotal::where('document_id', $id)->delete();

        // 2.4 create record in document_totals
        $documentTotalSort = 1;
        // 2.4.1 sub_total
        $documentTotal = new DocumentTotal();
        $documentTotal->type = 'invoice'; // env('DOCUMENTTOTAL_TYPE_INVOICE');
        $documentTotal->code = 'sub_total'; // env('DOCUMENTTOTAL_CODE_SUBTOTAL');
        $documentTotal->name = 'invoices.sub_total'; // env('DOCUMENTTOTAL_NAME_SUBTOTAL');
        $documentTotal->amount = $subtotal;
        $documentTotal->document_id = $id;
        $documentTotal->sort_order = $documentTotalSort;
        $documentTotalSort += 1;
        $documentTotal->created_by = auth()->user()->id;
        $documentTotal->document_type_id = 1; // invoice
        $documentTotal->save();

        // 2.4.2 discount
        if ($data->discount_type != null && $data->discount_value != null) {
            $documentTotal = new DocumentTotal();
            $documentTotal->type = 'invoice'; // env('DOCUMENTTOTAL_TYPE_INVOICE');
            $documentTotal->code = 'discount'; // env('DOCUMENTTOTAL_CODE_DISCOUNT');
            $documentTotal->name = 'invoices.discount'; // env('DOCUMENTTOTAL_NAME_DISCOUNT');

            // compute discount amount
            $documentTotalAmount = 0;
            if ($data->discount_type == 1) { // percentage
                $documentTotal->amount = $subtotal * ($data->discount_value/100);
                $documentTotalAmount = $subtotal * ($data->discount_value/100);
            } else { // fix amount
                $documentTotal->amount = $data->discount_value;
                $documentTotalAmount = $data->discount_value;
            }
            $documentTotal->document_id = $id;
            $documentTotal->sort_order = $documentTotalSort;
            $documentTotalSort += 1;
            $documentTotal->created_by = auth()->user()->id;
            $documentTotal->document_type_id = 1; // invoice
            $documentTotal->save();

            $grandTotal = $grandTotal - $documentTotalAmount;
        }

        // 2.4.3 tax
        if ($data->tax_id != null) {
            $tax = Tax::findOrFail($data->tax_id);
            // dump($tax);
            $taxRate = $tax->rate;
            $taxAmount = $subtotal * ($taxRate/100);

            $documentTotal = new DocumentTotal();
            $documentTotal->type = 'invoice'; // env('DOCUMENTTOTAL_TYPE_INVOICE');
            $documentTotal->code = 'tax'; // env('DOCUMENTTOTAL_CODE_TAX');
            $documentTotal->name = $tax->name;
            $documentTotal->amount = $taxAmount;
            $documentTotal->document_id = $id;
            $documentTotal->sort_order = $documentTotalSort;
            $documentTotalSort += 1;
            $documentTotal->created_by = auth()->user()->id;
            $documentTotal->document_type_id = 1; // invoice
            $documentTotal->save();

            $grandTotal = $grandTotal + $taxAmount;
        }

        // 2.4.4 total
        $documentTotal = new DocumentTotal();
        $documentTotal->type = 'invoice'; // env('DOCUMENTTOTAL_TYPE_INVOICE');
        $documentTotal->code = 'total'; // env('DOCUMENTTOTAL_CODE_TOTAL');
        $documentTotal->name = 'invoices.total'; // env('DOCUMENTTOTAL_NAME_TOTAL');
        $documentTotal->amount = $grandTotal;
        $documentTotal->document_id = $id;
        $documentTotal->sort_order = $documentTotalSort;
        $documentTotal->created_by = auth()->user()->id;
        $documentTotal->document_type_id = 1; // invoice
        $documentTotal->save();

        // 2.5.1 created by
        $data->updated_by = auth()->user()->id;
        $data->updated_name = auth()->user()->name;

        // 2.5.3 grand total amount, subtotal - discount + tax
        $data->grand_amount = $grandTotal;

        // 2.5.4 set discount_value to null if discount_value is null
        if ($data['discount_type'] == null) {
            $data['discount_value'] = null;
        }

        // 2.6 update Document
        $data->items_search = $data->items->pluck('name')->join(', '); // Denormalized
        $data->save();

        //$documentStatusReleased = DocumentStatus::where('name', 'released')->firstOrFail(); // get the id of paid status
        // dump('$recordDocumentStatusId:'.$recordDocumentStatusId.' $documentStatusReleased:'.$documentStatusReleased);

        // if ($recordDocumentStatusId == $documentStatusPaid->id) {
        if ($recordDocumentStatusId == $documentStatusReleased->id) {
            // 3.1 check if already set as paid or released then don't minus
            // $documentHistory = DocumentHistory::where('document_id', $data->id)->count();
            $documentHistory = DocumentHistory::where('document_id', $data->id)->limit(1)->count();

            //if ($documentHistory == 0) {
                // dump('INSERT TO ItemHistory');
                foreach($items as $item) {
                    // check if item set as partial delivery
                    $itemIsPartialDelivery = $item->is_partial_delivery;

                    $quantityToBeMinus = $item->quantity;

                    // do not minus and record to itemhistory if partial delivery
                    // if ($itemIsPartialDelivery == false) {
                    if (
                        $item->is_partial_delivery == false &&
                        $item->is_preorder == false &&
                        $item->is_returns == false &&
                        $item->is_refunds == false &&
                        $item->is_offset == false

                        ) {
                        // 3.1 minus the item quantity
                        $itemModel = Item::findOrFail($item->item_id);

                        // check item stock if 0 then do not minus
                        if ($itemModel->quantity > 0) {
                            $itemModel->quantity = $itemModel->quantity - $quantityToBeMinus;
                        }
                        $itemModel->save();

                        // 3.2 record the item in item_histories as 'invoice'
                        $newItemHistory = new ItemHistory();
                        $newItemHistory->item_id = $item->item_id;
                        $newItemHistory->document_id = $id;
                        $newItemHistory->item_history_action_type_id = 2; // invoice
                        $newItemHistory->stock = -$quantityToBeMinus;
                        $newItemHistory->created_by = auth()->user()->id;
                        $newItemHistory->created_name = auth()->user()->name;
                        $newItemHistory->save();

                        // dump($newItemHistory);
                    }
                }
            //}
        }

        // 3.2 record to document_histories if paid or released status
        DocumentHistory::insert([
            'document_id' => $data->id,
            'document_type_id' => $data->document_type_id,
            'document_number' => $data->document_number,
            'order_number' => $data->order_number,
            'tracking_number' => $data->tracking_number,
            'document_status_id' => $data->document_status_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'created_by' => auth()->user()->id,
            'created_name' => auth()->user()->name,
        ]);

        // 3.2.1 filter all document_items.is_freebie
        // delete existing freebie in freebie_customer_document_items where customer_id=$item->customer->customer_id AND document_item_id=$item->id AND document_id=$id
        FreebieCustomerDocumentItem::where('document_id', $id)->delete();
        foreach($items as $item) {
            if ($item->is_freebie) {
                // insert in freebie_customer_document_items
                $freebieCustomerDocumentItem = new FreebieCustomerDocumentItem();
                $freebieCustomerDocumentItem->customer_id = $item->document->customer_id;
                $freebieCustomerDocumentItem->document_id = $id;
                $freebieCustomerDocumentItem->document_item_id = $item->id;
                $freebieCustomerDocumentItem->save();
            }
        }

        // HISTORY MODULE - NOT YET IN PROD
        $newDocument = Document::findOrFail($record['id']);
        CreateDocument::createDocumentHistory($newDocument);


    }
}
