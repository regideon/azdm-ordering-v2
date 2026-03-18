<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class DocumentReportPerMonthPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.document-report-per-month-page';

    protected static ?string $title = 'Sales Reports';

    protected static ?string $navigationLabel = 'Sales Reports';

    protected static ?int $navigationSort = 256;

    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['Superadmin', 'Admin', 'Accounting Supervisor']);
    }
}
