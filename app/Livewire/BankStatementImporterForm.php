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

class BankStatementImporterForm extends Component implements HasForms
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
                    ->label('Bank Statement CSV')
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

        $records = $csv->getRecords([
            'bank_statement_bank_id',
            'date_at',
            'description',
            'amount',
        ]);

        $rowsToInsert = [];
        $errorRecords = [];

        foreach ($records as $index => $record) {
            $bankId = trim($record['bank_statement_bank_id'] ?? '');
            $dateAt = trim($record['date_at'] ?? '');
            $description = trim($record['description'] ?? '');
            $amount = trim($record['amount'] ?? '');

            if (! $bankId || ! $dateAt || ! $description || ! $amount) {
                DB::table('bank_statement_logs')->insert([
                    'bank_statement_bank_id' => $bankId ?: null,
                    'date_at' => $dateAt ?: now()->toDateString(),
                    'description' => $description ?: null,
                    'amount' => $amount ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $errorRecords[] = 'Record ' . ($index + 1);

                continue;
            }

            $description = preg_replace('/\s+/', ' ', $description);
            $description = trim(str_replace(["\r", "\n"], '', $description));
            $amount = (float) str_replace(',', '', $amount);

            try {
                $parsedDate = \Carbon\Carbon::parse($dateAt)->format('Y-m-d');
            } catch (\Exception $exception) {
                $errorRecords[] = 'Record ' . ($index + 1);

                continue;
            }

            $rowsToInsert[] = [
                'bank_statement_bank_id' => $bankId,
                'date_at' => $parsedDate,
                'description' => $description,
                'amount' => $amount,
                'is_used' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rowsToInsert !== []) {
            DB::table('bank_statements')->insert($rowsToInsert);
        }

        $message = $errorRecords !== []
            ? 'Some bank statements were uploaded successfully, but the following record(s) had issues and were logged. Please check:' . PHP_EOL . implode(PHP_EOL, $errorRecords)
            : 'Bank statements uploaded successfully. It will be processed shortly.';

        session()->flash('success', $message);
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.bank-statement-importer-form');
    }
}
