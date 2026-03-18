<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class BankStatementDeletePage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.bank-statement-delete-page';

    protected static ?string $navigationLabel = 'Delete Bank Statements';

    protected static ?string $title = 'Delete Bank Statements';

    protected static bool $shouldRegisterNavigation = false;
}
