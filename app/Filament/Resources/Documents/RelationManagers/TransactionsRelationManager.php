<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use App\Models\Credit;
use App\Models\Document;
use App\Models\DocumentStatus;
use App\Models\Transaction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Payment Transactions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
            ->components([
                Hidden::make('document_type_id')
                    ->default(3),

                Hidden::make('account_id')
                    ->default(1),

                Hidden::make('document_category_id')
                    ->default(2),

                Hidden::make('created_by')
                    ->default(auth()->id()),

                Select::make('payment_type_id')
                    ->label('Payment Method')
                    ->options(fn (): array => $this->getPaymentTypeOptions())
                    ->default($this->getDefaultPaymentTypeId())
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $this->handlePaymentTypeUpdated($state, $set);
                    })
                    ->required()
                    ->columnSpan(['sm' => 12, 'md' => 3, 'xl' => 3]),

                TextInput::make('transaction_number')
                    ->default(fn (?Transaction $record): string => $record?->transaction_number ?? $this->generateNewTransactionNumber())
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->columnSpan(['sm' => 12, 'md' => 3, 'xl' => 3]),

                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->prefix('P')
                    ->required()
                    ->columnSpan(['sm' => 12, 'md' => 3, 'xl' => 3]),

                DatePicker::make('paid_at')
                    ->label('Date Paid')
                    ->default(now())
                    ->required()
                    ->columnSpan(['sm' => 12, 'md' => 3, 'xl' => 3]),

                TextInput::make('reference')
                    ->maxLength(190)
                    ->columnSpan(['sm' => 12, 'md' => 3, 'xl' => 3]),

                Select::make('bank_statement_id')
                    ->label('Bank Statement')
                    ->options(fn (): array => $this->getBankStatementOptions())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state): void {
                        $amount = $this->getBankStatementAmount($state);

                        if ($amount !== null) {
                            $set('amount', number_format($amount, 2, '.', ''));
                        }
                    })
                    ->columnSpan(['sm' => 12, 'md' => 9, 'xl' => 9]),

                TextInput::make('description')
                    ->label('Notes')
                    ->maxLength(190)
                    ->columnSpan(['sm' => 12, 'md' => 12, 'xl' => 12]),

                Placeholder::make('ph_warning')
                    ->label('')
                    ->content(new HtmlString(
                        '<b style="color: black; font-style: italic;">If using <span style="color: red;">Credit as Payment Type</span>, please make sure you enter the right amount because you cannot undo your action.</b>'
                    ))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('id'))
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('transaction_number')
                    ->searchable(),

                TextColumn::make('amount')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->prefix('P')
                    ->summarize([
                        Sum::make()->label('Total Payments'),
                    ]),

                TextColumn::make('payment_type_id')
                    ->label('Payment Method')
                    ->state(fn (Transaction $record): ?string => $this->resolvePaymentTypeName($record->payment_type_id))
                    ->formatStateUsing(fn (?string $state): ?string => $state ? Str::headline($state) : null)
                    ->badge()
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->label('Date Paid')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('bank_statement_id')
                    ->label('Bank Statement')
                    ->state(fn (Transaction $record): ?string => $this->resolveBankStatementLabel($record->bank_statement_id))
                    ->description(fn (Transaction $record): ?string => $record->reference)
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make('header')
                    ->hidden(fn (): bool => false)
                    ->mutateFormDataUsing(function (array $data, CreateAction $action): array {
                        return $this->mutateCreateData($data, $action);
                    })
                    ->after(function (Transaction $record): void {
                        $this->afterTransactionCreated($record);
                    })
                    ->successNotificationTitle('Payment added!'),
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (Transaction $record): bool => $this->shouldHideEditAction($record)),

                DeleteAction::make()
                    ->hidden(fn (Transaction $record): bool => false)
                    ->after(function (Transaction $record): void {
                        $this->afterTransactionDeleted($record);
                    }),
            ])
            ->toolbarActions([
                //
            ])
            ->emptyStateActions([
                //
            ]);
    }

    protected function mutateCreateData(array $data, CreateAction $action): array
    {
        $ownerRecord = $this->getOwnerDocument();
        $documentId = $ownerRecord->id;

        $data['customer_id'] = $ownerRecord->customer_id;

        $grandAmount = (float) $ownerRecord->grand_amount;
        $newAmount = (float) ($data['amount'] ?? 0);
        $transactionsAmount = (float) Transaction::query()
            ->where('document_id', $documentId)
            ->sum('amount');
        $transactionsAmountTotal = $transactionsAmount + $newAmount;

        $latestTransaction = Transaction::query()->latest('id')->first();
        $nextTransactionId = ($latestTransaction?->id ?? 0) + 1;

        $isCreditUsed = false;
        $isCODUsed = false;

        $creditPaymentTypeId = $this->findPaymentTypeIdByName('Credit');
        $codPaymentTypeId = $this->findPaymentTypeIdByName('Cash On Delivery');

        if (($data['payment_type_id'] ?? null) === $creditPaymentTypeId && $creditPaymentTypeId !== null) {
            $isCreditUsed = true;
            $creditsSum = (float) Credit::query()
                ->where('customer_id', $ownerRecord->customer_id)
                ->sum('amount');

            if ($newAmount > $creditsSum) {
                Notification::make()
                    ->warning()
                    ->title('You credits is insufficient!')
                    ->body("'{$ownerRecord->customer_name}' credits is 'P{$creditsSum}'. Enter a valid amount.")
                    ->persistent()
                    ->send();

                $action->halt();
            }

            if (
                Transaction::query()->where('document_id', $documentId)->count() > 0 &&
                (bool) env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION')
            ) {
                Notification::make()
                    ->warning()
                    ->title((string) env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION_TITLE'))
                    ->body((string) env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION_MSG'))
                    ->persistent()
                    ->send();

                $action->halt();
            }

            $credit = new Credit();
            $credit->customer_id = $ownerRecord->customer_id;
            $credit->document_id = $documentId;
            $credit->document_number = $ownerRecord->document_number;
            $credit->transaction_id = $nextTransactionId;
            $credit->transaction_number = $data['transaction_number'];
            $credit->amount = -$newAmount;
            $credit->is_credit = false;
            $credit->issued_at = now();
            $credit->created_by = $data['created_by'] ?? auth()->id();
            $credit->created_name = auth()->user()?->name;
            $credit->save();
        }

        if (($data['payment_type_id'] ?? null) === $codPaymentTypeId && $codPaymentTypeId !== null) {
            $isCODUsed = true;
            $documentStatusCOD = DocumentStatus::query()
                ->where('name', 'cash on delivery')
                ->first();

            $document = Document::query()->findOrFail($documentId);
            $document->is_cod = true;
            $document->payment_sort_id = 1;

            if ($documentStatusCOD) {
                $document->document_status_id = $documentStatusCOD->id;
            }

            $document->save();
        }

        if ((bool) env('HALT_SAVE_RECORD_FOR_TRANSACTIONS')) {
            Notification::make()
                ->danger()
                ->title((string) env('HALT_SAVE_RECORD_MSG'))
                ->seconds(3)
                ->send();

            $action->halt();
        }

        if ($transactionsAmountTotal >= $grandAmount && ! $isCODUsed) {
            $documentStatusPaid = DocumentStatus::query()
                ->where('name', 'paid')
                ->first();

            if ($documentStatusPaid) {
                $document = Document::query()->findOrFail($documentId);
                $document->document_status_id = $documentStatusPaid->id;
                $document->save();
            }
        }

        if ($transactionsAmountTotal > $grandAmount && ! $isCreditUsed && ! $isCODUsed) {
            $creditAmount = $transactionsAmountTotal - $grandAmount;

            $credit = new Credit();
            $credit->customer_id = $ownerRecord->customer_id;
            $credit->document_id = $documentId;
            $credit->document_number = $ownerRecord->document_number;
            $credit->transaction_id = $nextTransactionId;
            $credit->transaction_number = $data['transaction_number'];
            $credit->amount = $creditAmount;
            $credit->is_credit = true;
            $credit->issued_at = now();
            $credit->created_by = $data['created_by'] ?? auth()->id();
            $credit->created_name = auth()->user()?->name;
            $credit->save();
        }

        if (! empty($data['bank_statement_id'])) {
            $bankStatement = $this->findBankStatement($data['bank_statement_id']);

            if ($bankStatement) {
                $data['amount'] = $bankStatement->amount;
            }
        }

        return $data;
    }

    protected function afterTransactionCreated(Transaction $record): void
    {
        $bankStatement = $this->findBankStatement($record->bank_statement_id);

        if (! $bankStatement) {
            return;
        }

        $ownerRecord = $this->getOwnerDocument();

        $bankStatement->is_used = true;
        $bankStatement->document_id = $ownerRecord->id;
        $bankStatement->transaction_id = $record->id;
        $bankStatement->created_by = auth()->id();
        $bankStatement->created_by_name = auth()->user()?->name;
        $bankStatement->save();

        $historyClass = $this->getBankStatementHistoryModelClass();

        if (! $historyClass) {
            return;
        }

        $history = new $historyClass();
        $history->bank_statement_id = $bankStatement->id;
        $history->document_id = $ownerRecord->id;
        $history->transaction_id = $record->id;
        $history->date_at = $bankStatement->date_at;
        $history->bank_statement_bank_id = $bankStatement->bank_statement_bank_id;
        $history->description = $bankStatement->description;
        $history->amount = $bankStatement->amount;
        $history->is_used = $bankStatement->is_used;
        $history->is_revoked = 0;
        $history->created_by = auth()->id();
        $history->created_by_name = auth()->user()?->name;
        $history->save();
    }

    protected function afterTransactionDeleted(Transaction $record): void
    {
        $bankStatement = $this->findBankStatement($record->bank_statement_id);

        if (! $bankStatement) {
            return;
        }

        $historySnapshot = clone $bankStatement;

        $bankStatement->is_used = false;
        $bankStatement->document_id = null;
        $bankStatement->transaction_id = null;
        $bankStatement->updated_at = null;
        $bankStatement->save();

        $historyClass = $this->getBankStatementHistoryModelClass();

        if (! $historyClass) {
            return;
        }

        $history = new $historyClass();
        $history->bank_statement_id = $historySnapshot->id;
        $history->document_id = $historySnapshot->document_id;
        $history->transaction_id = $historySnapshot->transaction_id;
        $history->date_at = $historySnapshot->date_at;
        $history->bank_statement_bank_id = $historySnapshot->bank_statement_bank_id;
        $history->description = $historySnapshot->description;
        $history->amount = $historySnapshot->amount;
        $history->is_used = $historySnapshot->is_used;
        $history->is_revoked = 1;
        $history->created_by = auth()->id();
        $history->created_by_name = auth()->user()?->name;
        $history->save();
    }

    protected function handlePaymentTypeUpdated(mixed $state, callable $set): void
    {
        $creditPaymentTypeId = $this->findPaymentTypeIdByName('Credit');
        $codPaymentTypeId = $this->findPaymentTypeIdByName('Cash On Delivery');

        if ($state === $creditPaymentTypeId && $creditPaymentTypeId !== null) {
            $ownerRecord = $this->getOwnerDocument();

            if (
                Transaction::query()->where('document_id', $ownerRecord->id)->count() > 0 &&
                (bool) env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION')
            ) {
                Notification::make()
                    ->warning()
                    ->title((string) env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION_TITLE'))
                    ->body((string) env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION_MSG'))
                    ->persistent()
                    ->send();

                return;
            }

            $creditsSum = (float) Credit::query()
                ->where('customer_id', $ownerRecord->customer_id)
                ->sum('amount');
            $totalPaymentMade = (float) Transaction::query()
                ->where('document_id', $ownerRecord->id)
                ->sum('amount');
            $remainingBalance = (float) $ownerRecord->grand_amount - $totalPaymentMade;

            $notifMsgAdded = '';

            if ($creditsSum >= (float) $ownerRecord->grand_amount) {
                $set('amount', $remainingBalance);
                $notifMsgAdded = " You can use the P{$remainingBalance} credits.";
            }

            Notification::make()
                ->title("'{$ownerRecord->customer_name}' has a total credits of P{$creditsSum}.{$notifMsgAdded}")
                ->icon('heroicon-o-currency-dollar')
                ->iconColor('success')
                ->persistent()
                ->send();
        }

        if ($state === $codPaymentTypeId && $codPaymentTypeId !== null) {
            // Original resource intentionally left the COD amount assignment disabled.
        }
    }

    protected function shouldHideEditAction(Transaction $record): bool
    {
        $creditPaymentTypeId = $this->findPaymentTypeIdByName('Credit');

        if (($creditPaymentTypeId !== null && $record->payment_type_id == $creditPaymentTypeId) || $record->bank_statement_id != null) {
            return true;
        }

        return Document::setIfDisabled($this->getOwnerDocument()->id);
    }

    protected function getOwnerDocument(): Document
    {
        /** @var Document $ownerRecord */
        $ownerRecord = $this->ownerRecord;

        return $ownerRecord;
    }

    protected function getPaymentTypeOptions(): array
    {
        $paymentTypeClass = $this->getPaymentTypeModelClass();

        if (! $paymentTypeClass) {
            return [];
        }

        return $paymentTypeClass::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function getDefaultPaymentTypeId(): ?int
    {
        return $this->findPaymentTypeIdByName('Cash');
    }

    protected function resolvePaymentTypeName(?int $paymentTypeId): ?string
    {
        $paymentTypeClass = $this->getPaymentTypeModelClass();

        if (! $paymentTypeClass || ! $paymentTypeId) {
            return null;
        }

        return $paymentTypeClass::query()
            ->whereKey($paymentTypeId)
            ->value('name');
    }

    protected function findPaymentTypeIdByName(string $name): ?int
    {
        $paymentTypeClass = $this->getPaymentTypeModelClass();

        if (! $paymentTypeClass) {
            return null;
        }

        return $paymentTypeClass::query()
            ->where('name', $name)
            ->value('id');
    }

    protected function getBankStatementOptions(): array
    {
        $bankStatementClass = $this->getBankStatementModelClass();

        if (! $bankStatementClass) {
            return [];
        }

        return $bankStatementClass::query()
            ->leftJoin('bank_statement_banks', 'bank_statements.bank_statement_bank_id', '=', 'bank_statement_banks.id')
            ->selectRaw("CONCAT(bank_statements.description, ' - ', COALESCE(bank_statement_banks.name, ''), ' - ', bank_statements.amount, ' (', DATE_FORMAT(bank_statements.date_at, '%Y/%m/%d'), ')') as fullinfo")
            ->addSelect('bank_statements.id')
            ->where('bank_statements.is_used', false)
            ->orderBy('bank_statements.description')
            ->pluck('fullinfo', 'bank_statements.id')
            ->all();
    }

    protected function resolveBankStatementLabel(?int $bankStatementId): ?string
    {
        $bankStatement = $this->findBankStatement($bankStatementId);

        if (! $bankStatement) {
            return null;
        }

        $bankName = data_get($bankStatement, 'bankStatementBank.name');

        return trim(implode(' - ', array_filter([
            $bankStatement->description,
            $bankName,
            $bankStatement->amount,
        ])));
    }

    protected function getBankStatementAmount(mixed $bankStatementId): ?float
    {
        $bankStatement = $this->findBankStatement($bankStatementId);

        return $bankStatement ? (float) $bankStatement->amount : null;
    }

    protected function findBankStatement(mixed $bankStatementId): ?Model
    {
        $bankStatementClass = $this->getBankStatementModelClass();

        if (! $bankStatementClass || blank($bankStatementId)) {
            return null;
        }

        return $bankStatementClass::query()
            ->with('bankStatementBank')
            ->find($bankStatementId);
    }

    protected function getPaymentTypeModelClass(): ?string
    {
        return class_exists(\App\Models\PaymentType::class) ? \App\Models\PaymentType::class : null;
    }

    protected function getBankStatementModelClass(): ?string
    {
        return class_exists(\App\Models\BankStatement::class) ? \App\Models\BankStatement::class : null;
    }

    protected function getBankStatementHistoryModelClass(): ?string
    {
        return class_exists(\App\Models\BankStatementHistory::class) ? \App\Models\BankStatementHistory::class : null;
    }

    protected function generateNewTransactionNumber(): string
    {
        if (method_exists(Transaction::class, 'generateNewNumber')) {
            return app(Transaction::class)->generateNewNumber();
        }

        $nextNumber = (Transaction::query()->max('id') ?? 0) + 1;
        $prefix = (string) env('PREFIX_TRANSACTIONS', 'TRX-');

        return $prefix . str_pad((string) $nextNumber, 7, '0', STR_PAD_LEFT);
    }
}
