<?php

namespace App\Livewire;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Component;

class DocumentReportPerMonthForm extends Component implements HasForms
{
    use InteractsWithForms;

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->endOfMonth()->toDateString(),
            'document_status_id' => 5,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filters')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required(),

                        Select::make('document_status_id')
                            ->label('Order Status')
                            ->disabled()
                            ->dehydrated()
                            ->options([
                                '5' => 'Settled',
                            ])
                            ->placeholder('All statuses')
                            ->searchable(),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $maxDays = 31;
        $data = $this->form->getState();
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();
        $paidStatusIds = [5, 6, 7, 8, 9, 10, 11, 12, 13, 23, 26];

        $statusId = $data['document_status_id'] ?? null;
        $statusOrigId = $data['document_status_id'] ?? null;

        if ($statusId === '' || $statusId === 0 || $statusId === 5 || $statusId === '5') {
            $statusId = null;
        }

        $rangeDays = $startDate->diffInDays($endDate) + 1;
        if ($rangeDays > $maxDays) {
            Notification::make()
                ->title('Invalid Date Range')
                ->body("Your selected range is {$rangeDays} days. Maximum allowed is {$maxDays} days.")
                ->danger()
                ->duration(8000)
                ->send();

            return response()->stream(function (): void {}, 204, ['Content-Type' => 'text/plain']);
        }

        $filename = 'document_items_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($startDate, $endDate, $statusId, $paidStatusIds, $statusOrigId): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'document number',
                'issued at',
                'status name',
                'quantity',
                'price',
                'subtotal',
                'subtotal excluded',
                'is_out_of_stock',
                'is_freebie',
                'is_preorder',
                'is_returns',
                'is_refunds',
                'is_offset',
                'is_cancelled',
                'DIID',
            ]);

            $query = \DB::table('documents as d')
                ->join('document_items as di', 'di.document_id', '=', 'd.id')
                ->leftJoin('document_statuses as ds', function ($join): void {
                    $join->on('ds.id', '=', 'd.document_status_id')
                        ->where('ds.enabled', '=', 1);
                })
                ->whereBetween('d.issued_at', [$startDate, $endDate])
                ->selectRaw("
                    d.document_number,
                    d.issued_at AS issued_at,
                    COALESCE(ds.name, 'N/A') as status_name,
                    di.quantity,
                    di.price,
                    CASE
                        WHEN COALESCE(di.is_partial_delivery,0)=1
                        OR COALESCE(di.is_freebie,0)=1
                        OR COALESCE(di.is_preorder,0)=1
                        OR COALESCE(di.is_returns,0)=1
                        OR COALESCE(di.is_refunds,0)=1
                        OR COALESCE(di.is_offset,0)=1
                        OR COALESCE(di.is_cancelled,0)=1
                        THEN 0
                        ELSE di.subtotal
                    END AS subtotal,
                    CASE
                        WHEN COALESCE(di.is_partial_delivery,0)=1
                        OR COALESCE(di.is_freebie,0)=1
                        OR COALESCE(di.is_preorder,0)=1
                        OR COALESCE(di.is_returns,0)=1
                        OR COALESCE(di.is_refunds,0)=1
                        OR COALESCE(di.is_offset,0)=1
                        OR COALESCE(di.is_cancelled,0)=1
                        THEN di.subtotal
                        ELSE 0
                    END AS subtotal_excluded,
                    COALESCE(di.is_partial_delivery,0) AS is_partial_delivery,
                    COALESCE(di.is_freebie,0) AS is_freebie,
                    COALESCE(di.is_preorder,0) AS is_preorder,
                    COALESCE(di.is_returns,0) AS is_returns,
                    COALESCE(di.is_refunds,0) AS is_refunds,
                    COALESCE(di.is_offset,0) AS is_offset,
                    COALESCE(di.is_cancelled,0) AS is_cancelled,
                    di.id AS DIID
                ")
                ->orderBy('d.document_number')
                ->orderBy('di.id');

            if ($statusOrigId == 20 || $statusOrigId == 22) {
                if ($statusOrigId == 20) {
                    $query->where('di.is_cancelled', true);
                } elseif ($statusOrigId == 22) {
                    $query->where('di.is_refunds', true);
                }
            } else {
                if (! is_null($statusId)) {
                    $query->where('d.document_status_id', $statusId);
                } else {
                    $query->whereIn('d.document_status_id', $paidStatusIds);
                }
            }

            $rows = $query->cursor();
            $sumSubtotal = 0.0;

            foreach ($rows as $row) {
                $sumSubtotal += (float) ($row->subtotal ?? 0);

                fputcsv($out, [
                    $row->document_number,
                    Carbon::parse($row->issued_at)->format('Y-m-d'),
                    $row->status_name,
                    (string) $row->quantity,
                    number_format((float) ($row->price ?? 0), 2, '.', ''),
                    number_format((float) ($row->subtotal ?? 0), 2, '.', ''),
                    number_format((float) ($row->subtotal_excluded ?? 0), 2, '.', ''),
                    (int) $row->is_partial_delivery,
                    (int) $row->is_freebie,
                    (int) $row->is_preorder,
                    (int) $row->is_returns,
                    (int) $row->is_refunds,
                    (int) $row->is_offset,
                    (int) $row->is_cancelled,
                    (string) $row->DIID,
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, [
                'TOTAL',
                '',
                '',
                '',
                '',
                number_format($sumSubtotal, 2, '.', ''),
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ]);

            fclose($out);
        }, $filename);
    }

    public function render()
    {
        return view('livewire.document-report-per-month-form');
    }
}
