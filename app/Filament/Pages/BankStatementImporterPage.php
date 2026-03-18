<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class BankStatementImporterPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.bank-statement-importer-page';

    protected static ?string $title = 'Import Bank Statements';

    protected static bool $shouldRegisterNavigation = false;
}
