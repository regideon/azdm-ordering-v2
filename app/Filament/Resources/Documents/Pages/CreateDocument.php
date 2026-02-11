<?php

namespace App\Filament\Resources\Documents\Pages;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\User;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\DocumentTotal;
use App\Models\DocumentHistory;
use App\Models\DocumentHistoryNew;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Models\FreebieCustomerDocumentItem;
use App\Filament\Resources\Documents\DocumentResource;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {        
        if ($this->noDuplicateItems) {
            // dump('beforeCreate');
            $data = $this->data;
            $items = $data['items'];
            // // dd($data);

            $itemIds = [];
            $duplicateItemNames = [];
            $hasDuplicateItem = false;
            foreach ($items as $item) {
                $itemId = $item['item_id'];
                // dump($item);
                if (in_array($itemId, $itemIds)) {
                    $hasDuplicateItem = true;
                    $itemObject = Item::findOrFail($itemId);
                    $duplicateItemNames[] = $itemObject->name;
                }
                $itemIds[] = $itemId;
            }
           
            if ($hasDuplicateItem) {
                Notification::make()
                ->warning()
                ->title('Duplicate Items')
                ->body('Please remove the duplicate item(s) ' . implode(', ', $duplicateItemNames) . '.')
                ->persistent()
                ->actions([
                    // Action::make('subscribe')
                    //     ->button()
                    //     ->url(route('subscribe'), shouldOpenInNewTab: true),
                ])
                ->send();

                $this->halt();
            }
        }
    }

    protected function afterCreate(): void
    {
        // $data = $this->data;
        // dump($data);
        
        $record = $this->record;
        $id = $record['id'];

        $data = Document::findOrFail($id);
        //dd($record->toArray());
        
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
        $items = DocumentItem::where('document_id', $id)->get();
        // dump($items);
        foreach($items as $item) {
            $itemSum = $item->price * $item->quantity;
            $subtotal += $itemSum;

            // 2.1.2 save subtotal
            $documentItem = DocumentItem::findOrFail($item->id);
            // dump($documentItem);
            $documentItem->subtotal = $itemSum;

            // 2.1.3 get all item info to insert to document_items
            $itemModel = Item::findOrFail($item->item_id);
            // dump($itemModel);
            $documentItem->name = $itemModel->name;
            // $documentItem->description = $itemModel->description;
            $documentItem->sku = $itemModel->sku;

            $documentItem->save();

            // 2.1.4 check for is_partial_delivery then set Document status to Partial delivery
            if ($record['is_partial_delivery']) {
                $data->document_status_id = 7; // partial delivery
            }
        }
        $grandTotal += $subtotal;
        // dd('IM HERE');
        
        
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
        $documentTotal->created_name = auth()->user()->name;
        $documentTotal->document_type_id = 1; // invoice
        $documentTotal->save();

        // 2.4.2 discount
        if ($data->discount_type != null) {
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
            $documentTotal->created_name = auth()->user()->name;
            $documentTotal->document_type_id = 1; // invoice
            $documentTotal->save();

            $grandTotal = $grandTotal - $documentTotalAmount;
        }

        // 2.4.3 tax
        if ($data->tax_id != null) {
            $tax = Tax::findOrFail($data->tax_id);
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
            $documentTotal->created_name = auth()->user()->name;
            $documentTotal->document_type_id = 1; // invoice
            $documentTotal->save();

            $grandTotal = $grandTotal + $taxAmount;
        }

        // 2.4.4 total (grand total)
        $documentTotal = new DocumentTotal();
        $documentTotal->type = 'invoice'; // env('DOCUMENTTOTAL_TYPE_INVOICE');
        $documentTotal->code = 'total'; // env('DOCUMENTTOTAL_CODE_TOTAL');
        $documentTotal->name = 'invoices.total'; // env('DOCUMENTTOTAL_NAME_TOTAL');
        $documentTotal->amount = $grandTotal;
        $documentTotal->document_id = $id;
        $documentTotal->sort_order = $documentTotalSort;
        $documentTotal->created_by = auth()->user()->id;
        $documentTotal->created_name = auth()->user()->name;
        $documentTotal->document_type_id = 1; // invoice
        $documentTotal->save();
        
        // 2.5.1 created by
        $data->created_by = auth()->user()->id;
        $data->created_name = auth()->user()->name;
        
       

        // 2.5.3 grand total amount, subtotal - discount + tax
        $data->grand_amount = $grandTotal;

        // 2.5.4 set discount_value to null if discount_value is null
        if ($data['discount_type'] == null) {
            $data['discount_value'] = null;
        }

        // 2.6 update Document        
        $data->items_search = $data->items->pluck('name')->join(', '); // Denormalized
        $data->save();
        
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
        
        $databaseName = DB::connection()->getDatabaseName();
        $user = User::where('email', 'admin@ziontek.co')->first();
        // if ($databaseName == 'ztwebordering') {
        //     $user = User::where('email', 'admin@ziontek.co')->first();
        // } else {
        //     $user = User::where('email', 'salesadmin@ziontek.co')->first();
        // }

        $newDocument = Document::findOrFail($record['id']);
        CreateDocument::createDocumentHistory($newDocument);

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
    }

    function randomString($len)
    {
        $string = "";
        // $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        for($i=0;$i<$len;$i++)
            $string.=substr($chars,rand(0,strlen($chars)),1);
        return $string;
    }

    public static function createDocumentHistory($newDocument)
    {
        $documentHistoryNew = new DocumentHistoryNew();
        $documentHistoryNew->document_id = $newDocument->id;
        $documentHistoryNew->document_type_id = $newDocument->document_type_id;
        $documentHistoryNew->document_number = $newDocument->document_number;
        $documentHistoryNew->order_number = $newDocument->order_number;
        $documentHistoryNew->tracking_number = $newDocument->tracking_number;
        $documentHistoryNew->document_status_id = $newDocument->document_status_id;
        $documentHistoryNew->issued_at = $newDocument->issued_at;
        $documentHistoryNew->due_at = $newDocument->due_at;
        $documentHistoryNew->grand_subtotal = $newDocument->grand_subtotal;
        $documentHistoryNew->discount_type = $newDocument->discount_type;
        $documentHistoryNew->discount_value = $newDocument->discount_value;
        $documentHistoryNew->tax_id = $newDocument->tax_id;
        $documentHistoryNew->tax_value = $newDocument->tax_value;
        $documentHistoryNew->grand_amount = $newDocument->grand_amount;
        $documentHistoryNew->currency_code = $newDocument->currency_code;
        $documentHistoryNew->currency_rate = $newDocument->currency_rate;
        $documentHistoryNew->document_category_id = $newDocument->document_category_id;
        $documentHistoryNew->customer_id = $newDocument->customer_id;
        $documentHistoryNew->customer_name = $newDocument->customer_name;
        $documentHistoryNew->customer_nick = $newDocument->customer_nick;
        $documentHistoryNew->customer_email = $newDocument->customer_email;
        $documentHistoryNew->customer_tax_number = $newDocument->customer_tax_number;
        $documentHistoryNew->customer_phone = $newDocument->customer_phone;
        $documentHistoryNew->customer_address = $newDocument->customer_address;
        $documentHistoryNew->customer_city = $newDocument->customer_city;
        $documentHistoryNew->customer_zip_code = $newDocument->customer_zip_code;
        $documentHistoryNew->customer_state = $newDocument->customer_state;
        $documentHistoryNew->customer_country = $newDocument->customer_country;
        $documentHistoryNew->notes = $newDocument->notes;
        $documentHistoryNew->payment_info = $newDocument->payment_info;
        $documentHistoryNew->attachments = $newDocument->attachments;
        $documentHistoryNew->attachments_qc = $newDocument->attachments_qc;
        $documentHistoryNew->attachments_packing = $newDocument->attachments_packing;
        $documentHistoryNew->attachments_clerk = $newDocument->attachments_clerk;
        $documentHistoryNew->document_shipment_type_id = $newDocument->document_shipment_type_id;
        $documentHistoryNew->notes_clerk = $newDocument->notes_clerk;
        $documentHistoryNew->ship_at = $newDocument->ship_at;
        $documentHistoryNew->attachments_delivery = $newDocument->attachments_delivery;
        $documentHistoryNew->notes_delivery = $newDocument->notes_delivery;
        $documentHistoryNew->attachments_orderchecker = $newDocument->attachments_orderchecker;
        $documentHistoryNew->notes_orderchecker = $newDocument->notes_orderchecker;
        $documentHistoryNew->created_by = $newDocument->created_by;
        $documentHistoryNew->created_name = $newDocument->created_name;
        $documentHistoryNew->updated_by = auth()->user()->id;
        $documentHistoryNew->updated_name = auth()->user()->name;
        $documentHistoryNew->panel_name = $newDocument->panel_name;
        $documentHistoryNew->created_at = $newDocument->created_at;
        $documentHistoryNew->updated_at = Carbon::now();
        $documentHistoryNew->return_at = $newDocument->return_at;
        $documentHistoryNew->refunded_at = $newDocument->refunded_at;
        $documentHistoryNew->is_cod = $newDocument->is_cod;
        $documentHistoryNew->payment_sort_id = $newDocument->payment_sort_id;
        $documentHistoryNew->save();
    }
}
