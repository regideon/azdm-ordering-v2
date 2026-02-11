<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Models\Document;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Group;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['lg' => 3])
                    ->schema([

                        Section::make('Order')
                            ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
                            ->schema([
                                TextEntry::make('customer_name')
                                    ->label('Customer')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),
                                    
                                TextEntry::make('customer_nick')
                                    ->label('FB Name')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('customer_address')
                                    ->label('Shipping Address')
                                    ->columnSpan(['sm' => 1, 'md' => 6, 'xl' => 6]),


                                TextEntry::make('document_number')
                                    ->label('Invoice Number')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('issued_at')
                                    ->label('Invoice Date')
                                    ->date()
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('due_at')
                                    ->label('Due Date')
                                    ->date()
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('documentStatus.name')
                                    ->label('Status')
                                    ->badge()
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('order_number')
                                    ->label('P.O./S.O. Number')
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('tracking_number')
                                    ->color('danger')
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('paymentSort.name')
                                    ->label('COD ODR OK REMIT')
                                    ->badge()
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),
                                
                            ]),   

                        Section::make('Items')
                            ->columnSpanFull()
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('')
                                    ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
                                    ->schema([
                                        TextEntry::make('name')
                                            ->weight(FontWeight::Bold)
                                            ->columnSpan(['sm' => 1, 'md' => 6, 'xl' => 6]),
                                        
                                        
                                        TextEntry::make('quantity')
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                        TextEntry::make('price')
                                            // ->money('PHP')
                                            ->formatStateUsing(fn ($state) => $state !== null
                                                ? '₱' . number_format($state, 0)
                                                : null)
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                        TextEntry::make('subtotal')
                                            // ->money('PHP')
                                            ->formatStateUsing(fn ($state) => $state !== null
                                                ? '₱' . number_format($state, 0)
                                                : null)
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),

                                        TextEntry::make('description')
                                            ->label('')
                                            ->columnSpan(['sm' => 1, 'md' => 12, 'xl' => 12]),


                                        TextEntry::make('is_partial_delivery')
                                            ->label('Out of stock')
                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'danger' : 'success')
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                        TextEntry::make('is_freebie')
                                            ->label('Freebie')
                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'danger' : 'success')
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                        TextEntry::make('is_preorder')
                                            ->label('Pre-order')
                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'danger' : 'success')
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                        TextEntry::make('is_returns')
                                            ->label('Returns')
                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'danger' : 'success')
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                        TextEntry::make('is_refunds')
                                            ->label('Refunds')
                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'danger' : 'success')
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                        TextEntry::make('is_offset')
                                            ->label('Offset')
                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'danger' : 'success')
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                        TextEntry::make('is_cancelled')
                                            ->label('Cancelled')
                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'danger' : 'success')
                                            ->columnSpan(['sm' => 1, 'md' => 2, 'xl' => 2]),
                                    ]),
                            ]),

                        Section::make('Grand Total')
                            ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
                            ->schema([
                                TextEntry::make('notes')
                                    ->html()
                                    ->columnSpan(['sm' => 1, 'md' => 6, 'xl' => 6]),

                                TextEntry::make('grand_subtotal')
                                    ->label('Subtotal')
                                    ->money('PHP')
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('grand_amount')
                                    ->label('Grand Total')
                                    ->weight(FontWeight::Bold)
                                    ->money('PHP')
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),

                                TextEntry::make('return_at')
                                    ->label('Date Return')
                                    ->date()
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),
                                TextEntry::make('refunded_at')
                                    ->label('Date Refunded')
                                    ->date()
                                    ->columnSpan(['sm' => 1, 'md' => 3, 'xl' => 3]),
                            ]),

                        Section::make('Media Uploaded')
                            ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
                            ->schema([

                                TextEntry::make('attachments')
                                    ->label('Photos/Videos')
                                    ->state(function (?Document $record) {
                                        if (! $record || ! isset($record->attachments)) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }

                                        $files = is_array($record->attachments)
                                            ? $record->attachments
                                            : json_decode($record->attachments, true);

                                        if (! $files || ! is_array($files) || count($files) === 0) {
                                            return "<span ='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }

                                        $counter = 1;
                                        $html = '';

                                        foreach (array_reverse($files) as $path) {
                                            $url = Storage::disk('spaces')->url($path);
                                            $html .= '<p><a href="'.e($url).'" target="_blank" class="text-primary-600 underline">'
                                                .'View attachment '.$counter
                                                .'</a></p>';
                                            $counter++;
                                        }

                                        return new HtmlString($html);
                                    })
                                    ->html() // 🔥 render HTML instead of plain text
                                    ->columnSpanFull(),


                                TextEntry::make('attachments_qc')
                                    ->label('Photos/Videos for Quality Control')
                                    ->state(function (?Document $record) {
                                        if (! $record || ! isset($record->attachments_qc)) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }

                                        $files = is_array($record->attachments_qc)
                                            ? $record->attachments_qc
                                            : json_decode($record->attachments_qc, true);

                                        if (! $files || ! is_array($files) || count($files) === 0) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }
                                        $counter = 1;
                                        $html = '';
                                        foreach (array_reverse($files) as $path) {
                                            $url = Storage::disk('spaces')->url($path);
                                            $html .= '<p><a href="'.e($url).'" target="_blank" class="text-primary-600 underline">'
                                                .'View attachment '.$counter
                                                .'</a></p>';
                                            $counter++;
                                        }
                                        return new HtmlString($html);
                                    })
                                    ->html()
                                    ->columnSpanFull(),
                                    
                                TextEntry::make('attachments_packing')
                                    ->label('Photos/Videos for Packing')
                                    ->state(function (?Document $record) {
                                        if (! $record || ! isset($record->attachments_packing)) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }

                                        $files = is_array($record->attachments_packing)
                                            ? $record->attachments_packing
                                            : json_decode($record->attachments_packing, true);

                                        if (! $files || ! is_array($files) || count($files) === 0) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }
                                        $counter = 1;
                                        $html = '';
                                        foreach (array_reverse($files) as $path) {
                                            $url = Storage::disk('spaces')->url($path);
                                            $html .= '<p><a href="'.e($url).'" target="_blank" class="text-primary-600 underline">'
                                                .'View attachment '.$counter
                                                .'</a></p>';
                                            $counter++;
                                        }
                                        return new HtmlString($html);
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                                TextEntry::make('attachments_clerk')
                                    ->label('Photos/Videos for Clerk Checker')
                                    ->state(function (?Document $record) {
                                        if (! $record || ! isset($record->attachments_clerk)) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }

                                        $files = is_array($record->attachments_clerk)
                                            ? $record->attachments_clerk
                                            : json_decode($record->attachments_clerk, true);

                                        if (! $files || ! is_array($files) || count($files) === 0) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }
                                        $counter = 1;
                                        $html = '';
                                        foreach (array_reverse($files) as $path) {
                                            $url = Storage::disk('spaces')->url($path);
                                            $html .= '<p><a href="'.e($url).'" target="_blank" class="text-primary-600 underline">'
                                                .'View attachment '.$counter
                                                .'</a></p>';
                                            $counter++;
                                        }
                                        return new HtmlString($html);
                                    })
                                    ->html()
                                    ->columnSpanFull(),                        

                                TextEntry::make('attachments_delivery')
                                    ->label('Photos for Delivery Rider')
                                    ->state(function (?Document $record) {
                                        if (! $record || ! isset($record->attachments_delivery)) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }

                                        $files = is_array($record->attachments_delivery)
                                            ? $record->attachments_delivery
                                            : json_decode($record->attachments_delivery, true);

                                        if (! $files || ! is_array($files) || count($files) === 0) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }
                                        $counter = 1;
                                        $html = '';
                                        foreach (array_reverse($files) as $path) {
                                            $url = Storage::disk('spaces')->url($path);
                                            $html .= '<p><a href="'.e($url).'" target="_blank" class="text-primary-600 underline">'
                                                .'View attachment '.$counter
                                                .'</a></p>';
                                            $counter++;
                                        }
                                        return new HtmlString($html);
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                                TextEntry::make('attachments_orderchecker')
                                    ->label('Photos for Order Checker')
                                    ->state(function (?Document $record) {
                                        if (! $record || ! isset($record->attachments_orderchecker)) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }

                                        $files = is_array($record->attachments_orderchecker)
                                            ? $record->attachments_orderchecker
                                            : json_decode($record->attachments_orderchecker, true);

                                        if (! $files || ! is_array($files) || count($files) === 0) {
                                            return "<span style='color: #c5c5c5;'>No photos/videos uploaded.</span>";
                                        }
                                        $counter = 1;
                                        $html = '';
                                        foreach (array_reverse($files) as $path) {
                                            $url = Storage::disk('spaces')->url($path);
                                            $html .= '<p><a href="'.e($url).'" target="_blank" class="text-primary-600 underline">'
                                                .'View attachment '.$counter
                                                .'</a></p>';
                                            $counter++;
                                        }
                                        return new HtmlString($html);
                                    })
                                    ->html()
                                    ->columnSpanFull(),

                            ]),

                        
                        Section::make('Notes')
                            ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
                            ->schema([
                                TextEntry::make('notes_clerk')
                                    ->label('Notes for Clerk Checker')
                                    ->html()
                                    ->columnSpan(['sm' => 1, 'md' => 6, 'xl' => 6]),

                                TextEntry::make('notes_delivery')
                                    ->label('Notes for Delivery Rider')
                                    ->html()
                                    ->columnSpan(['sm' => 1, 'md' => 6, 'xl' => 6]),

                                TextEntry::make('notes_orderchecker')
                                    ->label('Notes for Order Checker')
                                    ->html()
                                    ->columnSpan(['sm' => 1, 'md' => 6, 'xl' => 6]),

                                
                                
                            ]),

                    


                    
                    ]),
                 
            
            ]);
    }
}
