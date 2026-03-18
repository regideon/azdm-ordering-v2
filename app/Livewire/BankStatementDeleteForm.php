<?php

namespace App\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BankStatementDeleteForm extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file')
                    ->label('Delete Bank Statement CSV')
                    ->required()
                    ->disk('local')
                    ->directory('livewire-tmp')
                    ->acceptedFileTypes(['text/csv'])
                    ->extraAttributes(['style' => 'margin-bottom: 16px']),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $tempPath = $data['file'] ?? null;

        if (! $tempPath || ! Storage::disk('local')->exists($tempPath)) {
            abort(422, "File does not exist at local path: {$tempPath}");
        }

        $filename = basename($tempPath);
        $finalPath = 'bank-statements/' . $filename;

        Storage::disk('spaces')->put(
            $finalPath,
            Storage::disk('local')->get($tempPath)
        );

        $csvContent = Storage::disk('local')->get($tempPath);

        if (! $csvContent) {
            abort(422, 'CSV content is empty.');
        }

        $csv = \League\Csv\Reader::createFromString($csvContent);
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        $errorRecords = [];

        foreach ($records as $index => $record) {
            $row = array_values($record);
            $id = trim($row[4] ?? '');

            if (! $id || ! is_numeric($id)) {
                DB::table('bank_statement_logs')->insert([
                    'bank_statement_bank_id' => $row[0] ?? null,
                    'date_at' => $row[1] ?? now()->toDateString(),
                    'description' => 'id=' . $id . ' description:' . ($row[2] ?? null),
                    'amount' => $row[3] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $errorRecords[] = 'Row ' . ($index + 2) . ' - Invalid or missing ID';

                continue;
            }

            DB::table('bank_statements')
                ->where('id', $id)
                ->update(['deleted_at' => now()]);
        }

        $message = $errorRecords !== []
            ? 'Some bank statements were deleted successfully, but the following record(s) had issues and were logged. Please check:' . PHP_EOL . implode(PHP_EOL, $errorRecords)
            : 'Bank statements deleted successfully.';

        session()->flash('success', $message);
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.bank-statement-delete-form');
    }
}
