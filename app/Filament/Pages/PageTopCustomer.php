<?php

namespace App\Filament\Pages;

use App\Models\Document;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class PageTopCustomer extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.page-top-customer';

    protected static string | UnitEnum | null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 140;

    protected static ?string $navigationLabel = 'Top Customers';

    public $records;

    public ?array $data = [];

    public function mount(): void
    {
        $this->records = Document::select('customer_name', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(grand_amount) as total_spent'))
            ->groupBy('customer_name')
            ->orderBy('total_spent', 'desc')
            ->take(20)
            ->get();

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter')
                    ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
                    ->schema([
                        DatePicker::make('start_at')
                            ->label('Start date')
                            ->required()
                            ->columnSpan(['sm' => 12, 'md' => 4, 'xl' => 4]),

                        DatePicker::make('end_at')
                            ->label('End date')
                            ->required()
                            ->columnSpan(['sm' => 12, 'md' => 4, 'xl' => 4]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Filter results')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $this->records = Document::select('customer_name', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(grand_amount) as total_spent'))
                ->whereBetween('updated_at', [$data['start_at'], $data['end_at']])
                ->groupBy('customer_name')
                ->orderBy('total_spent', 'desc')
                ->take(20)
                ->get();
        } catch (Halt $exception) {
            return;
        }

        Notification::make()
            ->success()
            ->title('Filters applied successfully!')
            ->send();
    }
}
