<?php

namespace App\Filament\Resources\BankStatements\Pages;

use App\Filament\Pages\BankStatementDeletePage;
use App\Filament\Pages\BankStatementImporterPage;
use App\Filament\Resources\BankStatements\BankStatementResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListBankStatements extends ListRecords
{
    protected static string $resource = BankStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('BankStatementDeletePage')
                ->label('Delete bank statements')
                ->url(BankStatementDeletePage::getUrl())
                ->color('danger'),

            Action::make('BankStatementImporterPage')
                ->label('Import bank statements')
                ->url(BankStatementImporterPage::getUrl())
                ->color('primary'),
        ];
    }
}
